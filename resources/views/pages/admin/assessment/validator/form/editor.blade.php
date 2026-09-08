@extends('layouts.app', ['title' => $form->exists ? 'Edit Form Validator' : 'Buat Form Validator'])

@php
    $sections = old('sections', $builderData);
    if (empty($sections)) {
        $sections = [[
            'title' => 'Aspek Penilaian',
            'description' => '',
            'fields' => [[
                'label' => '',
                'description' => '',
                'field_type' => 'likert',
                'options_text' => "1\n2\n3\n4\n5",
                'is_required' => true,
                'is_scored' => true,
                'max_score' => 5,
                'is_active' => true,
            ]],
        ]];
    }
@endphp

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ $form->exists ? 'Edit Form Validator' : 'Buat Form Validator' }}</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.validator.form.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="section-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Form belum dapat disimpan.</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                    action="{{ $form->exists ? route('assessment.validator.form.update', $form) : route('assessment.validator.form.store') }}">
                    @csrf
                    @if ($form->exists) @method('PUT') @endif

                    <div class="card">
                        <div class="card-header"><h4>Identitas Form QA</h4></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>Kode Form</label>
                                    <input type="text" name="code" class="form-control"
                                        value="{{ old('code', $form->code) }}" placeholder="Otomatis jika dikosongkan">
                                </div>
                                <div class="form-group col-md-8">
                                    <label>Judul Form <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" required
                                        value="{{ old('title', $form->title) }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Deskripsi</label>
                                    <textarea name="description" class="form-control" rows="4">{{ old('description', $form->description) }}</textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Petunjuk Validator</label>
                                    <textarea name="instructions" class="form-control" rows="4">{{ old('instructions', $form->instructions) }}</textarea>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="draft" @selected(old('status', $form->status) === 'draft')>Draft</option>
                                        <option value="published" @selected(old('status', $form->status) === 'published')>Dipublikasikan</option>
                                        <option value="inactive" @selected(old('status', $form->status) === 'inactive')>Nonaktif</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4 d-flex align-items-end">
                                    <input type="hidden" name="is_active" value="0">
                                    <label class="custom-switch mb-2">
                                        <input type="checkbox" name="is_active" value="1" class="custom-switch-input"
                                            @checked((bool) old('is_active', $form->is_active ?? true))>
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description">Form aktif</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">Struktur Form Validator</h5>
                            <p class="text-muted mb-0">Kelompokkan indikator QA ke dalam beberapa aspek/bagian.</p>
                        </div>
                        <button type="button" class="btn btn-info" id="add-validator-section">
                            <i class="fas fa-plus"></i> Tambah Bagian
                        </button>
                    </div>

                    <div id="validator-sections">
                        @foreach ($sections as $sectionIndex => $section)
                            @include('pages.admin.assessment.validator.form.partials.section', [
                                'sectionIndex' => $sectionIndex,
                                'section' => $section,
                                'fieldTypes' => $fieldTypes,
                            ])
                        @endforeach
                    </div>

                    <div class="card">
                        <div class="card-body text-right">
                            <a href="{{ route('assessment.validator.form.index') }}" class="btn btn-light mr-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Form Validator
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <template id="validator-section-template">
        @include('pages.admin.assessment.validator.form.partials.section', [
            'sectionIndex' => '__SECTION__',
            'section' => ['title' => '', 'description' => '', 'fields' => []],
            'fieldTypes' => $fieldTypes,
        ])
    </template>
    <template id="validator-field-template">
        @include('pages.admin.assessment.validator.form.partials.field', [
            'sectionIndex' => '__SECTION__',
            'fieldIndex' => '__FIELD__',
            'field' => [
                'label' => '',
                'description' => '',
                'field_type' => 'likert',
                'options_text' => "1\n2\n3\n4\n5",
                'is_required' => true,
                'is_scored' => true,
                'max_score' => 5,
                'is_active' => true,
            ],
            'fieldTypes' => $fieldTypes,
        ])
    </template>
@endsection

@push('scripts')
    <script>
        $(function () {
            let sectionCounter = Date.now();
            let fieldCounter = Date.now();

            $('#add-validator-section').on('click', function () {
                const sectionIndex = sectionCounter++;
                const html = $('#validator-section-template').html().replaceAll('__SECTION__', sectionIndex);
                $('#validator-sections').append(html);
                addField($('#validator-sections .validator-section').last(), sectionIndex);
            });

            $(document).on('click', '.add-validator-field', function () {
                const section = $(this).closest('.validator-section');
                addField(section, section.data('section-index'));
            });

            $(document).on('click', '.remove-validator-field', function () {
                const fields = $(this).closest('.validator-fields');
                if (fields.children('.validator-field').length <= 1) {
                    alert('Setiap bagian wajib memiliki minimal satu butir.');
                    return;
                }
                $(this).closest('.validator-field').remove();
            });

            $(document).on('click', '.remove-validator-section', function () {
                if ($('#validator-sections .validator-section').length <= 1) {
                    alert('Form wajib memiliki minimal satu bagian.');
                    return;
                }
                $(this).closest('.validator-section').remove();
            });

            function addField(section, sectionIndex) {
                const html = $('#validator-field-template').html()
                    .replaceAll('__SECTION__', sectionIndex)
                    .replaceAll('__FIELD__', fieldCounter++);
                section.find('.validator-fields').append(html);
            }
        });
    </script>
@endpush
