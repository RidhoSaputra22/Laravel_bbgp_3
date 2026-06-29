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
        ->values()
        ->all();
    $inputName = str_ends_with($name, '[]') ? $name : $name . '[]';
    $idPrefix = $idPrefix ?: trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-');
    $normalizedOptions = collect($options ?? [])
        ->map(function ($option, $index) {
            if (is_array($option)) {
                $value = trim((string) ($option['value'] ?? ''));
                $label = trim((string) ($option['label'] ?? $value));
                $description = array_key_exists('description', $option)
                    ? trim((string) ($option['description'] ?? ''))
                    : null;
            } else {
                $value = trim((string) $option);
                $label = $value;
                $description = null;
            }

            return [
                'index' => $index,
                'value' => $value,
                'label' => $label,
                'description' => $description ?: null,
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
    @foreach ($normalizedOptions as $option)
        <x-assessment::form.choice-option :id="$idPrefix . '-' . $option['index']" type="checkbox" :name="$inputName"
            :value="$option['value']" :checked="in_array((string) $option['value'], $selectedValues, true)"
            :label="$option['label']" :description="$option['description']" :disabled="$disabled" />
    @endforeach
</div>
