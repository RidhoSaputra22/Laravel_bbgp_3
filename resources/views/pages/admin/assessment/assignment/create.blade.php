@extends('layouts.app', ['title' => 'Buat Penugasan Assessment'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
@endpush

@section('content')
    @php
        $oldAssessmentIds = collect(old('assessment_ids', old('assessment_id') ? [old('assessment_id')] : []))
            ->map(fn ($id) => (string) $id)
            ->all();
        $selectedDurationHours = (int) old('durasi_sesi_jam', $defaultSessionDurationHours);
        $selectedStartTime = old('jam_mulai');
        $initialGuruSelectionState = $initialGuruSelectionState ?? [
            'mode' => 'manual',
            'scope' => ['q' => '', 'filters' => []],
            'excludedIds' => [],
            'totalMatched' => 0,
        ];
        $oldGuruKetenagaan = old(
            'guru_filter_eksternal_jabatan',
            data_get($initialGuruSelectionState, 'scope.filters.eksternal_jabatan', '')
        );
        $oldGuruJabatan = old(
            'guru_filter_jenis_jabatan',
            data_get($initialGuruSelectionState, 'scope.filters.jenis_jabatan', '')
        );
        $initialGuruSearchValue = data_get($initialGuruSelectionState, 'scope.q', '');
        $guruKetenagaanOptions = collect($guruFilterOptions['ketenagaan'] ?? [])->values()->all();
        $guruJabatanOptionsByKetenagaan = collect($guruFilterOptions['jabatan_by_ketenagaan'] ?? [])
            ->map(fn ($options) => collect($options)->values()->all())
            ->all();
        $initialGuruJabatanOptions = collect($guruJabatanOptionsByKetenagaan[$oldGuruKetenagaan] ?? [])
            ->when(
                $oldGuruKetenagaan === '',
                fn ($collection) => collect($guruJabatanOptionsByKetenagaan)
                    ->flatten()
                    ->merge($collection)
                    ->filter(fn ($value) => filled($value))
                    ->unique()
                    ->values()
            )
            ->when(
                filled($oldGuruJabatan),
                fn ($collection) => $collection->push($oldGuruJabatan)->unique()->values()
            )
            ->all();

        $assessmentTableItems = $assessmentList
            ->map(function ($assessment) {
                $totalForms = (int) ($assessment->forms_count ?? 0);
                $totalFields = (int) ($assessment->fields_count ?? 0);

                return [
                    'id' => (string) $assessment->id,
                    'label' => $assessment->judul,
                    'description' => $assessment->kode_assessment.' | '.ucfirst($assessment->status),
                    'cells' => [
                        $assessment->kode_assessment,
                        $assessment->judul,
                        ucfirst($assessment->status),
                        $totalForms.' form / '.$totalFields.' pertanyaan',
                    ],
                    'payload' => [
                        'kode' => $assessment->kode_assessment,
                        'judul' => $assessment->judul,
                        'status' => ucfirst($assessment->status),
                        'forms' => $totalForms,
                        'fields' => $totalFields,
                    ],
                ];
            })
            ->all();
    @endphp

    @push('styles')
        <style>
            .summary-value {
                font-size: 1.2rem;
                font-weight: 700;
                color: #34395e;
            }

            .assignment-select-note {
                font-size: 0.9rem;
            }
        </style>
    @endpush

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

                    <div class="row ">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Informasi Penugasan</h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-light border mb-4">
                                        Kode penugasan tidak perlu diisi lagi. Sistem akan membuat kode otomatis saat
                                        penugasan disimpan.
                                    </div>

                                    <div class="form-group">
                                        <label>Judul Penugasan <span class="text-danger">*</span></label>
                                        <input type="text" name="judul_penugasan"
                                            class="form-control @error('judul_penugasan') is-invalid @enderror"
                                            value="{{ old('judul_penugasan') }}"
                                            placeholder="Contoh: Penugasan Monitoring Guru GP Angkatan 1">
                                        @error('judul_penugasan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Form Assessment <span class="text-danger">*</span></label>
                                        <x-multiple-choice-table id="assessment-selector" name="assessment_ids"
                                            :headers="['Kode', 'Judul', 'Status', 'Struktur']"
                                            :items="$assessmentTableItems" :selected="$oldAssessmentIds"
                                            description="Pilih satu atau banyak form assessment. Gunakan pencarian untuk mempercepat seleksi."
                                            search-placeholder="Cari kode atau judul assessment..."
                                            empty-message="Belum ada form assessment aktif yang bisa dipilih."
                                            selected-title="Form Assessment Terpilih" />
                                        @error('assessment_ids')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('assessment_ids.*')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
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
                                                <label>Kapasitas Guru Per Sesi</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $sessionCapacity }} guru" readonly>
                                                <small class="text-muted">
                                                    Sistem otomatis membagi guru per {{ $sessionCapacity }} orang untuk
                                                    setiap sesi.
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

                            <div class="card">
                                <div class="card-header">
                                    <h4>Target Guru</h4>
                                </div>
                                <div class="card-body">
                                    <p class="mb-3 text-muted">
                                        Data guru dimuat per halaman agar tidak semua data terpanggil sekaligus. Pilihan
                                        yang sudah dicentang akan tetap tersimpan saat pindah halaman atau mencari data
                                        lain. Jika jumlah target lebih dari {{ $batchThreshold }} orang, distribusi
                                        otomatis diproses menggunakan batch job.
                                    </p>

                                    <x-multiple-choice-table id="guru-selector" name="guru_ids"
                                        :headers="['Nama Guru', 'Email', 'Instansi', 'Kabupaten', 'Verifikasi']"
                                        :items="[]" :selected="$selectedGuruIds"
                                        :initial-selected-items="$selectedGuruItems"
                                        :ajax-url="route('assessment.assignment.guru-options')"
                                        description="Gunakan pencarian untuk memfilter data guru. Tabel akan memuat data per halaman dan tetap mengingat semua guru yang sudah dipilih."
                                        search-placeholder="Cari nama, email, instansi, atau kabupaten guru..."
                                        empty-message="Belum ada data guru yang bisa dipilih."
                                        selected-title="Guru Terpilih" />
                                    <div class="assignment-select-note text-muted mt-2" id="guru-selection-caption">
                                        Belum ada guru dipilih.
                                    </div>
                                    @error('guru_ids')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('guru_ids.*')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4  ">
                            <div class="sticky-top    ">
                                 <div class="card card-body mb-4 sticky-top">
                                <h6 class="text-primary mb-3">Ringkasan Penugasan</h6>
                                <div class="mb-3">
                                    <div class="text-muted small">Kode Penugasan</div>
                                    <div class="summary-value">Otomatis saat simpan</div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small">Total Assessment Dipilih</div>
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
                                        <div class="text-muted small">Target Guru</div>
                                        <div class="summary-value" id="summary-guru-count">0</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small">Kapasitas/Sesi</div>
                                        <div class="summary-value" id="summary-session-capacity">
                                            {{ $sessionCapacity }} guru
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
                                    <button type="submit" class="btn btn-primary btn-block">
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
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>

    <script>
        (() => {
            const sessionCapacity = {{ $sessionCapacity }};
            const defaultDurationHours = {{ $defaultSessionDurationHours }};
            const batchThreshold = {{ $batchThreshold }};
            const guruJabatanOptionsByKetenagaan = @json($guruJabatanOptionsByKetenagaan);
            let selectedAssessments = [];
            let selectedGurus = [];

            function getSelectedDurationHours() {
                const durationSelect = document.getElementById('durasi_sesi_jam');

                return Number((durationSelect ? durationSelect.value : '') || defaultDurationHours);
            }

            function getSelectedStartTime() {
                const startTimeInput = document.getElementById('jam_mulai');

                return (startTimeInput ? startTimeInput.value : '') || '-';
            }

            function updateAssessmentSummary() {
                const summaryCount = document.getElementById('summary-assessment-count');
                const summaryForms = document.getElementById('summary-forms');
                const summaryFields = document.getElementById('summary-fields');

                const totalForms = selectedAssessments.reduce((sum, item) => {
                    const payload = item.payload || {};

                    return sum + Number(payload.forms || 0);
                }, 0);
                const totalFields = selectedAssessments.reduce((sum, item) => {
                    const payload = item.payload || {};

                    return sum + Number(payload.fields || 0);
                }, 0);

                if (summaryCount) {
                    summaryCount.textContent = selectedAssessments.length;
                }

                if (summaryForms) {
                    summaryForms.textContent = totalForms;
                }

                if (summaryFields) {
                    summaryFields.textContent = totalFields;
                }
            }

            function updateGuruSummary() {
                const totalGuru = Number(selectedGurus.count || 0);
                const totalSessions = totalGuru > 0 ? Math.ceil(totalGuru / sessionCapacity) : 0;
                const durationHours = getSelectedDurationHours();
                const distributionMethod = totalGuru === 0 ? '-' : (totalGuru > batchThreshold ? 'Batch Job' : 'Langsung');

                const summaryGuruCount = document.getElementById('summary-guru-count');
                const summarySessionCapacity = document.getElementById('summary-session-capacity');
                const summarySessionDuration = document.getElementById('summary-session-duration');
                const summaryTotalSessions = document.getElementById('summary-total-sessions');
                const summarySessionStartTime = document.getElementById('summary-session-start-time');
                const summaryDistributionMethod = document.getElementById('summary-distribution-method');

                if (summaryGuruCount) {
                    summaryGuruCount.textContent = totalGuru;
                }

                if (summarySessionCapacity) {
                    summarySessionCapacity.textContent = sessionCapacity + ' guru';
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

            function updateGuruCaption() {
                const caption = document.getElementById('guru-selection-caption');

                if (!caption) {
                    return;
                }

                if (selectedGurus.length === 0) {
                    caption.textContent = 'Belum ada guru dipilih.';

                    return;
                }

                const previewNames = selectedGurus.slice(0, 3).map((guru) => guru.text || guru.id);
                const extraCount = selectedGurus.length - previewNames.length;
                let message = previewNames.join(', ');

                if (extraCount > 0) {
                    message += ' dan ' + extraCount + ' guru lainnya';
                }

                caption.textContent = selectedGurus.length + ' guru dipilih: ' + message + '.';
            }

            document.addEventListener('multiple-choice-table:change', function(event) {
                if (event.detail.tableId === 'assessment-selector') {
                    selectedAssessments = event.detail.selectedItems || [];
                    updateAssessmentSummary();
                }

                if (event.detail.tableId === 'guru-selector') {
                    selectedGurus = (event.detail.selectedItems || []).map((item) => ({
                        id: String(item.id),
                        text: item.label || item.id || '',
                    }));
                    updateGuruCaption();
                    updateGuruSummary();
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                const durationSelect = document.getElementById('durasi_sesi_jam');
                const startTimeInput = document.getElementById('jam_mulai');
                const ketenagaanSelect = document.getElementById('guru-filter-eksternal-jabatan');
                const jabatanSelect = document.getElementById('guru-filter-jenis-jabatan');

                if (ketenagaanSelect && jabatanSelect) {
                    populateGuruJabatanOptions(
                        ketenagaanSelect.value || '',
                        jabatanSelect.dataset.selectedValue || jabatanSelect.value || ''
                    );

                    ketenagaanSelect.addEventListener('change', function() {
                        populateGuruJabatanOptions(this.value || '', jabatanSelect.value || '');
                        refreshGuruSelector();
                    });

                    jabatanSelect.addEventListener('change', function() {
                        refreshGuruSelector();
                    });
                }

                if (durationSelect) {
                    durationSelect.addEventListener('change', function() {
                        updateGuruSummary();
                    });
                }

                if (startTimeInput) {
                    startTimeInput.addEventListener('change', function() {
                        updateGuruSummary();
                    });
                }

                updateAssessmentSummary();
                updateGuruCaption();
                updateGuruSummary();
            });
        })();
    </script>
@endpush
