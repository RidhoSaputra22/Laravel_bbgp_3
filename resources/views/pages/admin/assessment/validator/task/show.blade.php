@extends('layouts.app', ['title' => 'Lembar Quality Assurance'])

@php
    $isLocked = $assignment->status === 'submitted';
    $isUpcoming = $assignment->start_date && $assignment->start_date->isFuture();
@endphp

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Lembar Quality Assurance</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.validator.task.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Daftar Tugas
                    </a>
                </div>
            </div>
            <div class="section-body">
                @if (session('validator_success'))
                    <div class="alert alert-success">{{ session('validator_success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Periksa kembali isian validasi.</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                @if ($isUpcoming)
                    <div class="alert alert-info">
                        Tugas ini baru dapat diisi mulai {{ $assignment->start_date->format('d-m-Y') }}.
                    </div>
                @endif
                @if ($isLocked)
                    <div class="alert alert-success">
                        <i class="fas fa-lock"></i>
                        Hasil dikirim pada {{ $assignment->submitted_at?->format('d-m-Y H:i') }} dan telah dikunci.
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <small class="text-muted">{{ $assignment->code }}</small>
                        <h3>{{ $assignment->title }}</h3>
                        <p class="mb-1"><strong>Form QA:</strong> {{ $assignment->validatorForm->title }}</p>
                        @if ($assignment->notes)<p class="mb-0"><strong>Catatan admin:</strong> {{ $assignment->notes }}</p>@endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-5">
                        <div class="card sticky-top" style="top: 85px;">
                            <div class="card-header"><h4>Snapshot Assessment yang Diperiksa</h4></div>
                            <div class="card-body" style="max-height: 72vh; overflow-y: auto;">
                                <small class="text-muted">{{ data_get($assignment->assessment_snapshot, 'code') }}</small>
                                <h5>{{ data_get($assignment->assessment_snapshot, 'title') }}</h5>
                                <p>{{ data_get($assignment->assessment_snapshot, 'description') }}</p>
                                @if (data_get($assignment->assessment_snapshot, 'instructions'))
                                    <div class="alert alert-light border">
                                        <strong>Petunjuk peserta</strong><br>
                                        {{ data_get($assignment->assessment_snapshot, 'instructions') }}
                                    </div>
                                @endif

                                @foreach (data_get($assignment->assessment_snapshot, 'forms', []) as $assessmentForm)
                                    <div class="border rounded p-3 mb-3">
                                        <strong>{{ $loop->iteration }}. {{ $assessmentForm['title'] ?? '-' }}</strong>
                                        @if (!empty($assessmentForm['description']))
                                            <small class="d-block text-muted mb-2">{{ $assessmentForm['description'] }}</small>
                                        @endif
                                        <ol class="pl-3 mb-0">
                                            @foreach (($assessmentForm['fields'] ?? []) as $assessmentField)
                                                <li class="mb-2">
                                                    {{ $assessmentField['label'] ?? '-' }}
                                                    <small class="d-block text-muted">
                                                        {{ $assessmentField['type'] ?? 'text' }}
                                                        {{ !empty($assessmentField['required']) ? ' · wajib' : '' }}
                                                        @if (!empty($assessmentField['options']))
                                                            · opsi: {{ is_array($assessmentField['options'])
                                                                ? collect($assessmentField['options'])->map(fn ($option) => is_scalar($option)
                                                                    ? (string) $option
                                                                    : (data_get($option, 'label') ?? data_get($option, 'text') ?? json_encode($option)))->implode(', ')
                                                                : $assessmentField['options'] }}
                                                        @endif
                                                    </small>
                                                </li>
                                            @endforeach
                                        </ol>
                                    </div>
                                @endforeach
                                <small class="text-muted">
                                    Snapshot disimpan saat penugasan dibuat:
                                    {{ data_get($assignment->assessment_snapshot, 'captured_at', '-') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-7">
                        <form method="POST" action="{{ route('assessment.validator.task.submit', $assignment) }}">
                            @csrf
                            @if ($assignment->validatorForm->instructions)
                                <div class="alert alert-info">{{ $assignment->validatorForm->instructions }}</div>
                            @endif

                            @foreach ($assignment->validatorForm->sections as $section)
                                <div class="card">
                                    <div class="card-header"><h4>{{ $loop->iteration }}. {{ $section->title }}</h4></div>
                                    <div class="card-body">
                                        @if ($section->description)<p class="text-muted">{{ $section->description }}</p>@endif
                                        @foreach ($section->fields->where('is_active', true) as $field)
                                            @php
                                                $response = $responseLookup->get($field->id);
                                                $oldValue = old(
                                                    'answers.'.$field->id,
                                                    $field->field_type === 'checkbox'
                                                        ? ($response?->answer_payload ?? [])
                                                        : $response?->answer_text
                                                );
                                                $inputName = 'answers['.$field->id.']';
                                            @endphp
                                            <div class="form-group border rounded p-3">
                                                <label class="font-weight-bold">
                                                    {{ $loop->iteration }}. {{ $field->label }}
                                                    @if ($field->is_required)<span class="text-danger">*</span>@endif
                                                </label>
                                                @if ($field->description)
                                                    <small class="d-block text-muted mb-3">{{ $field->description }}</small>
                                                @endif

                                                @switch($field->field_type)
                                                    @case('textarea')
                                                        <textarea name="{{ $inputName }}" class="form-control" rows="4"
                                                            @disabled($isLocked || $isUpcoming)>{{ $oldValue }}</textarea>
                                                        @break
                                                    @case('number')
                                                        <input type="number" step="0.01" min="0"
                                                            @if ($field->max_score) max="{{ $field->max_score }}" @endif
                                                            name="{{ $inputName }}" value="{{ $oldValue }}" class="form-control"
                                                            @disabled($isLocked || $isUpcoming)>
                                                        @break
                                                    @case('date')
                                                        <input type="date" name="{{ $inputName }}" value="{{ $oldValue }}"
                                                            class="form-control" @disabled($isLocked || $isUpcoming)>
                                                        @break
                                                    @case('select')
                                                        <select name="{{ $inputName }}" class="form-control" @disabled($isLocked || $isUpcoming)>
                                                            <option value="">-- Pilih Jawaban --</option>
                                                            @foreach ($field->resolvedOptions() as $option)
                                                                <option value="{{ $option }}" @selected((string) $oldValue === (string) $option)>
                                                                    {{ $option }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @break
                                                    @case('checkbox')
                                                        @foreach ($field->resolvedOptions() as $option)
                                                            <label class="custom-control custom-checkbox">
                                                                <input type="checkbox" class="custom-control-input"
                                                                    name="{{ $inputName }}[]" value="{{ $option }}"
                                                                    @checked(in_array((string) $option, array_map('strval', (array) $oldValue), true))
                                                                    @disabled($isLocked || $isUpcoming)>
                                                                <span class="custom-control-label">{{ $option }}</span>
                                                            </label>
                                                        @endforeach
                                                        @break
                                                    @case('radio')
                                                    @case('likert')
                                                        <div class="d-flex flex-wrap">
                                                            @foreach ($field->resolvedOptions() as $option)
                                                                <label class="btn btn-outline-primary mr-2 mb-2">
                                                                    <input type="radio" name="{{ $inputName }}" value="{{ $option }}"
                                                                        @checked((string) $oldValue === (string) $option)
                                                                        @disabled($isLocked || $isUpcoming)>
                                                                    {{ $option }}
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                        @break
                                                    @default
                                                        <input type="text" name="{{ $inputName }}" value="{{ $oldValue }}"
                                                            class="form-control" @disabled($isLocked || $isUpcoming)>
                                                @endswitch

                                                @if ($field->is_scored)
                                                    <small class="text-success">Skor maksimum: {{ $field->max_score }}</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <div class="card">
                                <div class="card-header"><h4>Kesimpulan Quality Assurance</h4></div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Rekomendasi Akhir <span class="text-danger">*</span></label>
                                        <select name="recommendation" class="form-control"
                                            @disabled($isLocked || $isUpcoming)>
                                            <option value="">-- Pilih Rekomendasi --</option>
                                            @foreach ($recommendations as $value => $label)
                                                <option value="{{ $value }}"
                                                    @selected(old('recommendation', $assignment->recommendation) === $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Catatan Akhir</label>
                                        <textarea name="final_notes" class="form-control" rows="5"
                                            @disabled($isLocked || $isUpcoming)>{{ old('final_notes', $assignment->final_notes) }}</textarea>
                                        <small class="text-muted">Wajib diisi jika assessment membutuhkan revisi atau belum layak.</small>
                                    </div>
                                </div>
                                @unless ($isLocked || $isUpcoming)
                                    <div class="card-footer text-right">
                                        <button type="submit"
                                            formaction="{{ route('assessment.validator.task.draft', $assignment) }}"
                                            class="btn btn-light mr-2">
                                            <i class="fas fa-save"></i> Simpan Draf
                                        </button>
                                        <button type="submit" class="btn btn-success"
                                            onclick="return confirm('Kirim hasil QA? Setelah dikirim hasil akan dikunci.')">
                                            <i class="fas fa-paper-plane"></i> Kirim Hasil QA
                                        </button>
                                    </div>
                                @endunless
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
