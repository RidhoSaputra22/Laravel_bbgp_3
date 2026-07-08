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

     @if ($assessmentCount > 0)
            <div class="flex flex-col gap-4 mt-2">


                <div class="flex flex-col gap-3 sm:flex-row justify-end">
                    <x-assessment::ui.button type="button" variant="outline" icon="fas fa-arrow-left"
                        x-show="!isFirstAssessment()" x-bind:disabled="isBusy()"
                        @click="goToAssessment(currentAssessmentIndex - 1)">
                        Assessment Sebelumnya
                    </x-assessment::ui.button>

                    <x-assessment::ui.button type="button" icon="fas fa-flag-checkered" x-show="isLastAssessment()"
                        x-bind:disabled="isBusy()" @click="openFinishModal()">
                        Selesai Assessment
                    </x-assessment::ui.button>

                    <x-assessment::ui.button type="button" icon="fas fa-arrow-right" x-show="!isLastAssessment()"
                        x-bind:disabled="isBusy()" @click="goToAssessment(currentAssessmentIndex + 1)">
                        Next Assessment
                    </x-assessment::ui.button>
                </div>
            </div>
        @endif


</div>
