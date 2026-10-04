package main

import (
	"context"
	"database/sql"
	"reflect"
	"testing"
	"time"
)

func TestJSONValueScanCases(t *testing.T) {
	tests := []struct {
		name      string
		input     any
		wantValid bool
		wantError bool
	}{
		{name: "nil", input: nil},
		{name: "json bytes", input: []byte(`{"label":"baru"}`), wantValid: true},
		{name: "json string", input: `{"label":"baru"}`, wantValid: true},
		{name: "null", input: "null"},
		{name: "empty", input: ""},
		{name: "invalid json", input: "{", wantError: true},
		{name: "unsupported type", input: 10, wantError: true},
	}

	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			var value JSONValue
			err := value.Scan(test.input)
			if (err != nil) != test.wantError {
				t.Fatalf("Scan error = %v, wantError=%t", err, test.wantError)
			}
			if test.wantError {
				return
			}
			if value.Valid != test.wantValid {
				t.Fatalf("Valid = %t, want %t", value.Valid, test.wantValid)
			}
		})
	}
}

func TestCloneMapIsDeepCopy(t *testing.T) {
	original := map[string]any{
		"nested": map[string]any{"label": "lama"},
		"items":  []any{map[string]any{"value": "A"}},
	}
	copy := cloneMap(original)
	copy["nested"].(map[string]any)["label"] = "baru"
	copy["items"].([]any)[0].(map[string]any)["value"] = "B"

	if original["nested"].(map[string]any)["label"] != "lama" {
		t.Fatal("nested map was not cloned")
	}
	if original["items"].([]any)[0].(map[string]any)["value"] != "A" {
		t.Fatal("nested slice was not cloned")
	}
}

func TestDatabaseAndCollectionHelpers(t *testing.T) {
	placeholders, args := inClause([]int64{4, 9})
	if placeholders != "?,?" || !reflect.DeepEqual(args, []any{int64(4), int64(9)}) {
		t.Fatalf("unexpected IN clause: %q %#v", placeholders, args)
	}
	placeholders, args = inClause(nil)
	if placeholders != "" || len(args) != 0 {
		t.Fatalf("empty IN clause: %q %#v", placeholders, args)
	}

	if got := uniqueStrings([]string{"Z", "a", "A", "z", "B"}); !reflect.DeepEqual(got, []string{"B", "Z", "a"}) {
		t.Fatalf("unexpected unique strings: %#v", got)
	}
	if got := lookupOptions([]string{"B", "A"}); !reflect.DeepEqual(got, []any{
		map[string]any{"label": "B", "value": "B"},
		map[string]any{"label": "A", "value": "A"},
	}) {
		t.Fatalf("unexpected lookup options: %#v", got)
	}
}

func TestConfigValidationAndDSNDefaults(t *testing.T) {
	for _, key := range []string{
		"SYNC_MYSQL_DSN", "DB_HOST", "DB_PORT", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD",
		"SYNC_BATCH_SIZE", "SYNC_BUILD_CONCURRENCY", "SYNC_MEMORY_LIMIT_MB", "SYNC_MAX_ATTEMPTS",
	} {
		t.Setenv(key, "")
	}
	if _, err := loadConfig(); err == nil {
		t.Fatal("expected missing MySQL configuration error")
	}

	t.Setenv("SYNC_MYSQL_DSN", "user:pass@tcp(localhost:3306)/database")
	t.Setenv("SYNC_BATCH_SIZE", "25")
	t.Setenv("SYNC_BUILD_CONCURRENCY", "3")
	t.Setenv("SYNC_MEMORY_LIMIT_MB", "2")
	t.Setenv("SYNC_MAX_ATTEMPTS", "4")
	config, err := loadConfig()
	if err != nil {
		t.Fatal(err)
	}
	if config.BatchSize != 25 || config.BuildConcurrency != 3 || config.MemoryLimitBytes != 2*1024*1024 || config.MaxAttempts != 4 {
		t.Fatalf("unexpected config: %+v", config)
	}
	if config.MySQLDSN != "user:pass@tcp(localhost:3306)/database?time_zone=%27%2B00%3A00%27" {
		t.Fatalf("unexpected UTC DSN: %q", config.MySQLDSN)
	}
}

func TestStageConfigDefaultsAndOverrides(t *testing.T) {
	defaults := stageConfig("studi_kasus", 1, nil)
	if defaults["enabled"] != true || defaults["entry_mode"] != "start_button" || defaults["finalize_mode"] != "auto" {
		t.Fatalf("unexpected study-case defaults: %#v", defaults)
	}

	configured := stageConfig("pilihan_ganda_kompleks", 2, map[string]any{
		"time_limit_minutes": 30,
		"security":           map[string]any{"enabled": false},
	})
	if configured["time_limit_minutes"] != 30 {
		t.Fatalf("time limit override was lost: %#v", configured)
	}
	security := configured["security"].(map[string]any)
	if security["enabled"] != false || security["require_fullscreen"] != true {
		t.Fatalf("nested config merge is wrong: %#v", security)
	}
}

func TestNormalizeAndMergeOptions(t *testing.T) {
	options := normalizeOptions([]any{
		"B",
		map[string]any{"label": "A", "value": "a"},
		map[string]any{"label": "Tanpa value"},
	})
	if got := []string{
		stringValue(options[0].(map[string]any), "value"),
		stringValue(options[1].(map[string]any), "value"),
		stringValue(options[2].(map[string]any), "value"),
	}; !reflect.DeepEqual(got, []string{"B", "Tanpa value", "a"}) {
		t.Fatalf("unexpected normalized options: %#v", options)
	}

	lookup := []any{
		map[string]any{"label": "Alpha", "value": "alpha"},
		map[string]any{"label": "Beta", "value": "beta"},
	}
	stored := []any{
		map[string]any{"label": "ALPHA", "value": "ALPHA", "score": 3.0, "level_kompetensi": "tinggi"},
	}
	merged := mergeLookupOptions(lookup, stored)
	if merged[0].(map[string]any)["score"] != 3.0 || merged[0].(map[string]any)["level_kompetensi"] != "tinggi" {
		t.Fatalf("stored scoring metadata was not preserved: %#v", merged)
	}
	if merged[1].(map[string]any)["score"] != nil {
		t.Fatalf("unmatched option unexpectedly received metadata: %#v", merged)
	}

	levels := normalizeOptions([]any{
		map[string]any{"label": "A", "value": "a", "level_kompetensi": 1},
		map[string]any{"label": "E", "value": "e", "level_kompetensi": "5"},
	})
	if levels[0].(map[string]any)["score"] != 1.0 || levels[1].(map[string]any)["score"] != 5.0 {
		t.Fatalf("competency levels were not used as scores: %#v", levels)
	}
}

func TestSchemaSnapshotFiltersInactiveAndCountsRequired(t *testing.T) {
	row := TargetRow{Assignment: AssignmentRow{ID: 7}}
	snapshot := schemaSnapshot(row, []SchemaAssessment{
		{
			ID:         10,
			Active:     true,
			Instrument: sql.NullString{String: "portofolio", Valid: true},
			Forms: []SchemaForm{{
				ID: 20,
				Fields: []SchemaField{
					{ID: 30, Active: true, Required: true, Label: sql.NullString{String: "Aktif", Valid: true}},
					{ID: 31, Active: false, Required: true, Label: sql.NullString{String: "Mati", Valid: true}},
				},
			}},
		},
		{ID: 11, Active: false, Forms: []SchemaForm{{ID: 21, Fields: []SchemaField{{ID: 32, Active: true}}}}},
	})

	if len(arrayValue(snapshot, "assessments")) != 1 {
		t.Fatalf("inactive assessment was not filtered: %#v", snapshot)
	}
	assessment := arrayValue(snapshot, "assessments")[0].(map[string]any)
	form := arrayValue(assessment, "forms")[0].(map[string]any)
	if len(arrayValue(form, "fields")) != 1 {
		t.Fatalf("inactive field was not filtered: %#v", form)
	}
	meta := mapValue(snapshot, "meta")
	if meta["total_questions"] != 1 || meta["required_questions"] != 1 {
		t.Fatalf("question counters are wrong: %#v", meta)
	}
}

func TestAutofillInfersIdentityValues(t *testing.T) {
	forms := []any{map[string]any{
		"instrument_type": "portofolio",
		"assessments": []any{map[string]any{
			"instrument_type": "portofolio",
			"forms": []any{map[string]any{
				"kode_form": "IDENTITAS",
				"fields": []any{
					map[string]any{"label": "Nama Lengkap", "tipe_field": "text"},
					map[string]any{
						"label":      "Kabupaten/Kota",
						"tipe_field": "select",
						"opsi_field": []any{map[string]any{"label": "Gowa", "value": "Gowa"}},
					},
				},
			}},
		}},
	}}
	guru := GuruRow{
		Nama:      sql.NullString{String: "Guru Contoh", Valid: true},
		Kabupaten: sql.NullString{String: "Gowa", Valid: true},
	}
	applyAutofill(forms, guru)

	fields := arrayValue(arrayValue(arrayValue(forms[0].(map[string]any), "assessments")[0].(map[string]any), "forms")[0].(map[string]any), "fields")
	if fields[0].(map[string]any)["default_value"] != "Guru Contoh" {
		t.Fatalf("name autofill is wrong: %#v", fields[0].(map[string]any)["default_value"])
	}
	if fields[1].(map[string]any)["default_value"] != "Gowa" {
		t.Fatalf("city autofill is wrong: %#v", fields[1].(map[string]any)["default_value"])
	}
}

func TestAutofillMatchesPHPResolverForChoiceAndValidationFields(t *testing.T) {
	forms := []any{map[string]any{
		"instrument_type": "portofolio",
		"assessments": []any{map[string]any{
			"forms": []any{map[string]any{
				"kode_form": "FORM-IDENTITAS",
				"fields": []any{
					map[string]any{"label": "Nama Peserta", "tipe_field": "text"},
					map[string]any{
						"autofill_source": "jabatan",
						"tipe_field":      "checkbox",
						"opsi_field": []any{
							map[string]any{"label": "Guru", "value": "guru"},
							map[string]any{"label": "Kepala Sekolah", "value": "kepala"},
						},
					},
					map[string]any{
						"autofill_source": "eksternal_jabatan",
						"tipe_field":      "select",
						"validasi":        map[string]any{"allow_other_input": true},
						"opsi_field":      []any{map[string]any{"label": "Guru", "value": "guru"}},
					},
					map[string]any{"autofill_source": "email", "tipe_field": "email"},
					map[string]any{"autofill_source": "no_hp", "tipe_field": "number"},
				},
			}},
		}},
	}}
	guru := GuruRow{
		Nama:             sql.NullString{String: "Guru Contoh", Valid: true},
		Jabatan:          sql.NullString{String: "Guru; Kepala Sekolah", Valid: true},
		EksternalJabatan: sql.NullString{String: "Pengawas Madrasah", Valid: true},
		Email:            sql.NullString{String: "bukan-email", Valid: true},
		NoHP:             sql.NullString{String: "bukan-angka", Valid: true},
	}
	applyAutofill(forms, guru)

	fields := arrayValue(arrayValue(arrayValue(forms[0].(map[string]any), "assessments")[0].(map[string]any), "forms")[0].(map[string]any), "fields")
	if fields[0].(map[string]any)["default_value"] != "Guru Contoh" {
		t.Fatalf("inferred text autofill is wrong: %#v", fields[0])
	}
	if got := fields[1].(map[string]any)["default_value"]; !reflect.DeepEqual(got, []string{"guru", "kepala"}) {
		t.Fatalf("checkbox autofill is wrong: %#v", got)
	}
	if fields[2].(map[string]any)["default_value"] != "Pengawas Madrasah" {
		t.Fatalf("select other autofill is wrong: %#v", fields[2])
	}
	if fields[3].(map[string]any)["default_value"] != nil || fields[4].(map[string]any)["default_value"] != nil {
		t.Fatalf("invalid typed autofill should be empty: %#v", fields[3:5])
	}
}

func TestNormalizeScoringAndGroupForms(t *testing.T) {
	snapshot := map[string]any{
		"assessments": []any{
			map[string]any{"instrument_type": "unknown", "forms": []any{}},
			map[string]any{"instrument_type": "portofolio", "forms": []any{}, "scoring_config": map[string]any{"advanced_rules_text": `{"min":1}`}},
			map[string]any{"instrument_type": "pilihan_ganda_kompleks", "forms": []any{}},
		},
	}
	normalizeScoring(snapshot)
	portfolio := arrayValue(snapshot, "assessments")[1].(map[string]any)
	if _, ok := portfolio["scoring_config"].(map[string]any)["advanced_rules_text"].(map[string]any); !ok {
		t.Fatalf("advanced scoring rule was not decoded: %#v", portfolio)
	}

	groups := groupedForms(snapshot)
	if stringValue(groups[0].(map[string]any), "instrument_type") != "portofolio" || stringValue(groups[1].(map[string]any), "instrument_type") != "pilihan_ganda_kompleks" {
		t.Fatalf("forms were not grouped in instrument order: %#v", groups)
	}
}

func TestTargetSnapshotFallbackOrder(t *testing.T) {
	row := TargetRow{
		ID: 42,
		Attempt: JSONValue{Valid: true, Value: map[string]any{
			"assessments": []any{map[string]any{"forms": []any{map[string]any{"fields": []any{map[string]any{"label": "attempt"}}}}}},
		}},
	}
	builder := &Builder{}
	if snapshot, source := builder.targetSnapshot(row, nil, nil); source != "attempt" || stringValue(arrayValue(arrayValue(arrayValue(snapshot, "assessments")[0].(map[string]any), "forms")[0].(map[string]any), "fields")[0].(map[string]any), "label") != "attempt" {
		t.Fatalf("attempt fallback failed: source=%q snapshot=%#v", source, snapshot)
	}

	row.Attempt = JSONValue{}
	assignment, source := builder.targetSnapshot(row, []SchemaAssessment{{
		ID: 1, Active: true, Forms: []SchemaForm{{ID: 2, Fields: []SchemaField{{ID: 3, Active: true, Label: sql.NullString{String: "assignment", Valid: true}}}}},
	}}, nil)
	if source != "assignment" || stringValue(arrayValue(arrayValue(arrayValue(assignment, "assessments")[0].(map[string]any), "forms")[0].(map[string]any), "fields")[0].(map[string]any), "label") != "assignment" {
		t.Fatalf("assignment fallback failed: source=%q snapshot=%#v", source, assignment)
	}
}

func TestChoiceRandomizationAndAutofillDoNotAffectOtherFields(t *testing.T) {
	snapshot := map[string]any{"assessments": []any{
		map[string]any{
			"id":              int64(1),
			"instrument_type": "pilihan_ganda_kompleks",
			"forms": []any{map[string]any{"id": int64(2), "fields": []any{
				map[string]any{"id": int64(3), "tipe_field": "radio", "opsi_field": []any{"A", "B", "C"}},
				map[string]any{"id": int64(4), "tipe_field": "text", "label": "Tetap"},
			}}},
		},
	}}
	randomizeSnapshot(snapshot, 42)
	fields := arrayValue(arrayValue(arrayValue(snapshot, "assessments")[0].(map[string]any), "forms")[0].(map[string]any), "fields")
	if stringValue(fields[1].(map[string]any), "label") != "Tetap" {
		t.Fatal("non-radio field was unexpectedly changed")
	}
	if len(fields[0].(map[string]any)["opsi_field"].([]any)) != 3 {
		t.Fatal("radio options were lost")
	}
}

func TestValidatorDocumentAndFallbacks(t *testing.T) {
	row := ValidatorRow{
		ID:         9,
		FormID:     10,
		SourceMode: sql.NullString{String: "all_forms", Valid: true},
		Status:     sql.NullString{String: "assigned", Valid: true},
		AssessmentSnapshots: JSONValue{Valid: true, Value: []any{map[string]any{
			"id": int64(50),
			"assessments": []any{map[string]any{
				"id": int64(20), "forms": []any{map[string]any{"id": int64(30), "fields": []any{map[string]any{"id": int64(40), "label": "Soal"}}}},
			}},
		}}},
		Form: ValidatorFormRow{ID: 10, Sections: []ValidatorSectionRow{{
			ID:     11,
			Fields: []ValidatorFieldRow{{ID: 12, Active: true, Required: true, Scored: true, Type: sql.NullString{String: "likert", Valid: true}, Options: JSONValue{Valid: true, Value: []any{"1", "2"}}}},
		}}},
	}
	document := validatorDocument(row)
	meta := document["meta"].(map[string]any)
	if meta["assessment_assignment_count"] != 1 || meta["assessment_count"] != 1 || meta["total_questions"] != 1 || meta["required_questions"] != 1 || meta["scored_questions"] != 1 {
		t.Fatalf("validator counters are wrong: %#v", meta)
	}
	if defaultSourceMode(sql.NullString{}) != "combination" {
		t.Fatal("empty validator source mode should default to combination")
	}

	legacy := sourceSnapshots(ValidatorRow{AssessmentSnapshot: JSONValue{Valid: true, Value: map[string]any{"id": int64(20), "title": "Legacy"}}})
	if len(legacy) != 1 || len(arrayValue(legacy[0].(map[string]any), "assessments")) != 1 {
		t.Fatalf("legacy snapshot fallback failed: %#v", legacy)
	}
	if got := validatorOptions(JSONValue{Valid: true, Value: map[string]any{"b": "B", "a": "A"}}); len(got) != 2 || got[0] != "A" || got[1] != "B" {
		t.Fatalf("validator object options were not sorted: %#v", got)
	}
}

func TestCanonicalDocumentRemovesVolatileValues(t *testing.T) {
	canonical := canonicalDocument(map[string]any{
		"_id":          "x",
		"generated_at": "now",
		"nested":       map[string]any{"synced_at": "now", "value": 1},
		"items":        []any{map[string]any{"captured_at": "now", "value": 2}},
	})
	if _, ok := canonical["generated_at"]; ok {
		t.Fatal("top-level volatile field remains")
	}
	if _, ok := canonical["nested"].(map[string]any)["synced_at"]; ok {
		t.Fatal("nested volatile field remains")
	}
	if _, ok := canonical["items"].([]any)[0].(map[string]any)["captured_at"]; ok {
		t.Fatal("array volatile field remains")
	}
}

func TestEngineMetadataIsEmbeddedAndIgnoredByParityComparison(t *testing.T) {
	syncState := withEngineMetadata(map[string]any{"is_active": true})
	if syncState["engine_schema_version"] != syncEngineSchemaVersion ||
		syncState["engine_name"] != "bbpg-sync-worker" {
		t.Fatalf("engine metadata is incomplete: %#v", syncState)
	}
	if syncState["engine_version"] == "" || syncState["engine_hash"] == "" || syncState["engine_build_time"] == "" {
		t.Fatalf("engine build identity is incomplete: %#v", syncState)
	}

	canonical := canonicalDocument(map[string]any{"sync": syncState})
	if _, ok := canonical["sync"].(map[string]any)["engine_hash"]; ok {
		t.Fatal("engine metadata should not affect production/shadow parity")
	}
}

func TestIndexDocumentsSkipsMalformedIDs(t *testing.T) {
	documents := []map[string]any{
		{"_id": "assessment-target:10"},
		{"_id": "assessment-target:not-a-number"},
		{"_id": "wrong-prefix:20"},
		{"_id": 30},
	}
	indexed := indexDocuments(documents, "assessment-target:")
	if len(indexed) != 1 || indexed[10]["_id"] != "assessment-target:10" {
		t.Fatalf("malformed document IDs were indexed: %#v", indexed)
	}
}

func TestMongoWriterValidationAndNoop(t *testing.T) {
	writer := &MongoWriter{}
	if err := writer.Upsert(context.Background(), "unused", nil); err != nil {
		t.Fatalf("empty upsert should be a no-op: %v", err)
	}
	if err := writer.Upsert(context.Background(), "unused", []map[string]any{{"_id": 123}}); err == nil {
		t.Fatal("non-string MongoDB _id should be rejected before writing")
	}
	if err := writer.Upsert(context.Background(), "unused", []map[string]any{{"value": "missing id"}}); err == nil {
		t.Fatal("missing MongoDB _id should be rejected before writing")
	}
}

func TestNullAndDateHelpers(t *testing.T) {
	date := time.Date(2026, time.January, 2, 3, 4, 5, 0, time.FixedZone("WITA", 8*60*60))
	if nullString(sql.NullString{String: "x", Valid: true}) != "x" || nullString(sql.NullString{}) != nil {
		t.Fatal("null string conversion failed")
	}
	if nullDate(sql.NullTime{Time: date, Valid: true}) != "2026-01-01" {
		t.Fatalf("date should be converted to UTC: %#v", nullDate(sql.NullTime{Time: date, Valid: true}))
	}
	if nullTime(sql.NullTime{Time: date, Valid: true}) != "2026-01-01T19:04:05.000Z" {
		t.Fatalf("time should be converted to UTC: %#v", nullTime(sql.NullTime{Time: date, Valid: true}))
	}
}
