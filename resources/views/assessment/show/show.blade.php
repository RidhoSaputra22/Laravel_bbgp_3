@extends('assessment.layouts.app')

@section('content')
    @php
        $snapshot = $attempt->structure_snapshot ?? [];
        $stageProgress = \App\Support\Assessment\AssessmentStageProgress::normalize(
            $attempt->progress_snapshot,
            $snapshot
        );
        $stageFlowEnabled = \App\Support\Assessment\AssessmentStageProgress::usesStageFlow(
            $snapshot,
            $stageProgress
        );
        $answerLookup = $answerLookup ?? [];
        $participantAutoFillResolver = app(\App\Support\Assessment\ParticipantAutoFillResolver::class);
        $autoFilledAnswerLookup = [];

        foreach (($snapshot['assessments'] ?? []) as $assessmentEntry) {
            foreach (($assessmentEntry['forms'] ?? []) as $formEntry) {
                foreach (($formEntry['fields'] ?? []) as $fieldEntry) {
                    $fieldId = (int) ($fieldEntry['id'] ?? 0);

                    if ($fieldId < 1 || array_key_exists($fieldId, $answerLookup)) {
                        continue;
                    }

                    $resolvedAutoFill = $participantAutoFillResolver->resolveForField($fieldEntry, $guru);

                    if (! $resolvedAutoFill) {
                        continue;
                    }

                    $autoFilledAnswerLookup[$fieldId] = array_merge(
                        $resolvedAutoFill['answer'] ?? [],
                        [
                            'autofill_source' => $resolvedAutoFill['source'] ?? null,
                            'autofill_source_label' => $resolvedAutoFill['source_label'] ?? null,
                        ],
                    );
                }
            }
        }

        $answerLookup = array_replace($autoFilledAnswerLookup, $answerLookup);
        $buildDisplayFieldLabel = static function (
            array $field,
            ?int $displayQuestionNumber = null,
            ?string $displayQuestionPrefix = null
        ): string {
            $fieldLabel = trim((string) ($field['label'] ?? ''));

            if (! $displayQuestionNumber || $fieldLabel === '') {
                return $fieldLabel;
            }

            $normalizedLabel = preg_replace(
                '/^\s*(?:soal\s*)?\d+\s*[\.\)\-:]?\s*/iu',
                '',
                $fieldLabel,
                1
            ) ?? $fieldLabel;
            $displayLead = filled($displayQuestionPrefix)
                ? trim($displayQuestionPrefix).' '.$displayQuestionNumber
                : (string) $displayQuestionNumber;

            return trim($displayLead.($normalizedLabel !== '' ? '. '.trim($normalizedLabel) : ''));
        };
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
        $stageMetaByIndex = collect($assessmentItems)
            ->mapWithKeys(function (array $assessmentItem) use ($stageFlowEnabled, $stageProgress, $securityPayload) {
                $index = (int) ($assessmentItem['index'] ?? 0);
                $stage = $stageFlowEnabled
                    ? \App\Support\Assessment\AssessmentStageProgress::stage($stageProgress, $index)
                    : null;
                $config = $stageFlowEnabled
                    ? \App\Support\Assessment\AssessmentStageProgress::stageConfig($stageProgress, $index)
                    : [
                        'enabled' => false,
                        'entry_mode' => 'direct',
                        'allow_draft' => false,
                        'finalize_mode' => 'manual',
                        'lock_until_previous_stages_completed' => false,
                        'time_limit_minutes' => null,
                        'security' => [
                            'enabled' => (bool) data_get($securityPayload ?? [], 'enabled', false),
                            'require_fullscreen' => (bool) data_get($securityPayload ?? [], 'requireFullscreen', false),
                        ],
                    ];
                $status = $stageFlowEnabled
                    ? ($stage['status'] ?? \App\Support\Assessment\AssessmentStageProgress::STATUS_READY)
                    : 'in_progress';
                $isLocked = $status === \App\Support\Assessment\AssessmentStageProgress::STATUS_LOCKED;
                $requiresStartButton = $stageFlowEnabled
                    && $status === \App\Support\Assessment\AssessmentStageProgress::STATUS_READY
                    && ($config['entry_mode'] ?? null) === \App\Support\Assessment\AssessmentStageConfig::ENTRY_START_BUTTON;
                $isSubmitted = $status === \App\Support\Assessment\AssessmentStageProgress::STATUS_SUBMITTED;
                $isInteractive = ! $isLocked && ! $requiresStartButton && ! $isSubmitted;

                return [
                    $index => [
                        'index' => $index,
                        'status' => $status,
                        'title' => trim((string) data_get($assessmentItem, 'data.judul')) ?: 'Assessment '.($index + 1),
                        'can_access' => ! $isLocked,
                        'is_locked' => $isLocked,
                        'requires_start_button' => $requiresStartButton,
                        'allow_draft' => (bool) ($config['allow_draft'] ?? false),
                        'finalize_mode' => (string) ($config['finalize_mode'] ?? 'manual'),
                        'time_limit_minutes' => $config['time_limit_minutes'],
                        'security_enabled' => (bool) data_get($config, 'security.enabled', false),
                        'require_fullscreen' => (bool) data_get($config, 'security.require_fullscreen', false),
                        'security_max_serious_violations' => (int) data_get(
                            $config,
                            'security.max_serious_violations',
                            3
                        ),
                        'security_temporary_lock_seconds' => (int) data_get(
                            $config,
                            'security.temporary_lock_seconds',
                            2
                        ),
                        'security_fullscreen_grace_seconds' => (int) data_get(
                            $config,
                            'security.fullscreen_grace_seconds',
                            10
                        ),
                        'read_only' => $isSubmitted,
                        'show_questions' => ! $isLocked && ! $requiresStartButton,
                        'is_interactive' => $isInteractive,
                        'can_switch_away' => ! (
                            $stageFlowEnabled
                            && $status === \App\Support\Assessment\AssessmentStageProgress::STATUS_IN_PROGRESS
                            && (bool) data_get($config, 'security.enabled', false)
                        ),
                        'started_at' => $stage['started_at'] ?? null,
                        'submitted_at' => $stage['submitted_at'] ?? null,
                        'lock_reason' => $isLocked
                            ? \App\Support\Assessment\AssessmentStageProgress::lockReason($stageProgress, $index)
                            : null,
                    ],
                ];
            })
            ->all();
        $rawFlaggedFieldIds = old('flagged_field_ids', data_get($snapshot, 'meta.flagged_field_ids', []));
        $initialFlaggedFieldIds = collect(\Illuminate\Support\Arr::wrap($rawFlaggedFieldIds))
            ->map(fn($fieldId) => (int) $fieldId)
            ->filter(fn($fieldId) => $fieldId > 0)
            ->unique()
            ->values()
            ->all();
        $assessmentCount = count($assessmentItems);
        $totalQuestions = (int) data_get($snapshot, 'meta.total_questions', 0);
        $requiredQuestions = (int) data_get($snapshot, 'meta.required_questions', 0);
        $session = $target->session;
        $sessionEnabled = $target->assignment->usesSessionScheduling();
        $sessionStartAt = $session?->waktu_mulai;
        $sessionEndAt = $session?->waktu_selesai;
        $assignmentStartAt = \App\Support\Assessment\AssessmentTargetTiming::resolveAssignmentStartAt($target->assignment);
        $attemptStartedAt = $attempt->started_at ?? $target->started_at;
        $countdownTargetAt = \App\Support\Assessment\AssessmentTargetTiming::resolveDeadlineAt($target);
        $durationMinutes = \App\Support\Assessment\AssessmentTargetTiming::resolveDurationMinutes($target);
        $durationLabel = $durationMinutes
            ? collect([
                intdiv($durationMinutes, 60) > 0 ? intdiv($durationMinutes, 60) . ' jam' : null,
                $durationMinutes % 60 > 0 ? $durationMinutes % 60 . ' menit' : null,
            ])->filter()->implode(' ')
            : ($stageFlowEnabled ? 'Tanpa timer tahap' : 'Tanpa batas durasi');
        $countdownTitle = 'Sisa Waktu Pengerjaan';
        $countdownCaption = 'Timer dimulai saat peserta menekan tombol Mulai Ujian dan mengikuti durasi penugasan yang tersimpan.';

        if ($stageFlowEnabled) {
            $countdownTitle = 'Timer Tahap Aktif';
            $countdownCaption = $countdownTargetAt
                ? 'Timer mengikuti konfigurasi tahap yang sedang aktif.'
                : 'Tahap yang sedang dibuka tidak memakai batas waktu.';
        }
        $formatDateTime = fn($value) => $value ? $value->format('d M Y H:i') . ' WITA' : '-';
        $sessionDetails = [
            [
                'label' => $sessionEnabled ? 'Label Sesi' : 'Mode Sesi',
                'value' => $meta['session_label'],
            ],
            [
                'label' => $sessionEnabled ? 'Jadwal Sesi' : 'Akses Peserta',
                'value' => $meta['session_schedule_text'],
            ],
            [
                'label' => $sessionEnabled ? 'Mulai Sesi' : 'Mulai Penugasan',
                'value' => $formatDateTime($sessionEnabled ? $sessionStartAt : $assignmentStartAt),
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

        $questionNavigationGroups = collect($assessmentItems)
            ->map(function ($assessmentItem) use ($answerLookup, $buildDisplayFieldLabel, $stageMetaByIndex) {
                $assessment = $assessmentItem['data'];
                $stageMeta = $stageMetaByIndex[$assessmentItem['index']] ?? null;

                if ($stageMeta && ! ($stageMeta['show_questions'] ?? true)) {
                    return [
                        'assessment_index' => $assessmentItem['index'],
                        'heading' => 'Tahap '.($assessmentItem['index'] + 1),
                        'title' => trim((string) ($assessment['judul'] ?? '')) ?: 'Assessment '.($assessmentItem['index'] + 1),
                        'question_count' => 0,
                        'questions' => [],
                    ];
                }

                $isMultipleChoiceAssessment =
                    ($assessment['instrument_type'] ?? null) ===
                    \App\Enum\AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value;
                $questionNumber = 1;
                $questions = [];

                foreach ($assessment['forms'] ?? [] as $form) {
                    foreach ($form['fields'] ?? [] as $field) {
                        $fieldId = (int) ($field['id'] ?? 0);

                        if ($fieldId <= 0) {
                            continue;
                        }

                        $questions[] = [
                            'field_id' => $fieldId,
                            'assessment_index' => $assessmentItem['index'],
                            'number' => $questionNumber,
                            'label' => $buildDisplayFieldLabel(
                                $field,
                                $questionNumber,
                                $isMultipleChoiceAssessment ? 'Soal' : null
                            ),
                            'is_required' => (bool) ($field['is_required'] ?? false),
                            'initially_answered' => \App\Support\Assessment\AssessmentAnswerViewHelper::hasAnswer(
                                $field,
                                $answerLookup[$fieldId] ?? null
                            ),
                        ];

                        $questionNumber++;
                    }
                }

                return [
                    'assessment_index' => $assessmentItem['index'],
                    'heading' => 'Tahap '.($assessmentItem['index'] + 1),
                    'title' => trim((string) ($assessment['judul'] ?? '')) ?: 'Assessment '.($assessmentItem['index'] + 1),
                    'question_count' => count($questions),
                    'questions' => $questions,
                ];
            })
            ->filter(fn($group) => $group['question_count'] > 0)
            ->values()
            ->all();
        $questionNavigationItems = collect($questionNavigationGroups)
            ->flatMap(fn($group) => $group['questions'] ?? [])
            ->values()
            ->all();

        $assessmentNavigationItems = collect($assessmentItems)
            ->map(fn($assessmentItem) => [
                'index' => $assessmentItem['index'],
                'form_count' => $assessmentItem['form_count'],
                'question_count' => $assessmentItem['question_count'],
                'field_ids' => $assessmentItem['field_ids'],
                'stage_meta' => $stageMetaByIndex[$assessmentItem['index']] ?? null,
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
        $stageFlowAssessmentIndex = $stageFlowEnabled
            ? \App\Support\Assessment\AssessmentStageProgress::resolveCurrentStageIndex($stageProgress)
            : 0;
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
                                : $stageFlowAssessmentIndex),
                    ),
                )
                : 0;
        $initialQuestionFieldId = $errorFieldId;

        if (! $initialQuestionFieldId) {
            $initialQuestionItem = collect($questionNavigationItems)
                ->firstWhere('assessment_index', $initialAssessmentIndex);

            $initialQuestionFieldId = (int) ($initialQuestionItem['field_id'] ?? 0);
        }

        if ($initialQuestionFieldId <= 0) {
            $initialQuestionFieldId = (int) (collect($questionNavigationItems)->first()['field_id'] ?? 0);
        }
        $securityPayload = array_merge($securityPayload ?? [], [
            'enabled' => (bool) data_get($securityPayload ?? [], 'enabled', false),
            'violationUrl' => route('assessment.portal.security.violation', $target->id),
            'disqualifyUrl' => route('assessment.portal.security.disqualify', $target->id),
            'resultUrl' => route('assessment.portal.result', $target->id),
            'csrfToken' => csrf_token(),
            'targetId' => (int) $target->id,
        ]);
    @endphp

    @include('assessment.show.partials.portal-header', ['guru' => $guru])

    <div x-data="assessmentExamFlow({
        initialIndex: {{ $initialAssessmentIndex }},
        totalAssessments: {{ $assessmentCount }},
        assessmentItems: @js($assessmentNavigationItems),
        questionItems: @js($questionNavigationItems),
        stageFlowEnabled: @js($stageFlowEnabled),
        initialFlaggedFieldIds: @js($initialFlaggedFieldIds),
        initialQuestionFieldId: {{ $initialQuestionFieldId }},
        autosaveUrl: @js(route('assessment.portal.autosave', $target->id)),
        autosaveActionThreshold: 3,
        resultUrl: @js(route('assessment.portal.result', $target->id)),
        deadlineAt: @js(optional($countdownTargetAt)->toIso8601String()),
        textareaWordLimits: @js([
            'min' => \App\Support\Assessment\TextareaWordLimit::minWords(),
            'max' => \App\Support\Assessment\TextareaWordLimit::maxWords(),
        ]),
        security: @js($securityPayload),
    })" class="space-y-6 **:text-xs sm:**:text-sm">
        @include('assessment.show.partials.security-overlay', [
            'securityPayload' => $securityPayload,
        ])

        <div class="space-y-6" data-assessment-exam-content>
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
                            'stageMeta' => $stageMetaByIndex[$assessmentItem['index']] ?? null,
                        ])
                        @endforeach

                        @if ($assessmentCount > 0 && ! $stageFlowEnabled)
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
                        'questionNavigationGroups' => $questionNavigationGroups,
                        'meta' => $meta,
                        'countdownTitle' => $countdownTitle,
                        'countdownTargetAt' => $countdownTargetAt,
                        'countdownCaption' => $countdownCaption,
                        'sessionDetails' => $sessionDetails,
                        'securityPayload' => $securityPayload,
                    ])

            </section>

            @include('assessment.show.partials.session-bottom-nav', [
                'assessmentCount' => $assessmentCount,
                'questionNavigationGroups' => $questionNavigationGroups,
                'meta' => $meta,
                'countdownTitle' => $countdownTitle,
                'countdownTargetAt' => $countdownTargetAt,
                'countdownCaption' => $countdownCaption,
                'sessionDetails' => $sessionDetails,
                'securityPayload' => $securityPayload,
            ])
        </div>
    </div>

    @include('assessment.show.partials.scripts')
@endsection
