@php
    $trainingSummary = $trainingSummary ?? [];
    $entries = collect($trainingSummary['entries'] ?? [])->values();
    $hasTrainingData = (bool) ($trainingSummary['has_data'] ?? false);
    $trainingChartPayload = $trainingSummary['chart'] ?? [
        'labels' => [],
        'titles' => [],
        'providers' => [],
        'years' => [],
        'jp_values' => [],
        'cumulative_jp_values' => [],
    ];
@endphp

<x-assessment::ui.card>
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h3 class="text-xl font-bold text-[#0d3557]">
                Ringkasan Pelatihan dan JP
            </h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                Blok ini merangkum berapa pelatihan yang diikuti serta total jam pelajaran dari pelatihan 1, 2, dan
                seterusnya pada jawaban portfolio.
            </p>
        </div>

        @if ($hasTrainingData)
            <x-assessment::ui.status-badge tone="primary" class="rounded-sm px-3 py-1.5">
                {{ $trainingSummary['total_trainings'] ?? 0 }} pelatihan
            </x-assessment::ui.status-badge>
        @endif
    </div>

    @if (! $hasTrainingData)
        <div class="mt-5 rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-4 text-sm leading-relaxed text-slate-600">
            Belum ada data pengalaman pelatihan yang terisi pada hasil assessment ini.
        </div>
    @else
        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-4">
                <div class="text-sm font-medium text-slate-500">
                    Jumlah Pelatihan
                </div>
                <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                    {{ $trainingSummary['total_trainings'] ?? 0 }}
                </div>
            </div>

            <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-4">
                <div class="text-sm font-medium text-slate-500">
                    Total JP
                </div>
                <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                    {{ $trainingSummary['formatted_total_jp'] ?? '0' }}
                </div>
            </div>

            <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-4">
                <div class="text-sm font-medium text-slate-500">
                    Rata-rata JP
                </div>
                <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                    {{ $trainingSummary['formatted_average_jp'] ?? '0' }}
                </div>
            </div>

            <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-4">
                <div class="text-sm font-medium text-slate-500">
                    JP Tertinggi
                </div>
                <div class="mt-2 text-[30px] font-bold leading-none text-[#0d3557]">
                    {{ $trainingSummary['formatted_max_jp'] ?? '0' }}
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.55fr)_minmax(320px,1fr)]">
            <div class="rounded-sm border border-[#dce8f1] bg-white px-4 py-4">
                <h4 class="text-lg font-bold text-[#0d3557]">
                    Grafik JP Per Pelatihan
                </h4>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Batang menunjukkan JP tiap pelatihan, sedangkan garis menampilkan akumulasi total JP.
                </p>
                <div class="mt-4 h-[320px]">
                    <canvas id="resultTrainingChart"></canvas>
                </div>
            </div>

            <div class="space-y-3">
                @foreach ($entries as $entry)
                    <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-4">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-slate-500">
                                    {{ $entry['label'] }}
                                </div>
                                <div class="mt-1 text-base font-bold leading-snug text-[#0d3557]">
                                    {{ $entry['title'] }}
                                </div>
                                <div class="mt-2 text-sm leading-relaxed text-slate-500">
                                    {{ $entry['provider'] ?: 'Penyelenggara belum diisi' }}
                                    @if ($entry['year'])
                                        • {{ $entry['year'] }}
                                    @endif
                                </div>
                            </div>
                            <div class="rounded-sm bg-white px-3 py-2 text-right shadow-sm">
                                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    JP
                                </div>
                                <div class="text-lg font-bold leading-none text-[#1376bd]">
                                    {{ $entry['formatted_jp'] }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-assessment::ui.card>

@if ($hasTrainingData)
    @push('scripts')
        <script src="{{ asset('library/chart.js/dist/Chart.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chartElement = document.getElementById('resultTrainingChart');

                if (!chartElement || typeof Chart === 'undefined') {
                    return;
                }

                const chartPayload = @json($trainingChartPayload);

                new Chart(chartElement, {
                    type: 'bar',
                    data: {
                        labels: chartPayload.labels,
                            datasets: [{
                                label: 'JP per Pelatihan',
                                data: chartPayload.jp_values,
                                backgroundColor: '#1376bd',
                                order: 2,
                            },
                            {
                                type: 'line',
                                label: 'Akumulasi JP',
                                data: chartPayload.cumulative_jp_values,
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.18)',
                                fill: false,
                                borderWidth: 2,
                                pointBackgroundColor: '#f59e0b',
                                pointRadius: 4,
                                yAxisID: 'y-axis-1',
                                order: 1,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            position: 'bottom',
                        },
                        tooltips: {
                            callbacks: {
                                afterLabel: function(tooltipItem) {
                                    const index = tooltipItem.index || 0;
                                    const details = [];

                                    if (chartPayload.titles[index]) {
                                        details.push(chartPayload.titles[index]);
                                    }

                                    if (chartPayload.providers[index]) {
                                        details.push('Penyelenggara: ' + chartPayload.providers[index]);
                                    }

                                    if (chartPayload.years[index]) {
                                        details.push('Tahun: ' + chartPayload.years[index]);
                                    }

                                    return details;
                                },
                            },
                        },
                        scales: {
                            yAxes: [{
                                    id: 'y-axis-0',
                                    ticks: {
                                        beginAtZero: true,
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: 'JP',
                                    },
                                },
                                {
                                    id: 'y-axis-1',
                                    position: 'right',
                                    gridLines: {
                                        drawOnChartArea: false,
                                    },
                                    ticks: {
                                        beginAtZero: true,
                                    },
                                    scaleLabel: {
                                        display: true,
                                        labelString: 'Akumulasi JP',
                                    },
                                },
                            ],
                        },
                    },
                });
            });
        </script>
    @endpush
@endif
