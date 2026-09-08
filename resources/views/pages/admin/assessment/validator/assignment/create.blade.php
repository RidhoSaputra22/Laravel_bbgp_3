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

                @if ($forms->isEmpty() || $assessments->isEmpty() || $validators->isEmpty())
                    <div class="alert alert-warning">
                        <strong>Data belum lengkap.</strong>
                        @if ($forms->isEmpty())
                            Publikasikan minimal satu <a href="{{ route('assessment.validator.form.create') }}">form validator</a>.
                        @endif
                        @if ($assessments->isEmpty()) Belum ada assessment aktif. @endif
                        @if ($validators->isEmpty())
                            Belum ada akun dengan role <strong>Stakeholder</strong>, ketenagaan
                            <strong>Stakeholder</strong>, dan jabatan <strong>Validator</strong>.
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('assessment.validator.assignment.store') }}">
                    @csrf
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
                                <div class="form-group col-md-6">
                                    <label>Assessment yang Divalidasi <span class="text-danger">*</span></label>
                                    <select name="assessment_id" class="form-control select2" required>
                                        <option value="">-- Pilih Assessment --</option>
                                        @foreach ($assessments as $assessment)
                                            <option value="{{ $assessment->id }}" @selected((int) old('assessment_id') === $assessment->id)>
                                                {{ $assessment->kode_assessment }} — {{ $assessment->judul }}
                                                ({{ $assessment->forms_count }} form)
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Struktur assessment disalin sebagai snapshot saat penugasan dibuat.</small>
                                </div>
                                <div class="form-group col-md-6">
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
                                    <label>Validator <span class="text-danger">*</span></label>
                                    <select name="validator_user_id" class="form-control select2" required>
                                        <option value="">-- Pilih Validator yang Memenuhi Syarat --</option>
                                        @foreach ($validators as $validator)
                                            <option value="{{ $validator->id }}" @selected((int) old('validator_user_id') === $validator->id)>
                                                {{ $validator->guru?->nama_lengkap ?? $validator->name }}
                                                — {{ $validator->guru?->email ?? $validator->username }}
                                                — Stakeholder / Validator
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-success">
                                        <i class="fas fa-shield-alt"></i> Daftar difilter dan akan diverifikasi ulang saat disimpan.
                                    </small>
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
                                @disabled($forms->isEmpty() || $assessments->isEmpty() || $validators->isEmpty())>
                                <i class="fas fa-paper-plane"></i> Buat Penugasan Validator
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
