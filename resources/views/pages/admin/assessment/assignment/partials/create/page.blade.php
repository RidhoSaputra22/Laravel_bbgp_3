@extends('layouts.app', ['title' => $pageTitle])

@php
    $selectedDurationHours = (int) old(
        'durasi_sesi_jam',
        $assignment?->durasi_sesi_jam ?? $defaultSessionDurationHours,
    );
    $resolvedSecurityConfig = \App\Support\Assessment\AssessmentSecurityConfig::normalize($assignment?->security_config ?? []);
    $selectedSecurityEnabled = filter_var(
        old('security_enabled', $resolvedSecurityConfig['enabled'] ? '1' : '0'),
        FILTER_VALIDATE_BOOLEAN,
        FILTER_NULL_ON_FAILURE,
    );
    $selectedSecurityEnabled = $selectedSecurityEnabled ?? $resolvedSecurityConfig['enabled'];
    $selectedSecurityRequireFullscreen = filter_var(
        old('security_require_fullscreen', $resolvedSecurityConfig['require_fullscreen'] ? '1' : '0'),
        FILTER_VALIDATE_BOOLEAN,
        FILTER_NULL_ON_FAILURE,
    );
    $selectedSecurityRequireFullscreen = $selectedSecurityRequireFullscreen ?? $resolvedSecurityConfig['require_fullscreen'];
    $selectedSecurityMaxSeriousViolations = (int) old(
        'security_max_serious_violations',
        $resolvedSecurityConfig['max_serious_violations'],
    );
    $selectedSecurityTemporaryLockSeconds = (int) old(
        'security_temporary_lock_seconds',
        $resolvedSecurityConfig['temporary_lock_seconds'],
    );
    $selectedSecurityFullscreenGraceSeconds = (int) old(
        'security_fullscreen_grace_seconds',
        $resolvedSecurityConfig['fullscreen_grace_seconds'],
    );
    $selectedStartTime = old('jam_mulai', $assignment?->jam_mulai_label);
    $selectedJudul = old('judul_penugasan', $assignment?->judul_penugasan);
    $selectedStartDate = old('tanggal_mulai', $assignment?->tanggal_mulai?->format('Y-m-d'));
    $selectedEndDate = old('tanggal_selesai', $assignment?->tanggal_selesai?->format('Y-m-d'));
    $selectedDeskripsi = old('deskripsi', $assignment?->deskripsi);
    $selectedTargetKetenagaan = old(
        'target_ketenagaan',
        $assignment?->target_ketenagaan ?? \App\Enum\AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
    );
    $currentCombinationOptions = collect($combinationOptionsByKetenagaan[$selectedTargetKetenagaan] ?? [])
        ->values()
        ->all();
    $selectedTargetJabatan = collect((array) old('target_jabatan', $assignment?->target_jabatan ?? []))
        ->filter(fn ($jabatan) => filled($jabatan))
        ->map(fn ($jabatan) => trim((string) $jabatan))
        ->filter(fn ($jabatan) => $jabatan !== '')
        ->values()
        ->all();
    $selectedTargetKabupaten = collect((array) old('target_kabupaten', $assignment?->target_kabupaten ?? []))
        ->filter(fn ($kabupaten) => filled($kabupaten))
        ->map(fn ($kabupaten) => trim((string) $kabupaten))
        ->filter(fn ($kabupaten) => $kabupaten !== '')
        ->values()
        ->all();
    $selectedTargetSatuanPendidikan = collect((array) old('target_satuan_pendidikan', $assignment?->target_satuan_pendidikan ?? []))
        ->filter(fn ($selectionKey) => filled($selectionKey))
        ->map(fn ($selectionKey) => trim((string) $selectionKey))
        ->filter(fn ($selectionKey) => $selectionKey !== '')
        ->values()
        ->all();
    $currentJabatanItems = collect($jabatanOptionsByKetenagaan[$selectedTargetKetenagaan] ?? [])->values()->all();
    $currentSelectedJabatanItems = collect($currentJabatanItems)
        ->filter(fn ($item) => in_array((string) data_get($item, 'id'), $selectedTargetJabatan, true))
        ->values()
        ->all();
    $resolveKabupatenItems = function (array $items, array $selectedJabatan) {
        if ($selectedJabatan === []) {
            return [];
        }

        return collect($items)
            ->map(function ($item) use ($selectedJabatan) {
                $itemPayload = (array) data_get($item, 'payload', []);
                $countsByJabatan = collect((array) data_get($itemPayload, 'counts_by_jabatan', []));
                $selectedUserCount = $countsByJabatan
                    ->only($selectedJabatan)
                    ->sum(fn ($count) => (int) $count);

                if ($selectedUserCount < 1) {
                    return null;
                }

                return array_merge($item, [
                    'description' => $selectedUserCount.' user pada jabatan terpilih',
                    'cells' => [
                        (string) data_get($item, 'label', data_get($item, 'id', '-')),
                        $selectedUserCount.' user',
                    ],
                    'payload' => array_merge($itemPayload, [
                        'user_count' => $selectedUserCount,
                    ]),
                ]);
            })
            ->filter()
            ->values()
            ->all();
    };
    $currentKabupatenItems = $resolveKabupatenItems(
        collect($kabupatenOptionsByKetenagaan[$selectedTargetKetenagaan] ?? [])->values()->all(),
        $selectedTargetJabatan,
    );
    $currentSelectedKabupatenItems = collect($currentKabupatenItems)
        ->filter(fn ($item) => in_array((string) data_get($item, 'id'), $selectedTargetKabupaten, true))
        ->values()
        ->all();
    $resolveSatuanPendidikanItems = function (array $items, array $selectedJabatan, array $selectedKabupaten) {
        if ($selectedJabatan === [] || $selectedKabupaten === []) {
            return [];
        }

        return collect($items)
            ->map(function ($item) use ($selectedJabatan, $selectedKabupaten) {
                $itemPayload = (array) data_get($item, 'payload', []);
                $kabupaten = trim((string) data_get($itemPayload, 'kabupaten', ''));

                if (! in_array($kabupaten, $selectedKabupaten, true)) {
                    return null;
                }

                $countsByJabatan = collect((array) data_get($itemPayload, 'counts_by_jabatan', []));
                $selectedUserCount = $countsByJabatan
                    ->only($selectedJabatan)
                    ->sum(fn ($count) => (int) $count);

                if ($selectedUserCount < 1) {
                    return null;
                }

                return array_merge($item, [
                    'description' => $selectedUserCount.' user pada jabatan dan kabupaten terpilih',
                    'cells' => [
                        (string) data_get($item, 'label', data_get($item, 'id', '-')),
                        $kabupaten ?: '-',
                        $selectedUserCount.' user',
                    ],
                    'payload' => array_merge($itemPayload, [
                        'user_count' => $selectedUserCount,
                    ]),
                ]);
            })
            ->filter()
            ->values()
            ->all();
    };
    $currentSatuanPendidikanItems = $resolveSatuanPendidikanItems(
        collect($satuanPendidikanOptionsByKetenagaan[$selectedTargetKetenagaan] ?? [])->values()->all(),
        $selectedTargetJabatan,
        $selectedTargetKabupaten,
    );
    if ($assignment && ! session()->hasOldInput() && $selectedTargetSatuanPendidikan === []) {
        $selectedTargetSatuanPendidikan = collect($currentSatuanPendidikanItems)
            ->pluck('id')
            ->map(fn ($selectionKey) => (string) $selectionKey)
            ->values()
            ->all();
    }
    $currentSelectedSatuanPendidikanItems = collect($currentSatuanPendidikanItems)
        ->filter(fn ($item) => in_array((string) data_get($item, 'id'), $selectedTargetSatuanPendidikan, true))
        ->values()
        ->all();
    $initialKabupatenState = [
        'target' => $selectedTargetKetenagaan,
        'jabatan' => collect($selectedTargetJabatan)
            ->map(fn ($jabatan) => (string) $jabatan)
            ->sort()
            ->values()
            ->all(),
    ];
    $initialSatuanPendidikanState = [
        'target' => $selectedTargetKetenagaan,
        'jabatan' => collect($selectedTargetJabatan)
            ->map(fn ($jabatan) => (string) $jabatan)
            ->sort()
            ->values()
            ->all(),
        'kabupaten' => collect($selectedTargetKabupaten)
            ->map(fn ($kabupaten) => (string) $kabupaten)
            ->sort()
            ->values()
            ->all(),
    ];
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

        .summary-value--compact {
            font-size: 1rem;
            line-height: 1.45;
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
            border-radius: 0.2rem;
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
            border-radius: 0.2rem;
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
            border-radius: 0.2rem;
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

        .auto-summary-selected-jabatan {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-bottom: 1rem;
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
                <h1>{{ $pageTitle }}</h1>
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

                @if ($isEditMode)
                    <div class="alert alert-warning">
                        Mode edit akan menyusun ulang penugasan dari nol. Saat perubahan disimpan, sistem menghapus
                        pembagian sesi lama, riwayat pengerjaan, jawaban peserta, penilaian terkait, serta file
                        unggahan pada penugasan ini. Semua peserta target harus memulai assessment kembali dari awal.
                    </div>
                @endif

                <form action="{{ $formAction }}" method="POST" id="assignment-form">
                    @csrf
                    @if ($isEditMode)
                        @method($formMethod)
                    @endif

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Informasi Penugasan</h4>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-light border mb-4">
                                        @if ($isEditMode)
                                            Perbarui judul, target peserta, dan jadwal penugasan sesuai kebutuhan.
                                            Setelah disimpan, penugasan lama akan direset total agar seluruh peserta
                                            memulai assessment dari awal dengan konfigurasi terbaru.
                                        @else
                                            Kode penugasan dibuat otomatis. Admin cukup menentukan judul, ketenagaan target,
                                            jabatan target, kabupaten target, satuan pendidikan target, dan jadwal. Sistem akan mengambil semua form
                                            assessment yang aktif dan berstatus publish beserta seluruh user yang sesuai dengan ketenagaan,
                                            jabatan, kabupaten, dan satuan pendidikan yang dipilih.
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label>Judul Penugasan <span class="text-danger">*</span></label>
                                        <input type="text" name="judul_penugasan"
                                            class="form-control @error('judul_penugasan') is-invalid @enderror"
                                            value="{{ $selectedJudul }}"
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
                                                                Semua form + user pada jabatan, kabupaten, dan satuan pendidikan yang
                                                                dipilih di ketenagaan ini akan otomatis ditugaskan.
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

                                    <div class="form-group">
                                        <label>Jabatan Target <span class="text-danger">*</span></label>
                                        <x-multiple-choice-table id="assignment-jabatan-selector" name="target_jabatan"
                                            :headers="['Jabatan', 'Target User']" :items="$currentJabatanItems"
                                            :selected="$selectedTargetJabatan"
                                            :initialSelectedItems="$currentSelectedJabatanItems"
                                            searchPlaceholder="Cari jabatan target..."
                                            emptyMessage="{{ $selectedTargetKetenagaan ? 'Belum ada jabatan yang tersedia untuk ketenagaan ini.' : 'Pilih ketenagaan terlebih dahulu.' }}"
                                            selectedTitle="Jabatan Target" />
                                        <small class="form-text text-muted">
                                            Pilih satu atau beberapa jabatan sesuai ketenagaan target. Hanya user pada
                                            jabatan ini yang akan otomatis ditugaskan.
                                        </small>
                                        @if ($errors->has('target_jabatan') || $errors->has('target_jabatan.*'))
                                            <div class="invalid-feedback d-block">
                                                {{ $errors->first('target_jabatan') ?: $errors->first('target_jabatan.*') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label>Kabupaten Target <span class="text-danger">*</span></label>
                                        <x-multiple-choice-table id="assignment-kabupaten-selector" name="target_kabupaten"
                                            :headers="['Kabupaten', 'Target User']" :items="$currentKabupatenItems"
                                            :selected="$selectedTargetKabupaten"
                                            :initialSelectedItems="$currentSelectedKabupatenItems"
                                            searchPlaceholder="Cari kabupaten target..."
                                            emptyMessage="{{ $selectedTargetJabatan !== [] ? 'Belum ada kabupaten yang tersedia untuk kombinasi ketenagaan dan jabatan ini.' : 'Pilih minimal satu jabatan target terlebih dahulu.' }}"
                                            selectedTitle="Kabupaten Target" />
                                        <small class="form-text text-muted">
                                            Pilih satu atau beberapa kabupaten sesuai ketenagaan dan jabatan target.
                                            Hanya user pada kabupaten ini yang akan otomatis ditugaskan.
                                        </small>
                                        @if ($errors->has('target_kabupaten') || $errors->has('target_kabupaten.*'))
                                            <div class="invalid-feedback d-block">
                                                {{ $errors->first('target_kabupaten') ?: $errors->first('target_kabupaten.*') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label>Satuan Pendidikan Target <span class="text-danger">*</span></label>
                                        <x-multiple-choice-table id="assignment-satuan-pendidikan-selector"
                                            name="target_satuan_pendidikan"
                                            :headers="['Satuan Pendidikan', 'Kabupaten', 'Target User']"
                                            :items="$currentSatuanPendidikanItems"
                                            :selected="$selectedTargetSatuanPendidikan"
                                            :initialSelectedItems="$currentSelectedSatuanPendidikanItems"
                                            searchPlaceholder="Cari satuan pendidikan target..."
                                            emptyMessage="{{ $selectedTargetKabupaten !== [] ? 'Belum ada satuan pendidikan yang tersedia untuk kombinasi ketenagaan, jabatan, dan kabupaten ini.' : 'Pilih minimal satu kabupaten target terlebih dahulu.' }}"
                                            selectedTitle="Satuan Pendidikan Target" />
                                        <small class="form-text text-muted">
                                            Pilih satu atau beberapa satuan pendidikan sesuai ketenagaan, jabatan, dan
                                            kabupaten target. Hanya user pada satuan pendidikan ini yang akan otomatis
                                            ditugaskan.
                                        </small>
                                        @if ($errors->has('target_satuan_pendidikan') || $errors->has('target_satuan_pendidikan.*'))
                                            <div class="invalid-feedback d-block">
                                                {{ $errors->first('target_satuan_pendidikan') ?: $errors->first('target_satuan_pendidikan.*') }}
                                            </div>
                                        @endif
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
                                                <span class="auto-summary-pill" id="auto-summary-assessment-count">0 assessment sumber</span>
                                                <span class="auto-summary-pill" id="auto-summary-jabatan-count">0 jabatan</span>
                                                <span class="auto-summary-pill" id="auto-summary-kabupaten-count">0 kabupaten</span>
                                                <span class="auto-summary-pill" id="auto-summary-school-count">0 satuan pendidikan</span>
                                                <span class="auto-summary-pill" id="auto-summary-user-count">0 user</span>
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-md-4 col-6 mb-3">
                                                <div class="text-muted small">Total Form Sumber</div>
                                                <div class="summary-value" id="auto-summary-form-count">0</div>
                                            </div>
                                            <div class="col-md-4 col-6 mb-3">
                                                <div class="text-muted small">Total Pertanyaan Sumber</div>
                                                <div class="summary-value" id="auto-summary-field-count">0</div>
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

                                    <div class="card border shadow-none mb-4">
                                        <div class="card-header bg-white">
                                            <h4 class="mb-0">Distribusi Kombinasi Soal Otomatis</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-light border mb-4">
                                                Data form kombinasi ini akan didistibusikan secara merata ke seluruh peserta yang dipilih
                                            </div>

                                            <div class="auto-summary-panel mb-0">
                                                <div class="d-flex flex-wrap justify-content-between align-items-start">
                                                    <div class="mb-3">
                                                        <div class="text-muted small">Pool Kombinasi Aktif</div>
                                                        <div class="font-weight-bold" id="auto-combination-summary-title">
                                                            {{ count($currentCombinationOptions) > 0 ? count($currentCombinationOptions) . ' kombinasi siap dibagikan otomatis' : 'Belum ada kombinasi aktif untuk ketenagaan ini' }}
                                                        </div>
                                                        <small class="text-muted d-block" id="auto-combination-summary-code">
                                                            Acak kabupaten dengan urutan acak stabil.
                                                        </small>
                                                    </div>
                                                    <div class="mb-3 text-right">
                                                        <span class="auto-summary-pill" id="auto-combination-summary-count">
                                                            {{ count($currentCombinationOptions) }} kombinasi
                                                        </span>
                                                        <span class="auto-summary-pill" id="auto-combination-summary-assessments">
                                                            0 assessment
                                                        </span>
                                                        <span class="auto-summary-pill" id="auto-combination-summary-forms">
                                                            0 form
                                                        </span>
                                                        <span class="auto-summary-pill" id="auto-combination-summary-questions">
                                                            0 soal
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="table-responsive mb-4">
                                                    <table class="table table-sm auto-summary-table mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Kode Kombinasi</th>
                                                                <th>Assessment Sumber</th>
                                                                <th>Struktur</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="auto-combination-list">
                                                            @if (!empty($currentCombinationOptions))
                                                                @foreach ($currentCombinationOptions as $combinationOption)
                                                                    <tr>
                                                                        <td class="font-weight-bold">
                                                                            {{ $combinationOption['kode'] ?: '-' }}
                                                                        </td>
                                                                        <td>
                                                                            {{ $combinationOption['total_assessments'] ?? 0 }} assessment sumber
                                                                        </td>
                                                                        <td>
                                                                            {{ $combinationOption['total_forms'] ?? 0 }} form /
                                                                            {{ $combinationOption['total_questions'] ?? 0 }} soal
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="3" class="auto-summary-empty">
                                                                        Kombinasi aktif akan tampil otomatis setelah ketenagaan dipilih.
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-sm auto-summary-table mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Kabupaten</th>
                                                                <th>Satuan Pendidikan</th>
                                                                <th>Kode Kombinasi</th>
                                                                <th>Target User</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="auto-combination-distribution-list">
                                                            <tr>
                                                                <td colspan="4" class="auto-summary-empty">
                                                                    Preview pembagian kabupaten akan tampil setelah jabatan, kabupaten, dan satuan pendidikan target dipilih.
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
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
                                                    value="{{ $selectedStartDate }}">
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
                                                    value="{{ $selectedEndDate }}">
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

                                    <div class="card border shadow-none mb-4">
                                        <div class="card-header bg-white">
                                            <h4 class="mb-0">Guard Ujian & Anti Kecurangan</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-light border mb-4">
                                                Konfigurasi ini mengaktifkan blok shortcut, pantauan fokus halaman, serta
                                                kontrol fullscreen pada portal peserta. Seluruh pelanggaran akan masuk
                                                ke database attempt sebagai log audit.
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="custom-control custom-switch mb-3">
                                                        <input type="hidden" name="security_enabled" value="0">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="security_enabled" name="security_enabled" value="1"
                                                            @checked($selectedSecurityEnabled)>
                                                        <label class="custom-control-label" for="security_enabled">
                                                            Aktifkan Guard Ujian
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="custom-control custom-switch mb-3">
                                                        <input type="hidden" name="security_require_fullscreen" value="0">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="security_require_fullscreen"
                                                            name="security_require_fullscreen" value="1"
                                                            @checked($selectedSecurityRequireFullscreen)>
                                                        <label class="custom-control-label"
                                                            for="security_require_fullscreen">
                                                            Wajib Fullscreen
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Batas Pelanggaran Serius</label>
                                                        <input type="number" min="1" max="10"
                                                            name="security_max_serious_violations"
                                                            class="form-control @error('security_max_serious_violations') is-invalid @enderror"
                                                            value="{{ $selectedSecurityMaxSeriousViolations }}">
                                                        <small class="text-muted">
                                                            Saat limit tercapai, ujian dihentikan otomatis.
                                                        </small>
                                                        @error('security_max_serious_violations')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Kunci Sementara (detik)</label>
                                                        <input type="number" min="1" max="30"
                                                            name="security_temporary_lock_seconds"
                                                            class="form-control @error('security_temporary_lock_seconds') is-invalid @enderror"
                                                            value="{{ $selectedSecurityTemporaryLockSeconds }}">
                                                        <small class="text-muted">
                                                            Waktu lock saat warning atau pelanggaran non-final.
                                                        </small>
                                                        @error('security_temporary_lock_seconds')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Tenggang Fullscreen (detik)</label>
                                                        <input type="number" min="3" max="60"
                                                            name="security_fullscreen_grace_seconds"
                                                            class="form-control @error('security_fullscreen_grace_seconds') is-invalid @enderror"
                                                            value="{{ $selectedSecurityFullscreenGraceSeconds }}">
                                                        <small class="text-muted">
                                                            Batas kembali ke fullscreen sebelum diskualifikasi.
                                                        </small>
                                                        @error('security_fullscreen_grace_seconds')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Deskripsi Penugasan</label>
                                        <textarea name="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror"
                                            placeholder="Catatan, instruksi, atau konteks penugasan untuk admin.">{{ $selectedDeskripsi }}</textarea>
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
                                        <div class="summary-value">{{ $assignment?->kode_penugasan ?: 'Otomatis saat simpan' }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small">Ketenagaan Dipilih</div>
                                        <div class="summary-value" id="summary-ketenagaan">-</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small">Jabatan Dipilih</div>
                                        <div class="summary-value summary-value--compact" id="summary-jabatan">Belum dipilih</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small">Distribusi Kombinasi</div>
                                        <div class="summary-value summary-value--compact" id="summary-combination-title">
                                            {{ count($currentCombinationOptions) > 0 ? count($currentCombinationOptions) . ' kombinasi otomatis' : 'Belum tersedia' }}
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small">Kabupaten Dipilih</div>
                                        <div class="summary-value summary-value--compact" id="summary-kabupaten">Belum dipilih</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small">Satuan Pendidikan Dipilih</div>
                                        <div class="summary-value summary-value--compact" id="summary-satuan-pendidikan">
                                            Belum dipilih
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small">Assessment Sumber</div>
                                        <div class="summary-value" id="summary-assessment-count">0</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="text-muted small">Form Sumber</div>
                                            <div class="summary-value" id="summary-forms">0</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-muted small">Child Soal</div>
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
                                        <button type="{{ $isEditMode ? 'button' : 'submit' }}"
                                            class="btn btn-primary btn-block" id="assignment-submit-button">
                                            <i class="fas fa-paper-plane"></i> {{ $submitLabel }}
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

@if ($isEditMode)
    @push('modals')
        <div class="modal fade" id="assignmentEditWarningModal" tabindex="-1" role="dialog"
            aria-labelledby="assignmentEditWarningModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark" id="assignmentEditWarningModalLabel">
                            Reset Penugasan Saat Edit
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">
                            Menyimpan perubahan untuk <strong>{{ $assignment?->judul_penugasan }}</strong>
                            akan mereset penugasan ini dari nol.
                        </p>
                        <ul class="pl-3 mb-3">
                            <li>Pembagian target dan sesi lama akan dibentuk ulang.</li>
                            <li>Riwayat mulai/submit, jawaban, penilaian, dan file unggahan peserta akan dihapus.</li>
                            <li>Seluruh peserta target harus mengerjakan assessment kembali dari awal.</li>
                        </ul>
                        <div class="alert alert-warning mb-0">
                            Lanjutkan hanya jika Anda yakin data lama pada penugasan ini memang harus dibersihkan.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-warning" id="assignment-edit-confirm-button">
                            Ya, Reset dan Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endpush
@endif

@push('scripts')
    <script>
        (() => {
            const ketenagaanSummaries = @json($ketenagaanSummaries);
            const combinationOptionsByKetenagaan = @json($combinationOptionsByKetenagaan);
            const jabatanOptionsByKetenagaan = @json($jabatanOptionsByKetenagaan);
            const kabupatenOptionsByKetenagaan = @json($kabupatenOptionsByKetenagaan);
            const satuanPendidikanOptionsByKetenagaan = @json($satuanPendidikanOptionsByKetenagaan);
            const sessionCapacity = {{ $sessionCapacity }};
            const defaultDurationHours = {{ $defaultSessionDurationHours }};
            const batchThreshold = {{ $batchThreshold }};
            const initialKabupatenState = @json($initialKabupatenState);
            const initialSatuanPendidikanState = @json($initialSatuanPendidikanState);
            let activeJabatanTarget = @json($selectedTargetKetenagaan);
            let activeKabupatenStateKey = JSON.stringify(initialKabupatenState);
            let activeSatuanPendidikanStateKey = JSON.stringify(initialSatuanPendidikanState);
            const assignmentForm = document.getElementById('assignment-form');

            function escapeHtml(value) {
                const node = document.createElement('div');
                node.textContent = value == null ? '' : String(value);

                return node.innerHTML;
            }

            function getSelectedTargetKetenagaan() {
                const selected = document.querySelector('input[name="target_ketenagaan"]:checked');

                return selected ? selected.value : '';
            }

            function getJabatanSelector() {
                return document.querySelector('[data-table-id="assignment-jabatan-selector"]');
            }

            function getKabupatenSelector() {
                return document.querySelector('[data-table-id="assignment-kabupaten-selector"]');
            }

            function getSatuanPendidikanSelector() {
                return document.querySelector('[data-table-id="assignment-satuan-pendidikan-selector"]');
            }

            function getAvailableJabatanItems(target = getSelectedTargetKetenagaan()) {
                return target && Array.isArray(jabatanOptionsByKetenagaan[target]) ? jabatanOptionsByKetenagaan[target] : [];
            }

            function getAvailableKabupatenBaseItems(target = getSelectedTargetKetenagaan()) {
                return target && Array.isArray(kabupatenOptionsByKetenagaan[target]) ? kabupatenOptionsByKetenagaan[target] : [];
            }

            function getAvailableSatuanPendidikanBaseItems(target = getSelectedTargetKetenagaan()) {
                return target && Array.isArray(satuanPendidikanOptionsByKetenagaan[target]) ? satuanPendidikanOptionsByKetenagaan[target] : [];
            }

            function getSelectedJabatanIds() {
                const selector = getJabatanSelector();

                if (!selector) {
                    return [];
                }

                return Array.from(selector.querySelectorAll('input[name="target_jabatan[]"]'))
                    .map((input) => String(input.value || '').trim())
                    .filter((value) => value !== '');
            }

            function getSelectedJabatanItems() {
                const selectedIds = getSelectedJabatanIds();
                const itemMap = new Map(getAvailableJabatanItems().map((item) => [String(item.id), item]));

                return selectedIds
                    .map((id) => itemMap.get(String(id)))
                    .filter((item) => item);
            }

            function buildKabupatenItemsForSelection(
                target = getSelectedTargetKetenagaan(),
                selectedJabatanItems = getSelectedJabatanItems()
            ) {
                if (!target || selectedJabatanItems.length === 0) {
                    return [];
                }

                const selectedJabatanIds = selectedJabatanItems
                    .map((item) => String(item.id || '').trim())
                    .filter((value) => value !== '');

                return getAvailableKabupatenBaseItems(target)
                    .map((item) => {
                        const payload = item && item.payload && typeof item.payload === 'object' ? item.payload : {};
                        const countsByJabatan = payload && payload.counts_by_jabatan && typeof payload.counts_by_jabatan === 'object' ?
                            payload.counts_by_jabatan :
                            {};
                        const userCount = selectedJabatanIds.reduce((total, jabatanId) => {
                            return total + Number(countsByJabatan[jabatanId] || 0);
                        }, 0);

                        if (userCount < 1) {
                            return null;
                        }

                        return {
                            id: String(item.id || ''),
                            label: String(item.label || item.id || ''),
                            description: userCount + ' user pada jabatan terpilih',
                            cells: [
                                String(item.label || item.id || '-'),
                                userCount + ' user',
                            ],
                            payload: Object.assign({}, payload, {
                                user_count: userCount,
                            }),
                        };
                    })
                    .filter((item) => item && item.id !== '');
            }

            function buildKabupatenStateKey(
                target = getSelectedTargetKetenagaan(),
                selectedJabatanItems = getSelectedJabatanItems()
            ) {
                return JSON.stringify({
                    target: target || '',
                    jabatan: selectedJabatanItems
                        .map((item) => String(item.id || '').trim())
                        .filter((value) => value !== '')
                        .sort(),
                });
            }

            function getSelectedKabupatenIds() {
                const selector = getKabupatenSelector();

                if (!selector) {
                    return [];
                }

                return Array.from(selector.querySelectorAll('input[name="target_kabupaten[]"]'))
                    .map((input) => String(input.value || '').trim())
                    .filter((value) => value !== '');
            }

            function getSelectedKabupatenItems() {
                const selectedIds = getSelectedKabupatenIds();
                const itemMap = new Map(buildKabupatenItemsForSelection().map((item) => [String(item.id), item]));

                return selectedIds
                    .map((id) => itemMap.get(String(id)))
                    .filter((item) => item);
            }

            function buildSatuanPendidikanItemsForSelection(
                target = getSelectedTargetKetenagaan(),
                selectedJabatanItems = getSelectedJabatanItems(),
                selectedKabupatenItems = getSelectedKabupatenItems()
            ) {
                if (!target || selectedJabatanItems.length === 0 || selectedKabupatenItems.length === 0) {
                    return [];
                }

                const selectedJabatanIds = selectedJabatanItems
                    .map((item) => String(item.id || '').trim())
                    .filter((value) => value !== '');
                const selectedKabupatenIds = selectedKabupatenItems
                    .map((item) => String(item.id || '').trim())
                    .filter((value) => value !== '');

                return getAvailableSatuanPendidikanBaseItems(target)
                    .map((item) => {
                        const payload = item && item.payload && typeof item.payload === 'object' ? item.payload : {};
                        const kabupaten = String(payload.kabupaten || '').trim();
                        const countsByJabatan = payload && payload.counts_by_jabatan && typeof payload.counts_by_jabatan === 'object' ?
                            payload.counts_by_jabatan :
                            {};

                        if (selectedKabupatenIds.includes(kabupaten) === false) {
                            return null;
                        }

                        const userCount = selectedJabatanIds.reduce((total, jabatanId) => {
                            return total + Number(countsByJabatan[jabatanId] || 0);
                        }, 0);

                        if (userCount < 1) {
                            return null;
                        }

                        return {
                            id: String(item.id || ''),
                            label: String(item.label || item.id || ''),
                            description: userCount + ' user pada jabatan dan kabupaten terpilih',
                            cells: [
                                String(item.label || item.id || '-'),
                                kabupaten || '-',
                                userCount + ' user',
                            ],
                            payload: Object.assign({}, payload, {
                                user_count: userCount,
                            }),
                        };
                    })
                    .filter((item) => item && item.id !== '');
            }

            function buildSatuanPendidikanStateKey(
                target = getSelectedTargetKetenagaan(),
                selectedJabatanItems = getSelectedJabatanItems(),
                selectedKabupatenItems = getSelectedKabupatenItems()
            ) {
                return JSON.stringify({
                    target: target || '',
                    jabatan: selectedJabatanItems
                        .map((item) => String(item.id || '').trim())
                        .filter((value) => value !== '')
                        .sort(),
                    kabupaten: selectedKabupatenItems
                        .map((item) => String(item.id || '').trim())
                        .filter((value) => value !== '')
                        .sort(),
                });
            }

            function getSelectedSatuanPendidikanIds() {
                const selector = getSatuanPendidikanSelector();

                if (!selector) {
                    return [];
                }

                return Array.from(selector.querySelectorAll('input[name="target_satuan_pendidikan[]"]'))
                    .map((input) => String(input.value || '').trim())
                    .filter((value) => value !== '');
            }

            function getSelectedSatuanPendidikanItems() {
                const selectedIds = getSelectedSatuanPendidikanIds();
                const itemMap = new Map(buildSatuanPendidikanItemsForSelection().map((item) => [String(item.id), item]));

                return selectedIds
                    .map((id) => itemMap.get(String(id)))
                    .filter((item) => item);
            }

            function syncJabatanSelector(force = false) {
                const selector = getJabatanSelector();
                const target = getSelectedTargetKetenagaan();

                if (!selector || (!force && activeJabatanTarget === target)) {
                    return;
                }

                activeJabatanTarget = target;
                selector.dispatchEvent(new CustomEvent('multiple-choice-table:set-items', {
                    detail: {
                        items: getAvailableJabatanItems(target),
                        selectedIds: [],
                        emptyMessage: target ?
                            'Belum ada jabatan yang tersedia untuk ketenagaan ini.' :
                            'Pilih ketenagaan terlebih dahulu.',
                        emitChange: false,
                    },
                }));
            }

            function syncKabupatenSelector(force = false) {
                const selector = getKabupatenSelector();
                const target = getSelectedTargetKetenagaan();
                const selectedJabatanItems = getSelectedJabatanItems();
                const stateKey = buildKabupatenStateKey(target, selectedJabatanItems);

                if (!selector || (!force && activeKabupatenStateKey === stateKey)) {
                    return;
                }

                activeKabupatenStateKey = stateKey;
                selector.dispatchEvent(new CustomEvent('multiple-choice-table:set-items', {
                    detail: {
                        items: buildKabupatenItemsForSelection(target, selectedJabatanItems),
                        selectedIds: [],
                        emptyMessage: selectedJabatanItems.length === 0 ?
                            'Pilih minimal satu jabatan target terlebih dahulu.' :
                            'Belum ada kabupaten yang tersedia untuk kombinasi ketenagaan dan jabatan ini.',
                        emitChange: false,
                    },
                }));
            }

            function syncSatuanPendidikanSelector(force = false) {
                const selector = getSatuanPendidikanSelector();
                const target = getSelectedTargetKetenagaan();
                const selectedJabatanItems = getSelectedJabatanItems();
                const selectedKabupatenItems = getSelectedKabupatenItems();
                const stateKey = buildSatuanPendidikanStateKey(
                    target,
                    selectedJabatanItems,
                    selectedKabupatenItems
                );

                if (!selector || (!force && activeSatuanPendidikanStateKey === stateKey)) {
                    return;
                }

                activeSatuanPendidikanStateKey = stateKey;
                selector.dispatchEvent(new CustomEvent('multiple-choice-table:set-items', {
                    detail: {
                        items: buildSatuanPendidikanItemsForSelection(
                            target,
                            selectedJabatanItems,
                            selectedKabupatenItems
                        ),
                        selectedIds: [],
                        emptyMessage: selectedJabatanItems.length === 0 ?
                            'Pilih minimal satu jabatan target terlebih dahulu.' :
                            (selectedKabupatenItems.length === 0 ?
                                'Pilih minimal satu kabupaten target terlebih dahulu.' :
                                'Belum ada satuan pendidikan yang tersedia untuk kombinasi ketenagaan, jabatan, dan kabupaten ini.'),
                        emitChange: false,
                    },
                }));
            }

            function renderSelectedJabatanBadges(selectedJabatanItems) {
                const selectedJabatanNode = document.getElementById('auto-summary-selected-jabatan');

                if (!selectedJabatanNode) {
                    return;
                }

                if (selectedJabatanItems.length === 0) {
                    selectedJabatanNode.innerHTML = `
                        <span class="text-muted small">
                            Pilih minimal satu jabatan target untuk menentukan peserta penugasan.
                        </span>
                    `;

                    return;
                }

                selectedJabatanNode.innerHTML = selectedJabatanItems.map((item) => {
                    return `
                        <span class="auto-summary-pill">
                            ${escapeHtml(item.label || item.id || '-')}
                        </span>
                    `;
                }).join('');
            }

            function renderSelectedKabupatenBadges(selectedKabupatenItems, selectedJabatanItems) {
                const selectedKabupatenNode = document.getElementById('auto-summary-selected-kabupaten');

                if (!selectedKabupatenNode) {
                    return;
                }

                if (selectedJabatanItems.length === 0) {
                    selectedKabupatenNode.innerHTML = `
                        <span class="text-muted small">
                            Pilih minimal satu jabatan target terlebih dahulu sebelum menentukan kabupaten.
                        </span>
                    `;

                    return;
                }

                if (selectedKabupatenItems.length === 0) {
                    selectedKabupatenNode.innerHTML = `
                        <span class="text-muted small">
                            Pilih minimal satu kabupaten target setelah memilih jabatan.
                        </span>
                    `;

                    return;
                }

                selectedKabupatenNode.innerHTML = selectedKabupatenItems.map((item) => {
                    const userCount = Number((item.payload && item.payload.user_count) || 0);

                    return `
                        <span class="auto-summary-pill">
                            ${escapeHtml(item.label || item.id || '-')} (${userCount} user)
                        </span>
                    `;
                }).join('');
            }

            function formatSelectedJabatanSummary(selectedJabatanItems) {
                if (selectedJabatanItems.length === 0) {
                    return 'Belum dipilih';
                }

                const preview = selectedJabatanItems
                    .slice(0, 2)
                    .map((item) => item.label || item.id || '-');

                if (selectedJabatanItems.length <= 2) {
                    return preview.join(', ');
                }

                return preview.join(', ') + ' +' + (selectedJabatanItems.length - 2) + ' lainnya';
            }

            function formatSelectedKabupatenSummary(selectedKabupatenItems) {
                if (selectedKabupatenItems.length === 0) {
                    return 'Belum dipilih';
                }

                const preview = selectedKabupatenItems
                    .slice(0, 2)
                    .map((item) => item.label || item.id || '-');

                if (selectedKabupatenItems.length <= 2) {
                    return preview.join(', ');
                }

                return preview.join(', ') + ' +' + (selectedKabupatenItems.length - 2) + ' lainnya';
            }

            function formatSelectedSatuanPendidikanSummary(selectedSatuanPendidikanItems) {
                if (selectedSatuanPendidikanItems.length === 0) {
                    return 'Belum dipilih';
                }

                const preview = selectedSatuanPendidikanItems
                    .slice(0, 2)
                    .map((item) => {
                        const kabupaten = String((item.payload && item.payload.kabupaten) || '').trim();
                        const label = item.label || item.id || '-';

                        return kabupaten !== '' ? `${label} (${kabupaten})` : label;
                    });

                if (selectedSatuanPendidikanItems.length <= 2) {
                    return preview.join(', ');
                }

                return preview.join(', ') + ' +' + (selectedSatuanPendidikanItems.length - 2) + ' lainnya';
            }

            function getSelectedSummary() {
                const target = getSelectedTargetKetenagaan();

                return target && ketenagaanSummaries[target] ? ketenagaanSummaries[target] : null;
            }

            function getSelectedDurationHours() {
                const durationSelect = document.getElementById('durasi_sesi_jam');

                return Number((durationSelect ? durationSelect.value : '') || defaultDurationHours);
            }

            function getAvailableCombinationOptions(target = getSelectedTargetKetenagaan()) {
                return target && Array.isArray(combinationOptionsByKetenagaan[target]) ? combinationOptionsByKetenagaan[target] : [];
            }

            function getAssignmentTitle() {
                const titleInput = document.querySelector('input[name="judul_penugasan"]');

                return titleInput ? String(titleInput.value || '').trim() : '';
            }

            function normalizeSeedSelectionList(values) {
                return values
                    .map((value) => String(value || '').trim())
                    .filter((value) => value !== '')
                    .sort((left, right) => left.localeCompare(right, 'id', {
                        sensitivity: 'base',
                        numeric: true,
                    }));
            }

            function buildCombinationSeedKey() {
                return [
                    getAssignmentTitle(),
                    getSelectedTargetKetenagaan(),
                    normalizeSeedSelectionList(getSelectedJabatanIds()).join(','),
                    normalizeSeedSelectionList(getSelectedKabupatenIds()).join(','),
                    normalizeSeedSelectionList(getSelectedSatuanPendidikanIds()).join(','),
                ].join('|');
            }

            function stableHash(value) {
                let hash = 5381;

                for (let index = 0; index < value.length; index += 1) {
                    hash = (((hash << 5) + hash) + value.charCodeAt(index)) & 0x7fffffff;
                }

                return hash >>> 0;
            }

            function sortCombinationOptionsForRoundRobin(options, seedKey = buildCombinationSeedKey()) {
                return [...options].sort((left, right) => {
                    const leftHash = stableHash(seedKey + '|' + Number(left.id || 0));
                    const rightHash = stableHash(seedKey + '|' + Number(right.id || 0));

                    if (leftHash === rightHash) {
                        return Number(left.id || 0) - Number(right.id || 0);
                    }

                    return leftHash - rightHash;
                });
            }

            function buildAutoCombinationDistribution() {
                const combinationOptions = getAvailableCombinationOptions();
                const orderedCombinations = sortCombinationOptionsForRoundRobin(combinationOptions);
                const selectedKabupatenItems = [...getSelectedKabupatenItems()].sort((left, right) => {
                    const leftLabel = String(left.label || left.id || '');
                    const rightLabel = String(right.label || right.id || '');

                    return leftLabel.localeCompare(rightLabel, 'id', {
                        sensitivity: 'base',
                        numeric: true,
                    });
                });
                const selectedSatuanPendidikanItems = getSelectedSatuanPendidikanItems();
                const schoolsByKabupaten = selectedSatuanPendidikanItems.reduce((map, item) => {
                    const payload = item && item.payload && typeof item.payload === 'object' ? item.payload : {};
                    const kabupaten = String(payload.kabupaten || '').trim();

                    if (!map.has(kabupaten)) {
                        map.set(kabupaten, []);
                    }

                    map.get(kabupaten).push(item);

                    return map;
                }, new Map());
                const rows = selectedKabupatenItems.map((item, index) => {
                    const kabupatenId = String(item.id || '').trim();
                    const schools = schoolsByKabupaten.get(kabupatenId) || [];

                    return {
                        kabupaten: item,
                        schoolCount: schools.length,
                        userCount: schools.reduce((total, schoolItem) => {
                            return total + Number((schoolItem.payload && schoolItem.payload.user_count) || 0);
                        }, 0),
                        combination: orderedCombinations[index % orderedCombinations.length] || null,
                    };
                });

                return {
                    combinationOptions,
                    orderedCombinations,
                    rows,
                    selectedSatuanPendidikanItems,
                };
            }

            function buildMetricValue(options, key) {
                const values = [...new Set(options.map((item) => Number(item && item[key] || 0)))].sort((left, right) => left - right);

                if (values.length === 0) {
                    return '0';
                }

                if (values.length === 1) {
                    return String(values[0]);
                }

                return `${values[0]}-${values[values.length - 1]}`;
            }

            function buildMetricLabel(options, key, label) {
                return `${buildMetricValue(options, key)} ${label}`;
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

            function renderAutoCombinationList(orderedCombinations) {
                const tbody = document.getElementById('auto-combination-list');

                if (!tbody) {
                    return;
                }

                if (orderedCombinations.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="3" class="auto-summary-empty">
                                Kombinasi aktif akan tampil otomatis setelah ketenagaan dipilih.
                            </td>
                        </tr>
                    `;

                    return;
                }

                tbody.innerHTML = orderedCombinations.map((item) => {
                    return `
                        <tr>
                            <td class="font-weight-bold">${escapeHtml(item.kode || '-')}</td>
                            <td>${Number(item.total_assessments || 0)} assessment sumber</td>
                            <td>${Number(item.total_forms || 0)} form / ${Number(item.total_questions || 0)} soal</td>
                        </tr>
                    `;
                }).join('');
            }

            function renderAutoCombinationDistribution(
                distributionRows,
                combinationOptions,
                selectedKabupatenItems,
                selectedSatuanPendidikanItems
            ) {
                const tbody = document.getElementById('auto-combination-distribution-list');

                if (!tbody) {
                    return;
                }

                if (combinationOptions.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="auto-summary-empty">
                                Belum ada kombinasi aktif yang bisa dibagikan otomatis untuk ketenagaan ini.
                            </td>
                        </tr>
                    `;

                    return;
                }

                if (selectedKabupatenItems.length === 0 || selectedSatuanPendidikanItems.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="auto-summary-empty">
                                Preview pembagian kabupaten akan tampil setelah jabatan, kabupaten, dan satuan pendidikan target dipilih.
                            </td>
                        </tr>
                    `;

                    return;
                }

                tbody.innerHTML = distributionRows.map((row) => {
                    return `
                        <tr>
                            <td class="font-weight-bold">${escapeHtml(row.kabupaten.label || row.kabupaten.id || '-')}</td>
                            <td>${Number(row.schoolCount || 0)} satuan pendidikan</td>
                            <td>
                                <div class="font-weight-600">${escapeHtml(row.combination && row.combination.kode || '-')}</div>
                            </td>
                            <td>${Number(row.userCount || 0)} user</td>
                        </tr>
                    `;
                }).join('');
            }

            function updateAutoSummaryPanel() {
                const summary = getSelectedSummary();
                const availableJabatanItems = getAvailableJabatanItems();
                const selectedJabatanItems = getSelectedJabatanItems();
                const availableKabupatenItems = buildKabupatenItemsForSelection();
                const selectedKabupatenItems = getSelectedKabupatenItems();
                const availableSatuanPendidikanItems = buildSatuanPendidikanItemsForSelection();
                const selectedSatuanPendidikanItems = getSelectedSatuanPendidikanItems();
                const combinationDistribution = buildAutoCombinationDistribution();
                const combinationOptions = combinationDistribution.combinationOptions;
                const labelNode = document.getElementById('auto-summary-ketenagaan-label');
                const assessmentCountNode = document.getElementById('auto-summary-assessment-count');
                const jabatanCountNode = document.getElementById('auto-summary-jabatan-count');
                const kabupatenCountNode = document.getElementById('auto-summary-kabupaten-count');
                const schoolCountNode = document.getElementById('auto-summary-school-count');
                const userCountNode = document.getElementById('auto-summary-user-count');
                const formCountNode = document.getElementById('auto-summary-form-count');
                const fieldCountNode = document.getElementById('auto-summary-field-count');
                const distributionNode = document.getElementById('auto-summary-distribution');
                const warningNode = document.getElementById('auto-summary-warning');
                const submitButton = document.getElementById('assignment-submit-button');
                const combinationTitleNode = document.getElementById('auto-combination-summary-title');
                const combinationCodeNode = document.getElementById('auto-combination-summary-code');
                const combinationCountNode = document.getElementById('auto-combination-summary-count');
                const combinationAssessmentsNode = document.getElementById('auto-combination-summary-assessments');
                const combinationFormsNode = document.getElementById('auto-combination-summary-forms');
                const combinationQuestionsNode = document.getElementById('auto-combination-summary-questions');

                const assessmentCount = summary ? Number(summary.assessment_count || 0) : 0;
                const userCount = selectedSatuanPendidikanItems.reduce((total, item) => {
                    return total + Number((item.payload && item.payload.user_count) || 0);
                }, 0);
                const distributionMethod = userCount === 0
                    ? '-'
                    : `Acak per Kabupaten / ${userCount > batchThreshold ? 'Batch Job' : 'Langsung'}`;

                if (labelNode) {
                    labelNode.textContent = summary ? summary.label : 'Pilih ketenagaan terlebih dahulu';
                }

                if (assessmentCountNode) {
                    assessmentCountNode.textContent = assessmentCount + ' assessment sumber';
                }

                if (jabatanCountNode) {
                    jabatanCountNode.textContent = selectedJabatanItems.length + ' jabatan';
                }

                if (kabupatenCountNode) {
                    kabupatenCountNode.textContent = selectedKabupatenItems.length + ' kabupaten';
                }

                if (schoolCountNode) {
                    schoolCountNode.textContent = selectedSatuanPendidikanItems.length + ' satuan pendidikan';
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

                renderSelectedJabatanBadges(selectedJabatanItems);
                renderSelectedKabupatenBadges(selectedKabupatenItems, selectedJabatanItems);

                if (combinationTitleNode) {
                    combinationTitleNode.textContent = combinationOptions.length > 0
                        ? `${combinationOptions.length} kombinasi siap dibagikan otomatis`
                        : 'Belum ada kombinasi aktif untuk ketenagaan ini';
                }

                if (combinationCodeNode) {
                    combinationCodeNode.textContent = combinationOptions.length > 0
                        ? 'Urutan kombinasi diacak stabil dari judul penugasan + target jabatan/kabupaten/satuan pendidikan.'
                        : 'Acak per kabupaten dengan urutan acak stabil.';
                }

                if (combinationCountNode) {
                    combinationCountNode.textContent = combinationOptions.length + ' kombinasi';
                }

                if (combinationAssessmentsNode) {
                    combinationAssessmentsNode.textContent = buildMetricLabel(
                        combinationOptions,
                        'total_assessments',
                        'assessment'
                    );
                }

                if (combinationFormsNode) {
                    combinationFormsNode.textContent = buildMetricLabel(
                        combinationOptions,
                        'total_forms',
                        'form'
                    );
                }

                if (combinationQuestionsNode) {
                    combinationQuestionsNode.textContent = buildMetricLabel(
                        combinationOptions,
                        'total_questions',
                        'soal'
                    );
                }

                if (warningNode) {
                    const warningMessages = [];

                    if (summary && assessmentCount === 0) {
                        warningMessages.push('Belum ada assessment yang aktif dan berstatus publish untuk ketenagaan ini.');
                    }

                    if (summary && combinationOptions.length === 0) {
                        warningMessages.push('Belum ada kombinasi soal aktif yang bisa dibagikan otomatis pada ketenagaan ini.');
                    }

                    if (summary && availableJabatanItems.length === 0) {
                        warningMessages.push('Belum ada data jabatan untuk ketenagaan ini.');
                    } else if (summary && selectedJabatanItems.length === 0) {
                        warningMessages.push('Pilih minimal satu jabatan target.');
                    } else if (summary && availableKabupatenItems.length === 0) {
                        warningMessages.push('Belum ada data kabupaten untuk kombinasi ketenagaan dan jabatan ini.');
                    } else if (summary && selectedKabupatenItems.length === 0) {
                        warningMessages.push('Pilih minimal satu kabupaten target.');
                    } else if (summary && availableSatuanPendidikanItems.length === 0) {
                        warningMessages.push('Belum ada data satuan pendidikan untuk kombinasi ketenagaan, jabatan, dan kabupaten ini.');
                    } else if (summary && selectedSatuanPendidikanItems.length === 0) {
                        warningMessages.push('Pilih minimal satu satuan pendidikan target.');
                    } else if (summary && userCount === 0) {
                        warningMessages.push('Belum ada user/peserta pada kombinasi jabatan, kabupaten, dan satuan pendidikan yang dipilih.');
                    }

                    warningNode.classList.toggle('d-none', warningMessages.length === 0);
                    warningNode.textContent = warningMessages.join(' ');
                }

                if (submitButton) {
                    submitButton.disabled = !summary || combinationOptions.length === 0 || assessmentCount === 0 || selectedJabatanItems.length === 0 ||
                        selectedKabupatenItems.length === 0 ||
                        selectedSatuanPendidikanItems.length === 0 ||
                        userCount === 0;
                }

                renderAssessmentList(summary);
                renderAutoCombinationList(combinationDistribution.orderedCombinations);
                renderAutoCombinationDistribution(
                    combinationDistribution.rows,
                    combinationOptions,
                    selectedKabupatenItems,
                    selectedSatuanPendidikanItems
                );
            }

            function updateSidebarSummary() {
                const summary = getSelectedSummary();
                const selectedJabatanItems = getSelectedJabatanItems();
                const selectedKabupatenItems = getSelectedKabupatenItems();
                const selectedSatuanPendidikanItems = getSelectedSatuanPendidikanItems();
                const combinationOptions = getAvailableCombinationOptions();
                const assessmentCount = buildMetricValue(combinationOptions, 'total_assessments');
                const formCount = buildMetricValue(combinationOptions, 'total_forms');
                const fieldCount = buildMetricValue(combinationOptions, 'total_questions');
                const userCount = selectedSatuanPendidikanItems.reduce((total, item) => {
                    return total + Number((item.payload && item.payload.user_count) || 0);
                }, 0);
                const totalSessions = userCount > 0 ? Math.ceil(userCount / sessionCapacity) : 0;
                const durationHours = getSelectedDurationHours();
                const distributionMethod = userCount === 0
                    ? '-'
                    : ` ${userCount > batchThreshold ? 'Batch Job' : 'Langsung'}`;

                const summaryKetenagaan = document.getElementById('summary-ketenagaan');
                const summaryJabatan = document.getElementById('summary-jabatan');
                const summaryCombinationTitle = document.getElementById('summary-combination-title');
                const summaryKabupaten = document.getElementById('summary-kabupaten');
                const summarySatuanPendidikan = document.getElementById('summary-satuan-pendidikan');
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

                if (summaryJabatan) {
                    summaryJabatan.textContent = formatSelectedJabatanSummary(selectedJabatanItems);
                }

                if (summaryCombinationTitle) {
                    summaryCombinationTitle.textContent = combinationOptions.length > 0
                        ? `${combinationOptions.length} kombinasi / round robin`
                        : 'Belum tersedia';
                }

                if (summaryKabupaten) {
                    summaryKabupaten.textContent = formatSelectedKabupatenSummary(selectedKabupatenItems);
                }

                if (summarySatuanPendidikan) {
                    summarySatuanPendidikan.textContent = formatSelectedSatuanPendidikanSummary(selectedSatuanPendidikanItems);
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
                const jabatanSelector = getJabatanSelector();
                const kabupatenSelector = getKabupatenSelector();
                const satuanPendidikanSelector = getSatuanPendidikanSelector();
                const titleInput = document.querySelector('input[name="judul_penugasan"]');

                if (jabatanSelector) {
                    jabatanSelector.addEventListener('multiple-choice-table:change', function() {
                        syncKabupatenSelector();
                        syncSatuanPendidikanSelector();
                        refreshSummaries();
                    });
                }

                if (kabupatenSelector) {
                    kabupatenSelector.addEventListener('multiple-choice-table:change', function() {
                        syncSatuanPendidikanSelector();
                        refreshSummaries();
                    });
                }

                if (satuanPendidikanSelector) {
                    satuanPendidikanSelector.addEventListener('multiple-choice-table:change', refreshSummaries);
                }

                document.querySelectorAll('input[name="target_ketenagaan"]').forEach((input) => {
                    input.addEventListener('change', function() {
                        syncJabatanSelector();
                        syncKabupatenSelector();
                        syncSatuanPendidikanSelector();
                        refreshSummaries();
                    });
                });

                const durationSelect = document.getElementById('durasi_sesi_jam');
                const startTimeInput = document.getElementById('jam_mulai');

                if (titleInput) {
                    titleInput.addEventListener('input', refreshSummaries);
                }

                if (durationSelect) {
                    durationSelect.addEventListener('change', updateSidebarSummary);
                }

                if (startTimeInput) {
                    startTimeInput.addEventListener('change', updateSidebarSummary);
                }

                refreshSummaries();

                @if ($isEditMode)
                    const submitButton = document.getElementById('assignment-submit-button');
                    const confirmEditButton = document.getElementById('assignment-edit-confirm-button');
                    const editWarningModal = $('#assignmentEditWarningModal');

                    if (submitButton && assignmentForm) {
                        submitButton.addEventListener('click', function() {
                            if (submitButton.disabled) {
                                return;
                            }

                            editWarningModal.modal('show');
                        });
                    }

                    if (confirmEditButton && assignmentForm) {
                        confirmEditButton.addEventListener('click', function() {
                            confirmEditButton.disabled = true;

                            if (submitButton) {
                                submitButton.disabled = true;
                            }

                            assignmentForm.submit();
                        });
                    }
                @endif
            });
        })();
    </script>
@endpush
