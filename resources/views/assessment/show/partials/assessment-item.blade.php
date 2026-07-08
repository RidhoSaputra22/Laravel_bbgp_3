@php
    $isMultipleChoiceAssessment =
        ($assessment['instrument_type'] ?? null) === \App\Enum\AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value;
    $questionNumber = 1;
@endphp

<div x-show="isCurrent({{ $assessmentItem['index'] }})" style="display: none;" class="space-y-6"
    data-assessment-panel="{{ $assessmentItem['index'] }}">
    @include('assessment.show.partials.assessment-overview-card', ['assessment' => $assessment])

    @foreach ($assessment['forms'] ?? [] as $form)
        @include('assessment.show.partials.assessment-form-card', [
            'form' => $form,
            'assessmentIndex' => $assessmentItem['index'],
            'questionNumberStart' => $questionNumber,
            'hideFormHeader' => $isMultipleChoiceAssessment,
            'displayQuestionPrefix' => $isMultipleChoiceAssessment ? 'Soal' : null,
        ])
        @php($questionNumber += count($form['fields'] ?? []))
    @endforeach

   


</div>
