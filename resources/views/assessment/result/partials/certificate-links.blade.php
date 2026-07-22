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
                @php
                    $preview = \App\Support\Assessment\AssessmentAttachmentPreviewHelper::resolve(
                        $certificateLink['url'] ?? '',
                        $certificateLink['title'] ?? '',
                        null,
                        'external_link',
                    );
                    $previewType = $certificateLink['preview_type'] ?? $preview['preview_type'];
                    $previewUrl = trim((string) ($certificateLink['preview_url'] ?? $preview['preview_url']));
                    $isEmbeddable = (bool) ($certificateLink['is_embeddable'] ?? $preview['is_embeddable']);
                @endphp
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

                        @if ($isEmbeddable && $previewType === 'image' && $previewUrl !== '')
                            <a href="{{ $certificateLink['url'] }}" target="_blank" rel="noopener noreferrer"
                                class="block overflow-hidden rounded-sm border border-[#dce8f1] bg-white">
                                <img src="{{ $previewUrl }}" alt="{{ $certificateLink['title'] }}"
                                    class="h-64 w-full object-contain">
                            </a>
                        @elseif ($isEmbeddable && $previewUrl !== '')
                            <div class="overflow-hidden rounded-sm border border-[#dce8f1] bg-white">
                                <iframe src="{{ $previewUrl }}" title="{{ $certificateLink['title'] }}"
                                    loading="lazy"
                                    class="h-72 w-full border-0 bg-white"></iframe>
                            </div>
                        @else
                            <div class="rounded-sm border border-[#dce8f1] bg-white px-4 py-4 text-sm text-slate-600">
                                <i class="{{ $certificateLink['icon_class'] ?? $preview['icon_class'] }} mr-2 text-[#1376BD]"></i>
                                Pratinjau langsung tidak tersedia. Buka tautan untuk melihat file.
                            </div>
                        @endif

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
