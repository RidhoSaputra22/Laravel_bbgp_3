<?php

namespace App\Http\Controllers;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Enum\KompetensiGuru;
use App\Enum\LevelKompetensi;
use App\Models\Assessment;
use App\Models\AssessmentForm;
use App\Services\Assessment\AssessmentCombinationService;
use App\Support\Assessment\AssessmentFieldLookupResolver;
use App\Support\Assessment\ChoiceOptionNormalizer;
use App\Support\Assessment\ParticipantAutoFillResolver;
use App\Support\Assessment\ScoringGuidanceAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AssessmentController extends Controller
{
    private string $menu = 'assessment';

    public function __construct(
        private readonly AssessmentCombinationService $combinationService,
        private readonly ParticipantAutoFillResolver $participantAutoFillResolver,
        private readonly AssessmentFieldLookupResolver $fieldLookupResolver
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAccess();

        $datas = Assessment::with(['forms.fields'])
            ->orderByDesc('id')
            ->get();

        return view('pages.admin.assessment.index', [
            'menu' => $this->menu,
            'datas' => $datas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeAccess();

        return view('pages.admin.assessment.create', [
            'menu' => $this->menu,
            'assessment' => new Assessment([
                'status' => 'draft',
                'is_active' => true,
                'target_ketenagaan' => AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
            ]),
            'fieldTypes' => $this->fieldTypes(),
            'formBuilderData' => [],
            'participantAutoFillOptions' => $this->participantAutoFillResolver->options(),
            'fieldLookupOptions' => $this->fieldLookupResolver->options(),
            'fieldLookupCatalog' => $this->fieldLookupResolver->previewCatalog(),
            'ketenagaanOptions' => AssessmentKetenagaanType::options(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $this->validatePayload($request);

        DB::beginTransaction();

        try {
            $assessmentCode = $this->resolveAssessmentCode(
                $validated['kode_assessment'] ?? null,
                $validated['judul'],
                $validated['instrument_type'] ?? null
            );

            $assessment = Assessment::create([
                'kode_assessment' => $assessmentCode,
                'judul' => $validated['judul'],
                'slug' => $this->generateUniqueSlug($validated['judul']),
                'deskripsi' => $validated['deskripsi'] ?? null,
                'petunjuk' => $validated['petunjuk'] ?? null,
                'instrument_type' => $validated['instrument_type'] ?? null,
                'target_ketenagaan' => $validated['target_ketenagaan'],
                'scoring_config' => $this->buildAssessmentScoringConfig($validated['instrument_type'] ?? null),
                'status' => $validated['status'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            $this->syncForms($assessment, $validated['forms']);

            DB::commit();

            return redirect()->route('assessment.index')->with('message', 'store');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['assessment' => 'Terjadi kesalahan saat menyimpan data assessment.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorizeAccess();

        $assessment = Assessment::with(['forms.fields'])->findOrFail($id);

        return view('pages.admin.assessment.show', [
            'menu' => $this->menu,
            'assessment' => $assessment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorizeAccess();

        $assessment = Assessment::with(['forms.fields'])->findOrFail($id);

        return view('pages.admin.assessment.edit', [
            'menu' => $this->menu,
            'assessment' => $assessment,
            'fieldTypes' => $this->fieldTypes(),
            'formBuilderData' => $this->buildFormBuilderData($assessment),
            'participantAutoFillOptions' => $this->participantAutoFillResolver->options(),
            'fieldLookupOptions' => $this->fieldLookupResolver->options(),
            'fieldLookupCatalog' => $this->fieldLookupResolver->previewCatalog(),
            'ketenagaanOptions' => AssessmentKetenagaanType::options(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizeAccess();

        $assessment = Assessment::with('forms.fields')->findOrFail($id);
        $validated = $this->validatePayload($request, $assessment->id);

        DB::beginTransaction();

        try {
            $assessmentCode = $this->resolveAssessmentCode(
                $validated['kode_assessment'] ?? null,
                $validated['judul'],
                $validated['instrument_type'] ?? null,
                $assessment->id,
                $assessment->kode_assessment
            );

            $assessment->update([
                'kode_assessment' => $assessmentCode,
                'judul' => $validated['judul'],
                'slug' => $this->generateUniqueSlug($validated['judul'], $assessment->id),
                'deskripsi' => $validated['deskripsi'] ?? null,
                'petunjuk' => $validated['petunjuk'] ?? null,
                'instrument_type' => $validated['instrument_type'] ?? null,
                'target_ketenagaan' => $validated['target_ketenagaan'],
                'scoring_config' => $this->buildAssessmentScoringConfig($validated['instrument_type'] ?? null),
                'status' => $validated['status'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            $this->syncForms($assessment, $validated['forms']);
            $assessment->load(['forms.fields']);
            $this->combinationService->syncCombinationsForAssessment($assessment);

            DB::commit();

            return redirect()->route('assessment.index')->with('message', 'update');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['assessment' => 'Terjadi kesalahan saat memperbarui data assessment.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorizeAccess();

        $assessment = Assessment::findOrFail($id);
        $assessment->delete();

        return response()->json([
            'status' => true,
        ]);
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }

    private function fieldTypes(): array
    {
        return [
            'text' => 'Teks',
            'textarea' => 'Area Teks',
            'number' => 'Angka',
            'email' => 'Email',
            'date' => 'Tanggal',
            'select' => 'Daftar Pilihan',
            'radio' => 'Pilihan Ganda',
            'checkbox' => 'Kotak Centang',
            'file' => 'Unggah File',
            'repeater' => 'Tabel Berulang',
        ];
    }

    private function validatePayload(Request $request, ?int $assessmentId = null): array
    {
        $this->mergeSerializedFormsPayload($request);

        $validator = Validator::make(
            $request->all(),
            [
                'forms_payload' => 'nullable|string',
                'kode_assessment' => [
                    'nullable',
                    'string',
                    'max:100',
                    Rule::unique('assessments', 'kode_assessment')->ignore($assessmentId),
                ],
                'judul' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'petunjuk' => 'nullable|string',
                'target_ketenagaan' => [
                    'required',
                    'string',
                    Rule::in(array_keys(AssessmentKetenagaanType::options())),
                ],
                'instrument_type' => [
                    'nullable',
                    'string',
                    Rule::in(array_keys(AssessmentInstrumentType::options())),
                ],
                'status' => 'required|in:draft,publish,nonaktif',
                'is_active' => 'nullable|boolean',
                'forms' => 'required|array|min:1',
                'forms.*.id' => 'nullable|integer',
                'forms.*.judul_form' => 'required|string|max:255',
                'forms.*.kode_form' => 'nullable|string|max:100',
                'forms.*.deskripsi' => 'nullable|string',
                'forms.*.kompetensi' => [
                    'nullable',
                    'string',
                    Rule::in(array_keys(KompetensiGuru::options())),
                ],
                'forms.*.indikator_kode' => 'nullable|string|max:100',
                'forms.*.indikator_label' => 'nullable|string|max:255',
                'forms.*.is_scoreable' => 'nullable|boolean',
                'forms.*.urutan' => 'nullable|integer|min:1',
                'forms.*.is_active' => 'nullable|boolean',
                'forms.*.scoring' => 'nullable|array',
                'forms.*.scoring.profile' => 'nullable|string|max:100',
                'forms.*.scoring.weight' => 'nullable|numeric|min:0',
                'forms.*.scoring.exclude_from_competency' => 'nullable|boolean',
                'forms.*.scoring.advanced_rules_text' => 'nullable|string',
                'forms.*.fields' => 'required|array|min:1',
                'forms.*.fields.*.id' => 'nullable|integer',
                'forms.*.fields.*.label' => 'required|string|max:255',
                'forms.*.fields.*.deskripsi' => 'nullable|string',
                'forms.*.fields.*.tipe_field' => [
                    'required',
                    'string',
                    Rule::in(array_keys($this->fieldTypes())),
                ],
                'forms.*.fields.*.placeholder' => 'nullable|string|max:255',
                'forms.*.fields.*.bantuan' => 'nullable|string',
                'forms.*.fields.*.autofill_source' => [
                    'nullable',
                    'string',
                    Rule::in(array_keys($this->participantAutoFillResolver->options())),
                ],
                'forms.*.fields.*.lookup_source' => [
                    'nullable',
                    'string',
                    Rule::in(array_keys($this->fieldLookupResolver->options())),
                ],
                'forms.*.fields.*.allow_other_input' => 'nullable|boolean',
                'forms.*.fields.*.opsi_field_text' => 'nullable|string',
                'forms.*.fields.*.opsi_score_text' => 'nullable|string',
                'forms.*.fields.*.repeater_config_text' => 'nullable|string',
                'forms.*.fields.*.file_input_mode' => [
                    'nullable',
                    'string',
                    Rule::in(['file', 'link']),
                ],
                'forms.*.fields.*.raw_opsi_field_json' => 'nullable|string',
                'forms.*.fields.*.radio_options' => 'nullable|array',
                'forms.*.fields.*.radio_options.*.label' => 'nullable|string|max:1000',
                'forms.*.fields.*.radio_options.*.value' => 'nullable|string|max:255',
                'forms.*.fields.*.radio_options.*.score' => 'nullable|numeric|min:0|max:5',
                'forms.*.fields.*.radio_options.*.level_kompetensi' => [
                    'nullable',
                    'integer',
                    Rule::in(LevelKompetensi::values()),
                ],
                'forms.*.fields.*.scoring' => 'nullable|array',
                'forms.*.fields.*.scoring.enabled' => 'nullable|boolean',
                'forms.*.fields.*.scoring.profile' => 'nullable|string|max:100',
                'forms.*.fields.*.scoring.method' => [
                    'nullable',
                    'string',
                    Rule::in([
                        'presence',
                        'choice_option_score',
                        'choice_option_average',
                        'choice_option_sum',
                        'choice_option_max',
                        'numeric_threshold',
                        'numeric_range',
                        'semantic_similarity',
                        'keyword_coverage',
                        'repeater_completeness',
                    ]),
                ],
                'forms.*.fields.*.scoring.rubric_code' => 'nullable|string|max:100',
                'forms.*.fields.*.scoring.weight' => 'nullable|numeric|min:0',
                'forms.*.fields.*.scoring.score_if_answered' => 'nullable|numeric|min:0|max:5',
                'forms.*.fields.*.scoring.scale_min' => 'nullable|numeric|min:0|max:5',
                'forms.*.fields.*.scoring.scale_max' => 'nullable|numeric|min:0|max:5',
                'forms.*.fields.*.scoring.min_words' => 'nullable|integer|min:0',
                'forms.*.fields.*.scoring.confidence_threshold' => 'nullable|numeric|min:0|max:1',
                'forms.*.fields.*.scoring.manual_review_below_confidence' => 'nullable|boolean',
                'forms.*.fields.*.scoring.numeric_direction' => 'nullable|in:greater_is_better,lower_is_better,range',
                'forms.*.fields.*.scoring.min_threshold' => 'nullable|numeric',
                'forms.*.fields.*.scoring.target_threshold' => 'nullable|numeric',
                'forms.*.fields.*.scoring.max_threshold' => 'nullable|numeric',
                'forms.*.fields.*.scoring.min_score' => 'nullable|numeric|min:0|max:5',
                'forms.*.fields.*.scoring.target_score' => 'nullable|numeric|min:0|max:5',
                'forms.*.fields.*.scoring.max_score' => 'nullable|numeric|min:0|max:5',
                'forms.*.fields.*.scoring.reference_answer' => 'nullable|string',
                'forms.*.fields.*.scoring.keyword_groups_text' => 'nullable|string',
                'forms.*.fields.*.scoring.synonym_map_text' => 'nullable|string',
                'forms.*.fields.*.scoring.advanced_rules_text' => 'nullable|string',
                'forms.*.fields.*.lebar_kolom' => [
                    'nullable',
                    'string',
                    Rule::in(['col-md-12', 'col-md-8', 'col-md-6', 'col-md-4']),
                ],
                'forms.*.fields.*.urutan' => 'nullable|integer|min:1',
                'forms.*.fields.*.is_required' => 'nullable|boolean',
                'forms.*.fields.*.is_active' => 'nullable|boolean',
            ],
            [
                'kode_assessment.unique' => 'Kode assessment sudah digunakan.',
                'judul.required' => 'Judul assessment wajib diisi.',
                'forms.required' => 'Minimal harus ada satu form.',
                'forms.*.judul_form.required' => 'Judul form wajib diisi.',
                'forms.*.fields.required' => 'Setiap form minimal memiliki satu pertanyaan.',
                'forms.*.fields.*.label.required' => 'Label pertanyaan wajib diisi.',
                'forms.*.fields.*.tipe_field.required' => 'Tipe Pertanyaan wajib dipilih.',
            ]
        );

        $validator->after(function ($validator) use ($request, $assessmentId) {
            $forms = $request->input('forms', []);
            $fieldTypesWithTextOptions = ['select', 'checkbox'];
            $targetKetenagaan = $request->input('target_ketenagaan');

            foreach ($forms as $formIndex => $form) {
                $usedFieldNames = [];

                if ((bool) ($form['is_scoreable'] ?? true) && blank($form['kompetensi'] ?? null)) {
                    $validator->errors()->add(
                        "forms.$formIndex.kompetensi",
                        'Kompetensi wajib dipilih untuk form yang ikut dihitung pada penilaian.'
                    );
                }

                foreach (($form['fields'] ?? []) as $fieldIndex => $field) {
                    $namaField = $this->generateFieldNameFromLabel($field['label'] ?? '');
                    $lookupSource = $this->resolveFieldLookupSource(
                        $field['lookup_source'] ?? null,
                        $field['tipe_field'] ?? null,
                        $field['label'] ?? null,
                        $namaField,
                        $targetKetenagaan
                    );

                    if ($namaField === '') {
                        $validator->errors()->add(
                            "forms.$formIndex.fields.$fieldIndex.label",
                            'Label field harus mengandung huruf atau angka agar nama field otomatis bisa dibuat.'
                        );
                    }

                    if (in_array($namaField, $usedFieldNames, true)) {
                        $validator->errors()->add(
                            "forms.$formIndex.fields.$fieldIndex.label",
                            'Label field tidak boleh sama. Ubah label agar berbeda.'
                        );
                    }

                    if ($namaField !== '') {
                        $usedFieldNames[] = $namaField;
                    }

                    if (($field['tipe_field'] ?? '') === 'radio') {
                        $radioOptions = ChoiceOptionNormalizer::normalizeMany($field['radio_options'] ?? []);

                        if (count($radioOptions) < 2) {
                            $validator->errors()->add(
                                "forms.$formIndex.fields.$fieldIndex.radio_options",
                                'Pilihan ganda wajib memiliki minimal dua opsi.'
                            );
                        }

                        $usedOptionCodes = [];

                        foreach ($radioOptions as $optionIndex => $option) {
                            $optionLabel = trim((string) ($option['label'] ?? ''));
                            $optionValue = trim((string) ($option['value'] ?? ''));

                            if ($optionLabel === '') {
                                $validator->errors()->add(
                                    "forms.$formIndex.fields.$fieldIndex.radio_options.$optionIndex.label",
                                    'Isi jawaban pilihan ganda wajib diisi.'
                                );
                            }

                            if ($optionValue === '') {
                                $validator->errors()->add(
                                    "forms.$formIndex.fields.$fieldIndex.radio_options.$optionIndex.value",
                                    'Kode jawaban pilihan ganda wajib diisi.'
                                );
                            }

                            if (! LevelKompetensi::tryFromMixed($option['level_kompetensi'] ?? null)) {
                                $validator->errors()->add(
                                    "forms.$formIndex.fields.$fieldIndex.radio_options.$optionIndex.level_kompetensi",
                                    'Level kompetensi pilihan ganda wajib dipilih.'
                                );
                            }

                            if ($optionValue !== '' && in_array(Str::upper($optionValue), $usedOptionCodes, true)) {
                                $validator->errors()->add(
                                    "forms.$formIndex.fields.$fieldIndex.radio_options.$optionIndex.value",
                                    'Kode jawaban pilihan ganda harus unik.'
                                );
                            }

                            if ($optionValue !== '') {
                                $usedOptionCodes[] = Str::upper($optionValue);
                            }
                        }
                    }

                    if (
                        ($field['tipe_field'] ?? '') !== 'select'
                        && filled($field['lookup_source'] ?? null)
                    ) {
                        $validator->errors()->add(
                            "forms.$formIndex.fields.$fieldIndex.lookup_source",
                            'Lookup opsi database hanya tersedia untuk field daftar pilihan.'
                        );
                    }

                    if (
                        ($field['tipe_field'] ?? '') !== 'select'
                        && (bool) ($field['allow_other_input'] ?? false)
                    ) {
                        $validator->errors()->add(
                            "forms.$formIndex.fields.$fieldIndex.allow_other_input",
                            'Opsi jawaban "Lainnya" dengan input manual hanya tersedia untuk field daftar pilihan.'
                        );
                    }

                    if (in_array($field['tipe_field'] ?? '', $fieldTypesWithTextOptions, true)) {
                        if (($field['tipe_field'] ?? '') === 'select' && $lookupSource) {
                            if ($this->fieldLookupResolver->resolveOptions($lookupSource) === []) {
                                $validator->errors()->add(
                                    "forms.$formIndex.fields.$fieldIndex.lookup_source",
                                    'Sumber lookup database belum memiliki data yang bisa dipakai sebagai opsi.'
                                );
                            }
                        } elseif (blank($field['opsi_field_text'] ?? null)) {
                            $validator->errors()->add(
                                "forms.$formIndex.fields.$fieldIndex.opsi_field_text",
                                ($field['tipe_field'] ?? '') === 'select'
                                    ? 'Isi opsi manual atau pilih lookup database untuk field daftar pilihan.'
                                    : 'Opsi wajib diisi untuk field kotak centang.'
                            );
                        }
                    }

                    if (($field['tipe_field'] ?? '') === 'repeater') {
                        $repeaterValidation = $this->validateRepeaterConfigText($field['repeater_config_text'] ?? null);

                        if (! $repeaterValidation['valid']) {
                            $validator->errors()->add(
                                "forms.$formIndex.fields.$fieldIndex.repeater_config_text",
                                $repeaterValidation['message']
                            );
                        }
                    }

                    $keywordGroupsValidation = $this->validateKeywordGroupsText(data_get($field, 'scoring.keyword_groups_text'));

                    if (! $keywordGroupsValidation['valid']) {
                        $validator->errors()->add(
                            "forms.$formIndex.fields.$fieldIndex.scoring.keyword_groups_text",
                            $keywordGroupsValidation['message']
                        );
                    }
                }
            }

            if (! $assessmentId) {
                return;
            }

            $existingForms = AssessmentForm::query()
                ->with('fields:id,assessment_form_id')
                ->where('assessment_id', $assessmentId)
                ->get()
                ->keyBy('id');

            foreach ($forms as $formIndex => $form) {
                $submittedFormId = (int) ($form['id'] ?? 0);

                if ($submittedFormId > 0 && ! $existingForms->has($submittedFormId)) {
                    $validator->errors()->add(
                        "forms.$formIndex.id",
                        'Form assessment yang dipilih tidak valid.'
                    );

                    continue;
                }

                $existingFieldIds = $submittedFormId > 0
                    ? $existingForms
                        ->get($submittedFormId)
                        ?->fields
                        ->pluck('assessment_form_id', 'id')
                        ->all() ?? []
                    : [];

                foreach (($form['fields'] ?? []) as $fieldIndex => $field) {
                    $submittedFieldId = (int) ($field['id'] ?? 0);

                    if ($submittedFieldId < 1) {
                        continue;
                    }

                    if ($submittedFormId < 1 || ! array_key_exists($submittedFieldId, $existingFieldIds)) {
                        $validator->errors()->add(
                            "forms.$formIndex.fields.$fieldIndex.id",
                            'Field assessment yang dipilih tidak valid.'
                        );
                    }
                }
            }
        });

        return $validator->validate();
    }

    private function syncForms(Assessment $assessment, array $forms): void
    {
        $existingForms = $assessment->forms()
            ->with('fields')
            ->get()
            ->keyBy('id');
        $retainedFormIds = [];

        foreach (array_values($forms) as $formIndex => $formData) {
            $formAttributes = [
                'judul_form' => $formData['judul_form'],
                'kode_form' => $formData['kode_form'] ?: 'FORM-'.str_pad((string) ($formIndex + 1), 2, '0', STR_PAD_LEFT),
                'deskripsi' => $formData['deskripsi'] ?? null,
                'kompetensi' => $formData['kompetensi'] ?? null,
                'indikator_kode' => $formData['indikator_kode'] ?? null,
                'indikator_label' => $formData['indikator_label'] ?? null,
                'is_scoreable' => (bool) ($formData['is_scoreable'] ?? true),
                'scoring_config' => $this->parseFormScoringConfig($formData, $assessment->instrument_type),
                'urutan' => (int) ($formData['urutan'] ?? ($formIndex + 1)),
                'is_active' => (bool) ($formData['is_active'] ?? false),
            ];
            $submittedFormId = (int) ($formData['id'] ?? 0);

            if ($submittedFormId > 0 && $existingForms->has($submittedFormId)) {
                $form = $existingForms->get($submittedFormId);
                $form->update($formAttributes);
            } else {
                $form = $assessment->forms()->create($formAttributes);
            }

            $retainedFormIds[] = (int) $form->id;
            $this->syncFormFields(
                $form,
                $formData['fields'] ?? [],
                $assessment->instrument_type,
                $assessment->target_ketenagaan
            );
        }

        $formIdsToDelete = $existingForms
            ->keys()
            ->reject(fn ($formId) => in_array((int) $formId, $retainedFormIds, true))
            ->values()
            ->all();

        if ($formIdsToDelete !== []) {
            $assessment->forms()->whereIn('id', $formIdsToDelete)->delete();
        }
    }

    private function syncFormFields(
        AssessmentForm $form,
        array $fields,
        ?string $instrumentType = null,
        ?string $targetKetenagaan = null
    ): void
    {
        $existingFields = $form->fields()
            ->get()
            ->keyBy('id');
        $retainedFieldIds = [];

        foreach (array_values($fields) as $fieldIndex => $fieldData) {
            $generatedFieldName = $this->generateFieldNameFromLabel($fieldData['label'] ?? '');
            $fieldAttributes = [
                'label' => $fieldData['label'],
                'deskripsi' => $fieldData['deskripsi'] ?? null,
                'nama_field' => $generatedFieldName,
                'tipe_field' => $fieldData['tipe_field'],
                'placeholder' => $fieldData['placeholder'] ?? null,
                'bantuan' => $fieldData['bantuan'] ?? null,
                'opsi_field' => $this->parseFieldOptions($fieldData, $targetKetenagaan, $generatedFieldName),
                'nilai_default' => null,
                'autofill_source' => $this->resolveFieldAutoFillSource(
                    $fieldData['autofill_source'] ?? null,
                    $fieldData['tipe_field'] ?? null,
                    $fieldData['label'] ?? null,
                    $generatedFieldName
                ),
                'lookup_source' => $this->resolveFieldLookupSource(
                    $fieldData['lookup_source'] ?? null,
                    $fieldData['tipe_field'] ?? null,
                    $fieldData['label'] ?? null,
                    $generatedFieldName,
                    $targetKetenagaan
                ),
                'validasi' => [
                    'required' => (bool) ($fieldData['is_required'] ?? false),
                    'tipe_field' => $fieldData['tipe_field'],
                    'allow_other_input' => ($fieldData['tipe_field'] ?? null) === 'select'
                        ? (bool) ($fieldData['allow_other_input'] ?? false)
                        : false,
                ],
                'scoring_config' => $this->parseFieldScoringConfig($fieldData, $instrumentType),
                'lebar_kolom' => $fieldData['lebar_kolom'] ?? 'col-md-12',
                'urutan' => (int) ($fieldData['urutan'] ?? ($fieldIndex + 1)),
                'is_required' => (bool) ($fieldData['is_required'] ?? false),
                'is_active' => (bool) ($fieldData['is_active'] ?? false),
            ];
            $submittedFieldId = (int) ($fieldData['id'] ?? 0);

            if ($submittedFieldId > 0 && $existingFields->has($submittedFieldId)) {
                $field = $existingFields->get($submittedFieldId);
                $field->update($fieldAttributes);
            } else {
                $field = $form->fields()->create($fieldAttributes);
            }

            $retainedFieldIds[] = (int) $field->id;
        }

        $fieldIdsToDelete = $existingFields
            ->keys()
            ->reject(fn ($fieldId) => in_array((int) $fieldId, $retainedFieldIds, true))
            ->values()
            ->all();

        if ($fieldIdsToDelete !== []) {
            $form->fields()->whereIn('id', $fieldIdsToDelete)->delete();
        }
    }

    private function parseFieldOptions(
        array $fieldData,
        ?string $targetKetenagaan = null,
        ?string $fieldName = null
    ): ?array
    {
        $fieldType = $fieldData['tipe_field'] ?? null;

        if ($fieldType === 'file') {
            return $this->parseFileFieldOptions($fieldData);
        }

        if (! in_array($fieldType, ['select', 'radio', 'checkbox', 'repeater'], true)) {
            return null;
        }

        if ($fieldType === 'radio') {
            $options = collect(ChoiceOptionNormalizer::normalizeMany($fieldData['radio_options'] ?? []))
                ->map(fn ($option) => [
                    'label' => trim((string) ($option['label'] ?? '')),
                    'value' => trim((string) ($option['value'] ?? '')),
                    'score' => is_numeric($option['score'] ?? null) ? (float) $option['score'] : null,
                    'level_kompetensi' => LevelKompetensi::tryFromMixed($option['level_kompetensi'] ?? null)?->value,
                ])
                ->filter(fn ($option) => $option['label'] !== '' && $option['value'] !== '')
                ->values()
                ->toArray();

            return $options === [] ? null : $options;
        }

        if ($fieldType === 'repeater') {
            return $this->parseRepeaterConfigText($fieldData['repeater_config_text'] ?? null);
        }

        $lookupSource = $this->resolveFieldLookupSource(
            $fieldData['lookup_source'] ?? null,
            $fieldType,
            $fieldData['label'] ?? null,
            $fieldName,
            $targetKetenagaan
        );
        $options = $lookupSource
            ? collect($this->fieldLookupResolver->resolveOptions($lookupSource))
                ->pluck('label')
                ->map(fn ($label) => trim((string) $label))
                ->filter()
                ->values()
                ->all()
            : array_values(array_filter(array_map(
                'trim',
                preg_split('/[\r\n,]+/', (string) ($fieldData['opsi_field_text'] ?? null)) ?: []
            )));
        $scoreMap = $this->parseOptionScoreText($fieldData['opsi_score_text'] ?? null, $options);

        if ($options === []) {
            return null;
        }

        return collect($options)
            ->values()
            ->map(function (string $optionLabel, int $index) use ($scoreMap) {
                $optionKey = Str::lower(trim($optionLabel));
                $score = $scoreMap[$optionKey] ?? $scoreMap[$index] ?? null;

                return array_filter([
                    'label' => $optionLabel,
                    'value' => $optionLabel,
                    'score' => is_numeric($score) ? (float) $score : null,
                ], static fn ($value) => $value !== null && $value !== '');
            })
            ->values()
            ->all();
    }

    private function parseRawFieldOptionsJson(?string $rawOptions): ?array
    {
        $decoded = json_decode((string) $rawOptions, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function parseFileFieldOptions(array $fieldData): ?array
    {
        $rawConfig = $this->parseRawFieldOptionsJson($fieldData['raw_opsi_field_json'] ?? null);
        $rawConfig = is_array($rawConfig) ? $rawConfig : [];
        $inputMode = $this->normalizeFileInputMode($fieldData['file_input_mode'] ?? data_get($rawConfig, 'input_mode'));
        $accept = collect(Arr::wrap($rawConfig['accept'] ?? []))
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();
        $maxSizeKb = is_numeric($rawConfig['max_size_kb'] ?? null)
            ? max((int) $rawConfig['max_size_kb'], 1)
            : 5120;
        $maxFiles = is_numeric($rawConfig['max_files'] ?? null)
            ? max((int) $rawConfig['max_files'], 1)
            : 1;

        return array_filter([
            'input_mode' => $inputMode,
            'accept' => $accept !== [] ? $accept : null,
            'max_size_kb' => $inputMode === 'file' ? $maxSizeKb : null,
            'max_files' => $inputMode === 'file' ? $maxFiles : null,
        ], static fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param  array<int, string>  $options
     * @return array<int|string, float>
     */
    private function parseOptionScoreText(?string $rawScoreText, array $options): array
    {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) $rawScoreText))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values();

        if ($lines->isEmpty()) {
            return [];
        }

        if ($lines->every(fn ($line) => preg_match('/[:=]/', $line) === 1)) {
            return $lines->mapWithKeys(function ($line) {
                [$rawKey, $rawValue] = array_pad(preg_split('/\s*[:=]\s*/', (string) $line, 2), 2, null);
                $optionKey = Str::lower(trim((string) $rawKey));

                if ($optionKey === '' || ! is_numeric($rawValue)) {
                    return [];
                }

                return [$optionKey => (float) $rawValue];
            })->all();
        }

        $numericLines = $lines
            ->filter(fn ($line) => is_numeric($line))
            ->values();

        if ($numericLines->count() !== count($options)) {
            return [];
        }

        return $numericLines
            ->mapWithKeys(fn ($line, $index) => [$index => (float) $line])
            ->all();
    }

    private function parseRepeaterConfigText(mixed $rawConfig): ?array
    {
        if (is_array($rawConfig)) {
            $decoded = $rawConfig;
        } else {
            $decoded = json_decode((string) $rawConfig, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return null;
            }
        }

        $allowedColumnTypes = ['text', 'textarea', 'number', 'email', 'date', 'url', 'select'];

        $columns = collect($decoded['columns'] ?? [])
            ->filter(fn ($column) => is_array($column))
            ->map(function (array $column) use ($allowedColumnTypes) {
                $fieldName = Str::slug(trim((string) ($column['nama_field'] ?? '')), '_');
                $fieldType = trim((string) ($column['tipe_field'] ?? 'text')) ?: 'text';
                $fieldType = in_array($fieldType, $allowedColumnTypes, true) ? $fieldType : 'text';
                $options = collect(Arr::wrap($column['opsi_field'] ?? []))
                    ->map(function ($option) {
                        if (is_array($option)) {
                            $option = $option['label'] ?? $option['value'] ?? null;
                        }

                        return trim((string) $option);
                    })
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'label' => trim((string) ($column['label'] ?? '')),
                    'nama_field' => $fieldName,
                    'tipe_field' => $fieldType,
                    'placeholder' => trim((string) ($column['placeholder'] ?? '')),
                    'opsi_field' => $fieldType === 'select' ? $options : [],
                    'is_required' => (bool) ($column['is_required'] ?? false),
                ];
            })
            ->values()
            ->all();

        if ($columns === []) {
            return null;
        }

        return [
            'min_rows' => max((int) ($decoded['min_rows'] ?? 0), 0),
            'max_rows' => max((int) ($decoded['max_rows'] ?? 0), 0),
            'columns' => $columns,
        ];
    }

    private function validateRepeaterConfigText(mixed $rawConfig): array
    {
        $parsedConfig = $this->parseRepeaterConfigText($rawConfig);

        if (! $parsedConfig) {
            return [
                'valid' => false,
                'message' => 'Konfigurasi tabel berulang wajib berupa JSON valid dan minimal memiliki satu kolom.',
            ];
        }

        $columns = collect($parsedConfig['columns'] ?? []);
        $minRows = max((int) ($parsedConfig['min_rows'] ?? 0), 0);
        $maxRows = max((int) ($parsedConfig['max_rows'] ?? 0), 0);

        if ($maxRows > 0 && $maxRows < $minRows) {
            return [
                'valid' => false,
                'message' => 'Maksimal baris tabel berulang tidak boleh lebih kecil dari minimal baris.',
            ];
        }

        if ($columns->isEmpty()) {
            return [
                'valid' => false,
                'message' => 'Tambahkan minimal satu kolom pada tabel berulang.',
            ];
        }

        foreach ($columns as $column) {
            $columnLabel = trim((string) ($column['label'] ?? ''));
            $columnName = trim((string) ($column['nama_field'] ?? ''));
            $columnType = trim((string) ($column['tipe_field'] ?? 'text')) ?: 'text';

            if ($columnLabel === '') {
                return [
                    'valid' => false,
                    'message' => 'Label kolom pada tabel berulang wajib diisi.',
                ];
            }

            if ($columnName === '') {
                return [
                    'valid' => false,
                    'message' => 'Nama field kolom pada tabel berulang wajib diisi.',
                ];
            }

            if (! in_array($columnType, ['text', 'textarea', 'number', 'email', 'date', 'url', 'select'], true)) {
                return [
                    'valid' => false,
                    'message' => "Tipe input untuk kolom {$columnLabel} tidak dikenali.",
                ];
            }

            if ($columnType === 'select' && collect($column['opsi_field'] ?? [])->filter()->isEmpty()) {
                return [
                    'valid' => false,
                    'message' => "Kolom {$columnLabel} bertipe daftar pilihan wajib memiliki minimal satu opsi.",
                ];
            }
        }

        $duplicateNames = $columns
            ->pluck('nama_field')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->duplicates()
            ->values();

        if ($duplicateNames->isNotEmpty()) {
            return [
                'valid' => false,
                'message' => 'Nama field kolom pada tabel berulang harus unik.',
            ];
        }

        return [
            'valid' => true,
            'message' => null,
        ];
    }

    private function buildAssessmentScoringConfig(?string $instrumentType): ?array
    {
        $instrument = AssessmentInstrumentType::tryFromMixed($instrumentType);

        if (! $instrument) {
            return null;
        }

        return [
            'profile' => $instrument->value,
            'weight' => $instrument->weight(),
            'verification_gap_threshold' => 1.5,
            'empty_response_threshold_percent' => 10,
        ];
    }

    private function parseFormScoringConfig(array $formData, ?string $instrumentType = null): ?array
    {
        $instrument = AssessmentInstrumentType::tryFromMixed($instrumentType);
        $rawConfig = is_array($formData['scoring'] ?? null) ? $formData['scoring'] : [];
        $config = array_filter([
            'profile' => trim((string) ($rawConfig['profile'] ?? $instrument?->value ?? '')),
            'weight' => is_numeric($rawConfig['weight'] ?? null) ? (float) $rawConfig['weight'] : null,
            'exclude_from_competency' => (bool) ($rawConfig['exclude_from_competency'] ?? false),
            'advanced_rules_text' => trim((string) ($rawConfig['advanced_rules_text'] ?? '')),
        ], fn ($value, $key) => match ($key) {
            'exclude_from_competency' => (bool) $value,
            default => $value !== null && $value !== '',
        }, ARRAY_FILTER_USE_BOTH);

        return $config === [] ? null : $config;
    }

    private function parseFieldScoringConfig(array $fieldData, ?string $instrumentType = null): ?array
    {
        $instrument = AssessmentInstrumentType::tryFromMixed($instrumentType);
        $rawConfig = is_array($fieldData['scoring'] ?? null) ? $fieldData['scoring'] : [];
        $fieldType = $fieldData['tipe_field'] ?? 'text';
        $config = [
            'enabled' => (bool) ($rawConfig['enabled'] ?? false),
            'profile' => trim((string) ($rawConfig['profile'] ?? $instrument?->value ?? '')) ?: null,
            'method' => trim((string) ($rawConfig['method'] ?? '')) ?: null,
            'rubric_code' => trim((string) ($rawConfig['rubric_code'] ?? '')) ?: null,
            'weight' => is_numeric($rawConfig['weight'] ?? null) ? (float) $rawConfig['weight'] : null,
            'score_if_answered' => is_numeric($rawConfig['score_if_answered'] ?? null) ? (float) $rawConfig['score_if_answered'] : null,
            'scale_min' => is_numeric($rawConfig['scale_min'] ?? null) ? (float) $rawConfig['scale_min'] : null,
            'scale_max' => is_numeric($rawConfig['scale_max'] ?? null) ? (float) $rawConfig['scale_max'] : null,
            'reference_answer' => trim((string) ($rawConfig['reference_answer'] ?? '')) ?: null,
            'keyword_groups_text' => $this->normalizeKeywordGroupsText($rawConfig['keyword_groups_text'] ?? null),
            'synonym_map_text' => trim((string) ($rawConfig['synonym_map_text'] ?? '')) ?: null,
            'min_words' => is_numeric($rawConfig['min_words'] ?? null) ? (int) $rawConfig['min_words'] : null,
            'confidence_threshold' => is_numeric($rawConfig['confidence_threshold'] ?? null) ? (float) $rawConfig['confidence_threshold'] : null,
            'manual_review_below_confidence' => false,
            'numeric_direction' => trim((string) ($rawConfig['numeric_direction'] ?? '')) ?: null,
            'min_threshold' => is_numeric($rawConfig['min_threshold'] ?? null) ? (float) $rawConfig['min_threshold'] : null,
            'target_threshold' => is_numeric($rawConfig['target_threshold'] ?? null) ? (float) $rawConfig['target_threshold'] : null,
            'max_threshold' => is_numeric($rawConfig['max_threshold'] ?? null) ? (float) $rawConfig['max_threshold'] : null,
            'min_score' => is_numeric($rawConfig['min_score'] ?? null) ? (float) $rawConfig['min_score'] : null,
            'target_score' => is_numeric($rawConfig['target_score'] ?? null) ? (float) $rawConfig['target_score'] : null,
            'max_score' => is_numeric($rawConfig['max_score'] ?? null) ? (float) $rawConfig['max_score'] : null,
            'advanced_rules_text' => trim((string) ($rawConfig['advanced_rules_text'] ?? '')) ?: null,
        ];

        if (in_array($fieldType, ['radio', 'select', 'checkbox'], true) && empty($config['method'])) {
            $config['method'] = $fieldType === 'checkbox' ? 'choice_option_average' : 'choice_option_score';
        }

        if (in_array($fieldType, ['text', 'textarea'], true) && empty($config['method']) && filled($config['reference_answer'] ?? null)) {
            $config['method'] = 'semantic_similarity';
        }

        if ($fieldType === 'number' && empty($config['method'])) {
            $config['method'] = 'numeric_threshold';
        }

        if ($fieldType === 'repeater' && empty($config['method'])) {
            $config['method'] = 'repeater_completeness';
        }

        $config = $this->applyScoringGuidanceSuggestions($fieldData, $config);

        $config = array_filter($config, function ($value, $key) {
            return match ($key) {
                'enabled', 'manual_review_below_confidence' => $key === 'enabled',
                default => $value !== null && $value !== '',
            };
        }, ARRAY_FILTER_USE_BOTH);

        return $config === [] ? null : $config;
    }

    private function applyScoringGuidanceSuggestions(array $fieldData, array $config): array
    {
        $fieldType = trim((string) ($fieldData['tipe_field'] ?? 'text')) ?: 'text';
        $method = trim((string) ($config['method'] ?? ''));
        $supportsGuidanceAssistant = (
            in_array($fieldType, ['text', 'textarea'], true)
            && in_array($method, ['semantic_similarity', 'keyword_coverage'], true)
        ) || ($fieldType === 'repeater' && $method === 'repeater_completeness');

        if (! $supportsGuidanceAssistant) {
            return $config;
        }

        $sourceText = collect([
            trim((string) ($config['reference_answer'] ?? '')),
            trim((string) ($fieldData['deskripsi'] ?? '')),
            trim((string) ($fieldData['bantuan'] ?? '')),
        ])->filter()->unique()->implode("\n");

        if ($sourceText === '') {
            return $config;
        }

        $repeaterConfig = $fieldType === 'repeater'
            ? $this->parseRepeaterConfigText($fieldData['repeater_config_text'] ?? null)
            : null;

        $suggestions = (new ScoringGuidanceAssistant)->suggest($sourceText, $fieldType, [
            'target_rows' => (int) data_get($repeaterConfig, 'min_rows', 1),
        ]);

        if (blank($config['keyword_groups_text'] ?? null) && filled($suggestions['keyword_groups_text'] ?? null)) {
            $config['keyword_groups_text'] = $suggestions['keyword_groups_text'];
        }

        if (blank($config['synonym_map_text'] ?? null) && filled($suggestions['synonym_map_text'] ?? null)) {
            $config['synonym_map_text'] = $suggestions['synonym_map_text'];
        }

        if (! is_numeric($config['min_words'] ?? null) && is_numeric($suggestions['min_words'] ?? null)) {
            $config['min_words'] = (int) $suggestions['min_words'];
        }

        if (
            blank($config['advanced_rules_text'] ?? null)
            && empty($config['advanced_rules'] ?? [])
            && ($suggestions['advanced_rules'] ?? []) !== []
        ) {
            $config['advanced_rules'] = $suggestions['advanced_rules'];
        }

        return $config;
    }

    private function generateOptionLabel(int $index): string
    {
        $label = '';
        $number = $index + 1;

        while ($number > 0) {
            $number--;
            $label = chr(65 + ($number % 26)).$label;
            $number = intdiv($number, 26);
        }

        return $label;
    }

    private function mergeSerializedFormsPayload(Request $request): void
    {
        $formsPayload = $request->input('forms_payload');

        if (! is_string($formsPayload) || trim($formsPayload) === '') {
            return;
        }

        $decodedPayload = json_decode($formsPayload, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decodedPayload)) {
            return;
        }

        $request->merge([
            'forms' => $decodedPayload,
        ]);
    }

    private function generateFieldNameFromLabel(?string $label): string
    {
        return Str::slug((string) $label, '_');
    }

    private function resolveFieldAutoFillSource(
        ?string $source,
        ?string $fieldType,
        ?string $label,
        ?string $fieldName = null
    ): ?string {
        $normalizedSource = $this->participantAutoFillResolver->normalizeSource($source, $fieldType);

        if ($normalizedSource) {
            return $normalizedSource;
        }

        return $this->participantAutoFillResolver->normalizeSource(
            $this->participantAutoFillResolver->inferSourceFromField($label, $fieldName),
            $fieldType
        );
    }

    private function resolveFieldLookupSource(
        ?string $source,
        ?string $fieldType,
        ?string $label,
        ?string $fieldName = null,
        ?string $targetKetenagaan = null
    ): ?string {
        $normalizedSource = $this->fieldLookupResolver->normalizeSource($source, $fieldType);

        if ($normalizedSource) {
            return $normalizedSource;
        }

        return $this->fieldLookupResolver->normalizeSource(
            $this->fieldLookupResolver->inferSourceFromField($label, $fieldName, $targetKetenagaan),
            $fieldType
        );
    }

    private function normalizeFileInputMode(mixed $value): string
    {
        $mode = trim((string) $value);

        return in_array($mode, ['file', 'link'], true) ? $mode : 'file';
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Assessment::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function resolveAssessmentCode(
        ?string $rawCode,
        string $title,
        ?string $instrumentType = null,
        ?int $ignoreId = null,
        ?string $existingCode = null
    ): string {
        $manualCode = Str::upper(trim((string) $rawCode));

        if ($manualCode !== '') {
            return $manualCode;
        }

        $existingCode = trim((string) $existingCode);

        if ($existingCode !== '') {
            return $existingCode;
        }

        $baseCode = $this->buildAssessmentCodePrefix($title, $instrumentType);
        $counter = 1;

        do {
            $candidate = sprintf('%s-%03d', $baseCode, $counter);
            $exists = Assessment::query()
                ->where('kode_assessment', $candidate)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists();
            $counter++;
        } while ($exists);

        return $candidate;
    }

    private function buildAssessmentCodePrefix(string $title, ?string $instrumentType = null): string
    {
        $instrumentPrefix = match (AssessmentInstrumentType::tryFromMixed($instrumentType)) {
            AssessmentInstrumentType::PORTOFOLIO => 'PORTOFOLIO',
            AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS => 'PG',
            AssessmentInstrumentType::STUDI_KASUS => 'STUDI-KASUS',
            AssessmentInstrumentType::MONITORING_OBSERVASI_EVIDEN => 'MOE',
            default => null,
        };

        if ($instrumentPrefix) {
            return 'ASM-'.$instrumentPrefix;
        }

        $titleTokens = collect(explode('-', Str::slug($title)))
            ->filter()
            ->take(4)
            ->map(fn ($token) => Str::upper($token))
            ->values()
            ->all();

        return 'ASM-'.implode('-', $titleTokens ?: ['ASSESSMENT']);
    }

    private function buildFormBuilderData(Assessment $assessment): array
    {
        return $assessment->forms->map(function ($form) use ($assessment) {
            return [
                'id' => $form->id,
                'judul_form' => $form->judul_form,
                'kode_form' => $form->kode_form,
                'deskripsi' => $form->deskripsi,
                'kompetensi' => $form->kompetensi,
                'indikator_kode' => $form->indikator_kode,
                'indikator_label' => $form->indikator_label,
                'is_scoreable' => $form->is_scoreable,
                'urutan' => $form->urutan,
                'is_active' => $form->is_active,
                'scoring' => [
                    'profile' => data_get($form->scoring_config, 'profile'),
                    'weight' => data_get($form->scoring_config, 'weight'),
                    'exclude_from_competency' => (bool) data_get($form->scoring_config, 'exclude_from_competency', false),
                    'advanced_rules_text' => $this->formatAdvancedRulesText(data_get($form->scoring_config, 'advanced_rules_text') ?: data_get($form->scoring_config, 'advanced_rules')),
                ],
                'fields' => $form->fields->map(function ($field) use ($assessment) {
                    $radioOptions = [];
                    $choiceOptions = [];

                    if ($field->tipe_field === 'radio') {
                        $radioOptions = collect(ChoiceOptionNormalizer::normalizeMany($field->opsi_field ?? []))
                            ->map(fn ($option) => [
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'score' => $option['score'],
                                'level_kompetensi' => $option['level_kompetensi'],
                            ])
                            ->toArray();
                    }

                    if (in_array($field->tipe_field, ['select', 'checkbox'], true)) {
                        $choiceOptions = collect(ChoiceOptionNormalizer::normalizeMany($field->opsi_field ?? []))
                            ->map(fn ($option) => [
                                'label' => $option['label'],
                                'value' => $option['value'],
                                'score' => $option['score'],
                            ])
                            ->toArray();
                    }

                    return [
                        'id' => $field->id,
                        'label' => $field->label,
                        'nama_field' => $field->nama_field,
                        'deskripsi' => $field->deskripsi,
                        'tipe_field' => $field->tipe_field,
                        'placeholder' => $field->placeholder,
                        'bantuan' => $field->bantuan,
                        'autofill_source' => $this->resolveFieldAutoFillSource(
                            $field->autofill_source,
                            $field->tipe_field,
                            $field->label,
                            $field->nama_field
                        ),
                        'lookup_source' => $this->resolveFieldLookupSource(
                            $field->lookup_source,
                            $field->tipe_field,
                            $field->label,
                            $field->nama_field,
                            $assessment->target_ketenagaan
                        ),
                        'allow_other_input' => $field->tipe_field === 'select'
                            ? (bool) data_get($field->validasi, 'allow_other_input', false)
                            : false,
                        'file_input_mode' => $field->tipe_field === 'file'
                            ? $this->normalizeFileInputMode(data_get($field->opsi_field, 'input_mode'))
                            : null,
                        'raw_opsi_field_json' => $this->formatRawFieldOptionsJsonForBuilder($field->tipe_field, $field->opsi_field),
                        'repeater_config_text' => $field->tipe_field === 'repeater' && is_array($field->opsi_field)
                            ? json_encode($field->opsi_field, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                            : null,
                        'opsi_field_text' => in_array($field->tipe_field, ['select', 'checkbox'], true)
                            ? $this->formatChoiceOptionLabelsForBuilder($choiceOptions, $field->opsi_field)
                            : null,
                        'opsi_score_text' => $this->formatOptionScoreText($choiceOptions),
                        'radio_options' => $radioOptions,
                        'scoring' => [
                            'enabled' => (bool) data_get($field->scoring_config, 'enabled', false),
                            'profile' => data_get($field->scoring_config, 'profile'),
                            'method' => data_get($field->scoring_config, 'method'),
                            'rubric_code' => data_get($field->scoring_config, 'rubric_code'),
                            'weight' => data_get($field->scoring_config, 'weight'),
                            'score_if_answered' => data_get($field->scoring_config, 'score_if_answered'),
                            'scale_min' => data_get($field->scoring_config, 'scale_min'),
                            'scale_max' => data_get($field->scoring_config, 'scale_max'),
                            'reference_answer' => data_get($field->scoring_config, 'reference_answer'),
                            'keyword_groups_text' => $this->formatKeywordGroupsText(data_get($field->scoring_config, 'keyword_groups_text') ?: data_get($field->scoring_config, 'keyword_groups')),
                            'synonym_map_text' => $this->resolveSynonymMapTextForBuilder($field->scoring_config ?? []),
                            'min_words' => data_get($field->scoring_config, 'min_words'),
                            'confidence_threshold' => data_get($field->scoring_config, 'confidence_threshold'),
                            'manual_review_below_confidence' => (bool) data_get($field->scoring_config, 'manual_review_below_confidence', false),
                            'numeric_direction' => data_get($field->scoring_config, 'numeric_direction'),
                            'min_threshold' => data_get($field->scoring_config, 'min_threshold'),
                            'target_threshold' => data_get($field->scoring_config, 'target_threshold'),
                            'max_threshold' => data_get($field->scoring_config, 'max_threshold'),
                            'min_score' => data_get($field->scoring_config, 'min_score'),
                            'target_score' => data_get($field->scoring_config, 'target_score'),
                            'max_score' => data_get($field->scoring_config, 'max_score'),
                            'advanced_rules_text' => $this->formatAdvancedRulesText(data_get($field->scoring_config, 'advanced_rules_text') ?: data_get($field->scoring_config, 'advanced_rules')),
                        ],
                        'lebar_kolom' => $field->lebar_kolom,
                        'urutan' => $field->urutan,
                        'is_required' => $field->is_required,
                        'is_active' => $field->is_active,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    private function formatChoiceOptionLabelsForBuilder(array $choiceOptions, mixed $rawOptions): ?string
    {
        if ($choiceOptions !== []) {
            $labels = collect($choiceOptions)
                ->pluck('label')
                ->map(fn ($label) => trim((string) $label))
                ->filter()
                ->values();

            return $labels->isEmpty() ? null : $labels->implode(', ');
        }

        if (! is_array($rawOptions) || ! array_is_list($rawOptions)) {
            return null;
        }

        $labels = collect($rawOptions)
            ->filter(fn ($option) => is_scalar($option))
            ->map(fn ($option) => trim((string) $option))
            ->filter()
            ->values();

        return $labels->isEmpty() ? null : $labels->implode(', ');
    }

    private function formatRawFieldOptionsJsonForBuilder(?string $fieldType, mixed $rawOptions): ?string
    {
        if (in_array($fieldType, ['select', 'radio', 'checkbox', 'repeater'], true) || ! is_array($rawOptions)) {
            return null;
        }

        return json_encode($rawOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }

    private function formatOptionScoreText(array $options): ?string
    {
        $scoredOptions = collect($options)
            ->filter(fn ($option) => is_numeric($option['score'] ?? null))
            ->map(fn ($option) => ($option['label'] ?? $option['value'] ?? '').' = '.$option['score'])
            ->filter()
            ->values();

        return $scoredOptions->isEmpty() ? null : $scoredOptions->implode("\n");
    }

    /**
     * @return array{valid:bool,message?:string}
     */
    private function validateKeywordGroupsText(mixed $value): array
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return ['valid' => true];
        }

        if ($this->keywordGroupsUseLegacyFormat($raw)) {
            return [
                'valid' => false,
                'message' => 'Pisahkan kata kunci hanya dengan koma. Contoh: sertifikat, program studi, lembaga. Jangan gunakan baris baru, tanda |, atau titik koma.',
            ];
        }

        return ['valid' => true];
    }

    private function normalizeKeywordGroupsText(mixed $value): ?string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        if ($this->keywordGroupsUseLegacyFormat($raw)) {
            return $raw;
        }

        $keywords = collect(explode(',', $raw))
            ->map(fn ($keyword) => trim((string) $keyword))
            ->filter()
            ->unique()
            ->values();

        return $keywords->isEmpty() ? null : $keywords->implode(', ');
    }

    private function formatKeywordGroupsText(mixed $value): ?string
    {
        $keywords = collect($this->extractKeywordGroups($value))
            ->map(fn (array $group) => $group[0] ?? null)
            ->filter()
            ->unique()
            ->values();

        return $keywords->isEmpty() ? null : $keywords->implode(', ');
    }

    private function resolveSynonymMapTextForBuilder(array $scoringConfig): ?string
    {
        $synonyms = data_get($scoringConfig, 'synonym_map_text') ?: data_get($scoringConfig, 'synonyms');

        if (filled($synonyms)) {
            return $this->formatSynonymsText($synonyms);
        }

        return $this->formatSynonymsText(
            $this->buildSynonymMapFromKeywordGroups(
                data_get($scoringConfig, 'keyword_groups_text') ?: data_get($scoringConfig, 'keyword_groups')
            )
        );
    }

    private function formatSynonymsText(mixed $value): ?string
    {
        if (is_string($value)) {
            return trim($value) !== '' ? trim($value) : null;
        }

        if (! is_array($value)) {
            return null;
        }

        $lines = collect($value)
            ->map(function ($variants, $baseWord) {
                $baseWord = trim((string) $baseWord);

                if ($baseWord === '') {
                    return null;
                }

                $variantText = collect((array) $variants)
                    ->map(fn ($variant) => trim((string) $variant))
                    ->filter()
                    ->implode(', ');

                return $variantText !== '' ? $baseWord.': '.$variantText : null;
            })
            ->filter()
            ->values();

        return $lines->isEmpty() ? null : $lines->implode("\n");
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function extractKeywordGroups(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->map(function ($group) {
                    return collect((array) $group)
                        ->map(fn ($item) => trim((string) $item))
                        ->filter()
                        ->values()
                        ->all();
                })
                ->filter()
                ->values()
                ->all();
        }

        $raw = trim((string) $value);

        if ($raw === '') {
            return [];
        }

        if (! $this->keywordGroupsUseLegacyFormat($raw)) {
            return collect(explode(',', $raw))
                ->map(fn ($keyword) => trim((string) $keyword))
                ->filter()
                ->values()
                ->map(fn ($keyword) => [$keyword])
                ->all();
        }

        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(function ($line) {
                return collect(preg_split('/\s*(?:\||;|,)\s*/', (string) $line))
                    ->map(fn ($keyword) => trim((string) $keyword))
                    ->filter()
                    ->values()
                    ->all();
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function buildSynonymMapFromKeywordGroups(mixed $value): array
    {
        return collect($this->extractKeywordGroups($value))
            ->reduce(function (array $carry, array $group) {
                $baseKeyword = trim((string) ($group[0] ?? ''));
                $variants = collect(array_slice($group, 1))
                    ->map(fn ($keyword) => trim((string) $keyword))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if ($baseKeyword === '' || $variants === []) {
                    return $carry;
                }

                $carry[$baseKeyword] = $variants;

                return $carry;
            }, []);
    }

    private function keywordGroupsUseLegacyFormat(string $value): bool
    {
        return Str::contains($value, ["\n", "\r", '|', ';']);
    }

    private function formatAdvancedRulesText(mixed $value): ?string
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : null;
        }

        if (! is_array($value) || $value === []) {
            return null;
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
