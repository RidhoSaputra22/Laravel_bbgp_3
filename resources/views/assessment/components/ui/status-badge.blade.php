@props([
    'tone' => 'secondary',
])

@php
    $tones = [
        'success' => 'bg-[#27ae60]/[0.12] text-[#1f8b4d]',
        'warning' => 'bg-[#f5a623]/[0.14] text-[#b36b00]',
        'primary' => 'bg-[#1376bd]/[0.14] text-[#0d5f98]',
        'secondary' => 'bg-[#6c7a89]/[0.14] text-[#5f6f7f]',
        'danger' => 'bg-[#da4453]/[0.14] text-[#bf3041]',
        'info' => 'bg-[#3498db]/[0.14] text-[#217cb5]',
        'dark' => 'bg-[#343a40]/[0.14] text-[#2f3438]',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center text-xs font-bold', $tones[$tone] ?? $tones['secondary']],) }}>
    {{ $slot }}
</span>
