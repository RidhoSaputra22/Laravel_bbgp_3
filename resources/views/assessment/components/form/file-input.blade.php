@props([
    'name',
    'id' => null,
    'label' => null,
    'description' => null,
    'hint' => null,
    'required' => false,
    'error' => null,
])

@php
    $id = $id ?: trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-');
    $errorKey = trim((string) preg_replace('/\[(.*?)\]/', '.$1', $name), '.');
    $errorBag = $errors ?? null;
    $errorMessage = $error ?: ($errorBag ? $errorBag->first($errorKey) : null);
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

    <input {{ $attributes->except('class') }} id="{{ $id }}" type="file" name="{{ $name }}"
        @required($required)
        @class([
            'block w-full cursor-pointer rounded-sm border bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-sm file:border-0 file:bg-[#eaf5fb] file:px-3 file:py-2 file:font-semibold file:text-[#0d5f98] hover:file:bg-[#dff0fb]',
            'border-red-500' => $errorMessage,
            'border-[#d7e3ee]' => !$errorMessage,
        ])>

    @if ($hint)
        <p class="block text-sm text-slate-700">
            {{ $hint }}
        </p>
    @endif
</div>
