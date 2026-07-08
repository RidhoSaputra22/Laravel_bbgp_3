@props([
    'field',
    'number',
    'error' => null,
    'oldValue' => null,
    'checkboxValues' => [],
])

@php
    $answerName = 'answers[' . $field['id'] . ']';
    $textareaMinWords = \App\Support\Assessment\TextareaWordLimit::minWords();
    $textareaMaxWords = \App\Support\Assessment\TextareaWordLimit::maxWords();
    $inputType = match ($field['tipe_field']) {
        'number' => 'number',
        'date' => 'date',
        'email' => 'email',
        default => 'text',
    };
    $isChoiceField = in_array($field['tipe_field'], ['radio', 'checkbox'], true);
@endphp

<div class="mb-[18px] rounded-[20px] border p-[22px] last:mb-0 {{ $error ? 'border-red-500/50 bg-red-50/50' : 'border-[#e4edf4] bg-[#fbfdff]' }}">
    <div class="mb-[14px] inline-flex items-center rounded-sm bg-[#eaf5fb] px-3 py-1 text-xs font-semibold tracking-[0.14em] text-[#0d5f98]">
        Soal {{ $number }}
    </div>

    @if ($isChoiceField)
        <div class="mb-2 text-xl font-bold leading-[1.5] text-[#0d3557] sm:text-[21px]">
            {{ $field['label'] }}
            @if ($field['is_required'])
                <span class="text-red-600">*</span>
            @endif
        </div>

        @if (!empty($field['deskripsi']))
            <div class="mb-2 leading-[1.8] text-[#6c8092]">
                {{ $field['deskripsi'] }}
            </div>
        @endif
    @endif

    @switch($field['tipe_field'])
        @case('textarea')
            <x-assessment::form.textarea :label="$field['label']" :description="$field['deskripsi']" :hint="$field['bantuan']"
                :name="$answerName" :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Tuliskan jawaban Anda'" :required="(bool) $field['is_required']"
                :error="$error" :min-words="$textareaMinWords" :max-words="$textareaMaxWords" />
        @break

        @case('select')
            <x-assessment::form.select :label="$field['label']" :description="$field['deskripsi']" :hint="$field['bantuan']"
                :name="$answerName" placeholder="Pilih jawaban"
                :required="(bool) $field['is_required']" :error="$error">
                @foreach ($field['opsi_field'] ?? [] as $option)
                    <option value="{{ $option['value'] }}" @selected((string) $oldValue === (string) $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </x-assessment::form.select>
        @break

        @case('radio')
            <x-assessment::form.radio-group :name="$answerName" :options="$field['opsi_field'] ?? []"
                :selected="\Illuminate\Support\Arr::wrap($oldValue)" :id-prefix="'field-' . $field['id']" />
        @break

        @case('checkbox')
            <x-assessment::form.checkbox-group :name="$answerName" :options="$field['opsi_field'] ?? []"
                :selected="$checkboxValues" :id-prefix="'field-' . $field['id']" />
        @break

        @case('file')
            <x-assessment::form.file-input :label="$field['label']" :description="$field['deskripsi']" :hint="$field['bantuan']"
                :name="$answerName" :required="(bool) $field['is_required']" :error="$error" />
        @break

        @default
            <x-assessment::form.input :label="$field['label']" :description="$field['deskripsi']" :hint="$field['bantuan']"
                :type="$inputType" :name="$answerName" :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Masukkan jawaban Anda'"
                :required="(bool) $field['is_required']" :error="$error" />
    @endswitch

    @if ($error)
        <div class="mt-2 text-sm text-red-600">
            {{ $error }}
        </div>
    @endif

    @if ($isChoiceField && !empty($field['bantuan']))
        <div class="mt-2.5 text-sm leading-[1.8] text-[#6c8092]">
            <i class="far fa-lightbulb mr-1"></i>
            {{ $field['bantuan'] }}
        </div>
    @endif
</div>
