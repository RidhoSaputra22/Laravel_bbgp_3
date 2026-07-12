@php
    $builderSeed = old('forms', $formBuilderData ?? []);
    $assessmentCodeValue = old('kode_assessment', $assessment->kode_assessment);
    $assessmentCodeDisplay = $assessmentCodeValue ?: 'Otomatis saat disimpan';
    $competencyLevels = \App\Enum\LevelKompetensi::options();
    $instrumentTypes = \App\Enum\AssessmentInstrumentType::options();
    $teacherCompetencies = \App\Enum\KompetensiGuru::options();
    $fieldTypeBadges = $fieldTypes ?? [];
    $participantAutoFillOptions = $participantAutoFillOptions ?? [];
    $fieldLookupOptions = $fieldLookupOptions ?? [];
    $fieldLookupCatalog = $fieldLookupCatalog ?? [];
    $validationErrors = $errors->getMessages();
    $ketenagaanOptions = $ketenagaanOptions ?? \App\Enum\AssessmentKetenagaanType::options();
    $selectedTargetKetenagaan = old(
        'target_ketenagaan',
        $assessment->target_ketenagaan ?: \App\Enum\AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
    );
    $ketenagaanCards = collect(\App\Enum\AssessmentKetenagaanType::cases())
        ->mapWithKeys(function ($case) {
            return [
                $case->value => [
                    'label' => $case->label(),
                    'icon' => $case->iconClass(),
                    'theme' => match ($case) {
                        \App\Enum\AssessmentKetenagaanType::TENAGA_PENDIDIK => 'pendidik',
                        \App\Enum\AssessmentKetenagaanType::TENAGA_KEPENDIDIKAN => 'kependidikan',
                        \App\Enum\AssessmentKetenagaanType::STAKEHOLDER => 'stakeholder',
                    },
                ],
            ];
        })
        ->all();
    $scoringGuidancePreset = \App\Support\Assessment\ScoringGuidanceAssistant::clientPreset();
    $formScoringProfiles = [
        'generic' => 'Umum',
        'portofolio' => 'Portofolio',
        'study_case_default' => 'Studi Kasus',
        'pilihan_ganda_kompleks' => 'Pilihan Ganda Kompleks',
    ];
    $fieldScoringMethods = [
        'presence' => 'Nilai saat ada jawaban',
        'choice_option_score' => 'Ambil dari skor opsi',
        'choice_option_average' => 'Rata-rata opsi terpilih',
        'choice_option_sum' => 'Jumlahkan skor opsi',
        'choice_option_max' => 'Ambil skor opsi tertinggi',
        'numeric_threshold' => 'Nilai berdasarkan target angka',
        'numeric_range' => 'Nilai berdasarkan rentang ideal',
        'semantic_similarity' => 'Analisis isi jawaban',
        'keyword_coverage' => 'Cek kata kunci penting',
        'repeater_completeness' => 'Cek kelengkapan tabel',
    ];
    $isEditMode = $httpMethod !== 'POST';
    $previewUrl = $isEditMode && $assessment->id ? route('assessment.show', $assessment->id) : null;
    $normalizeCheckedValue = function (mixed $value, bool $default = false): bool {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if ($normalized === '') {
                return $default;
            }

            return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
        }

        return (bool) $value;
    };
    $initialStatus = old('status', $assessment->status ?: 'draft');
    $initialInstrumentValue = old('instrument_type', $assessment->instrument_type);
    $initialSummaryTitle = trim((string) old('judul', $assessment->judul));
    $builderSeedCollection = collect($builderSeed)
        ->map(fn ($form) => is_array($form) ? $form : (array) $form)
        ->values();
    $resolveMeaningfulFields = function (array $form) {
        return collect((array) ($form['fields'] ?? []))
            ->map(fn ($field) => is_array($field) ? $field : (array) $field)
            ->filter(fn ($field) => trim((string) ($field['label'] ?? '')) !== '')
            ->values();
    };
    $initialTotalForms = $builderSeedCollection->count();
    $initialTotalQuestions = $builderSeedCollection->sum(
        fn ($form) => $resolveMeaningfulFields($form)->count()
    );
    $initialActiveForms = $builderSeedCollection->filter(
        fn ($form) => $normalizeCheckedValue($form['is_active'] ?? true, true)
    )->count();
    $initialScoreableForms = $builderSeedCollection->filter(function ($form) use (
        $normalizeCheckedValue,
        $resolveMeaningfulFields
    ) {
        return $normalizeCheckedValue($form['is_scoreable'] ?? true, true)
            && $resolveMeaningfulFields($form)->isNotEmpty();
    })->count();
    $initialAutoScoringQuestions = $builderSeedCollection->sum(function ($form) use (
        $normalizeCheckedValue,
        $resolveMeaningfulFields
    ) {
        return $resolveMeaningfulFields($form)->filter(function ($field) use ($normalizeCheckedValue) {
            return $normalizeCheckedValue(data_get($field, 'scoring.enabled'), false);
        })->count();
    });
    $initialVisibleQuestions = $builderSeedCollection->sum(function ($form) use (
        $normalizeCheckedValue,
        $resolveMeaningfulFields
    ) {
        if (! $normalizeCheckedValue($form['is_active'] ?? true, true)) {
            return 0;
        }

        return $resolveMeaningfulFields($form)->filter(function ($field) use ($normalizeCheckedValue) {
            return $normalizeCheckedValue($field['is_active'] ?? true, true);
        })->count();
    });
    $initialPreviewForms = $builderSeedCollection->filter(function ($form) use (
        $normalizeCheckedValue,
        $resolveMeaningfulFields
    ) {
        if (! $normalizeCheckedValue($form['is_active'] ?? true, true)) {
            return false;
        }

        return $resolveMeaningfulFields($form)->contains(function ($field) use ($normalizeCheckedValue) {
            return $normalizeCheckedValue($field['is_active'] ?? true, true);
        });
    })->count();
    $initialBuilderSummary = [
        'title' => $initialSummaryTitle,
        'status' => $initialStatus,
        'status_label' => ucfirst((string) $initialStatus),
        'is_active' => $normalizeCheckedValue(old('is_active', $assessment->is_active), false),
        'target_label' => $ketenagaanOptions[$selectedTargetKetenagaan] ?? 'Belum dipilih',
        'instrument_label' => $instrumentTypes[$initialInstrumentValue] ?? 'Belum dipilih',
        'total_forms' => $initialTotalForms,
        'total_questions' => $initialTotalQuestions,
        'active_forms' => $initialActiveForms,
        'scoreable_forms' => $initialScoreableForms,
        'auto_scoring_questions' => $initialAutoScoringQuestions,
        'visible_questions' => $initialVisibleQuestions,
        'preview_forms' => $initialPreviewForms,
    ];
    $initialBuilderSummary['display_label'] = $initialBuilderSummary['preview_forms'] > 0
        ? $initialBuilderSummary['preview_forms'].' form / '.$initialBuilderSummary['visible_questions'].' soal'
        : 'Belum ada form aktif';
    $initialBuilderSummary['builder_note'] = match (true) {
        $initialBuilderSummary['title'] === '' => 'Isi judul assessment terlebih dahulu agar struktur yang Anda susun mudah dikenali.',
        $initialBuilderSummary['total_questions'] < 1 => 'Tambahkan minimal satu pertanyaan agar assessment siap dipakai pada penugasan.',
        $initialBuilderSummary['preview_forms'] < 1 => 'Aktifkan minimal satu form agar pertanyaan bisa tampil pada sisi peserta.',
        ! $initialBuilderSummary['is_active'] => 'Struktur assessment sudah terisi, tetapi assessment utama masih nonaktif.',
        $initialBuilderSummary['status'] !== 'publish' => 'Struktur sudah siap, namun status assessment masih '.strtolower($initialBuilderSummary['status_label']).'.',
        default => $initialBuilderSummary['preview_forms'].' form aktif dengan '.$initialBuilderSummary['visible_questions'].' pertanyaan siap ditampilkan ke peserta. '.$initialBuilderSummary['scoreable_forms'].' form masuk penilaian.',
    };
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <div class="font-weight-bold mb-2">Periksa kembali input assessment berikut:</div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@push('styles')
    <style>
        .assessment-required {
            color: #fc544b;
            font-weight: 700;
        }

        .assessment-invalid-wrapper {
            border: 1px solid #fc544b;
            border-radius: 0.5rem;
            padding: 0.75rem;
        }

        .assessment-invalid-list {
            border: 1px dashed #fc544b;
            border-radius: 0.5rem;
            padding: 0.75rem;
        }

        .invalid-feedback {
            display: block;
            font-size: 0.8rem;
        }

        .multiple-choice-option-row {
            border: 1px solid #e4e6fc;
            border-radius: .1rem;
            padding: 0.75rem;
            background: #fff;
        }

        .repeater-config-shell {
            background: #f8fbff;
            border: 1px solid #dbe8fb;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .repeater-config-shell .form-text code,
        .repeater-column-name-hint code {
            background: #eef4ff;
            border-radius: 0.25rem;
            color: #2455a6;
            padding: 0.12rem 0.35rem;
        }

        .repeater-column-row {
            background: #fff;
            border: 1px solid #dfe7f7;
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .repeater-column-row__title {
            color: #23396b;
            font-size: 0.92rem;
            font-weight: 700;
        }

        .repeater-column-row__meta {
            color: #6b7a90;
            display: block;
            font-size: 0.8rem;
            margin-top: 0.2rem;
        }

        .repeater-column-row textarea.form-control {
            height: 88px !important;
            min-height: 88px !important;
        }

        .repeater-column-required-switch {
            min-height: 100%;
        }

        .repeater-column-required-switch .custom-control-label {
            font-weight: 600;
        }

        .assessment-builder-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .assessment-builder-actions .custom-switch {
            padding-left: 2.25rem;
            margin-bottom: 0;
        }

        .assessment-builder-actions .custom-control-label {
            white-space: nowrap;

        }

        .auto-field-name-hint code {
            background: #f4f7fb;
            border-radius: 0.25rem;
            color: #0c63e7;
            padding: 0.15rem 0.35rem;
        }

        .assessment-meta-textarea.form-control {
            height: 180px !important;
        }

        .assessment-form-card textarea.form-control,
        .assessment-field-card textarea.form-control {
            height: 140px !important;
            min-height: 140px !important;
        }

        .assessment-ketenagaan-grid {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .assessment-ketenagaan-option {
            position: relative;
        }

        .assessment-ketenagaan-input {
            opacity: 0;
            pointer-events: none;
            position: absolute;
        }

        .assessment-ketenagaan-card {
            align-items: center;
            background: #fff;
            border: 1px solid #dfe7f7;
            border-radius: 0.2rem;
            cursor: pointer;
            display: flex;
            gap: 0.85rem;
            margin-bottom: 0;
            min-height: 92px;
            padding: 1rem 1.1rem;
            transition: all 0.2s ease;
        }

        .assessment-ketenagaan-card:hover {
            border-color: #bccdf5;
            box-shadow: 0 10px 24px rgba(52, 73, 94, 0.08);
            transform: translateY(-1px);
        }

        .assessment-ketenagaan-card__icon {
            align-items: center;
            border-radius: 0.2rem;
            color: #fff;
            display: inline-flex;
            flex: 0 0 46px;
            font-size: 1.1rem;
            height: 46px;
            justify-content: center;
            width: 46px;
        }

        .assessment-ketenagaan-card__title {
            color: #334155;
            display: block;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .assessment-ketenagaan-card__hint {
            color: #7b8898;
            display: block;
            font-size: 0.8rem;
            margin-top: 0.15rem;
        }

        .assessment-ketenagaan-card--pendidik .assessment-ketenagaan-card__icon {
            background: linear-gradient(135deg, #1174c7, #2f8fe1);
        }

        .assessment-ketenagaan-card--kependidikan .assessment-ketenagaan-card__icon {
            background: linear-gradient(135deg, #0d8b8c, #1fa3a4);
        }

        .assessment-ketenagaan-card--stakeholder .assessment-ketenagaan-card__icon {
            background: linear-gradient(135deg, #e5a100, #f5bc2b);
        }

        .assessment-ketenagaan-input:checked + .assessment-ketenagaan-card {
            border-color: #6777ef;
            box-shadow: 0 14px 28px rgba(103, 119, 239, 0.16);
            transform: translateY(-1px);
        }

        .assessment-ketenagaan-input:checked + .assessment-ketenagaan-card .assessment-ketenagaan-card__title {
            color: #23396b;
        }

        .scoring-main-note {
            background: #f8fbff;
            border: 1px solid #dbe8fb;
            border-radius: 0.5rem;
            color: #2d4369;
            font-size: 0.92rem;
            padding: 0.85rem 1rem;
        }

        .scoring-default-summary {
            background: #fcfcfd;
            border: 1px dashed #d7dce5;
            border-radius: 0.5rem;
            padding: 0.85rem 1rem;
        }

        .scoring-summary-pill {
            background: #eef3ff;
            border-radius: 999px;
            color: #34539d;
            display: inline-flex;
            font-size: 0.78rem;
            font-weight: 600;
            margin: 0 0.4rem 0.4rem 0;
            padding: 0.28rem 0.7rem;
        }

        .scoring-advanced-panel {
            background: #fbfcfe;
            border: 1px dashed #d7dce5;
            border-radius: 0.5rem;
            margin-top: 1rem;
            padding: 1rem;
        }

        .scoring-helper-status {
            color: #5f6b7a;
            display: block;
            min-height: 1.15rem;
        }

        .scoring-assist-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: space-between;
        }

        .assessment-builder-layout {
            align-items: flex-start;
        }

        .assessment-builder-sidebar {
            margin-top: 1.5rem;
        }

        .assessment-builder-sidebar-inner {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .assessment-builder-shell {
            min-height: 220px;

        }

        .assessment-builder-shell.is-loading #form-builder-empty,
        .assessment-builder-shell.is-loading #form-builder-list {
            display: hidden;
        }

        .assessment-builder-loading {
            align-items: end;



            height: 5px;
            text-align: end;

            padding: 1.5rem;
            position: absolute;
            top: 0;
            right: 0;




            z-index: 2;
        }

        .assessment-builder-loading__content {
            max-width: 340px;
        }

        .assessment-builder-loading .spinner-border {
            height: 2rem;

        }

        .assessment-builder-loading__title {
            color: #23396b;
            font-size: 1rem;
            font-weight: 700;
        }

        .assessment-builder-loading__text {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .assessment-summary-card {
            border: 1px solid #dfe7f7;
            overflow: hidden;
        }

        .assessment-summary-card.is-loading {
            border-color: #cfdaf7;
            box-shadow: inset 0 0 0 1px rgba(103, 119, 239, 0.08);
        }

        .assessment-summary-eyebrow {
            color: #6777ef;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
        }

        .assessment-summary-card h5 {
            color: #23396b;
        }

        .assessment-summary-grid {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .assessment-summary-stat {
            background: linear-gradient(180deg, #f8faff 0%, #eef3ff 100%);
            border: 1px solid #dbe4fb;
            border-radius: 0.2rem;
            min-height: 92px;
            padding: 0.9rem 0.95rem;
        }

        .assessment-summary-stat__label {
            color: #64748b;
            display: block;
            font-size: 0.78rem;
            margin-bottom: 0.35rem;
        }

        .assessment-summary-stat__value {
            color: #23396b;
            display: block;
            font-size: 1.55rem;
            font-weight: 700;
            line-height: 1;
        }

        .assessment-summary-section-title {
            color: #334155;
            font-size: 0.83rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 0.85rem;
            text-transform: uppercase;
        }

        .assessment-summary-list {
            border-top: 1px solid #edf1f7;
        }

        .assessment-summary-row {
            align-items: center;
            border-bottom: 1px solid #edf1f7;
            display: flex;
            font-size: 0.88rem;
            gap: 1rem;
            justify-content: space-between;
            padding: 0.4rem 0;
        }

        .assessment-summary-row span {
            color: #64748b;
        }

        .assessment-summary-row strong {
            color: #1e293b;
            font-size: 0.87rem;
            font-weight: 700;
            max-width: 55%;
            text-align: right;
        }

        .assessment-summary-note {
            background: #f8fbff;
            border: 1px solid #dbe8fb;
            border-radius: 0.2rem;
            color: #34539d;
            font-size: 0.86rem;
            line-height: 1.55;
            padding: 0.95rem 1rem;
        }

        .assessment-summary-actions .btn {
            border-radius: 0.2rem;
            padding-bottom: 0.75rem;
            padding-top: 0.75rem;
        }

        @media (max-width: 991.98px) {
            .assessment-ketenagaan-grid {
                grid-template-columns: 1fr;
            }

            .assessment-summary-row {
                align-items: flex-start;
                flex-direction: column;
            }

            .assessment-summary-row strong {
                max-width: 100%;
                text-align: left;
            }
        }

        @media (min-width: 992px) {
            .assessment-builder-sidebar {
                margin-top: 0;
            }

            .assessment-builder-sidebar-inner {
                position: sticky;
                top: 100px;
            }
        }
    </style>
@endpush

<form action="{{ $formAction }}" method="POST" id="assessment-builder-form">
    @csrf
    <input type="hidden" name="forms_payload" id="forms-payload">
    @if ($httpMethod !== 'POST')
        @method($httpMethod)
    @endif

    <div class="row assessment-builder-layout">
        <div class="col-xl-8 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $pageTitle }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Kode Assessment</label>
                                <input type="hidden" name="kode_assessment" value="{{ $assessmentCodeValue }}">
                                <input type="text" class="form-control @error('kode_assessment') is-invalid @enderror"
                                    value="{{ $assessmentCodeDisplay }}" data-assessment-code-display readonly>
                                <small class="form-text text-muted">
                                    Kode assessment dibuat otomatis saat data disimpan.
                                </small>
                                @error('kode_assessment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Judul Assessment <span class="assessment-required">*</span></label>
                                <input type="text" name="judul"
                                    class="form-control @error('judul') is-invalid @enderror"
                                    value="{{ old('judul', $assessment->judul) }}"
                                    placeholder="Masukkan judul assessment" required>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status <span class="assessment-required">*</span></label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror"
                                    required>
                                    <option value="draft"
                                        @selected(old('status', $assessment->status ?: 'draft') == 'draft')>Draft</option>
                                    <option value="publish"
                                        @selected(old('status', $assessment->status) == 'publish')>Publish</option>
                                    <option value="nonaktif"
                                        @selected(old('status', $assessment->status) == 'nonaktif')>Nonaktif</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Ketenagaan Assessment <span class="assessment-required">*</span></label>
                                <div class="assessment-ketenagaan-grid">
                                    @foreach ($ketenagaanCards as $value => $card)
                                        <div class="assessment-ketenagaan-option">
                                            <input type="radio" class="assessment-ketenagaan-input"
                                                id="assessment-ketenagaan-{{ $value }}" name="target_ketenagaan"
                                                value="{{ $value }}" @checked($selectedTargetKetenagaan === $value) required>
                                            <label for="assessment-ketenagaan-{{ $value }}"
                                                class="assessment-ketenagaan-card assessment-ketenagaan-card--{{ $card['theme'] }}">
                                                <span class="assessment-ketenagaan-card__icon">
                                                    <i class="{{ $card['icon'] }}"></i>
                                                </span>
                                                <span>
                                                    <span
                                                        class="assessment-ketenagaan-card__title">{{ $card['label'] }}</span>
                                                    <span class="assessment-ketenagaan-card__hint">
                                                        Form ini akan masuk ke penugasan otomatis untuk
                                                        {{ strtolower($card['label']) }}.
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="form-text text-muted">
                                    Pilih ketenagaan tujuan assessment. Menu penugasan akan memakai pilihan ini untuk
                                    menentukan form dan seluruh user yang otomatis ditugaskan.
                                </small>
                                @error('target_ketenagaan')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Jenis Instrumen Penilaian</label>
                                <select name="instrument_type"
                                    class="form-control @error('instrument_type') is-invalid @enderror">
                                    <option value="">Pilih jenis instrumen</option>
                                    @foreach ($instrumentTypes as $value => $label)
                                        <option value="{{ $value }}"
                                            @selected(old('instrument_type', $assessment->instrument_type) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('instrument_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea name="deskripsi" class="form-control assessment-meta-textarea @error('deskripsi') is-invalid @enderror" rows="6"
                                    placeholder="Deskripsi singkat assessment">{{ old('deskripsi', $assessment->deskripsi) }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Petunjuk Pengisian</label>
                                <textarea name="petunjuk" class="form-control assessment-meta-textarea @error('petunjuk') is-invalid @enderror" rows="6"
                                    placeholder="Petunjuk untuk pengguna form">{{ old('petunjuk', $assessment->petunjuk) }}</textarea>
                                @error('petunjuk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" id="assessment-active" name="is_active"
                            value="1" @checked(old('is_active', $assessment->is_active))>
                        <label class="custom-control-label" for="assessment-active">Aktifkan assessment</label>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Form Builder</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border">
                        <div class="font-weight-bold mb-2">Petunjuk Penggunaan</div>
                        <ul class="mb-0 pl-3">
                            <li>Isi informasi assessment di bagian atas terlebih dahulu.</li>
                            <li>Klik <strong>Tambah Form</strong> di bagian bawah untuk membuat bagian form, lalu
                                tambahkan field di bawah form terkait.</li>
                            <li>Pilih <strong>jenis instrumen</strong> di level assessment, lalu atur
                                <strong>kompetensi</strong>, <strong>indikator</strong>, dan status
                                <strong>masuk penilaian</strong> di setiap form.</li>
                            <li>Untuk field <strong>Daftar Pilihan</strong> dan <strong>Kotak Centang</strong>,
                                pisahkan opsi dengan koma atau baris baru.</li>
                            <li>Untuk field <strong>Pilihan Ganda</strong>, isi <strong>kode jawaban</strong>,
                                <strong>isi jawaban</strong>, dan <strong>level kompetensi</strong> pada setiap opsi.
                            </li>
                            <li>Pada panel <strong>Pengaturan Skor Otomatis</strong>, isi dulu bagian utama seperti
                                <strong>pedoman penilaian</strong> atau <strong>target angka</strong>. Pengaturan
                                lanjutan bisa dibiarkan otomatis jika tidak diperlukan.</li>
                            <li>Untuk pertanyaan teks atau tabel, Anda bisa memakai tombol bantuan otomatis agar sistem
                                menyiapkan <strong>kata kunci</strong>, <strong>padanan kata</strong>, dan
                                <strong>saran panjang jawaban</strong> dari deskripsi yang sudah Anda tulis.</li>
                            <li>Untuk field <strong>Tabel Berulang</strong>, isi konfigurasi JSON kolom tabel sesuai
                                contoh yang tersedia.</li>
                            <li>Nama field akan dibuat otomatis dari label yang Anda isi.</li>
                            <li>Aktifkan hanya form dan field yang ingin ditampilkan ke pengguna.</li>
                        </ul>
                    </div>

                    <div class="assessment-builder-shell is-loading" id="assessment-builder-shell" aria-busy="true"
                        aria-live="polite">
                        <div class="assessment-builder-loading" id="assessment-builder-loading" role="status">
                            <div class="assessment-builder-loading__content">
                                <div class="spinner-border text-primary mb-3" aria-hidden="true"></div>

                            </div>
                        </div>

                        <div id="form-builder-empty" class="empty-state d-none" data-height="220">
                            <div class="empty-state-icon bg-primary">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <h2>Belum ada form</h2>
                            <p class="lead">Tambahkan form pertama untuk mulai menyusun struktur assessment dinamis.</p>
                        </div>

                        <div id="form-builder-list"></div>
                    </div>
                    @error('forms')
                        <div class="invalid-feedback mt-2">{{ $message }}</div>
                    @enderror

                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-primary btn-sm" id="btn-add-form"
                            data-builder-loading-lock disabled>
                            <i class="fas fa-plus"></i> Tambah Form
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 sticky-top ">
            <aside class="assessment-builder-sidebar ">
                <div class="assessment-builder-sidebar-inner">
                    <div class="card assessment-summary-card" id="assessment-builder-summary">
                        <div class="card-body">
                            <div class="assessment-summary-eyebrow">Rekapan Assessment</div>
                            <h5 class="mb-2" id="summary-assessment-title">
                                {{ old('judul', $assessment->judul) ?: 'Judul assessment belum diisi' }}
                            </h5>
                            <p class="text-muted mb-2">
                                {{ $isEditMode ? 'Pantau jumlah soal, status, dan kesiapan tampil saat Anda memperbarui struktur assessment.' : 'Pantau jumlah soal, status, dan kesiapan tampil saat Anda menyusun assessment baru.' }}
                            </p>

                            <div class="assessment-summary-grid mb-4">
                                <div class="assessment-summary-stat">
                                    <span class="assessment-summary-stat__label">Total Form</span>
                                    <span class="assessment-summary-stat__value" id="summary-total-forms">{{ $initialBuilderSummary['total_forms'] }}</span>
                                </div>
                                <div class="assessment-summary-stat">
                                    <span class="assessment-summary-stat__label">Total Soal</span>
                                    <span class="assessment-summary-stat__value" id="summary-total-questions">{{ $initialBuilderSummary['total_questions'] }}</span>
                                </div>
                                <div class="assessment-summary-stat">
                                    <span class="assessment-summary-stat__label">Form Aktif</span>
                                    <span class="assessment-summary-stat__value" id="summary-active-forms">{{ $initialBuilderSummary['active_forms'] }}</span>
                                </div>
                                <div class="assessment-summary-stat">
                                    <span class="assessment-summary-stat__label">Skor Otomatis</span>
                                    <span class="assessment-summary-stat__value" id="summary-auto-scoring-questions">{{ $initialBuilderSummary['auto_scoring_questions'] }}</span>
                                </div>
                            </div>

                            <div class="assessment-summary-section">
                                <div class="assessment-summary-section-title">Detail Cepat</div>
                                <div class="assessment-summary-list">
                                    <div class="assessment-summary-row">
                                        <span>Status</span>
                                        <strong id="summary-status-label">{{ $initialBuilderSummary['status_label'] }}</strong>
                                    </div>
                                    <div class="assessment-summary-row">
                                        <span>Aktivasi</span>
                                        <strong id="summary-activation-label">{{ $initialBuilderSummary['is_active'] ? 'Aktif' : 'Nonaktif' }}</strong>
                                    </div>
                                    <div class="assessment-summary-row">
                                        <span>Target</span>
                                        <strong id="summary-target-label">{{ $initialBuilderSummary['target_label'] }}</strong>
                                    </div>
                                    <div class="assessment-summary-row">
                                        <span>Instrumen</span>
                                        <strong id="summary-instrument-label">{{ $initialBuilderSummary['instrument_label'] }}</strong>
                                    </div>
                                    <div class="assessment-summary-row">
                                        <span>Masuk Penilaian</span>
                                        <strong id="summary-scoreable-label">{{ $initialBuilderSummary['scoreable_forms'] }} form</strong>
                                    </div>
                                    <div class="assessment-summary-row">
                                        <span>Siap Tampil</span>
                                        <strong id="summary-display-label">{{ $initialBuilderSummary['display_label'] }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="assessment-summary-note mt-4" id="summary-builder-note">
                                {{ $initialBuilderSummary['builder_note'] }}
                            </div>

                            <div class="mt-4">
                                <button type="button" class="btn btn-outline-primary btn-block" id="btn-sidebar-add-form"
                                    data-builder-loading-lock disabled>
                                    <i class="fas fa-plus"></i> Tambah Form
                                </button>
                            </div>

                            <div class="assessment-summary-actions mt-4 row g-2 px-2 ">

                                @if ($previewUrl)
                                    <a href="{{ $previewUrl }}" class="col btn mx-1 btn-info  ">
                                        <i class="fas fa-eye"></i> Lihat Preview
                                    </a>
                                @endif
                                <a href="{{ route('assessment.index') }}" class="col btn mx-1 btn-light  ">
                                    Kembali
                                </a>
                                <button type="submit" class="col btn mx-1 btn-primary " id="assessment-summary-submit"
                                    data-builder-loading-lock disabled>
                                    <i class="fas fa-save"></i> {{ $submitLabel }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        $(document).ready(function() {
            const assessmentFieldTypes = @json($fieldTypes);
            const competencyLevels = @json($competencyLevels);
            const teacherCompetencies = @json($teacherCompetencies);
            const formScoringProfiles = @json($formScoringProfiles);
            const fieldScoringMethods = @json($fieldScoringMethods);
            const ketenagaanLabels = @json($ketenagaanOptions);
            const instrumentTypes = @json($instrumentTypes);
            const participantAutoFillOptions = @json($participantAutoFillOptions);
            const fieldLookupOptions = @json($fieldLookupOptions);
            const fieldLookupCatalog = @json($fieldLookupCatalog);
            const scoringGuidancePreset = @json($scoringGuidancePreset);
            const initialForms = @json($builderSeed);
            const validationErrors = @json($validationErrors);
            const textOptionFieldTypes = ['select', 'checkbox'];
            const multipleChoiceFieldType = 'radio';
            const repeaterFieldType = 'repeater';
            const fileFieldType = 'file';
            const selectOtherOptionValue = @js(\App\Support\Assessment\ChoiceFieldOtherOption::VALUE);
            const selectOtherOptionLabel = @js(\App\Support\Assessment\ChoiceFieldOtherOption::LABEL);
            const repeaterColumnFieldTypes = {
                text: 'Teks',
                textarea: 'Area Teks',
                number: 'Angka',
                email: 'Email',
                date: 'Tanggal',
                url: 'URL / Link',
                select: 'Daftar Pilihan',
            };
            const participantAutofillSupportedFieldTypes = ['text', 'textarea', 'number', 'email', 'date', 'select', 'radio', 'checkbox'];
            const fieldLookupSupportedFieldTypes = ['select'];
            const columnOptions = ['col-md-12', 'col-md-8', 'col-md-6', 'col-md-4'];
            const $builderShell = $('#assessment-builder-shell');
            const $builderLoading = $('#assessment-builder-loading');
            const $builderLoadingText = $('#assessment-builder-loading-text');
            const $summaryCard = $('#assessment-builder-summary');
            const $loadingLockedActions = $('[data-builder-loading-lock]');
            const $submitButton = $('#assessment-summary-submit');
            const $previewPanel = $('#assessment-preview-panel');
            const $previewContent = $('#assessment-preview-content');
            const $previewToggleButton = $('.btn-toggle-preview-panel');
            const requiredMarker = '<span class="assessment-required">*</span>';
            const errorKeys = Object.keys(validationErrors || {});
            const submitButtonDefaultHtml = $submitButton.html();
            let formIndexCounter = 0;
            let previewRenderTimer = null;

            const escapeHtml = (value) => $('<div>').text(value ?? '').html();
            const joinClasses = (...classes) => classes.filter(Boolean).join(' ');
            const scheduleAfterPaint = (callback) => {
                if (window.requestAnimationFrame) {
                    window.requestAnimationFrame(() => window.requestAnimationFrame(callback));
                    return;
                }

                window.setTimeout(callback, 32);
            };

            const buildBuilderLoadingMessage = (loadedCount, totalCount) => {
                if (totalCount <= 1) {
                    return loadedCount < totalCount
                        ? 'Menyiapkan form awal agar siap diedit.'
                        : 'Merapikan tampilan form...';
                }

                if (loadedCount <= 0) {
                    return `Menyiapkan ${totalCount} form agar siap diedit oleh admin.`;
                }

                if (loadedCount >= totalCount) {
                    return 'Merapikan tampilan akhir assessment...';
                }

                return `Memuat ${loadedCount} dari ${totalCount} form. Tombol simpan akan aktif setelah proses selesai.`;
            };

            const setBuilderLoadingState = (isLoading, message = '') => {
                if ($builderShell.length) {
                    $builderShell
                        .toggleClass('is-loading', isLoading)
                        .attr('aria-busy', isLoading ? 'true' : 'false');
                }

                $builderLoading.toggleClass('d-none', !isLoading);
                $summaryCard.toggleClass('is-loading', isLoading);
                $loadingLockedActions.prop('disabled', isLoading);

                if (message) {
                    $builderLoadingText.text(message);

                    if (isLoading) {
                        $('#summary-builder-note').text(message);
                    }
                }

                if ($submitButton.length) {
                    $submitButton.html(
                        isLoading
                            ? '<i class="fas fa-spinner fa-spin"></i> Memuat Form...'
                            : submitButtonDefaultHtml
                    );
                }
            };

            const nameToErrorKey = (name) => String(name || '')
                .replace(/\[(.*?)\]/g, '.$1')
                .replace(/^\./, '');

            const getFieldError = (name) => {
                const messages = validationErrors[nameToErrorKey(name)] || [];
                return Array.isArray(messages) && messages.length ? messages[0] : '';
            };

            const hasError = (name) => Boolean(getFieldError(name));

            const hasNestedErrors = (prefix) => {
                const normalizedPrefix = nameToErrorKey(prefix);
                return errorKeys.some((key) => key === normalizedPrefix || key.startsWith(`${normalizedPrefix}.`));
            };

            const getInputClass = (name, baseClass = 'form-control') => {
                return joinClasses(baseClass, hasError(name) ? 'is-invalid' : '');
            };

            const buildInvalidFeedback = (name, extraClass = '') => {
                const message = getFieldError(name);

                if (!message) {
                    return '';
                }

                return `<div class="${joinClasses('invalid-feedback', 'd-block', extraClass)}">${escapeHtml(message)}</div>`;
            };

            const buildRequiredLabel = (label) => `${label} ${requiredMarker}`;

            const slugifyFieldName = (value) => String(value || '')
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '')
                .replace(/_+/g, '_');
            const buildAutoFieldNameHint = (labelValue) => {
                const generatedName = slugifyFieldName(labelValue);

                if (!generatedName) {
                    return 'Nama field otomatis akan muncul setelah label diisi.';
                }

                return `Nama field otomatis: <code>${escapeHtml(generatedName)}</code>`;
            };
            const normalizeAutofillKeyword = (value) => String(value || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, ' ')
                .trim()
                .replace(/\s+/g, ' ');
            const participantAutofillSuggestionMap = {
                nama_lengkap: ['nama_lengkap', 'nama lengkap', 'nama peserta'],
                no_ktp: ['nik', 'no ktp', 'nomor ktp', 'ktp'],
                nip_nuptk: ['nip_nuptk', 'nip nuptk'],
                nip: [' nip ', 'nomor induk pegawai', 'nip'],
                nuptk: ['nuptk'],
                golongan: ['golongan', 'pangkat'],
                jabatan: ['jabatan'],
                status_kepegawaian: ['status_kepegawaian', 'status kepegawaian'],
                eksternal_jabatan: ['ketenagaan', 'kelompok jabatan'],
                jenis_jabatan: ['jenis_jabatan', 'jenis jabatan'],
                kategori_jabatan: ['kategori_jabatan', 'kategori jabatan'],
                tugas_jabatan: ['tugas_jabatan', 'tugas jabatan'],
                latar_jabatan: ['latar_jabatan', 'latar jabatan'],
                gender: ['jenis_kelamin', 'jenis kelamin', 'gender', 'kelamin'],
                tempat_lahir: ['tempat_lahir', 'tempat lahir'],
                tgl_lahir: ['tanggal_lahir', 'tanggal lahir', 'tgl_lahir', 'tgl lahir'],
                agama: ['agama'],
                pendidikan: ['pendidikan', 'kualifikasi akademik'],
                email: ['email', 'surel'],
                no_hp: ['no_hp', 'nomor hp', 'no hp', 'telepon'],
                no_wa: ['no_wa', 'nomor wa', 'nomor whatsapp', 'whatsapp'],
                satuan_pendidikan: ['satuan_pendidikan', 'satuan pendidikan', 'sekolah', 'instansi'],
                npsn_sekolah: ['npsn'],
                kabupaten: ['kabupaten_kota', 'kabupaten kota', 'kabupaten', 'kota'],
                alamat_satuan: ['alamat_satuan', 'alamat satuan'],
                alamat_rumah: ['alamat_rumah', 'alamat rumah'],
                npwp: ['npwp'],
                no_rek: ['no_rek', 'nomor rekening', 'rekening'],
                jenis_bank: ['jenis_bank', 'jenis bank', 'bank'],
            };
            const supportsParticipantAutofill = (fieldType) => participantAutofillSupportedFieldTypes.includes(String(fieldType || ''));
            const resolveSuggestedParticipantAutofillSource = (labelValue, fieldNameValue = '') => {
                const haystack = normalizeAutofillKeyword([fieldNameValue, labelValue].filter(Boolean).join(' '));

                if (!haystack) {
                    return '';
                }

                if (['nama', 'nama lengkap'].includes(haystack)) {
                    return 'nama_lengkap';
                }

                return Object.entries(participantAutofillSuggestionMap).find(([, keywords]) => {
                    return keywords.some((keyword) => haystack.includes(normalizeAutofillKeyword(keyword)));
                })?.[0] || '';
            };
            const buildParticipantAutofillOptions = (selectedValue = '') => {
                let optionsHtml = '<option value="">Manual / tanpa auto-fill</option>';

                Object.entries(participantAutoFillOptions).forEach(([value, label]) => {
                    optionsHtml += `
                        <option value="${escapeHtml(value)}" ${String(selectedValue || '') === value ? 'selected' : ''}>
                            ${escapeHtml(label)}
                        </option>
                    `;
                });

                return optionsHtml;
            };
            const buildParticipantAutofillHint = (fieldType, selectedSource = '') => {
                if (!supportsParticipantAutofill(fieldType)) {
                    return 'Auto-fill peserta belum didukung untuk tipe field ini.';
                }

                if (!selectedSource) {
                    return 'Pilih sumber data peserta jika jawaban perlu terisi otomatis saat assessment dibuka.';
                }

                const sourceLabel = participantAutoFillOptions[selectedSource] || selectedSource;

                return `Nilai akan diisi otomatis dari data peserta: <code>${escapeHtml(sourceLabel)}</code>.`;
            };
            const supportsFieldLookup = (fieldType) => fieldLookupSupportedFieldTypes.includes(String(fieldType || ''));
            const resolveSelectedTargetKetenagaanValue = () => $('input[name="target_ketenagaan"]:checked').val() || '';
            const fieldLookupSuggestionMap = {
                master_golongan: ['golongan', 'pangkat'],
                master_golongan_pns: ['golongan pns', 'pangkat pns'],
                master_golongan_pppk: ['golongan pppk', 'pangkat pppk'],
                master_status_kepegawaian: ['status kepegawaian'],
                master_pendidikan: ['pendidikan', 'kualifikasi akademik'],
                master_kabupaten: ['kabupaten kota', 'kabupaten', 'kota'],
                master_satuan_pendidikan: ['satuan pendidikan', 'sekolah', 'instansi'],
                master_jenis_jabatan: ['jenis jabatan'],
                master_tugas_jabatan: ['tugas jabatan'],
                master_latar_jabatan: ['latar jabatan'],
            };
            const resolveSuggestedFieldLookupSource = (labelValue, fieldNameValue = '', targetKetenagaanValue = '') => {
                const haystack = normalizeAutofillKeyword([fieldNameValue, labelValue].filter(Boolean).join(' '));

                if (!haystack) {
                    return '';
                }

                if (
                    haystack.includes('jabatan')
                    && !haystack.includes('jenis jabatan')
                    && !haystack.includes('tugas jabatan')
                    && !haystack.includes('latar jabatan')
                ) {
                    if (targetKetenagaanValue === 'tenaga_pendidik') {
                        return 'master_jabatan_pendidik';
                    }

                    if (targetKetenagaanValue === 'tenaga_kependidikan') {
                        return 'master_jabatan_kependidikan';
                    }

                    if (targetKetenagaanValue === 'stakeholder') {
                        return 'master_jabatan_stakeholder';
                    }

                    return 'master_jabatan_umum';
                }

                return Object.entries(fieldLookupSuggestionMap).find(([, keywords]) => {
                    return keywords.some((keyword) => haystack.includes(normalizeAutofillKeyword(keyword)));
                })?.[0] || '';
            };
            const buildFieldLookupSourceOptions = (selectedValue = '') => {
                let optionsHtml = '<option value="">Manual / tanpa lookup database</option>';

                Object.entries(fieldLookupOptions).forEach(([value, label]) => {
                    optionsHtml += `
                        <option value="${escapeHtml(value)}" ${String(selectedValue || '') === value ? 'selected' : ''}>
                            ${escapeHtml(label)}
                        </option>
                    `;
                });

                return optionsHtml;
            };
            const resolveFieldLookupPreviewMeta = (selectedSource = '') => {
                return fieldLookupCatalog[selectedSource] || null;
            };
            const buildFieldLookupHint = (fieldType, selectedSource = '') => {
                if (!supportsFieldLookup(fieldType)) {
                    return 'Lookup opsi database hanya tersedia untuk field daftar pilihan.';
                }

                if (!selectedSource) {
                    return 'Pilih sumber database jika opsi field perlu mengikuti master data tanpa input manual.';
                }

                const sourceLabel = fieldLookupOptions[selectedSource] || selectedSource;
                const previewMeta = resolveFieldLookupPreviewMeta(selectedSource);
                const totalOptions = Number(previewMeta?.total || 0);

                if (totalOptions < 1) {
                    return `Master data <code>${escapeHtml(sourceLabel)}</code> belum memiliki opsi yang bisa dipakai.`;
                }

                return `Opsi akan diambil otomatis dari <code>${escapeHtml(sourceLabel)}</code>. Saat ini tersedia ${totalOptions} data.`;
            };
            const buildFieldLookupPreview = (fieldType, selectedSource = '') => {
                if (!supportsFieldLookup(fieldType) || !selectedSource) {
                    return '';
                }

                const previewMeta = resolveFieldLookupPreviewMeta(selectedSource);
                const previewItems = Array.isArray(previewMeta?.preview) ? previewMeta.preview : [];

                if (!previewItems.length) {
                    return '<div class="text-warning mt-2">Belum ada contoh opsi dari database.</div>';
                }

                const badgesHtml = previewItems.map((item) => {
                    const label = typeof item === 'object' ? (item.label || item.value || '') : String(item || '');
                    return `<span class="badge badge-light border mr-1 mb-1">${escapeHtml(label)}</span>`;
                }).join('');
                const remaining = Math.max(Number(previewMeta?.total || 0) - previewItems.length, 0);

                return `
                    <div class="mt-2">
                        <div class="mb-1">${badgesHtml}</div>
                        ${remaining > 0 ? `<small class="text-muted">+${remaining} opsi lainnya akan tetap dimuat dari database.</small>` : ''}
                    </div>
                `;
            };
            const resolvePreviewChoiceOptions = (field) => {
                if ((field.tipe_field || '') === 'select' && (field.lookup_source || '')) {
                    const previewItems = resolveFieldLookupPreviewMeta(field.lookup_source)?.preview || [];

                    const options = previewItems.map((item) => {
                        if (item && typeof item === 'object') {
                            return {
                                label: item.label || item.value || '',
                                value: item.value || item.label || '',
                            };
                        }

                        const value = String(item || '');

                        return {
                            label: value,
                            value: value,
                        };
                    }).filter((item) => item.value);

                    return appendSelectOtherOption(options, field.allow_other_input);
                }

                const options = parseOptionText(field.opsi_field_text).map((item) => ({
                    label: item,
                    value: item,
                }));

                return appendSelectOtherOption(options, field.allow_other_input);
            };

            const normalizeChecked = (value) => {
                return value === true || value === 1 || value === '1' || value === 'on';
            };

            const supportsSelectOtherInput = (fieldType) => String(fieldType || '') === 'select';

            const appendSelectOtherOption = (options = [], allowOtherInput = false) => {
                if (!normalizeChecked(allowOtherInput)) {
                    return Array.isArray(options) ? options : [];
                }

                const normalizedOptions = Array.isArray(options) ? [...options] : [];
                const hasOtherOption = normalizedOptions.some((option) => {
                    if (option && typeof option === 'object') {
                        return String(option.value || '').trim() === selectOtherOptionValue;
                    }

                    return String(option || '').trim() === selectOtherOptionValue;
                });

                if (hasOtherOption) {
                    return normalizedOptions;
                }

                normalizedOptions.push({
                    label: selectOtherOptionLabel,
                    value: selectOtherOptionValue,
                    is_other: true,
                });

                return normalizedOptions;
            };

            const buildFieldTypeOptions = (selectedValue) => {
                return Object.entries(assessmentFieldTypes).map(([value, label]) => {
                    const selected = value === selectedValue ? 'selected' : '';
                    return `<option value="${value}" ${selected}>${label}</option>`;
                }).join('');
            };

            const buildColumnOptions = (selectedValue) => {
                return columnOptions.map((value) => {
                    const selected = value === selectedValue ? 'selected' : '';
                    return `<option value="${value}" ${selected}>${value}</option>`;
                }).join('');
            };

            const buildTeacherCompetencyOptions = (selectedValue) => {
                let optionsHtml = '<option value="">Pilih kompetensi</option>';

                Object.entries(teacherCompetencies).forEach(([value, label]) => {
                    const selected = value === selectedValue ? 'selected' : '';
                    optionsHtml += `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
                });

                return optionsHtml;
            };

            const buildFormScoringProfileOptions = (selectedValue) => {
                let optionsHtml = '<option value="">Ikuti instrumen assessment</option>';

                Object.entries(formScoringProfiles).forEach(([value, label]) => {
                    const selected = value === selectedValue ? 'selected' : '';
                    optionsHtml += `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
                });

                return optionsHtml;
            };

            const resolveDefaultScoringMethod = (fieldType) => {
                if (fieldType === multipleChoiceFieldType || fieldType === 'select') {
                    return 'choice_option_score';
                }

                if (fieldType === 'checkbox') {
                    return 'choice_option_average';
                }

                if (fieldType === 'number') {
                    return 'numeric_threshold';
                }

                if (fieldType === repeaterFieldType) {
                    return 'repeater_completeness';
                }

                if (['text', 'textarea'].includes(fieldType)) {
                    return 'semantic_similarity';
                }

                return 'presence';
            };

            const resolveAllowedScoringMethods = (fieldType) => {
                switch (fieldType) {
                    case multipleChoiceFieldType:
                    case 'select':
                        return ['choice_option_score', 'presence'];
                    case 'checkbox':
                        return ['choice_option_average', 'choice_option_sum', 'choice_option_max', 'presence'];
                    case 'number':
                        return ['numeric_threshold', 'numeric_range', 'presence'];
                    case 'textarea':
                    case 'text':
                        return ['semantic_similarity', 'keyword_coverage', 'presence'];
                    case repeaterFieldType:
                        return ['repeater_completeness', 'presence'];
                    default:
                        return ['presence'];
                }
            };

            const buildFieldScoringMethodOptions = (fieldType, selectedValue) => {
                const allowedMethods = resolveAllowedScoringMethods(fieldType);
                const normalizedSelectedValue = allowedMethods.includes(selectedValue)
                    ? selectedValue
                    : resolveDefaultScoringMethod(fieldType);

                return allowedMethods.map((value) => {
                    const selected = value === normalizedSelectedValue ? 'selected' : '';
                    return `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(fieldScoringMethods[value] || value)}</option>`;
                }).join('');
            };

            const normalizeFormScoringConfig = (config = {}) => {
                return {
                    profile: String(config?.profile || '').trim(),
                    weight: config?.weight ?? '',
                    exclude_from_competency: normalizeChecked(config?.exclude_from_competency),
                    advanced_rules_text: String(config?.advanced_rules_text || '').trim(),
                };
            };

            const normalizeFieldScoringConfig = (config = {}, fieldType = 'text') => {
                return {
                    enabled: normalizeChecked(config?.enabled),
                    profile: String(config?.profile || '').trim(),
                    method: String(config?.method || resolveDefaultScoringMethod(fieldType)).trim(),
                    rubric_code: String(config?.rubric_code || '').trim(),
                    weight: config?.weight ?? '',
                    score_if_answered: config?.score_if_answered ?? '',
                    scale_min: config?.scale_min ?? '',
                    scale_max: config?.scale_max ?? '',
                    reference_answer: String(config?.reference_answer || '').trim(),
                    keyword_groups_text: String(config?.keyword_groups_text || '').trim(),
                    synonym_map_text: String(config?.synonym_map_text || '').trim(),
                    min_words: config?.min_words ?? '',
                    confidence_threshold: config?.confidence_threshold ?? '',
                    manual_review_below_confidence: false,
                    numeric_direction: String(config?.numeric_direction || 'greater_is_better').trim(),
                    min_threshold: config?.min_threshold ?? '',
                    target_threshold: config?.target_threshold ?? '',
                    max_threshold: config?.max_threshold ?? '',
                    min_score: config?.min_score ?? '',
                    target_score: config?.target_score ?? '',
                    max_score: config?.max_score ?? '',
                    advanced_rules_text: String(config?.advanced_rules_text || '').trim(),
                };
            };

            const scoringStopWords = new Set(scoringGuidancePreset?.stop_words || []);
            const scoringSynonymLibrary = scoringGuidancePreset?.synonym_library || {};
            const scoringPhraseLibrary = scoringGuidancePreset?.phrase_library || {};
            const scoringSynonymReverseMap = Object.entries(scoringSynonymLibrary).reduce((carry, [baseWord, variants]) => {
                const normalizedBaseWord = String(baseWord || '').trim().toLowerCase();

                if (normalizedBaseWord) {
                    carry[normalizedBaseWord] = normalizedBaseWord;
                }

                (variants || []).forEach((variant) => {
                    const normalizedVariant = String(variant || '').trim().toLowerCase();

                    if (normalizedVariant) {
                        carry[normalizedVariant] = normalizedBaseWord;
                    }
                });

                return carry;
            }, {});

            const normalizeScoringText = (value) => {
                const rawValue = String(value || '').toLowerCase();

                try {
                    return rawValue
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '');
                } catch (error) {
                    return rawValue;
                }
            };

            const sanitizeScoringWords = (value) => normalizeScoringText(value)
                .replace(/[^a-z0-9\s]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            const isMeaningfulScoringWord = (word) => {
                return Boolean(word) && word.length >= 4 && !scoringStopWords.has(word);
            };

            const resolveDefaultMinWords = (fieldType) => {
                if (fieldType === 'textarea') {
                    return 40;
                }

                if (fieldType === 'text') {
                    return 8;
                }

                return 0;
            };

            const formatScoringNumber = (value, fallback = '') => {
                if (value === null || value === undefined || value === '') {
                    return fallback;
                }

                return String(value);
            };

            const extractCuratedScoringTerms = (text) => {
                const normalizedText = sanitizeScoringWords(text);

                return Object.keys(scoringPhraseLibrary).filter((phrase) => normalizedText.includes(phrase));
            };

            const extractAdjacentScoringTerms = (text) => {
                const tokens = sanitizeScoringWords(text).split(/\s+/).filter(Boolean);
                const phrases = [];

                for (let index = 0; index < tokens.length - 1; index += 1) {
                    const first = tokens[index];
                    const second = tokens[index + 1];

                    if (!isMeaningfulScoringWord(first) || !isMeaningfulScoringWord(second)) {
                        continue;
                    }

                    const phrase = `${first} ${second}`;

                    if (!phrases.includes(phrase)) {
                        phrases.push(phrase);
                    }
                }

                return phrases.slice(0, 4);
            };

            const extractImportantScoringWords = (text) => {
                const words = sanitizeScoringWords(text).split(/\s+/).filter(Boolean);
                const uniqueWords = [];

                words.forEach((word) => {
                    if (!isMeaningfulScoringWord(word) || uniqueWords.includes(word)) {
                        return;
                    }

                    uniqueWords.push(word);
                });

                return uniqueWords.slice(0, 8);
            };

            const buildKeywordGroupFromTerm = (term) => {
                const normalizedTerm = sanitizeScoringWords(term);

                if (!normalizedTerm) {
                    return [];
                }

                if (Object.prototype.hasOwnProperty.call(scoringPhraseLibrary, normalizedTerm)) {
                    return [normalizedTerm, ...(scoringPhraseLibrary[normalizedTerm] || [])]
                        .map((item) => String(item || '').trim())
                        .filter(Boolean);
                }

                const baseWord = scoringSynonymReverseMap[normalizedTerm] || normalizedTerm;
                const variants = scoringSynonymLibrary[baseWord] || [];

                return [baseWord, ...variants]
                    .map((item) => String(item || '').trim())
                    .filter(Boolean)
                    .filter((item, index, allItems) => allItems.indexOf(item) === index);
            };

            const estimateScoringMinWords = (text, fieldType) => {
                const wordCount = Math.max(sanitizeScoringWords(text).split(/\s+/).filter(Boolean).length, 1);
                const baseline = fieldType === 'textarea' ? 18 : (fieldType === repeaterFieldType ? 12 : (fieldType === 'text' ? 8 : 5));
                const ratio = fieldType === 'textarea' ? 0.45 : (fieldType === repeaterFieldType ? 0.30 : (fieldType === 'text' ? 0.35 : 0.25));

                return Math.max(baseline, Math.min(Math.round(wordCount * ratio), 60));
            };

            const buildScoringGuidanceSuggestion = (sourceText, fieldType = 'text', context = {}) => {
                const mergedTerms = [
                    ...extractCuratedScoringTerms(sourceText),
                    ...extractAdjacentScoringTerms(sourceText),
                    ...extractImportantScoringWords(sourceText),
                ].filter(Boolean).filter((term, index, allTerms) => allTerms.indexOf(term) === index).slice(0, 6);
                const keywordGroups = mergedTerms
                    .map((term) => buildKeywordGroupFromTerm(term))
                    .filter((group) => group.length);
                const synonymLines = keywordGroups
                    .filter((group) => group.length > 1)
                    .slice(0, 4)
                    .map((group) => `${group[0]}: ${group.slice(1).join(', ')}`);
                const signalKeywords = keywordGroups
                    .map((group) => group[0])
                    .filter(Boolean)
                    .slice(0, 5);
                const advancedRules = {};

                if (signalKeywords.length) {
                    advancedRules.signal_keywords = signalKeywords;
                }

                if (fieldType === repeaterFieldType) {
                    advancedRules.target_rows = Math.max(Number(context?.target_rows || 1), 1);
                }

                return {
                    keyword_groups_text: keywordGroups.length
                        ? keywordGroups.map((group) => group[0]).filter(Boolean).join(', ')
                        : '',
                    synonym_map_text: synonymLines.join('\n'),
                    min_words: estimateScoringMinWords(sourceText, fieldType),
                    advanced_rules_text: Object.keys(advancedRules).length ? JSON.stringify(advancedRules, null, 2) : '',
                };
            };

            const keywordGroupsFieldSelector = 'textarea[name$="[scoring][keyword_groups_text]"]';
            const keywordGroupsCommaOnlyMessage = 'Pisahkan kata kunci hanya dengan koma. Contoh: sertifikat, program studi, lembaga. Jangan gunakan Enter, tanda |, atau titik koma.';

            const normalizeKeywordGroupsText = (value) => {
                return String(value || '')
                    .split(',')
                    .map((keyword) => keyword.trim())
                    .filter(Boolean)
                    .filter((keyword, index, keywords) => keywords.indexOf(keyword) === index)
                    .join(', ');
            };

            const getKeywordGroupsValidationMessage = (value) => {
                const rawValue = String(value || '').trim();

                if (!rawValue) {
                    return '';
                }

                return /[\r\n|;]/.test(rawValue) ? keywordGroupsCommaOnlyMessage : '';
            };

            const setKeywordGroupsFieldValidationState = ($input, message = '') => {
                if (!$input.length) {
                    return;
                }

                const inputElement = $input.get(0);
                const $clientFeedback = $input.siblings('.keyword-groups-client-feedback');
                const hasServerFeedback = $input.siblings('.invalid-feedback').not('.keyword-groups-client-feedback').length > 0;

                if (inputElement?.setCustomValidity) {
                    inputElement.setCustomValidity(message || '');
                }

                if (!message) {
                    $clientFeedback.remove();

                    if (!hasServerFeedback) {
                        $input.removeClass('is-invalid');
                    }

                    return;
                }

                if ($clientFeedback.length) {
                    $clientFeedback.text(message);
                } else {
                    $('<div class="invalid-feedback d-block keyword-groups-client-feedback"></div>')
                        .text(message)
                        .insertAfter($input);
                }

                $input.addClass('is-invalid');
            };

            const validateKeywordGroupsField = ($input, options = {}) => {
                if (!$input.length) {
                    return true;
                }

                const message = getKeywordGroupsValidationMessage($input.val());

                if (!message && options.normalize) {
                    $input.val(normalizeKeywordGroupsText($input.val()));
                }

                setKeywordGroupsFieldValidationState($input, message);

                return !message;
            };

            const validateAllKeywordGroupsFields = () => {
                let $firstInvalidField = null;

                $(keywordGroupsFieldSelector).each(function() {
                    const $input = $(this);
                    const isValid = validateKeywordGroupsField($input, { normalize: true });

                    if (!isValid && !$firstInvalidField) {
                        $firstInvalidField = $input;
                    }
                });

                if ($firstInvalidField) {
                    $firstInvalidField.trigger('focus');
                    $firstInvalidField.get(0)?.reportValidity?.();

                    return false;
                }

                return true;
            };

            const resolveScoringGuidanceSource = ($fieldCard) => {
                return [
                    $fieldCard.find('textarea[name$="[scoring][reference_answer]"]').val()?.trim() || '',
                    $fieldCard.find('textarea[name$="[deskripsi]"]').val()?.trim() || '',
                    $fieldCard.find('textarea[name$="[bantuan]"]').val()?.trim() || '',
                ].filter(Boolean).join('\n');
            };

            const updateScoringAssistantStatus = ($fieldCard, message = '') => {
                $fieldCard.find('.scoring-helper-status').text(message);
            };

            const supportsScoringAssistant = (fieldType, method) => {
                return (
                    ['text', 'textarea'].includes(fieldType)
                    && ['semantic_similarity', 'keyword_coverage'].includes(method)
                ) || (fieldType === repeaterFieldType && method === 'repeater_completeness');
            };

            const applyScoringGuidanceSuggestion = ($fieldCard, options = {}) => {
                const fieldType = $fieldCard.find('.field-type-select').val() || 'text';
                const method = $fieldCard.find('.field-scoring-method').val() || resolveDefaultScoringMethod(fieldType);

                if (!supportsScoringAssistant(fieldType, method)) {
                    return false;
                }

                if (options.copyDescriptionToReference) {
                    const currentReference = $fieldCard.find('textarea[name$="[scoring][reference_answer]"]').val()?.trim() || '';
                    const descriptionText = $fieldCard.find('textarea[name$="[deskripsi]"]').val()?.trim() || '';

                    if (!currentReference && descriptionText) {
                        $fieldCard.find('textarea[name$="[scoring][reference_answer]"]').val(descriptionText);
                    }
                }

                const sourceText = resolveScoringGuidanceSource($fieldCard);

                if (!sourceText) {
                    updateScoringAssistantStatus($fieldCard, 'Belum ada deskripsi atau pedoman yang bisa diolah.');
                    return false;
                }

                const repeaterConfig = fieldType === repeaterFieldType
                    ? collectRepeaterConfigData($fieldCard)
                    : null;
                const suggestion = buildScoringGuidanceSuggestion(sourceText, fieldType, {
                    target_rows: repeaterConfig?.min_rows || 1,
                });
                const shouldForce = Boolean(options.force);
                const fillIfNeeded = (selector, value) => {
                    const $input = $fieldCard.find(selector);

                    if (!value || !$input.length) {
                        return;
                    }

                    if (shouldForce || !String($input.val() || '').trim()) {
                        $input.val(value);
                    }
                };

                fillIfNeeded('textarea[name$="[scoring][keyword_groups_text]"]', suggestion.keyword_groups_text);
                fillIfNeeded('textarea[name$="[scoring][synonym_map_text]"]', suggestion.synonym_map_text);
                fillIfNeeded('input[name$="[scoring][min_words]"]', suggestion.min_words);
                fillIfNeeded('textarea[name$="[scoring][advanced_rules_text]"]', suggestion.advanced_rules_text);
                validateKeywordGroupsField($fieldCard.find(keywordGroupsFieldSelector), { normalize: true });

                updateScoringAssistantStatus(
                    $fieldCard,
                    shouldForce
                    ? 'Bantuan otomatis diperbarui. Hasilnya masih bisa disesuaikan.'
                    : 'Saran default disiapkan dari deskripsi dan pedoman yang ada.'
                );

                return true;
            };

            const resolveScoringSummaryMessage = (fieldType, method) => {
                if (method === 'presence') {
                    return 'Cocok untuk field yang cukup dicek keberadaan jawabannya, seperti unggah file, tanggal, atau isian singkat.';
                }

                if (fieldType === 'number') {
                    return 'Isi batas angka minimum, target, dan maksimum. Sistem akan mengubahnya menjadi nilai secara otomatis.';
                }

                if (fieldType === repeaterFieldType) {
                    return 'Tuliskan gambaran isi tabel yang baik. Sistem akan menilai kelengkapan baris, kolom wajib, dan mutu isi tabel.';
                }

                if (['text', 'textarea'].includes(fieldType) && method === 'keyword_coverage') {
                    return 'Fokuskan pada kata kunci utama yang wajib muncul dalam jawaban peserta.';
                }

                if (['text', 'textarea'].includes(fieldType)) {
                    return 'Tuliskan pedoman jawaban dengan bahasa sehari-hari. Sistem akan membaca makna jawaban dan mencocokkannya.';
                }

                if (['radio', 'select', 'checkbox'].includes(fieldType)) {
                    return 'Nilai utama diambil dari skor pada setiap opsi jawaban yang Anda isi di bagian atas.';
                }

                return 'Sistem akan memakai aturan nilai yang paling sesuai dengan tipe pertanyaan ini.';
            };

            const buildScoringSummaryHtml = ($fieldCard, fieldType, method) => {
                const scaleMin = $fieldCard.find('input[name$="[scoring][scale_min]"]').val()?.trim() || '1';
                const scaleMax = $fieldCard.find('input[name$="[scoring][scale_max]"]').val()?.trim() || '5';
                const minWords = $fieldCard.find('input[name$="[scoring][min_words]"]').val()?.trim() || resolveDefaultMinWords(fieldType);
                const confidence = $fieldCard.find('input[name$="[scoring][confidence_threshold]"]').val()?.trim() || '0.55';
                const summaryPills = [
                    `Cara nilai: ${fieldScoringMethods[method] || method}`,
                    `Skala: ${formatScoringNumber(scaleMin)}-${formatScoringNumber(scaleMax)}`,
                ];

                if (supportsScoringAssistant(fieldType, method)) {
                    summaryPills.push(`Saran minimal kata: ${minWords}`);
                    summaryPills.push(`Ambang keyakinan: ${confidence}`);
                }

                if (['radio', 'select', 'checkbox'].includes(fieldType) && method !== 'presence') {
                    summaryPills.push('Skor mengikuti opsi jawaban');
                }

                if (fieldType === 'number' && method !== 'presence') {
                    summaryPills.push('Nilai mengikuti target angka');
                }

                return summaryPills.map((pill) => `<span class="scoring-summary-pill">${escapeHtml(pill)}</span>`).join('');
            };

            const generateChoiceLabel = (index) => {
                let label = '';
                let number = index + 1;

                while (number > 0) {
                    number -= 1;
                    label = String.fromCharCode(65 + (number % 26)) + label;
                    number = Math.floor(number / 26);
                }

                return label;
            };

            const getDefaultCompetencyLevel = (index) => {
                const optionNumber = Number(index) + 1;
                return competencyLevels[String(optionNumber)] ? String(optionNumber) : '';
            };

            const normalizeCompetencyLevelValue = (value, fallbackIndex = null) => {
                const normalizedValue = String(value ?? '').trim();

                if (normalizedValue && competencyLevels[normalizedValue]) {
                    return normalizedValue;
                }

                if (fallbackIndex === null || fallbackIndex === undefined) {
                    return '';
                }

                return getDefaultCompetencyLevel(fallbackIndex);
            };

            const resolveCompetencyLevelLabel = (value) => {
                const normalizedValue = normalizeCompetencyLevelValue(value);
                return normalizedValue ? competencyLevels[normalizedValue] || '' : '';
            };

            const buildCompetencyLevelOptions = (selectedValue, optionIndex = null) => {
                const normalizedValue = normalizeCompetencyLevelValue(selectedValue, optionIndex);
                let optionsHtml = '<option value="">Pilih level</option>';

                Object.entries(competencyLevels).forEach(([value, label]) => {
                    const selected = value === normalizedValue ? 'selected' : '';
                    optionsHtml += `<option value="${escapeHtml(value)}" ${selected}>${escapeHtml(label)}</option>`;
                });

                return optionsHtml;
            };

            const looksLikeChoiceCode = (value) => {
                const normalizedValue = String(value || '').trim();

                if (!normalizedValue || /\s/.test(normalizedValue) || normalizedValue.length > 6) {
                    return false;
                }

                return /^[A-Za-z0-9._-]+$/.test(normalizedValue);
            };

            const looksLikeChoiceAnswerText = (value) => {
                const normalizedValue = String(value || '').trim();

                if (!normalizedValue) {
                    return false;
                }

                return /\s/.test(normalizedValue) || normalizedValue.length >= 6;
            };

            const normalizeRadioOptionShape = (option = {}) => {
                if (typeof option === 'string') {
                    const normalizedValue = option.trim();

                    return {
                        label: normalizedValue,
                        value: normalizedValue,
                        level_kompetensi: '',
                    };
                }

                let optionLabel = String(option?.label || '').trim();
                let optionValue = String(option?.value || '').trim();

                if (looksLikeChoiceCode(optionLabel) && looksLikeChoiceAnswerText(optionValue)) {
                    [optionLabel, optionValue] = [optionValue, optionLabel];
                }

                if (!optionLabel && optionValue) {
                    optionLabel = optionValue;
                }

                if (!optionValue && optionLabel) {
                    optionValue = optionLabel;
                }

                return {
                    label: optionLabel,
                    value: optionValue,
                    level_kompetensi: normalizeCompetencyLevelValue(option?.level_kompetensi),
                };
            };

            const normalizeRadioOptions = (options = []) => {
                if (!Array.isArray(options) || !options.length) {
                    return [{
                        label: '',
                        value: '',
                        level_kompetensi: getDefaultCompetencyLevel(0),
                    }, {
                        label: '',
                        value: '',
                        level_kompetensi: getDefaultCompetencyLevel(1),
                    }];
                }

                const normalizedOptions = options.map((option, index) => ({
                    ...normalizeRadioOptionShape(option),
                    score: option?.score ?? '',
                    level_kompetensi: normalizeCompetencyLevelValue(option?.level_kompetensi, index),
                }));

                while (normalizedOptions.length < 2) {
                    normalizedOptions.push({
                        label: '',
                        value: '',
                        score: '',
                        level_kompetensi: getDefaultCompetencyLevel(normalizedOptions.length),
                    });
                }

                return normalizedOptions;
            };

            const normalizeRepeaterColumnType = (value) => {
                const normalizedValue = String(value || '').trim();
                return Object.prototype.hasOwnProperty.call(repeaterColumnFieldTypes, normalizedValue)
                    ? normalizedValue
                    : 'text';
            };

            const normalizeNonNegativeInteger = (value, fallback = 0) => {
                const numericValue = Number(value);

                if (!Number.isFinite(numericValue) || numericValue < 0) {
                    return fallback;
                }

                return Math.round(numericValue);
            };

            const buildDefaultRepeaterConfig = () => ({
                min_rows: 1,
                max_rows: 10,
                columns: [
                    {
                        label: 'Kolom 1',
                        nama_field: 'kolom_1',
                        tipe_field: 'text',
                        placeholder: 'Isi kolom 1',
                        opsi_field: [],
                        is_required: true,
                        auto_generated: true,
                    },
                    {
                        label: 'Kolom 2',
                        nama_field: 'kolom_2',
                        tipe_field: 'text',
                        placeholder: 'Isi kolom 2',
                        opsi_field: [],
                        is_required: false,
                        auto_generated: true,
                    },
                ],
            });

            const normalizeRepeaterColumnOptions = (value) => {
                if (!Array.isArray(value)) {
                    return [];
                }

                return value.map((item) => {
                    if (item && typeof item === 'object') {
                        return String(item.label || item.value || '').trim();
                    }

                    return String(item || '').trim();
                }).filter(Boolean);
            };

            const normalizeRepeaterColumnShape = (column = {}, index = 0) => {
                const rawColumn = column && typeof column === 'object' ? column : {};
                const label = String(rawColumn.label ?? '').trim();
                const explicitFieldName = String(rawColumn.nama_field ?? '').trim();
                const generatedFieldName = slugifyFieldName(explicitFieldName || label || `kolom_${index + 1}`) || `kolom_${index + 1}`;

                return {
                    label: label,
                    nama_field: generatedFieldName,
                    tipe_field: normalizeRepeaterColumnType(rawColumn.tipe_field),
                    placeholder: String(rawColumn.placeholder ?? '').trim(),
                    opsi_field: normalizeRepeaterColumnOptions(rawColumn.opsi_field),
                    is_required: normalizeChecked(rawColumn.is_required),
                    auto_generated: rawColumn.auto_generated === true
                        || explicitFieldName === ''
                        || explicitFieldName === slugifyFieldName(label),
                };
            };

            const normalizeRepeaterConfigData = (value) => {
                const isBlankString = typeof value === 'string' && String(value).trim() === '';
                const useDefaultConfig = value === null || value === undefined || isBlankString;
                const fallbackConfig = buildDefaultRepeaterConfig();
                const rawConfig = typeof value === 'string'
                    ? parseJsonSafely(value)
                    : (value && typeof value === 'object' ? value : null);

                if (!rawConfig || typeof rawConfig !== 'object') {
                    return fallbackConfig;
                }

                const rawColumns = Array.isArray(rawConfig.columns)
                    ? rawConfig.columns
                    : (useDefaultConfig ? fallbackConfig.columns : []);

                return {
                    min_rows: normalizeNonNegativeInteger(rawConfig.min_rows, 0),
                    max_rows: normalizeNonNegativeInteger(rawConfig.max_rows, 0),
                    columns: rawColumns.map((column, index) => normalizeRepeaterColumnShape(column, index)),
                };
            };

            const buildRepeaterConfigJson = (config = {}) => {
                const normalizedConfig = normalizeRepeaterConfigData(config);

                return JSON.stringify({
                    min_rows: normalizeNonNegativeInteger(normalizedConfig.min_rows, 0),
                    max_rows: normalizeNonNegativeInteger(normalizedConfig.max_rows, 0),
                    columns: (normalizedConfig.columns || []).map((column, index) => {
                        const normalizedColumn = normalizeRepeaterColumnShape(column, index);

                        return {
                            label: normalizedColumn.label,
                            nama_field: normalizedColumn.nama_field,
                            tipe_field: normalizedColumn.tipe_field,
                            placeholder: normalizedColumn.placeholder,
                            opsi_field: normalizedColumn.tipe_field === 'select' ? normalizedColumn.opsi_field : [],
                            is_required: normalizedColumn.is_required,
                        };
                    }),
                });
            };

            const parseJsonSafely = (value) => {
                const rawValue = String(value || '').trim();

                if (!rawValue) {
                    return null;
                }

                try {
                    const parsed = JSON.parse(rawValue);
                    return parsed && typeof parsed === 'object' ? parsed : null;
                } catch (error) {
                    return null;
                }
            };

            const normalizeFileInputMode = (value) => String(value || '').trim() === 'link' ? 'link' : 'file';

            const normalizeFileFieldConfig = (config = {}) => {
                const normalizedConfig = config && typeof config === 'object' ? config : {};
                const accept = Array.isArray(normalizedConfig.accept)
                    ? normalizedConfig.accept.map((item) => String(item || '').trim()).filter(Boolean)
                    : [];
                const maxSizeKb = Number(normalizedConfig.max_size_kb || 0) > 0
                    ? Number(normalizedConfig.max_size_kb)
                    : 5120;
                const maxFiles = Number(normalizedConfig.max_files || 0) > 0
                    ? Number(normalizedConfig.max_files)
                    : 1;

                return {
                    input_mode: normalizeFileInputMode(normalizedConfig.input_mode),
                    accept: accept,
                    max_size_kb: Math.max(1, Math.round(maxSizeKb)),
                    max_files: Math.max(1, Math.round(maxFiles)),
                };
            };

            const parseFileFieldConfigJson = (value) => {
                return normalizeFileFieldConfig(parseJsonSafely(value) || {});
            };

            const buildFileFieldConfigPayload = (rawValue, inputMode = 'file') => {
                const normalizedConfig = normalizeFileFieldConfig({
                    ...parseFileFieldConfigJson(rawValue),
                    input_mode: inputMode,
                });

                return {
                    input_mode: normalizedConfig.input_mode,
                    ...(normalizedConfig.accept.length ? {
                        accept: normalizedConfig.accept
                    } : {}),
                    max_size_kb: normalizedConfig.max_size_kb,
                    max_files: normalizedConfig.max_files,
                };
            };

            const buildFileFieldConfigJson = (rawValue, inputMode = 'file') => {
                return JSON.stringify(buildFileFieldConfigPayload(rawValue, inputMode));
            };

            const buildFileInputModeOptions = (selectedValue = 'file') => {
                const normalizedValue = normalizeFileInputMode(selectedValue);

                return `
                    <option value="file" ${normalizedValue === 'file' ? 'selected' : ''}>Unggah file</option>
                    <option value="link" ${normalizedValue === 'link' ? 'selected' : ''}>Link file / Google Drive</option>
                `;
            };

            const buildFileConfigHint = (selectedMode = 'file') => {
                const normalizedMode = normalizeFileInputMode(selectedMode);

                if (normalizedMode === 'link') {
                    return 'Peserta akan mengisi tautan file, misalnya link Google Drive sertifikat atau SK.';
                }

                return 'Peserta akan mengunggah file langsung ke sistem.';
            };

            const buildRepeaterColumnTypeOptions = (selectedValue = 'text') => {
                const normalizedValue = normalizeRepeaterColumnType(selectedValue);

                return Object.entries(repeaterColumnFieldTypes).map(([value, label]) => `
                    <option value="${escapeHtml(value)}" ${normalizedValue === value ? 'selected' : ''}>
                        ${escapeHtml(label)}
                    </option>
                `).join('');
            };

            const buildRepeaterColumnRow = (columnIndex, columnData = {}) => {
                const normalizedColumn = normalizeRepeaterColumnShape(columnData, columnIndex);
                const showSelectOptions = normalizedColumn.tipe_field === 'select';
                const optionLines = normalizedColumn.opsi_field.join('\n');
                const fieldNameHint = normalizedColumn.nama_field
                    ? `Key penyimpanan: <code>${escapeHtml(normalizedColumn.nama_field)}</code>`
                    : 'Nama field otomatis dibuat dari label kolom.';
                const requiredInputId = `repeater-column-required-${Date.now()}-${columnIndex}-${Math.random().toString(36).slice(2, 8)}`;

                return `
                    <div class="repeater-column-row mb-3" data-column-index="${columnIndex}">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="repeater-column-row__title">Kolom ${columnIndex + 1}</div>
                                <small class="repeater-column-row__meta">
                                    ${escapeHtml(normalizedColumn.label || 'Label kolom belum diisi')}
                                </small>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-repeater-column">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>${buildRequiredLabel('Label Kolom')}</label>
                                    <input type="text" class="form-control repeater-column-label-input"
                                        value="${escapeHtml(normalizedColumn.label)}"
                                        placeholder="Contoh: Pengalaman">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>${buildRequiredLabel('Nama Field')}</label>
                                    <input type="text" class="form-control repeater-column-name-input"
                                        value="${escapeHtml(normalizedColumn.nama_field)}"
                                        data-auto-generated="${normalizedColumn.auto_generated ? '1' : '0'}"
                                        placeholder="pengalaman">
                                    <small class="form-text text-muted repeater-column-name-hint">${fieldNameHint}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>${buildRequiredLabel('Tipe Input')}</label>
                                    <select class="form-control repeater-column-type-select">
                                        ${buildRepeaterColumnTypeOptions(normalizedColumn.tipe_field)}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-md-0">
                                    <label>Placeholder</label>
                                    <input type="text" class="form-control repeater-column-placeholder-input"
                                        value="${escapeHtml(normalizedColumn.placeholder)}"
                                        placeholder="Placeholder kolom">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="custom-control custom-switch repeater-column-required-switch mb-0">
                                    <input type="checkbox" class="custom-control-input repeater-column-required-input"
                                        id="${requiredInputId}"
                                        ${normalizedColumn.is_required ? 'checked' : ''}>
                                    <label class="custom-control-label" for="${requiredInputId}">
                                        Wajib diisi
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="${joinClasses('form-group', 'repeater-column-options-wrapper', showSelectOptions ? '' : 'd-none')}">
                            <label class="mt-3">Opsi Pilihan</label>
                            <textarea class="form-control repeater-column-options-input"
                                rows="3"
                                placeholder="Contoh: SD&#10;SMP&#10;SMA">${escapeHtml(optionLines)}</textarea>
                            <small class="text-muted d-block mt-2">
                                Isi opsi per baris atau dipisah koma jika tipe kolom menggunakan daftar pilihan.
                            </small>
                        </div>
                    </div>
                `;
            };

            const buildRadioOptionRow = (formIndex, fieldIndex, optionIndex, optionData = {}) => {
                const normalizedOption = normalizeRadioOptionShape(optionData);
                const optionText = normalizedOption.label || '';
                const optionCode = normalizedOption.value || '';
                const optionCompetencyLevel = normalizeCompetencyLevelValue(
                    normalizedOption.level_kompetensi,
                    optionIndex
                );
                const optionScore = optionData?.score ?? optionCompetencyLevel ?? '';
                const optionTextName = `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][label]`;
                const optionCodeName = `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][value]`;
                const optionScoreName = `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][score]`;
                const optionCompetencyLevelName =
                    `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][level_kompetensi]`;
                const generatedCode = generateChoiceLabel(optionIndex);

                return `
                    <div class="multiple-choice-option-row mb-2" data-option-index="${optionIndex}">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <div class="form-group mb-md-0">
                                    <label>${buildRequiredLabel('Kode')}</label>
                                    <input type="text" class="${getInputClass(optionCodeName, 'form-control radio-option-code')}"
                                        name="${optionCodeName}"
                                        value="${escapeHtml(optionCode)}"
                                        placeholder="${escapeHtml(generatedCode)}">
                                    ${buildInvalidFeedback(optionCodeName)}
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group mb-md-0">
                                    <label>${buildRequiredLabel('Isi Jawaban')}</label>
                                    <input type="text" class="${getInputClass(optionTextName, 'form-control radio-option-text')}"
                                        name="${optionTextName}"
                                        value="${escapeHtml(optionText)}"
                                        placeholder="Contoh: Mengenali faktor yang memengaruhi perilaku peserta didik">
                                    ${buildInvalidFeedback(optionTextName)}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-md-0">
                                    <label>Skor</label>
                                    <input type="number" min="0" max="5" step="0.01"
                                        class="${getInputClass(optionScoreName, 'form-control radio-option-score')}"
                                        name="${optionScoreName}"
                                        value="${escapeHtml(optionScore)}"
                                        placeholder="${escapeHtml(optionCompetencyLevel || '')}">
                                    ${buildInvalidFeedback(optionScoreName)}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-md-0">
                                    <label>${buildRequiredLabel('Level Kompetensi')}</label>
                                    <select class="${getInputClass(optionCompetencyLevelName, 'form-control radio-option-level')}"
                                        name="${optionCompetencyLevelName}">
                                        ${buildCompetencyLevelOptions(optionCompetencyLevel, optionIndex)}
                                    </select>
                                    ${buildInvalidFeedback(optionCompetencyLevelName)}
                                </div>
                            </div>
                            <div class="col-md-1 text-md-right">
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-radio-option">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            };

            const buildFieldCard = (formIndex, fieldIndex, fieldData = {}) => {
                const fieldType = fieldData.tipe_field || 'text';
                const showTextOptions = textOptionFieldTypes.includes(fieldType);
                const showMultipleChoiceOptions = fieldType === multipleChoiceFieldType;
                const repeaterConfig = normalizeRepeaterConfigData(fieldData.repeater_config_text);
                const radioOptions = normalizeRadioOptions(fieldData.radio_options);
                const scoringData = normalizeFieldScoringConfig(fieldData.scoring || {}, fieldType);
                const fileFieldConfig = parseFileFieldConfigJson(fieldData.raw_opsi_field_json || '');
                const resolvedFileInputMode = normalizeFileInputMode(fieldData.file_input_mode || fileFieldConfig.input_mode);
                const resolvedAllowOtherInput = supportsSelectOtherInput(fieldType) && normalizeChecked(fieldData.allow_other_input);
                const resolvedAutofillSource = fieldData.autofill_source || resolveSuggestedParticipantAutofillSource(
                    fieldData.label || '',
                    fieldData.nama_field || ''
                );
                const resolvedLookupSource = fieldData.lookup_source || resolveSuggestedFieldLookupSource(
                    fieldData.label || '',
                    fieldData.nama_field || '',
                    resolveSelectedTargetKetenagaanValue()
                );
                const fieldPrefix = `forms[${formIndex}][fields][${fieldIndex}]`;
                const fieldIdName = `${fieldPrefix}[id]`;
                const labelName = `${fieldPrefix}[label]`;
                const deskripsiName = `${fieldPrefix}[deskripsi]`;
                const tipeFieldName = `${fieldPrefix}[tipe_field]`;
                const placeholderName = `${fieldPrefix}[placeholder]`;
                const autofillSourceName = `${fieldPrefix}[autofill_source]`;
                const lookupSourceName = `${fieldPrefix}[lookup_source]`;
                const allowOtherInputName = `${fieldPrefix}[allow_other_input]`;
                const fileInputModeName = `${fieldPrefix}[file_input_mode]`;
                const urutanName = `${fieldPrefix}[urutan]`;
                const opsiFieldTextName = `${fieldPrefix}[opsi_field_text]`;
                const opsiScoreTextName = `${fieldPrefix}[opsi_score_text]`;
                const repeaterConfigName = `${fieldPrefix}[repeater_config_text]`;
                const rawOpsiFieldJsonName = `${fieldPrefix}[raw_opsi_field_json]`;
                const radioOptionsName = `${fieldPrefix}[radio_options]`;
                const bantuanName = `${fieldPrefix}[bantuan]`;
                const lebarKolomName = `${fieldPrefix}[lebar_kolom]`;
                const scoringPrefix = `${fieldPrefix}[scoring]`;
                const scoringEnabledName = `${scoringPrefix}[enabled]`;
                const scoringProfileName = `${scoringPrefix}[profile]`;
                const scoringMethodName = `${scoringPrefix}[method]`;
                const scoringRubricCodeName = `${scoringPrefix}[rubric_code]`;
                const scoringWeightName = `${scoringPrefix}[weight]`;
                const scoringPresenceName = `${scoringPrefix}[score_if_answered]`;
                const scoringScaleMinName = `${scoringPrefix}[scale_min]`;
                const scoringScaleMaxName = `${scoringPrefix}[scale_max]`;
                const scoringReferenceAnswerName = `${scoringPrefix}[reference_answer]`;
                const scoringKeywordGroupsName = `${scoringPrefix}[keyword_groups_text]`;
                const scoringSynonymMapName = `${scoringPrefix}[synonym_map_text]`;
                const scoringMinWordsName = `${scoringPrefix}[min_words]`;
                const scoringConfidenceThresholdName = `${scoringPrefix}[confidence_threshold]`;
                const scoringManualReviewName = `${scoringPrefix}[manual_review_below_confidence]`;
                const scoringNumericDirectionName = `${scoringPrefix}[numeric_direction]`;
                const scoringMinThresholdName = `${scoringPrefix}[min_threshold]`;
                const scoringTargetThresholdName = `${scoringPrefix}[target_threshold]`;
                const scoringMaxThresholdName = `${scoringPrefix}[max_threshold]`;
                const scoringMinScoreName = `${scoringPrefix}[min_score]`;
                const scoringTargetScoreName = `${scoringPrefix}[target_score]`;
                const scoringMaxScoreName = `${scoringPrefix}[max_score]`;
                const scoringAdvancedRulesName = `${scoringPrefix}[advanced_rules_text]`;
                const fieldCardClass = joinClasses(
                    '',
                    'border',
                    'assessment-field-card',
                    'mb-3',
                    hasNestedErrors(fieldPrefix) ? 'border-danger' : ''
                );
                const standardOptionWrapperClass = joinClasses(
                    'form-group',
                    'standard-option-wrapper',
                    showTextOptions ? '' : 'd-none',
                );
                const lookupSourceWrapperClass = joinClasses(
                    'form-group',
                    'field-lookup-source-wrapper',
                    fieldType === 'select' ? '' : 'd-none',
                );
                const allowOtherInputWrapperClass = joinClasses(
                    'form-group',
                    'field-allow-other-input-wrapper',
                    supportsSelectOtherInput(fieldType) ? '' : 'd-none',
                );
                const multipleChoiceWrapperClass = joinClasses(
                    'multiple-choice-wrapper',
                    showMultipleChoiceOptions ? '' : 'd-none',
                );
                const repeaterWrapperClass = joinClasses(
                    'form-group',
                    'repeater-option-wrapper',
                    fieldType === repeaterFieldType ? '' : 'd-none',
                );
                const repeaterConfigShellClass = joinClasses(
                    'repeater-config-shell',
                    hasError(repeaterConfigName) ? 'assessment-invalid-wrapper' : '',
                );
                const fileOptionWrapperClass = joinClasses(
                    'form-group',
                    'file-option-wrapper',
                    fieldType === fileFieldType ? '' : 'd-none',
                );

                return `
                    <div class="${fieldCardClass}" data-field-index="${fieldIndex}" data-radio-option-counter="${radioOptions.length}">
                        <div class="card-body">
                            <input type="hidden" class="assessment-field-id-input"
                                name="${fieldIdName}"
                                value="${escapeHtml(fieldData.id || '')}">
                            <input type="hidden"
                                name="${rawOpsiFieldJsonName}"
                                value="${escapeHtml(fieldData.raw_opsi_field_json || '')}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Pertanyaan ${fieldIndex + 1}</h6>
                                <div class="assessment-builder-actions">
                                    <input type="checkbox" class="d-none"
                                        name="forms[${formIndex}][fields][${fieldIndex}][is_required]"
                                        value="1" ${normalizeChecked(fieldData.is_required) ? 'checked' : ''}>
                                    <input type="checkbox" class="d-none"
                                        name="forms[${formIndex}][fields][${fieldIndex}][is_active]"
                                        value="1" ${fieldData.is_active === undefined || normalizeChecked(fieldData.is_active) ? 'checked' : ''}>
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-field">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>${buildRequiredLabel('Label Field')}</label>
                                        <input type="text" class="${getInputClass(labelName, 'form-control field-label-input')}"
                                            name="${labelName}"
                                            value="${escapeHtml(fieldData.label)}"
                                            placeholder="Contoh: Nama Lengkap"
                                            required>
                                        ${buildInvalidFeedback(labelName)}
                                        <small class="form-text text-muted auto-field-name-hint">
                                            ${buildAutoFieldNameHint(fieldData.label)}
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>${buildRequiredLabel('Tipe Pertanyaan')}</label>
                                        <select class="${getInputClass(tipeFieldName, 'form-control field-type-select')}"
                                            name="${tipeFieldName}"
                                            required>
                                            ${buildFieldTypeOptions(fieldType)}
                                        </select>
                                        ${buildInvalidFeedback(tipeFieldName)}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Urutan</label>
                                        <input type="number" min="1" class="${getInputClass(urutanName)}"
                                            name="${urutanName}"
                                            value="${escapeHtml(fieldData.urutan || fieldIndex + 1)}">
                                        ${buildInvalidFeedback(urutanName)}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group participant-autofill-wrapper">
                                        <label>Auto-fill dari Data Peserta</label>
                                        <select class="${getInputClass(autofillSourceName, 'form-control field-autofill-source-select')}"
                                            name="${autofillSourceName}"
                                            ${supportsParticipantAutofill(fieldType) ? '' : 'disabled'}>
                                            ${buildParticipantAutofillOptions(resolvedAutofillSource)}
                                        </select>
                                        ${buildInvalidFeedback(autofillSourceName)}
                                        <small class="form-text text-muted participant-autofill-hint">
                                            ${buildParticipantAutofillHint(fieldType, resolvedAutofillSource)}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Deskripsi Pertanyaan</label>
                                        <textarea class="${getInputClass(deskripsiName)} field-description-input"
                                            name="${deskripsiName}"
                                            rows="3"
                                            placeholder="Tambahkan deskripsi pertanyaan bila diperlukan">${escapeHtml(fieldData.deskripsi)}</textarea>
                                        ${buildInvalidFeedback(deskripsiName)}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Placeholder</label>
                                        <input type="text" class="${getInputClass(placeholderName)}"
                                            name="${placeholderName}"
                                            value="${escapeHtml(fieldData.placeholder)}"
                                            placeholder="Placeholder field">
                                        ${buildInvalidFeedback(placeholderName)}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="${fileOptionWrapperClass}">
                                        <label>Mode Input Bukti</label>
                                        <select class="${getInputClass(fileInputModeName, 'form-control field-file-input-mode-select')}"
                                            name="${fileInputModeName}">
                                            ${buildFileInputModeOptions(resolvedFileInputMode)}
                                        </select>
                                        ${buildInvalidFeedback(fileInputModeName)}
                                        <small class="form-text text-muted file-option-hint">
                                            ${buildFileConfigHint(resolvedFileInputMode)}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="${standardOptionWrapperClass}">
                                <div class="${lookupSourceWrapperClass}">
                                    <label>Lookup Opsi dari Database</label>
                                    <select class="${getInputClass(lookupSourceName, 'form-control field-lookup-source-select')}"
                                        name="${lookupSourceName}"
                                        data-suggested-default="${fieldData.lookup_source ? '0' : '1'}"
                                        ${supportsFieldLookup(fieldType) ? '' : 'disabled'}>
                                        ${buildFieldLookupSourceOptions(resolvedLookupSource)}
                                    </select>
                                    ${buildInvalidFeedback(lookupSourceName)}
                                    <small class="form-text text-muted field-lookup-source-hint">
                                        ${buildFieldLookupHint(fieldType, resolvedLookupSource)}
                                    </small>
                                    <div class="field-lookup-source-preview">
                                        ${buildFieldLookupPreview(fieldType, resolvedLookupSource)}
                                    </div>
                                </div>
                                <div class="${allowOtherInputWrapperClass}">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input field-allow-other-input-checkbox"
                                            id="field-allow-other-input-${formIndex}-${fieldIndex}"
                                            name="${allowOtherInputName}"
                                            value="1" ${resolvedAllowOtherInput ? 'checked' : ''}>
                                        <label class="custom-control-label"
                                            for="field-allow-other-input-${formIndex}-${fieldIndex}">
                                            Tambahkan opsi ${escapeHtml(selectOtherOptionLabel)}
                                        </label>
                                    </div>
                                    ${buildInvalidFeedback(allowOtherInputName)}
                                    <small class="text-muted d-block mt-2">
                                        Jika aktif, peserta bisa memilih ${escapeHtml(selectOtherOptionLabel)} lalu menulis jawaban sendiri.
                                    </small>
                                </div>
                                <div class="manual-choice-options-wrapper">
                                    <label>${buildRequiredLabel('Opsi Field (Daftar Pilihan / Kotak Centang)')}</label>
                                    <textarea class="${getInputClass(opsiFieldTextName)} field-manual-options-input"
                                        name="${opsiFieldTextName}"
                                        rows="2"
                                        placeholder="Contoh: Ya, Tidak, Mungkin">${escapeHtml(fieldData.opsi_field_text)}</textarea>
                                    ${buildInvalidFeedback(opsiFieldTextName)}
                                    <small class="text-muted d-block mt-2 manual-choice-options-hint">
                                        Isi opsi manual per baris atau dipisah koma.
                                    </small>
                                </div>
                                <div class="option-score-wrapper">
                                    <small class="text-muted d-block mt-2">
                                        Isi skor opsi per baris dengan format <code>Label = Skor</code>, misalnya
                                        <code>Ya = 5</code>.
                                    </small>
                                    <label class="mt-3">Skor Opsi (Opsional)</label>
                                    <textarea class="${getInputClass(opsiScoreTextName)}"
                                        name="${opsiScoreTextName}"
                                        rows="3"
                                        placeholder="Ya = 5&#10;Tidak = 1">${escapeHtml(fieldData.opsi_score_text)}</textarea>
                                    ${buildInvalidFeedback(opsiScoreTextName)}
                                    <small class="text-muted d-block mt-2 option-score-hint">
                                        Jika memakai lookup database, gunakan label opsi sesuai master data pada preview di atas.
                                    </small>
                                </div>
                            </div>

                            <div class="${multipleChoiceWrapperClass}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="mb-0">${buildRequiredLabel('Opsi Pilihan Ganda')}</label>
                                    <button type="button" class="btn btn-light btn-sm btn-add-radio-option">
                                        <i class="fas fa-plus"></i> Tambah Opsi
                                    </button>
                                </div>
                                <div class="radio-option-list">
                                    ${radioOptions.map((option, optionIndex) => buildRadioOptionRow(formIndex, fieldIndex, optionIndex, option)).join('')}
                                </div>
                                ${buildInvalidFeedback(radioOptionsName, 'mt-2')}
                                <small class="text-muted d-block mt-2">
                                    Kode jawaban akan menjadi nilai yang disimpan saat peserta memilih opsi ini, isi jawaban akan ditampilkan ke peserta, dan level kompetensi ikut tersimpan untuk kebutuhan pemetaan. Jika kolom skor dikosongkan, sistem akan memakai level kompetensi sebagai skor default.
                                </small>

                            </div>

                            <div class="${repeaterWrapperClass}">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <label class="mb-1">${buildRequiredLabel('Konfigurasi Tabel Berulang')}</label>
                                        <small class="text-muted d-block">
                                            Susun jumlah baris dan kolom tabel tanpa menulis JSON manual.
                                        </small>
                                    </div>
                                    <button type="button" class="btn btn-light btn-sm btn-add-repeater-column">
                                        <i class="fas fa-plus"></i> Tambah Kolom
                                    </button>
                                </div>
                                <input type="hidden" class="repeater-config-json-input"
                                    name="${repeaterConfigName}"
                                    value="${escapeHtml(buildRepeaterConfigJson(repeaterConfig))}">
                                ${buildInvalidFeedback(repeaterConfigName)}
                                <div class="${repeaterConfigShellClass}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Minimal Baris Terisi</label>
                                                <input type="number" min="0" class="form-control repeater-min-rows-input"
                                                    value="${escapeHtml(repeaterConfig.min_rows)}"
                                                    placeholder="1">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Maksimal Baris Terisi</label>
                                                <input type="number" min="0" class="form-control repeater-max-rows-input"
                                                    value="${escapeHtml(repeaterConfig.max_rows)}"
                                                    placeholder="10">
                                                <small class="form-text text-muted">Isi <code>0</code> jika tanpa batas.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="repeater-column-list">
                                        ${repeaterConfig.columns.map((column, columnIndex) => buildRepeaterColumnRow(columnIndex, column)).join('')}
                                    </div>

                                    <small class="text-muted d-block mt-2">
                                        Setiap kolom minimal membutuhkan label, nama field, dan tipe input.
                                    </small>
                                </div>
                            </div>

                            <div class="card border mt-4 scoring-config-card">
                                <div class="card-header bg-light py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 ">Pengaturan Skor Otomatis</h6>
                                        <div class="custom-control custom-switch mx-3">
                                            <input type="checkbox" class="custom-control-input field-scoring-enabled"
                                                id="field-scoring-enabled-${formIndex}-${fieldIndex}"
                                                name="${scoringEnabledName}"
                                                value="1" ${scoringData.enabled ? 'checked' : ''}>
                                            <label class="custom-control-label"
                                                for="field-scoring-enabled-${formIndex}-${fieldIndex}">Aktif</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body scoring-config-body">
                                    <div class="scoring-main-note mb-3 scoring-main-guidance">
                                        Isi bagian utama terlebih dahulu. Jika belum ada kebutuhan khusus, sistem akan memakai aturan default yang aman.
                                    </div>

                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="form-group">
                                                <label>Cara Sistem Menilai</label>
                                                <select class="${getInputClass(scoringMethodName)} form-control field-scoring-method"
                                                    name="${scoringMethodName}">
                                                    ${buildFieldScoringMethodOptions(fieldType, scoringData.method)}
                                                </select>
                                                ${buildInvalidFeedback(scoringMethodName)}
                                                <small class="text-muted d-block mt-2">
                                                    Biasanya sudah sesuai otomatis dengan tipe pertanyaan. Ubah hanya jika diperlukan.
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Bobot Nilai Pertanyaan <span class="text-muted">(opsional)</span></label>
                                                <input type="number" min="0" step="0.01" class="${getInputClass(scoringWeightName)}"
                                                    name="${scoringWeightName}"
                                                    value="${escapeHtml(scoringData.weight)}"
                                                    placeholder="Kosongkan jika bobot sama dengan pertanyaan lain">
                                                ${buildInvalidFeedback(scoringWeightName)}
                                            </div>
                                        </div>
                                    </div>



                                    <div class="scoring-choice-wrapper d-none mb-3">
                                        <div class="alert alert-light border mb-0">
                                            Nilai utama untuk pertanyaan pilihan diambil dari skor pada masing-masing opsi jawaban di atas.
                                        </div>
                                    </div>

                                    <div class="scoring-presence-wrapper d-none">
                                        <div class="form-group">
                                            <label>Nilai Saat Jawaban Ada</label>
                                            <input type="number" min="0" max="5" step="0.01"
                                                class="${getInputClass(scoringPresenceName)}"
                                                name="${scoringPresenceName}"
                                                value="${escapeHtml(scoringData.score_if_answered)}"
                                                placeholder="Contoh: 3">
                                            ${buildInvalidFeedback(scoringPresenceName)}
                                            <small class="text-muted d-block mt-2">
                                                Gunakan untuk field yang cukup dicek keberadaan jawabannya, misalnya unggah file atau tanggal.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="row scoring-numeric-wrapper d-none">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Arah Penilaian</label>
                                                <select class="${getInputClass(scoringNumericDirectionName)} form-control"
                                                    name="${scoringNumericDirectionName}">
                                                    <option value="greater_is_better" ${scoringData.numeric_direction === 'greater_is_better' ? 'selected' : ''}>Semakin besar semakin baik</option>
                                                    <option value="lower_is_better" ${scoringData.numeric_direction === 'lower_is_better' ? 'selected' : ''}>Semakin kecil semakin baik</option>
                                                    <option value="range" ${scoringData.numeric_direction === 'range' ? 'selected' : ''}>Ada rentang nilai ideal</option>
                                                </select>
                                                ${buildInvalidFeedback(scoringNumericDirectionName)}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Batas Minimum</label>
                                                <input type="number" step="0.01" class="${getInputClass(scoringMinThresholdName)}"
                                                    name="${scoringMinThresholdName}"
                                                    value="${escapeHtml(scoringData.min_threshold)}"
                                                    placeholder="Contoh: 1">
                                                ${buildInvalidFeedback(scoringMinThresholdName)}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Target / Nilai Ideal</label>
                                                <input type="number" step="0.01" class="${getInputClass(scoringTargetThresholdName)}"
                                                    name="${scoringTargetThresholdName}"
                                                    value="${escapeHtml(scoringData.target_threshold)}"
                                                    placeholder="Contoh: 10">
                                                ${buildInvalidFeedback(scoringTargetThresholdName)}
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-0">
                                                <label>Batas Maksimum</label>
                                                <input type="number" step="0.01" class="${getInputClass(scoringMaxThresholdName)}"
                                                    name="${scoringMaxThresholdName}"
                                                    value="${escapeHtml(scoringData.max_threshold)}"
                                                    placeholder="Contoh: 20">
                                                ${buildInvalidFeedback(scoringMaxThresholdName)}
                                                <small class="text-muted d-block mt-2">
                                                    Jika kosong, sistem akan memakai nilai target sebagai batas atas.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="scoring-text-wrapper d-none">
                                        <div class="form-group">
                                            <div class="scoring-assist-actions mb-2">
                                                <label class="mb-0">Pedoman Penilaian / Ciri Jawaban yang Diharapkan</label>
                                                <div class="d-flex flex-wrap" style="gap:0.5rem;">
                                                    <button type="button" class="btn btn-light btn-sm btn-copy-description-to-scoring">
                                                        Ambil dari deskripsi pertanyaan
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary btn-sm btn-generate-scoring-helper">
                                                        Isi bantuan otomatis
                                                    </button>
                                                </div>
                                            </div>
                                            <textarea class="${getInputClass(scoringReferenceAnswerName)}"
                                                name="${scoringReferenceAnswerName}"
                                                rows="4"
                                                placeholder="Tuliskan dengan bahasa sederhana: jawaban seperti apa yang dianggap baik, bukti apa yang diharapkan, atau isi tabel apa yang perlu muncul">${escapeHtml(scoringData.reference_answer)}</textarea>
                                            ${buildInvalidFeedback(scoringReferenceAnswerName)}
                                            <small class="text-muted d-block mt-2">
                                                Satu deskripsi ini bisa membantu sistem menyusun kata kunci, padanan kata, dan saran panjang jawaban.
                                            </small>
                                            <small class="scoring-helper-status mt-2"></small>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Kata Kunci Penting</label>
                                            <textarea class="${getInputClass(scoringKeywordGroupsName)}"
                                                name="${scoringKeywordGroupsName}"
                                                rows="4"
                                                placeholder="Pisahkan dengan koma, misalnya: sertifikat, program studi, lembaga, bukti dukung">${escapeHtml(scoringData.keyword_groups_text)}</textarea>
                                            ${buildInvalidFeedback(scoringKeywordGroupsName)}
                                            <small class="text-muted d-block mt-2">
                                                Pisahkan kata kunci hanya dengan koma. Jangan gunakan Enter, tanda <code>|</code>, atau titik koma. Jika perlu padanan kata, isi di pengaturan lanjutan.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="scoring-advanced-panel d-none">
                                        <div class="font-weight-bold mb-3">Pengaturan Lanjutan</div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Konteks Penilaian</label>
                                                    <select class="${getInputClass(scoringProfileName)} form-control field-scoring-profile"
                                                        name="${scoringProfileName}">
                                                        ${buildFormScoringProfileOptions(scoringData.profile)}
                                                    </select>
                                                    ${buildInvalidFeedback(scoringProfileName)}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Kode Rubrik / Indikator <span class="text-muted">(opsional)</span></label>
                                                    <input type="text" class="${getInputClass(scoringRubricCodeName)}"
                                                        name="${scoringRubricCodeName}"
                                                        value="${escapeHtml(scoringData.rubric_code)}"
                                                        placeholder="Contoh: P2 / K1">
                                                    ${buildInvalidFeedback(scoringRubricCodeName)}
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Skala Minimum</label>
                                                    <input type="number" min="0" max="5" step="0.01"
                                                        class="${getInputClass(scoringScaleMinName)}"
                                                        name="${scoringScaleMinName}"
                                                        value="${escapeHtml(scoringData.scale_min)}"
                                                        placeholder="1">
                                                    ${buildInvalidFeedback(scoringScaleMinName)}
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Skala Maksimum</label>
                                                    <input type="number" min="0" max="5" step="0.01"
                                                        class="${getInputClass(scoringScaleMaxName)}"
                                                        name="${scoringScaleMaxName}"
                                                        value="${escapeHtml(scoringData.scale_max)}"
                                                        placeholder="5">
                                                    ${buildInvalidFeedback(scoringScaleMaxName)}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row scoring-synonym-wrapper d-none">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Padanan Kata <span class="text-muted">(opsional)</span></label>
                                                    <textarea class="${getInputClass(scoringSynonymMapName)}"
                                                        name="${scoringSynonymMapName}"
                                                        rows="4"
                                                        placeholder="asesmen: penilaian, evaluasi&#10;pelatihan: diklat, bimtek">${escapeHtml(scoringData.synonym_map_text)}</textarea>
                                                    ${buildInvalidFeedback(scoringSynonymMapName)}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row scoring-confidence-wrapper d-none">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Saran Panjang Jawaban Minimum</label>
                                                    <input type="number" min="0" class="${getInputClass(scoringMinWordsName)}"
                                                        name="${scoringMinWordsName}"
                                                        value="${escapeHtml(scoringData.min_words)}"
                                                        placeholder="Contoh: 40">
                                                    ${buildInvalidFeedback(scoringMinWordsName)}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Ambang Keyakinan Sistem</label>
                                                    <input type="number" min="0" max="1" step="0.01"
                                                        class="${getInputClass(scoringConfidenceThresholdName)}"
                                                        name="${scoringConfidenceThresholdName}"
                                                        value="${escapeHtml(scoringData.confidence_threshold)}"
                                                        placeholder="0.55">
                                                    ${buildInvalidFeedback(scoringConfidenceThresholdName)}
                                                </div>
                                            </div>
                                        </div>
                                        <textarea class="d-none"
                                            name="${scoringAdvancedRulesName}"
                                            rows="4">${escapeHtml(scoringData.advanced_rules_text)}</textarea>
                                        <input type="checkbox" class="d-none"
                                            name="${scoringManualReviewName}"
                                            value="1" ${scoringData.manual_review_below_confidence ? 'checked' : ''}>

                                        <div class="row scoring-numeric-score-wrapper d-none">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Nilai Terendah</label>
                                                    <input type="number" min="0" max="5" step="0.01" class="${getInputClass(scoringMinScoreName)}"
                                                        name="${scoringMinScoreName}"
                                                        value="${escapeHtml(scoringData.min_score)}"
                                                        placeholder="1">
                                                    ${buildInvalidFeedback(scoringMinScoreName)}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Nilai Target</label>
                                                    <input type="number" min="0" max="5" step="0.01" class="${getInputClass(scoringTargetScoreName)}"
                                                        name="${scoringTargetScoreName}"
                                                        value="${escapeHtml(scoringData.target_score)}"
                                                        placeholder="3">
                                                    ${buildInvalidFeedback(scoringTargetScoreName)}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group mb-0">
                                                    <label>Nilai Tertinggi</label>
                                                    <input type="number" min="0" max="5" step="0.01" class="${getInputClass(scoringMaxScoreName)}"
                                                        name="${scoringMaxScoreName}"
                                                        value="${escapeHtml(scoringData.max_score)}"
                                                        placeholder="5">
                                                    ${buildInvalidFeedback(scoringMaxScoreName)}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-right mt-3">
                                        <button type="button" class="btn btn-light btn-sm btn-toggle-scoring-advanced">
                                            Tampilkan pengaturan lanjutan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Bantuan / Petunjuk Tambahan</label>
                                        <textarea class="${getInputClass(bantuanName)}"
                                            name="${bantuanName}"
                                            rows="2"
                                            placeholder="Tambahkan bantuan singkat untuk peserta">${escapeHtml(fieldData.bantuan)}</textarea>
                                        ${buildInvalidFeedback(bantuanName)}
                                    </div>
                                </div>

                            </div>


                        </div>
                    </div>
                `;
            };

            const buildFormCard = (formIndex, formData = {}) => {
                const formScoring = normalizeFormScoringConfig(formData.scoring || {});
                const formPrefix = `forms[${formIndex}]`;
                const formIdName = `${formPrefix}[id]`;
                const judulFormName = `${formPrefix}[judul_form]`;
                const kodeFormName = `${formPrefix}[kode_form]`;
                const urutanName = `${formPrefix}[urutan]`;
                const deskripsiName = `${formPrefix}[deskripsi]`;
                const kompetensiName = `${formPrefix}[kompetensi]`;
                const indikatorKodeName = `${formPrefix}[indikator_kode]`;
                const indikatorLabelName = `${formPrefix}[indikator_label]`;
                const formScoringPrefix = `${formPrefix}[scoring]`;
                const formScoringProfileName = `${formScoringPrefix}[profile]`;
                const formScoringWeightName = `${formScoringPrefix}[weight]`;
                const formScoringAdvancedRulesName = `${formScoringPrefix}[advanced_rules_text]`;
                const fieldsName = `${formPrefix}[fields]`;
                const formCardClass = joinClasses(
                    'card',
                    'border',
                    'assessment-form-card',
                    'mb-4',
                    hasNestedErrors(formPrefix) ? 'border-danger' : ''
                );

                return `
                    <div class="${formCardClass}" data-form-index="${formIndex}" data-field-counter="0">
                        <input type="hidden" class="assessment-form-id-input"
                            name="${formIdName}"
                            value="${escapeHtml(formData.id || '')}">
                        <div class="card-header">
                            <h4>Form ${formIndex + 1}</h4>
                            <div class="assessment-builder-actions">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input"
                                        id="form-active-${formIndex}"
                                        name="forms[${formIndex}][is_active]"
                                        value="1" ${formData.is_active === undefined || normalizeChecked(formData.is_active) ? 'checked' : ''}>
                                    <label class="custom-control-label" for="form-active-${formIndex}">Aktif</label>
                                </div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input"
                                        id="form-scoreable-${formIndex}"
                                        name="forms[${formIndex}][is_scoreable]"
                                        value="1" ${formData.is_scoreable === undefined || normalizeChecked(formData.is_scoreable) ? 'checked' : ''}>
                                    <label class="custom-control-label" for="form-scoreable-${formIndex}">Masuk penilaian</label>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-form">
                                    <i class="fas fa-trash-alt"></i> Hapus Form
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>${buildRequiredLabel('Judul Form')}</label>
                                        <input type="text" class="${getInputClass(judulFormName)}"
                                            name="${judulFormName}"
                                            value="${escapeHtml(formData.judul_form)}"
                                            placeholder="Contoh: Profil Peserta"
                                            required>
                                        ${buildInvalidFeedback(judulFormName)}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kode Form</label>
                                        <input type="text" class="${getInputClass(kodeFormName)}"
                                            name="${kodeFormName}"
                                            value="${escapeHtml(formData.kode_form)}"
                                            placeholder="Contoh: FORM-PROFIL">
                                        ${buildInvalidFeedback(kodeFormName)}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Urutan</label>
                                        <input type="number" min="1" class="${getInputClass(urutanName)}"
                                            name="${urutanName}"
                                            value="${escapeHtml(formData.urutan || formIndex + 1)}">
                                        ${buildInvalidFeedback(urutanName)}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kompetensi</label>
                                        <select class="${getInputClass(kompetensiName)}"
                                            name="${kompetensiName}">
                                            ${buildTeacherCompetencyOptions(formData.kompetensi || '')}
                                        </select>
                                        ${buildInvalidFeedback(kompetensiName)}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Kode Indikator</label>
                                        <input type="text" class="${getInputClass(indikatorKodeName)}"
                                            name="${indikatorKodeName}"
                                            value="${escapeHtml(formData.indikator_kode)}"
                                            placeholder="Contoh: 1.1">
                                        ${buildInvalidFeedback(indikatorKodeName)}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Label Indikator</label>
                                        <input type="text" class="${getInputClass(indikatorLabelName)}"
                                            name="${indikatorLabelName}"
                                            value="${escapeHtml(formData.indikator_label)}"
                                            placeholder="Contoh: Lingkungan belajar aman dan nyaman">
                                        ${buildInvalidFeedback(indikatorLabelName)}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Deskripsi Form</label>
                                <textarea class="${getInputClass(deskripsiName)} form-description-input"
                                    name="${deskripsiName}"
                                    rows="2"
                                    placeholder="Deskripsi singkat form">${escapeHtml(formData.deskripsi)}</textarea>
                                ${buildInvalidFeedback(deskripsiName)}
                            </div>

                            <div class="card border mb-4">
                                <div class="card-header bg-light py-3">
                                    <h6 class="mb-0">Pengaturan Skor Form</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Profil Scoring</label>
                                                <select class="${getInputClass(formScoringProfileName)} form-control"
                                                    name="${formScoringProfileName}">
                                                    ${buildFormScoringProfileOptions(formScoring.profile)}
                                                </select>
                                                ${buildInvalidFeedback(formScoringProfileName)}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bobot Form</label>
                                                <input type="number" min="0" step="0.01" class="${getInputClass(formScoringWeightName)}"
                                                    name="${formScoringWeightName}"
                                                    value="${escapeHtml(formScoring.weight)}"
                                                    placeholder="Contoh: 20">
                                                ${buildInvalidFeedback(formScoringWeightName)}
                                            </div>
                                        </div>
                                    </div>
                                    <input type="checkbox" class="d-none"
                                        name="${formScoringPrefix}[exclude_from_competency]"
                                        value="1" ${formScoring.exclude_from_competency ? 'checked' : ''}>
                                    <textarea class="d-none"
                                        name="${formScoringAdvancedRulesName}"
                                        rows="3">${escapeHtml(formScoring.advanced_rules_text)}</textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Daftar Pertanyaan</h6>
                            </div>

                            <div class="${joinClasses('assessment-field-list', hasError(fieldsName) ? 'assessment-invalid-list' : '')}"></div>
                            ${buildInvalidFeedback(fieldsName, 'mt-2')}

                            <div class="text-right mt-3">
                                <button type="button" class="btn btn-primary btn-sm btn-add-field">
                                    <i class="fas fa-plus"></i> Tambah Field
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            };

            const appendField = ($formCard, fieldData = {}, options = {}) => {
                const formIndex = Number($formCard.data('form-index'));
                const fieldIndex = Number($formCard.attr('data-field-counter'));

                $formCard.find('.assessment-field-list').append(buildFieldCard(formIndex, fieldIndex, fieldData));
                $formCard.attr('data-field-counter', fieldIndex + 1);

                const $fieldCard = $formCard.find('.assessment-field-card').last();
                toggleOptionWrapper($fieldCard);
                toggleScoringWrapper($fieldCard);
                updateAutoFieldNameHint($fieldCard);
                updateParticipantAutofillState($fieldCard);
                updateFieldLookupState($fieldCard);
                updateFileInputModeState($fieldCard);
                syncRepeaterConfigState($fieldCard);

                if (!options.skipSummary) {
                    renderBuilderSummary();
                }
            };

            const appendForm = (formData = {}, options = {}) => {
                const formIndex = formIndexCounter++;

                $('#form-builder-list').append(buildFormCard(formIndex, formData));
                const $formCard = $('.assessment-form-card').last();
                const fields = Array.isArray(formData.fields) && formData.fields.length ? formData.fields : [{}];

                fields.forEach((field) => appendField($formCard, field, {
                    skipSummary: true,
                }));
                toggleEmptyState();

                if (!options.skipSummary) {
                    renderBuilderSummary();
                }
            };

            const appendRadioOption = ($fieldCard, optionData = {}) => {
                const formIndex = Number($fieldCard.closest('.assessment-form-card').data('form-index'));
                const fieldIndex = Number($fieldCard.data('field-index'));
                const optionIndex = Number($fieldCard.attr('data-radio-option-counter') || 0);
                const normalizedOption = {
                    ...normalizeRadioOptionShape(optionData),
                    level_kompetensi: normalizeCompetencyLevelValue(optionData?.level_kompetensi, optionIndex),
                };

                $fieldCard.find('.radio-option-list').append(buildRadioOptionRow(formIndex, fieldIndex, optionIndex,
                    normalizedOption));
                $fieldCard.attr('data-radio-option-counter', optionIndex + 1);
                reindexRadioOptions($fieldCard);
            };

            const updateRemoveRadioOptionState = ($fieldCard) => {
                const shouldDisable = $fieldCard.find('.multiple-choice-option-row').length <= 2;

                $fieldCard.find('.btn-remove-radio-option')
                    .prop('disabled', shouldDisable)
                    .toggleClass('disabled', shouldDisable);
            };

            const reindexRadioOptions = ($fieldCard) => {
                const formIndex = Number($fieldCard.closest('.assessment-form-card').data('form-index'));
                const fieldIndex = Number($fieldCard.data('field-index'));

                $fieldCard.find('.multiple-choice-option-row').each(function(optionIndex) {
                    const $optionRow = $(this);
                    const generatedLabel = generateChoiceLabel(optionIndex);
                    const $textInput = $optionRow.find('.radio-option-text');
                    const $codeInput = $optionRow.find('.radio-option-code');
                    const $scoreInput = $optionRow.find('.radio-option-score');
                    const $levelSelect = $optionRow.find('.radio-option-level');

                    $optionRow.attr('data-option-index', optionIndex);
                    $textInput
                        .attr('name', `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][label]`)
                        .attr('placeholder', 'Contoh: Mengenali faktor yang memengaruhi perilaku peserta didik');

                    $codeInput.attr(
                        'name',
                        `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][value]`
                    ).attr('placeholder', generatedLabel);

                    $scoreInput.attr(
                        'name',
                        `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][score]`
                    ).attr('placeholder', $levelSelect.val() || '');

                    $levelSelect
                        .attr(
                            'name',
                            `forms[${formIndex}][fields][${fieldIndex}][radio_options][${optionIndex}][level_kompetensi]`
                        )
                        .html(buildCompetencyLevelOptions($levelSelect.val(), optionIndex));
                });

                $fieldCard.attr('data-radio-option-counter', $fieldCard.find('.multiple-choice-option-row').length);
                updateRemoveRadioOptionState($fieldCard);
            };

            const ensureMultipleChoiceOptions = ($fieldCard, minimum = 2) => {
                const optionCount = $fieldCard.find('.multiple-choice-option-row').length;

                if (optionCount >= minimum) {
                    return;
                }

                for (let index = optionCount; index < minimum; index += 1) {
                    appendRadioOption($fieldCard, {
                        label: '',
                        value: '',
                        level_kompetensi: getDefaultCompetencyLevel(index),
                    });
                }

                reindexRadioOptions($fieldCard);
            };

            const toggleOptionWrapper = ($fieldCard) => {
                const selectedType = $fieldCard.find('.field-type-select').val();
                const showTextOptions = textOptionFieldTypes.includes(selectedType);
                const showMultipleChoiceOptions = selectedType === multipleChoiceFieldType;
                const showRepeaterOptions = selectedType === repeaterFieldType;
                const showFileOptions = selectedType === fileFieldType;
                const showAllowOtherInput = supportsSelectOtherInput(selectedType);
                const selectedLookupSource = $fieldCard.find('.field-lookup-source-select').val()?.trim() || '';
                const showLookupSource = selectedType === 'select';
                const showManualChoiceOptions = showTextOptions && (!showLookupSource || !selectedLookupSource);

                $fieldCard.find('.standard-option-wrapper')
                    .toggleClass('d-none', !showTextOptions)
                    .find('.field-lookup-source-select, textarea')
                    .prop('disabled', !showTextOptions);
                $fieldCard.find('.field-lookup-source-wrapper')
                    .toggleClass('d-none', !showLookupSource);
                $fieldCard.find('.field-allow-other-input-wrapper')
                    .toggleClass('d-none', !showAllowOtherInput);
                $fieldCard.find('.field-allow-other-input-checkbox')
                    .prop('disabled', !showAllowOtherInput);
                $fieldCard.find('.manual-choice-options-wrapper')
                    .toggleClass('d-none', !showManualChoiceOptions);
                $fieldCard.find('.field-manual-options-input')
                    .prop('disabled', !showManualChoiceOptions);
                $fieldCard.find('.option-score-wrapper')
                    .toggleClass('d-none', !showTextOptions);
                $fieldCard.find('.field-lookup-source-select')
                    .prop('disabled', !showLookupSource);

                $fieldCard.find('.multiple-choice-wrapper')
                    .toggleClass('d-none', !showMultipleChoiceOptions)
                    .find('input, select')
                    .prop('disabled', !showMultipleChoiceOptions);

                $fieldCard.find('.repeater-option-wrapper')
                    .toggleClass('d-none', !showRepeaterOptions)
                    .find('input, select, textarea')
                    .prop('disabled', !showRepeaterOptions);
                $fieldCard.find('.file-option-wrapper')
                    .toggleClass('d-none', !showFileOptions);
                $fieldCard.find('.field-file-input-mode-select')
                    .prop('disabled', !showFileOptions);

                if (showMultipleChoiceOptions) {
                    ensureMultipleChoiceOptions($fieldCard);
                } else {
                    updateRemoveRadioOptionState($fieldCard);
                }
            };

            const updateFileInputModeState = ($fieldCard) => {
                const selectedType = $fieldCard.find('.field-type-select').val() || 'text';
                const $select = $fieldCard.find('.field-file-input-mode-select');
                const $hint = $fieldCard.find('.file-option-hint');
                const isSupported = selectedType === fileFieldType;
                const selectedMode = normalizeFileInputMode($select.val() || 'file');

                if (!$select.length) {
                    return;
                }

                if (isSupported && !$select.val()) {
                    $select.val('file');
                }

                $select.prop('disabled', !isSupported);
                $hint.html(buildFileConfigHint(selectedMode));
            };

            const updateParticipantAutofillState = ($fieldCard) => {
                const selectedType = $fieldCard.find('.field-type-select').val() || 'text';
                const $select = $fieldCard.find('.field-autofill-source-select');
                const $hint = $fieldCard.find('.participant-autofill-hint');
                const isSupported = supportsParticipantAutofill(selectedType);
                const labelValue = $fieldCard.find('.field-label-input').val()?.trim() || '';
                const fieldNameValue = slugifyFieldName(labelValue);
                const currentValue = $select.val()?.trim() || '';
                const userTouched = $select.data('userTouched') === true;

                if (isSupported && !currentValue && !userTouched) {
                    const suggestedSource = resolveSuggestedParticipantAutofillSource(labelValue, fieldNameValue);

                    if (suggestedSource) {
                        $select.val(suggestedSource);
                    }
                }

                const selectedSource = isSupported ? ($select.val()?.trim() || '') : '';

                $select.prop('disabled', !isSupported);
                $hint.html(buildParticipantAutofillHint(selectedType, selectedSource));
            };

            const updateFieldLookupState = ($fieldCard) => {
                const selectedType = $fieldCard.find('.field-type-select').val() || 'text';
                const $select = $fieldCard.find('.field-lookup-source-select');
                const $hint = $fieldCard.find('.field-lookup-source-hint');
                const $preview = $fieldCard.find('.field-lookup-source-preview');
                const isSupported = supportsFieldLookup(selectedType);
                const labelValue = $fieldCard.find('.field-label-input').val()?.trim() || '';
                const fieldNameValue = slugifyFieldName(labelValue);
                const targetKetenagaanValue = resolveSelectedTargetKetenagaanValue();
                const currentValue = $select.val()?.trim() || '';
                const userTouched = $select.data('userTouched') === true;
                const usesSuggestedDefault = String($select.data('suggestedDefault') || '') === '1' || $select.data(
                    'suggestedDefault'
                ) === true;
                const suggestedSource = resolveSuggestedFieldLookupSource(
                    labelValue,
                    fieldNameValue,
                    targetKetenagaanValue
                );

                if (isSupported && !userTouched && suggestedSource && (!currentValue || usesSuggestedDefault)) {
                    $select.val(suggestedSource);
                    $select.data('suggestedDefault', true);
                } else if (isSupported && !userTouched && usesSuggestedDefault && !suggestedSource) {
                    $select.val('');
                }

                const selectedSource = isSupported ? ($select.val()?.trim() || '') : '';

                $select.prop('disabled', !isSupported);
                $hint.html(buildFieldLookupHint(selectedType, selectedSource));
                $preview.html(buildFieldLookupPreview(selectedType, selectedSource));
                toggleOptionWrapper($fieldCard);
            };

            const toggleScoringWrapper = ($fieldCard) => {
                const selectedType = $fieldCard.find('.field-type-select').val() || 'text';
                const $methodSelect = $fieldCard.find('.field-scoring-method');
                const currentMethod = $methodSelect.val();
                const normalizedMethod = resolveAllowedScoringMethods(selectedType).includes(currentMethod)
                    ? currentMethod
                    : resolveDefaultScoringMethod(selectedType);
                const scoringEnabled = $fieldCard.find('.field-scoring-enabled').is(':checked');
                const showNumericConfig = selectedType === 'number' && ['numeric_threshold', 'numeric_range'].includes(normalizedMethod);
                const showTextConfig = supportsScoringAssistant(selectedType, normalizedMethod);
                const showConfidenceConfig = showTextConfig;
                const showPresenceConfig = normalizedMethod === 'presence';
                const showChoiceConfig = ['radio', 'select', 'checkbox'].includes(selectedType) && !showPresenceConfig;
                const advancedPanelVisible = !$fieldCard.find('.scoring-advanced-panel').hasClass('d-none');

                $methodSelect.html(buildFieldScoringMethodOptions(selectedType, normalizedMethod)).val(normalizedMethod);
                $fieldCard.find('.scoring-config-body').toggleClass('d-none', !scoringEnabled);
                $fieldCard.find('.scoring-main-guidance').text(resolveScoringSummaryMessage(selectedType, normalizedMethod));
                $fieldCard.find('.scoring-default-summary-content').html(buildScoringSummaryHtml($fieldCard, selectedType, normalizedMethod));
                $fieldCard.find('.scoring-numeric-wrapper').toggleClass('d-none', !showNumericConfig);
                $fieldCard.find('.scoring-text-wrapper').toggleClass('d-none', !showTextConfig);
                $fieldCard.find('.scoring-presence-wrapper').toggleClass('d-none', !showPresenceConfig);
                $fieldCard.find('.scoring-choice-wrapper').toggleClass('d-none', !showChoiceConfig);
                $fieldCard.find('.scoring-confidence-wrapper').toggleClass('d-none', !showConfidenceConfig);
                $fieldCard.find('.scoring-synonym-wrapper').toggleClass('d-none', !showTextConfig);
                $fieldCard.find('.scoring-numeric-score-wrapper').toggleClass('d-none', !showNumericConfig);
                $fieldCard.find('.btn-toggle-scoring-advanced').text(
                    advancedPanelVisible ? 'Sembunyikan pengaturan lanjutan' : 'Tampilkan pengaturan lanjutan'
                );

                if (showTextConfig) {
                    applyScoringGuidanceSuggestion($fieldCard);
                } else {
                    updateScoringAssistantStatus($fieldCard, '');
                }
            };

            const updateAutoFieldNameHint = ($fieldCard) => {
                const labelValue = $fieldCard.find('.field-label-input').val()?.trim() || '';
                $fieldCard.find('.auto-field-name-hint').html(buildAutoFieldNameHint(labelValue));
            };

            const toggleEmptyState = () => {
                const hasForms = $('.assessment-form-card').length > 0;
                $('#form-builder-empty').toggleClass('d-none', hasForms);
            };

            const parseOptionText = (value) => {
                if (!value) {
                    return [];
                }

                return value
                    .split(/[\r\n,]+/)
                    .map((item) => item.trim())
                    .filter(Boolean);
            };

            const parseRepeaterConfig = (value) => {
                if (!value) {
                    return null;
                }

                const normalizedConfig = normalizeRepeaterConfigData(value);
                return normalizedConfig && typeof normalizedConfig === 'object' ? normalizedConfig : null;
            };

            const getRepeaterColumnDefaultName = ($columnRow) => {
                const rowIndex = Number($columnRow.attr('data-column-index') || 0);
                const labelValue = $columnRow.find('.repeater-column-label-input').val()?.trim() || '';
                return slugifyFieldName(labelValue || `kolom_${rowIndex + 1}`) || `kolom_${rowIndex + 1}`;
            };

            const isRepeaterColumnNameAutoGenerated = ($nameInput) => {
                const autoGeneratedValue = $nameInput.data('autoGenerated');
                return autoGeneratedValue === true
                    || autoGeneratedValue === 1
                    || autoGeneratedValue === '1'
                    || autoGeneratedValue === 'true';
            };

            const updateRepeaterColumnRowState = ($columnRow) => {
                const columnIndex = Number($columnRow.attr('data-column-index') || 0);
                const labelValue = $columnRow.find('.repeater-column-label-input').val()?.trim() || '';
                const $nameInput = $columnRow.find('.repeater-column-name-input');
                const currentName = $nameInput.val()?.trim() || '';
                const defaultName = getRepeaterColumnDefaultName($columnRow);
                const autoGenerated = isRepeaterColumnNameAutoGenerated($nameInput);
                const columnType = normalizeRepeaterColumnType($columnRow.find('.repeater-column-type-select').val() || 'text');

                $columnRow.find('.repeater-column-row__title').text(`Kolom ${columnIndex + 1}`);
                $columnRow.find('.repeater-column-row__meta').text(labelValue || 'Label kolom belum diisi');

                if (!currentName || autoGenerated) {
                    $nameInput.val(defaultName);
                    $nameInput.data('autoGenerated', true);
                }

                $columnRow.find('.repeater-column-name-hint').html(
                    $nameInput.val()?.trim()
                        ? `Key penyimpanan: <code>${escapeHtml($nameInput.val()?.trim() || '')}</code>`
                        : 'Nama field otomatis dibuat dari label kolom.'
                );

                $columnRow.find('.repeater-column-type-select').val(columnType);
                $columnRow.find('.repeater-column-options-wrapper')
                    .toggleClass('d-none', columnType !== 'select');
                $columnRow.find('.repeater-column-options-input')
                    .prop('disabled', columnType !== 'select');
            };

            const collectRepeaterConfigData = ($fieldCard) => {
                const minRows = normalizeNonNegativeInteger($fieldCard.find('.repeater-min-rows-input').val(), 0);
                const maxRows = normalizeNonNegativeInteger($fieldCard.find('.repeater-max-rows-input').val(), 0);
                const columns = $fieldCard.find('.repeater-column-row').map(function(index) {
                    const $columnRow = $(this);
                    const label = $columnRow.find('.repeater-column-label-input').val()?.trim() || '';
                    const rawFieldName = $columnRow.find('.repeater-column-name-input').val()?.trim() || '';
                    const fieldName = slugifyFieldName(rawFieldName || label || `kolom_${index + 1}`) || `kolom_${index + 1}`;
                    const columnType = normalizeRepeaterColumnType($columnRow.find('.repeater-column-type-select').val() || 'text');
                    const placeholder = $columnRow.find('.repeater-column-placeholder-input').val()?.trim() || '';
                    const optionsText = $columnRow.find('.repeater-column-options-input').val()?.trim() || '';
                    const options = columnType === 'select' ? parseOptionText(optionsText) : [];
                    const isRequired = $columnRow.find('.repeater-column-required-input').is(':checked');
                    const hasMeaningfulValue = Boolean(label || rawFieldName || placeholder || optionsText || isRequired || columnType !== 'text');

                    if (!hasMeaningfulValue) {
                        return null;
                    }

                    return {
                        label: label,
                        nama_field: fieldName,
                        tipe_field: columnType,
                        placeholder: placeholder,
                        opsi_field: options,
                        is_required: isRequired,
                    };
                }).get().filter(Boolean);

                return {
                    min_rows: minRows,
                    max_rows: maxRows,
                    columns: columns,
                };
            };

            const syncRepeaterConfigState = ($fieldCard) => {
                $fieldCard.find('.repeater-column-row').each(function(index) {
                    $(this).attr('data-column-index', index);
                    updateRepeaterColumnRowState($(this));
                });

                const shouldDisableRemove = $fieldCard.find('.repeater-column-row').length <= 1;
                $fieldCard.find('.btn-remove-repeater-column')
                    .prop('disabled', shouldDisableRemove)
                    .toggleClass('disabled', shouldDisableRemove);

                const configJson = buildRepeaterConfigJson(collectRepeaterConfigData($fieldCard));
                $fieldCard.find('.repeater-config-json-input').val(configJson);

                if (($fieldCard.find('.field-type-select').val() || '') !== repeaterFieldType) {
                    $fieldCard.find('.repeater-option-wrapper')
                        .find('input, select, textarea')
                        .prop('disabled', true);
                }
            };

            const appendRepeaterColumn = ($fieldCard, columnData = {}) => {
                const columnIndex = $fieldCard.find('.repeater-column-row').length;
                $fieldCard.find('.repeater-column-list').append(buildRepeaterColumnRow(columnIndex, columnData));
                syncRepeaterConfigState($fieldCard);
            };

            const collectFieldPayload = ($fieldCard, fieldIndex) => {
                const fieldType = $fieldCard.find('select[name$="[tipe_field]"]').val() || 'text';
                const scoringMethod = $fieldCard.find('select[name$="[scoring][method]"]').val() || resolveDefaultScoringMethod(fieldType);
                const rawFieldId = $fieldCard.find('.assessment-field-id-input').val();
                const fieldId = Number(rawFieldId || 0);
                const rawFileOptionConfig = $fieldCard.find('input[name$="[raw_opsi_field_json]"]').val()?.trim() || '';
                const fileInputMode = fieldType === fileFieldType
                    ? normalizeFileInputMode($fieldCard.find('select[name$="[file_input_mode]"]').val() || 'file')
                    : '';

                return {
                    id: fieldId > 0 ? fieldId : null,
                    label: $fieldCard.find('input[name$="[label]"]').val()?.trim() || '',
                    nama_field: slugifyFieldName($fieldCard.find('input[name$="[label]"]').val()?.trim() || ''),
                    deskripsi: $fieldCard.find('textarea[name$="[deskripsi]"]').val()?.trim() || '',
                    tipe_field: fieldType,
                    placeholder: $fieldCard.find('input[name$="[placeholder]"]').val()?.trim() || '',
                    bantuan: $fieldCard.find('textarea[name$="[bantuan]"]').val()?.trim() || '',
                    autofill_source: supportsParticipantAutofill(fieldType)
                        ? ($fieldCard.find('select[name$="[autofill_source]"]').val()?.trim() || '')
                        : '',
                    lookup_source: supportsFieldLookup(fieldType)
                        ? ($fieldCard.find('select[name$="[lookup_source]"]').val()?.trim() || '')
                        : '',
                    allow_other_input: supportsSelectOtherInput(fieldType)
                        ? $fieldCard.find('input[name$="[allow_other_input]"]').is(':checked')
                        : false,
                    file_input_mode: fileInputMode,
                    opsi_field_text: textOptionFieldTypes.includes(fieldType) ?
                        $fieldCard.find('textarea[name$="[opsi_field_text]"]').val()?.trim() || '' : null,
                    opsi_score_text: textOptionFieldTypes.includes(fieldType) ?
                        $fieldCard.find('textarea[name$="[opsi_score_text]"]').val()?.trim() || '' : null,
                    repeater_config_text: fieldType === repeaterFieldType
                        ? ($fieldCard.find('.repeater-config-json-input').val()?.trim() || buildRepeaterConfigJson(collectRepeaterConfigData($fieldCard)))
                        : null,
                    raw_opsi_field_json: fieldType === fileFieldType
                        ? buildFileFieldConfigJson(rawFileOptionConfig, fileInputMode)
                        : rawFileOptionConfig,
                    radio_options: fieldType === multipleChoiceFieldType ? getMultipleChoiceOptions($fieldCard) : [],
                    scoring: {
                        enabled: $fieldCard.find('input[name$="[scoring][enabled]"]').is(':checked'),
                        profile: $fieldCard.find('select[name$="[scoring][profile]"]').val()?.trim() || '',
                        method: scoringMethod,
                        rubric_code: $fieldCard.find('input[name$="[scoring][rubric_code]"]').val()?.trim() || '',
                        weight: $fieldCard.find('input[name$="[scoring][weight]"]').val()?.trim() || '',
                        score_if_answered: $fieldCard.find('input[name$="[scoring][score_if_answered]"]').val()?.trim() || '',
                        scale_min: $fieldCard.find('input[name$="[scoring][scale_min]"]').val()?.trim() || '',
                        scale_max: $fieldCard.find('input[name$="[scoring][scale_max]"]').val()?.trim() || '',
                        reference_answer: $fieldCard.find('textarea[name$="[scoring][reference_answer]"]').val()?.trim() || '',
                        keyword_groups_text: $fieldCard.find('textarea[name$="[scoring][keyword_groups_text]"]').val()?.trim() || '',
                        synonym_map_text: $fieldCard.find('textarea[name$="[scoring][synonym_map_text]"]').val()?.trim() || '',
                        min_words: $fieldCard.find('input[name$="[scoring][min_words]"]').val()?.trim() || '',
                        confidence_threshold: $fieldCard.find('input[name$="[scoring][confidence_threshold]"]').val()?.trim() || '',
                        manual_review_below_confidence: false,
                        numeric_direction: $fieldCard.find('select[name$="[scoring][numeric_direction]"]').val()?.trim() || '',
                        min_threshold: $fieldCard.find('input[name$="[scoring][min_threshold]"]').val()?.trim() || '',
                        target_threshold: $fieldCard.find('input[name$="[scoring][target_threshold]"]').val()?.trim() || '',
                        max_threshold: $fieldCard.find('input[name$="[scoring][max_threshold]"]').val()?.trim() || '',
                        min_score: $fieldCard.find('input[name$="[scoring][min_score]"]').val()?.trim() || '',
                        target_score: $fieldCard.find('input[name$="[scoring][target_score]"]').val()?.trim() || '',
                        max_score: $fieldCard.find('input[name$="[scoring][max_score]"]').val()?.trim() || '',
                        advanced_rules_text: $fieldCard.find('textarea[name$="[scoring][advanced_rules_text]"]').val()?.trim() || '',
                    },
                    lebar_kolom: $fieldCard.find('select[name$="[lebar_kolom]"]').val() || 'col-md-12',
                    urutan: Number($fieldCard.find('input[name$="[urutan]"]').val() || fieldIndex + 1),
                    is_required: $fieldCard.find('input[name$="[is_required]"]').is(':checked'),
                    is_active: $fieldCard.find('input[name$="[is_active]"]').is(':checked'),
                };
            };

            const collectBuilderPayload = () => {
                return $('.assessment-form-card').map(function(formIndex) {
                    const $formCard = $(this);
                    const rawFormId = $formCard.find('.assessment-form-id-input').val();
                    const formId = Number(rawFormId || 0);
                    const fields = $formCard.find('.assessment-field-card').map(function(fieldIndex) {
                        return collectFieldPayload($(this), fieldIndex);
                    }).get();

                    return {
                        id: formId > 0 ? formId : null,
                        judul_form: $formCard.find('input[name$="[judul_form]"]').val()?.trim() || '',
                        kode_form: $formCard.find('input[name$="[kode_form]"]').val()?.trim() || '',
                        deskripsi: $formCard.find('.form-description-input').first().val()?.trim() || '',
                        kompetensi: $formCard.find('select[name$="[kompetensi]"]').val()?.trim() || '',
                        indikator_kode: $formCard.find('input[name$="[indikator_kode]"]').val()?.trim() || '',
                        indikator_label: $formCard.find('input[name$="[indikator_label]"]').val()?.trim() || '',
                        scoring: {
                            profile: $formCard.find('select[name$="[scoring][profile]"]').val()?.trim() || '',
                            weight: $formCard.find('input[name$="[scoring][weight]"]').val()?.trim() || '',
                            exclude_from_competency: $formCard.find('input[name$="[scoring][exclude_from_competency]"]').is(':checked'),
                            advanced_rules_text: $formCard.find('textarea[name$="[scoring][advanced_rules_text]"]').val()?.trim() || '',
                        },
                        is_scoreable: $formCard.find('input[name$="[is_scoreable]"]').first().is(':checked'),
                        urutan: Number($formCard.find('input[name$="[urutan]"]').val() || formIndex + 1),
                        is_active: $formCard.find('input[name$="[is_active]"]').first().is(':checked'),
                        fields: fields,
                    };
                }).get();
            };

            const getMultipleChoiceOptions = ($fieldCard) => {
                return $fieldCard.find('.multiple-choice-option-row').map(function() {
                    const $optionRow = $(this);

                    return {
                        label: $optionRow.find('.radio-option-text').val()?.trim() || '',
                        value: $optionRow.find('.radio-option-code').val()?.trim() || '',
                        score: $optionRow.find('.radio-option-score').val()?.trim() || '',
                        level_kompetensi: $optionRow.find('.radio-option-level').val()?.trim() || '',
                    };
                }).get().filter((option) => option.label || option.value || option.level_kompetensi);
            };

            const getBadgeClass = (status) => {
                if (status === 'publish') {
                    return 'success';
                }

                if (status === 'draft') {
                    return 'warning';
                }

                return 'secondary';
            };

            const formatStatusLabel = (status) => {
                if (!status) {
                    return 'Draft';
                }

                return status.charAt(0).toUpperCase() + status.slice(1);
            };

            const resolveSelectedKetenagaanLabel = () => {
                const value = $('input[name="target_ketenagaan"]:checked').val() || '';
                return ketenagaanLabels[value] || 'Belum dipilih';
            };

            const resolveSelectedInstrumentLabel = () => {
                const value = $('select[name="instrument_type"]').val() || '';
                return instrumentTypes[value] || 'Belum dipilih';
            };

            const buildBuilderSummarySnapshot = () => {
                const forms = collectBuilderPayload();
                const getMeaningfulFields = (form) => {
                    return (form.fields || []).filter((field) => {
                        return Boolean(String(field.label || '').trim());
                    });
                };
                const totalForms = forms.length;
                const totalQuestions = forms.reduce((total, form) => total + getMeaningfulFields(form).length, 0);
                const activeForms = forms.filter((form) => normalizeChecked(form.is_active)).length;
                const scoreableForms = forms.filter((form) => {
                    return normalizeChecked(form.is_scoreable) && getMeaningfulFields(form).length > 0;
                }).length;
                const autoScoringQuestions = forms.reduce((total, form) => {
                    return total + getMeaningfulFields(form).filter((field) => normalizeChecked(field.scoring?.enabled)).length;
                }, 0);
                const visibleQuestions = forms.reduce((total, form) => {
                    if (!normalizeChecked(form.is_active)) {
                        return total;
                    }

                    return total + getMeaningfulFields(form).filter((field) => normalizeChecked(field.is_active)).length;
                }, 0);
                const previewForms = forms.filter((form) => {
                    return normalizeChecked(form.is_active)
                        && getMeaningfulFields(form).some((field) => normalizeChecked(field.is_active));
                }).length;

                return {
                    title: $('input[name="judul"]').val()?.trim() || '',
                    status: $('select[name="status"]').val() || 'draft',
                    isActive: $('#assessment-active').is(':checked'),
                    targetLabel: resolveSelectedKetenagaanLabel(),
                    instrumentLabel: resolveSelectedInstrumentLabel(),
                    totalForms: totalForms,
                    totalQuestions: totalQuestions,
                    activeForms: activeForms,
                    scoreableForms: scoreableForms,
                    autoScoringQuestions: autoScoringQuestions,
                    visibleQuestions: visibleQuestions,
                    previewForms: previewForms,
                };
            };

            const buildBuilderSummaryNote = (summary) => {
                if (!summary.title) {
                    return 'Isi judul assessment terlebih dahulu agar struktur yang Anda susun mudah dikenali.';
                }

                if (!summary.totalQuestions) {
                    return 'Tambahkan minimal satu pertanyaan agar assessment siap dipakai pada penugasan.';
                }

                if (!summary.previewForms) {
                    return 'Aktifkan minimal satu form agar pertanyaan bisa tampil pada sisi peserta.';
                }

                if (!summary.isActive) {
                    return 'Struktur assessment sudah terisi, tetapi assessment utama masih nonaktif.';
                }

                if (summary.status !== 'publish') {
                    return `Struktur sudah siap, namun status assessment masih ${formatStatusLabel(summary.status).toLowerCase()}.`;
                }

                return `${summary.previewForms} form aktif dengan ${summary.visibleQuestions} pertanyaan siap ditampilkan ke peserta. ${summary.scoreableForms} form masuk penilaian.`;
            };

            const renderBuilderSummary = () => {
                const $summaryCard = $('#assessment-builder-summary');

                if (!$summaryCard.length) {
                    return;
                }

                const summary = buildBuilderSummarySnapshot();

                $('#summary-assessment-title').text(summary.title || 'Judul assessment belum diisi');
                $('#summary-total-forms').text(summary.totalForms);
                $('#summary-total-questions').text(summary.totalQuestions);
                $('#summary-active-forms').text(summary.activeForms);
                $('#summary-auto-scoring-questions').text(summary.autoScoringQuestions);
                $('#summary-status-label').text(formatStatusLabel(summary.status));
                $('#summary-activation-label').text(summary.isActive ? 'Aktif' : 'Nonaktif');
                $('#summary-target-label').text(summary.targetLabel);
                $('#summary-instrument-label').text(summary.instrumentLabel);
                $('#summary-scoreable-label').text(`${summary.scoreableForms} form`);
                $('#summary-display-label').text(
                    summary.previewForms
                    ? `${summary.previewForms} form / ${summary.visibleQuestions} soal`
                    : 'Belum ada form aktif'
                );
                $('#summary-builder-note').text(buildBuilderSummaryNote(summary));
            };

            const sanitizePreviewKey = (value) => {
                return String(value || 'field')
                    .toLowerCase()
                    .replace(/[^a-z0-9_-]/g, '-');
            };

            const isPreviewPanelVisible = () => !$previewPanel.hasClass('d-none');

            const updatePreviewToggleButton = () => {
                const isVisible = isPreviewPanelVisible();

                $previewToggleButton
                    .toggleClass('btn-primary', !isVisible)
                    .toggleClass('btn-secondary', isVisible);

                $previewToggleButton.find('.preview-toggle-label').text(isVisible ? 'Tutup Preview' :
                    'Preview Form');
            };

            const getPreviewState = () => {
                const forms = collectBuilderPayload()
                    .filter((form) => normalizeChecked(form.is_active))
                    .map((form) => {
                        const activeFields = (form.fields || [])
                            .filter((field) => normalizeChecked(field.is_active))
                            .map((field) => ({
                                label: field.label || 'Field tanpa label',
                                description: field.deskripsi || '',
                                name: slugifyFieldName(field.label || ''),
                                type: field.tipe_field || 'text',
                                placeholder: field.placeholder || '',
                                helpText: field.bantuan || '',
                                autofillSource: field.autofill_source || '',
                                autofillSourceLabel: participantAutoFillOptions[field.autofill_source || ''] || '',
                                lookupSource: field.lookup_source || '',
                                lookupSourceLabel: fieldLookupOptions[field.lookup_source || ''] || '',
                                lookupSourceCount: Number(resolveFieldLookupPreviewMeta(field.lookup_source || '')?.total || 0),
                                allowOtherInput: normalizeChecked(field.allow_other_input),
                                fileInputMode: normalizeFileInputMode(
                                    field.file_input_mode || parseFileFieldConfigJson(field.raw_opsi_field_json || '').input_mode
                                ),
                                options: field.tipe_field === multipleChoiceFieldType ?
                                    (field.radio_options || []) :
                                    (field.tipe_field === repeaterFieldType ?
                                        parseRepeaterConfig(field.repeater_config_text) :
                                        resolvePreviewChoiceOptions(field)),
                                widthClass: field.lebar_kolom || 'col-md-12',
                                required: normalizeChecked(field.is_required),
                            }));

                        return {
                            title: form.judul_form || 'Child form tanpa judul',
                            code: form.kode_form || '-',
                            description: form.deskripsi || '',
                            kompetensi: form.kompetensi || '',
                            kompetensiLabel: teacherCompetencies[form.kompetensi || ''] || '',
                            indikatorKode: form.indikator_kode || '',
                            indikatorLabel: form.indikator_label || '',
                            isScoreable: normalizeChecked(form.is_scoreable),
                            fields: activeFields,
                        };
                    })
                    .filter((form) => form.fields.length);

                return {
                    title: $('input[name="judul"]').val()?.trim() || 'Judul assessment belum diisi',
                    code: $('[data-assessment-code-display]').val()?.trim() || 'Otomatis saat disimpan',
                    description: $('textarea[name="deskripsi"]').val()?.trim() || '',
                    instruction: $('textarea[name="petunjuk"]').val()?.trim() || '',
                    status: $('select[name="status"]').val() || 'draft',
                    isActive: $('#assessment-active').is(':checked'),
                    forms: forms,
                };
            };

            const renderPreviewFieldInput = (field, previewKey) => {
                const fieldLabel = `${escapeHtml(field.label)}${field.required ? ' <span class="text-danger">*</span>' : ''}`;
                const placeholder = escapeHtml(field.placeholder);
                let inputHtml = '';

                if (field.type === 'textarea') {
                    inputHtml = `
                        <textarea class="form-control" rows="3" placeholder="${placeholder}"></textarea>
                    `;
                } else if (field.type === 'select') {
                    const options = field.options.length ? field.options : [{
                        label: 'Belum ada opsi',
                        value: ''
                    }];
                    const optionsHtml = options.map((option) => {
                        const normalizedOption = option && typeof option === 'object' ? option : {
                            label: String(option || ''),
                            value: String(option || ''),
                        };

                        return `<option value="${escapeHtml(normalizedOption.value || '')}">${escapeHtml(normalizedOption.label || normalizedOption.value || '')}</option>`;
                    }).join('');

                    inputHtml = `
                        <select class="form-control">
                            <option value="" selected>${placeholder || '-- Pilih salah satu --'}</option>
                            ${optionsHtml}
                        </select>
                        ${field.allowOtherInput ? `
                            <input type="text" class="form-control mt-2"
                                value=""
                                placeholder="Tulis jawaban ${escapeHtml(selectOtherOptionLabel.toLowerCase())}"
                                disabled>
                        ` : ''}
                    `;
                } else if (field.type === 'radio') {
                    const options = field.options.length ? field.options : [{
                        label: '',
                        value: ''
                    }];

                    inputHtml = options.map((option, index) => {
                        const normalizedOption = normalizeRadioOptionShape(option);
                        const optionCode = normalizedOption.value || generateChoiceLabel(index);
                        const optionText = normalizedOption.label || 'Belum ada isi jawaban';
                        const optionCompetencyLevelLabel = resolveCompetencyLevelLabel(
                            normalizedOption.level_kompetensi || getDefaultCompetencyLevel(index)
                        );
                        const inputId = `${sanitizePreviewKey(previewKey)}-${index}`;

                        return `
                            <label for="${inputId}" class="d-block rounded border bg-white px-3 py-3 mb-2">
                                <div class="d-flex align-items-start">
                                    <input type="radio" class="mt-1 mr-3"
                                        id="${inputId}"
                                        name="${sanitizePreviewKey(previewKey)}">
                                    <div class="flex-grow-1">
                                        <div class="row">
                                            <div class="col-md-2 mb-2 mb-md-0">

                                                <div class="">${escapeHtml(optionCode)}</div>
                                            </div>
                                            <div class="col-md-10">

                                                <div class="">${escapeHtml(optionText)}</div>
                                                ${optionCompetencyLevelLabel ? `<span class="badge badge-light border mt-2">${escapeHtml(optionCompetencyLevelLabel)}</span>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        `;
                    }).join('');
                } else if (field.type === repeaterFieldType) {
                    const config = field.options && typeof field.options === 'object' ? field.options : null;
                    const columns = Array.isArray(config?.columns) ? config.columns : [];

                    if (!columns.length) {
                        inputHtml = `
                            <div class="alert alert-light border mb-0">
                                Konfigurasi tabel belum tersedia.
                            </div>
                        `;
                    } else {
                        const headerHtml = columns.map((column) => {
                            return `<th>${escapeHtml(column.label || column.nama_field || 'Kolom')}</th>`;
                        }).join('');
                        const cellHtml = columns.map((column) => {
                            const type = column.tipe_field || 'text';

                            if (type === 'select') {
                                const options = Array.isArray(column.opsi_field) ? column.opsi_field : [];
                                const optionsHtml = options.map((option) => {
                                    return `<option value="${escapeHtml(option)}">${escapeHtml(option)}</option>`;
                                }).join('');

                                return `
                                    <td>
                                        <select class="form-control" disabled>
                                            <option value="">Pilih</option>
                                            ${optionsHtml}
                                        </select>
                                    </td>
                                `;
                            }

                            if (type === 'textarea') {
                                return '<td><textarea class="form-control" rows="2" disabled></textarea></td>';
                            }

                            return `
                                <td>
                                    <input type="${['text', 'email', 'number', 'date', 'url'].includes(type) ? type : 'text'}"
                                        class="form-control"
                                        placeholder="${escapeHtml(column.placeholder || '')}"
                                        readonly>
                                </td>
                            `;
                        }).join('');

                        inputHtml = `
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>${headerHtml}</tr>
                                    </thead>
                                    <tbody>
                                        <tr>${cellHtml}</tr>
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }
                } else if (field.type === 'checkbox') {
                    const options = field.options.length ? field.options : [{
                        label: 'Belum ada opsi',
                        value: ''
                    }];

                    inputHtml = options.map((option, index) => {
                        const normalizedOption = option && typeof option === 'object' ? option : {
                            label: String(option || ''),
                            value: String(option || ''),
                        };
                        const inputId = `${sanitizePreviewKey(previewKey)}-${index}`;

                        return `
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input"
                                    id="${inputId}"
                                    name="${sanitizePreviewKey(previewKey)}[]"
                                    value="${escapeHtml(normalizedOption.value || '')}">
                                <label class="custom-control-label"
                                    for="${inputId}">
                                    ${escapeHtml(normalizedOption.label || normalizedOption.value || '')}
                                </label>
                            </div>
                        `;
                    }).join('');
                } else if (field.type === 'file') {
                    const fileInputMode = normalizeFileInputMode(field.fileInputMode || 'file');

                    inputHtml = fileInputMode === 'link'
                        ? `
                            <input type="url" class="form-control"
                                value=""
                                placeholder="${placeholder || 'https://drive.google.com/file/d/.../view'}">
                        `
                        : `
                            <div class="custom-file">
                                <input type="file" class="custom-file-input">
                                <label class="custom-file-label">
                                    Pilih file
                                </label>
                            </div>
                        `;
                } else {
                    const typeMap = {
                        text: 'text',
                        email: 'email',
                        number: 'number',
                        date: 'date',
                    };

                    inputHtml = `
                        <input type="${typeMap[field.type] || 'text'}" class="form-control"
                            value="" placeholder="${placeholder}">
                    `;
                }

                return `
                    <div class="form-group">
                        <label>${fieldLabel}</label>
                        ${field.description ? `<small class="form-text text-muted mb-2">${escapeHtml(field.description)}</small>` : ''}
                        ${field.autofillSourceLabel ? `<small class="form-text text-primary mb-2">Auto-fill peserta: ${escapeHtml(field.autofillSourceLabel)}</small>` : ''}
                        ${field.lookupSourceLabel ? `<small class="form-text text-info mb-2">Lookup database: ${escapeHtml(field.lookupSourceLabel)}${field.lookupSourceCount ? ` (${escapeHtml(field.lookupSourceCount)} data)` : ''}</small>` : ''}
                        ${inputHtml}
                        ${field.helpText ? `<small class="form-text text-muted">${escapeHtml(field.helpText)}</small>` : ''}
                    </div>
                `;
            };

            const renderPreview = () => {
                const data = getPreviewState();
                let contentHtml = `
                    <div class="card  border-0 mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                <div class="mb-3">
                                    <div class="text-muted small">Kode Assessment</div>
                                    <div class="font-weight-bold">${escapeHtml(data.code)}</div>
                                </div>
                                <div class="mb-3">
                                    <span class="badge badge-${getBadgeClass(data.status)}">${escapeHtml(formatStatusLabel(data.status))}</span>
                                    <span class="badge badge-${data.isActive ? 'primary' : 'light'}">
                                        ${data.isActive ? 'Aktif' : 'Nonaktif'}
                                    </span>
                                </div>
                            </div>
                            <h3 class="mb-2">${escapeHtml(data.title)}</h3>
                            ${data.description ? `<p class="text-muted mb-3">${escapeHtml(data.description)}</p>` : ''}
                            ${data.instruction ? `
                                <div class="alert alert-light border mb-0">
                                    <div class="font-weight-bold mb-1">Petunjuk Pengisian</div>
                                    <div>${escapeHtml(data.instruction)}</div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;

                if (!data.forms.length) {
                    contentHtml += `
                        <div class="empty-state" data-height="260">
                            <div class="empty-state-icon bg-secondary">
                                <i class="fas fa-eye-slash"></i>
                            </div>
                            <h2>Belum ada form aktif untuk dipreview</h2>
                            <p class="lead mb-0">
                                Aktifkan form dan field yang ingin ditampilkan ke user.
                            </p>
                        </div>
                    `;

                    $previewContent.html(contentHtml);
                    return;
                }

                data.forms.forEach((form, index) => {
                    const fieldsHtml = form.fields.map((field, fieldIndex) => {
                        return `
                            <div class="${escapeHtml(field.widthClass || 'col-md-12')}">
                                ${renderPreviewFieldInput(field, `${form.code || form.title}-${field.name || fieldIndex}`)}
                            </div>
                        `;
                    }).join('');

                    contentHtml += `
                        <div class="card  border-0 mb-4">
                            <div class="card-header bg-white">
                                <div>
                                    <h4 class="mb-1">${escapeHtml(form.title)}</h4>
                                    <small class="text-muted">Bagian ${index + 1} • ${escapeHtml(form.code)}</small>
                                    ${(form.kompetensiLabel || form.indikatorKode || form.isScoreable !== undefined) ? `
                                        <div class="mt-2">
                                            ${form.kompetensiLabel ? `<span class="badge badge-info mr-1">${escapeHtml(form.kompetensiLabel)}</span>` : ''}
                                            ${form.indikatorKode ? `<span class="badge badge-light border mr-1">Indikator ${escapeHtml(form.indikatorKode)}</span>` : ''}
                                            <span class="badge badge-${form.isScoreable ? 'success' : 'secondary'}">
                                                ${form.isScoreable ? 'Masuk penilaian' : 'Hanya pengumpulan data'}
                                            </span>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                            <div class="card-body">
                                ${form.description ? `<p class="text-muted">${escapeHtml(form.description)}</p>` : ''}
                                <div class="row">
                                    ${fieldsHtml}
                                </div>
                            </div>
                        </div>
                    `;
                });

                $previewContent.html(contentHtml);
            };

            const syncPreviewFileInput = () => {
                $previewContent.off('change', '.custom-file-input').on('change', '.custom-file-input',
                    function() {
                        const fileName = this.files && this.files.length ? this.files[0].name :
                            'Pilih file';
                        $(this).next('.custom-file-label').text(fileName);
                    });
            };

            const openPreviewPanel = () => {
                renderPreview();
                syncPreviewFileInput();
                $previewPanel.removeClass('d-none');
                updatePreviewToggleButton();

                $('html, body').animate({
                    scrollTop: $previewPanel.offset().top - 90
                }, 250);
            };

            const closePreviewPanel = () => {
                $previewPanel.addClass('d-none');
                updatePreviewToggleButton();
            };

            const schedulePreviewRender = () => {
                if (!isPreviewPanelVisible()) {
                    return;
                }

                clearTimeout(previewRenderTimer);
                previewRenderTimer = setTimeout(function() {
                    renderPreview();
                    syncPreviewFileInput();
                }, 120);
            };

            $('#btn-add-form').on('click', function() {
                appendForm();
                schedulePreviewRender();
            });

            $('#btn-sidebar-add-form').on('click', function() {
                appendForm();
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-add-field', function() {
                appendField($(this).closest('.assessment-form-card'));
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-add-radio-option', function() {
                const $fieldCard = $(this).closest('.assessment-field-card');
                appendRadioOption($fieldCard);
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-remove-form', function() {
                $(this).closest('.assessment-form-card').remove();
                toggleEmptyState();
                renderBuilderSummary();
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-remove-field', function() {
                $(this).closest('.assessment-field-card').remove();
                renderBuilderSummary();
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-remove-radio-option', function() {
                const $fieldCard = $(this).closest('.assessment-field-card');

                if ($fieldCard.find('.multiple-choice-option-row').length <= 2) {
                    return;
                }

                $(this).closest('.multiple-choice-option-row').remove();
                reindexRadioOptions($fieldCard);
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-add-repeater-column', function() {
                const $fieldCard = $(this).closest('.assessment-field-card');
                appendRepeaterColumn($fieldCard);
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-remove-repeater-column', function() {
                const $fieldCard = $(this).closest('.assessment-field-card');

                if ($fieldCard.find('.repeater-column-row').length <= 1) {
                    return;
                }

                $(this).closest('.repeater-column-row').remove();
                syncRepeaterConfigState($fieldCard);
                schedulePreviewRender();
            });

            $(document).on('input', '.repeater-column-label-input', function() {
                const $columnRow = $(this).closest('.repeater-column-row');
                const $nameInput = $columnRow.find('.repeater-column-name-input');
                const autoGenerated = isRepeaterColumnNameAutoGenerated($nameInput);

                if (autoGenerated || !$nameInput.val()?.trim()) {
                    $nameInput.val(getRepeaterColumnDefaultName($columnRow));
                    $nameInput.data('autoGenerated', true);
                }

                syncRepeaterConfigState($(this).closest('.assessment-field-card'));
                schedulePreviewRender();
            });

            $(document).on('input', '.repeater-column-name-input', function() {
                const $columnRow = $(this).closest('.repeater-column-row');
                const normalizedValue = slugifyFieldName($(this).val() || '');
                const defaultName = getRepeaterColumnDefaultName($columnRow);

                $(this).val(normalizedValue);
                $(this).data('autoGenerated', !normalizedValue || normalizedValue === defaultName);

                syncRepeaterConfigState($(this).closest('.assessment-field-card'));
                schedulePreviewRender();
            });

            $(document).on('input', '.repeater-min-rows-input, .repeater-max-rows-input, .repeater-column-placeholder-input, .repeater-column-options-input', function() {
                syncRepeaterConfigState($(this).closest('.assessment-field-card'));
                schedulePreviewRender();
            });

            $(document).on('change', '.repeater-column-type-select, .repeater-column-required-input', function() {
                syncRepeaterConfigState($(this).closest('.assessment-field-card'));
                schedulePreviewRender();
            });

            $(document).on('input', '.radio-option-text, .radio-option-code', function() {
                schedulePreviewRender();
            });

            $(document).on('input', '.field-label-input', function() {
                const $fieldCard = $(this).closest('.assessment-field-card');
                updateAutoFieldNameHint($fieldCard);
                updateParticipantAutofillState($fieldCard);
                updateFieldLookupState($fieldCard);
                syncRepeaterConfigState($fieldCard);
                schedulePreviewRender();
            });

            $(document).on('change', '.field-type-select', function() {
                const $fieldCard = $(this).closest('.assessment-field-card');
                toggleOptionWrapper($fieldCard);
                updateParticipantAutofillState($fieldCard);
                updateFieldLookupState($fieldCard);
                updateFileInputModeState($fieldCard);
                toggleScoringWrapper($fieldCard);
                syncRepeaterConfigState($fieldCard);
                schedulePreviewRender();
            });

            $(document).on('change', '.field-autofill-source-select', function() {
                $(this).data('userTouched', true);
                updateParticipantAutofillState($(this).closest('.assessment-field-card'));
                schedulePreviewRender();
            });

            $(document).on('change', '.field-lookup-source-select', function() {
                $(this).data('userTouched', true);
                $(this).data('suggestedDefault', false);
                updateFieldLookupState($(this).closest('.assessment-field-card'));
                schedulePreviewRender();
            });

            $(document).on('change', '.field-file-input-mode-select', function() {
                updateFileInputModeState($(this).closest('.assessment-field-card'));
                schedulePreviewRender();
            });

            $(document).on('change', 'input[name="target_ketenagaan"]', function() {
                $('.assessment-field-card').each(function() {
                    updateFieldLookupState($(this));
                });
                renderBuilderSummary();
                schedulePreviewRender();
            });

            $(document).on('change', '.field-scoring-enabled, .field-scoring-method', function() {
                toggleScoringWrapper($(this).closest('.assessment-field-card'));
                schedulePreviewRender();
            });

            $(document).on('change', '.scoring-config-card input, .scoring-config-card textarea, .scoring-config-card select', function() {
                toggleScoringWrapper($(this).closest('.assessment-field-card'));
            });

            $(document).on('click', '.btn-toggle-scoring-advanced', function() {
                const $fieldCard = $(this).closest('.assessment-field-card');
                $fieldCard.find('.scoring-advanced-panel').toggleClass('d-none');
                toggleScoringWrapper($fieldCard);
            });

            $(document).on('click', '.btn-generate-scoring-helper', function() {
                const $fieldCard = $(this).closest('.assessment-field-card');
                applyScoringGuidanceSuggestion($fieldCard, {
                    force: true,
                });
                toggleScoringWrapper($fieldCard);
                schedulePreviewRender();
            });

            $(document).on('click', '.btn-copy-description-to-scoring', function() {
                const $fieldCard = $(this).closest('.assessment-field-card');
                applyScoringGuidanceSuggestion($fieldCard, {
                    copyDescriptionToReference: true,
                    force: true,
                });
                toggleScoringWrapper($fieldCard);
                schedulePreviewRender();
            });

            $previewToggleButton.on('click', function() {
                if (isPreviewPanelVisible()) {
                    closePreviewPanel();
                    return;
                }

                openPreviewPanel();
            });

            $('.btn-close-preview-panel').on('click', function() {
                closePreviewPanel();
            });

            $('.btn-refresh-preview-panel').on('click', function() {
                renderPreview();
                syncPreviewFileInput();
            });

            $('#assessment-builder-form').on('input change', 'input, textarea, select', function() {
                renderBuilderSummary();
                schedulePreviewRender();
            });

            $('#assessment-builder-form').on('input', keywordGroupsFieldSelector, function() {
                validateKeywordGroupsField($(this));
            });

            $('#assessment-builder-form').on('change focusout', keywordGroupsFieldSelector, function() {
                validateKeywordGroupsField($(this), { normalize: true });
            });

            $('#assessment-builder-form').on('submit', function(event) {
                if ($builderShell.hasClass('is-loading')) {
                    event.preventDefault();
                    setBuilderLoadingState(true, 'Form masih dimuat. Tunggu sebentar sampai seluruh struktur selesai tampil.');
                    return false;
                }

                $('.assessment-field-card').each(function() {
                    applyScoringGuidanceSuggestion($(this));
                    syncRepeaterConfigState($(this));
                });

                if (!validateAllKeywordGroupsFields()) {
                    event.preventDefault();
                    return false;
                }

                $('#forms-payload').val(JSON.stringify(collectBuilderPayload()));
                $('#form-builder-list').find('input, textarea, select').prop('disabled', true);
            });

            const initializeBuilder = () => {
                const formsToRender = Array.isArray(initialForms) && initialForms.length ? initialForms : [{}];
                const totalForms = formsToRender.length;
                const batchSize = totalForms > 12 ? 1 : (totalForms > 4 ? 2 : totalForms);
                let renderedForms = 0;

                const renderBatch = () => {
                    formsToRender.slice(renderedForms, renderedForms + batchSize).forEach((form) => {
                        appendForm(form, {
                            skipSummary: true,
                        });
                    });

                    renderedForms = Math.min(renderedForms + batchSize, totalForms);

                    if (renderedForms < totalForms) {
                        setBuilderLoadingState(true, buildBuilderLoadingMessage(renderedForms, totalForms));
                        scheduleAfterPaint(renderBatch);
                        return;
                    }

                    toggleEmptyState();
                    renderBuilderSummary();
                    updatePreviewToggleButton();
                    setBuilderLoadingState(false);
                };

                setBuilderLoadingState(true, buildBuilderLoadingMessage(0, totalForms));
                scheduleAfterPaint(renderBatch);
            };

            initializeBuilder();
        });
    </script>
@endpush
