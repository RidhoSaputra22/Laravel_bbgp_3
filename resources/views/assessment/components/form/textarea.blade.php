@props([
    'name',
    'id' => null,
    'label' => null,
    'description' => null,
    'hint' => null,
    'value' => null,
    'placeholder' => null,
    'rows' => 5,
    'required' => false,
    'error' => null,
    'minWords' => null,
    'maxWords' => null,
])

@php
    $id = $id ?: trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-');
    $errorKey = trim((string) preg_replace('/\[(.*?)\]/', '.$1', $name), '.');
    $errorBag = $errors ?? null;
    $errorMessage = $error ?: ($errorBag ? $errorBag->first($errorKey) : null);
    $minWords = is_numeric($minWords) ? max((int) $minWords, 0) : null;
    $maxWords = is_numeric($maxWords) ? max((int) $maxWords, 0) : null;
    $hasWordLimit = ($minWords ?? 0) > 0 || ($maxWords ?? 0) > 0;
    $wordCount = $hasWordLimit ? \App\Support\Assessment\TextareaWordLimit::count((string) $value) : null;
    $wordLimitText = collect([
        ($minWords ?? 0) > 0 ? 'Minimal '.$minWords.' kata' : null,
        ($maxWords ?? 0) > 0 ? 'maksimal '.$maxWords.' kata' : null,
    ])->filter()->implode(', ');
    $wordCountText = $hasWordLimit
        ? trim(($wordCount ?? 0).' kata'.($wordLimitText !== '' ? ' / '.$wordLimitText : ''))
        : null;
@endphp

<div {{ $attributes->only('class')->class(['space-y-2']) }}>
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

    <textarea {{ $attributes->except('class') }} id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
        placeholder="{{ $placeholder }}" @required($required)
        @if ($hasWordLimit)
            data-textarea-word-limit="1"
            data-min-words="{{ $minWords }}"
            data-max-words="{{ $maxWords }}"
        @endif
        @class([
            'w-full rounded-sm border bg-white px-4 py-3 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-[#1376bd] focus:ring-4 focus:ring-[#1376bd]/15',
            'border-red-500 focus:border-red-500 focus:ring-red-500/15' => $errorMessage,
            'border-[#d7e3ee]' => !$errorMessage,
        ])>{{ $value }}</textarea>

    @if ($hint || $hasWordLimit)
        <div class="flex flex-col gap-1 text-sm text-slate-700 sm:flex-row sm:items-start sm:justify-between sm:gap-3">
            <div class="space-y-1">
                @if ($hint)
                    <p class="block text-sm text-slate-700">
                        {{ $hint }}
                    </p>
                @endif

                @if ($hasWordLimit && $wordLimitText !== '')
                    <p class="block text-xs text-slate-500">
                        {{ ucfirst($wordLimitText) }}.
                    </p>
                @endif
            </div>

            @if ($hasWordLimit)
                <p class="shrink-0 text-xs text-slate-500" data-word-count-display>
                    {{ $wordCountText }}
                </p>
            @endif
        </div>
    @endif
</div>
