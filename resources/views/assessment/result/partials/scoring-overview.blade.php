@php
    $overallScore = data_get($scoringSummary, 'overall.formatted_score', '-');
    $overallLevel = data_get($scoringSummary, 'overall.level.short_label', 'Belum ada level');
    $statusLabel = data_get($scoringSummary, 'status_label', 'Belum Ada Skor');
    $statusDescription = data_get($scoringSummary, 'status_description', '-');
    $pendingManualItems = (int) data_get($scoringSummary, 'manual_review.pending_items', 0);
    $competencies = collect($scoringSummary['competencies'] ?? [])->values();
    $recommendations = collect($scoringSummary['development_recommendations'] ?? [])->take(4);
    $careerRecommendations = collect($scoringSummary['career_recommendations'] ?? [])->take(3);
    $datasets = collect(data_get($scoringSummary, 'radar_chart.datasets', []))->values();
    $chartSize = 260;
    $chartCenter = $chartSize / 2;
    $chartRadius = 78;
    $maxScore = max((float) data_get($scoringSummary, 'radar_chart.max_score', 5), 1);
    $axes = $datasets->map(function ($dataset, $index) use ($datasets, $chartCenter, $chartRadius, $maxScore) {
        $count = max($datasets->count(), 1);
        $angle = -M_PI / 2 + (2 * M_PI * $index / $count);
        $outerX = $chartCenter + cos($angle) * $chartRadius;
        $outerY = $chartCenter + sin($angle) * $chartRadius;
        $labelX = $chartCenter + cos($angle) * ($chartRadius + 28);
        $labelY = $chartCenter + sin($angle) * ($chartRadius + 28);
        $score = (float) ($dataset['score'] ?? 0);
        $ratio = min(max($score / $maxScore, 0), 1);
        $valueX = $chartCenter + cos($angle) * ($chartRadius * $ratio);
        $valueY = $chartCenter + sin($angle) * ($chartRadius * $ratio);

        return [
            'label' => $dataset['label'] ?? 'Kompetensi',
            'score' => $dataset['formatted_score'] ?? null,
            'is_available' => (bool) ($dataset['is_available'] ?? false),
            'outer_x' => round($outerX, 2),
            'outer_y' => round($outerY, 2),
            'label_x' => round($labelX, 2),
            'label_y' => round($labelY, 2),
            'value_x' => round($valueX, 2),
            'value_y' => round($valueY, 2),
        ];
    });
    $valuePolygonPoints = $axes
        ->map(fn ($axis) => $axis['value_x'] . ',' . $axis['value_y'])
        ->implode(' ');
@endphp

<div class="space-y-6">
    <x-assessment::ui.card>
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <h3 class="text-xl font-bold text-[#0d3557]">
                    Ringkasan Penilaian Kompetensi
                </h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    {{ $statusDescription }}
                </p>
            </div>

            <x-assessment::ui.status-badge
                :tone="data_get($scoringSummary, 'status') === 'complete' ? 'success' : 'warning'"
                class="rounded-sm px-3 py-1.5"
            >
                {{ $statusLabel }}
            </x-assessment::ui.status-badge>
        </div>
    </x-assessment::ui.card>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-assessment::ui.card>
            <div class="text-sm font-medium text-slate-500">
                Skor Umum Guru
            </div>
            <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                {{ $overallScore }}
            </div>
        </x-assessment::ui.card>

        <x-assessment::ui.card>
            <div class="text-sm font-medium text-slate-500">
                Level Umum
            </div>
            <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                {{ $overallLevel }}
            </div>
        </x-assessment::ui.card>

        <x-assessment::ui.card>
            <div class="text-sm font-medium text-slate-500">
                Status Penilaian
            </div>
            <div class="mt-2 text-xl font-bold leading-tight text-[#0d3557]">
                {{ $statusLabel }}
            </div>
        </x-assessment::ui.card>

        <x-assessment::ui.card>
            <div class="text-sm font-medium text-slate-500">
                Pending Review Manual
            </div>
            <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                {{ $pendingManualItems }}
            </div>
        </x-assessment::ui.card>
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
        <x-assessment::ui.card>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
                <div class="mx-auto flex justify-center">
                    <svg width="{{ $chartSize }}" height="{{ $chartSize }}" viewBox="0 0 {{ $chartSize }} {{ $chartSize }}"
                        class="max-w-full">
                        @for ($level = 1; $level <= 5; $level++)
                            @php
                                $ratio = $level / 5;
                                $gridPoints = $axes
                                    ->map(function ($axis) use ($chartCenter, $chartRadius, $ratio) {
                                        $x = $chartCenter + (($axis['outer_x'] - $chartCenter) * $ratio);
                                        $y = $chartCenter + (($axis['outer_y'] - $chartCenter) * $ratio);

                                        return round($x, 2) . ',' . round($y, 2);
                                    })
                                    ->implode(' ');
                            @endphp
                            <polygon points="{{ $gridPoints }}" fill="none" stroke="#d9e7f2" stroke-width="1" />
                        @endfor

                        @foreach ($axes as $axis)
                            <line x1="{{ $chartCenter }}" y1="{{ $chartCenter }}" x2="{{ $axis['outer_x'] }}"
                                y2="{{ $axis['outer_y'] }}" stroke="#d9e7f2" stroke-width="1" />
                        @endforeach

                        <polygon points="{{ $valuePolygonPoints }}" fill="rgba(19, 118, 189, 0.18)" stroke="#1376bd"
                            stroke-width="2" />

                        @foreach ($axes as $axis)
                            <circle cx="{{ $axis['value_x'] }}" cy="{{ $axis['value_y'] }}" r="4" fill="#1376bd" />
                            <text x="{{ $axis['label_x'] }}" y="{{ $axis['label_y'] }}" fill="#48637a"
                                font-size="11" text-anchor="middle">
                                {{ $axis['label'] }}
                            </text>
                        @endforeach
                    </svg>
                </div>

                <div class="min-w-0 flex-1 space-y-3">
                    <div>
                        <h4 class="text-lg font-bold text-[#0d3557]">
                            Visual Jaring Laba-laba Kompetensi
                        </h4>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">
                            Grafik ini merangkum distribusi skor kompetensi pedagogik, kepribadian, sosial, dan
                            profesional pada skala 1 sampai 5.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($datasets as $dataset)
                            <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-3">
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ $dataset['label'] }}
                                </div>
                                <div class="mt-1 text-lg font-bold text-[#1376bd]">
                                    {{ $dataset['formatted_score'] ?: '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-assessment::ui.card>

        <x-assessment::ui.card>
            <div class="space-y-5">
                <div>
                    <h4 class="text-lg font-bold text-[#0d3557]">
                        Ringkasan Naratif
                    </h4>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        {{ $scoringSummary['narrative'] ?? 'Ringkasan naratif belum tersedia.' }}
                    </p>
                </div>

                <div>
                    <h4 class="text-lg font-bold text-[#0d3557]">
                        Rekomendasi Pengembangan
                    </h4>
                    <div class="mt-3 space-y-3">
                        @forelse ($recommendations as $recommendation)
                            <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="font-semibold text-slate-900">
                                        {{ $recommendation['label'] }}
                                    </div>
                                    <x-assessment::ui.status-badge
                                        :tone="$loop->first ? 'warning' : 'primary'"
                                        class="rounded-sm px-2.5 py-1"
                                    >
                                        {{ $recommendation['category'] }}
                                    </x-assessment::ui.status-badge>
                                </div>
                                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                    {{ $recommendation['description'] }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">
                                Belum ada rekomendasi pengembangan yang bisa ditampilkan.
                            </p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-bold text-[#0d3557]">
                        Rekomendasi Karier / Peran
                    </h4>
                    <div class="mt-3 space-y-3">
                        @forelse ($careerRecommendations as $careerRecommendation)
                            <div class="rounded-sm border border-[#dce8f1] bg-white px-4 py-3">
                                <div class="font-semibold text-slate-900">
                                    {{ $careerRecommendation['title'] }}
                                </div>
                                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                                    {{ $careerRecommendation['reason'] }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">
                                Rekomendasi peran belum tersedia.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-assessment::ui.card>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($competencies as $competency)
            <x-assessment::ui.card>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-medium text-slate-500">
                            {{ $competency['label'] }}
                        </div>
                        <div class="mt-2 text-[28px] font-bold leading-none text-[#0d3557]">
                            {{ $competency['formatted_score'] ?: '-' }}
                        </div>
                    </div>
                    <x-assessment::ui.status-badge
                        :tone="$competency['score'] !== null && $competency['score'] >= 3.41 ? 'success' : 'warning'"
                        class="rounded-sm px-2.5 py-1"
                    >
                        {{ data_get($competency, 'level.short_label', 'Belum dinilai') }}
                    </x-assessment::ui.status-badge>
                </div>

                <div class="mt-3 text-sm leading-relaxed text-slate-500">
                    {{ $competency['recommendation_description'] ?: 'Kompetensi ini belum memiliki data skor yang cukup untuk dirangkum.' }}
                </div>
            </x-assessment::ui.card>
        @endforeach
    </div>
</div>
