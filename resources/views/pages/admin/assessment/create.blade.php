@extends('layouts.app', ['title' => 'Tambah Assessment'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Tambah Assessment</h1>
            </div>

            <div class="section-body">
                @include('pages.admin.assessment.partials.form', [
                    'assessment' => $assessment,
                    'fieldTypes' => $fieldTypes,
                    'formBuilderData' => $formBuilderData,
                    'formAction' => route('assessment.store'),
                    'httpMethod' => 'POST',
                    'submitLabel' => 'Simpan Assessment',
                    'pageTitle' => 'Form Builder Assessment',
                ])
            </div>
        </section>
    </div>
@endsection
