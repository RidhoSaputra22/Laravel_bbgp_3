@extends('layouts.app', ['title' => 'Detail Kombinasi Soal'])

@section('content')
    @php
        $selectionAssessments = collect(data_get($combination->selection_config, 'assessments', []))->values();
        $selectionForms = collect(data_get($combination->selection_config, 'forms', []))->values();
        $sourceAssessments = collect($snapshot['assessments'] ?? [])->values();
    @endphp

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Kombinasi Soal</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.combination.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('assessment.combination.create') }}" class="btn btn-primary mr-2">
                        <i class="fas fa-random"></i> Buat Kombinasi Baru
                    </a>
                    @if ($combination->assignments->isEmpty())
                        <form action="{{ route('assessment.combination.hapus', $combination->id) }}" method="POST"
                            class="d-inline-block"
                            onsubmit="return confirm('Hapus kombinasi soal {{ $combination->judul }}?')">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Assessment Sumber</h4>
                                </div>
                                <div class="card-body">
                                    {{ $combination->total_assessments }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-copy"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Form</h4>
                                </div>
                                <div class="card-body">
                                    {{ $combination->total_forms }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Total Child Soal</h4>
                                </div>
                                <div class="card-body">
                                    {{ $combination->total_questions }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Dipakai Penugasan</h4>
                                </div>
                                <div class="card-body">
                                    {{ $combination->assignments->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Informasi Kombinasi</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Kode Kombinasi</div>
                                <div class="font-weight-bold">{{ $combination->kode_kombinasi }}</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Ketenagaan</div>
                                <div>
                                    <span class="badge badge-{{ $combination->target_ketenagaan_badge_class }}">
                                        {{ $combination->target_ketenagaan_label ?: '-' }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Dibuat Oleh</div>
                                <div>{{ optional($combination->generator)->name ?: 'Sistem' }}</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Waktu Generate</div>
                                <div>
                                    {{ optional($combination->generated_at ?: $combination->created_at)->format('d M Y H:i') }}
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Random Seed</div>
                                <div>{{ $combination->random_seed ?: '-' }}</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-muted small">Status</div>
                                <div>{{ $combination->is_active ? 'Aktif' : 'Nonaktif' }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted small">Judul</div>
                            <div class="font-weight-bold">{{ $combination->judul }}</div>
                        </div>

                        <div class="mb-0">
                            <div class="text-muted small">Deskripsi</div>
                            <div>{{ $combination->deskripsi ?: 'Tidak ada deskripsi tambahan.' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Konfigurasi Pengambilan Soal</h4>
                    </div>
                    <div class="card-body">
                        @if ($selectionAssessments->isNotEmpty())
                            @foreach ($selectionAssessments as $assessment)
                                @php
                                    $competencies = collect($assessment['competencies'] ?? [])->values();
                                    $autoIncludedForms = collect($assessment['auto_included_forms'] ?? [])->values();
                                @endphp

                                <div class="border rounded p-3 mb-4">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
                                        <div>
                                            <div class="font-weight-bold">
                                                {{ $assessment['assessment_title'] ?? '-' }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $assessment['assessment_code'] ?? '-' }}
                                                @if (!empty($assessment['instrument_label']))
                                                    | {{ $assessment['instrument_label'] }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-muted small mt-2 mt-md-0">
                                            {{ $assessment['selected_question_count'] ?? 0 }} child soal
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Kompetensi</th>
                                                    <th class="text-center">Pool Form</th>
                                                    <th class="text-center">Soal Tersedia</th>
                                                    <th class="text-center">Mode</th>
                                                    <th class="text-center">Diambil</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($competencies as $competency)
                                                    <tr>
                                                        <td>
                                                            <div class="font-weight-bold">
                                                                {{ $competency['kompetensi_label'] ?? '-' }}
                                                            </div>
                                                            @if (!empty($competency['forms']))
                                                                <small class="text-muted">
                                                                    {{ collect($competency['forms'])->pluck('form_title')->filter()->implode(', ') }}
                                                                </small>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            {{ $competency['available_form_count'] ?? 0 }}
                                                        </td>
                                                        <td class="text-center">
                                                            {{ $competency['available_question_count'] ?? 0 }}
                                                        </td>
                                                        <td class="text-center">
                                                            @php
                                                                $mode = $competency['selection_mode'] ?? 'count';
                                                            @endphp
                                                            <span class="badge badge-{{ $mode === 'all' ? 'info' : ($mode === 'unavailable' ? 'secondary' : 'primary') }}">
                                                                {{ $mode === 'all' ? 'Semua soal' : ($mode === 'unavailable' ? 'Tidak tersedia' : 'Jumlah soal') }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-primary">
                                                                {{ $competency['selected_question_count'] ?? 0 }} soal
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    @if ($autoIncludedForms->isNotEmpty())
                                        <div class="mt-3">
                                            <div class="font-weight-bold mb-2">Form Tanpa Kompetensi</div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Form</th>
                                                            <th class="text-center">Soal Aktif</th>
                                                            <th class="text-center">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($autoIncludedForms as $form)
                                                            <tr>
                                                                <td>
                                                                    <div class="font-weight-bold">{{ $form['form_title'] ?? '-' }}</div>
                                                                    <small class="text-muted">{{ $form['form_code'] ?? '-' }}</small>
                                                                </td>
                                                                <td class="text-center">
                                                                    {{ $form['selected_question_count'] ?? 0 }}
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-info">Semua ikut</span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @elseif ($selectionForms->isEmpty())
                            <div class="alert alert-warning mb-0">
                                Konfigurasi pengambilan soal tidak ditemukan pada kombinasi ini.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Assessment</th>
                                            <th>Form</th>
                                            <th class="text-center">Soal Aktif</th>
                                            <th class="text-center">Diambil</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($selectionForms as $form)
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold">{{ $form['assessment_title'] ?? '-' }}</div>
                                                    <small class="text-muted">{{ $form['assessment_code'] ?? '-' }}</small>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $form['form_title'] ?? '-' }}</div>
                                                    <small class="text-muted">{{ $form['form_code'] ?? '-' }}</small>
                                                </td>
                                                <td class="text-center">
                                                    {{ $form['available_question_count'] ?? 0 }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-primary">
                                                        {{ $form['requested_question_count'] ?? 0 }} soal
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="section-title">Preview Child Soal Per Form</div>
                @include('pages.admin.assessment.combination.partials.preview', [
                    'combination' => $combination,
                    'snapshot' => $snapshot,
                    'sourceAssessments' => $sourceAssessments,
                ])
            </div>
        </section>
    </div>
@endsection
