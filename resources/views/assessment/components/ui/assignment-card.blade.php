@props([
    'target',
    'meta',
])

@php
    $durationMinutes = (int) ($meta['duration_minutes'] ?? 0);
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
                <x-assessment::ui.button
                    :href="route('assessment.portal.show', $target->id)"
                    icon="fas fa-play-circle"
                    class="font-bold"
                >
                    Lanjutkan Ujian
                </x-assessment::ui.button>
            @elseif ($meta['status'] === 'ready')
                <form action="{{ route('assessment.portal.start', $target->id) }}" method="POST">
                    @csrf

                    <x-assessment::ui.button
                        type="submit"
                        icon="fas fa-play-circle"
                        class="font-bold"
                    >
                        Mulai Ujian
                    </x-assessment::ui.button>
                </form>
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
