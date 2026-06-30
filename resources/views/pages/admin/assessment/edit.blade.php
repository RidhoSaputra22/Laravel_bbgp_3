@extends('layouts.app', ['title' => 'Edit Assessment'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Assessment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.show', $assessment->id) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> Lihat Preview
                    </a>
                </div>
            </div>

            <div class="section-body">
                @include('pages.admin.assessment.partials.form', [
                    'assessment' => $assessment,
                    'fieldTypes' => $fieldTypes,
                    'formBuilderData' => $formBuilderData,
                    'formAction' => route('assessment.update', $assessment->id),
                    'httpMethod' => 'PUT',
                    'submitLabel' => 'Edit Assessment',
                    'pageTitle' => 'Edit Struktur Assessment',
                ])
            </div>
        </section>
    </div>
@endsection
