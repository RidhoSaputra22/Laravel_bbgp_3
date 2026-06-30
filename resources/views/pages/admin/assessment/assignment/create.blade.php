@extends('layouts.app', ['title' => 'Buat Penugasan Assessment'])

@php
    $selectedDurationHours = (int) old('durasi_sesi_jam', $defaultSessionDurationHours);
    $selectedStartTime = old('jam_mulai');
    $selectedTargetKetenagaan = old('target_ketenagaan', '');
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
        .summary-value {
            color: #34395e;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .assignment-ketenagaan-grid {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .assignment-ketenagaan-option {
            position: relative;
        }

        .assignment-ketenagaan-input {
            opacity: 0;
            pointer-events: none;
            position: absolute;
        }

        .assignment-ketenagaan-card {
            align-items: center;
            background: #fff;
            border: 1px solid #dfe7f7;
            border-radius: 0.9rem;
            cursor: pointer;
            display: flex;
            gap: 0.85rem;
            margin-bottom: 0;
            min-height: 94px;
            padding: 1rem 1.1rem;
            transition: all 0.2s ease;
        }

        .assignment-ketenagaan-card:hover {
            border-color: #bccdf5;
            box-shadow: 0 10px 24px rgba(52, 73, 94, 0.08);
            transform: translateY(-1px);
        }

        .assignment-ketenagaan-card__icon {
            align-items: center;
            border-radius: 0.9rem;
            color: #fff;
            display: inline-flex;
            flex: 0 0 46px;
            font-size: 1.1rem;
            height: 46px;
            justify-content: center;
            width: 46px;
        }

        .assignment-ketenagaan-card__title {
            color: #334155;
            display: block;
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .assignment-ketenagaan-card__hint {
            color: #7b8898;
            display: block;
            font-size: 0.8rem;
            margin-top: 0.15rem;
        }

        .assignment-ketenagaan-card--pendidik .assignment-ketenagaan-card__icon {
            background: linear-gradient(135deg, #1174c7, #2f8fe1);
        }

        .assignment-ketenagaan-card--kependidikan .assignment-ketenagaan-card__icon {
            background: linear-gradient(135deg, #0d8b8c, #1fa3a4);
        }

        .assignment-ketenagaan-card--stakeholder .assignment-ketenagaan-card__icon {
            background: linear-gradient(135deg, #e5a100, #f5bc2b);
        }

        .assignment-ketenagaan-input:checked + .assignment-ketenagaan-card {
            border-color: #6777ef;
            box-shadow: 0 14px 28px rgba(103, 119, 239, 0.16);
            transform: translateY(-1px);
        }

        .auto-summary-panel {
            background: #f8fbff;
            border: 1px solid #d7e3f8;
            border-radius: 0.9rem;
            padding: 1rem 1rem 0.75rem;
        }

        .auto-summary-pill {
            background: #eef3ff;
            border-radius: 999px;
            color: #34539d;
            display: inline-flex;
            font-size: 0.78rem;
            font-weight: 600;
            margin: 0 0.45rem 0.45rem 0;
            padding: 0.28rem 0.7rem;
        }

        .auto-summary-table td,
        .auto-summary-table th {
            vertical-align: middle;
        }

        .auto-summary-empty {
            color: #7b8898;
            font-size: 0.9rem;
            padding: 1rem 0;
            text-align: center;
        }

        @media (max-width: 991.98px) {
            .assignment-ketenagaan-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Buat Penugasan Assessment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.assignment.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="section-body">
                @if ($errors->has('assignment'))
                    <div class="alert alert-danger">
                        {{ $errors->first('assignment') }}
                    </div>
                @endif

                <form action="{{ route('assessment.assignment.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Informasi Penugasan</h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-light border mb-4">
                                        Kode penugasan dibuat otomatis. Admin cukup menentukan judul, ketenagaan target,
                                        dan jadwal. Sistem akan mengambil semua form assessment aktif/publish beserta
                                        seluruh user pada ketenagaan yang sama.
                                    </div>

                                    <div class="form-group">
                                        <label>Judul Penugasan <span class="text-danger">*</span></label>
                                        <input type="text" name="judul_penugasan"
                                            class="form-control @error('judul_penugasan') is-invalid @enderror"
                                            value="{{ old('judul_penugasan') }}"
                                            placeholder="Contoh: Assessment Tenaga Pendidik Periode Juli">
                                        @error('judul_penugasan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Ketenagaan Target <span class="text-danger">*</span></label>
                                        <div class="assignment-ketenagaan-grid">
                                            @foreach ($ketenagaanCards as $value => $card)
                                                <div class="assignment-ketenagaan-option">
                                                    <input type="radio" class="assignment-ketenagaan-input"
                                                        id="assignment-ketenagaan-{{ $value }}"
                                                        name="target_ketenagaan" value="{{ $value }}"
                                                        @checked($selectedTargetKetenagaan === $value) required>
                                                    <label for="assignment-ketenagaan-{{ $value }}"
                                                        class="assignment-ketenagaan-card assignment-ketenagaan-card--{{ $card['theme'] }}">
                                                        <span class="assignment-ketenagaan-card__icon">
                                                            <i class="{{ $card['icon'] }}"></i>
                                                        </span>
                                                        <span>
                                                            <span class="assignment-ketenagaan-card__title">{{ $card['label'] }}</span>
                                                            <span class="assignment-ketenagaan-card__hint">
                                                                Semua form + semua user pada ketenagaan ini akan
                                                                otomatis ditugaskan.
                                                            </span>
                                                        </span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="form-text text-muted">
                                            Penugasan tidak perlu lagi memilih form satu-satu atau user satu-satu.
                                        </small>
                                        @error('target_ketenagaan')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="auto-summary-panel mb-4">
                                        <div class="d-flex flex-wrap justify-content-between align-items-start">
                                            <div class="mb-3">
                                                <div class="text-muted small">Ringkasan Otomatis</div>
                                                <div class="font-weight-bold" id="auto-summary-ketenagaan-label">
                                                    Pilih ketenagaan terlebih dahulu
                                                </div>
                                            </div>
                                            <div class="mb-3 text-right">
                                                <span class="auto-summary-pill" id="auto-summary-assessment-count">0 assessment</span>
                                                <span class="auto-summary-pill" id="auto-summary-user-count">0 user</span>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 col-6 mb-3">
                                                <div class="text-muted small">Total Form</div>
                                                <div class="summary-value" id="auto-summary-form-count">0</div>
                                            </div>
                                            <div class="col-md-4 col-6 mb-3">
                                                <div class="text-muted small">Total Pertanyaan</div>
                                                <div class="summary-value" id="auto-summary-field-count">0</div>
                                            </div>
                                            <div class="col-md-4 col-12 mb-3">
                                                <div class="text-muted small">Estimasi Distribusi</div>
                                                <div class="summary-value" id="auto-summary-distribution">-</div>
                                            </div>
                                        </div>

                                        <div class="alert alert-warning d-none" id="auto-summary-warning"></div>

                                        <div class="table-responsive">
                                            <table class="table table-sm auto-summary-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Kode</th>
                                                        <th>Judul Assessment</th>
                                                        <th>Struktur</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="auto-summary-assessment-list">
                                                    <tr>
                                                        <td colspan="3" class="auto-summary-empty">
                                                            Daftar assessment otomatis akan tampil setelah ketenagaan dipilih.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Jam Mulai Sesi Awal</label>
                                                <input type="time" name="jam_mulai" id="jam_mulai"
                                                    class="form-control @error('jam_mulai') is-invalid @enderror"
                                                    value="{{ $selectedStartTime }}">
                                                <small class="text-muted">
                                                    Sesi 1 dimulai pada jam ini. Sesi berikutnya otomatis berurutan
                                                    mengikuti durasi per sesi.
                                                </small>
                                                @error('jam_mulai')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Tanggal Mulai</label>
                                                <input type="date" name="tanggal_mulai"
                                                    class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                                    value="{{ old('tanggal_mulai') }}">
                                                @error('tanggal_mulai')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Tanggal Selesai</label>
                                                <input type="date" name="tanggal_selesai"
                                                    class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                                    value="{{ old('tanggal_selesai') }}">
                                                @error('tanggal_selesai')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Durasi Sesi Assessment <span class="text-danger">*</span></label>
                                                <select name="durasi_sesi_jam" id="durasi_sesi_jam"
                                                    class="form-control @error('durasi_sesi_jam') is-invalid @enderror">
                                                    @foreach ($sessionDurationOptions as $durationHour)
                                                        <option value="{{ $durationHour }}"
                                                            @selected($selectedDurationHours === (int) $durationHour)>
                                                            {{ $durationHour }} jam
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('durasi_sesi_jam')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Kapasitas Peserta Per Sesi</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $sessionCapacity }} peserta" readonly>
                                                <small class="text-muted">
                                                    Sistem otomatis membagi {{ $sessionCapacity }} peserta untuk setiap
                                                    sesi assessment.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Deskripsi Penugasan</label>
                                        <textarea name="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror"
                                            placeholder="Catatan, instruksi, atau konteks penugasan untuk admin.">{{ old('deskripsi') }}</textarea>
                                        @error('deskripsi')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="sticky-top">
                                <div class="card card-body mb-4">
                                    <h6 class="text-primary mb-3">Ringkasan Penugasan</h6>
                                    <div class="mb-3">
                                        <div class="text-muted small">Kode Penugasan</div>
                                        <div class="summary-value">Otomatis saat simpan</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small">Ketenagaan Dipilih</div>
                                        <div class="summary-value" id="summary-ketenagaan">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small">Total Assessment</div>
                                        <div class="summary-value" id="summary-assessment-count">0</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="text-muted small">Total Form</div>
                                            <div class="summary-value" id="summary-forms">0</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted small">Total Pertanyaan</div>
                                            <div class="summary-value" id="summary-fields">0</div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="text-muted small">Target User</div>
                                            <div class="summary-value" id="summary-user-count">0</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted small">Kapasitas/Sesi</div>
                                            <div class="summary-value" id="summary-session-capacity">
                                                {{ $sessionCapacity }} peserta
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <div class="text-muted small">Durasi/Sesi</div>
                                            <div class="summary-value" id="summary-session-duration">
                                                {{ $selectedDurationHours }} jam
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted small">Estimasi Sesi</div>
                                            <div class="summary-value" id="summary-total-sessions">0</div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <div class="text-muted small">Jam Sesi Awal</div>
                                            <div class="summary-value" id="summary-session-start-time">
                                                {{ $selectedStartTime ?: '-' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="text-muted small">Metode Distribusi</div>
                                        <div class="summary-value" id="summary-distribution-method">-</div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h4>Aksi</h4>
                                    </div>
                                    <div class="card-body">
                                        <button type="submit" class="btn btn-primary btn-block" id="assignment-submit-button">
                                            <i class="fas fa-paper-plane"></i> Simpan Penugasan
                                        </button>
                                        <a href="{{ route('assessment.assignment.index') }}" class="btn btn-light btn-block">
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
            const ketenagaanSummaries = @json($ketenagaanSummaries);
            const sessionCapacity = {{ $sessionCapacity }};
            const defaultDurationHours = {{ $defaultSessionDurationHours }};
            const batchThreshold = {{ $batchThreshold }};

            function getSelectedTargetKetenagaan() {
                const selected = document.querySelector('input[name="target_ketenagaan"]:checked');

                return selected ? selected.value : '';
            }

            function getSelectedSummary() {
                const target = getSelectedTargetKetenagaan();

                return target && ketenagaanSummaries[target] ? ketenagaanSummaries[target] : null;
            }

            function getSelectedDurationHours() {
                const durationSelect = document.getElementById('durasi_sesi_jam');

                return Number((durationSelect ? durationSelect.value : '') || defaultDurationHours);
            }

            function getSelectedStartTime() {
                const startTimeInput = document.getElementById('jam_mulai');

                return (startTimeInput ? startTimeInput.value : '') || '-';
            }

            function renderAssessmentList(summary) {
                const tbody = document.getElementById('auto-summary-assessment-list');

                if (!tbody) {
                    return;
                }

                const items = summary && Array.isArray(summary.assessment_items) ? summary.assessment_items : [];

                if (items.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="3" class="auto-summary-empty">
                                Belum ada assessment otomatis yang bisa dipakai untuk ketenagaan ini.
                            </td>
                        </tr>
                    `;

                    return;
                }

                tbody.innerHTML = items.map((item) => {
                    return `
                        <tr>
                            <td class="font-weight-bold">${item.kode || '-'}</td>
                            <td>
                                <div class="font-weight-600">${item.judul || '-'}</div>
                                <small class="text-muted">${item.status || '-'}</small>
                            </td>
                            <td>${Number(item.forms || 0)} form / ${Number(item.fields || 0)} pertanyaan</td>
                        </tr>
                    `;
                }).join('');
            }

            function updateAutoSummaryPanel() {
                const summary = getSelectedSummary();
                const labelNode = document.getElementById('auto-summary-ketenagaan-label');
                const assessmentCountNode = document.getElementById('auto-summary-assessment-count');
                const userCountNode = document.getElementById('auto-summary-user-count');
                const formCountNode = document.getElementById('auto-summary-form-count');
                const fieldCountNode = document.getElementById('auto-summary-field-count');
                const distributionNode = document.getElementById('auto-summary-distribution');
                const warningNode = document.getElementById('auto-summary-warning');
                const submitButton = document.getElementById('assignment-submit-button');

                const assessmentCount = summary ? Number(summary.assessment_count || 0) : 0;
                const userCount = summary ? Number(summary.user_count || 0) : 0;
                const distributionMethod = userCount === 0 ? '-' : (userCount > batchThreshold ? 'Batch Job' : 'Langsung');

                if (labelNode) {
                    labelNode.textContent = summary ? summary.label : 'Pilih ketenagaan terlebih dahulu';
                }

                if (assessmentCountNode) {
                    assessmentCountNode.textContent = assessmentCount + ' assessment';
                }

                if (userCountNode) {
                    userCountNode.textContent = userCount + ' user';
                }

                if (formCountNode) {
                    formCountNode.textContent = summary ? Number(summary.form_count || 0) : 0;
                }

                if (fieldCountNode) {
                    fieldCountNode.textContent = summary ? Number(summary.field_count || 0) : 0;
                }

                if (distributionNode) {
                    distributionNode.textContent = distributionMethod;
                }

                if (warningNode) {
                    const warningMessages = [];

                    if (summary && assessmentCount === 0) {
                        warningMessages.push('Belum ada assessment aktif/publish untuk ketenagaan ini.');
                    }

                    if (summary && userCount === 0) {
                        warningMessages.push('Belum ada user/peserta pada ketenagaan ini.');
                    }

                    warningNode.classList.toggle('d-none', warningMessages.length === 0);
                    warningNode.textContent = warningMessages.join(' ');
                }

                if (submitButton) {
                    submitButton.disabled = !summary || assessmentCount === 0 || userCount === 0;
                }

                renderAssessmentList(summary);
            }

            function updateSidebarSummary() {
                const summary = getSelectedSummary();
                const assessmentCount = summary ? Number(summary.assessment_count || 0) : 0;
                const formCount = summary ? Number(summary.form_count || 0) : 0;
                const fieldCount = summary ? Number(summary.field_count || 0) : 0;
                const userCount = summary ? Number(summary.user_count || 0) : 0;
                const totalSessions = userCount > 0 ? Math.ceil(userCount / sessionCapacity) : 0;
                const durationHours = getSelectedDurationHours();
                const distributionMethod = userCount === 0 ? '-' : (userCount > batchThreshold ? 'Batch Job' : 'Langsung');

                const summaryKetenagaan = document.getElementById('summary-ketenagaan');
                const summaryAssessmentCount = document.getElementById('summary-assessment-count');
                const summaryForms = document.getElementById('summary-forms');
                const summaryFields = document.getElementById('summary-fields');
                const summaryUserCount = document.getElementById('summary-user-count');
                const summarySessionCapacity = document.getElementById('summary-session-capacity');
                const summarySessionDuration = document.getElementById('summary-session-duration');
                const summaryTotalSessions = document.getElementById('summary-total-sessions');
                const summarySessionStartTime = document.getElementById('summary-session-start-time');
                const summaryDistributionMethod = document.getElementById('summary-distribution-method');

                if (summaryKetenagaan) {
                    summaryKetenagaan.textContent = summary ? summary.label : '-';
                }

                if (summaryAssessmentCount) {
                    summaryAssessmentCount.textContent = assessmentCount;
                }

                if (summaryForms) {
                    summaryForms.textContent = formCount;
                }

                if (summaryFields) {
                    summaryFields.textContent = fieldCount;
                }

                if (summaryUserCount) {
                    summaryUserCount.textContent = userCount;
                }

                if (summarySessionCapacity) {
                    summarySessionCapacity.textContent = sessionCapacity + ' peserta';
                }

                if (summarySessionDuration) {
                    summarySessionDuration.textContent = durationHours + ' jam';
                }

                if (summaryTotalSessions) {
                    summaryTotalSessions.textContent = totalSessions;
                }

                if (summarySessionStartTime) {
                    summarySessionStartTime.textContent = getSelectedStartTime();
                }

                if (summaryDistributionMethod) {
                    summaryDistributionMethod.textContent = distributionMethod;
                }
            }

            function refreshSummaries() {
                updateAutoSummaryPanel();
                updateSidebarSummary();
            }

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('input[name="target_ketenagaan"]').forEach((input) => {
                    input.addEventListener('change', refreshSummaries);
                });

                const durationSelect = document.getElementById('durasi_sesi_jam');
                const startTimeInput = document.getElementById('jam_mulai');

                if (durationSelect) {
                    durationSelect.addEventListener('change', updateSidebarSummary);
                }

                if (startTimeInput) {
                    startTimeInput.addEventListener('change', updateSidebarSummary);
                }

                refreshSummaries();
            });
        })();
    </script>
@endpush
