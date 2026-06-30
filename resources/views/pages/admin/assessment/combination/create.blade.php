@extends('layouts.app', ['title' => 'Buat Kombinasi Soal'])

@php
    $selectedTargetKetenagaan = old(
        'target_ketenagaan',
        \App\Enum\AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
    );
    $initialSelectionModes = collect((array) old('competency_selection_modes', []))
        ->mapWithKeys(function ($modes, $assessmentId) {
            return [
                (int) $assessmentId => collect((array) $modes)
                    ->mapWithKeys(fn ($mode, $kompetensi) => [trim((string) $kompetensi) => $mode === 'all' ? 'all' : 'count'])
                    ->all(),
            ];
        })
        ->all();
    $initialTakeCounts = collect((array) old('competency_take_counts', []))
        ->mapWithKeys(function ($counts, $assessmentId) {
            return [
                (int) $assessmentId => collect((array) $counts)
                    ->mapWithKeys(fn ($count, $kompetensi) => [trim((string) $kompetensi) => max((int) $count, 0)])
                    ->all(),
            ];
        })
        ->all();
    $competencyErrorMap = collect($errors->getMessages())
        ->mapWithKeys(function ($messages, $key) {
            if (preg_match('/^competency_(?:take_counts|selection_modes)\.(\d+)\.([a-z_]+)$/', $key, $matches) !== 1) {
                return [];
            }

            return [
                $matches[1].'.'.$matches[2] => $messages[0] ?? '',
            ];
        })
        ->all();
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
@endphp

@push('styles')
    <style>
        .combination-ketenagaan-grid {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .combination-ketenagaan-option {
            position: relative;
        }

        .combination-ketenagaan-input {
            opacity: 0;
            pointer-events: none;
            position: absolute;
        }

        .combination-ketenagaan-card {
            align-items: center;
            background: #fff;
            border: 1px solid #dfe7f7;
            border-radius: 0.2rem;
            cursor: pointer;
            display: flex;
            gap: 0.85rem;
            margin-bottom: 0;
            min-height: 94px;
            padding: 1rem 1.1rem;
            transition: all 0.2s ease;
        }

        .combination-ketenagaan-card:hover {
            border-color: #bccdf5;
            box-shadow: 0 10px 24px rgba(52, 73, 94, 0.08);
            transform: translateY(-1px);
        }

        .combination-ketenagaan-card__icon {
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

        .combination-ketenagaan-card__title {
            color: #334155;
            display: block;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .combination-ketenagaan-card__hint {
            color: #7b8898;
            display: block;
            font-size: 0.8rem;
            margin-top: 0.15rem;
        }

        .combination-ketenagaan-card--pendidik .combination-ketenagaan-card__icon {
            background: linear-gradient(135deg, #1174c7, #2f8fe1);
        }

        .combination-ketenagaan-card--kependidikan .combination-ketenagaan-card__icon {
            background: linear-gradient(135deg, #0d8b8c, #1fa3a4);
        }

        .combination-ketenagaan-card--stakeholder .combination-ketenagaan-card__icon {
            background: linear-gradient(135deg, #e5a100, #f5bc2b);
        }

        .combination-ketenagaan-input:checked + .combination-ketenagaan-card {
            border-color: #6777ef;
            box-shadow: 0 14px 28px rgba(103, 119, 239, 0.16);
            transform: translateY(-1px);
        }

        .combination-summary-panel {
            background: #f8fbff;
            border: 1px solid #d7e3f8;
            border-radius: 0.2rem;
            padding: 1rem;
        }

        .combination-empty-state {
            color: #7b8898;
            padding: 2rem 1rem;
            text-align: center;
        }

        .combination-assessment-card {
            border: 1px solid #e6ebf5;
            border-radius: 0.35rem;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .combination-assessment-card__header {
            background: #f8fbff;
            border-bottom: 1px solid #e6ebf5;
            padding: 1rem 1.1rem;
        }

        .combination-assessment-card__body {
            padding: 1rem 1.1rem 1.15rem;
        }

        .combination-assessment-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .combination-assessment-meta .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.42rem 0.6rem;
        }

        .combination-competency-table th,
        .combination-competency-table td,
        .combination-fixed-form-table th,
        .combination-fixed-form-table td {
            vertical-align: middle;
        }

        .combination-competency-table .form-control {
            min-width: 120px;
        }

        .combination-row-disabled {
            background: #fafbfd;
        }

        .combination-row-error {
            border-left: 3px solid #fc544b;
        }

        .combination-fixed-note {
            background: #fbfcfe;
            border: 1px dashed #d9e1ef;
            border-radius: 0.25rem;
            padding: 0.85rem 0.95rem;
        }

        @media (max-width: 991.98px) {
            .combination-ketenagaan-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Buat Kombinasi Soal</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.combination.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="section-body">
                @if ($errors->has('combination'))
                    <div class="alert alert-danger">
                        {{ $errors->first('combination') }}
                    </div>
                @endif

                @if ($errors->has('competency_selection_modes'))
                    <div class="alert alert-danger">
                        {{ $errors->first('competency_selection_modes') }}
                    </div>
                @endif

                @if ($errors->has('competency_take_counts'))
                    <div class="alert alert-danger">
                        {{ $errors->first('competency_take_counts') }}
                    </div>
                @endif

                <form action="{{ route('assessment.combination.store') }}" method="POST" id="combination-form">
                    @csrf

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Konfigurasi Kombinasi</h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-light border mb-4">
                                        Sistem akan mengambil child soal berdasarkan kompetensi pada setiap assessment.
                                        Untuk form yang tidak memiliki kompetensi, seluruh input pada form tersebut akan ikut
                                        otomatis dan tidak diacak.
                                    </div>

                                    <div class="form-group">
                                        <label>Ketenagaan <span class="text-danger">*</span></label>
                                        <div class="combination-ketenagaan-grid">
                                            @foreach ($ketenagaanCards as $value => $card)
                                                <div class="combination-ketenagaan-option">
                                                    <input type="radio" class="combination-ketenagaan-input"
                                                        id="combination-ketenagaan-{{ $value }}"
                                                        name="target_ketenagaan" value="{{ $value }}"
                                                        @checked($selectedTargetKetenagaan === $value) required>
                                                    <label for="combination-ketenagaan-{{ $value }}"
                                                        class="combination-ketenagaan-card combination-ketenagaan-card--{{ $card['theme'] }}">
                                                        <span class="combination-ketenagaan-card__icon">
                                                            <i class="{{ $card['icon'] }}"></i>
                                                        </span>
                                                        <span>
                                                            <span class="combination-ketenagaan-card__title">{{ $card['label'] }}</span>
                                                            <span class="combination-ketenagaan-card__hint">
                                                                Assessment aktif pada ketenagaan ini akan dipetakan ke empat
                                                                kompetensi dan form tanpa kompetensi.
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('target_ketenagaan')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
                                        <h4 class="mb-0">Input Soal Per Assessment</h4>
                                        <div class="d-flex align-items-center flex-wrap">
                                            <input type="number" min="1" value="10"
                                                class="form-control form-control-sm mr-2" id="apply-all-count"
                                                style="width: 90px;">
                                            <button type="button" class="btn btn-sm btn-light mr-2" id="apply-all-button">
                                                Terapkan ke Semua Kompetensi
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="apply-all-mode-all">
                                                Gunakan Semua Soal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="combination-assessment-panels">
                                        <div class="combination-empty-state">
                                            Memuat data assessment...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="sticky-top">
                                <div class="card card-body mb-4">
                                    <h6 class="text-primary mb-3">Ringkasan Kombinasi</h6>
                                    <div class="mb-3">
                                        <div class="text-muted small">Kodenya</div>
                                        <div class="font-weight-bold">Otomatis saat simpan</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small">Ketenagaan Dipilih</div>
                                        <div class="font-weight-bold" id="summary-ketenagaan">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small">Assessment Sumber</div>
                                        <div class="font-weight-bold" id="summary-assessments">0</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="text-muted small">Total Form</div>
                                            <div class="font-weight-bold" id="summary-forms">0</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted small">Soal Sumber</div>
                                            <div class="font-weight-bold" id="summary-source-questions">0</div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="mb-3">
                                        <div class="text-muted small">Total Child Soal</div>
                                        <div class="font-weight-bold" id="summary-selected-questions">0</div>
                                    </div>
                                    <div class="mb-0">
                                        <div class="text-muted small">Catatan</div>
                                        <div class="text-muted" id="summary-note">
                                            Kompetensi memakai jumlah soal atau semua soal. Form tanpa kompetensi otomatis ikut seluruh input.
                                        </div>
                                    </div>
                                </div>

                                <div class="combination-summary-panel mb-4">
                                    <div class="text-muted small mb-2">Info Penting</div>
                                    <div class="font-weight-bold mb-2" id="current-source-title">
                                        Pilih ketenagaan untuk melihat sumber assessment.
                                    </div>
                                    <div class="text-muted small mb-0" id="current-source-description">
                                        Kombinasi ini akan menjadi sumber snapshot ujian untuk penugasan yang memilihnya.
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h4>Aksi</h4>
                                    </div>
                                    <div class="card-body">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-save"></i> Simpan Kombinasi
                                        </button>
                                        <a href="{{ route('assessment.combination.index') }}" class="btn btn-light btn-block">
                                            Batal
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const assessmentCatalogByKetenagaan = @json($assessmentCatalogByKetenagaan);
            const ketenagaanOptions = @json($ketenagaanOptions);
            const initialSelectionModes = @json($initialSelectionModes);
            const initialTakeCounts = @json($initialTakeCounts);
            const competencyErrorMap = @json($competencyErrorMap);
            const assessmentPanelsNode = document.getElementById('combination-assessment-panels');
            const applyAllInput = document.getElementById('apply-all-count');
            const applyAllButton = document.getElementById('apply-all-button');
            const applyAllModeAllButton = document.getElementById('apply-all-mode-all');
            const selectionModesState = Object.assign({}, initialSelectionModes);
            const takeCountsState = Object.assign({}, initialTakeCounts);

            function escapeHtml(value) {
                const node = document.createElement('div');
                node.textContent = value == null ? '' : String(value);

                return node.innerHTML;
            }

            function getSelectedKetenagaan() {
                const input = document.querySelector('input[name="target_ketenagaan"]:checked');

                return input ? input.value : '';
            }

            function getAssessmentsForSelectedKetenagaan() {
                const target = getSelectedKetenagaan();

                return target && Array.isArray(assessmentCatalogByKetenagaan[target]) ? assessmentCatalogByKetenagaan[target] : [];
            }

            function ensureAssessmentState(assessment) {
                const assessmentId = Number(assessment.assessment_id || 0);

                if (!selectionModesState[assessmentId] || typeof selectionModesState[assessmentId] !== 'object') {
                    selectionModesState[assessmentId] = {};
                }

                if (!takeCountsState[assessmentId] || typeof takeCountsState[assessmentId] !== 'object') {
                    takeCountsState[assessmentId] = {};
                }

                (assessment.competencies || []).forEach((competency) => {
                    const key = String(competency.kompetensi || '');
                    const available = Number(competency.available_question_count || 0);

                    if (!key || available < 1) {
                        return;
                    }

                    if (!['count', 'all'].includes(selectionModesState[assessmentId][key])) {
                        selectionModesState[assessmentId][key] = 'count';
                    }

                    const currentValue = Number(takeCountsState[assessmentId][key] || 0);
                    takeCountsState[assessmentId][key] = currentValue > 0
                        ? Math.min(currentValue, available)
                        : Math.min(10, available);
                });
            }

            function getCompetencyMode(assessmentId, competencyKey, availableCount) {
                if (availableCount < 1) {
                    return 'unavailable';
                }

                const currentMode = selectionModesState[assessmentId]?.[competencyKey];

                return currentMode === 'all' ? 'all' : 'count';
            }

            function getCompetencyCount(assessmentId, competencyKey, availableCount) {
                if (availableCount < 1) {
                    return 0;
                }

                const currentValue = Number(takeCountsState[assessmentId]?.[competencyKey] || 0);

                if (currentValue > 0) {
                    return Math.min(currentValue, availableCount);
                }

                return Math.min(10, availableCount);
            }

            function setCompetencyMode(assessmentId, competencyKey, value) {
                if (!selectionModesState[assessmentId] || typeof selectionModesState[assessmentId] !== 'object') {
                    selectionModesState[assessmentId] = {};
                }

                selectionModesState[assessmentId][competencyKey] = value === 'all' ? 'all' : 'count';
            }

            function setCompetencyCount(assessmentId, competencyKey, value) {
                if (!takeCountsState[assessmentId] || typeof takeCountsState[assessmentId] !== 'object') {
                    takeCountsState[assessmentId] = {};
                }

                takeCountsState[assessmentId][competencyKey] = Math.max(Number(value || 0), 0);
            }

            function renderAssessmentPanels() {
                const assessments = getAssessmentsForSelectedKetenagaan();

                if (!assessmentPanelsNode) {
                    return;
                }

                if (assessments.length < 1) {
                    assessmentPanelsNode.innerHTML = `
                        <div class="combination-empty-state">
                            Belum ada assessment aktif yang tersedia pada ketenagaan ini.
                        </div>
                    `;
                    updateSummary();

                    return;
                }

                assessmentPanelsNode.innerHTML = assessments.map((assessment, index) => {
                    ensureAssessmentState(assessment);

                    const assessmentId = Number(assessment.assessment_id || 0);
                    const competencies = Array.isArray(assessment.competencies) ? assessment.competencies : [];
                    const autoIncludedForms = Array.isArray(assessment.auto_included_forms) ? assessment.auto_included_forms : [];
                    const competencyRows = competencies.map((competency, competencyIndex) => {
                        const competencyKey = String(competency.kompetensi || '');
                        const available = Number(competency.available_question_count || 0);
                        const availableForms = Number(competency.available_form_count || 0);
                        const mode = getCompetencyMode(assessmentId, competencyKey, available);
                        const count = mode === 'all' ? available : getCompetencyCount(assessmentId, competencyKey, available);
                        const rowError = competencyErrorMap[`${assessmentId}.${competencyKey}`] || '';
                        const indicatorText = Array.isArray(competency.indikator_codes)
                            ? competency.indikator_codes.filter(Boolean).join(', ')
                            : '';

                        if (available > 0) {
                            setCompetencyMode(assessmentId, competencyKey, mode);
                            setCompetencyCount(assessmentId, competencyKey, count);
                        }

                        return `
                            <tr class="${available < 1 ? 'combination-row-disabled' : ''} ${rowError ? 'combination-row-error' : ''}">
                                <td>
                                    <div class="font-weight-bold">${competencyIndex + 1}. ${escapeHtml(competency.kompetensi_label || '-')}</div>
                                    <small class="text-muted">
                                        ${indicatorText ? 'Indikator: ' + escapeHtml(indicatorText) : 'Pool semua form dengan kompetensi ini'}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border">${availableForms} form</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border">${available} soal</span>
                                </td>
                                <td>
                                    <select
                                        name="competency_selection_modes[${assessmentId}][${competencyKey}]"
                                        class="form-control js-competency-mode"
                                        data-assessment-id="${assessmentId}"
                                        data-competency-key="${escapeHtml(competencyKey)}"
                                        data-available="${available}"
                                        ${available < 1 ? 'disabled' : ''}
                                    >
                                        <option value="count" ${mode === 'count' ? 'selected' : ''}>Jumlah soal</option>
                                        <option value="all" ${mode === 'all' ? 'selected' : ''}>Semua soal</option>
                                    </select>
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        min="1"
                                        max="${available}"
                                        step="1"
                                        name="competency_take_counts[${assessmentId}][${competencyKey}]"
                                        value="${available < 1 ? 0 : count}"
                                        class="form-control text-center js-competency-count ${rowError ? 'is-invalid' : ''}"
                                        data-assessment-id="${assessmentId}"
                                        data-competency-key="${escapeHtml(competencyKey)}"
                                        data-available="${available}"
                                        ${available < 1 ? 'disabled' : ''}
                                        ${mode === 'all' ? 'readonly' : ''}
                                    >
                                    ${rowError ? `<div class="invalid-feedback d-block">${escapeHtml(rowError)}</div>` : ''}
                                </td>
                            </tr>
                        `;
                    }).join('');

                    const autoFormsHtml = autoIncludedForms.length > 0
                        ? `
                            <div class="mt-4">
                                <div class="combination-fixed-note mb-3">
                                    <div class="font-weight-bold mb-1">Form Tanpa Kompetensi</div>
                                    <div class="text-muted small mb-0">
                                        Seluruh input dari form berikut akan ikut otomatis dan tidak diacak.
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped combination-fixed-form-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Form</th>
                                                <th class="text-center">Soal Aktif</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${autoIncludedForms.map((form) => `
                                                <tr>
                                                    <td>
                                                        <div class="font-weight-bold">${escapeHtml(form.form_title || '-')}</div>
                                                        <small class="text-muted">${escapeHtml(form.form_code || '-')}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-light border">${Number(form.available_question_count || 0)} soal</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-info">Semua ikut</span>
                                                    </td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        `
                        : '';

                    return `
                        <div class="combination-assessment-card">
                            <div class="combination-assessment-card__header">
                                <div class="d-flex justify-content-between align-items-start flex-wrap">
                                    <div>
                                        <div class="text-primary font-weight-bold mb-1">Assessment ${index + 1}</div>
                                        <div class="h6 mb-1">${escapeHtml(assessment.assessment_title || '-')}</div>
                                        <div class="text-muted small">
                                            ${escapeHtml(assessment.assessment_code || '-')} | ${escapeHtml(assessment.instrument_label || 'Tanpa instrumen')}
                                        </div>
                                    </div>
                                    <div class="text-muted small mt-2 mt-md-0">
                                        ${Number(assessment.total_forms || 0)} form | ${Number(assessment.total_questions || 0)} soal sumber
                                    </div>
                                </div>
                                <div class="combination-assessment-meta">
                                    <span class="badge badge-primary">${competencies.filter((item) => Number(item.available_question_count || 0) > 0).length} kompetensi aktif</span>
                                    <span class="badge badge-light border">${Number(assessment.auto_included_form_count || 0)} form auto</span>
                                    <span class="badge badge-light border">${Number(assessment.auto_included_question_count || 0)} soal auto</span>
                                </div>
                            </div>
                            <div class="combination-assessment-card__body">
                                <div class="table-responsive">
                                    <table class="table table-striped combination-competency-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Kompetensi</th>
                                                <th class="text-center">Pool Form</th>
                                                <th class="text-center">Soal Tersedia</th>
                                                <th>Mode</th>
                                                <th class="text-center">Jumlah / Semua</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${competencyRows}
                                        </tbody>
                                    </table>
                                </div>
                                ${autoFormsHtml}
                            </div>
                        </div>
                    `;
                }).join('');

                bindAssessmentInputs();
                updateSummary();
            }

            function bindAssessmentInputs() {
                document.querySelectorAll('.js-competency-mode').forEach((input) => {
                    input.addEventListener('change', () => {
                        const assessmentId = Number(input.dataset.assessmentId || 0);
                        const competencyKey = String(input.dataset.competencyKey || '');
                        const available = Number(input.dataset.available || 0);
                        const nextMode = input.value === 'all' ? 'all' : 'count';

                        setCompetencyMode(assessmentId, competencyKey, nextMode);

                        if (nextMode === 'all') {
                            setCompetencyCount(assessmentId, competencyKey, available);
                        } else if (getCompetencyCount(assessmentId, competencyKey, available) < 1) {
                            setCompetencyCount(assessmentId, competencyKey, Math.min(10, available));
                        }

                        renderAssessmentPanels();
                    });
                });

                document.querySelectorAll('.js-competency-count').forEach((input) => {
                    input.addEventListener('input', () => {
                        const assessmentId = Number(input.dataset.assessmentId || 0);
                        const competencyKey = String(input.dataset.competencyKey || '');
                        const available = Number(input.dataset.available || 0);
                        let value = Number(input.value || 0);

                        if (value < 1) {
                            value = 1;
                        }

                        if (available > 0 && value > available) {
                            value = available;
                        }

                        input.value = value;
                        setCompetencyMode(assessmentId, competencyKey, 'count');
                        setCompetencyCount(assessmentId, competencyKey, value);
                        updateSummary();
                    });
                });
            }

            function updateSummary() {
                const assessments = getAssessmentsForSelectedKetenagaan();
                const selectedKetenagaan = getSelectedKetenagaan();
                const totalForms = assessments.reduce((total, assessment) => total + Number(assessment.total_forms || 0), 0);
                const sourceQuestionCount = assessments.reduce((total, assessment) => total + Number(assessment.total_questions || 0), 0);
                const autoFormCount = assessments.reduce((total, assessment) => total + Number(assessment.auto_included_form_count || 0), 0);
                const autoQuestionCount = assessments.reduce((total, assessment) => total + Number(assessment.auto_included_question_count || 0), 0);
                const selectedQuestionCount = assessments.reduce((total, assessment) => {
                    ensureAssessmentState(assessment);

                    const assessmentId = Number(assessment.assessment_id || 0);
                    const competencySelectedCount = (assessment.competencies || []).reduce((competencyTotal, competency) => {
                        const available = Number(competency.available_question_count || 0);

                        if (available < 1) {
                            return competencyTotal;
                        }

                        const mode = getCompetencyMode(assessmentId, String(competency.kompetensi || ''), available);

                        if (mode === 'all') {
                            return competencyTotal + available;
                        }

                        return competencyTotal + Number(getCompetencyCount(
                            assessmentId,
                            String(competency.kompetensi || ''),
                            available
                        ) || 0);
                    }, 0);

                    return total + competencySelectedCount + Number(assessment.auto_included_question_count || 0);
                }, 0);
                const activeCompetencyCount = assessments.reduce((total, assessment) => {
                    return total + (assessment.competencies || []).filter((competency) => Number(competency.available_question_count || 0) > 0).length;
                }, 0);

                const summaryKetenagaan = document.getElementById('summary-ketenagaan');
                const summaryAssessments = document.getElementById('summary-assessments');
                const summaryForms = document.getElementById('summary-forms');
                const summarySourceQuestions = document.getElementById('summary-source-questions');
                const summarySelectedQuestions = document.getElementById('summary-selected-questions');
                const summaryNote = document.getElementById('summary-note');
                const currentSourceTitle = document.getElementById('current-source-title');
                const currentSourceDescription = document.getElementById('current-source-description');

                if (summaryKetenagaan) {
                    summaryKetenagaan.textContent = ketenagaanOptions[selectedKetenagaan] || '-';
                }

                if (summaryAssessments) {
                    summaryAssessments.textContent = String(assessments.length);
                }

                if (summaryForms) {
                    summaryForms.textContent = String(totalForms);
                }

                if (summarySourceQuestions) {
                    summarySourceQuestions.textContent = String(sourceQuestionCount);
                }

                if (summarySelectedQuestions) {
                    summarySelectedQuestions.textContent = String(selectedQuestionCount);
                }

                if (summaryNote) {
                    summaryNote.textContent = autoFormCount > 0
                        ? `${activeCompetencyCount} kompetensi dipetakan manual, ${autoFormCount} form tanpa kompetensi ikut otomatis (${autoQuestionCount} soal).`
                        : 'Semua child soal berasal dari pemetaan kompetensi assessment yang dipilih.';
                }

                if (currentSourceTitle) {
                    currentSourceTitle.textContent = assessments.length > 0
                        ? `${assessments.length} assessment sumber untuk ${ketenagaanOptions[selectedKetenagaan] || 'ketenagaan ini'}`
                        : 'Belum ada assessment sumber aktif';
                }

                if (currentSourceDescription) {
                    currentSourceDescription.textContent = assessments.length > 0
                        ? `${activeCompetencyCount} kompetensi aktif dipetakan, ${autoFormCount} form tanpa kompetensi ikut penuh, dan ${sourceQuestionCount} soal sumber tersedia.`
                        : 'Silakan lengkapi assessment aktif pada ketenagaan ini terlebih dahulu.';
                }
            }

            function applyToAllCompetencies() {
                const assessments = getAssessmentsForSelectedKetenagaan();
                const desiredCount = Math.max(Number(applyAllInput ? applyAllInput.value : 10), 1);

                assessments.forEach((assessment) => {
                    ensureAssessmentState(assessment);

                    (assessment.competencies || []).forEach((competency) => {
                        const available = Number(competency.available_question_count || 0);

                        if (available < 1) {
                            return;
                        }

                        const assessmentId = Number(assessment.assessment_id || 0);
                        const competencyKey = String(competency.kompetensi || '');

                        setCompetencyMode(assessmentId, competencyKey, 'count');
                        setCompetencyCount(assessmentId, competencyKey, Math.min(desiredCount, available));
                    });
                });

                renderAssessmentPanels();
            }

            function applyAllModeAll() {
                const assessments = getAssessmentsForSelectedKetenagaan();

                assessments.forEach((assessment) => {
                    ensureAssessmentState(assessment);

                    (assessment.competencies || []).forEach((competency) => {
                        const available = Number(competency.available_question_count || 0);

                        if (available < 1) {
                            return;
                        }

                        const assessmentId = Number(assessment.assessment_id || 0);
                        const competencyKey = String(competency.kompetensi || '');

                        setCompetencyMode(assessmentId, competencyKey, 'all');
                        setCompetencyCount(assessmentId, competencyKey, available);
                    });
                });

                renderAssessmentPanels();
            }

            document.querySelectorAll('input[name="target_ketenagaan"]').forEach((input) => {
                input.addEventListener('change', renderAssessmentPanels);
            });

            if (applyAllButton) {
                applyAllButton.addEventListener('click', applyToAllCompetencies);
            }

            if (applyAllModeAllButton) {
                applyAllModeAllButton.addEventListener('click', applyAllModeAll);
            }

            renderAssessmentPanels();
        })();
    </script>
@endpush
