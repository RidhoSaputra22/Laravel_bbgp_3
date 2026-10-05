package main

import (
	"context"
	"database/sql"
	"sort"
	"sync"
)

func (b *Builder) BuildValidators(ctx context.Context, ids []int64) ([]map[string]any, error) {
	rows, err := b.db.loadValidators(ctx, ids)
	if err != nil {
		return nil, err
	}
	if err := b.applyCombinationSnapshots(ctx, rows); err != nil {
		return nil, err
	}
	if err := b.applyAllFormsSchemas(ctx, rows); err != nil {
		return nil, err
	}
	result := make([]map[string]any, len(ids))
	workers := b.concurrency
	if workers < 1 {
		workers = 1
	}
	if workers > len(ids) {
		workers = len(ids)
	}
	jobs := make(chan int)
	var wait sync.WaitGroup
	for worker := 0; worker < workers; worker++ {
		wait.Add(1)
		go func() {
			defer wait.Done()
			for index := range jobs {
				id := ids[index]
				row, ok := rows[id]
				if !ok {
					result[index] = validatorTombstone(id)
					continue
				}
				result[index] = validatorDocument(row)
			}
		}()
	}
	for index := range ids {
		jobs <- index
	}
	close(jobs)
	wait.Wait()
	return result, nil
}

func (b *Builder) applyAllFormsSchemas(ctx context.Context, rows map[int64]ValidatorRow) error {
	assignmentIDs := map[int64]bool{}
	for _, row := range rows {
		if defaultSourceMode(row.SourceMode) != "all_forms" || !row.AssessmentSnapshots.Valid {
			continue
		}
		for _, raw := range arrayAny(row.AssessmentSnapshots.Value) {
			if source, ok := raw.(map[string]any); ok {
				if id := int64Any(source["id"]); id > 0 {
					assignmentIDs[id] = true
				}
			}
		}
	}

	ids := make([]int64, 0, len(assignmentIDs))
	for id := range assignmentIDs {
		ids = append(ids, id)
	}
	schemas, err := b.db.loadSchemas(ctx, ids)
	if err != nil {
		return err
	}

	for id, row := range rows {
		if defaultSourceMode(row.SourceMode) != "all_forms" || !row.AssessmentSnapshots.Valid {
			continue
		}
		sources := arrayAny(row.AssessmentSnapshots.Value)
		applySchemaSnapshots(sources, schemas)
		row.AssessmentSnapshots.Value = sources
		rows[id] = row
	}
	return nil
}

func applySchemaSnapshots(sources []any, schemas map[int64][]SchemaAssessment) {
	for _, raw := range sources {
		source, ok := raw.(map[string]any)
		if !ok {
			continue
		}
		if assessments := schemas[int64Any(source["id"])]; len(assessments) > 0 {
			source["assessments"] = schemaAssessmentsSnapshot(assessments)
		}
	}
}

func (b *Builder) applyCombinationSnapshots(ctx context.Context, rows map[int64]ValidatorRow) error {
	combinationIDs := map[int64]bool{}
	for _, row := range rows {
		if defaultSourceMode(row.SourceMode) != "combination" || !row.AssessmentSnapshots.Valid {
			continue
		}
		for _, raw := range arrayAny(row.AssessmentSnapshots.Value) {
			if source, ok := raw.(map[string]any); ok {
				if id := int64Any(source["combination_id"]); id > 0 {
					combinationIDs[id] = true
				}
			}
		}
	}
	ids := make([]int64, 0, len(combinationIDs))
	for id := range combinationIDs {
		ids = append(ids, id)
	}
	snapshots, err := b.db.loadCombinationSnapshots(ctx, ids)
	if err != nil {
		return err
	}
	for id, row := range rows {
		if defaultSourceMode(row.SourceMode) != "combination" || !row.AssessmentSnapshots.Valid {
			continue
		}
		sources := arrayAny(row.AssessmentSnapshots.Value)
		applyCombinationSnapshotSources(sources, snapshots)
		row.AssessmentSnapshots.Value = sources
		rows[id] = row
	}
	return nil
}

func applyCombinationSnapshotSources(sources []any, snapshots map[int64]map[string]any) {
	for _, raw := range sources {
		source, ok := raw.(map[string]any)
		if !ok {
			continue
		}
		if snapshot, found := snapshots[int64Any(source["combination_id"])]; found {
			source["assessments"] = snapshot["assessments"]
		}
	}
}

func validatorDocument(row ValidatorRow) map[string]any {
	sources := sourceSnapshots(row)
	form := validatorForm(row.Form)
	totalQuestions, requiredQuestions, scoredQuestions := 0, 0, 0
	for _, section := range row.Form.Sections {
		for _, field := range section.Fields {
			if !field.Active {
				continue
			}
			totalQuestions++
			if field.Required {
				requiredQuestions++
			}
			if field.Scored {
				scoredQuestions++
			}
		}
	}
	now := nowISO()
	return map[string]any{
		"_id": "validator-assignment:" + itoa(row.ID), "schema_version": validatorSchemaVersion,
		"validator_assignment_id": row.ID,
		"assignment": map[string]any{
			"id": row.ID, "code": nullString(row.Code), "title": nullString(row.Title), "notes": nullString(row.Notes),
			"validator_form_id": row.FormID, "source_mode": defaultSourceMode(row.SourceMode),
			"assessment_id": nullableInt(row.AssessmentID), "status": nullString(row.Status),
			"start_date": nullDate(row.StartDate), "due_date": nullDate(row.DueDate),
			"started_at": nullTime(row.StartedAt), "submitted_at": nullTime(row.SubmittedAt),
		},
		"validator": validatorSnapshot(row.ValidatorSnapshot), "validator_form": form,
		"assessment_assignments": sources,
		"meta": map[string]any{
			"snapshot_source": "validator_assignment", "source_mode": defaultSourceMode(row.SourceMode),
			"assessment_assignment_count": len(sources), "assessment_count": sourceAssessmentCount(sources),
			"total_questions": totalQuestions, "required_questions": requiredQuestions,
			"scored_questions": scoredQuestions, "scoring_scope": "validator_form_only", "generated_at": now,
		},
		"sync": map[string]any{"is_active": stringValueSQL(row.Status) != "cancelled", "source_updated_at": nullTime(row.UpdatedAt), "synced_at": now},
	}
}

func validatorTombstone(id int64) map[string]any {
	now := nowISO()
	return map[string]any{
		"_id": "validator-assignment:" + itoa(id), "schema_version": validatorSchemaVersion,
		"validator_assignment_id": id, "assessment_assignments": []any{},
		"meta": map[string]any{"snapshot_source": "tombstone", "generated_at": now},
		"sync": map[string]any{"is_active": false, "source_updated_at": now, "synced_at": now, "deleted_at": now},
	}
}

func sourceSnapshots(row ValidatorRow) []any {
	var raw []any
	if row.AssessmentSnapshots.Valid {
		raw = arrayAny(row.AssessmentSnapshots.Value)
	}
	if len(raw) == 0 && row.AssessmentSnapshot.Valid {
		raw = []any{map[string]any{
			"id": nil, "code": nil, "title": mapString(row.AssessmentSnapshot.Value, "title"),
			"target_ketenagaan_label": mapString(row.AssessmentSnapshot.Value, "target_ketenagaan"),
			"assessments":             []any{row.AssessmentSnapshot.Value}, "captured_at": mapString(row.AssessmentSnapshot.Value, "captured_at"),
		}}
	}
	result := make([]any, 0, len(raw))
	for _, value := range raw {
		if source, ok := value.(map[string]any); ok {
			result = append(result, sourceAssignment(source))
		}
	}
	return result
}

func schemaAssessmentsSnapshot(assessments []SchemaAssessment) []any {
	return arrayValue(schemaSnapshot(TargetRow{}, assessments), "assessments")
}

func arrayAny(value any) []any { result, _ := value.([]any); return result }
func int64Any(value any) int64 {
	switch number := value.(type) {
	case float64:
		return int64(number)
	case int64:
		return number
	case int:
		return int64(number)
	}
	return 0
}

func sourceAssignment(source map[string]any) map[string]any {
	assessments := make([]any, 0)
	for _, value := range arrayValue(source, "assessments") {
		if assessment, ok := value.(map[string]any); ok {
			assessments = append(assessments, sourceAssessment(assessment))
		}
	}
	return map[string]any{
		"id": source["id"], "code": source["code"], "title": source["title"], "is_active": boolValue(source, "is_active", true),
		"status_distribusi": source["status_distribusi"], "target_ketenagaan": map[string]any{"kode": source["target_ketenagaan"], "label": source["target_ketenagaan_label"]},
		"description": source["description"], "start_date": source["start_date"], "end_date": source["end_date"],
		"total_target": source["total_target"], "captured_at": source["captured_at"], "assessments": assessments,
	}
}

func sourceAssessment(value map[string]any) map[string]any {
	forms := make([]any, 0)
	for _, raw := range arrayValue(value, "forms") {
		if form, ok := raw.(map[string]any); ok {
			forms = append(forms, sourceForm(form))
		}
	}
	return map[string]any{
		"id": value["id"], "code": firstValue(value, "code", "kode_assessment"), "title": firstValue(value, "title", "judul"),
		"description": firstValue(value, "description", "deskripsi"), "instructions": firstValue(value, "instructions", "petunjuk"),
		"instrument_type": value["instrument_type"], "target_ketenagaan": value["target_ketenagaan"], "status": value["status"],
		"captured_at": value["captured_at"], "forms": forms,
	}
}

func sourceForm(value map[string]any) map[string]any {
	fields := make([]any, 0)
	for _, raw := range arrayValue(value, "fields") {
		if field, ok := raw.(map[string]any); ok {
			fields = append(fields, map[string]any{
				"id": field["id"], "label": field["label"], "description": firstValue(field, "description", "deskripsi"),
				"type": firstValue(field, "type", "tipe_field"), "options": firstValue(field, "options", "opsi_field"), "required": boolValue(field, "required", boolValue(field, "is_required", false)),
			})
		}
	}
	return map[string]any{"id": value["id"], "code": firstValue(value, "code", "kode_form"), "title": firstValue(value, "title", "judul_form"), "description": firstValue(value, "description", "deskripsi"), "fields": fields}
}

func validatorForm(row ValidatorFormRow) any {
	if row.ID == 0 {
		return nil
	}
	sections := make([]any, 0, len(row.Sections))
	for _, section := range row.Sections {
		fields := make([]any, 0, len(section.Fields))
		for _, field := range section.Fields {
			fields = append(fields, map[string]any{
				"id": field.ID, "label": nullString(field.Label), "description": nullString(field.Description), "field_type": nullString(field.Type),
				"options": validatorOptions(field.Options), "is_required": field.Required, "is_scored": field.Scored,
				"max_score": nullableFloat(field.MaxScore), "sort_order": field.SortOrder, "is_active": field.Active,
			})
		}
		sections = append(sections, map[string]any{"id": section.ID, "title": nullString(section.Title), "description": nullString(section.Description), "sort_order": section.SortOrder, "fields": fields})
	}
	return map[string]any{"id": row.ID, "code": nullString(row.Code), "title": nullString(row.Title), "description": nullString(row.Description), "instructions": nullString(row.Instructions), "status": nullString(row.Status), "is_active": row.Active, "sections": sections}
}

func validatorOptions(value JSONValue) []any {
	if !value.Valid {
		return []any{}
	}
	if items, ok := value.Value.([]any); ok {
		return items
	}
	if object, ok := value.Value.(map[string]any); ok {
		result := make([]any, 0, len(object))
		keys := make([]string, 0, len(object))
		for key := range object {
			keys = append(keys, key)
		}
		sort.Strings(keys)
		for _, key := range keys {
			result = append(result, object[key])
		}
		return result
	}
	return []any{}
}
func validatorSnapshot(value JSONValue) map[string]any {
	if !value.Valid {
		return map[string]any{"user_id": nil, "guru_id": nil, "name": nil, "email": nil, "no_ktp": nil, "role": nil, "eksternal_jabatan": nil, "jenis_jabatan": nil}
	}
	object, _ := value.Value.(map[string]any)
	return map[string]any{"user_id": object["user_id"], "guru_id": object["guru_id"], "name": object["name"], "email": object["email"], "no_ktp": object["no_ktp"], "role": object["role"], "eksternal_jabatan": object["eksternal_jabatan"], "jenis_jabatan": object["jenis_jabatan"]}
}
func sourceAssessmentCount(sources []any) int {
	count := 0
	for _, value := range sources {
		if source, ok := value.(map[string]any); ok {
			count += len(arrayValue(source, "assessments"))
		}
	}
	return count
}
func defaultSourceMode(value sql.NullString) string {
	if value.Valid && value.String != "" {
		return value.String
	}
	return "combination"
}
func nullableFloat(value sql.NullFloat64) any {
	if value.Valid {
		return value.Float64
	}
	return nil
}
func mapString(value any, key string) any {
	if object, ok := value.(map[string]any); ok {
		return object[key]
	}
	return nil
}
func firstValue(value map[string]any, keys ...string) any {
	for _, key := range keys {
		if item, ok := value[key]; ok && item != nil {
			return item
		}
	}
	return nil
}
func boolValue(value map[string]any, key string, fallback bool) bool {
	if item, ok := value[key].(bool); ok {
		return item
	}
	return fallback
}
