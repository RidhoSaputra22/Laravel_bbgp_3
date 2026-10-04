package main

import (
	"context"
	"database/sql"
	"encoding/json"
	"hash/crc32"
	"math"
	"net/mail"
	"sort"
	"strconv"
	"strings"
	"sync"
	"time"
	"unicode"
	"unicode/utf8"

	"golang.org/x/text/transform"
	"golang.org/x/text/unicode/norm"
)

const targetSchemaVersion = "assessment_assignment-target-v1"
const validatorSchemaVersion = "validator_assignment-v1"

var instrumentOrder = map[string]int{
	"portofolio":                  1,
	"studi_kasus":                 2,
	"pilihan_ganda_kompleks":      3,
	"monitoring_observasi_eviden": 4,
	"skala_likert":                5,
}

type Builder struct {
	db          *Database
	concurrency int
}

func (b *Builder) BuildTargets(ctx context.Context, ids []int64) ([]map[string]any, error) {
	rows, err := b.db.loadTargets(ctx, ids)
	if err != nil {
		return nil, err
	}
	assignmentIDs := make([]int64, 0)
	seen := map[int64]bool{}
	for _, row := range rows {
		if !validSnapshotAny(row.Combination.Snapshot.Value) && !seen[row.AssignmentID] {
			assignmentIDs, seen[row.AssignmentID] = append(assignmentIDs, row.AssignmentID), true
		}
	}
	schemas, err := b.db.loadSchemas(ctx, assignmentIDs)
	if err != nil {
		return nil, err
	}
	lookupSources := map[string]bool{}
	for assignmentID := range schemas {
		for assessmentIndex := range schemas[assignmentID] {
			for formIndex := range schemas[assignmentID][assessmentIndex].Forms {
				for fieldIndex := range schemas[assignmentID][assessmentIndex].Forms[formIndex].Fields {
					source := stringValueSQL(schemas[assignmentID][assessmentIndex].Forms[formIndex].Fields[fieldIndex].Lookup)
					if source != "" {
						lookupSources[source] = true
					}
				}
			}
		}
	}
	if len(lookupSources) > 0 {
		lookups, lookupErr := b.db.loadLookupOptions(ctx, lookupSources)
		if lookupErr != nil {
			return nil, lookupErr
		}
		for assignmentID := range schemas {
			for assessmentIndex := range schemas[assignmentID] {
				for formIndex := range schemas[assignmentID][assessmentIndex].Forms {
					for fieldIndex := range schemas[assignmentID][assessmentIndex].Forms[formIndex].Fields {
						field := &schemas[assignmentID][assessmentIndex].Forms[formIndex].Fields[fieldIndex]
						if options, ok := lookups[stringValueSQL(field.Lookup)]; ok && len(options) > 0 {
							field.Options.Value, field.Options.Valid = mergeLookupOptions(options, normalizeOptions(jsonAny(field.Options))), true
						}
					}
				}
			}
		}
	}
	allAssignmentIDs := make([]int64, 0, len(rows))
	allSeen := map[int64]bool{}
	for _, row := range rows {
		if !allSeen[row.AssignmentID] {
			allAssignmentIDs = append(allAssignmentIDs, row.AssignmentID)
			allSeen[row.AssignmentID] = true
		}
	}
	stageConfigs, err := b.db.loadStageConfigs(ctx, allAssignmentIDs)
	if err != nil {
		return nil, err
	}

	documents := make([]map[string]any, len(ids))
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
					documents[index] = targetTombstone(id)
					continue
				}
				snapshot, source := b.targetSnapshot(row, schemas[row.AssignmentID], stageConfigs[row.AssignmentID])
				documents[index] = b.targetDocument(row, snapshot, source)
			}
		}()
	}
	for index := range ids {
		jobs <- index
	}
	close(jobs)
	wait.Wait()
	return documents, nil
}

func (b *Builder) targetSnapshot(row TargetRow, schemas []SchemaAssessment, stageConfigs map[int64]map[string]any) (map[string]any, string) {
	if snapshot, ok := mapAny(row.Combination.Snapshot.Value); ok && validSnapshot(snapshot) {
		snapshot = cloneMap(snapshot)
		applyStageConfigs(snapshot, stageConfigs)
		normalizeSnapshotChoiceOptions(snapshot)
		randomizeSnapshot(snapshot, row.ID)
		normalizeScoring(snapshot)
		return snapshot, "combination"
	}
	snapshot := schemaSnapshot(row, schemas)
	if validSnapshot(snapshot) {
		randomizeSnapshot(snapshot, row.ID)
		return snapshot, "assignment"
	}
	if snapshot, ok := mapAny(row.Attempt.Value); ok && validSnapshot(snapshot) {
		snapshot = cloneMap(snapshot)
		normalizeSnapshotChoiceOptions(snapshot)
		normalizeScoring(snapshot)
		return snapshot, "attempt"
	}
	return snapshot, "assignment"
}

func applyStageConfigs(snapshot map[string]any, configured map[int64]map[string]any) {
	for index, raw := range arrayValue(snapshot, "assessments") {
		assessment, ok := raw.(map[string]any)
		if !ok {
			continue
		}
		assessment["stage_config"] = stageConfig(stringValue(assessment, "instrument_type"), index, configured[int64Value(assessment, "id")])
	}
}

func (b *Builder) targetDocument(row TargetRow, snapshot map[string]any, source string) map[string]any {
	forms := groupedForms(snapshot)
	applyAutofill(forms, row.Guru)
	meta := mapValue(snapshot, "meta")
	if meta == nil {
		meta = map[string]any{}
	}
	meta["snapshot_source"] = source
	meta["generated_at"] = nowISO()
	return map[string]any{
		"_id":                  "assessment-target:" + itoa(row.ID),
		"schema_version":       targetSchemaVersion,
		"assignment_target_id": row.ID,
		"assignment": map[string]any{
			"id":                row.Assignment.ID,
			"kode_penugasan":    nullString(row.Assignment.Code),
			"judul_penugasan":   nullString(row.Assignment.Title),
			"deskripsi":         nullString(row.Assignment.Description),
			"is_active":         row.Assignment.IsActive,
			"status_distribusi": nullString(row.Assignment.Distribution),
			"ketenagaan": map[string]any{
				"kode":  nullString(row.Assignment.TargetKetenagaan),
				"label": ketenagaanLabel(row.Assignment.TargetKetenagaan),
			},
			"tanggal_mulai":   nullDate(row.Assignment.StartDate),
			"tanggal_selesai": nullDate(row.Assignment.EndDate),
		},
		"user":        targetUser(row),
		"target":      targetState(row),
		"combination": targetCombination(row),
		"forms":       forms,
		"meta":        meta,
		"sync": withEngineMetadata(map[string]any{
			"is_active":         true,
			"source_updated_at": nullTime(row.UpdatedAt),
			"synced_at":         nowISO(),
		}),
	}
}

func targetTombstone(id int64) map[string]any {
	now := nowISO()
	return map[string]any{
		"_id":                  "assessment-target:" + itoa(id),
		"schema_version":       targetSchemaVersion,
		"assignment_target_id": id,
		"target":               map[string]any{"id": id, "status": "dibatalkan"},
		"forms":                []any{},
		"meta":                 map[string]any{"snapshot_source": "tombstone", "generated_at": now},
		"sync":                 withEngineMetadata(map[string]any{"is_active": false, "source_updated_at": now, "synced_at": now, "deleted_at": now}),
	}
}

func targetState(row TargetRow) map[string]any {
	return map[string]any{
		"id":              row.ID,
		"status":          nullString(row.Status),
		"session_id":      nullableInt(row.SessionID),
		"assigned_at":     nullTime(row.AssignedAt),
		"started_at":      nullTime(row.StartedAt),
		"deadline_at":     nullTime(row.DeadlineAt),
		"submitted_at":    nullTime(row.SubmittedAt),
		"completion_mode": nullString(row.CompletionMode),
		"timed_out_at":    nullTime(row.TimedOutAt),
	}
}

func targetUser(row TargetRow) any {
	if row.Guru.ID == 0 {
		return nil
	}
	role := nullString(row.Guru.EksternalJabatan)
	if role == nil {
		role = ketenagaanLabel(row.Assignment.TargetKetenagaan)
	}
	job := nullString(row.Guru.JenisJabatan)
	if job == nil {
		job = nullString(row.Guru.Jabatan)
	}
	return map[string]any{
		"id": row.Guru.ID, "nama": nullString(row.Guru.Nama), "nik": nullString(row.Guru.NIK),
		"nip": nullString(row.Guru.NIP), "nuptk": nullString(row.Guru.NUPTK), "email": nullString(row.Guru.Email),
		"role": role, "jabatan": job, "kabupaten": nullString(row.Guru.Kabupaten),
		"satuan_pendidikan": nullString(row.Guru.SatuanPendidikan),
	}
}

func targetCombination(row TargetRow) any {
	if !row.Combination.ID.Valid {
		return nil
	}
	return map[string]any{
		"id": row.Combination.ID.Int64, "kode_kombinasi": nullString(row.Combination.Code),
		"judul": nullString(row.Combination.Title), "target_ketenagaan": nullString(row.Combination.TargetKetenagaan),
	}
}

func schemaSnapshot(row TargetRow, assessments []SchemaAssessment) map[string]any {
	result := make([]any, 0, len(assessments))
	allFields := 0
	requiredFields := 0
	stageIndex := 0
	for _, assessment := range assessments {
		if !assessment.Active {
			continue
		}
		assessmentMap := map[string]any{
			"id": assessment.ID, "stage_index": stageIndex,
			"stage_config":    stageConfig(stringValueSQL(assessment.Instrument), stageIndex, jsonObject(assessment.StageConfig)),
			"kode_assessment": nullString(assessment.Code), "judul": nullString(assessment.Title),
			"deskripsi": nullString(assessment.Description), "petunjuk": nullString(assessment.Instructions),
			"instrument_type":  nullString(assessment.Instrument),
			"instrument_label": instrumentLabel(nullString(assessment.Instrument)),
			"scoring_config":   jsonObject(assessment.Scoring), "forms": []any{},
		}
		forms := make([]any, 0, len(assessment.Forms))
		for _, form := range assessment.Forms {
			if len(form.Fields) == 0 {
				continue
			}
			fields := make([]any, 0, len(form.Fields))
			for _, field := range form.Fields {
				if !field.Active {
					continue
				}
				allFields++
				if field.Required {
					requiredFields++
				}
				fields = append(fields, map[string]any{
					"id": field.ID, "assessment_id": field.AssessmentID, "assessment_form_id": field.FormID,
					"label": nullString(field.Label), "deskripsi": nullString(field.Description),
					"nama_field": nullString(field.Name), "tipe_field": nullString(field.Type),
					"placeholder": nullString(field.Placeholder), "bantuan": nullString(field.Help),
					"autofill_source": nullString(field.Autofill), "lookup_source": nullString(field.Lookup),
					"dependency_config": jsonObject(field.Dependency), "opsi_field": normalizeOptions(jsonAny(field.Options)),
					"validasi": jsonObject(field.Validation), "scoring_config": jsonObject(field.Scoring),
					"is_required": field.Required,
				})
			}
			if len(fields) == 0 {
				continue
			}
			forms = append(forms, map[string]any{
				"id": form.ID, "assessment_id": form.AssessmentID, "judul_form": nullString(form.Title),
				"kode_form": nullString(form.Code), "deskripsi": nullString(form.Description),
				"kompetensi": nullString(form.Competency), "indikator_kode": nullString(form.IndicatorCode),
				"kompetensi_label": kompetensiLabel(nullString(form.Competency)),
				"indikator_label":  nullString(form.IndicatorLabel), "is_scoreable": form.Scoreable,
				"scoring_config": jsonObject(form.Scoring), "fields": fields,
			})
		}
		if len(forms) == 0 {
			continue
		}
		assessmentMap["forms"] = forms
		result = append(result, assessmentMap)
		stageIndex++
	}
	return map[string]any{
		"generated_at": nowISO(), "assignment": map[string]any{"id": row.Assignment.ID, "kode_penugasan": nullString(row.Assignment.Code), "judul_penugasan": nullString(row.Assignment.Title)},
		"assessments": result,
		"meta":        map[string]any{"total_questions": allFields, "required_questions": requiredFields, "randomization": map[string]any{"version": 2, "question_order": "fixed", "choice_order": "radio_options_for_pilihan_ganda_kompleks"}},
	}
}

func stageConfig(instrument string, index int, configured map[string]any) map[string]any {
	enabled, entry, draft, finalize := false, "direct", false, "manual"
	var timeLimit any
	securityEnabled, fullscreen := false, false
	switch instrument {
	case "portofolio":
		enabled, draft = true, true
	case "studi_kasus":
		enabled, entry, finalize, securityEnabled, fullscreen = true, "start_button", "auto", true, true
	case "pilihan_ganda_kompleks":
		enabled, finalize, timeLimit, securityEnabled, fullscreen = true, "auto", 90, true, true
	case "skala_likert":
		enabled, finalize = true, "auto"
	default:
		enabled = index < 3
	}
	defaults := map[string]any{
		"enabled": enabled, "entry_mode": entry, "allow_draft": draft, "finalize_mode": finalize,
		"admin_gate_enabled": index > 0, "lock_until_previous_stages_completed": index > 0,
		"time_limit_minutes": timeLimit,
		"security":           map[string]any{"enabled": securityEnabled, "require_fullscreen": fullscreen, "max_serious_violations": 3, "temporary_lock_seconds": 2, "fullscreen_grace_seconds": 10},
	}
	mergeMap(defaults, configured)
	return defaults
}

func mergeMap(destination, source map[string]any) {
	for key, value := range source {
		if nested, ok := value.(map[string]any); ok {
			if target, ok := destination[key].(map[string]any); ok {
				mergeMap(target, nested)
				continue
			}
		}
		destination[key] = value
	}
}

func groupedForms(snapshot map[string]any) []any {
	groups := map[string][]any{}
	for _, raw := range arrayValue(snapshot, "assessments") {
		assessment, ok := raw.(map[string]any)
		if !ok {
			continue
		}
		instrument := stringValue(assessment, "instrument_type")
		groups[instrument] = append(groups[instrument], assessment)
	}
	keys := make([]string, 0, len(groups))
	for key := range groups {
		keys = append(keys, key)
	}
	sort.SliceStable(keys, func(i, j int) bool { return instrumentOrderOrDefault(keys[i]) < instrumentOrderOrDefault(keys[j]) })
	result := make([]any, 0, len(keys))
	for _, key := range keys {
		result = append(result, map[string]any{"instrument_type": key, "instrument_label": instrumentLabel(key), "assessments": groups[key]})
	}
	return result
}

func randomizeSnapshot(snapshot map[string]any, targetID int64) {
	for _, rawAssessment := range arrayValue(snapshot, "assessments") {
		assessment, ok := rawAssessment.(map[string]any)
		if !ok {
			continue
		}
		if stringValue(assessment, "instrument_type") != "pilihan_ganda_kompleks" {
			continue
		}
		for _, rawForm := range arrayValue(assessment, "forms") {
			form, ok := rawForm.(map[string]any)
			if !ok {
				continue
			}
			for _, rawField := range arrayValue(form, "fields") {
				field, ok := rawField.(map[string]any)
				if !ok || stringValue(field, "tipe_field") != "radio" {
					continue
				}
				options, ok := field["opsi_field"].([]any)
				if !ok || len(options) < 2 {
					continue
				}
				seed := choiceSeed(targetID, int64Value(assessment, "id"), int64Value(form, "id"), int64Value(field, "id"))
				shufflePHP(options, seed)
				field["opsi_field"] = options
			}
		}
	}
}

func choiceSeed(targetID, assessmentID, formID, fieldID int64) uint32 {
	value := []byte("assessment-choice|" + itoa(targetID) + "|" + itoa(assessmentID) + "|" + itoa(formID) + "|" + itoa(fieldID))
	return crc32.ChecksumIEEE(value)
}

type phpMT struct {
	state [624]uint32
	index int
}

func newPHPMT(seed uint32) *phpMT {
	mt := &phpMT{index: 624}
	mt.state[0] = seed
	for i := 1; i < 624; i++ {
		mt.state[i] = 1812433253*(mt.state[i-1]^(mt.state[i-1]>>30)) + uint32(i)
	}
	return mt
}
func (m *phpMT) twist() {
	for i := 0; i < 624; i++ {
		y := (m.state[i] & 0x80000000) | (m.state[(i+1)%624] & 0x7fffffff)
		m.state[i] = m.state[(i+397)%624] ^ (y >> 1)
		if y&1 != 0 {
			m.state[i] ^= 0x9908b0df
		}
	}
	m.index = 0
}
func (m *phpMT) next() uint32 {
	if m.index >= 624 {
		m.twist()
	}
	y := m.state[m.index]
	m.index++
	y ^= y >> 11
	y ^= (y << 7) & 0x9d2c5680
	y ^= (y << 15) & 0xefc60000
	y ^= y >> 18
	return y
}
func shufflePHP(items []any, seed uint32) {
	mt := newPHPMT(seed)
	for i := len(items) - 1; i > 0; i-- {
		j := int(mt.next() % uint32(i+1))
		items[i], items[j] = items[j], items[i]
	}
}

func normalizeScoring(value map[string]any) {
	for _, raw := range arrayValue(value, "assessments") {
		a, ok := raw.(map[string]any)
		if !ok {
			continue
		}
		normalizeScoringMap(a)
		for _, rf := range arrayValue(a, "forms") {
			if f, ok := rf.(map[string]any); ok {
				normalizeScoringMap(f)
				for _, rfield := range arrayValue(f, "fields") {
					if field, ok := rfield.(map[string]any); ok {
						normalizeScoringMap(field)
					}
				}
			}
		}
	}
}
func normalizeScoringMap(value map[string]any) {
	config, ok := value["scoring_config"].(map[string]any)
	if !ok {
		return
	}
	if text, ok := config["advanced_rules_text"].(string); ok {
		var parsed any
		if json.Unmarshal([]byte(text), &parsed) == nil {
			config["advanced_rules_text"] = parsed
		}
	}
}

func applyAutofill(forms []any, guru GuruRow) {
	for _, group := range forms {
		g, ok := group.(map[string]any)
		if !ok {
			continue
		}
		for _, ra := range arrayValue(g, "assessments") {
			a, ok := ra.(map[string]any)
			if !ok {
				continue
			}
			for _, rf := range arrayValue(a, "forms") {
				f, ok := rf.(map[string]any)
				if !ok {
					continue
				}
				identityForm := stringValue(g, "instrument_type") == "portofolio" && strings.Contains(strings.ToUpper(formIdentityName(f)), "IDENTITAS")
				for _, rfield := range arrayValue(f, "fields") {
					field, ok := rfield.(map[string]any)
					if !ok {
						continue
					}
					fieldType, hasFieldType := optionalStringValue(field, "tipe_field")
					source := normalizeAutofillSource(stringValue(field, "autofill_source"), fieldType, hasFieldType)
					if source == "" {
						source = normalizeAutofillSource(
							inferAutofill(stringValue(field, "label"), stringValue(field, "nama_field")),
							fieldType,
							hasFieldType,
						)
					}
					if source == "" {
						field["autofill_source"] = nil
					} else {
						field["autofill_source"] = source
					}
					field["default_value"] = nil
					if identityForm && source != "" {
						field["default_value"] = autofillDefault(field, source, guru)
					}
				}
			}
		}
	}
}

func formIdentityName(form map[string]any) string {
	if value, ok := form["kode_form"]; ok && value != nil {
		return stringValue(form, "kode_form")
	}
	return stringValue(form, "judul_form")
}

var autofillLabels = map[string]string{
	"nama_lengkap": "Nama Lengkap", "no_ktp": "NIK / No. KTP", "nip": "NIP", "nuptk": "NUPTK",
	"nip_nuptk": "NIP / NUPTK", "golongan": "Golongan", "jabatan": "Jabatan", "status_kepegawaian": "Status Kepegawaian",
	"eksternal_jabatan": "Ketenagaan", "jenis_jabatan": "Jenis Jabatan", "kategori_jabatan": "Kategori Jabatan",
	"tugas_jabatan": "Tugas Jabatan", "latar_jabatan": "Latar Jabatan", "gender": "Jenis Kelamin", "tempat_lahir": "Tempat Lahir",
	"tgl_lahir": "Tanggal Lahir", "agama": "Agama", "pendidikan": "Pendidikan Terakhir", "email": "Email", "no_hp": "No. HP",
	"no_wa": "No. WhatsApp", "satuan_pendidikan": "Satuan Pendidikan", "npsn_sekolah": "NPSN Sekolah", "kabupaten": "Kabupaten / Kota",
	"alamat_satuan": "Alamat Satuan Pendidikan", "alamat_rumah": "Alamat Rumah", "npwp": "NPWP", "no_rek": "No. Rekening", "jenis_bank": "Jenis Bank",
}

func normalizeAutofillSource(source, fieldType string, hasFieldType bool) string {
	source = strings.TrimSpace(source)
	if _, ok := autofillLabels[source]; !ok {
		return ""
	}
	if hasFieldType && !supportsAutofillFieldType(fieldType) {
		return ""
	}
	return source
}

func supportsAutofillFieldType(fieldType string) bool {
	switch fieldType {
	case "text", "textarea", "number", "email", "date", "select", "radio", "checkbox":
		return true
	default:
		return false
	}
}

func autofillDefault(field map[string]any, source string, guru GuruRow) any {
	value := strings.TrimSpace(guruValue(source, guru))
	if value == "" {
		return nil
	}
	fieldType := strings.TrimSpace(stringValue(field, "tipe_field"))
	if fieldType == "" {
		fieldType = "text"
	}

	switch fieldType {
	case "checkbox":
		return resolveCheckboxAutofill(field, value)
	case "select", "radio":
		return resolveChoiceAutofill(field, fieldType, value)
	case "number":
		if !isNumericAutofill(value) {
			return nil
		}
	case "email":
		if !isEmailAutofill(value) {
			return nil
		}
	case "date":
		normalized, ok := normalizeDateAutofill(value)
		if !ok {
			return nil
		}
		value = normalized
	}

	return value
}

func stringValueAny(value any) string {
	text, _ := value.(string)
	return text
}

func optionalStringValue(value map[string]any, key string) (string, bool) {
	item, ok := value[key]
	if !ok || item == nil {
		return "", false
	}
	text, ok := item.(string)
	return text, ok
}

func resolveChoiceAutofill(field map[string]any, fieldType, rawValue string) any {
	for _, rawOption := range normalizeOptions(field["opsi_field"]) {
		option, ok := rawOption.(map[string]any)
		if !ok || !matchesAutofillChoice(option, rawValue) {
			continue
		}
		value := strings.TrimSpace(stringValue(option, "value"))
		if value != "" {
			return value
		}
	}

	if fieldType == "select" && fieldAllowsOtherInput(field) {
		return rawValue
	}
	return nil
}

func resolveCheckboxAutofill(field map[string]any, rawValue string) any {
	selectedRawValues := splitAutofillValues(rawValue)
	if len(selectedRawValues) == 0 {
		return nil
	}

	selectedValues := []string{}
	for _, rawOption := range normalizeOptions(field["opsi_field"]) {
		option, ok := rawOption.(map[string]any)
		if !ok {
			continue
		}
		matched := false
		for _, selected := range selectedRawValues {
			if matchesAutofillChoice(option, selected) {
				matched = true
				break
			}
		}
		if matched {
			if value := strings.TrimSpace(stringValue(option, "value")); value != "" {
				selectedValues = append(selectedValues, value)
			}
		}
	}
	if len(selectedValues) == 0 {
		return nil
	}
	return selectedValues
}

func splitAutofillValues(value string) []string {
	parts := strings.FieldsFunc(value, func(r rune) bool {
		return r == '\r' || r == '\n' || r == ',' || r == ';' || r == '|'
	})
	result := make([]string, 0, len(parts))
	for _, part := range parts {
		if part = strings.TrimSpace(part); part != "" {
			result = append(result, part)
		}
	}
	return result
}

func matchesAutofillChoice(option map[string]any, rawValue string) bool {
	wanted := normalizeKeywordSource(rawValue)
	if wanted == "" {
		return false
	}
	for _, candidate := range []string{stringValue(option, "value"), stringValue(option, "label")} {
		if normalizeKeywordSource(candidate) == wanted {
			return true
		}
	}
	return false
}

func fieldAllowsOtherInput(field map[string]any) bool {
	if validation, ok := field["validasi"].(map[string]any); ok {
		if value, exists := validation["allow_other_input"]; exists && value != nil {
			allowed, _ := value.(bool)
			return allowed
		}
	}
	allowed, _ := field["allow_other_input"].(bool)
	return allowed
}

func isNumericAutofill(value string) bool {
	number, err := strconv.ParseFloat(value, 64)
	return err == nil && !math.IsNaN(number) && !math.IsInf(number, 0)
}

func isEmailAutofill(value string) bool {
	parsed, err := mail.ParseAddress(value)
	at := strings.LastIndex(value, "@")
	return err == nil && parsed.Address == value && at > 0 && strings.Contains(value[at+1:], ".")
}

func normalizeDateAutofill(value string) (string, bool) {
	for _, layout := range []string{
		"2006-01-02", time.RFC3339, "2006-01-02 15:04:05", "02/01/2006", "01/02/2006",
	} {
		if parsed, err := time.Parse(layout, value); err == nil {
			return parsed.Format("2006-01-02"), true
		}
	}
	return "", false
}

func guruValue(source string, guru GuruRow) string {
	switch source {
	case "nama_lengkap":
		return guruString(guru.Nama)
	case "no_ktp":
		return guruString(guru.NIK)
	case "nip":
		return guruString(guru.NIP)
	case "nuptk":
		return guruString(guru.NUPTK)
	case "nip_nuptk":
		if value := guruString(guru.NIP); value != "" {
			return value
		}
		return guruString(guru.NUPTK)
	case "jabatan":
		if value := guruString(guru.Jabatan); value != "" {
			return value
		}
		if value := guruString(guru.JenisJabatan); value != "" {
			return value
		}
		if value := guruString(guru.EksternalJabatan); value != "" {
			return value
		}
		return guruString(guru.StatusKepegawaian)
	case "jenis_jabatan":
		return guruString(guru.JenisJabatan)
	case "kategori_jabatan":
		return guruString(guru.KategoriJabatan)
	case "tugas_jabatan":
		return guruString(guru.TugasJabatan)
	case "latar_jabatan":
		return guruString(guru.LatarJabatan)
	case "status_kepegawaian":
		return guruString(guru.StatusKepegawaian)
	case "gender":
		return guruString(guru.Gender)
	case "tempat_lahir":
		return guruString(guru.TempatLahir)
	case "tgl_lahir":
		return stringValueAny(nullDate(guru.TglLahir))
	case "agama":
		return guruString(guru.Agama)
	case "pendidikan":
		return guruString(guru.Pendidikan)
	case "npsn_sekolah":
		return guruString(guru.NPSNSekolah)
	case "alamat_satuan":
		return guruString(guru.AlamatSatuan)
	case "alamat_rumah":
		return guruString(guru.AlamatRumah)
	case "no_hp":
		return guruString(guru.NoHP)
	case "no_wa":
		return guruString(guru.NoWA)
	case "npwp":
		return guruString(guru.NPWP)
	case "no_rek":
		return guruString(guru.NoRek)
	case "jenis_bank":
		return guruString(guru.JenisBank)
	case "eksternal_jabatan":
		return guruString(guru.EksternalJabatan)
	case "kabupaten":
		return guruString(guru.Kabupaten)
	case "satuan_pendidikan":
		return guruString(guru.SatuanPendidikan)
	case "email":
		return guruString(guru.Email)
	}
	return ""
}

func guruString(value sql.NullString) string {
	return strings.TrimSpace(stringValueSQL(value))
}

type autofillInference struct {
	source  string
	needles []string
}

var autofillInferenceMap = []autofillInference{
	{source: "nama_lengkap", needles: []string{"nama_lengkap", "nama lengkap", "nama peserta"}},
	{source: "no_ktp", needles: []string{"nik", "no ktp", "nomor ktp", "ktp"}},
	{source: "nip_nuptk", needles: []string{"nip_nuptk", "nip nuptk"}},
	{source: "nip", needles: []string{" nip ", "nomor induk pegawai", "nip"}},
	{source: "nuptk", needles: []string{"nuptk"}},
	{source: "golongan", needles: []string{"golongan", "pangkat"}},
	{source: "jabatan", needles: []string{"jabatan"}},
	{source: "status_kepegawaian", needles: []string{"status_kepegawaian", "status kepegawaian"}},
	{source: "eksternal_jabatan", needles: []string{"ketenagaan", "kelompok jabatan"}},
	{source: "jenis_jabatan", needles: []string{"jenis_jabatan", "jenis jabatan"}},
	{source: "kategori_jabatan", needles: []string{"kategori_jabatan", "kategori jabatan"}},
	{source: "tugas_jabatan", needles: []string{"tugas_jabatan", "tugas jabatan"}},
	{source: "latar_jabatan", needles: []string{"latar_jabatan", "latar jabatan"}},
	{source: "gender", needles: []string{"jenis_kelamin", "jenis kelamin", "gender", "kelamin"}},
	{source: "tempat_lahir", needles: []string{"tempat_lahir", "tempat lahir"}},
	{source: "tgl_lahir", needles: []string{"tanggal_lahir", "tanggal lahir", "tgl_lahir", "tgl lahir", "lahir"}},
	{source: "agama", needles: []string{"agama"}},
	{source: "pendidikan", needles: []string{"pendidikan", "kualifikasi akademik"}},
	{source: "email", needles: []string{"email", "surel"}},
	{source: "no_hp", needles: []string{"no_hp", "nomor hp", "no hp", "telepon"}},
	{source: "no_wa", needles: []string{"no_wa", "nomor wa", "nomor whatsapp", "whatsapp"}},
	{source: "satuan_pendidikan", needles: []string{"satuan_pendidikan", "satuan pendidikan", "sekolah", "instansi"}},
	{source: "npsn_sekolah", needles: []string{"npsn"}},
	{source: "kabupaten", needles: []string{"kabupaten_kota", "kabupaten kota", "kabupaten", "kota"}},
	{source: "alamat_satuan", needles: []string{"alamat_satuan", "alamat satuan"}},
	{source: "alamat_rumah", needles: []string{"alamat_rumah", "alamat rumah"}},
	{source: "npwp", needles: []string{"npwp"}},
	{source: "no_rek", needles: []string{"no_rek", "nomor rekening", "rekening"}},
	{source: "jenis_bank", needles: []string{"jenis_bank", "jenis bank", "bank"}},
}

func inferAutofill(label, name string) string {
	candidates := []string{normalizeKeywordSource(name), normalizeKeywordSource(label)}
	candidates = filterNonEmpty(candidates)
	if containsString(candidates, "nama") || containsString(candidates, "nama lengkap") {
		return "nama_lengkap"
	}
	haystack := strings.Join(candidates, " ")
	for _, entry := range autofillInferenceMap {
		for _, needle := range entry.needles {
			if normalized := normalizeKeywordSource(needle); normalized != "" && containsString(candidates, normalized) {
				return entry.source
			}
		}
	}
	for _, entry := range autofillInferenceMap {
		for _, needle := range entry.needles {
			if normalized := normalizeKeywordSource(needle); normalized != "" && strings.Contains(haystack, normalized) {
				return entry.source
			}
		}
	}
	return ""
}

func filterNonEmpty(values []string) []string {
	result := make([]string, 0, len(values))
	for _, value := range values {
		if value != "" {
			result = append(result, value)
		}
	}
	return result
}

func containsString(values []string, wanted string) bool {
	for _, value := range values {
		if value == wanted {
			return true
		}
	}
	return false
}

func normalizeKeywordSource(value string) string {
	transformed, _, err := transform.String(norm.NFD, strings.ToLower(value))
	if err == nil {
		value = transformed
	}

	var builder strings.Builder
	space := false
	for _, r := range value {
		if unicode.Is(unicode.Mn, r) {
			continue
		}
		if (r >= 'a' && r <= 'z') || (r >= '0' && r <= '9') {
			if space && builder.Len() > 0 {
				builder.WriteByte(' ')
			}
			builder.WriteRune(r)
			space = false
			continue
		}
		if builder.Len() > 0 {
			space = true
		}
	}
	return strings.TrimSpace(builder.String())
}

func normalizeOptions(options any) []any {
	if options == nil {
		return []any{}
	}
	result := []any{}
	switch values := options.(type) {
	case []any:
		for _, raw := range values {
			result = append(result, normalizeOption(raw, ""))
		}
	case map[string]any:
		if _, hasLabel := values["label"]; hasLabel {
			result = append(result, normalizeOption(values, ""))
			break
		}
		if _, hasValue := values["value"]; hasValue {
			result = append(result, normalizeOption(values, ""))
			break
		}
		for key, raw := range values {
			if entry, ok := raw.(map[string]any); ok {
				result = append(result, normalizeOption(entry, key))
				continue
			}
			result = append(result, normalizeOption(map[string]any{"label": raw, "value": key}, ""))
		}
	}
	filtered := result[:0]
	for _, raw := range result {
		if stringValue(raw.(map[string]any), "value") != "" {
			filtered = append(filtered, raw)
		}
	}
	sort.SliceStable(filtered, func(i, j int) bool {
		return stringValue(filtered[i].(map[string]any), "value") < stringValue(filtered[j].(map[string]any), "value")
	})
	return filtered
}

func mergeLookupOptions(lookup, stored []any) []any {
	storedByKey := map[string]map[string]any{}
	for _, raw := range stored {
		entry, ok := raw.(map[string]any)
		if !ok {
			continue
		}
		storedByKey[strings.ToLower(stringValue(entry, "value"))] = entry
		storedByKey[strings.ToLower(stringValue(entry, "label"))] = entry
	}
	result := make([]any, 0, len(lookup))
	for _, raw := range lookup {
		entry, ok := raw.(map[string]any)
		if !ok {
			continue
		}
		value := stringValue(entry, "value")
		matched := storedByKey[strings.ToLower(value)]
		if matched == nil {
			matched = storedByKey[strings.ToLower(stringValue(entry, "label"))]
		}
		if matched != nil {
			for _, key := range []string{"score", "level_kompetensi", "level_kompetensi_label"} {
				if matched[key] != nil {
					entry[key] = matched[key]
				}
			}
		}
		result = append(result, entry)
	}
	return result
}

func normalizeOption(raw any, fallback string) map[string]any {
	if entry, ok := raw.(map[string]any); ok {
		rawLabel, rawValue := strings.TrimSpace(stringValue(entry, "label")), strings.TrimSpace(stringValue(entry, "value"))
		label, value := rawLabel, rawValue
		if shouldSwapChoiceLabelAndValue(label, value) {
			label, value = value, label
		}
		if label == "" {
			label = fallback
		}
		if value == "" {
			value = label
		}
		return map[string]any{"label": label, "value": value, "score": optionScore(entry), "level_kompetensi": entry["level_kompetensi"], "level_kompetensi_label": entry["level_kompetensi_label"]}
	}
	text, _ := raw.(string)
	if text == "" {
		text = fallback
	}
	return map[string]any{"label": text, "value": text, "score": nil, "level_kompetensi": nil, "level_kompetensi_label": nil}
}

func optionScore(entry map[string]any) any {
	if score, ok := numericValue(entry["score"]); ok {
		return score
	}
	if level, ok := numericValue(entry["level_kompetensi"]); ok && level >= 1 && level <= 5 {
		return level
	}
	return nil
}

func numericValue(value any) (float64, bool) {
	switch number := value.(type) {
	case float64:
		return number, true
	case int:
		return float64(number), true
	case string:
		parsed, err := strconv.ParseFloat(strings.TrimSpace(number), 64)
		return parsed, err == nil
	default:
		return 0, false
	}
}

func normalizeSnapshotChoiceOptions(snapshot map[string]any) {
	for _, rawAssessment := range arrayValue(snapshot, "assessments") {
		assessment, ok := rawAssessment.(map[string]any)
		if !ok {
			continue
		}
		for _, rawForm := range arrayValue(assessment, "forms") {
			form, ok := rawForm.(map[string]any)
			if !ok {
				continue
			}
			for _, rawField := range arrayValue(form, "fields") {
				field, ok := rawField.(map[string]any)
				if !ok || !isChoiceField(stringValue(field, "tipe_field")) {
					continue
				}
				field["opsi_field"] = normalizeOptions(field["opsi_field"])
			}
		}
	}
}

func isChoiceField(fieldType string) bool {
	switch fieldType {
	case "radio", "select", "checkbox", "likert":
		return true
	default:
		return false
	}
}

func shouldSwapChoiceLabelAndValue(label, value string) bool {
	return looksLikeChoiceCode(label) && looksLikeChoiceText(value)
}

func looksLikeChoiceCode(value string) bool {
	if value == "" || strings.IndexFunc(value, unicode.IsSpace) >= 0 || utf8.RuneCountInString(value) > 6 {
		return false
	}
	for _, r := range value {
		if !((r >= 'a' && r <= 'z') || (r >= 'A' && r <= 'Z') || (r >= '0' && r <= '9') || r == '.' || r == '_' || r == '-') {
			return false
		}
	}
	return true
}

func looksLikeChoiceText(value string) bool {
	return value != "" && (strings.IndexFunc(value, unicode.IsSpace) >= 0 || utf8.RuneCountInString(value) >= 6)
}

func validSnapshot(value map[string]any) bool { return len(arrayValue(value, "assessments")) > 0 }
func validSnapshotAny(value any) bool {
	snapshot, ok := mapAny(value)
	return ok && validSnapshot(snapshot)
}
func mapAny(value any) (map[string]any, bool) {
	result, ok := value.(map[string]any)
	return result, ok
}
func mapValue(value map[string]any, key string) map[string]any {
	result, _ := value[key].(map[string]any)
	return result
}
func arrayValue(value map[string]any, key string) []any {
	result, _ := value[key].([]any)
	return result
}
func stringValue(value map[string]any, key string) string {
	if text, ok := value[key].(string); ok {
		return text
	}
	return ""
}
func int64Value(value map[string]any, key string) int64 {
	switch n := value[key].(type) {
	case float64:
		return int64(n)
	case int64:
		return n
	case int:
		return int64(n)
	}
	return 0
}
func nullableInt(value sql.NullInt64) any {
	if value.Valid {
		return value.Int64
	}
	return nil
}
func stringValueSQL(value sql.NullString) string {
	if value.Valid {
		return value.String
	}
	return ""
}
func instrumentOrderOrDefault(value string) int {
	if order, ok := instrumentOrder[value]; ok {
		return order
	}
	return 99
}
func instrumentLabel(value any) string {
	text := ""
	switch v := value.(type) {
	case string:
		text = v
	case sql.NullString:
		text = v.String
	}
	switch text {
	case "portofolio":
		return "Portofolio"
	case "pilihan_ganda_kompleks":
		return "Pilihan Ganda Kompleks"
	case "skala_likert":
		return "Skala Likert"
	case "studi_kasus":
		return "Studi Kasus"
	case "monitoring_observasi_eviden":
		return "Monitoring / Observasi / Eviden"
	}
	return text
}

func kompetensiLabel(value any) any {
	text := ""
	switch v := value.(type) {
	case string:
		text = v
	case sql.NullString:
		text = v.String
	}
	switch text {
	case "pedagogik":
		return "Pedagogik"
	case "kepribadian":
		return "Kepribadian"
	case "sosial":
		return "Sosial"
	case "profesional":
		return "Profesional"
	default:
		return nil
	}
}

func ketenagaanLabel(value any) string {
	text := ""
	switch v := value.(type) {
	case string:
		text = v
	case sql.NullString:
		text = v.String
	}
	switch text {
	case "tenaga_pendidik":
		return "Tenaga Pendidik"
	case "tenaga_kependidikan":
		return "Tenaga Kependidikan"
	case "stakeholder":
		return "Stakeholder"
	}
	return text
}
func itoa(value int64) string { return strconv.FormatInt(value, 10) }
func nowISO() string          { return time.Now().UTC().Format("2006-01-02T15:04:05-07:00") }
