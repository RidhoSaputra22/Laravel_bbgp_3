package main

import (
	"context"
	"fmt"

	"go.mongodb.org/mongo-driver/bson"
	"go.mongodb.org/mongo-driver/mongo"
	"go.mongodb.org/mongo-driver/mongo/options"
)

type MongoWriter struct {
	db     *mongo.Database
	config Config
	shadow bool
}

func NewMongoWriter(ctx context.Context, config Config, shadow bool) (*MongoWriter, func() error, error) {
	client, err := mongo.Connect(ctx, options.Client().ApplyURI(config.MongoURI))
	if err != nil {
		return nil, nil, err
	}
	if err := client.Ping(ctx, nil); err != nil {
		_ = client.Disconnect(ctx)
		return nil, nil, err
	}
	return &MongoWriter{db: client.Database(config.MongoDatabase), config: config, shadow: shadow}, func() error { return client.Disconnect(context.Background()) }, nil
}

func (w *MongoWriter) collection(name string) *mongo.Collection {
	if w.shadow {
		name += w.config.ShadowSuffix
	}
	return w.db.Collection(name)
}

func (w *MongoWriter) Upsert(ctx context.Context, collection string, documents []map[string]any) error {
	if len(documents) == 0 {
		return nil
	}
	models := make([]mongo.WriteModel, 0, len(documents))
	for _, document := range documents {
		id, ok := document["_id"].(string)
		if !ok || id == "" {
			return fmt.Errorf("document has no string _id")
		}
		if isTombstone(document) {
			models = append(models, mongo.NewDeleteOneModel().SetFilter(bson.M{"_id": id}))
			continue
		}
		models = append(models, mongo.NewReplaceOneModel().SetFilter(bson.M{"_id": id}).SetReplacement(document).SetUpsert(true))
	}
	_, err := w.collection(collection).BulkWrite(ctx, models, options.BulkWrite().SetOrdered(false))
	return err
}

func isTombstone(document map[string]any) bool {
	meta, ok := document["meta"].(map[string]any)
	if !ok {
		return false
	}
	source, ok := meta["snapshot_source"].(string)
	return ok && source == "tombstone"
}

func (w *MongoWriter) RebuildReset(ctx context.Context, collection string) error {
	_, err := w.collection(collection).DeleteMany(ctx, bson.D{})
	return err
}

func (w *MongoWriter) EnsureIndexes(ctx context.Context) error {
	target := w.collection(w.config.TargetCollection)
	validator := w.collection(w.config.ValidatorCollection)
	_, err := target.Indexes().CreateMany(ctx, []mongo.IndexModel{
		{Keys: bson.D{{Key: "assignment_target_id", Value: 1}}, Options: options.Index().SetName("assignment_target_id_unique").SetUnique(true)},
		{Keys: bson.D{{Key: "assignment.id", Value: 1}}, Options: options.Index().SetName("assignment_id")},
		{Keys: bson.D{{Key: "user.id", Value: 1}}, Options: options.Index().SetName("guru_id")},
		{Keys: bson.D{{Key: "target.status", Value: 1}}, Options: options.Index().SetName("target_status")},
	})
	if err != nil {
		return err
	}
	_, err = validator.Indexes().CreateMany(ctx, []mongo.IndexModel{
		{Keys: bson.D{{Key: "validator_assignment_id", Value: 1}}, Options: options.Index().SetName("validator_assignment_id_unique").SetUnique(true)},
		{Keys: bson.D{{Key: "assignment.status", Value: 1}}, Options: options.Index().SetName("validator_assignment_status")},
		{Keys: bson.D{{Key: "sync.is_active", Value: 1}}, Options: options.Index().SetName("validator_sync_active")},
	})
	return err
}
