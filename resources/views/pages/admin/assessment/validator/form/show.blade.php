@extends('layouts.app', ['title' => 'Detail Form Validator'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Form Validator</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.validator.form.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Daftar Form
                    </a>
                    @if ($form->assignments_count === 0)
                        <a href="{{ route('assessment.validator.form.edit', $form) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                </div>
            </div>
            <div class="section-body">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between flex-wrap">
                            <div>
                                <small class="text-muted">{{ $form->code }}</small>
                                <h3>{{ $form->title }}</h3>
                                <p class="text-muted mb-2">{{ $form->description ?: 'Tanpa deskripsi.' }}</p>
                            </div>
                            <div>
                                <span class="badge badge-{{ $form->status_badge_class }}">{{ $form->status_label }}</span>
                                @if ($form->assignments_count > 0)
                                    <span class="badge badge-light"><i class="fas fa-lock"></i> Terkunci</span>
                                @endif
                            </div>
                        </div>
                        @if ($form->instructions)
                            <div class="alert alert-info mt-3 mb-0">{{ $form->instructions }}</div>
                        @endif
                    </div>
                </div>

                @foreach ($form->sections as $section)
                    <div class="card">
                        <div class="card-header"><h4>{{ $loop->iteration }}. {{ $section->title }}</h4></div>
                        <div class="card-body">
                            @if ($section->description)<p class="text-muted">{{ $section->description }}</p>@endif
                            @foreach ($section->fields as $field)
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $loop->iteration }}. {{ $field->label }}</strong>
                                        <div>
                                            <span class="badge badge-light">{{ App\Models\ValidatorFormField::TYPES[$field->field_type] ?? $field->field_type }}</span>
                                            @if ($field->is_required)<span class="badge badge-danger">Wajib</span>@endif
                                            @if ($field->is_scored)<span class="badge badge-success">Skor maks. {{ $field->max_score }}</span>@endif
                                        </div>
                                    </div>
                                    @if ($field->description)<small class="text-muted d-block mt-1">{{ $field->description }}</small>@endif
                                    @if ($field->resolvedOptions())
                                        <small class="d-block mt-2">Opsi: {{ implode(' · ', $field->resolvedOptions()) }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
