@extends('layouts.app', ['title' => ($isEvaluationPelaksanaan ?? false) ? 'Edit Bank Soal Evaluasi' : 'Edit Assessment'])

@php
    $isEvaluationPelaksanaan = $isEvaluationPelaksanaan ?? false;
    $assessmentRoutePrefix = $assessmentRoutePrefix ?? 'assessment';
@endphp

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ $isEvaluationPelaksanaan ? 'Edit Bank Soal Evaluasi Pelaksanaan' : 'Edit Assessment' }}</h1>
            </div>

            <div class="section-body">
                @include('pages.admin.assessment.partials.form', [
                    'assessment' => $assessment,
                    'fieldTypes' => $fieldTypes,
                    'formBuilderData' => $formBuilderData,
                    'formAction' => route($assessmentRoutePrefix.'.update', $assessment->id),
                    'httpMethod' => 'PUT',
                    'submitLabel' => $isEvaluationPelaksanaan ? 'Simpan Perubahan' : 'Edit Assessment',
                    'pageTitle' => $isEvaluationPelaksanaan ? 'Edit Struktur Evaluasi Pelaksanaan' : 'Edit Struktur Assessment',
                    'assessmentRoutePrefix' => $assessmentRoutePrefix,
                    'isEvaluationPelaksanaan' => $isEvaluationPelaksanaan,
                ])
            </div>
        </section>
    </div>
@endsection
