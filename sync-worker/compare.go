package main

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"reflect"

	"go.mongodb.org/mongo-driver/bson"
	"go.mongodb.org/mongo-driver/mongo"
)

func (w *MongoWriter) Compare(ctx context.Context, collection string) error {
	production := w.db.Collection(collection)
	shadow := w.db.Collection(collection + w.config.ShadowSuffix)
	productionDocs, err := loadMongoDocuments(ctx, production)
	if err != nil {
		return err
	}
	shadowDocs, err := loadMongoDocuments(ctx, shadow)
	if err != nil {
		return err
	}
	mismatch := 0
	for id, document := range productionDocs {
		other, ok := shadowDocs[id]
		if !ok || !reflect.DeepEqual(canonicalDocument(document), canonicalDocument(other)) {
			mismatch++
			if mismatch <= 20 {
				log.Printf("parity mismatch collection=%s id=%s", collection, id)
			}
		}
	}
	for id := range shadowDocs {
		if _, ok := productionDocs[id]; !ok {
			mismatch++
			if mismatch <= 20 {
				log.Printf("parity extra shadow collection=%s id=%s", collection, id)
			}
		}
	}
	log.Printf("parity collection=%s production=%d shadow=%d mismatch=%d", collection, len(productionDocs), len(shadowDocs), mismatch)
	if mismatch > 0 {
		return fmt.Errorf("parity check failed for %s: %d mismatches", collection, mismatch)
	}
	return nil
}

func loadMongoDocuments(ctx context.Context, collection *mongo.Collection) (map[string]map[string]any, error) {
	cursor, err := collection.Find(ctx, bson.D{})
	if err != nil {
		return nil, err
	}
	defer cursor.Close(ctx)
	result := map[string]map[string]any{}
	for cursor.Next(ctx) {
		var document map[string]any
		if err := cursor.Decode(&document); err != nil {
			return nil, err
		}
		if id, ok := document["_id"].(string); ok {
			result[id] = document
		}
	}
	return result, cursor.Err()
}

func canonicalDocument(document map[string]any) map[string]any {
	bytes, _ := json.Marshal(document)
	var value map[string]any
	_ = json.Unmarshal(bytes, &value)
	stripVolatile(value)
	return value
}

func stripVolatile(value any) {
	switch current := value.(type) {
	case map[string]any:
		for key := range current {
			if key == "generated_at" || key == "synced_at" || key == "captured_at" ||
				key == "engine_schema_version" || key == "engine_name" || key == "engine_version" ||
				key == "engine_hash" || key == "engine_build_time" {
				delete(current, key)
				continue
			}
			stripVolatile(current[key])
		}
	case []any:
		for _, item := range current {
			stripVolatile(item)
		}
	}
}
