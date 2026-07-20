@props([
    'name',
    'id' => null,
    'label' => null,
    'description' => null,
    'hint' => null,
    'required' => false,
    'error' => null,
    'mode' => 'file',
    'value' => null,
    'placeholder' => null,
    'allowedDomains' => [],
    'inputTitle' => null,
    'existingFileUrl' => null,
    'existingFileName' => null,
    'existingFileMime' => null,
    'existingFileSize' => null,
    'existingLinkUrl' => null,
])

@php
    $extensionFrom = static function ($name, $url): string {
        $candidate = trim((string) $name);

        if ($candidate === '') {
            $candidate = trim((string) parse_url((string) $url, PHP_URL_PATH));
        }

        return strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
    };
    $formatBytes = static function ($bytes): string {
        $bytes = (int) $bytes;

        if ($bytes <= 0) {
            return '';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        $precision = $unitIndex === 0 ? 0 : 1;

        return number_format($size, $precision).' '.$units[$unitIndex];
    };
    $previewTypeFor = static function ($name, $url, $mime, $fallback = 'file') use ($extensionFrom): string {
        $mime = strtolower(trim((string) $mime));
        $extension = $extensionFrom($name, $url);

        if (str_starts_with($mime, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true)) {
            return 'image';
        }

        if ($mime === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }

        return $fallback;
    };
    $id = $id ?: trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-');
    $errorKey = trim((string) preg_replace('/\[(.*?)\]/', '.$1', $name), '.');
    $errorBag = $errors ?? null;
    $errorMessage = $error ?: ($errorBag ? $errorBag->first($errorKey) : null);
    $mode = in_array(trim((string) $mode), ['file', 'link'], true) ? trim((string) $mode) : 'file';
    $allowedDomains = collect($allowedDomains)
        ->map(fn ($domain) => trim((string) $domain))
        ->filter()
        ->values()
        ->all();
    $existingFileUrl = trim((string) $existingFileUrl);
    $existingFileName = trim((string) $existingFileName);
    $existingFileMime = trim((string) $existingFileMime);
    $existingLinkUrl = trim((string) $existingLinkUrl);
    $initialPreview = null;

    if ($mode === 'link') {
        $initialUrl = trim((string) old($errorKey, $value ?: $existingLinkUrl));

        if ($initialUrl !== '') {
            $initialPreview = [
                'name' => $initialUrl,
                'url' => $initialUrl,
                'type' => 'link',
                'mime' => '',
                'sizeText' => '',
                'source' => $initialUrl === $existingLinkUrl ? 'existing' : 'selected',
            ];
        }
    } elseif ($existingFileUrl !== '' || $existingFileName !== '') {
        $resolvedName = $existingFileName !== '' ? $existingFileName : (basename((string) parse_url($existingFileUrl, PHP_URL_PATH)) ?: 'Lampiran tersimpan');

        $initialPreview = [
            'name' => $resolvedName,
            'url' => $existingFileUrl,
            'type' => $previewTypeFor($resolvedName, $existingFileUrl, $existingFileMime),
            'mime' => $existingFileMime,
            'sizeText' => $formatBytes($existingFileSize),
            'source' => 'existing',
        ];
    }
@endphp

@once
    @push('scripts')
        <script>
            window.assessmentFilePreview = function(config) {
                return {
                    inputMode: config.mode === 'link' ? 'link' : 'file',
                    initialPreview: config.initialPreview || null,
                    preview: null,
                    objectUrl: null,

                    init() {
                        this.preview = this.normalizePreview(this.initialPreview);

                        if (this.inputMode === 'link') {
                            this.syncLinkPreview(this.$refs.linkInput?.value || '');
                        }
                    },
                    destroy() {
                        this.revokeObjectUrl();
                    },
                    normalizePreview(preview) {
                        if (!preview || typeof preview !== 'object') {
                            return null;
                        }

                        const normalized = {
                            name: String(preview.name || '').trim(),
                            url: String(preview.url || '').trim(),
                            type: String(preview.type || '').trim(),
                            mime: String(preview.mime || '').trim(),
                            sizeText: String(preview.sizeText || '').trim(),
                            source: String(preview.source || 'existing').trim(),
                        };

                        if (!normalized.name && !normalized.url) {
                            return null;
                        }

                        if (!['image', 'pdf', 'link', 'file'].includes(normalized.type)) {
                            normalized.type = this.resolvePreviewType(normalized.name, normalized.url, normalized.mime);
                        }

                        return normalized;
                    },
                    resolvePreviewType(name, url, mime = '') {
                        const normalizedMime = String(mime || '').trim().toLowerCase();
                        const extension = this.resolveExtension(name, url);

                        if (normalizedMime.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(extension)) {
                            return 'image';
                        }

                        if (normalizedMime === 'application/pdf' || extension === 'pdf') {
                            return 'pdf';
                        }

                        return this.inputMode === 'link' ? 'link' : 'file';
                    },
                    resolveExtension(name, url) {
                        let candidate = String(name || '').trim();

                        if (!candidate && url) {
                            try {
                                candidate = new URL(url, window.location.href).pathname || '';
                            } catch (error) {
                                candidate = String(url || '');
                            }
                        }

                        const match = candidate.toLowerCase().match(/\.([a-z0-9]+)(?:$|[?#])/);

                        return match ? match[1] : '';
                    },
                    formatFileSize(size) {
                        let normalizedSize = Number(size || 0);

                        if (!Number.isFinite(normalizedSize) || normalizedSize <= 0) {
                            return '';
                        }

                        const units = ['B', 'KB', 'MB', 'GB'];
                        let unitIndex = 0;

                        while (normalizedSize >= 1024 && unitIndex < units.length - 1) {
                            normalizedSize /= 1024;
                            unitIndex++;
                        }

                        return `${normalizedSize.toFixed(unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
                    },
                    previewTitle() {
                        if (!this.preview) {
                            return '';
                        }

                        if (this.inputMode === 'link') {
                            return this.preview.source === 'existing' ? 'Link bukti tersimpan' : 'Pratinjau link bukti';
                        }

                        return this.preview.source === 'selected' ? 'File baru dipilih' : 'File tersimpan';
                    },
                    previewNote() {
                        if (!this.preview) {
                            return '';
                        }

                        if (this.inputMode === 'link') {
                            return this.preview.source === 'existing' ?
                                'Isi link baru hanya jika ingin mengganti tautan yang sudah tersimpan.' :
                                'Pastikan link dapat diakses sesuai petunjuk.';
                        }

                        return this.preview.source === 'existing' ?
                            'Pilih file baru hanya jika ingin mengganti lampiran yang sudah tersimpan.' :
                            'Pratinjau ini belum tersimpan sampai Anda menekan tombol simpan.';
                    },
                    previewIconClass() {
                        if (!this.preview) {
                            return 'fa-regular fa-file';
                        }

                        if (this.preview.type === 'image') {
                            return 'fa-regular fa-file-image';
                        }

                        if (this.preview.type === 'pdf') {
                            return 'fa-regular fa-file-pdf';
                        }

                        if (this.preview.type === 'link') {
                            return 'fa-solid fa-link';
                        }

                        return 'fa-regular fa-file';
                    },
                    canOpenPreview() {
                        const url = String(this.preview?.url || '').trim();

                        if (!url) {
                            return false;
                        }

                        if (this.inputMode !== 'link') {
                            return true;
                        }

                        try {
                            const parsedUrl = new URL(url);

                            return ['http:', 'https:'].includes(parsedUrl.protocol);
                        } catch (error) {
                            return false;
                        }
                    },
                    revokeObjectUrl() {
                        if (this.objectUrl) {
                            URL.revokeObjectURL(this.objectUrl);
                            this.objectUrl = null;
                        }
                    },
                    handleFileChange(event) {
                        const file = event.target?.files?.[0] || null;
                        this.revokeObjectUrl();

                        if (!file) {
                            this.preview = this.normalizePreview(this.initialPreview);
                            return;
                        }

                        this.objectUrl = URL.createObjectURL(file);
                        this.preview = this.normalizePreview({
                            name: file.name,
                            url: this.objectUrl,
                            type: this.resolvePreviewType(file.name, '', file.type),
                            mime: file.type || '',
                            sizeText: this.formatFileSize(file.size),
                            source: 'selected',
                        });
                    },
                    handleLinkInput(event) {
                        this.syncLinkPreview(event.target?.value || '');
                    },
                    syncLinkPreview(value) {
                        const linkValue = String(value || '').trim();

                        if (!linkValue) {
                            this.preview = this.normalizePreview(this.initialPreview);
                            return;
                        }

                        const initialUrl = String(this.initialPreview?.url || '').trim();

                        this.preview = this.normalizePreview({
                            name: linkValue,
                            url: linkValue,
                            type: 'link',
                            mime: '',
                            sizeText: '',
                            source: linkValue === initialUrl ? 'existing' : 'selected',
                        });
                    },
                };
            };
        </script>
    @endpush
@endonce

<div
    {{ $attributes->only('class')->class(['space-y-2']) }}
    x-data="assessmentFilePreview({
        mode: @js($mode),
        initialPreview: @js($initialPreview),
    })">
    <div>
        @if ($label)
            <label for="{{ $id }}" class="block text-sm font-semibold text-slate-700">
                {{ $label }}
                @if ($required)
                    <span class="text-red-600">*</span>
                @endif
            </label>
        @endif

        @if ($description)
            <p class="mt-1 block text-sm text-slate-700">
                {{ $description }}
            </p>
        @endif
    </div>

    @if ($mode === 'link')
        <input {{ $attributes->except('class') }} id="{{ $id }}" type="url" name="{{ $name }}"
            value="{{ old($errorKey, $value) }}"
            placeholder="{{ $placeholder ?: 'https://drive.google.com/file/d/.../view' }}"
            @if ($allowedDomains !== []) data-url-allowed-domains="{{ implode(',', $allowedDomains) }}" @endif
            @if ($inputTitle) title="{{ $inputTitle }}" @endif
            @required($required)
            x-ref="linkInput"
            @input="handleLinkInput($event)"
            @change="handleLinkInput($event)"
            @class([
                'block w-full rounded-sm border bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#1376bd] focus:ring-4 focus:ring-[#1376bd]/15',
                'border-red-500 focus:border-red-500 focus:ring-red-500/15' => $errorMessage,
                'border-[#d7e3ee]' => !$errorMessage,
            ])>
    @else
        <input {{ $attributes->except('class') }} id="{{ $id }}" type="file" name="{{ $name }}"
            @required($required)
            @change="handleFileChange($event)"
            @class([
                'block w-full cursor-pointer rounded-sm border bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-sm file:border-0 file:bg-[#eaf5fb] file:px-3 file:py-2 file:font-semibold file:text-[#0d5f98] hover:file:bg-[#dff0fb]',
                'border-red-500' => $errorMessage,
                'border-[#d7e3ee]' => !$errorMessage,
            ])>
    @endif

    <template x-if="preview">
        <div class="overflow-hidden rounded-sm border border-[#dce8f1] bg-[#f8fbfe] text-sm text-slate-600">
            <div class="flex items-start justify-between gap-3 px-4 py-3">
                <div class="flex min-w-0 items-start gap-3">
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-sm bg-[#eaf5fb] text-[#0d5f98]">
                        <i x-bind:class="previewIconClass()"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-800" x-text="previewTitle()"></div>
                        <div class="mt-1 break-all" x-text="preview.name || 'Lampiran tersimpan'"></div>
                        <div class="mt-1 flex flex-wrap gap-2 text-xs text-slate-500">
                            <span x-show="preview.sizeText" x-text="preview.sizeText"></span>
                            <span x-show="preview.mime" x-text="preview.mime"></span>
                        </div>
                    </div>
                </div>
                <a x-show="canOpenPreview()"
                    x-bind:href="canOpenPreview() ? preview.url : '#'"
                    target="_blank"
                    rel="noopener"
                    class="shrink-0 rounded-sm border border-[#b7d7ee] bg-white px-3 py-1.5 text-xs font-semibold text-[#1376bd] transition hover:border-[#1376bd] hover:bg-[#eaf5fb]">
                    Buka
                </a>
            </div>

            <div x-show="preview.type === 'image' && preview.url" class="border-t border-[#dce8f1] bg-white p-3">
                <img x-bind:src="preview.url" x-bind:alt="preview.name || 'Pratinjau file'"
                    class="max-h-72 w-full rounded-sm object-contain">
            </div>

            <div x-show="preview.type === 'pdf' && preview.url" class="border-t border-[#dce8f1] bg-white p-3">
                <iframe x-bind:src="preview.url" title="Pratinjau PDF"
                    class="h-72 w-full rounded-sm border border-[#dce8f1] bg-white"></iframe>
            </div>

            <div class="border-t border-[#dce8f1] px-4 py-2 text-xs text-slate-500" x-text="previewNote()"></div>
        </div>
    </template>

    @if ($hint)
        <p class="block text-sm text-slate-700">
            {{ $hint }}
        </p>
    @endif
</div>
