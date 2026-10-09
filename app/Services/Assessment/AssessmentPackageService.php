<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Enum\KompetensiGuru;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentSession;
use App\Models\AssessmentCombinationItem;
use App\Models\AssessmentForm;
use App\Models\AssessmentFormField;
use App\Support\Assessment\AssessmentJsonStream;
use App\Support\Assessment\LikertScale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AssessmentPackageService
{
    private const SCHEMA = 'database-assessment-v1';

    private const FIELD_TYPES = [
        'text', 'textarea', 'number', 'email', 'date', 'select', 'radio',
        LikertScale::FIELD_TYPE, 'checkbox', 'file', 'repeater',
    ];

    private const ASSESSMENT_KEYS = [
        'id', 'kode_assessment', 'judul', 'slug', 'deskripsi', 'petunjuk',
        'instrument_type', 'kategori', 'target_ketenagaan', 'target_jabatan',
        'scoring_config', 'status', 'is_active', 'created_at', 'updated_at',
    ];

    private const FORM_KEYS = [
        'id', 'assessment_id', 'judul_form', 'kode_form', 'deskripsi',
        'kompetensi', 'indikator_kode', 'indikator_label', 'is_scoreable',
        'scoring_config', 'urutan', 'is_active', 'created_at', 'updated_at',
    ];

    private const FIELD_KEYS = [
        'id', 'assessment_form_id', 'label', 'deskripsi', 'nama_field',
        'tipe_field', 'placeholder', 'bantuan', 'opsi_field', 'nilai_default',
        'autofill_source', 'lookup_source', 'dependency_config', 'validasi',
        'scoring_config', 'urutan', 'is_required', 'is_active', 'created_at',
        'updated_at',
    ];

    private const ASSIGNMENT_KEYS = [
        'kode_penugasan', 'judul_penugasan', 'is_active', 'session_enabled',
        'target_ketenagaan', 'target_jabatan', 'target_kabupaten',
        'target_satuan_pendidikan', 'deskripsi', 'tanggal_mulai', 'jam_mulai',
        'tanggal_selesai', 'kapasitas_per_sesi', 'durasi_sesi_jam', 'security_config',
        'status_distribusi', 'assessments', 'sessions',
    ];

    public function export(Assessment $assessment): StreamedResponse
    {
        $filename = Str::slug($assessment->kode_assessment ?: $assessment->judul).'.json';

        return response()->streamDownload(function () use ($assessment): void {
            $this->streamPackage($assessment);
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Validate the complete file before a job is dispatched.
     * Only one form and one field are held at a time while the file is read.
     *
     * @return array{valid:bool,errors:array<int,string>,summary:array<string,mixed>}
     */
    public function validateFile(string $path): array
    {
        $errors = [];
        $formFieldNames = [];
        $formFieldCounts = [];
        $assignmentCodes = [];
        $assessmentCode = null;
        $summary = [
            'assessment_code' => null,
            'assessment_title' => null,
            'forms' => 0,
            'fields' => 0,
            'assignments' => 0,
        ];

        $addError = static function (string $path, string $message) use (&$errors): void {
            if (count($errors) < 100) {
                $errors[] = $path.': '.$message;
            }
        };

        try {
            $result = AssessmentJsonStream::open($path)->scan(
                function (array $assessment) use (&$summary, &$assessmentCode, $addError): void {
                    $summary['assessment_code'] = $assessment['kode_assessment'] ?? null;
                    $assessmentCode = $summary['assessment_code'];
                    $summary['assessment_title'] = $assessment['judul'] ?? null;
                    $this->validateAssessment($assessment, $addError);
                },
                function (array $form, int $formIndex) use (&$formFieldNames, &$formFieldCounts, $addError): void {
                    if ($formIndex > 0 && ($formFieldCounts[$formIndex - 1] ?? 0) < 1) {
                        $addError('data.forms.'.($formIndex - 1).'.fields', 'Minimal satu pertanyaan wajib tersedia.');
                    }

                    // Keep only the current form's names in memory.
                    $formFieldNames = [];
                    $formFieldCounts[$formIndex] = 0;
                    $this->validateForm($form, $formIndex, $addError);
                },
                function (array $field, int $formIndex, int $fieldIndex) use (&$formFieldNames, &$formFieldCounts, $addError): void {
                    $formFieldCounts[$formIndex] = ($formFieldCounts[$formIndex] ?? 0) + 1;
                    $this->validateField($field, $formIndex, $fieldIndex, $formFieldNames, $addError);
                },
                function (array $assignment, int $assignmentIndex) use (&$assignmentCodes, &$assessmentCode, $addError): void {
                    $this->validateAssignment($assignment, $assignmentIndex, $assignmentCodes, $addError, $assessmentCode);
                }
            );

            $summary['forms'] = $result['counts']['forms'];
            $summary['fields'] = $result['counts']['fields'];
            $summary['assignments'] = $result['counts']['assignments'];

            foreach ($formFieldCounts as $formIndex => $fieldCount) {
                if ($fieldCount < 1) {
                    $addError("data.forms.$formIndex.fields", 'Minimal satu pertanyaan wajib tersedia.');
                }
            }

            $this->validateMeta($result['meta'], $addError);

            if ($summary['forms'] < 1) {
                $addError('data.forms', 'Minimal satu form wajib tersedia.');
            }

            if ($summary['fields'] < 1) {
                $addError('data.forms.*.fields', 'Minimal satu pertanyaan wajib tersedia.');
            }
        } catch (Throwable $exception) {
            $addError('json', $exception->getMessage());
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'summary' => $summary,
        ];
    }

    /**
     * Import is called only after validateFile() succeeds. It still re-reads
     * the file with the same streaming reader so the queued job never carries
     * the complete question bank in its serialized payload.
     *
     * @return array{assessment_id:int,forms:int,fields:int,assignments:int}
     */
    public function importFile(string $path): array
    {
        return Model::withoutEvents(function () use ($path): array {
            $imported = DB::transaction(function () use ($path): array {
                $assessment = null;
                $currentForm = null;
                $assignmentIds = [];
                $counts = ['forms' => 0, 'fields' => 0, 'assignments' => 0];

                AssessmentJsonStream::open($path)->scan(
                    function (array $attributes) use (&$assessment, &$currentForm): void {
                        $assessment = $this->upsertAssessment($attributes);
                        $assessment->forms()->delete();
                        $currentForm = null;
                    },
                    function (array $attributes) use (&$assessment, &$currentForm): void {
                        if (! $assessment) {
                            throw new RuntimeException('Assessment belum tersedia saat membaca form.');
                        }

                        $currentForm = $assessment->forms()->create(
                            $this->filterAttributes($attributes, AssessmentForm::class, [
                                'id', 'assessment_id', 'created_at', 'updated_at',
                            ])
                        );
                    },
                    function (array $attributes) use (&$currentForm): void {
                        if (! $currentForm) {
                            throw new RuntimeException('Form belum tersedia saat membaca pertanyaan.');
                        }

                        $currentForm->fields()->create(
                            $this->filterAttributes($attributes, \App\Models\AssessmentFormField::class, [
                                'id', 'assessment_form_id', 'created_at', 'updated_at',
                            ])
                        );
                    },
                    function (array $attributes) use (&$assessment, &$assignmentIds): void {
                        if (! $assessment) {
                            throw new RuntimeException('Assessment belum tersedia saat membaca penugasan.');
                        }

                        $assignment = $this->importAssignment($attributes, $assessment);
                        $assignmentIds[] = (int) $assignment->id;
                    }
                );

                if (! $assessment) {
                    throw new RuntimeException('Data assessment tidak ditemukan.');
                }

                $combinationResult = ['updated' => 0];

                if (AssessmentCombinationItem::query()
                    ->where('assessment_id', $assessment->id)
                    ->exists()) {
                    $combinationResult = app(AssessmentCombinationService::class)
                        ->syncCombinationsForAssessment($assessment);
                }

                $counts['forms'] = (int) $assessment->forms()->count();
                $counts['fields'] = (int) AssessmentFormField::query()
                    ->whereHas('form', fn ($query) => $query->where('assessment_id', $assessment->id))
                    ->count();
                $counts['assignments'] = count(array_unique($assignmentIds));

                app(AssessmentTargetSyncDispatcher::class)->assessments([(int) $assessment->id]);
                app(AssessmentTargetSyncDispatcher::class)->assignments($assignmentIds);

                return [
                    'assessment_id' => (int) $assessment->id,
                    ...$counts,
                    'combinations_updated' => (int) ($combinationResult['updated'] ?? 0),
                ];
            });

            return $imported;
        });
    }

    private function streamPackage(Assessment $assessment): void
    {
        $meta = [
            'schema' => self::SCHEMA,
            'version' => 1,
            'export_type' => 'assessment-package',
            'exported_at' => now()->toISOString(),
            'application' => config('app.name'),
            'streaming' => true,
            'runtime_data_excluded' => [
                'assessment_assignment_targets',
                'assessment_attempts',
                'assessment_attempt_answers',
                'assessment_attempt_security_events',
            ],
        ];

        echo '{"meta":'.$this->encode($meta).',"data":';
        $this->writeAssessment($assessment);
        echo ',"included":{"assignment_configs":[';

        $first = true;
        AssessmentAssignment::query()
            ->whereHas('assessments', fn ($query) => $query->whereKey($assessment->id))
            ->orderBy('id')
            ->cursor()
            ->each(function (AssessmentAssignment $assignment) use (&$first, $assessment): void {
                if (! $first) {
                    echo ',';
                }

                $first = false;
                echo $this->encode($this->assignmentExportData($assignment, $assessment));
            });

        echo ']}}';
    }

    private function writeAssessment(Assessment $assessment): void
    {
        $attributes = $assessment->attributesToArray();
        unset($attributes['forms']);

        echo '{';
        $first = true;

        foreach ($attributes as $key => $value) {
            if (! in_array($key, self::ASSESSMENT_KEYS, true)) {
                continue;
            }

            if (! $first) {
                echo ',';
            }

            $first = false;
            echo $this->encode((string) $key).':'.$this->encode($value);
        }

        if (! $first) {
            echo ',';
        }

        echo '"forms":[';
        $firstForm = true;

        AssessmentForm::query()
            ->where('assessment_id', $assessment->id)
            ->orderBy('urutan')
            ->orderBy('id')
            ->cursor()
            ->each(function (AssessmentForm $form) use (&$firstForm): void {
                if (! $firstForm) {
                    echo ',';
                }

                $firstForm = false;
                $attributes = $form->attributesToArray();
                $fields = [];

                echo '{';
                $first = true;

                foreach ($attributes as $key => $value) {
                    if (! in_array($key, self::FORM_KEYS, true)) {
                        continue;
                    }

                    if (! $first) {
                        echo ',';
                    }

                    $first = false;
                    echo $this->encode((string) $key).':'.$this->encode($value);
                }

                if (! $first) {
                    echo ',';
                }

                echo '"fields":[';
                $firstField = true;
                $form->fields()
                    ->orderBy('urutan')
                    ->orderBy('id')
                    ->cursor()
                    ->each(function ($field) use (&$firstField): void {
                        if (! $firstField) {
                            echo ',';
                        }

                        $firstField = false;
                        $attributes = $field->attributesToArray();
                        $attributes = array_intersect_key($attributes, array_flip(self::FIELD_KEYS));
                        echo $this->encode($attributes);
                    });
                echo ']}';
            });

        echo ']}';
    }

    /** @return array<string,mixed> */
    private function assignmentExportData(AssessmentAssignment $assignment, Assessment $assessment): array
    {
        $attributes = array_intersect_key(
            $assignment->attributesToArray(),
            array_flip([
                'kode_penugasan', 'judul_penugasan', 'is_active', 'session_enabled',
                'target_ketenagaan', 'target_jabatan', 'target_kabupaten',
                'target_satuan_pendidikan', 'deskripsi', 'tanggal_mulai', 'jam_mulai',
                'tanggal_selesai', 'kapasitas_per_sesi', 'durasi_sesi_jam',
                'security_config', 'status_distribusi',
            ])
        );

        $stage = DB::table('assessment_assignment_assessments')
            ->where('assessment_assignment_id', $assignment->id)
            ->where('assessment_id', $assessment->id)
            ->first();

        $attributes['assessments'] = [[
            'kode_assessment' => $assessment->kode_assessment,
            'urutan' => (int) ($stage->urutan ?? 1),
            'stage_config' => $this->decodeJsonValue($stage->stage_config ?? null),
        ]];
        $attributes['sessions'] = [];

        $assignment->sessions()
            ->orderBy('nomor_sesi')
            ->cursor()
            ->each(function (AssessmentAssignmentSession $session) use (&$attributes): void {
                $attributes['sessions'][] = array_intersect_key(
                    $session->attributesToArray(),
                    array_flip([
                        'nomor_sesi', 'label_sesi', 'waktu_mulai', 'waktu_selesai',
                        'kapasitas_peserta', 'durasi_sesi_jam',
                    ])
                );
            });

        return $attributes;
    }

    private function validateMeta(array $meta, callable $addError): void
    {
        if (($meta['schema'] ?? null) !== self::SCHEMA) {
            $addError('meta.schema', 'Schema harus '.$this::SCHEMA.'.');
        }

        if (isset($meta['version']) && (int) $meta['version'] !== 1) {
            $addError('meta.version', 'Versi JSON assessment tidak didukung.');
        }
    }

    private function validateAssessment(array $data, callable $addError): void
    {
        $this->rejectUnknownKeys($data, self::ASSESSMENT_KEYS, 'data', $addError);

        $this->validateWithRules($data, [
            'kode_assessment' => ['required', 'string', 'max:100'],
            'judul' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'petunjuk' => ['nullable', 'string'],
            'instrument_type' => ['nullable', 'string', 'in:'.implode(',', array_keys(AssessmentInstrumentType::options()))],
            'kategori' => ['nullable', 'string', 'max:100'],
            'target_ketenagaan' => ['nullable', 'string', 'in:'.implode(',', array_keys(AssessmentKetenagaanType::options()))],
            'target_jabatan' => ['nullable', 'array'],
            'scoring_config' => ['nullable', 'array'],
            'status' => ['required', 'in:draft,publish,nonaktif'],
            'is_active' => ['required', 'boolean'],
        ], 'data', $addError);
    }

    private function validateForm(array $data, int $index, callable $addError): void
    {
        $path = "data.forms.$index";
        $this->rejectUnknownKeys($data, self::FORM_KEYS, $path, $addError);
        $this->validateWithRules($data, [
            'judul_form' => ['required', 'string', 'max:255'],
            'kode_form' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'kompetensi' => ['nullable', 'string', 'in:'.implode(',', array_keys(KompetensiGuru::options()))],
            'indikator_kode' => ['nullable', 'string', 'max:100'],
            'indikator_label' => ['nullable', 'string', 'max:255'],
            'is_scoreable' => ['required', 'boolean'],
            'scoring_config' => ['nullable', 'array'],
            'urutan' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ], $path, $addError);
    }

    /** @param array<int,string> $formFieldNames */
    private function validateField(
        array $data,
        int $formIndex,
        int $fieldIndex,
        array &$formFieldNames,
        callable $addError
    ): void {
        $path = "data.forms.$formIndex.fields.$fieldIndex";
        $this->rejectUnknownKeys($data, self::FIELD_KEYS, $path, $addError);
        $this->validateWithRules($data, [
            'label' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'nama_field' => ['required', 'string', 'max:255'],
            'tipe_field' => ['required', 'string', 'in:'.implode(',', self::FIELD_TYPES)],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'bantuan' => ['nullable', 'string'],
            'opsi_field' => ['nullable', 'array'],
            'nilai_default' => ['nullable'],
            'autofill_source' => ['nullable', 'string', 'max:100'],
            'lookup_source' => ['nullable', 'string', 'max:100'],
            'dependency_config' => ['nullable', 'array'],
            'validasi' => ['nullable', 'array'],
            'scoring_config' => ['nullable', 'array'],
            'urutan' => ['required', 'integer', 'min:1'],
            'is_required' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ], $path, $addError);

        $fieldName = trim((string) ($data['nama_field'] ?? ''));

        if ($fieldName !== '' && in_array($fieldName, $formFieldNames, true)) {
            $addError($path.'.nama_field', 'Nama field harus unik dalam form.');
        }

        if ($fieldName !== '') {
            $formFieldNames[] = $fieldName;
        }
    }

    /** @param array<int,string> $assignmentCodes */
    private function validateAssignment(
        array $data,
        int $index,
        array &$assignmentCodes,
        callable $addError,
        ?string $assessmentCode = null
    ): void {
        $path = "included.assignment_configs.$index";
        $this->rejectUnknownKeys($data, self::ASSIGNMENT_KEYS, $path, $addError);
        $this->validateWithRules($data, [
            'kode_penugasan' => ['required', 'string', 'max:100'],
            'judul_penugasan' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'session_enabled' => ['required', 'boolean'],
            'target_ketenagaan' => ['nullable', 'string'],
            'target_jabatan' => ['nullable', 'array'],
            'target_kabupaten' => ['nullable', 'array'],
            'target_satuan_pendidikan' => ['nullable', 'array'],
            'deskripsi' => ['nullable', 'string'],
            'tanggal_mulai' => ['nullable', 'date'],
            'jam_mulai' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'tanggal_selesai' => ['nullable', 'date'],
            'kapasitas_per_sesi' => ['nullable', 'integer', 'min:1'],
            'durasi_sesi_jam' => ['nullable', 'integer', 'min:1'],
            'security_config' => ['nullable', 'array'],
            'assessments' => ['required', 'array', 'min:1'],
            'sessions' => ['nullable', 'array'],
        ], $path, $addError);

        $code = trim((string) ($data['kode_penugasan'] ?? ''));

        if ($code !== '' && in_array($code, $assignmentCodes, true)) {
            $addError($path.'.kode_penugasan', 'Kode penugasan tidak boleh berulang.');
        }

        if ($code !== '') {
            $assignmentCodes[] = $code;
        }

        foreach ((array) ($data['assessments'] ?? []) as $stageIndex => $stage) {
            if (! is_array($stage)) {
                $addError("$path.assessments.$stageIndex", 'Stage assessment harus berupa objek.');

                continue;
            }

            $this->rejectUnknownKeys($stage, ['kode_assessment', 'urutan', 'stage_config'], "$path.assessments.$stageIndex", $addError);
            $this->validateWithRules($stage, [
                'kode_assessment' => ['required', 'string', 'max:100'],
                'urutan' => ['required', 'integer', 'min:1'],
                'stage_config' => ['nullable', 'array'],
            ], "$path.assessments.$stageIndex", $addError);

            if ($assessmentCode !== null && ($stage['kode_assessment'] ?? null) !== $assessmentCode) {
                $addError(
                    "$path.assessments.$stageIndex.kode_assessment",
                    'Stage harus merujuk ke kode assessment pada data utama.'
                );
            }
        }

        $sessionNumbers = [];
        foreach ((array) ($data['sessions'] ?? []) as $sessionIndex => $session) {
            $sessionPath = "$path.sessions.$sessionIndex";

            if (! is_array($session)) {
                $addError($sessionPath, 'Sesi harus berupa objek.');

                continue;
            }

            $this->rejectUnknownKeys($session, [
                'nomor_sesi', 'label_sesi', 'waktu_mulai', 'waktu_selesai',
                'kapasitas_peserta', 'durasi_sesi_jam',
            ], $sessionPath, $addError);
            $this->validateWithRules($session, [
                'nomor_sesi' => ['required', 'integer', 'min:1'],
                'label_sesi' => ['nullable', 'string', 'max:255'],
                'waktu_mulai' => ['nullable', 'date'],
                'waktu_selesai' => ['nullable', 'date'],
                'kapasitas_peserta' => ['nullable', 'integer', 'min:1'],
                'durasi_sesi_jam' => ['nullable', 'integer', 'min:1'],
            ], $sessionPath, $addError);

            $sessionNumber = $session['nomor_sesi'] ?? null;
            if ($sessionNumber !== null && in_array((int) $sessionNumber, $sessionNumbers, true)) {
                $addError($sessionPath.'.nomor_sesi', 'Nomor sesi tidak boleh berulang.');
            }

            if ($sessionNumber !== null) {
                $sessionNumbers[] = (int) $sessionNumber;
            }
        }
    }

    private function validateWithRules(array $data, array $rules, string $path, callable $addError): void
    {
        $validator = Validator::make($data, $rules);

        foreach ($validator->errors()->all() as $message) {
            $addError($path, $message);
        }
    }

    private function rejectUnknownKeys(array $data, array $allowed, string $path, callable $addError): void
    {
        foreach (array_diff(array_keys($data), $allowed) as $key) {
            $addError($path.'.'.$key, 'Properti tidak dikenali oleh schema assessment.');
        }
    }

    private function upsertAssessment(array $attributes): Assessment
    {
        $code = trim((string) ($attributes['kode_assessment'] ?? ''));
        $assessment = Assessment::query()->firstOrNew(['kode_assessment' => $code]);
        $data = $this->filterAttributes($attributes, Assessment::class, ['id', 'created_at', 'updated_at']);
        $data['kode_assessment'] = $code;
        $data['slug'] = $this->resolveSlug(
            (string) ($attributes['slug'] ?? ''),
            (string) ($attributes['judul'] ?? $code),
            $assessment->exists ? (int) $assessment->id : null
        );
        $assessment->fill($data);
        $assessment->save();

        return $assessment;
    }

    private function importAssignment(array $attributes, Assessment $assessment): AssessmentAssignment
    {
        $code = trim((string) ($attributes['kode_penugasan'] ?? ''));
        $assignment = AssessmentAssignment::query()->firstOrNew(['kode_penugasan' => $code]);
        $data = array_intersect_key($attributes, array_flip([
            'judul_penugasan', 'is_active', 'session_enabled', 'target_ketenagaan',
            'target_jabatan', 'target_kabupaten', 'target_satuan_pendidikan',
            'deskripsi', 'tanggal_mulai', 'jam_mulai', 'tanggal_selesai',
            'kapasitas_per_sesi', 'durasi_sesi_jam', 'security_config',
        ]));
        $data['kode_penugasan'] = $code;
        $data['status_distribusi'] = 'draft';
        $data['total_target'] = 0;
        $data['total_ditugaskan'] = 0;
        $data['assigned_by'] = null;
        $data['job_batch_id'] = null;
        $data['processed_at'] = null;
        $data['assessment_combination_id'] = null;
        $assignment->fill($this->filterExistingColumns($data, AssessmentAssignment::class));
        $assignment->save();

        if (Schema::hasTable('assessment_assignment_assessments')) {
            $stage = collect((array) ($attributes['assessments'] ?? []))
                ->firstWhere('kode_assessment', $assessment->kode_assessment)
                ?: collect((array) ($attributes['assessments'] ?? []))->first();

            DB::table('assessment_assignment_assessments')->updateOrInsert(
                [
                    'assessment_assignment_id' => $assignment->id,
                    'assessment_id' => $assessment->id,
                ],
                [
                    'urutan' => (int) ($stage['urutan'] ?? 1),
                    'stage_config' => isset($stage['stage_config'])
                        ? json_encode($stage['stage_config'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        : null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        if (Schema::hasTable('assessment_assignment_sessions')) {
            foreach ((array) ($attributes['sessions'] ?? []) as $session) {
                if (! is_array($session) || ! isset($session['nomor_sesi'])) {
                    continue;
                }

                AssessmentAssignmentSession::query()->updateOrCreate(
                    [
                        'assessment_assignment_id' => $assignment->id,
                        'nomor_sesi' => (int) $session['nomor_sesi'],
                    ],
                    array_intersect_key($session, array_flip([
                        'label_sesi', 'waktu_mulai', 'waktu_selesai',
                        'kapasitas_peserta', 'durasi_sesi_jam',
                    ]))
                );
            }
        }

        return $assignment;
    }

    /** @param class-string<Model> $modelClass */
    private function filterAttributes(array $attributes, string $modelClass, array $except = []): array
    {
        $model = new $modelClass;
        $fillable = array_flip($model->getFillable());
        $except = array_fill_keys($except, true);

        return collect(array_intersect_key($attributes, $fillable))
            ->reject(fn ($value, $key) => isset($except[$key]))
            ->all();
    }

    /** @param class-string<Model> $modelClass */
    private function filterExistingColumns(array $attributes, string $modelClass): array
    {
        $table = (new $modelClass)->getTable();

        return collect($attributes)
            ->filter(fn ($value, $key) => Schema::hasColumn($table, $key))
            ->all();
    }

    private function resolveSlug(string $requested, string $title, ?int $ignoreId): string
    {
        $base = Str::slug($requested !== '' ? $requested : $title);
        $base = $base !== '' ? $base : 'assessment';
        $slug = $base;
        $suffix = 2;

        while (Assessment::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = Str::limit($base, 240, '').'-'.$suffix++;
        }

        return $slug;
    }

    private function decodeJsonValue(mixed $value): mixed
    {
        if (! is_string($value) || trim($value) === '') {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    private function encode(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }
}
