@extends('layouts.app', ['title' => 'Edit Assessment'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Edit Assessment</h1>
                <div class="section-header-breadcrumb">
                    <form action="{{ route('assessment.preview.launch', $assessment->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-play-circle"></i> Lihat Preview
                        </button>
                    </form>
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
