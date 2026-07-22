@php
    $fileAttachments = collect($fileAttachments ?? [])
        ->filter(fn ($item) => is_array($item) && (filled($item['url'] ?? null) || filled($item['file_name'] ?? null)))
        ->values()
        ->all();
@endphp

@if ($fileAttachments !== [])
    <x-assessment::ui.card>
        <div class="flex items-center gap-3 border-b border-slate-300 pb-4">
            <i class="fa-regular fa-folder-open text-[#1376BD]" aria-hidden="true"></i>
            <div>
                <h3 class="text-md font-medium text-slate-900">
                    File Unggahan Assessment
                </h3>
                <p class="mt-1 text-sm leading-relaxed text-slate-500">
                    {{ count($fileAttachments) }} file atau link file diisi peserta pada field unggah file.
                </p>
            </div>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            @foreach ($fileAttachments as $attachment)
                @php
                    $previewType = $attachment['preview_type'] ?? 'file';
                    $previewUrl = trim((string) ($attachment['preview_url'] ?? ''));
                    $fileUrl = trim((string) ($attachment['url'] ?? ''));
                    $fileName = trim((string) ($attachment['file_name'] ?? '')) ?: 'File assessment';
                    $isEmbeddable = (bool) ($attachment['is_embeddable'] ?? false);
                @endphp

                <div class="overflow-hidden rounded-sm border border-[#dce8f1] bg-[#f8fbfe]">
                    <div class="border-b border-[#dce8f1] px-4 py-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-[#1376BD]">
                            {{ $attachment['form_title'] ?? 'Form' }}
                        </div>
                        <div class="mt-1 text-base font-semibold leading-snug text-[#0d3557]">
                            {{ $attachment['field_label'] ?? $fileName }}
                        </div>
                        <div class="mt-1 text-sm text-slate-500">
                            {{ $attachment['assessment_title'] ?? 'Assessment' }}
                            @if (filled($attachment['source_label'] ?? null))
                                • {{ $attachment['source_label'] }}
                            @endif
                        </div>
                    </div>

                    <div class="bg-white">
                        @if ($isEmbeddable && $previewType === 'image' && $previewUrl !== '')
                            <a href="{{ $fileUrl ?: $previewUrl }}" target="_blank" rel="noopener noreferrer"
                                class="block bg-slate-100">
                                <img src="{{ $previewUrl }}" alt="{{ $fileName }}"
                                    class="h-64 w-full object-contain">
                            </a>
                        @elseif ($isEmbeddable && $previewUrl !== '')
                            <iframe src="{{ $previewUrl }}" title="{{ $fileName }}"
                                loading="lazy"
                                class="h-72 w-full border-0 bg-white"></iframe>
                        @else
                            <div class="flex h-48 items-center justify-center bg-slate-100 px-4 text-center">
                                <div>
                                    <i class="{{ $attachment['icon_class'] ?? 'fa-regular fa-file' }} text-4xl text-[#1376BD]"></i>
                                    <div class="mt-3 text-sm font-semibold text-[#0d3557]">
                                        Pratinjau langsung tidak tersedia
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-3 px-4 py-4">
                        <div>
                            <div class="break-words text-sm font-semibold text-slate-800">
                                {{ $fileName }}
                            </div>
                            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                                @if (filled($attachment['mime_type'] ?? null))
                                    <span>{{ $attachment['mime_type'] }}</span>
                                @endif
                                @if (filled($attachment['size_text'] ?? null))
                                    <span>{{ $attachment['size_text'] }}</span>
                                @endif
                                @if (filled($attachment['host_label'] ?? null))
                                    <span>{{ $attachment['host_label'] }}</span>
                                @endif
                                @if (filled($attachment['answered_at'] ?? null))
                                    <span>Dijawab {{ $attachment['answered_at'] }}</span>
                                @endif
                            </div>
                        </div>

                        @if (filled($attachment['description'] ?? null))
                            <div class="text-sm leading-relaxed text-slate-600">
                                {{ $attachment['description'] }}
                            </div>
                        @endif

                        @if ($fileUrl !== '')
                            <div class="break-all text-xs leading-relaxed text-slate-500">
                                {{ \Illuminate\Support\Str::limit($fileUrl, 120) }}
                            </div>

                            <x-assessment::ui.button
                                :href="$fileUrl"
                                variant="outline"
                                icon="fas fa-external-link-alt"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-full justify-center font-bold"
                            >
                                Buka File
                            </x-assessment::ui.button>
                        @else
                            <div class="rounded-sm border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                                File tercatat, tetapi URL file tidak tersedia.
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-assessment::ui.card>
@endif
