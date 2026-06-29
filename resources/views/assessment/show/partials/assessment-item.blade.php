<div x-show="isCurrent({{ $assessmentItem['index'] }})" style="display: none;" class="space-y-6"
    data-assessment-panel="{{ $assessmentItem['index'] }}">
    @include('assessment.show.partials.assessment-overview-card', ['assessment' => $assessment])

    @foreach ($assessment['forms'] ?? [] as $form)
        @include('assessment.show.partials.assessment-form-card', ['form' => $form])
    @endforeach


</div>
