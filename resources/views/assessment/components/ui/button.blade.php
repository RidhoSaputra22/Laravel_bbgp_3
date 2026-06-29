@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
    'icon' => null,
    'disabled' => false,

])

@php
    $variants = [
        'primary' => 'border border-[#1376bd] bg-[#1376bd] text-white hover:bg-[#0f619c] focus:ring-[#1376bd]/25',
        'outline' => 'border border-[#1376bd] bg-transparent text-[#1376bd] hover:bg-[#1376bd] hover:text-white focus:ring-[#1376bd]/20',
        'white' => 'border border-transparent bg-white text-[#0d5f98] hover:bg-slate-100 focus:ring-white/30',
        'muted' => 'cursor-not-allowed border border-transparent bg-slate-100 text-slate-400',
    ];

    $buttonClasses = [
        'cursor-pointer inline-flex items-center justify-center text-sm font-semibold transition focus:outline-none focus:ring-4 px-3 py-2 rounded-sm ',

        $variants[$variant] ?? $variants['primary'],
    ];
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        {{ $attributes->class($buttonClasses) }}
    >
        @if ($icon)
            <i class="{{ $icon }} mr-2"></i>
        @endif

        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($disabled)
        {{ $attributes->class($buttonClasses) }}
    >
        @if ($icon)
            <i class="{{ $icon }} mr-2"></i>
        @endif

        {{ $slot }}
    </button>
@endif
