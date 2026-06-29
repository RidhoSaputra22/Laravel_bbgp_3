@props([
    'label',
    'value' => null,
    'valueClass' => 'font-bold text-slate-900',
    'first' => false,
])

<div {{ $attributes->class(['py-3', ]) }}>
    <div class="text-sm text-slate-500">
        {{ $label }}
    </div>

    <div class="{{ $valueClass }}">
        @if ($slot->isEmpty())
            {{ $value }}
        @else
            {{ $slot }}
        @endif
    </div>
</div>
