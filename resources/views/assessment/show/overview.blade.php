@extends('assessment.layouts.app')

@section('content')
    @php
        $stageOverview = $stageOverview ?? [
            'stage_total' => 0,
            'submitted_total' => 0,
            'in_progress_total' => 0,
            'draft_total' => 0,
            'ready_total' => 0,
            'available_total' => 0,
            'locked_total' => 0,
            'completion_percent' => 0,
            'stages' => [],
        ];
        $stageCollection = collect($stageOverview['stages'] ?? []);
        $currentStage = $stageCollection->firstWhere('is_current', true) ?: $stageCollection->first();
        $durationMinutes = (int) ($meta['duration_minutes'] ?? 0);
        $durationLabel = 'Tanpa batas durasi';

        if ($durationMinutes > 0) {
            $hours = intdiv($durationMinutes, 60);
            $minutes = $durationMinutes % 60;

            $durationLabel = collect([$hours > 0 ? $hours . ' jam' : null, $minutes > 0 ? $minutes . ' menit' : null])
                ->filter()
                ->implode(' ');
        }
        $portalUrls = array_merge([
            'dashboard' => route('assessment.portal.dashboard'),
            'show' => route('assessment.portal.show', $target->id),
            'start' => route('assessment.portal.start', $target->id),
            'result' => route('assessment.portal.result', $target->id),
        ], $portalUrls ?? []);
        $dashboardLabel = $dashboardLabel ?? 'Kembali ke Dashboard';
    @endphp

    <div x-data="assessmentEntryGate()">
        <div>
            <div class="flex justify-between bg-[#1376BD] px-5 py-4 text-white">
                <div>
                    <h1 class="text-xl font-medium">
                        Tahap Penugasan Assessment
                    </h1>
                    <p class="text-xs font-light">
                        Buka tahap assessment yang tersedia, pantau status tiap tahap, lalu lanjutkan sesuai progres
                        pengerjaan Anda.
                    </p>
                </div>
                <div class="hidden text-right text-sm sm:block">
                    <div class="font-bold">{{ $guru->nama_lengkap }}</div>
                    <div>
                        {{ $guru->satuan_pendidikan ?: '-' }}
                    </div>
                </div>
            </div>
        </div>
        <section class="grid gap-8 p-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)] lg:gap-10 lg:p-14">
            <div class="space-y-8">
                <x-assessment::ui.card>
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="lg:pr-6">
                            <div class="font-mono font-bold">
                                {{ $target->assignment->kode_penugasan }}
                            </div>

                            <h2 class="py-2 text-2xl font-bold text-[#0d3557]">
                                {{ $target->assignment->judul_penugasan }}
                            </h2>

                            <p class="text-sm leading-relaxed text-slate-500">
                                {{ $target->assignment->deskripsi ?: 'Pilih tahap assessment yang ingin Anda buka dari daftar di bawah ini.' }}
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            <x-assessment::ui.status-badge :tone="$meta['badge']" class="px-4 py-2">
                                {{ $meta['label'] }}
                            </x-assessment::ui.status-badge>

                            <x-assessment::ui.button :href="$portalUrls['dashboard']" variant="outline" icon="fas fa-arrow-left">
                                {{ $dashboardLabel }}
                            </x-assessment::ui.button>
                        </div>
                    </div>

                    <div class="my-5 flex flex-wrap gap-x-[18px] gap-y-2.5 text-sm text-[#6a7e90]">
                        <span class="inline-flex items-center gap-2">
                            <i class="far fa-calendar-alt"></i>
                            {{ $meta['date_text'] }}
                        </span>
                        <span class="inline-flex items-center gap-2">
                            <i class="fas fa-layer-group"></i>
                            {{ $stageOverview['stage_total'] }} tahap
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

                    @if ($stageOverview['stage_total'] > 0)
                        <div class="mt-5 h-2 overflow-hidden rounded-full bg-[#e2edf5]">
                            <div class="h-full rounded-full bg-[#0d5f98] transition-all duration-300"
                                style="width: {{ $stageOverview['completion_percent'] }}%"></div>
                        </div>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ $stageOverview['submitted_total'] }} dari {{ $stageOverview['stage_total'] }} tahap sudah
                            selesai.
                        </p>
                    @endif
                </x-assessment::ui.card>

                <div class="space-y-5">
                    @forelse ($stageOverview['stages'] as $stage)
                        @php
                            $stageCanOpen = in_array($stage['action_mode'] ?? null, ['start', 'open'], true);
                            $stageEntryPayload = $stageCanOpen
                                ? [
                                    'action' => route('assessment.portal.confirm', $target->id),
                                    'entryAction' => ($stage['action_mode'] ?? 'open') === 'start' ? 'start' : 'open',
                                    'stageIndex' => (int) ($stage['index'] ?? 0),
                                    'title' => $stage['title'] ?? 'Assessment',
                                    'stageLabel' => 'Tahap '.($stage['number'] ?? 0).' - '.($stage['code'] ?? '-'),
                                    'instrumentType' => $stage['instrument_type'] ?? '',
                                    'instrumentLabel' => $stage['instrument_label'] ?? '',
                                    'questionTotal' => (int) ($stage['question_total'] ?? 0),
                                    'durationMinutes' => (int) ($stage['time_limit_minutes'] ?? 0),
                                    'customInstruction' => $stage['instruction'] ?? '',
                                ]
                                : null;
                        @endphp

                        <x-assessment::ui.card
                            class="{{ $stage['is_current'] ? 'ring-1 ring-[#1376bd]/20 shadow-[0_0_0_4px_rgba(19,118,189,0.06)]' : '' }} relative">

                            <x-assessment::ui.status-badge tone="primary"
                                class="px-4 py-2 rounded-br-sm absolute left-0 top-0 ">
                                Tahap {{ $stage['number'] }}
                            </x-assessment::ui.status-badge>

                            <x-assessment::ui.status-badge :tone="$stage['status_tone']"
                                class="px-4 py-2 rounded-bl-sm absolute right-0 top-0 ">
                                {{ $stage['status_label'] }}
                            </x-assessment::ui.status-badge>

                            <div class="mt-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="lg:pr-6">
                                    <div class="font-mono font-bold">
                                        {{ $stage['code'] }}
                                    </div>

                                    <h3 class="py-1 text-xl font-bold text-[#0d3557]">
                                        {{ $stage['title'] }}
                                    </h3>

                                    <p class="text-sm leading-relaxed text-slate-500">
                                        {{ $stage['description'] ?: 'Tahap ini belum memiliki deskripsi tambahan.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                <div class="text-sm text-slate-500">
                                    <span class="block">Batas selesai: {{ $stage['deadline_at_label'] }}</span>
                                    <span class="block">Simpan permanen: {{ $stage['submitted_at_label'] }}</span>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    @if ($stageCanOpen)
                                        <button
                                            type="button"
                                            class="cursor-pointer inline-flex items-center justify-center text-sm font-semibold transition focus:outline-none focus:ring-4 px-3 py-2 rounded-sm border border-[#1376bd] bg-[#1376bd] text-white hover:bg-[#0f619c] focus:ring-[#1376bd]/25 font-bold"
                                            x-on:click.prevent="openEntryModal({{ \Illuminate\Support\Js::from($stageEntryPayload) }})"
                                        >
                                            <i class="fas fa-play-circle mr-2"></i>
                                            {{ $stage['action_label'] }}
                                        </button>
                                    @else
                                        <x-assessment::ui.button variant="muted" icon="fas fa-lock" :disabled="true">
                                            {{ $stage['action_label'] }}
                                        </x-assessment::ui.button>
                                    @endif
                                </div>
                            </div>
                        </x-assessment::ui.card>
                    @empty
                        <x-assessment::ui.empty-state icon="far fa-folder-open" title="Belum ada tahap assessment"
                            description="Tahap assessment akan muncul di halaman ini setelah penugasan memiliki struktur soal yang aktif." />
                    @endforelse
                </div>
            </div>

            <aside class="min-w-0 space-y-6">
                <x-assessment::ui.card>
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="text-lg font-bold text-[#0d3557]">Tahap Saat Ini</h3>

                        @if ($currentStage)
                            <x-assessment::ui.status-badge :tone="$currentStage['status_tone']" class="px-3 py-1.5">
                                {{ $currentStage['status_label'] }}
                            </x-assessment::ui.status-badge>
                        @endif
                    </div>

                    @if ($currentStage)
                        @php
                            $currentStageCanOpen = in_array($currentStage['action_mode'] ?? null, ['start', 'open'], true);
                            $currentStageEntryPayload = $currentStageCanOpen
                                ? [
                                    'action' => route('assessment.portal.confirm', $target->id),
                                    'entryAction' => ($currentStage['action_mode'] ?? 'open') === 'start' ? 'start' : 'open',
                                    'stageIndex' => (int) ($currentStage['index'] ?? 0),
                                    'title' => $currentStage['title'] ?? 'Assessment',
                                    'stageLabel' => 'Tahap '.($currentStage['number'] ?? 0).' - '.($currentStage['code'] ?? '-'),
                                    'instrumentType' => $currentStage['instrument_type'] ?? '',
                                    'instrumentLabel' => $currentStage['instrument_label'] ?? '',
                                    'questionTotal' => (int) ($currentStage['question_total'] ?? 0),
                                    'durationMinutes' => (int) ($currentStage['time_limit_minutes'] ?? 0),
                                    'customInstruction' => $currentStage['instruction'] ?? '',
                                ]
                                : null;
                        @endphp

                        <div class="space-y-2">
                            <div class="font-mono text-sm font-bold">{{ $currentStage['code'] }}</div>
                            <div class="text-xl font-bold text-[#0d3557]">{{ $currentStage['title'] }}</div>
                            <p class="text-sm leading-relaxed text-slate-500">
                                {{ $currentStage['status_description'] }}
                            </p>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            @if ($currentStageCanOpen)
                                <button
                                    type="button"
                                    class="cursor-pointer inline-flex items-center justify-center text-sm font-semibold transition focus:outline-none focus:ring-4 px-3 py-2 rounded-sm border border-[#1376bd] bg-[#1376bd] text-white hover:bg-[#0f619c] focus:ring-[#1376bd]/25 font-bold"
                                    x-on:click.prevent="openEntryModal({{ \Illuminate\Support\Js::from($currentStageEntryPayload) }})"
                                >
                                    <i class="fas fa-play-circle mr-2"></i>
                                    {{ $currentStage['action_label'] }}
                                </button>
                            @else
                                <x-assessment::ui.button variant="muted" icon="fas fa-lock" :disabled="true">
                                    {{ $currentStage['action_label'] }}
                                </x-assessment::ui.button>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-slate-500">
                            Belum ada tahap yang bisa dibuka saat ini.
                        </p>
                    @endif
                </x-assessment::ui.card>

                @include('assessment.partials.participant-profile-card', [
                    'guru' => $guru,
                    'participantAction' => $participantAction ?? null,
                ])
            </aside>
        </section>

        @include('assessment.partials.entry-confirmation-modal')
    </div>
@endsection
