package main

import (
	"context"
	"database/sql"
	"fmt"
	"strings"
	"time"
)

const (
	entityTarget    = "assessment_target"
	entityValidator = "validator_assignment"
)

type OutboxEvent struct {
	ID         int64
	EntityType string
	EntityID   int64
	Version    int64
	Attempts   int
}

type Outbox struct {
	db     *sql.DB
	config Config
}

func (o *Outbox) Pending(ctx context.Context) (int, error) {
	var total int
	err := o.db.QueryRowContext(ctx, `
		SELECT COUNT(*)
		FROM sync_outbox
		WHERE status = 'pending'
		  AND (available_at IS NULL OR available_at <= UTC_TIMESTAMP())`).Scan(&total)
	return total, err
}

func (o *Outbox) RequeueExpired(ctx context.Context) error {
	_, err := o.db.ExecContext(ctx, `
		UPDATE sync_outbox
		SET status = 'pending', locked_at = NULL, locked_by = NULL,
			available_at = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP()
		WHERE status = 'processing' AND locked_at < ?`, time.Now().UTC().Add(-o.config.LeaseTimeout))
	return err
}

func (o *Outbox) Claim(ctx context.Context, limit int, workerID string) ([]OutboxEvent, error) {
	tx, err := o.db.BeginTx(ctx, nil)
	if err != nil {
		return nil, err
	}
	defer tx.Rollback()

	rows, err := tx.QueryContext(ctx, `
		SELECT id, entity_type, entity_id, version, attempts
		FROM sync_outbox
		WHERE status = 'pending'
		  AND (available_at IS NULL OR available_at <= UTC_TIMESTAMP())
		ORDER BY id
		LIMIT ? FOR UPDATE`, limit)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	events := make([]OutboxEvent, 0, limit)
	for rows.Next() {
		var event OutboxEvent
		if err := rows.Scan(&event.ID, &event.EntityType, &event.EntityID, &event.Version, &event.Attempts); err != nil {
			return nil, err
		}
		events = append(events, event)
	}
	if err := rows.Err(); err != nil {
		return nil, err
	}
	if len(events) == 0 {
		return events, nil
	}

	ids := make([]string, 0, len(events))
	now := time.Now().UTC()
	args := []any{workerID, now, now}
	for _, event := range events {
		ids = append(ids, "?")
		args = append(args, event.ID)
	}
	query := fmt.Sprintf(`
		UPDATE sync_outbox
		SET status = 'processing', locked_by = ?, locked_at = ?,
			attempts = attempts + 1, updated_at = ?
		WHERE id IN (%s)`, strings.Join(ids, ","))
	if _, err := tx.ExecContext(ctx, query, args...); err != nil {
		return nil, err
	}
	if err := tx.Commit(); err != nil {
		return nil, err
	}
	return events, nil
}

func (o *Outbox) Complete(ctx context.Context, event OutboxEvent, workerID string) error {
	_, err := o.db.ExecContext(ctx, `
		UPDATE sync_outbox
		SET status = CASE WHEN version = ? THEN 'done' ELSE 'pending' END,
			locked_at = NULL, locked_by = NULL,
			processed_at = CASE WHEN version = ? THEN UTC_TIMESTAMP() ELSE processed_at END,
			available_at = CASE WHEN version = ? THEN available_at ELSE UTC_TIMESTAMP() END,
			updated_at = UTC_TIMESTAMP()
		WHERE id = ? AND locked_by = ?`, event.Version, event.Version, event.Version, event.ID, workerID)
	return err
}

func (o *Outbox) Fail(ctx context.Context, event OutboxEvent, workerID string, cause error) error {
	status := "pending"
	if event.Attempts >= o.config.MaxAttempts {
		status = "failed"
	}
	delay := time.Duration(event.Attempts*event.Attempts) * 30 * time.Second
	_, err := o.db.ExecContext(ctx, `
		UPDATE sync_outbox
		SET status = CASE WHEN version <> ? THEN 'pending' ELSE ? END,
			locked_at = NULL, locked_by = NULL,
			available_at = CASE WHEN version <> ? THEN UTC_TIMESTAMP() ELSE ? END,
			last_error = ?, updated_at = UTC_TIMESTAMP()
		WHERE id = ? AND locked_by = ?`, event.Version, status, event.Version, time.Now().UTC().Add(delay), cause.Error(), event.ID, workerID)
	return err
}
