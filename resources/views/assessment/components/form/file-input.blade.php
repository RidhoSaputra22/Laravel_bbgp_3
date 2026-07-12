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
])

@php
    $id = $id ?: trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-');
    $errorKey = trim((string) preg_replace('/\[(.*?)\]/', '.$1', $name), '.');
    $errorBag = $errors ?? null;
    $errorMessage = $error ?: ($errorBag ? $errorBag->first($errorKey) : null);
    $mode = in_array(trim((string) $mode), ['file', 'link'], true) ? trim((string) $mode) : 'file';
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

    @if ($mode === 'link')
        <input {{ $attributes->except('class') }} id="{{ $id }}" type="url" name="{{ $name }}"
            value="{{ old($errorKey, $value) }}"
            placeholder="{{ $placeholder ?: 'https://drive.google.com/file/d/.../view' }}"
            @required($required)
            @class([
                'block w-full rounded-sm border bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#1376bd] focus:ring-4 focus:ring-[#1376bd]/15',
                'border-red-500 focus:border-red-500 focus:ring-red-500/15' => $errorMessage,
                'border-[#d7e3ee]' => !$errorMessage,
            ])>
    @else
        <input {{ $attributes->except('class') }} id="{{ $id }}" type="file" name="{{ $name }}"
            @required($required)
            @class([
                'block w-full cursor-pointer rounded-sm border bg-white px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-sm file:border-0 file:bg-[#eaf5fb] file:px-3 file:py-2 file:font-semibold file:text-[#0d5f98] hover:file:bg-[#dff0fb]',
                'border-red-500' => $errorMessage,
                'border-[#d7e3ee]' => !$errorMessage,
            ])>
    @endif

    @if ($hint)
        <p class="block text-sm text-slate-700">
            {{ $hint }}
        </p>
    @endif
</div>
