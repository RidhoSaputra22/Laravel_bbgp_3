package main

import (
	"context"
	"database/sql"
	"fmt"
	"log"
	"os"
	"strconv"
	"strings"
	"time"
)

type Worker struct {
	db       *Database
	outbox   *Outbox
	builder  *Builder
	mongo    *MongoWriter
	config   Config
	workerID string
}

func (w *Worker) RunOnce(ctx context.Context) (int, error) {
	return w.runOnce(ctx, true)
}

func (w *Worker) runOnce(ctx context.Context, verbose bool) (int, error) {
	if verbose {
		log.Printf("outbox scan batch_size=%d", w.config.BatchSize)
	}
	if err := w.outbox.RequeueExpired(ctx); err != nil {
		return 0, err
	}
	events, err := w.outbox.Claim(ctx, w.config.BatchSize, w.workerID)
	if err != nil {
		return 0, err
	}
	if len(events) == 0 {
		if verbose {
			log.Printf("outbox idle pending=0")
		}
		return 0, nil
	}

	targetIDs, validatorIDs := make([]int64, 0), make([]int64, 0)
	for _, event := range events {
		switch event.EntityType {
		case entityTarget:
			targetIDs = append(targetIDs, event.EntityID)
		case entityValidator:
			validatorIDs = append(validatorIDs, event.EntityID)
		default:
			_ = w.outbox.Fail(ctx, event, w.workerID, fmt.Errorf("unknown entity type %q", event.EntityType))
		}
	}
	if verbose {
		log.Printf("outbox claimed events=%d targets=%d validators=%d", len(events), len(targetIDs), len(validatorIDs))
	}

	documents := map[string]map[int64]map[string]any{}
	if len(targetIDs) > 0 {
		built, buildErr := w.builder.BuildTargets(ctx, targetIDs)
		if buildErr != nil {
			return 0, w.failBatch(ctx, events, buildErr)
		}
		documents[entityTarget] = indexDocuments(built, "assessment-target:")
		if err := w.mongo.Upsert(ctx, w.config.TargetCollection, built); err != nil {
			return 0, w.failBatch(ctx, events, err)
		}
	}
	if len(validatorIDs) > 0 {
		built, buildErr := w.builder.BuildValidators(ctx, validatorIDs)
		if buildErr != nil {
			return 0, w.failBatch(ctx, events, buildErr)
		}
		documents[entityValidator] = indexDocuments(built, "validator-assignment:")
		if err := w.mongo.Upsert(ctx, w.config.ValidatorCollection, built); err != nil {
			return 0, w.failBatch(ctx, events, err)
		}
	}

	for _, event := range events {
		if _, ok := documents[event.EntityType][event.EntityID]; !ok && event.EntityType != entityTarget && event.EntityType != entityValidator {
			continue
		}
		if err := w.outbox.Complete(ctx, event, w.workerID); err != nil {
			return 0, err
		}
	}
	if verbose {
		log.Printf("sync batch complete events=%d targets=%d validators=%d", len(events), len(targetIDs), len(validatorIDs))
	}
	return len(events), nil
}

func (w *Worker) RunUntilEmpty(ctx context.Context, batchTimeout time.Duration) error {
	if err := w.outbox.RequeueExpired(ctx); err != nil {
		return err
	}
	total, err := w.outbox.Pending(ctx)
	if err != nil {
		return err
	}

	batches := 0
	processed := 0
	renderProgress(processed, total)
	for {
		batchCtx, cancel := context.WithTimeout(ctx, batchTimeout)
		batchProcessed, err := w.runOnce(batchCtx, false)
		cancel()
		if err != nil {
			fmt.Println()
			return err
		}
		processed += batchProcessed
		renderProgress(processed, total)
		if batchProcessed == 0 {
			break
		}
		batches++
	}
	fmt.Println()
	log.Printf("outbox drain complete batches=%d events=%d", batches, processed)
	return nil
}

func renderProgress(processed, total int) {
	const width = 40
	if total < processed {
		total = processed
	}
	if total == 0 {
		fmt.Printf("\rMongoDB sync [%s] 0/0 (100%%)", strings.Repeat("=", width))
		return
	}

	percent := processed * 100 / total
	filled := processed * width / total
	if filled > width {
		filled = width
	}
	fmt.Printf("\rMongoDB sync [%s%s] %d/%d (%3d%%)",
		strings.Repeat("=", filled),
		strings.Repeat("-", width-filled),
		processed,
		total,
		percent,
	)
}

func (w *Worker) failBatch(ctx context.Context, events []OutboxEvent, cause error) error {
	for _, event := range events {
		if err := w.outbox.Fail(ctx, event, w.workerID, cause); err != nil {
			log.Printf("outbox failure update id=%d: %v", event.ID, err)
		}
	}
	return cause
}

func (w *Worker) Rebuild(ctx context.Context, kind string, reset bool) error {
	if reset {
		if kind == "targets" || kind == "all" {
			if err := w.mongo.RebuildReset(ctx, w.config.TargetCollection); err != nil {
				return err
			}
		}
		if kind == "validators" || kind == "all" {
			if err := w.mongo.RebuildReset(ctx, w.config.ValidatorCollection); err != nil {
				return err
			}
		}
	}
	var targetIDs, validatorIDs []int64
	if kind == "targets" || kind == "all" {
		ids, err := listIDs(ctx, w.db.db, `SELECT t.id FROM assessment_assignment_targets t JOIN assessment_assignments a ON a.id=t.assessment_assignment_id WHERE t.is_validator=0 AND (a.kode_penugasan IS NULL OR a.kode_penugasan NOT LIKE 'PREVIEW-ADM-%') ORDER BY t.id`)
		if err != nil {
			return err
		}
		targetIDs = ids
	}
	if kind == "validators" || kind == "all" {
		ids, err := listIDs(ctx, w.db.db, `SELECT id FROM validator_assignments ORDER BY id`)
		if err != nil {
			return err
		}
		validatorIDs = ids
	}

	processed := 0
	total := len(targetIDs) + len(validatorIDs)
	renderProgress(processed, total)
	defer fmt.Println()
	if targetIDs != nil {
		if err := w.rebuildTargets(ctx, targetIDs, &processed, total); err != nil {
			return err
		}
	}
	if validatorIDs != nil {
		if err := w.rebuildValidators(ctx, validatorIDs, &processed, total); err != nil {
			return err
		}
	}
	return nil
}

func (w *Worker) rebuildTargets(ctx context.Context, ids []int64, processed *int, total int) error {
	for start := 0; start < len(ids); start += w.config.BatchSize {
		end := start + w.config.BatchSize
		if end > len(ids) {
			end = len(ids)
		}
		docs, err := w.builder.BuildTargets(ctx, ids[start:end])
		if err != nil {
			return err
		}
		if err := w.mongo.Upsert(ctx, w.config.TargetCollection, docs); err != nil {
			return err
		}
		*processed += end - start
		renderProgress(*processed, total)
	}
	return nil
}
func (w *Worker) rebuildValidators(ctx context.Context, ids []int64, processed *int, total int) error {
	for start := 0; start < len(ids); start += w.config.BatchSize {
		end := start + w.config.BatchSize
		if end > len(ids) {
			end = len(ids)
		}
		docs, err := w.builder.BuildValidators(ctx, ids[start:end])
		if err != nil {
			return err
		}
		if err := w.mongo.Upsert(ctx, w.config.ValidatorCollection, docs); err != nil {
			return err
		}
		*processed += end - start
		renderProgress(*processed, total)
	}
	return nil
}

func indexDocuments(documents []map[string]any, prefix string) map[int64]map[string]any {
	result := map[int64]map[string]any{}
	for _, document := range documents {
		id, ok := document["_id"].(string)
		if !ok || len(id) <= len(prefix) {
			continue
		}
		number, err := strconv.ParseInt(id[len(prefix):], 10, 64)
		if err == nil {
			result[number] = document
		}
	}
	return result
}
func listIDs(ctx context.Context, db *sql.DB, query string) ([]int64, error) {
	rows, err := db.QueryContext(ctx, query)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	ids := []int64{}
	for rows.Next() {
		var id int64
		if err := rows.Scan(&id); err != nil {
			return nil, err
		}
		ids = append(ids, id)
	}
	return ids, rows.Err()
}
func newWorker(db *sql.DB, mongo *MongoWriter, config Config) *Worker {
	workerID := fmt.Sprintf("go-sync-%d-%d", os.Getpid(), time.Now().UnixNano())
	database := &Database{db: db}
	return &Worker{db: database, outbox: &Outbox{db: db, config: config}, builder: &Builder{db: database, concurrency: config.BuildConcurrency}, mongo: mongo, config: config, workerID: workerID}
}
