@extends('layouts.app', ['title' => 'Buat Penugasan Validator'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Buat Penugasan Validator</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.validator.assignment.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="section-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Penugasan belum dapat dibuat.</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                @if ($forms->isEmpty() || $assessmentAssignments->isEmpty() || $validators->isEmpty())
                    <div class="alert alert-warning">
                        <strong>Data belum lengkap.</strong>
                        @if ($forms->isEmpty())
                            Publikasikan minimal satu <a href="{{ route('assessment.validator.form.create') }}">form validator</a>.
                        @endif
                        @if ($assessmentAssignments->isEmpty())
                            Belum ada penugasan assessment aktif untuk Tenaga Pendidik atau Tenaga Kependidikan.
                        @endif
                        @if ($validators->isEmpty())
                            Belum ada akun dengan role <strong>Stakeholder</strong>, ketenagaan
                            <strong>Stakeholder</strong>, dan jabatan <strong>Validator</strong>.
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('assessment.validator.assignment.store') }}">
                    @csrf
                    @php
                        $defaultSourceMode = $combinationAssessmentAssignments->isNotEmpty() ? 'combination' : 'all_forms';
                        $selectedSourceMode = old('source_mode', $defaultSourceMode);
                    @endphp
                    <div class="card">
                        <div class="card-header"><h4>Detail Penugasan QA</h4></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Judul Penugasan <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" required
                                        value="{{ old('title') }}"
                                        placeholder="Contoh: QA Instrumen Pemetaan Kompetensi Guru 2026">
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Form Validator <span class="text-danger">*</span></label>
                                    <select name="validator_form_id" class="form-control select2" required>
                                        <option value="">-- Pilih Form QA --</option>
                                        @foreach ($forms as $form)
                                            <option value="{{ $form->id }}" @selected((int) old('validator_form_id') === $form->id)>
                                                {{ $form->code }} — {{ $form->title }} ({{ $form->sections_count }} bagian)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Sumber Soal <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="source-mode-combination" name="source_mode"
                                                    value="combination" class="custom-control-input"
                                                    @checked($selectedSourceMode === 'combination')
                                                    @disabled($combinationAssessmentAssignments->isEmpty())>
                                                <label class="custom-control-label" for="source-mode-combination">
                                                    Kombinasi soal
                                                </label>
                                                <small class="form-text text-muted">
                                                    Ambil soal sesuai kombinasi aktif yang digunakan pada target peserta
                                                    ({{ $combinationAssessmentAssignments->count() }} penugasan tersedia).
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="source-mode-all-forms" name="source_mode"
                                                    value="all_forms" class="custom-control-input"
                                                    @checked($selectedSourceMode === 'all_forms')>
                                                <label class="custom-control-label" for="source-mode-all-forms">
                                                    Seluruh form terkait
                                                </label>
                                                <small class="form-text text-muted">
                                                    Ambil seluruh form dan soal dari assessment yang terhubung
                                                    ({{ $assessmentAssignments->count() }} penugasan tersedia).
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Tanggal Mulai</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Batas Waktu</label>
                                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Catatan untuk Validator</label>
                                    <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ route('assessment.validator.assignment.index') }}" class="btn btn-light mr-2">Batal</a>
                            <button class="btn btn-primary"
                                @disabled($forms->isEmpty() || $assessmentAssignments->isEmpty() || $validators->isEmpty())>
                                <i class="fas fa-paper-plane"></i> Buat Penugasan Validator
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
