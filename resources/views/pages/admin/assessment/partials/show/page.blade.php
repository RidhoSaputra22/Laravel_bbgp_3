@extends('layouts.app', ['title' => 'Preview Assessment'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Preview Assessment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('assessment.edit', $assessment->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Struktur
                    </a>
                </div>
            </div>

            <div class="section-body">
                @include('pages.admin.assessment.partials.preview', ['assessment' => $assessment])
            </div>
        </section>
    </div>
@endsection
