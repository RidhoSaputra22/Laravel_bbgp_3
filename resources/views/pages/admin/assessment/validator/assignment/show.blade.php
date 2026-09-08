@extends('layouts.app', ['title' => 'Detail Penugasan Validator'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Penugasan Validator</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.validator.assignment.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Daftar Penugasan
                    </a>
                </div>
            </div>
            <div class="section-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <small class="text-muted">{{ $assignment->code }}</small>
                                <h3>{{ $assignment->title }}</h3>
                                <p class="mb-1"><strong>Assessment:</strong> {{ data_get($assignment->assessment_snapshot, 'title') }}</p>
                                <p class="mb-1"><strong>Form QA:</strong> {{ $assignment->validatorForm->title }}</p>
                                <p class="mb-0">
                                    <strong>Validator:</strong>
                                    {{ $assignment->validator?->guru?->nama_lengkap ?? $assignment->validator?->name ?? data_get($assignment->validator_snapshot, 'name', '-') }}
                                    <span class="badge badge-warning">Stakeholder / Validator</span>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                <span class="badge badge-{{ $assignment->status_badge_class }} p-2">
                                    {{ $assignment->status_label }}
                                </span>
                                <small class="d-block text-muted mt-2">
                                    {{ $assignment->start_date?->format('d-m-Y') ?? '-' }}
                                    s/d {{ $assignment->due_date?->format('d-m-Y') ?? '-' }}
                                </small>
                            </div>
                        </div>
                        @if ($assignment->notes)
                            <div class="alert alert-light border mt-3 mb-0">{{ $assignment->notes }}</div>
                        @endif
                    </div>
                </div>

                @if ($assignment->status === 'submitted')
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card card-statistic-1">
                                <div class="card-icon bg-success"><i class="fas fa-star"></i></div>
                                <div class="card-wrap">
                                    <div class="card-header"><h4>Skor QA</h4></div>
                                    <div class="card-body">
                                        {{ $assignment->score_percentage !== null ? number_format($assignment->score_percentage, 2).'%' : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">
                                    <strong>Rekomendasi Akhir</strong>
                                    <h5 class="mt-2">{{ $assignment->recommendation_label }}</h5>
                                    <p class="mb-0">{{ $assignment->final_notes ?: 'Tidak ada catatan akhir.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @foreach ($assignment->validatorForm->sections as $section)
                    <div class="card">
                        <div class="card-header"><h4>{{ $loop->iteration }}. {{ $section->title }}</h4></div>
                        <div class="card-body">
                            @foreach ($section->fields->where('is_active', true) as $field)
                                @php($response = $responseLookup->get($field->id))
                                <div class="border rounded p-3 mb-3">
                                    <strong>{{ $loop->iteration }}. {{ $field->label }}</strong>
                                    @if ($field->description)<small class="d-block text-muted">{{ $field->description }}</small>@endif
                                    <div class="mt-2">
                                        <span class="text-muted">Jawaban:</span>
                                        <strong>{{ $response?->answer_text ?? 'Belum diisi' }}</strong>
                                        @if ($response?->score !== null)
                                            <span class="badge badge-success ml-2">
                                                {{ $response->score }} / {{ $field->max_score }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
