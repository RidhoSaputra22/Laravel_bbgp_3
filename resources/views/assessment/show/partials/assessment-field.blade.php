@php
    $savedAnswer = $answerLookup[(int) $field['id']] ?? [];
    $savedPayload = is_array($savedAnswer['payload'] ?? null) ? $savedAnswer['payload'] : [];
    $fieldError = $errors->first('answers.' . $field['id']);
    $hasFieldError = filled($fieldError);
    $assessmentIndex = isset($assessmentIndex) ? max((int) $assessmentIndex, 0) : 0;
    $displayQuestionNumber = isset($displayQuestionNumber) ? max((int) $displayQuestionNumber, 0) : null;
    $displayQuestionPrefix = isset($displayQuestionPrefix) ? trim((string) $displayQuestionPrefix) : '';
    $fieldLabel = trim((string) ($field['label'] ?? ''));
    $displayLabel = $fieldLabel;

    if ($displayQuestionNumber && $fieldLabel !== '') {
        $normalizedLabel = preg_replace(
            '/^\s*(?:soal\s*)?\d+\s*[\.\)\-:]?\s*/iu',
            '',
            $fieldLabel,
            1
        ) ?? $fieldLabel;
        $displayLead = $displayQuestionPrefix !== ''
            ? $displayQuestionPrefix . ' ' . $displayQuestionNumber
            : (string) $displayQuestionNumber;

        $displayLabel = trim($displayLead . ($normalizedLabel !== '' ? '. ' . trim($normalizedLabel) : ''));
    }

    $oldValue = old('answers.' . $field['id'], $savedPayload['value'] ?? $savedAnswer['text'] ?? null);
    $checkboxValues = collect(old('answers.' . $field['id'], $savedPayload['values'] ?? []))
        ->map(fn($value) => (string) $value)
        ->all();
    $repeaterConfig = is_array($field['opsi_field'] ?? null) ? $field['opsi_field'] : [];
    $repeaterColumns = collect($repeaterConfig['columns'] ?? [])
        ->filter(fn($column) => is_array($column))
        ->values()
        ->all();
    $repeaterRows = collect(old('answers.' . $field['id'], $savedAnswer['rows'] ?? []))
        ->filter(fn($row) => is_array($row))
        ->values()
        ->all();
    $answerName = 'answers[' . $field['id'] . ']';
    $fieldType = $field['tipe_field'];
    $isRequired = (bool) ($field['is_required'] ?? false);
    $existingFileUrl = $savedAnswer['file_url'] ?? null;
    $existingFileName = $savedPayload['original_name'] ?? ($savedAnswer['text'] ?? null);
    $hasExistingFile = filled($existingFileUrl) || filled($savedAnswer['file_path'] ?? null);
    $textareaMinWords = \App\Support\Assessment\TextareaWordLimit::minWords();
    $textareaMaxWords = \App\Support\Assessment\TextareaWordLimit::maxWords();
    $textareaWordHelperText = \App\Support\Assessment\TextareaWordLimit::helperText();
    $isInitiallyFlagged = collect(\Illuminate\Support\Arr::wrap(
        old('flagged_field_ids', data_get($attempt->structure_snapshot ?? [], 'meta.flagged_field_ids', []))
    ))
        ->map(fn($flaggedFieldId) => (int) $flaggedFieldId)
        ->contains((int) $field['id']);
    $inputId = 'assessment-field-' . $field['id'];
    $inputType = match ($field['tipe_field']) {
        'number' => 'number',
        'date' => 'date',
        'email' => 'email',
        default => 'text',
    };
@endphp

<div
    @class([
        'mb-8 scroll-mt-32 transition-all duration-200',
        'rounded-sm border border-red-200 bg-red-50/70 p-1' => $hasFieldError,
    ])
    x-bind:class="fieldWrapperClass({{ (int) $field['id'] }}, {{ $assessmentIndex }})"
    data-assessment-field
    data-field-id="{{ $field['id'] }}" data-field-type="{{ $fieldType }}" data-field-label="{{ $displayLabel }}"
    data-required="{{ $isRequired ? '1' : '0' }}" data-has-existing-file="{{ $hasExistingFile ? '1' : '0' }}"
    data-question-number="{{ $displayQuestionNumber }}" data-assessment-index="{{ $assessmentIndex }}">
    <div class="mb-3 flex items-start justify-between gap-4">
        <div class="min-w-0">
            <label @if (!in_array($fieldType, ['radio', 'checkbox', 'repeater'], true)) for="{{ $inputId }}" @endif
                class="block text-sm font-semibold text-slate-700">
                {{ $displayLabel }}
                @if ($isRequired)
                    <span class="text-red-600">*</span>
                @endif
            </label>

            @if (!empty($field['deskripsi']))
                <p class="mt-1 text-sm text-slate-600">
                    {{ $field['deskripsi'] }}
                </p>
            @endif
        </div>

        <button type="button"
            class="inline-flex shrink-0 items-center gap-2 rounded-sm border px-3 py-1 text-xs font-semibold transition"
            x-bind:class="isFieldFlagged({{ (int) $field['id'] }})
                ? 'border-amber-300 bg-amber-50 text-amber-700'
                : 'border-[#d7e3ee] bg-white text-slate-500 hover:border-amber-300 hover:text-amber-700'"
            x-bind:aria-pressed="isFieldFlagged({{ (int) $field['id'] }}) ? 'true' : 'false'"
            @click="toggleFlag({{ (int) $field['id'] }})">
            <i class="fas fa-flag text-[11px]"></i>
            <span x-text="isFieldFlagged({{ (int) $field['id'] }}) ? 'Ditandai' : 'Flag'"></span>
        </button>
    </div>

    <input type="hidden" name="flagged_field_ids[]" value="{{ (int) $field['id'] }}" @disabled(! $isInitiallyFlagged)
        x-bind:disabled="!isFieldFlagged({{ (int) $field['id'] }})">

    @switch($fieldType)
        @case('textarea')
            <x-assessment::form.textarea :id="$inputId" :label="null" :description="null" :name="$answerName"
                :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Tuliskan jawaban Anda'" :required="$isRequired"
                :error="$fieldError" :min-words="$textareaMinWords" :max-words="$textareaMaxWords" />
        @break

        @case('select')
            <x-assessment::form.select :id="$inputId" :label="null" :description="null" :name="$answerName"
                placeholder="Pilih jawaban"
                :required="$isRequired" :error="$fieldError">
                @foreach ($field['opsi_field'] ?? [] as $option)
                    <option value="{{ $option['value'] }}" @selected((string) $oldValue === (string) $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </x-assessment::form.select>
        @break

        @case('radio')
            <x-assessment::form.radio-group :label="null" :description="null" :name="$answerName" :options="$field['opsi_field'] ?? []"
                :selected="\Illuminate\Support\Arr::wrap($oldValue)" :id-prefix="'field-' . $field['id']"
                :required="$isRequired" />


        @break

        @case('checkbox')
            <x-assessment::form.checkbox-group :label="null" :description="null" :name="$answerName" :options="$field['opsi_field'] ?? []"
                :selected="$checkboxValues" :id-prefix="'field-' . $field['id']"
                :required="$isRequired" />


        @break

        @case('file')
            <x-assessment::form.file-input :id="$inputId" :label="null" :description="null" :name="$answerName"
                :required="$isRequired" :error="$fieldError" />

            @if ($hasExistingFile)
                <div class="rounded-sm border border-[#dce8f1] bg-[#f8fbfe] px-4 py-3 text-sm text-slate-600">
                    <div class="font-semibold text-slate-800">File snapshot tersimpan</div>
                    <div class="mt-1">
                        {{ $existingFileName ?: 'Lampiran tersimpan' }}
                    </div>
                    @if ($existingFileUrl)
                        <a href="{{ $existingFileUrl }}" target="_blank" rel="noopener"
                            class="mt-2 inline-flex items-center text-[#1376bd] hover:underline">
                            Lihat file saat ini
                        </a>
                    @endif
                    <div class="mt-2 text-xs text-slate-500">
                        Pilih file baru hanya jika ingin mengganti lampiran yang sudah tersimpan.
                    </div>
                </div>
            @endif
        @break

        @case('repeater')
            <div
                x-data="assessmentRepeaterField({
                    initialRows: @js($repeaterRows),
                    columns: @js($repeaterColumns),
                    fieldNamePrefix: @js($answerName),
                    minRows: {{ (int) ($repeaterConfig['min_rows'] ?? 0) }},
                    maxRows: {{ (int) ($repeaterConfig['max_rows'] ?? 0) }},
                })"
                class="space-y-3"
            >
                <template x-for="(row, rowIndex) in rows" :key="row._key">
                    <div class="overflow-hidden rounded-sm border border-[#dce8f1] bg-[#f8fbfe]">
                        <div class="flex items-center justify-between border-b border-[#dce8f1] px-4 py-3">
                            <div class="text-sm font-semibold text-slate-700">
                                Entri <span x-text="rowIndex + 1"></span>
                            </div>
                            <button
                                type="button"
                                class="text-sm font-semibold text-red-600 disabled:cursor-not-allowed disabled:text-slate-400"
                                @click="removeRow(rowIndex)"
                                :disabled="! canRemove()"
                            >
                                Hapus
                            </button>
                        </div>

                        <div class="grid gap-4 px-4 py-4 md:grid-cols-2">
                            @foreach ($repeaterColumns as $column)
                                @php
                                    $columnName = $column['nama_field'] ?? 'kolom';
                                    $columnType = $column['tipe_field'] ?? 'text';
                                @endphp
                                <div class="{{ $columnType === 'textarea' ? 'md:col-span-2' : '' }}">
                                    <label class="mb-2 block text-sm font-medium text-slate-700">
                                        {{ $column['label'] ?? $columnName }}
                                        @if (!empty($column['is_required']))
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>

                                    @if ($columnType === 'select')
                                        <select
                                            class="w-full rounded-sm border border-[#d0dbe5] px-3 py-2 text-sm text-slate-700 focus:border-[#1376bd] focus:outline-none focus:ring-2 focus:ring-[#1376bd]/20"
                                            :name="fieldName(rowIndex, '{{ $columnName }}')"
                                            x-model="row['{{ $columnName }}']"
                                            data-repeater-required="{{ !empty($column['is_required']) ? '1' : '0' }}"
                                            data-repeater-label="{{ $column['label'] ?? $columnName }}"
                                        >
                                            <option value="">Pilih</option>
                                            @foreach (($column['opsi_field'] ?? []) as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @elseif ($columnType === 'textarea')
                                        <div class="space-y-2">
                                            <textarea
                                                rows="3"
                                                class="w-full rounded-sm border border-[#d0dbe5] px-3 py-2 text-sm text-slate-700 focus:border-[#1376bd] focus:outline-none focus:ring-2 focus:ring-[#1376bd]/20"
                                                :name="fieldName(rowIndex, '{{ $columnName }}')"
                                                x-model="row['{{ $columnName }}']"
                                                placeholder="{{ $column['placeholder'] ?? '' }}"
                                                data-textarea-word-limit="1"
                                                data-min-words="{{ $textareaMinWords }}"
                                                data-max-words="{{ $textareaMaxWords }}"
                                                data-repeater-required="{{ !empty($column['is_required']) ? '1' : '0' }}"
                                                data-repeater-label="{{ $column['label'] ?? $columnName }}"
                                            ></textarea>
                                            <div class="flex items-center justify-between gap-3 text-xs text-slate-500">
                                                <p>{{ $textareaWordHelperText }}</p>
                                                <p data-word-count-display>0 kata / Minimal {{ $textareaMinWords }} kata, maksimal {{ $textareaMaxWords }} kata</p>
                                            </div>
                                        </div>
                                    @else
                                        <input
                                            type="{{ in_array($columnType, ['number', 'email', 'date'], true) ? $columnType : 'text' }}"
                                            class="w-full rounded-sm border border-[#d0dbe5] px-3 py-2 text-sm text-slate-700 focus:border-[#1376bd] focus:outline-none focus:ring-2 focus:ring-[#1376bd]/20"
                                            :name="fieldName(rowIndex, '{{ $columnName }}')"
                                            x-model="row['{{ $columnName }}']"
                                            placeholder="{{ $column['placeholder'] ?? '' }}"
                                            data-repeater-required="{{ !empty($column['is_required']) ? '1' : '0' }}"
                                            data-repeater-label="{{ $column['label'] ?? $columnName }}"
                                        >
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </template>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="text-sm text-slate-500">
                        <span x-text="rows.length"></span> entri terisi
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-sm border border-[#1376bd] px-3 py-2 text-sm font-semibold text-[#1376bd] transition hover:bg-[#1376bd] hover:text-white disabled:cursor-not-allowed disabled:border-slate-300 disabled:text-slate-400"
                        @click="addRow()"
                        :disabled="! canAdd()"
                    >
                        Tambah Baris
                    </button>
                </div>
            </div>
        @break

        @default
            <x-assessment::form.input :id="$inputId" :label="null" :description="null" :type="$inputType"
                :name="$answerName" :value="$oldValue"
                :placeholder="$field['placeholder'] ?: 'Masukkan jawaban Anda'"
                :required="$isRequired" :error="$fieldError" />
    @endswitch

    @if ($fieldError)
        <div class="mt-3 rounded-lg border border-red-200 bg-white/80 px-4 py-3 text-sm leading-6 text-red-700"
            data-field-error role="alert">
            {{ $fieldError }}
        </div>
    @else
        <div class="mt-3 hidden rounded-lg border border-red-200 bg-white/80 px-4 py-3 text-sm leading-6 text-red-700"
            data-field-error role="alert"></div>
    @endif
</div>
