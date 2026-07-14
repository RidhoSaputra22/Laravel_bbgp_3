@php
    $certificateLinks = collect($certificateLinks ?? [])
        ->filter(fn ($item) => is_array($item) && filled($item['url'] ?? null))
        ->values()
        ->all();
@endphp

@if ($certificateLinks !== [])
    <x-assessment::ui.card>
        <div class="flex items-center gap-3 border-b border-slate-300 pb-4">
            <i class="fa fa-certificate text-[#1376BD]" aria-hidden="true"></i>
            <div>
                <h3 class="text-md font-medium text-slate-900">
                    Link Sertifikasi Peserta
                </h3>
                <p class="mt-1 text-sm leading-relaxed text-slate-500">
                    {{ count($certificateLinks) }} link sertifikat atau SK diisi peserta pada jawaban assessment.
                </p>
            </div>
        </div>

        <div class="mt-4 space-y-3">
            @foreach ($certificateLinks as $certificateLink)
                <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] p-4">
                    <div class="flex flex-col gap-4">
                        <div class="min-w-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-[#1376BD]">
                                {{ $certificateLink['form_title'] }}
                            </div>

                            <div class="mt-1 text-base font-semibold leading-snug text-[#0d3557]">
                                {{ $certificateLink['title'] }}
                            </div>

                            <div class="mt-1 text-sm text-slate-500">
                                {{ $certificateLink['assessment_title'] }} • {{ $certificateLink['link_label'] }}
                            </div>

                            @if (filled($certificateLink['detail'] ?? null))
                                <div class="mt-2 text-sm leading-relaxed text-slate-600">
                                    {{ $certificateLink['detail'] }}
                                </div>
                            @endif

                            <div class="mt-2 break-all text-xs leading-relaxed text-slate-500">
                                {{ \Illuminate\Support\Str::limit($certificateLink['url'], 90) }}
                            </div>
                        </div>

                        <div>
                            <x-assessment::ui.button
                                :href="$certificateLink['url']"
                                variant="outline"
                                icon="fas fa-external-link-alt"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-full justify-center font-bold sm:w-auto"
                            >
                                Lihat Sertifikat
                            </x-assessment::ui.button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-assessment::ui.card>
@endif
