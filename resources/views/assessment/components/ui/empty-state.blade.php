@props([
    'icon',
    'title',
    'description',
])

<div {{ $attributes->class(['px-[30px] pb-12 pt-11 text-center text-[#6d8092]']) }}>
    <div class="mb-[18px] inline-flex h-[78px] w-[78px] items-center justify-center rounded-full bg-[#eef6fb] text-[28px] text-[#1376bd]">
        <i class="{{ $icon }}"></i>
    </div>

    <h4 class="mb-2 text-xl font-bold text-slate-900">
        {{ $title }}
    </h4>

    <p>
        {{ $description }}
    </p>
</div>
