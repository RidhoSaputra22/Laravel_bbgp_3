package main

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"sort"
	"strings"
)

type JSONValue struct {
	Value any
	Raw   []byte
	Valid bool
}

func (j *JSONValue) Scan(src any) error {
	if src == nil {
		j.Value, j.Raw, j.Valid = nil, nil, false
		return nil
	}
	switch value := src.(type) {
	case []byte:
		j.Raw = append(j.Raw[:0], value...)
	case string:
		j.Raw = []byte(value)
	default:
		return fmt.Errorf("unsupported JSON value %T", src)
	}
	var decoded any
	if len(j.Raw) == 0 || string(j.Raw) == "null" {
		j.Value, j.Valid = nil, false
		return nil
	}
	if err := json.Unmarshal(j.Raw, &decoded); err != nil {
		return err
	}
	j.Value, j.Valid = decoded, true
	return nil
}

func cloneMap(input map[string]any) map[string]any {
	if input == nil {
		return nil
	}
	bytes, _ := json.Marshal(input)
	var output map[string]any
	_ = json.Unmarshal(bytes, &output)
	return output
}

type TargetRow struct {
	ID             int64
	AssignmentID   int64
	SessionID      sql.NullInt64
	CombinationID  sql.NullInt64
	GuruID         sql.NullInt64
	Status         sql.NullString
	AssignedAt     sql.NullTime
	StartedAt      sql.NullTime
	DeadlineAt     sql.NullTime
	SubmittedAt    sql.NullTime
	CompletionMode sql.NullString
	TimedOutAt     sql.NullTime
	UpdatedAt      sql.NullTime

	Assignment  AssignmentRow
	Guru        GuruRow
	Combination CombinationRow
	Attempt     JSONValue
}

type AssignmentRow struct {
	ID               int64
	Code             sql.NullString
	Title            sql.NullString
	Description      sql.NullString
	IsActive         bool
	Distribution     sql.NullString
	TargetKetenagaan sql.NullString
	StartDate        sql.NullTime
	EndDate          sql.NullTime
}

type GuruRow struct {
	ID                int64
	Nama              sql.NullString
	NIK               sql.NullString
	NIP               sql.NullString
	NUPTK             sql.NullString
	Email             sql.NullString
	Jabatan           sql.NullString
	StatusKepegawaian sql.NullString
	EksternalJabatan  sql.NullString
	JenisJabatan      sql.NullString
	KategoriJabatan   sql.NullString
	TugasJabatan      sql.NullString
	LatarJabatan      sql.NullString
	Gender            sql.NullString
	TempatLahir       sql.NullString
	TglLahir          sql.NullTime
	Agama             sql.NullString
	Pendidikan        sql.NullString
	Kabupaten         sql.NullString
	SatuanPendidikan  sql.NullString
	NPSNSekolah       sql.NullString
	AlamatSatuan      sql.NullString
	AlamatRumah       sql.NullString
	NoHP              sql.NullString
	NoWA              sql.NullString
	NPWP              sql.NullString
	NoRek             sql.NullString
	JenisBank         sql.NullString
}

type CombinationRow struct {
	ID               sql.NullInt64
	Code             sql.NullString
	Title            sql.NullString
	TargetKetenagaan sql.NullString
	Snapshot         JSONValue
	Active           bool
}

func (d *Database) preloadCombinations(ctx context.Context) error {
	if d.combinations != nil {
		return nil
	}

	rows, err := d.db.QueryContext(ctx, `
		SELECT id, kode_kombinasi, judul, target_ketenagaan, structure_snapshot, is_active
		FROM assessment_combinations`)
	if err != nil {
		return err
	}
	defer rows.Close()

	combinations := map[int64]CombinationRow{}
	for rows.Next() {
		var combination CombinationRow
		var id int64
		var active int
		if err := rows.Scan(
			&id, &combination.Code, &combination.Title, &combination.TargetKetenagaan,
			&combination.Snapshot, &active,
		); err != nil {
			return err
		}
		combination.ID = sql.NullInt64{Int64: id, Valid: true}
		combination.Active = active != 0
		combination.Snapshot.Raw = nil
		combinations[id] = combination
	}
	if err := rows.Err(); err != nil {
		return err
	}

	d.combinations = combinations
	return nil
}

func (d *Database) loadTargets(ctx context.Context, ids []int64) (map[int64]TargetRow, error) {
	if len(ids) == 0 {
		return map[int64]TargetRow{}, nil
	}
	if err := d.preloadCombinations(ctx); err != nil {
		return nil, err
	}
	placeholders, args := inClause(ids)
	query := fmt.Sprintf(`
		SELECT
			t.id, t.assessment_assignment_id, t.assessment_assignment_session_id,
			COALESCE(t.assessment_combination_id, a.assessment_combination_id), t.guru_id, t.status, t.assigned_at,
			t.started_at, t.deadline_at, t.submitted_at, t.completion_mode,
			t.timed_out_at, t.updated_at,
			a.id, a.kode_penugasan, a.judul_penugasan, a.deskripsi, a.is_active,
			a.status_distribusi, a.target_ketenagaan, a.tanggal_mulai, a.tanggal_selesai,
			g.id, g.nama_lengkap, g.no_ktp, g.nip, g.nuptk, g.email,
			g.jabatan, g.status_kepegawaian, g.eksternal_jabatan, g.jenis_jabatan,
			g.kategori_jabatan, g.tugas_jabatan, g.latar_jabatan, g.gender,
			g.tempat_lahir, g.tgl_lahir, g.agama, g.pendidikan,
			g.kabupaten, g.satuan_pendidikan, g.npsn_sekolah, g.alamat_satuan,
			g.alamat_rumah, g.no_hp, g.no_wa, g.npwp, g.no_rek, g.jenis_bank,
			attempt.structure_snapshot
		FROM assessment_assignment_targets t
		JOIN assessment_assignments a ON a.id = t.assessment_assignment_id
		LEFT JOIN gurus g ON g.id = t.guru_id
		LEFT JOIN assessment_attempts attempt ON attempt.assessment_assignment_target_id = t.id
		WHERE t.id IN (%s)
		  AND t.is_validator = 0
		  AND (a.kode_penugasan IS NULL OR a.kode_penugasan NOT LIKE 'PREVIEW-ADM-%%')`, placeholders)
	rows, err := d.db.QueryContext(ctx, query, args...)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	result := make(map[int64]TargetRow, len(ids))
	for rows.Next() {
		var row TargetRow
		var assignment AssignmentRow
		var guru GuruRow
		var active int
		if err := rows.Scan(
			&row.ID, &row.AssignmentID, &row.SessionID, &row.CombinationID, &row.GuruID,
			&row.Status, &row.AssignedAt, &row.StartedAt, &row.DeadlineAt, &row.SubmittedAt,
			&row.CompletionMode, &row.TimedOutAt, &row.UpdatedAt,
			&assignment.ID, &assignment.Code, &assignment.Title, &assignment.Description,
			&active, &assignment.Distribution, &assignment.TargetKetenagaan,
			&assignment.StartDate, &assignment.EndDate,
			&guru.ID, &guru.Nama, &guru.NIK, &guru.NIP, &guru.NUPTK, &guru.Email,
			&guru.Jabatan, &guru.StatusKepegawaian, &guru.EksternalJabatan,
			&guru.JenisJabatan, &guru.KategoriJabatan, &guru.TugasJabatan, &guru.LatarJabatan,
			&guru.Gender, &guru.TempatLahir, &guru.TglLahir, &guru.Agama, &guru.Pendidikan,
			&guru.Kabupaten, &guru.SatuanPendidikan, &guru.NPSNSekolah, &guru.AlamatSatuan,
			&guru.AlamatRumah, &guru.NoHP, &guru.NoWA, &guru.NPWP, &guru.NoRek, &guru.JenisBank,
			&row.Attempt,
		); err != nil {
			return nil, err
		}
		assignment.IsActive = active != 0
		combination := d.combinations[row.CombinationID.Int64]
		row.Assignment, row.Guru, row.Combination = assignment, guru, combination
		result[row.ID] = row
	}
	return result, rows.Err()
}

type SchemaField struct {
	ID           int64
	Order        int64
	AssessmentID int64
	FormID       int64
	Label        sql.NullString
	Description  sql.NullString
	Name         sql.NullString
	Type         sql.NullString
	Placeholder  sql.NullString
	Help         sql.NullString
	Options      JSONValue
	Autofill     sql.NullString
	Lookup       sql.NullString
	Dependency   JSONValue
	Validation   JSONValue
	Scoring      JSONValue
	Required     bool
	Active       bool
}

type SchemaForm struct {
	ID             int64
	Order          int64
	AssessmentID   int64
	Title          sql.NullString
	Code           sql.NullString
	Description    sql.NullString
	Competency     sql.NullString
	IndicatorCode  sql.NullString
	IndicatorLabel sql.NullString
	Scoreable      bool
	Scoring        JSONValue
	Fields         []SchemaField
}

type SchemaAssessment struct {
	ID           int64
	Order        int64
	Code         sql.NullString
	Title        sql.NullString
	Description  sql.NullString
	Instructions sql.NullString
	Instrument   sql.NullString
	Scoring      JSONValue
	StageConfig  JSONValue
	Active       bool
	Index        int
	Forms        []SchemaForm
}

func (d *Database) loadSchemas(ctx context.Context, assignmentIDs []int64) (map[int64][]SchemaAssessment, error) {
	if len(assignmentIDs) == 0 {
		return map[int64][]SchemaAssessment{}, nil
	}
	placeholders, args := inClause(assignmentIDs)
	query := fmt.Sprintf(`
		SELECT
			aa.assessment_assignment_id, a.id, a.kode_assessment, a.judul,
			a.deskripsi, a.petunjuk, a.instrument_type, a.scoring_config, a.is_active,
			aa.stage_config,
			f.id, f.judul_form, f.kode_form, f.deskripsi, f.kompetensi,
			f.indikator_kode, f.indikator_label, f.is_scoreable, f.scoring_config,
			ff.id, ff.assessment_form_id, ff.label, ff.deskripsi,
			ff.nama_field, ff.tipe_field, ff.placeholder, ff.bantuan, ff.opsi_field,
			ff.autofill_source, ff.lookup_source, ff.dependency_config, ff.validasi,
			ff.scoring_config, ff.is_required, ff.is_active,
			aa.urutan, f.urutan, ff.urutan
		FROM assessment_assignment_assessments aa
		JOIN assessments a ON a.id = aa.assessment_id
		LEFT JOIN assessment_forms f ON f.assessment_id = a.id
		LEFT JOIN assessment_form_fields ff ON ff.assessment_form_id = f.id
		WHERE aa.assessment_assignment_id IN (%s)`, placeholders)
	rows, err := d.db.QueryContext(ctx, query, args...)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	result := make(map[int64][]SchemaAssessment)
	assessmentIndexes := map[string]int{}
	formIndexes := map[string]int{}
	for rows.Next() {
		var assignmentID int64
		var assessment SchemaAssessment
		var form SchemaForm
		var field SchemaField
		var assessmentActive, scoreable, fieldRequired, fieldActive int
		var assessmentOrder, formOrder, fieldOrder sql.NullInt64
		if err := rows.Scan(
			&assignmentID, &assessment.ID, &assessment.Code, &assessment.Title,
			&assessment.Description, &assessment.Instructions, &assessment.Instrument,
			&assessment.Scoring, &assessmentActive, &assessment.StageConfig,
			&form.ID, &form.Title, &form.Code, &form.Description, &form.Competency,
			&form.IndicatorCode, &form.IndicatorLabel, &scoreable, &form.Scoring,
			&field.ID, &field.FormID, &field.Label, &field.Description,
			&field.Name, &field.Type, &field.Placeholder, &field.Help, &field.Options,
			&field.Autofill, &field.Lookup, &field.Dependency, &field.Validation,
			&field.Scoring, &fieldRequired, &fieldActive,
			&assessmentOrder, &formOrder, &fieldOrder,
		); err != nil {
			return nil, err
		}
		assessment.Order = orderValue(assessmentOrder)
		form.Order = orderValue(formOrder)
		field.Order = orderValue(fieldOrder)
		assessment.Active = assessmentActive != 0
		form.Scoreable = scoreable != 0
		field.Required, field.Active = fieldRequired != 0, fieldActive != 0
		field.AssessmentID = assessment.ID
		assessmentKey := fmt.Sprintf("%d:%d", assignmentID, assessment.ID)
		if _, ok := assessmentIndexes[assessmentKey]; !ok {
			assessment.Index = len(result[assignmentID])
			result[assignmentID] = append(result[assignmentID], assessment)
			assessmentIndexes[assessmentKey] = len(result[assignmentID]) - 1
		}
		assessmentIndex := assessmentIndexes[assessmentKey]
		formKey := fmt.Sprintf("%s:%d", assessmentKey, form.ID)
		if form.ID == 0 {
			continue
		}
		if _, ok := formIndexes[formKey]; !ok {
			result[assignmentID][assessmentIndex].Forms = append(result[assignmentID][assessmentIndex].Forms, form)
			formIndexes[formKey] = len(result[assignmentID][assessmentIndex].Forms) - 1
		}
		if field.ID > 0 && field.Active {
			formIndex := formIndexes[formKey]
			result[assignmentID][assessmentIndex].Forms[formIndex].Fields = append(
				result[assignmentID][assessmentIndex].Forms[formIndex].Fields, field,
			)
		}
	}
	for assignmentID, assessments := range result {
		sort.SliceStable(assessments, func(i, j int) bool {
			if assessments[i].Order != assessments[j].Order {
				return assessments[i].Order < assessments[j].Order
			}
			return assessments[i].ID < assessments[j].ID
		})
		for assessmentIndex := range assessments {
			assessment := &assessments[assessmentIndex]
			sort.SliceStable(assessment.Forms, func(i, j int) bool {
				if assessment.Forms[i].Order != assessment.Forms[j].Order {
					return assessment.Forms[i].Order < assessment.Forms[j].Order
				}
				return assessment.Forms[i].ID < assessment.Forms[j].ID
			})
			for formIndex := range assessment.Forms {
				fields := assessment.Forms[formIndex].Fields
				sort.SliceStable(fields, func(i, j int) bool {
					if fields[i].Order != fields[j].Order {
						return fields[i].Order < fields[j].Order
					}
					return fields[i].ID < fields[j].ID
				})
				assessment.Forms[formIndex].Fields = fields
			}
			assessment.Index = assessmentIndex
		}
		result[assignmentID] = assessments
	}
	return result, rows.Err()
}

func (d *Database) loadLookupOptions(ctx context.Context, sources map[string]bool) (map[string][]any, error) {
	result := make(map[string][]any)
	tables := map[string]string{
		"master_golongan_pns":         "jabatan_penugasan_golongans",
		"master_golongan_pppk":        "golongan_p3ks",
		"master_status_kepegawaian":   "kepegawaians",
		"master_pendidikan":           "pendidikans",
		"master_kabupaten":            "kabupatens",
		"master_satuan_pendidikan":    "satuan_pendidikans",
		"master_jabatan_umum":         "jabatans",
		"master_jabatan_pendidik":     "jabatan_pendidiks",
		"master_jabatan_kependidikan": "jabatan_kependidikans",
		"master_jabatan_stakeholder":  "jabatan_stake_holders",
		"master_jenis_jabatan":        "jenis_jabatans",
		"master_tugas_jabatan":        "jenis_tugas",
		"master_latar_jabatan":        "latar_jabatans",
	}
	for source := range sources {
		if source == "master_golongan" {
			labels := []string{}
			for _, table := range []string{"jabatan_penugasan_golongans", "golongan_p3ks"} {
				labels = append(labels, d.lookupLabels(ctx, table)...)
			}
			result[source] = lookupOptions(uniqueStrings(labels))
			continue
		}
		table, ok := tables[source]
		if !ok {
			continue
		}
		result[source] = lookupOptions(uniqueStrings(d.lookupLabels(ctx, table)))
	}
	return result, nil
}

func (d *Database) loadCombinationSnapshots(ctx context.Context, ids []int64) (map[int64]map[string]any, error) {
	result := map[int64]map[string]any{}
	if len(ids) == 0 {
		return result, nil
	}
	if err := d.preloadCombinations(ctx); err != nil {
		return nil, err
	}
	for _, id := range ids {
		combination, ok := d.combinations[id]
		if !ok || !combination.Active {
			continue
		}
		if object, ok := combination.Snapshot.Value.(map[string]any); ok && validSnapshot(object) {
			result[id] = object
		}
	}
	return result, nil
}

func (d *Database) loadStageConfigs(ctx context.Context, assignmentIDs []int64) (map[int64]map[int64]map[string]any, error) {
	result := map[int64]map[int64]map[string]any{}
	if len(assignmentIDs) == 0 {
		return result, nil
	}
	placeholders, args := inClause(assignmentIDs)
	rows, err := d.db.QueryContext(ctx, "SELECT assessment_assignment_id, assessment_id, stage_config FROM assessment_assignment_assessments WHERE assessment_assignment_id IN ("+placeholders+")", args...)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	for rows.Next() {
		var assignmentID, assessmentID int64
		var config JSONValue
		if err := rows.Scan(&assignmentID, &assessmentID, &config); err != nil {
			return nil, err
		}
		if value, ok := config.Value.(map[string]any); ok {
			if result[assignmentID] == nil {
				result[assignmentID] = map[int64]map[string]any{}
			}
			result[assignmentID][assessmentID] = value
		}
	}
	return result, rows.Err()
}

func (d *Database) lookupLabels(ctx context.Context, table string) []string {
	rows, err := d.db.QueryContext(ctx, "SELECT name FROM "+table)
	if err != nil {
		return nil
	}
	defer rows.Close()
	labels := []string{}
	for rows.Next() {
		var label string
		if rows.Scan(&label) == nil && strings.TrimSpace(label) != "" {
			labels = append(labels, strings.TrimSpace(label))
		}
	}
	return labels
}

func lookupOptions(labels []string) []any {
	result := make([]any, 0, len(labels))
	for _, label := range labels {
		result = append(result, map[string]any{"label": label, "value": label})
	}
	return result
}
func uniqueStrings(values []string) []string {
	seen := map[string]bool{}
	result := []string{}
	for _, value := range values {
		key := strings.ToLower(value)
		if !seen[key] {
			seen[key] = true
			result = append(result, value)
		}
	}
	sort.Strings(result)
	return result
}

type ValidatorRow struct {
	ID                  int64
	Code                sql.NullString
	Title               sql.NullString
	FormID              int64
	SourceMode          sql.NullString
	AssessmentID        sql.NullInt64
	Notes               sql.NullString
	Status              sql.NullString
	StartDate           sql.NullTime
	DueDate             sql.NullTime
	StartedAt           sql.NullTime
	SubmittedAt         sql.NullTime
	UpdatedAt           sql.NullTime
	AssessmentSnapshots JSONValue
	AssessmentSnapshot  JSONValue
	ValidatorSnapshot   JSONValue
	Form                ValidatorFormRow
}

type ValidatorFormRow struct {
	ID           int64
	Code         sql.NullString
	Title        sql.NullString
	Description  sql.NullString
	Instructions sql.NullString
	Status       sql.NullString
	Active       bool
	Sections     []ValidatorSectionRow
}

type ValidatorSectionRow struct {
	ID          int64
	Title       sql.NullString
	Description sql.NullString
	SortOrder   int
	Fields      []ValidatorFieldRow
}

type ValidatorFieldRow struct {
	ID          int64
	Label       sql.NullString
	Description sql.NullString
	Type        sql.NullString
	Options     JSONValue
	Required    bool
	Scored      bool
	MaxScore    sql.NullFloat64
	SortOrder   int
	Active      bool
}

func (d *Database) loadValidators(ctx context.Context, ids []int64) (map[int64]ValidatorRow, error) {
	if len(ids) == 0 {
		return map[int64]ValidatorRow{}, nil
	}
	placeholders, args := inClause(ids)
	query := fmt.Sprintf(`
		SELECT va.id, va.code, va.title, va.validator_form_id, va.source_mode,
			va.assessment_id, va.notes, va.status, va.start_date, va.due_date,
			va.started_at, va.submitted_at, va.updated_at, va.assessment_assignment_snapshots,
			va.assessment_snapshot, va.validator_snapshot,
			vf.id, vf.code, vf.title, vf.description, vf.instructions, vf.status, vf.is_active,
			vs.id, vs.title, vs.description, vs.sort_order,
		ff.id, ff.label, ff.description, ff.field_type, ff.options,
		ff.is_required, ff.is_scored, ff.max_score, ff.sort_order, ff.is_active
	FROM validator_assignments va
		LEFT JOIN validator_forms vf ON vf.id = va.validator_form_id
		LEFT JOIN validator_form_sections vs ON vs.validator_form_id = vf.id
		LEFT JOIN validator_form_fields ff ON ff.validator_form_section_id = vs.id
		WHERE va.id IN (%s)`, placeholders)
	rows, err := d.db.QueryContext(ctx, query, args...)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	result := make(map[int64]ValidatorRow)
	sectionIndex := map[string]int{}
	fieldIndex := map[string]int{}
	for rows.Next() {
		var row ValidatorRow
		var form ValidatorFormRow
		var section ValidatorSectionRow
		var field ValidatorFieldRow
		var formActive, required, scored, fieldActive int
		if err := rows.Scan(
			&row.ID, &row.Code, &row.Title, &row.FormID, &row.SourceMode,
			&row.AssessmentID, &row.Notes, &row.Status, &row.StartDate, &row.DueDate,
			&row.StartedAt, &row.SubmittedAt, &row.UpdatedAt, &row.AssessmentSnapshots,
			&row.AssessmentSnapshot, &row.ValidatorSnapshot,
			&form.ID, &form.Code, &form.Title, &form.Description, &form.Instructions,
			&form.Status, &formActive, &section.ID, &section.Title, &section.Description,
			&section.SortOrder, &field.ID, &field.Label, &field.Description, &field.Type,
			&field.Options, &required, &scored, &field.MaxScore, &field.SortOrder, &fieldActive,
		); err != nil {
			return nil, err
		}
		row.Form = form
		row.Form.Active = formActive != 0
		row.FormID = form.ID
		if section.ID == 0 {
			result[row.ID] = row
			continue
		}
		key := fmt.Sprintf("%d:%d", row.ID, section.ID)
		if _, ok := result[row.ID]; !ok {
			result[row.ID] = row
		}
		if _, ok := sectionIndex[key]; !ok {
			stored := result[row.ID]
			stored.Form.Sections = append(stored.Form.Sections, section)
			result[row.ID] = stored
			sectionIndex[key] = len(stored.Form.Sections) - 1
		}
		if field.ID == 0 {
			continue
		}
		field.Required, field.Scored, field.Active = required != 0, scored != 0, fieldActive != 0
		sectionPosition := sectionIndex[key]
		fieldKey := fmt.Sprintf("%s:%d", key, field.ID)
		if _, ok := fieldIndex[fieldKey]; ok {
			continue
		}
		stored := result[row.ID]
		stored.Form.Sections[sectionPosition].Fields = append(stored.Form.Sections[sectionPosition].Fields, field)
		result[row.ID] = stored
		fieldIndex[fieldKey] = len(stored.Form.Sections[sectionPosition].Fields) - 1
	}
	for id, row := range result {
		sort.SliceStable(row.Form.Sections, func(i, j int) bool {
			if row.Form.Sections[i].SortOrder != row.Form.Sections[j].SortOrder {
				return row.Form.Sections[i].SortOrder < row.Form.Sections[j].SortOrder
			}
			return row.Form.Sections[i].ID < row.Form.Sections[j].ID
		})
		for sectionIndex := range row.Form.Sections {
			fields := row.Form.Sections[sectionIndex].Fields
			sort.SliceStable(fields, func(i, j int) bool {
				if fields[i].SortOrder != fields[j].SortOrder {
					return fields[i].SortOrder < fields[j].SortOrder
				}
				return fields[i].ID < fields[j].ID
			})
			row.Form.Sections[sectionIndex].Fields = fields
		}
		result[id] = row
	}
	return result, rows.Err()
}

type Database struct {
	db           *sql.DB
	combinations map[int64]CombinationRow
}

func inClause(ids []int64) (string, []any) {
	pl := make([]string, len(ids))
	args := make([]any, len(ids))
	for i, id := range ids {
		pl[i], args[i] = "?", id
	}
	return strings.Join(pl, ","), args
}

func nullString(value sql.NullString) any {
	if !value.Valid {
		return nil
	}
	return value.String
}

func nullTime(value sql.NullTime) any {
	if !value.Valid {
		return nil
	}
	return value.Time.UTC().Format("2006-01-02T15:04:05.000Z")
}

func nullDate(value sql.NullTime) any {
	if !value.Valid {
		return nil
	}
	return value.Time.UTC().Format("2006-01-02")
}

func orderValue(value sql.NullInt64) int64 {
	if !value.Valid {
		return 0
	}
	return value.Int64
}

func jsonObject(value JSONValue) map[string]any {
	if !value.Valid {
		return nil
	}
	object, _ := value.Value.(map[string]any)
	return cloneMap(object)
}

func jsonAny(value JSONValue) any {
	if !value.Valid {
		return nil
	}
	return value.Value
}
