@php
    $isMultipleChoiceAssessment =
        ($assessment['instrument_type'] ?? null) === \App\Enum\AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value;
    $questionNumber = 1;
    $stageMeta = $stageMeta ?? [];
    $stageStatus = $stageMeta['status'] ?? 'in_progress';
    $showQuestions = (bool) ($stageMeta['show_questions'] ?? true);
    $isReadOnly = (bool) ($stageMeta['read_only'] ?? false);
    $stageTitle = trim((string) ($stageMeta['title'] ?? ($assessment['judul'] ?? 'Assessment')));
@endphp

<div x-show="isCurrent({{ $assessmentItem['index'] }})" style="display: none;" class="space-y-6"
    data-assessment-panel="{{ $assessmentItem['index'] }}">
    @include('assessment.show.partials.assessment-overview-card', ['assessment' => $assessment])

    @if (($stageMeta['is_locked'] ?? false) === true)
        <div class="rounded-sm border border-amber-200 bg-amber-50 px-5 py-5 text-sm text-amber-900">
            <div class="text-base font-semibold">Tahap ini masih terkunci</div>
            <p class="mt-2 leading-relaxed">
                {{ $stageMeta['lock_reason'] ?? 'Tahap ini masih dikunci admin dan baru tersedia setelah admin membukanya.' }}
            </p>
        </div>
    @elseif (($stageMeta['requires_start_button'] ?? false) === true)
        <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-5 py-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="text-base font-semibold text-slate-900">{{ $stageTitle }}</div>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Tahap ini menggunakan tombol mulai. Setelah dimulai, Anda langsung masuk ke isian tahap ini.
                    </p>
                </div>

                <form action="{{ route('assessment.portal.start', $target->id) }}" method="POST" class="shrink-0">
                    @csrf
                    <input type="hidden" name="stage_index" value="{{ $assessmentItem['index'] }}">
                    <x-assessment::ui.button type="submit" icon="fas fa-play-circle" class="font-bold">
                        Mulai Tahap {{ $assessmentItem['index'] + 1 }}
                    </x-assessment::ui.button>
                </form>
            </div>
        </div>
    @else
        @if ($isReadOnly)
            <div class="rounded-sm border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
                Tahap ini sudah disimpan permanen dan ditampilkan dalam mode baca.
            </div>
        @endif

        <fieldset @disabled($isReadOnly) class="space-y-6">
            @foreach ($assessment['forms'] ?? [] as $form)
                @include('assessment.show.partials.assessment-form-card', [
                    'form' => $form,
                    'assessmentIndex' => $assessmentItem['index'],
                    'questionNumberStart' => $questionNumber,
                    'hideFormHeader' => $isMultipleChoiceAssessment,
                    'displayQuestionPrefix' => $isMultipleChoiceAssessment ? 'Soal' : null,
                ])
                @php
                    $questionNumber += count($form['fields'] ?? []);
                @endphp
            @endforeach
        </fieldset>
    @endif
</div>
