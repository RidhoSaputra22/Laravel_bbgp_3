@props([
    'name',
    'options' => [],
    'selected' => [],
    'idPrefix' => null,
    'label' => null,
    'description' => null,
    'required' => null,
    'disabled' => false,
])

@php
    $selectedValues = collect(\Illuminate\Support\Arr::wrap($selected))
        ->map(fn($value) => trim((string) $value))
        ->filter(fn($value) => $value !== '')
        ->values();
    $selectedValue = $selectedValues->first();
    $idPrefix = $idPrefix ?: trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-');
    $sourceOptions = is_array($options ?? null) && ($options ?? []) !== []
        ? $options
        : \App\Support\Assessment\LikertScale::defaultOptions();
    $normalizedOptions = collect(\App\Support\Assessment\ChoiceOptionNormalizer::normalizeMany($sourceOptions))
        ->map(function ($option, $index) {
            $value = trim((string) ($option['value'] ?? ''));
            $label = trim((string) ($option['label'] ?? $value));
            $matchValues = collect($option['aliases'] ?? [$value, $label])
                ->map(fn($item) => trim((string) $item))
                ->filter(fn($item) => $item !== '')
                ->unique()
                ->values()
                ->all();

            return [
                'index' => $index,
                'value' => $value,
                'label' => $label,
                'match_values' => $matchValues,
            ];
        })
        ->filter(fn($option) => $option['value'] !== '')
        ->values();
@endphp

<div {{ $attributes->class(['space-y-3']) }}>
    <div>
        @if ($label)
            <label class="block text-sm font-semibold text-slate-700">
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

    <div class="grid gap-2 sm:grid-cols-5">
        @foreach ($normalizedOptions as $option)
            @php
                $inputId = $idPrefix . '-' . $option['index'];
                $checked = $selectedValue !== null && in_array($selectedValue, $option['match_values'], true);
            @endphp
            <label for="{{ $inputId }}" @class([
                'flex min-h-[76px] cursor-pointer items-center rounded-sm border bg-white px-3 py-3 text-sm font-semibold text-slate-700 transition',
                'border-[#1376bd] ring-2 ring-[#1376bd]/15' => $checked,
                'border-[#dce8f1] hover:border-[#1376bd]/60' => !$checked && !$disabled,
                'cursor-not-allowed bg-slate-50 opacity-80' => $disabled,
            ])>
                <input id="{{ $inputId }}" type="radio" name="{{ $name }}" value="{{ $option['value'] }}"
                    @checked($checked) @required($required) @disabled($disabled)
                    class="mr-3 h-4 w-4 border-slate-300 text-[#1376bd] focus:ring-[#1376bd]/30">
                <span>{{ $option['label'] }}</span>
            </label>
        @endforeach
    </div>
</div>
