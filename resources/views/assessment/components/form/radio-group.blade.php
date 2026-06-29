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
    $resolveDisplayLabel = static function (int $index): string {
        $label = '';
        $sequence = $index + 1;

        while ($sequence > 0) {
            $remainder = ($sequence - 1) % 26;
            $label = chr(65 + $remainder) . $label;
            $sequence = intdiv($sequence - 1, 26);
        }

        return $label;
    };
    $normalizedOptions = collect(\App\Support\Assessment\ChoiceOptionNormalizer::normalizeMany($options ?? []))
        ->map(function ($option, $index) use ($resolveDisplayLabel) {
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
                'label' => $resolveDisplayLabel($index),
                'description' => $label !== '' ? $label : null,
                'match_values' => $matchValues,
            ];
        })
        ->filter(fn($option) => $option['value'] !== '')
        ->values();
@endphp

<div {{ $attributes->class(['space-y-3']) }}>
     <div>
        @if ($label)
            <label  class="block text-sm font-semibold text-slate-700">
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
    @foreach ($normalizedOptions as $option)
        <x-assessment::form.choice-option :id="$idPrefix . '-' . $option['index']" type="radio" :name="$name"
            :value="$option['value']" :checked="$selectedValue !== null && in_array($selectedValue, $option['match_values'], true)"
            :label="$option['label']" :description="$option['description']" layout="split"
            :disabled="$disabled" />
    @endforeach
</div>
