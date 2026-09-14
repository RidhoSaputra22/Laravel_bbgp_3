@extends('layouts.app', ['title' => ($isEvaluationPelaksanaan ?? false) ? 'Tambah Bank Soal Evaluasi' : 'Tambah Assessment'])

@php
    $isEvaluationPelaksanaan = $isEvaluationPelaksanaan ?? false;
    $assessmentRoutePrefix = $assessmentRoutePrefix ?? 'assessment';
@endphp

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>{{ $isEvaluationPelaksanaan ? 'Tambah Bank Soal Evaluasi Pelaksanaan' : 'Tambah Assessment' }}</h1>
            </div>

            <div class="section-body">
                @include('pages.admin.assessment.partials.form', [
                    'assessment' => $assessment,
                    'fieldTypes' => $fieldTypes,
                    'formBuilderData' => $formBuilderData,
                    'formAction' => route($assessmentRoutePrefix.'.store'),
                    'httpMethod' => 'POST',
                    'submitLabel' => $isEvaluationPelaksanaan ? 'Simpan Bank Soal' : 'Buat Assessment',
                    'pageTitle' => $isEvaluationPelaksanaan ? 'Form Builder Evaluasi Pelaksanaan' : 'Form Builder Assessment',
                    'assessmentRoutePrefix' => $assessmentRoutePrefix,
                    'isEvaluationPelaksanaan' => $isEvaluationPelaksanaan,
                ])
            </div>
        </section>
    </div>
@endsection
