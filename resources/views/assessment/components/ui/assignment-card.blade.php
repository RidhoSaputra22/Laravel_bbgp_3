@props([
    'target',
    'meta',
])

@php
    $durationMinutes = (int) ($meta['duration_minutes'] ?? 0);
    $openEntryPayload = [
        'action' => route('assessment.portal.confirm', $target->id),
        'entryAction' => 'open',
        'title' => $target->assignment->judul_penugasan,
        'stageLabel' => $target->assignment->kode_penugasan,
        'questionTotal' => (int) ($meta['question_total'] ?? 0),
        'durationMinutes' => $durationMinutes,
        'customInstruction' => '',
    ];
    $startEntryPayload = [
        'action' => route('assessment.portal.confirm', $target->id),
        'entryAction' => 'start',
        'title' => $target->assignment->judul_penugasan,
        'stageLabel' => $target->assignment->kode_penugasan,
        'questionTotal' => (int) ($meta['question_total'] ?? 0),
        'durationMinutes' => $durationMinutes,
        'customInstruction' => '',
    ];
    $durationLabel = '-';

    if ($durationMinutes > 0) {
        $hours = intdiv($durationMinutes, 60);
        $minutes = $durationMinutes % 60;

        $durationLabel = collect([
            $hours > 0 ? $hours . ' jam' : null,
            $minutes > 0 ? $minutes . ' menit' : null,
        ])->filter()->implode(' ');
    }
@endphp

<div class="relative ">
    <x-assessment::ui.card>
         <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
        <div class="lg:pr-4">
            <div class=" font-bold font-mono">
                {{ $target->assignment->kode_penugasan }}
            </div>

            <div class=" text-sm font-bold   lg:text-2xl py-2">
                {{ $target->assignment->judul_penugasan }}
            </div>

            <div class="text-sm font-light text-slate-500">
                {{ $target->assignment->deskripsi ?: 'Penugasan ini belum memiliki deskripsi tambahan.' }}
            </div>
        </div>

        <div class="shrink-0 absolute top-0 right-0">
            <x-assessment::ui.status-badge :tone="$meta['badge']" class="rounded-bl-sm py-2 px-5  ">
                {{ $meta['label'] }}
            </x-assessment::ui.status-badge>
        </div>
    </div>

    <div class="my-4 flex flex-wrap gap-x-[18px] gap-y-2.5 text-sm text-[#6a7e90]">
        <span class="inline-flex items-center gap-2">
            <i class="far fa-calendar-alt"></i>
            {{ $meta['date_text'] }}
        </span>
        <span class="inline-flex items-center gap-2">
            <i class="fas fa-layer-group"></i>
            {{ $meta['assessment_total'] }} assessment
        </span>
        <span class="inline-flex items-center gap-2">
            <i class="far fa-copy"></i>
            {{ $meta['form_total'] }} form aktif
        </span>
        <span class="inline-flex items-center gap-2">
            <i class="fas fa-random"></i>
            {{ $meta['question_total'] }} soal
        </span>
        <span class="inline-flex items-center gap-2">
            <i class="far fa-clock"></i>
            {{ $meta['session_label'] }} | {{ $meta['session_schedule_text'] }}
        </span>
        <span class="inline-flex items-center gap-2">
            <i class="fas fa-stopwatch"></i>
            Durasi: {{ $durationLabel }}
        </span>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="text-sm text-slate-500">
            {{ $meta['description'] }}
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($meta['status'] === 'submitted')
                <x-assessment::ui.button
                    :href="route('assessment.portal.result', $target->id)"
                    icon="fas fa-poll"
                    class="font-bold"
                >
                    Lihat Hasil
                </x-assessment::ui.button>
            @elseif ($meta['status'] === 'in_progress')
                @if (($meta['uses_stage_flow'] ?? false) === true)
                    <x-assessment::ui.button
                        :href="route('assessment.portal.show', $target->id)"
                        icon="fas fa-play-circle"
                        class="font-bold"
                    >
                        {{ $meta['action_label'] ?? 'Lanjutkan Ujian' }}
                    </x-assessment::ui.button>
                @else
                    <button
                        type="button"
                        class="cursor-pointer inline-flex items-center justify-center text-sm font-semibold transition focus:outline-none focus:ring-4 px-3 py-2 rounded-sm border border-[#1376bd] bg-[#1376bd] text-white hover:bg-[#0f619c] focus:ring-[#1376bd]/25 font-bold"
                        x-on:click.prevent="openEntryModal({{ \Illuminate\Support\Js::from($openEntryPayload) }})"
                    >
                        <i class="fas fa-play-circle mr-2"></i>
                        {{ $meta['action_label'] ?? 'Lanjutkan Ujian' }}
                    </button>
                @endif
            @elseif ($meta['status'] === 'ready')
                @if (($meta['action_label'] ?? null) === 'Buka Penugasan')
                    <x-assessment::ui.button
                        :href="route('assessment.portal.show', $target->id)"
                        icon="fas fa-play-circle"
                        class="font-bold"
                    >
                        {{ $meta['action_label'] }}
                    </x-assessment::ui.button>
                @else
                    <button
                        type="button"
                        class="cursor-pointer inline-flex items-center justify-center text-sm font-semibold transition focus:outline-none focus:ring-4 px-3 py-2 rounded-sm border border-[#1376bd] bg-[#1376bd] text-white hover:bg-[#0f619c] focus:ring-[#1376bd]/25 font-bold"
                        x-on:click.prevent="openEntryModal({{ \Illuminate\Support\Js::from($startEntryPayload) }})"
                    >
                        <i class="fas fa-play-circle mr-2"></i>
                        {{ $meta['action_label'] ?? 'Mulai Ujian' }}
                    </button>
                @endif
            @else
                <x-assessment::ui.button
                    variant="muted"
                    icon="fas fa-lock"
                    :disabled="true"
                >
                    Tidak Tersedia
                </x-assessment::ui.button>
            @endif
        </div>
    </div>
    </x-assessment::ui.card>
</div>
