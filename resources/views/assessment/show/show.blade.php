@extends('assessment.layouts.app')

@section('content')
    @php
        $snapshot = $attempt->structure_snapshot ?? [];
        $answerLookup = $answerLookup ?? [];
        $assessmentItems = collect($snapshot['assessments'] ?? [])
            ->values()
            ->map(function ($assessment, $index) {
                $forms = collect($assessment['forms'] ?? [])->values();
                $fieldIds = $forms
                    ->flatMap(fn($form) => collect($form['fields'] ?? [])->pluck('id'))
                    ->map(fn($id) => (int) $id)
                    ->filter(fn($id) => $id > 0)
                    ->values()
                    ->all();

                return [
                    'index' => $index,
                    'data' => $assessment,
                    'form_count' => $forms->count(),
                    'question_count' => $forms->sum(fn($form) => count($form['fields'] ?? [])),
                    'field_ids' => $fieldIds,
                ];
            })
            ->all();
        $assessmentCount = count($assessmentItems);
        $totalQuestions = (int) data_get($snapshot, 'meta.total_questions', 0);
        $requiredQuestions = (int) data_get($snapshot, 'meta.required_questions', 0);
        $session = $target->session;
        $sessionStartAt = $session?->waktu_mulai;
        $sessionEndAt = $session?->waktu_selesai;
        $attemptStartedAt = $attempt->started_at ?? $target->started_at;
        $countdownTargetAt = \App\Support\Assessment\AssessmentTargetTiming::resolveDeadlineAt($target);
        $durationMinutes = \App\Support\Assessment\AssessmentTargetTiming::resolveDurationMinutes($target);
        $durationLabel = $durationMinutes
            ? collect([
                intdiv($durationMinutes, 60) > 0 ? intdiv($durationMinutes, 60) . ' jam' : null,
                $durationMinutes % 60 > 0 ? $durationMinutes % 60 . ' menit' : null,
            ])->filter()->implode(' ')
            : 'Tanpa batas durasi';
        $countdownTitle = 'Sisa Waktu Pengerjaan';
        $countdownCaption = 'Timer dimulai saat peserta menekan tombol Mulai Ujian dan mengikuti durasi penugasan yang tersimpan.';
        $formatDateTime = fn($value) => $value ? $value->format('d M Y H:i') . ' WITA' : '-';
        $sessionDetails = [
            [
                'label' => 'Label Sesi',
                'value' => $meta['session_label'],
            ],
            [
                'label' => 'Jadwal Sesi',
                'value' => $meta['session_schedule_text'],
            ],
            [
                'label' => 'Mulai Sesi',
                'value' => $formatDateTime($sessionStartAt),
            ],
            [
                'label' => 'Durasi Pengerjaan',
                'value' => $durationLabel,
            ],
            [
                'label' => 'Batas Selesai',
                'value' => $countdownTargetAt
                    ? $formatDateTime($countdownTargetAt)
                    : 'Tanpa batas waktu',
            ],
            [
                'label' => 'Mulai Dikerjakan',
                'value' => $formatDateTime($attemptStartedAt),
            ],
            [
                'label' => 'Status',
                'value' => $meta['label'],
            ],
            [
                'label' => 'Periode Penugasan',
                'value' => $meta['date_text'],
            ],
        ];
        $assessmentIndexByFieldId = [];

        foreach ($assessmentItems as $assessmentItem) {
            foreach ($assessmentItem['data']['forms'] ?? [] as $form) {
                foreach ($form['fields'] ?? [] as $field) {
                    $assessmentIndexByFieldId[(int) $field['id']] = $assessmentItem['index'];
                }
            }
        }

        $assessmentNavigationItems = collect($assessmentItems)
            ->map(fn($assessmentItem) => [
                'index' => $assessmentItem['index'],
                'form_count' => $assessmentItem['form_count'],
                'question_count' => $assessmentItem['question_count'],
                'field_ids' => $assessmentItem['field_ids'],
            ])
            ->values()
            ->all();

        $errorFieldKey = collect(array_keys($errors->getMessages()))->first(
            fn($key) => str_starts_with($key, 'answers.'),
        );
        $errorFieldId = null;

        if (is_string($errorFieldKey) && preg_match('/^answers\.(\d+)(?:\.|$)/', $errorFieldKey, $matches) === 1) {
            $errorFieldId = (int) $matches[1];
        }

        $errorAssessmentIndex = $errorFieldId !== null ? $assessmentIndexByFieldId[$errorFieldId] ?? null : null;
        $oldActiveAssessmentIndex = old('active_assessment_index');
        $initialAssessmentIndex =
            $assessmentCount > 0
                ? max(
                    0,
                    min(
                        $assessmentCount - 1,
                        is_numeric($errorAssessmentIndex)
                            ? (int) $errorAssessmentIndex
                            : (is_numeric($oldActiveAssessmentIndex)
                                ? (int) $oldActiveAssessmentIndex
                                : 0),
                    ),
                )
                : 0;
    @endphp

    @include('assessment.show.partials.portal-header', ['guru' => $guru])

    <div x-data="assessmentExamFlow({
        initialIndex: {{ $initialAssessmentIndex }},
        totalAssessments: {{ $assessmentCount }},
        assessmentItems: @js($assessmentNavigationItems),
        autosaveUrl: @js(route('assessment.portal.autosave', $target->id)),
        resultUrl: @js(route('assessment.portal.result', $target->id)),
        deadlineAt: @js(optional($countdownTargetAt)->toIso8601String()),
    })" class="space-y-6 **:text-xs sm:**:text-sm">
        <section class="grid gap-8 p-6 grid-cols-1 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)] md:gap-10 md:p-14">
            <div class="space-y-8 md:space-y-12" x-ref="assessmentFlowTop">
                <form id="assessment-exam-form" x-ref="assessmentExamForm"
                    action="{{ route('assessment.portal.submit', $target->id) }}" method="POST"
                    enctype="multipart/form-data" novalidate @submit.prevent="handleSubmit($event)">
                    @csrf
                    <input type="hidden" name="active_assessment_index" x-model="currentAssessmentIndex">

                    @if ($assessmentCount === 0)
                        @include('assessment.show.partials.empty-state')
                    @endif

                    @foreach ($assessmentItems as $assessmentItem)
                        @include('assessment.show.partials.assessment-item', [
                            'assessmentItem' => $assessmentItem,
                            'assessment' => $assessmentItem['data'],
                        ])
                    @endforeach

                    @if ($assessmentCount > 0)
                        @include('assessment.show.partials.finish-modal', [
                            'assessmentCount' => $assessmentCount,
                            'totalQuestions' => $totalQuestions,
                            'requiredQuestions' => $requiredQuestions,
                        ])
                    @endif
                </form>
            </div>

                @include('assessment.show.partials.session-sidebar', [
                    'assessmentCount' => $assessmentCount,
                    'meta' => $meta,
                    'countdownTitle' => $countdownTitle,
                    'countdownTargetAt' => $countdownTargetAt,
                    'countdownCaption' => $countdownCaption,
                    'sessionDetails' => $sessionDetails,
                ])

        </section>

        @include('assessment.show.partials.session-bottom-nav', [
            'assessmentCount' => $assessmentCount,
            'meta' => $meta,
            'countdownTitle' => $countdownTitle,
            'countdownTargetAt' => $countdownTargetAt,
            'countdownCaption' => $countdownCaption,
            'sessionDetails' => $sessionDetails,
        ])

    </div>

    @include('assessment.show.partials.scripts')
@endsection
