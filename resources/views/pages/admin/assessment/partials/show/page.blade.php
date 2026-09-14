@extends('layouts.app', ['title' => ($isEvaluationPelaksanaan ?? false) ? 'Detail Bank Soal Evaluasi' : 'Preview Assessment'])

@php
    $isEvaluationPelaksanaan = $isEvaluationPelaksanaan ?? false;
    $assessmentRoutePrefix = $assessmentRoutePrefix ?? 'assessment';
@endphp

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ $isEvaluationPelaksanaan ? 'Detail Bank Soal Evaluasi Pelaksanaan' : 'Preview Assessment' }}</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route($assessmentRoutePrefix.'.index') }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route($assessmentRoutePrefix.'.edit', $assessment->id) }}" class="btn btn-warning">
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
