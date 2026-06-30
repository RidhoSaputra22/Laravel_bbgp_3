@extends('assessment.layouts.app')

@section('content')
    @php
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
        $questionNumber = 0;
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
        $description = $meta['description'] ?? 'Hasil assessment ini tersimpan pada portal peserta.';
        $answerHelper = \App\Support\Assessment\AssessmentAnswerViewHelper::class;
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
                'label' => 'Label Sesi',
                'value' => $sessionLabel,
            ],
            [
                'label' => 'Jadwal Sesi',
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
                    Ringkasan dan seluruh jawaban yang sudah Anda kirim tersedia pada halaman ini.
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
                    <div class="space-y-3">
                        <div class="font-mono text-sm font-bold text-[#1376bd]">
                            {{ $target->assignment->kode_penugasan }}
                        </div>

                        <div>
                            <h2 class="text-2xl font-bold text-[#0d3557] lg:text-[30px]">
                                {{ $target->assignment->judul_penugasan }}
                            </h2>
                            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                {{ $target->assignment->deskripsi ?: 'Hasil pengisian assessment Anda tersimpan dan dapat ditinjau kembali kapan saja pada portal peserta.' }}
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

                    <div class="flex flex-wrap gap-2">
                        <x-assessment::ui.button
                            :href="route('assessment.portal.dashboard')"
                            icon="fas fa-th-large"
                            class="font-bold"
                        >
                            Kembali ke Dashboard
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

            @forelse ($snapshot['assessments'] ?? [] as $assessment)
                @php
                    $assessmentForms = collect($assessment['forms'] ?? []);
                    $assessmentQuestionTotal = $assessmentForms->sum(
                        fn ($form) => count($form['fields'] ?? []),
                    );
                @endphp

                <div class="space-y-4">
                    <x-assessment::ui.card>
                        <div class="mb-2 text-sm uppercase text-slate-500">
                            {{ $assessment['kode_assessment'] }}
                        </div>

                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-[#0d3557]">
                                    {{ $assessment['judul'] }}
                                </h3>
                                <p class="mt-2 leading-relaxed text-[#6a7e90]">
                                    {{ $assessment['deskripsi'] ?: 'Ringkasan jawaban untuk assessment ini ditampilkan pada bagian form di bawah.' }}
                                </p>
                            </div>

                            <div class="grid min-w-[220px] grid-cols-2 gap-3 rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-3 text-sm text-slate-600">
                                <div>
                                    <div class="text-xs uppercase tracking-[0.14em] text-slate-400">
                                        Form
                                    </div>
                                    <div class="mt-1 font-bold text-slate-900">
                                        {{ $assessmentForms->count() }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs uppercase tracking-[0.14em] text-slate-400">
                                        Soal
                                    </div>
                                    <div class="mt-1 font-bold text-slate-900">
                                        {{ $assessmentQuestionTotal }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (!empty($assessment['petunjuk']))
                            <div class="mt-4 rounded-sm border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-relaxed text-sky-800">
                                <strong>Petunjuk:</strong> {{ $assessment['petunjuk'] }}
                            </div>
                        @endif
                    </x-assessment::ui.card>

                    @foreach ($assessment['forms'] ?? [] as $form)
                        <x-assessment::ui.card>
                            <h4 class="text-lg font-bold text-[#0d3557]">
                                {{ $form['judul_form'] }}
                            </h4>
                            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                {{ $form['deskripsi'] ?: 'Daftar jawaban yang Anda kirim untuk form ini.' }}
                            </p>

                            <div class="mt-5 space-y-4">
                                @foreach ($form['fields'] ?? [] as $field)
                                    @php
                                        $questionNumber++;
                                        $fieldId = (int) ($field['id'] ?? 0);
                                        $fieldType = $field['tipe_field'] ?? 'text';
                                        $answer = $answerLookup[$fieldId] ?? null;
                                        $fieldHasAnswer = $answerHelper::hasAnswer($field, $answer);
                                        $selectedValues = $answerHelper::resolveSelectedValues($field, $answer);
                                        $resolvedAnswerText = $answerHelper::resolveAnswerText($field, $answer);
                                        $repeaterColumns = $answerHelper::resolveRepeaterColumns($field, $answer);
                                        $repeaterRows = $answerHelper::resolveRepeaterRows($answer);
                                        $fileUrl = data_get($answer, 'file_url');
                                        $fileName = $resolvedAnswerText ?: 'Belum ada file yang diunggah';
                                        $inputType = match ($fieldType) {
                                            'number' => 'number',
                                            'email' => 'email',
                                            default => 'text',
                                        };
                                    @endphp

                                    <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] p-4 sm:p-5">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center rounded-full bg-[#eaf5fb] px-3 py-1 text-xs font-semibold tracking-[0.14em] text-[#0d5f98]">
                                                    Soal {{ $questionNumber }}
                                                </span>

                                                <x-assessment::ui.status-badge
                                                    :tone="($field['is_required'] ?? false) ? 'primary' : 'secondary'"
                                                    class="rounded-sm px-2.5 py-1"
                                                >
                                                    {{ ($field['is_required'] ?? false) ? 'Wajib' : 'Opsional' }}
                                                </x-assessment::ui.status-badge>

                                                <x-assessment::ui.status-badge
                                                    :tone="$fieldHasAnswer ? 'success' : 'warning'"
                                                    class="rounded-sm px-2.5 py-1"
                                                >
                                                    {{ $fieldHasAnswer ? 'Terjawab' : 'Belum dijawab' }}
                                                </x-assessment::ui.status-badge>

                                                @if (!empty($answer['final_score']))
                                                    <x-assessment::ui.status-badge
                                                        tone="primary"
                                                        class="rounded-sm px-2.5 py-1"
                                                    >
                                                        Skor {{ number_format((float) $answer['final_score'], 2) }}
                                                    </x-assessment::ui.status-badge>
                                                @endif
                                            </div>

                                            @if (!empty($answer['answered_at']))
                                                <div class="text-xs text-slate-500">
                                                    Tersimpan: {{ $answer['answered_at'] }} WITA
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mt-4">
                                            <h5 class="text-base font-semibold text-slate-900">
                                                {{ $field['label'] }}
                                            </h5>

                                            @if (!empty($field['deskripsi']))
                                                <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                                    {{ $field['deskripsi'] }}
                                                </p>
                                            @endif

                                            @if (!empty($field['bantuan']))
                                                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                                    <i class="far fa-lightbulb mr-1"></i>
                                                    {{ $field['bantuan'] }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="mt-4">
                                            @switch($fieldType)
                                                @case('textarea')
                                                    <x-assessment::form.textarea
                                                        :name="'result_answers_'.$fieldId"
                                                        :value="$resolvedAnswerText"
                                                        placeholder="Belum dijawab"
                                                        rows="4"
                                                        readonly
                                                        disabled
                                                    />
                                                @break

                                                @case('select')
                                                    <x-assessment::form.select
                                                        :name="'result_answers_'.$fieldId"
                                                        placeholder="Belum dijawab"
                                                        disabled
                                                    >
                                                        @foreach ($field['opsi_field'] ?? [] as $option)
                                                            @php
                                                                $optionValue = is_array($option)
                                                                    ? (string) ($option['value'] ?? '')
                                                                    : (string) $option;
                                                                $optionLabel = is_array($option)
                                                                    ? ($option['label'] ?? $optionValue)
                                                                    : $optionValue;
                                                            @endphp
                                                            <option
                                                                value="{{ $optionValue }}"
                                                                @selected(($selectedValues[0] ?? '') === $optionValue)
                                                            >
                                                                {{ $optionLabel }}
                                                            </option>
                                                        @endforeach
                                                    </x-assessment::form.select>
                                                @break

                                                @case('radio')
                                                    <x-assessment::form.radio-group
                                                        :name="'result_answers_'.$fieldId"
                                                        :options="$field['opsi_field'] ?? []"
                                                        :selected="$selectedValues"
                                                        :id-prefix="'result-field-'.$fieldId"
                                                        disabled
                                                    />
                                                @break

                                                @case('checkbox')
                                                    <x-assessment::form.checkbox-group
                                                        :name="'result_answers_'.$fieldId"
                                                        :options="$field['opsi_field'] ?? []"
                                                        :selected="$selectedValues"
                                                        :id-prefix="'result-field-'.$fieldId"
                                                        disabled
                                                    />
                                                @break

                                                @case('file')
                                                    <div class="rounded-sm border border-[#d7e3ee] bg-white px-4 py-3">
                                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                            <div>
                                                                <div class="text-sm font-semibold text-slate-700">
                                                                    {{ $fileName }}
                                                                </div>
                                                                <div class="mt-1 text-sm text-slate-500">
                                                                    {{ $fieldHasAnswer ? 'File jawaban tersedia pada submission ini.' : 'Peserta tidak mengunggah file pada pertanyaan ini.' }}
                                                                </div>
                                                            </div>

                                                            @if ($fileUrl)
                                                                <x-assessment::ui.button
                                                                    :href="$fileUrl"
                                                                    variant="outline"
                                                                    icon="fas fa-download"
                                                                    target="_blank"
                                                                    rel="noopener"
                                                                >
                                                                    Lihat File
                                                                </x-assessment::ui.button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @break

                                                @case('repeater')
                                                    @if ($repeaterRows === [] || $repeaterColumns === [])
                                                        <div class="rounded-sm border border-[#d7e3ee] bg-white px-4 py-3 text-sm text-slate-500">
                                                            Peserta belum mengirim data tabel pada pertanyaan ini.
                                                        </div>
                                                    @else
                                                        <div class="overflow-x-auto rounded-sm border border-[#d7e3ee] bg-white">
                                                            <table class="min-w-full divide-y divide-[#d7e3ee] text-sm">
                                                                <thead class="bg-[#f8fbfe]">
                                                                    <tr>
                                                                        @foreach ($repeaterColumns as $column)
                                                                            <th class="px-4 py-3 text-left font-semibold text-slate-700">
                                                                                {{ $column['label'] ?? $column['nama_field'] }}
                                                                            </th>
                                                                        @endforeach
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="divide-y divide-[#edf2f7]">
                                                                    @foreach ($repeaterRows as $row)
                                                                        <tr>
                                                                            @foreach ($repeaterColumns as $column)
                                                                                <td class="px-4 py-3 text-slate-600">
                                                                                    {{ $answerHelper::formatRepeaterCell($column, $row[$column['nama_field']] ?? '') }}
                                                                                </td>
                                                                            @endforeach
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                @break

                                                @default
                                                    <x-assessment::form.input
                                                        :name="'result_answers_'.$fieldId"
                                                        :type="$inputType"
                                                        :value="$resolvedAnswerText"
                                                        placeholder="Belum dijawab"
                                                        readonly
                                                        disabled
                                                    />
                                            @endswitch

                                            @unless ($fieldHasAnswer)
                                                <p class="mt-3 text-sm text-amber-700">
                                                    Jawaban untuk pertanyaan ini tidak tersedia pada submission.
                                                </p>
                                            @endunless
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-assessment::ui.card>
                    @endforeach
                </div>
            @empty
                <x-assessment::ui.card>
                    <x-assessment::ui.empty-state
                        icon="far fa-folder-open"
                        title="Belum ada hasil assessment"
                        description="Struktur assessment tidak ditemukan pada submission ini."
                    />
                </x-assessment::ui.card>
            @endforelse
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

            <x-assessment::ui.card>
                <h3 class="text-md font-medium text-slate-900">
                    Breakdown per Assessment
                </h3>

                <div class="mt-4 space-y-4">
                    @forelse ($summary['assessment_breakdown'] ?? [] as $assessmentItem)
                        <div class="border-t border-[#ebf1f6] pt-4 first:border-t-0 first:pt-0">
                            <div class="flex flex-wrap justify-between gap-2">
                                <div>
                                    <div class="font-bold text-slate-900">
                                        {{ $assessmentItem['judul'] }}
                                    </div>
                                    <div class="text-sm text-slate-500">
                                        {{ $assessmentItem['kode_assessment'] }}
                                    </div>
                                </div>

                                <div class="font-bold text-[#1376bd]">
                                    {{ $assessmentItem['answered_questions'] }}/{{ $assessmentItem['total_questions'] }} soal
                                </div>
                            </div>

                            @foreach ($assessmentItem['forms'] ?? [] as $formItem)
                                <div class="mt-2 flex justify-between gap-3 text-sm text-slate-500">
                                    <span>{{ $formItem['judul_form'] }}</span>
                                    <span class="shrink-0">
                                        {{ $formItem['answered_questions'] }}/{{ $formItem['total_questions'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">
                            Belum ada ringkasan assessment yang tersedia.
                        </p>
                    @endforelse
                </div>
            </x-assessment::ui.card>
        </aside>
    </section>
@endsection
