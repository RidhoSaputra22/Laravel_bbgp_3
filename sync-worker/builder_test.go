package main

import (
	"database/sql"
	"reflect"
	"testing"
)

func TestPHPShuffleIsDeterministic(t *testing.T) {
	first := []any{"A", "B", "C", "D", "E"}
	second := []any{"A", "B", "C", "D", "E"}
	shufflePHP(first, choiceSeed(115844, 20, 30, 40))
	shufflePHP(second, choiceSeed(115844, 20, 30, 40))
	if !reflect.DeepEqual(first, second) {
		t.Fatalf("shuffle is not deterministic: %#v != %#v", first, second)
	}
	expected := []any{"D", "C", "A", "E", "B"}
	if !reflect.DeepEqual(first, expected) {
		t.Fatalf("shuffle does not match PHP fixture: %#v != %#v", first, expected)
	}
}

func TestTargetTombstoneContract(t *testing.T) {
	document := targetTombstone(42)
	if document["_id"] != "assessment-target:42" {
		t.Fatalf("unexpected id: %#v", document["_id"])
	}
	if document["schema_version"] != targetSchemaVersion {
		t.Fatalf("unexpected schema version: %#v", document["schema_version"])
	}
	sync, ok := document["sync"].(map[string]any)
	if !ok || sync["is_active"] != false {
		t.Fatalf("unexpected sync tombstone: %#v", document["sync"])
	}
	if sync["engine_schema_version"] != syncEngineSchemaVersion || sync["engine_hash"] == "" {
		t.Fatalf("engine identity missing from target tombstone: %#v", sync)
	}
}

func TestValidatorTombstoneContract(t *testing.T) {
	document := validatorTombstone(91)
	if document["_id"] != "validator-assignment:91" {
		t.Fatalf("unexpected validator id: %#v", document["_id"])
	}
	if document["schema_version"] != validatorSchemaVersion {
		t.Fatalf("unexpected schema version: %#v", document["schema_version"])
	}
	sync, ok := document["sync"].(map[string]any)
	if !ok || sync["is_active"] != false {
		t.Fatalf("unexpected validator sync tombstone: %#v", document["sync"])
	}
	if sync["engine_schema_version"] != syncEngineSchemaVersion || sync["engine_hash"] == "" {
		t.Fatalf("engine identity missing from validator tombstone: %#v", sync)
	}
	if assignments, ok := document["assessment_assignments"].([]any); !ok || len(assignments) != 0 {
		t.Fatalf("unexpected validator tombstone assignments: %#v", document["assessment_assignments"])
	}
}

func TestTargetSnapshotPrefersLatestCombinationOverAttempt(t *testing.T) {
	builder := &Builder{}
	row := TargetRow{
		ID: 42,
		Attempt: JSONValue{Valid: true, Value: map[string]any{
			"assessments": []any{map[string]any{
				"forms": []any{map[string]any{
					"fields": []any{map[string]any{"id": int64(2160), "label": "label lama"}},
				}},
			}},
		}},
		Combination: CombinationRow{Snapshot: JSONValue{Valid: true, Value: map[string]any{
			"assessments": []any{map[string]any{
				"forms": []any{map[string]any{
					"fields": []any{map[string]any{"id": int64(2160), "label": "label terbaru"}},
				}},
			}},
		}}},
	}

	snapshot, source := builder.targetSnapshot(row, nil, nil)
	if source != "combination" {
		t.Fatalf("expected combination source, got %q", source)
	}
	assessment := snapshot["assessments"].([]any)[0].(map[string]any)
	form := assessment["forms"].([]any)[0].(map[string]any)
	field := form["fields"].([]any)[0].(map[string]any)
	if field["label"] != "label terbaru" {
		t.Fatalf("expected latest field metadata, got %#v", field["label"])
	}
}

func TestAllFormsUsesCurrentAssessmentSchema(t *testing.T) {
	sources := []any{map[string]any{
		"id": int64(50),
		"assessments": []any{map[string]any{
			"forms": []any{map[string]any{
				"fields": []any{map[string]any{"id": int64(2155), "label": "label lama"}},
			}},
		}},
	}}
	schemas := map[int64][]SchemaAssessment{
		50: {{
			ID:     20,
			Active: true,
			Forms: []SchemaForm{{
				ID: 30,
				Fields: []SchemaField{{
					ID:     2155,
					Label:  sql.NullString{String: "label terbaru", Valid: true},
					Active: true,
				}},
			}},
		}},
	}

	applySchemaSnapshots(sources, schemas)
	assessment := sources[0].(map[string]any)["assessments"].([]any)[0].(map[string]any)
	form := assessment["forms"].([]any)[0].(map[string]any)
	field := form["fields"].([]any)[0].(map[string]any)
	if field["label"] != "label terbaru" {
		t.Fatalf("expected current field label, got %#v", field["label"])
	}
}

func TestCombinationUsesCurrentCombinationSnapshot(t *testing.T) {
	sources := []any{map[string]any{
		"combination_id": int64(77),
		"assessments": []any{map[string]any{
			"forms": []any{map[string]any{
				"fields": []any{map[string]any{"id": int64(2155), "label": "label lama"}},
			}},
		}},
	}}
	snapshots := map[int64]map[string]any{
		77: {"assessments": []any{map[string]any{
			"forms": []any{map[string]any{
				"fields": []any{map[string]any{"id": int64(2155), "label": "label terbaru"}},
			}},
		}}},
	}

	applyCombinationSnapshotSources(sources, snapshots)
	assessment := sources[0].(map[string]any)["assessments"].([]any)[0].(map[string]any)
	form := assessment["forms"].([]any)[0].(map[string]any)
	field := form["fields"].([]any)[0].(map[string]any)
	if field["label"] != "label terbaru" {
		t.Fatalf("expected current combination field label, got %#v", field["label"])
	}
}
