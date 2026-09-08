@extends('layouts.app', ['title' => 'Form Validator Assessment'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Form Validator</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.validator.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Panel
                    </a>
                    <a href="{{ route('assessment.validator.form.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Form Validator
                    </a>
                </div>
            </div>
            <div class="section-body">
                @if (session('validator_success'))
                    <div class="alert alert-success">{{ session('validator_success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="card">
                    <div class="card-header"><h4>Daftar Form QA</h4></div>
                    <div class="card-body">
                        @if ($forms->isEmpty())
                            <div class="empty-state" data-height="300">
                                <div class="empty-state-icon bg-primary"><i class="fas fa-clipboard-check"></i></div>
                                <h2>Belum ada form validator</h2>
                                <p class="lead">Buat form khusus QA sebelum menugaskan validator.</p>
                                <a href="{{ route('assessment.validator.form.create') }}" class="btn btn-primary mt-3">
                                    Buat Form Validator
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Form</th>
                                            <th>Status</th>
                                            <th>Struktur</th>
                                            <th>Penugasan</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($forms as $form)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <strong>{{ $form->title }}</strong>
                                                    <small class="d-block text-muted">{{ $form->code }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $form->status_badge_class }}">{{ $form->status_label }}</span>
                                                    <span class="badge badge-{{ $form->is_active ? 'primary' : 'light' }}">
                                                        {{ $form->is_active ? 'Aktif' : 'Nonaktif' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $form->sections_count }} bagian /
                                                    {{ $form->sections->sum(fn ($section) => $section->fields->count()) }} butir
                                                </td>
                                                <td>{{ $form->assignments_count }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('assessment.validator.form.show', $form) }}"
                                                        class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                                    @if ($form->assignments_count === 0)
                                                        <a href="{{ route('assessment.validator.form.edit', $form) }}"
                                                            class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                                        <form method="POST"
                                                            action="{{ route('assessment.validator.form.destroy', $form) }}"
                                                            class="d-inline"
                                                            onsubmit="return confirm('Hapus form validator ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                    @else
                                                        <span class="btn btn-light btn-sm disabled" title="Form terkunci setelah ditugaskan">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
