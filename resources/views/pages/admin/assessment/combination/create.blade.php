@extends('layouts.app', ['title' => 'Buat Kombinasi Soal'])

@php
    $selectedTargetKetenagaan = old(
        'target_ketenagaan',
        \App\Enum\AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
    );
    $initialTakeCounts = collect((array) old('form_take_counts', []))
        ->mapWithKeys(fn ($count, $formId) => [(int) $formId => max((int) $count, 0)])
        ->all();
    $formTakeErrorMap = collect($errors->getMessages())
        ->mapWithKeys(function ($messages, $key) {
            if (preg_match('/^form_take_counts\.(\d+)$/', $key, $matches) !== 1) {
                return [];
            }

            return [
                (int) $matches[1] => $messages[0] ?? '',
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

        .combination-form-table td,
        .combination-form-table th {
            vertical-align: middle;
        }

        .combination-form-table .form-control {
            min-width: 110px;
        }

        .combination-empty-state {
            color: #7b8898;
            padding: 2rem 1rem;
            text-align: center;
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

                @if ($errors->has('form_take_counts'))
                    <div class="alert alert-danger">
                        {{ $errors->first('form_take_counts') }}
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
                                        Sistem akan mengambil soal acak dari setiap form aktif sesuai ketenagaan yang dipilih.
                                        Jumlah soal per form disimpan sebagai child soal pada kombinasi ini, lalu penugasan
                                        assessment dapat merujuk ke kombinasi tersebut.
                                    </div>

                                    <div class="form-group">
                                        <label>Judul Kombinasi <span class="text-danger">*</span></label>
                                        <input type="text" name="judul"
                                            class="form-control @error('judul') is-invalid @enderror"
                                            value="{{ old('judul') }}"
                                            placeholder="Contoh: Kombinasi Soal Tenaga Pendidik Juli 2026">
                                        @error('judul')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                                                Semua form aktif pada ketenagaan ini akan dimunculkan
                                                                untuk diatur jumlah soal acaknya.
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

                                    <div class="form-group">
                                        <label>Deskripsi</label>
                                        <textarea name="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror"
                                            placeholder="Catatan tambahan untuk admin.">{{ old('deskripsi') }}</textarea>
                                        @error('deskripsi')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center w-100">
                                        <h4 class="mb-0">Jumlah Soal Per Form</h4>
                                        <div class="d-flex align-items-center">
                                            <input type="number" min="1" value="1"
                                                class="form-control form-control-sm mr-2" id="apply-all-count"
                                                style="width: 90px;">
                                            <button type="button" class="btn btn-sm btn-light" id="apply-all-button">
                                                Terapkan ke Semua Form
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped combination-form-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Assessment</th>
                                                    <th>Form</th>
                                                    <th>Kompetensi / Indikator</th>
                                                    <th class="text-center">Soal Aktif</th>
                                                    <th class="text-center">Ambil Acak</th>
                                                </tr>
                                            </thead>
                                            <tbody id="combination-form-rows">
                                                <tr>
                                                    <td colspan="5" class="combination-empty-state">
                                                        Memuat data form...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
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
                                            Setiap form minimal mengambil 1 soal dan tidak boleh melebihi soal aktif yang tersedia.
                                        </div>
                                    </div>
                                </div>

                                <div class="combination-summary-panel mb-4">
                                    <div class="text-muted small mb-2">Info Penting</div>
                                    <div class="font-weight-bold mb-2" id="current-source-title">
                                        Pilih ketenagaan untuk melihat sumber form.
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
            const formCatalogByKetenagaan = @json($formCatalogByKetenagaan);
            const ketenagaanOptions = @json($ketenagaanOptions);
            const initialTakeCounts = @json($initialTakeCounts);
            const formTakeErrorMap = @json($formTakeErrorMap);
            const formRowsNode = document.getElementById('combination-form-rows');
            const applyAllInput = document.getElementById('apply-all-count');
            const applyAllButton = document.getElementById('apply-all-button');
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

            function getFormsForSelectedKetenagaan() {
                const target = getSelectedKetenagaan();

                return target && Array.isArray(formCatalogByKetenagaan[target]) ? formCatalogByKetenagaan[target] : [];
            }

            function getTakeCount(form) {
                const formId = Number(form.form_id || 0);
                const currentValue = Number(takeCountsState[formId] || 0);

                if (currentValue > 0) {
                    return Math.min(currentValue, Number(form.available_question_count || 0));
                }

                return Math.min(1, Number(form.available_question_count || 0)) || 1;
            }

            function setTakeCount(formId, value) {
                takeCountsState[Number(formId)] = Math.max(Number(value || 0), 0);
            }

            function renderFormRows() {
                const forms = getFormsForSelectedKetenagaan();

                if (!formRowsNode) {
                    return;
                }

                if (forms.length < 1) {
                    formRowsNode.innerHTML = `
                        <tr>
                            <td colspan="5" class="combination-empty-state">
                                Belum ada form aktif yang tersedia pada ketenagaan ini.
                            </td>
                        </tr>
                    `;
                    updateSummary();

                    return;
                }

                formRowsNode.innerHTML = forms.map((form) => {
                    const formId = Number(form.form_id || 0);
                    const available = Number(form.available_question_count || 0);
                    const currentValue = getTakeCount(form);
                    const competencyText = [form.kompetensi_label, form.indikator_kode ? 'Indikator ' + form.indikator_kode : null]
                        .filter(Boolean)
                        .join(' / ');
                    const errorText = formTakeErrorMap[formId] || '';

                    setTakeCount(formId, currentValue);

                    return `
                        <tr>
                            <td>
                                <div class="font-weight-bold">${escapeHtml(form.assessment_title || '-')}</div>
                                <small class="text-muted">${escapeHtml(form.assessment_code || '-')} | ${escapeHtml(form.instrument_label || 'Tanpa instrumen')}</small>
                            </td>
                            <td>
                                <div class="font-weight-bold">${escapeHtml(form.form_title || '-')}</div>
                                <small class="text-muted">${escapeHtml(form.form_code || '-')}</small>
                            </td>
                            <td>
                                <div>${escapeHtml(competencyText || 'Belum ada kompetensi/indikator')}</div>
                                <small class="text-muted">${form.is_scoreable ? 'Masuk penilaian' : 'Hanya pengumpulan data'}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border">${available} soal</span>
                            </td>
                            <td class="text-center">
                                <input
                                    type="number"
                                    min="1"
                                    max="${available}"
                                    step="1"
                                    name="form_take_counts[${formId}]"
                                    value="${currentValue}"
                                    class="form-control text-center js-form-take-count ${errorText ? 'is-invalid' : ''}"
                                    data-form-id="${formId}"
                                    data-max="${available}"
                                >
                                ${errorText ? `<div class="invalid-feedback d-block text-left">${escapeHtml(errorText)}</div>` : ''}
                            </td>
                        </tr>
                    `;
                }).join('');

                bindTakeCountInputs();
                updateSummary();
            }

            function bindTakeCountInputs() {
                document.querySelectorAll('.js-form-take-count').forEach((input) => {
                    input.addEventListener('input', () => {
                        const formId = Number(input.dataset.formId || 0);
                        const max = Number(input.dataset.max || 0);
                        let value = Number(input.value || 0);

                        if (value < 1) {
                            value = 1;
                        }

                        if (max > 0 && value > max) {
                            value = max;
                        }

                        input.value = value;
                        setTakeCount(formId, value);
                        updateSummary();
                    });
                });
            }

            function updateSummary() {
                const forms = getFormsForSelectedKetenagaan();
                const uniqueAssessmentIds = Array.from(new Set(forms.map((form) => Number(form.assessment_id || 0)).filter((id) => id > 0)));
                const sourceQuestionCount = forms.reduce((total, form) => total + Number(form.available_question_count || 0), 0);
                const selectedQuestionCount = forms.reduce((total, form) => total + Number(getTakeCount(form) || 0), 0);
                const selectedKetenagaan = getSelectedKetenagaan();

                const summaryKetenagaan = document.getElementById('summary-ketenagaan');
                const summaryAssessments = document.getElementById('summary-assessments');
                const summaryForms = document.getElementById('summary-forms');
                const summarySourceQuestions = document.getElementById('summary-source-questions');
                const summarySelectedQuestions = document.getElementById('summary-selected-questions');
                const currentSourceTitle = document.getElementById('current-source-title');
                const currentSourceDescription = document.getElementById('current-source-description');

                if (summaryKetenagaan) {
                    summaryKetenagaan.textContent = ketenagaanOptions[selectedKetenagaan] || '-';
                }

                if (summaryAssessments) {
                    summaryAssessments.textContent = String(uniqueAssessmentIds.length);
                }

                if (summaryForms) {
                    summaryForms.textContent = String(forms.length);
                }

                if (summarySourceQuestions) {
                    summarySourceQuestions.textContent = String(sourceQuestionCount);
                }

                if (summarySelectedQuestions) {
                    summarySelectedQuestions.textContent = String(selectedQuestionCount);
                }

                if (currentSourceTitle) {
                    currentSourceTitle.textContent = forms.length > 0
                        ? 'Sumber form untuk ' + (ketenagaanOptions[selectedKetenagaan] || 'ketenagaan ini')
                        : 'Belum ada sumber form aktif';
                }

                if (currentSourceDescription) {
                    currentSourceDescription.textContent = forms.length > 0
                        ? uniqueAssessmentIds.length + ' assessment sumber, ' + forms.length + ' form aktif, dan ' + sourceQuestionCount + ' soal siap diacak.'
                        : 'Silakan lengkapi form assessment aktif pada ketenagaan ini terlebih dahulu.';
                }
            }

            function applyToAllForms() {
                const forms = getFormsForSelectedKetenagaan();
                const desiredCount = Math.max(Number(applyAllInput ? applyAllInput.value : 1), 1);

                forms.forEach((form) => {
                    const max = Number(form.available_question_count || 0);
                    setTakeCount(Number(form.form_id || 0), Math.min(desiredCount, max));
                });

                renderFormRows();
            }

            document.querySelectorAll('input[name="target_ketenagaan"]').forEach((input) => {
                input.addEventListener('change', renderFormRows);
            });

            if (applyAllButton) {
                applyAllButton.addEventListener('click', applyToAllForms);
            }

            renderFormRows();
        })();
    </script>
@endpush
