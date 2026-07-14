@extends('assessment.layouts.app')

@section('content')
    @php
        $viewerMode = $viewerMode ?? 'participant';
        $isAdminViewer = $viewerMode === 'admin';
        $backUrl = $backUrl ?? route('assessment.portal.dashboard');
        $backLabel = $backLabel ?? 'Kembali ke Dashboard';
        $isStakeholderDownloadAvailable = $isStakeholderDownloadAvailable ?? false;
        $stakeholderResultDownloadUrl = $stakeholderResultDownloadUrl ?? null;
        $snapshot = $attempt->structure_snapshot ?? [];
        $startedAt = ($attempt->started_at ?? $target->started_at)?->format('d M Y H:i');
        $submittedAt = $attempt->submitted_at?->format('d M Y H:i');
        $deadlineAt = ($attempt->deadline_at ?? $target->deadline_at)?->format('d M Y H:i');
        $startedAtLabel = $startedAt ? $startedAt . ' WITA' : '-';
        $submittedAtLabel = $submittedAt ? $submittedAt . ' WITA' : '-';
        $deadlineAtLabel = $deadlineAt ? $deadlineAt . ' WITA' : '-';
        $durationMinutes = (int) ($summary['duration_minutes'] ?? 0);
        $completionPercentage = (int) ($summary['completion_percentage'] ?? 0);
        $totalQuestions = (int) ($summary['total_questions'] ?? 0);
        $requiredQuestions = (int) ($summary['required_questions'] ?? 0);
        $answeredQuestions = (int) ($summary['answered_questions'] ?? 0);
        $answeredRequiredQuestions = (int) ($summary['answered_required_questions'] ?? 0);
        $sessionEnabled = $target->assignment->usesSessionScheduling();
        $sessionLabel = $meta['session_label'] ?? '-';
        $sessionScheduleText = $meta['session_schedule_text'] ?? '-';
        $submissionStatusLabel = $meta['label'] ?? 'Dikirim';
        $submissionStatusTone = $meta['badge'] ?? 'success';
        $isDisqualified = $attempt->disqualified_at !== null
            || ($summary['submission_mode'] ?? null) === 'security_disqualified';
        $completionMode = $attempt->completion_mode
            ?: $target->completion_mode
            ?: (($summary['submission_mode'] ?? null) === 'deadline_auto' ? 'timeout' : 'manual');
        $autoSubmitted = $completionMode === 'timeout';
        $completionModeLabel = $isDisqualified
            ? 'Didiskualifikasi Guard'
            : ($autoSubmitted ? 'Timeout / Terlambat' : 'Selesai Manual');
        $primarySubmissionBadge = $isDisqualified
            ? 'Assessment dihentikan oleh guard ujian'
            : ($autoSubmitted
                ? 'Assessment selesai otomatis karena batas waktu berakhir'
                : 'Assessment berhasil dikirim');
        $assignmentDateText = $meta['date_text'] ?? '-';
        $assessmentTotal = (int) ($meta['assessment_total'] ?? 0);
        $formTotal = (int) ($meta['form_total'] ?? 0);
        $description = $isAdminViewer
            ? ($isDisqualified
                ? ($attempt->disqualification_reason ?: 'Assessment dihentikan oleh sistem guard karena pelanggaran aturan ujian.')
                : ($autoSubmitted
                    ? 'Batas waktu peserta berakhir. Jawaban terakhir diproses otomatis dan soal kosong diberi skor 0.'
                    : 'Assessment peserta sudah dikirim dan hasilnya dapat ditinjau kembali kapan saja.'))
            : ($meta['description'] ?? 'Hasil assessment ini tersimpan pada portal peserta.');
        $pageDescription = $isAdminViewer
            ? 'Ringkasan dan seluruh jawaban peserta yang sudah dikirim tersedia pada halaman ini.'
            : 'Ringkasan dan seluruh jawaban yang sudah Anda kirim tersedia pada halaman ini.';
        $assignmentDescription = $target->assignment->deskripsi
            ?: ($isAdminViewer
                ? 'Hasil pengisian assessment peserta tersimpan dan dapat ditinjau kembali kapan saja.'
                : 'Hasil pengisian assessment Anda tersimpan dan dapat ditinjau kembali kapan saja pada portal peserta.');
        $answerHelper = \App\Support\Assessment\AssessmentAnswerViewHelper::class;
        $normalizeFieldLabel = static function (array $field): string {
            $fieldLabel = trim((string) ($field['label'] ?? ''));

            if ($fieldLabel === '') {
                return $fieldLabel;
            }

            return preg_replace(
                '/^\s*(?:soal\s*)?\d+\s*[\.\)\-:]?\s*/iu',
                '',
                $fieldLabel,
                1
            ) ?? $fieldLabel;
        };
        $buildDisplayFieldLabel = static function (
            array $field,
            ?int $displayQuestionNumber = null,
            ?string $displayQuestionPrefix = null
        ) use ($normalizeFieldLabel): string {
            $normalizedLabel = $normalizeFieldLabel($field);

            if (! $displayQuestionNumber || $normalizedLabel === '') {
                return $normalizedLabel;
            }

            $displayLead = filled($displayQuestionPrefix)
                ? trim($displayQuestionPrefix) . ' ' . $displayQuestionNumber
                : (string) $displayQuestionNumber;

            return trim($displayLead . '. ' . $normalizedLabel);
        };
        $seriousViolationCount = (int) ($attempt->serious_violation_count ?? 0);
        $warningViolationCount = (int) ($attempt->warning_violation_count ?? 0);
        $sessionDetails = [
            [
                'label' => 'Status Submission',
                'value' => $submissionStatusLabel,
            ],
            [
                'label' => 'Mulai Dikerjakan',
                'value' => $startedAtLabel,
            ],
            [
                'label' => 'Batas Selesai',
                'value' => $deadlineAtLabel,
            ],
            [
                'label' => 'Dikirim Pada',
                'value' => $submittedAtLabel,
            ],
            [
                'label' => 'Mode Penyelesaian',
                'value' => $completionModeLabel,
            ],
            [
                'label' => 'Kode Penugasan',
                'value' => $target->assignment->kode_penugasan ?: '-',
            ],
            [
                'label' => 'Periode Penugasan',
                'value' => $assignmentDateText,
            ],
            [
                'label' => $sessionEnabled ? 'Label Sesi' : 'Mode Sesi',
                'value' => $sessionLabel,
            ],
            [
                'label' => $sessionEnabled ? 'Jadwal Sesi' : 'Akses Peserta',
                'value' => $sessionScheduleText,
            ],
        ];
        $participantDetails = [
            [
                'label' => 'Nama Peserta',
                'value' => $guru->nama_lengkap ?: '-',
            ],
            [
                'label' => 'NIK',
                'value' => $guru->no_ktp ?: '-',
            ],
            [
                'label' => 'Satuan Pendidikan',
                'value' => $guru->satuan_pendidikan ?: '-',
            ],
            [
                'label' => 'Email',
                'value' => $guru->email ?: '-',
            ],
        ];
    @endphp

    <div>
        <div class="flex flex-col gap-4 bg-[#1376BD] px-5 py-4 text-white md:flex-row md:items-start md:justify-between">
            <div>
                <h1 class="text-xl font-medium">
                    Hasil Assessment Peserta
                </h1>
                <p class="text-xs font-light">
                    {{ $pageDescription }}
                </p>
            </div>
            <div class="text-right text-sm">
                <div class="font-bold">{{ $guru->nama_lengkap }}</div>
                <div>
                    {{ $guru->satuan_pendidikan ?: '-' }}
                </div>
            </div>
        </div>
    </div>

    <section class="grid grid-cols-1 gap-8 p-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)] md:gap-10 md:p-14">
        <div class="space-y-8">
            <x-assessment::ui.card>
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-3 flex-1">
                        <div class="font-mono text-sm font-bold text-[#1376bd]">
                            {{ $target->assignment->kode_penugasan }}
                        </div>

                        <div>
                            <h2 class="text-2xl font-bold text-[#0d3557] lg:text-[30px]">
                                {{ $target->assignment->judul_penugasan }}
                            </h2>
                            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                {{ $assignmentDescription }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <x-assessment::ui.status-badge tone="success" class="rounded-sm px-3 py-1.5">
                                {{ $primarySubmissionBadge }}
                            </x-assessment::ui.status-badge>

                            <x-assessment::ui.status-badge :tone="$submissionStatusTone" class="rounded-sm px-3 py-1.5">
                                {{ $submissionStatusLabel }}
                            </x-assessment::ui.status-badge>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2  justify-start lg:justify-end">
                        @if ($isStakeholderDownloadAvailable && $stakeholderResultDownloadUrl)
                            <x-assessment::ui.button
                                :href="$stakeholderResultDownloadUrl"
                                variant="outline"
                                icon="fas fa-file-pdf"
                                class="font-bold"
                            >
                                Download PDF Jawaban
                            </x-assessment::ui.button>
                        @endif

                        <x-assessment::ui.button
                            :href="$backUrl"
                            icon="fas fa-th-large"
                            class="font-bold"
                        >
                            {{ $backLabel }}
                        </x-assessment::ui.button>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-x-[18px] gap-y-2.5 text-sm text-[#6a7e90]">
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-play"></i>
                        Mulai: {{ $startedAtLabel }}
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <i class="far fa-calendar-check"></i>
                        Dikirim: {{ $submittedAtLabel }}
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-stopwatch"></i>
                        Deadline: {{ $deadlineAtLabel }}
                    </span>
                    @if ($autoSubmitted)
                        <span class="inline-flex items-center gap-2">
                            <i class="fas fa-hourglass-end"></i>
                            Soal yang kosong otomatis dinilai 0
                        </span>
                    @endif
                    @if ($isDisqualified)
                        <span class="inline-flex items-center gap-2 text-red-600">
                            <i class="fas fa-shield-alt"></i>
                            {{ $attempt->disqualification_reason ?: 'Guard ujian menghentikan sesi ini.' }}
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-layer-group"></i>
                        {{ $assessmentTotal }} assessment
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <i class="far fa-copy"></i>
                        {{ $formTotal }} form
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <i class="far fa-clock"></i>
                        {{ $sessionLabel }} | {{ $sessionScheduleText }}
                    </span>
                </div>
            </x-assessment::ui.card>

            @include('assessment.result.partials.scoring-overview', [
                'scoringSummary' => $scoringSummary,
            ])

            @include('assessment.result.partials.training-overview', [
                'trainingSummary' => $trainingSummary ?? [],
            ])

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <x-assessment::ui.card>
                    <div class="text-sm font-medium text-slate-500">
                        Total Soal
                    </div>
                    <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                        {{ $totalQuestions }}
                    </div>
                </x-assessment::ui.card>

                <x-assessment::ui.card>
                    <div class="text-sm font-medium text-slate-500">
                        Soal Terjawab
                    </div>
                    <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                        {{ $answeredQuestions }}/{{ $totalQuestions }}
                    </div>
                </x-assessment::ui.card>

                <x-assessment::ui.card>
                    <div class="text-sm font-medium text-slate-500">
                        Soal Wajib Terjawab
                    </div>
                    <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                        {{ $answeredRequiredQuestions }}/{{ $requiredQuestions }}
                    </div>
                </x-assessment::ui.card>

                <x-assessment::ui.card>
                    <div class="text-sm font-medium text-slate-500">
                        Persentase Terisi
                    </div>
                    <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                        {{ $completionPercentage }}%
                    </div>
                    <div class="mt-2 text-sm text-slate-500">
                        Durasi pengerjaan {{ $durationMinutes }} menit
                    </div>
                </x-assessment::ui.card>
            </div>

            @if ($seriousViolationCount > 0 || $warningViolationCount > 0 || $isDisqualified)
                <x-assessment::ui.card>
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-[#0d3557]">
                                Ringkasan Guard Ujian
                            </h3>
                            <p class="mt-2 text-sm leading-relaxed text-[#6a7e90]">
                                Semua aktivitas guard tersimpan sebagai audit attempt dan menjadi bagian dari hasil sesi ini.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <x-assessment::ui.status-badge :tone="$isDisqualified ? 'danger' : 'warning'" class="rounded-sm px-3 py-1.5">
                                {{ $isDisqualified ? 'Didiskualifikasi' : 'Terdeteksi Aktivitas Guard' }}
                            </x-assessment::ui.status-badge>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-4">
                            <div class="text-sm font-medium text-slate-500">
                                Pelanggaran Serius
                            </div>
                            <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                                {{ $seriousViolationCount }}
                            </div>
                        </div>
                        <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-4">
                            <div class="text-sm font-medium text-slate-500">
                                Warning Tidak Sengaja
                            </div>
                            <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                                {{ $warningViolationCount }}
                            </div>
                        </div>
                        <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-4">
                            <div class="text-sm font-medium text-slate-500">
                                Status Guard
                            </div>
                            <div class="mt-2 text-lg font-bold leading-tight {{ $isDisqualified ? 'text-red-600' : 'text-[#0d3557]' }}">
                                {{ $isDisqualified ? 'Sesi dihentikan guard' : 'Sesi tetap selesai' }}
                            </div>
                        </div>
                    </div>

                    @if ($isDisqualified)
                        <div class="mt-5 rounded-sm border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                            {{ $attempt->disqualification_reason ?: 'Assessment dihentikan oleh sistem guard karena pelanggaran aturan ujian.' }}
                        </div>
                    @endif
                </x-assessment::ui.card>
            @endif

             @include('assessment.result.partials.certificate-links', [
                'certificateLinks' => $certificateLinks ?? [],
            ])
        </div>

        <aside class="min-w-0 self-start space-y-4 xl:sticky xl:top-6">
            <x-assessment::ui.card>
                <div class="flex items-center gap-3 border-b border-slate-300 pb-4">
                    <i class="fa fa-circle-check text-[#1376BD]" aria-hidden="true"></i>
                    <h3 class="text-md font-medium text-slate-900">
                        Informasi Pengiriman
                    </h3>
                </div>

                <div class="mt-2">
                    @foreach ($sessionDetails as $detail)
                        <x-assessment::ui.detail-row
                            :label="$detail['label']"
                            :value="$detail['value']"
                            valueClass="font-medium leading-relaxed text-slate-900"
                            :first="$loop->first"
                        />
                    @endforeach
                </div>

                <div class="mt-2 rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-3 text-sm leading-relaxed text-slate-600">
                    {{ $description }}
                </div>
            </x-assessment::ui.card>

            <x-assessment::ui.card>
                <div class="flex items-center gap-3 border-b border-slate-300 pb-4">
                    <i class="fa fa-user text-[#1376BD]" aria-hidden="true"></i>
                    <h3 class="text-md font-medium text-slate-900">
                        Data Peserta
                    </h3>
                </div>

                <div class="mt-2">
                    @foreach ($participantDetails as $detail)
                        <x-assessment::ui.detail-row
                            :label="$detail['label']"
                            :value="$detail['value']"
                            valueClass="font-medium leading-relaxed text-slate-900"
                            :first="$loop->first"
                        />
                    @endforeach
                </div>
            </x-assessment::ui.card>


        </aside>
    </section>
@endsection
