@props([
    'id',
    'name',
    'type' => 'radio',
    'value',
    'checked' => false,
    'label',
    'description' => null,
    'required' => null,
    'hint' => null,
    'disabled' => false,

])

<label for="{{ $id }}" @class([
    'block rounded-sm border border-[#dce8f1] bg-white transition',

    'px-3 py-3.5',
    'cursor-pointer hover:border-[#1376bd]/60' => !$disabled,
    'cursor-not-allowed bg-slate-50 opacity-80' => $disabled,
])>
    <span class="flex items-start gap-3">
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}"
            @checked($checked) @required($required) @disabled($disabled) @class([
                'mt-1 h-4 w-4 border-slate-300 text-[#1376bd] focus:ring-[#1376bd]/30',
                'rounded' => $type === 'checkbox',
            ])>

        <span class="block flex-1">
            <div class="flex gap-4 justify-cenetr">
                <h1 class="font-semibold">
                    {{ $label . '. '}}
                </h1>
                <p>
                    {{ $description }}
                </p>

            </div>

            @if ($hint)
                <p class="mt-2 block text-sm text-slate-700">
                    {{ $hint }}
                </p>
            @endif
        </span>
    </span>
</label>
