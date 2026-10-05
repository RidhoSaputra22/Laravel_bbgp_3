package main

import (
	"context"
	"database/sql"
	"errors"
	"fmt"
	"os"
	"strconv"
	"strings"
	"testing"
	"time"

	"go.mongodb.org/mongo-driver/bson"
)

// Integration tests are opt-in and must use a dedicated test database. They
// intentionally never read Laravel's .env file or production defaults.
func requireIntegration(t *testing.T) {
	t.Helper()
	if os.Getenv("SYNC_INTEGRATION_TESTS") != "1" {
		t.Skip("set SYNC_INTEGRATION_TESTS=1 to run external database integration tests")
	}
}

func openIntegrationMySQL(t *testing.T) (*sql.DB, context.Context) {
	t.Helper()
	dsn := strings.TrimSpace(os.Getenv("SYNC_TEST_MYSQL_DSN"))
	if dsn == "" {
		t.Skip("SYNC_TEST_MYSQL_DSN is not configured")
	}

	ctx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	t.Cleanup(cancel)
	db, err := sql.Open("mysql", withUTCMySQLSession(dsn))
	if err != nil {
		t.Fatalf("open MySQL test database: %v", err)
	}
	t.Cleanup(func() { _ = db.Close() })
	if err := db.PingContext(ctx); err != nil {
		t.Fatalf("ping MySQL test database: %v", err)
	}
	return db, ctx
}

func TestIntegrationOutboxLifecycle(t *testing.T) {
	requireIntegration(t)
	db, ctx := openIntegrationMySQL(t)

	var tableCheck int
	if err := db.QueryRowContext(ctx, "SELECT 1 FROM sync_outbox LIMIT 1").Scan(&tableCheck); err != nil && !errors.Is(err, sql.ErrNoRows) {
		t.Skipf("sync_outbox is not available in the configured test database: %v", err)
	}

	outbox := &Outbox{db: db, config: Config{LeaseTimeout: time.Minute, MaxAttempts: 2}}
	pending, err := outbox.Pending(ctx)
	if err != nil {
		t.Fatalf("count pending outbox events: %v", err)
	}
	if pending != 0 {
		t.Skipf("test database has %d existing pending events; use a dedicated empty database", pending)
	}

	entityType := "sync_worker_integration"
	baseID := time.Now().UnixNano()
	ids := []int64{baseID, baseID + 1}
	for _, id := range ids {
		if _, err := db.ExecContext(ctx, `
			INSERT INTO sync_outbox
				(entity_type, entity_id, version, status, attempts, available_at, created_at, updated_at)
			VALUES (?, ?, 1, 'pending', 0, UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP())`, entityType, id); err != nil {
			t.Fatalf("insert test outbox event: %v", err)
		}
	}
	t.Cleanup(func() {
		_, _ = db.ExecContext(context.Background(),
			"DELETE FROM sync_outbox WHERE entity_type = ? AND entity_id IN (?, ?)", entityType, ids[0], ids[1])
	})

	workerID := "sync-worker-integration-" + strconv.FormatInt(baseID, 10)
	events, err := outbox.Claim(ctx, 2, workerID)
	if err != nil {
		t.Fatalf("claim test outbox events: %v", err)
	}
	if len(events) != 2 {
		t.Fatalf("claimed %d events, want 2: %#v", len(events), events)
	}

	if err := outbox.Complete(ctx, events[0], workerID); err != nil {
		t.Fatalf("complete current-version event: %v", err)
	}
	var status string
	if err := db.QueryRowContext(ctx, "SELECT status FROM sync_outbox WHERE id = ?", events[0].ID).Scan(&status); err != nil {
		t.Fatalf("read completed event: %v", err)
	}
	if status != "done" {
		t.Fatalf("completed event status = %q, want done", status)
	}

	if _, err := db.ExecContext(ctx, "UPDATE sync_outbox SET version = version + 1 WHERE id = ?", events[1].ID); err != nil {
		t.Fatalf("simulate newer event version: %v", err)
	}
	if err := outbox.Complete(ctx, events[1], workerID); err != nil {
		t.Fatalf("complete stale-version event: %v", err)
	}
	if err := db.QueryRowContext(ctx, "SELECT status FROM sync_outbox WHERE id = ?", events[1].ID).Scan(&status); err != nil {
		t.Fatalf("read stale-version event: %v", err)
	}
	if status != "pending" {
		t.Fatalf("stale-version event status = %q, want pending", status)
	}

	claimedAgain, err := outbox.Claim(ctx, 1, workerID)
	if err != nil {
		t.Fatalf("reclaim stale-version event: %v", err)
	}
	if len(claimedAgain) != 1 || claimedAgain[0].ID != events[1].ID {
		t.Fatalf("reclaimed events = %#v, want event %d", claimedAgain, events[1].ID)
	}
	if err := outbox.Fail(ctx, claimedAgain[0], workerID, fmt.Errorf("intentional integration failure")); err != nil {
		t.Fatalf("fail exhausted event: %v", err)
	}
	if err := db.QueryRowContext(ctx, "SELECT status FROM sync_outbox WHERE id = ?", events[1].ID).Scan(&status); err != nil {
		t.Fatalf("read failed event: %v", err)
	}
	if status != "failed" {
		t.Fatalf("failed event status = %q, want failed", status)
	}
}

func TestIntegrationMongoWriterLifecycle(t *testing.T) {
	requireIntegration(t)
	uri := strings.TrimSpace(os.Getenv("SYNC_TEST_MONGO_URI"))
	database := strings.TrimSpace(os.Getenv("SYNC_TEST_MONGO_DATABASE"))
	if uri == "" || database == "" {
		t.Skip("SYNC_TEST_MONGO_URI and SYNC_TEST_MONGO_DATABASE are not configured")
	}

	ctx, cancel := context.WithTimeout(context.Background(), 20*time.Second)
	defer cancel()
	config := Config{MongoURI: uri, MongoDatabase: database, ShadowSuffix: "_integration_shadow"}
	writer, disconnect, err := NewMongoWriter(ctx, config, false)
	if err != nil {
		t.Fatalf("connect MongoDB test database: %v", err)
	}
	defer func() { _ = disconnect() }()

	collection := "sync_worker_integration_" + strconv.FormatInt(time.Now().UnixNano(), 10)
	shadowWriter := &MongoWriter{db: writer.db, config: config, shadow: true}
	cleanup := func() {
		_, _ = writer.collection(collection).DeleteMany(context.Background(), bson.D{})
		_, _ = shadowWriter.collection(collection).DeleteMany(context.Background(), bson.D{})
	}
	t.Cleanup(cleanup)

	documents := []map[string]any{{"_id": "case-1", "value": 1}}
	if err := writer.Upsert(ctx, collection, documents); err != nil {
		t.Fatalf("insert MongoDB document: %v", err)
	}
	var got map[string]any
	if err := writer.collection(collection).FindOne(ctx, bson.M{"_id": "case-1"}).Decode(&got); err != nil {
		t.Fatalf("read inserted MongoDB document: %v", err)
	}
	if got["value"] != int32(1) && got["value"] != int64(1) && got["value"] != float64(1) {
		t.Fatalf("unexpected inserted document: %#v", got)
	}

	if err := writer.Upsert(ctx, collection, []map[string]any{{"_id": "case-1", "value": 2}}); err != nil {
		t.Fatalf("replace MongoDB document: %v", err)
	}
	if err := writer.collection(collection).FindOne(ctx, bson.M{"_id": "case-1"}).Decode(&got); err != nil {
		t.Fatalf("read replaced MongoDB document: %v", err)
	}
	if got["value"] != int32(2) && got["value"] != int64(2) && got["value"] != float64(2) {
		t.Fatalf("replacement did not take effect: %#v", got)
	}

	if err := shadowWriter.Upsert(ctx, collection, []map[string]any{{"_id": "shadow-1", "value": "shadow"}}); err != nil {
		t.Fatalf("insert shadow MongoDB document: %v", err)
	}
	if err := writer.collection(collection).FindOne(ctx, bson.M{"_id": "shadow-1"}).Err(); err == nil {
		t.Fatal("shadow document unexpectedly appeared in production collection")
	}
	if err := shadowWriter.collection(collection).FindOne(ctx, bson.M{"_id": "shadow-1"}).Err(); err != nil {
		t.Fatalf("shadow document was not written to suffixed collection: %v", err)
	}

	if err := writer.RebuildReset(ctx, collection); err != nil {
		t.Fatalf("reset MongoDB collection: %v", err)
	}
	count, err := writer.collection(collection).CountDocuments(ctx, bson.D{})
	if err != nil {
		t.Fatalf("count reset MongoDB collection: %v", err)
	}
	if count != 0 {
		t.Fatalf("reset collection contains %d documents, want 0", count)
	}
}
