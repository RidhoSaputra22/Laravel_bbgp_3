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

            $durationLabel = collect([
                $hours > 0 ? $hours . ' jam' : null,
                $minutes > 0 ? $minutes . ' menit' : null,
            ])->filter()->implode(' ');
        }
    @endphp

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

                        <x-assessment::ui.button :href="route('assessment.portal.dashboard')" variant="outline"
                            icon="fas fa-arrow-left">
                            Kembali ke Dashboard
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
                        {{ $stageOverview['submitted_total'] }} dari {{ $stageOverview['stage_total'] }} tahap sudah selesai.
                    </p>
                @endif
            </x-assessment::ui.card>

            <div class="space-y-5">
                @forelse ($stageOverview['stages'] as $stage)
                    <x-assessment::ui.card
                        class="{{ $stage['is_current'] ? 'ring-1 ring-[#1376bd]/20 shadow-[0_0_0_4px_rgba(19,118,189,0.06)]' : '' }} relative">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="lg:pr-6">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex items-center rounded-sm bg-[#eff6fb] px-2.5 py-1 text-xs font-semibold text-[#0d5f98]">
                                        Tahap {{ $stage['number'] }}
                                    </span>
                                    <span
                                        class="inline-flex items-center rounded-sm bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                        {{ $stage['instrument_label'] }}
                                    </span>
                                    @if ($stage['is_current'])
                                        <span
                                            class="inline-flex items-center rounded-sm bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                            Tahap Aktif
                                        </span>
                                    @endif
                                </div>

                                <div class="font-mono font-bold">
                                    {{ $stage['code'] }}
                                </div>

                                <h3 class="py-2 text-xl font-bold text-[#0d3557]">
                                    {{ $stage['title'] }}
                                </h3>

                                <p class="text-sm leading-relaxed text-slate-500">
                                    {{ $stage['description'] ?: 'Tahap ini belum memiliki deskripsi tambahan.' }}
                                </p>
                            </div>

                            <x-assessment::ui.status-badge :tone="$stage['status_tone']" class="px-4 py-2 rounded-bl-sm absolute right-0 top-0 ">
                                {{ $stage['status_label'] }}
                            </x-assessment::ui.status-badge>
                        </div>






                        <div class="mt-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div class="text-sm text-slate-500">
                                <span class="block">Batas selesai: {{ $stage['deadline_at_label'] }}</span>
                                <span class="block">Simpan permanen: {{ $stage['submitted_at_label'] }}</span>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @if ($stage['action_mode'] === 'start')
                                    <form action="{{ route('assessment.portal.start', $target->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="stage_index" value="{{ $stage['index'] }}">

                                        <x-assessment::ui.button type="submit" icon="fas fa-play-circle"
                                            class="font-bold">
                                            {{ $stage['action_label'] }}
                                        </x-assessment::ui.button>
                                    </form>
                                @elseif ($stage['action_mode'] === 'open')
                                    <x-assessment::ui.button
                                        :href="route('assessment.portal.show', ['id' => $target->id, 'stage' => $stage['index']])"
                                        icon="fas fa-play-circle" class="font-bold">
                                        {{ $stage['action_label'] }}
                                    </x-assessment::ui.button>
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
                    <div class="space-y-2">
                        <div class="font-mono text-sm font-bold">{{ $currentStage['code'] }}</div>
                        <div class="text-xl font-bold text-[#0d3557]">{{ $currentStage['title'] }}</div>
                        <p class="text-sm leading-relaxed text-slate-500">
                            {{ $currentStage['status_description'] }}
                        </p>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        @if ($currentStage['action_mode'] === 'start')
                            <form action="{{ route('assessment.portal.start', $target->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="stage_index" value="{{ $currentStage['index'] }}">

                                <x-assessment::ui.button type="submit" icon="fas fa-play-circle" class="font-bold">
                                    {{ $currentStage['action_label'] }}
                                </x-assessment::ui.button>
                            </form>
                        @elseif ($currentStage['action_mode'] === 'open')
                            <x-assessment::ui.button
                                :href="route('assessment.portal.show', ['id' => $target->id, 'stage' => $currentStage['index']])"
                                icon="fas fa-play-circle" class="font-bold">
                                {{ $currentStage['action_label'] }}
                            </x-assessment::ui.button>
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

            @include('assessment.partials.participant-profile-card', ['guru' => $guru])
        </aside>
    </section>
@endsection
