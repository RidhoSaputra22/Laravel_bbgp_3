@php
    $questionNumberStart = isset($questionNumberStart) ? max((int) $questionNumberStart, 1) : 1;
    $hideFormHeader = (bool) ($hideFormHeader ?? false);
@endphp

<x-assessment::ui.card>
    @unless($hideFormHeader)
        <div class="mb-8">
            <h4 class="text-lg font-bold text-slate-800">
                {{ $form['judul_form'] }}
            </h4>

            <div class="mb-3 text-slate-700">
                {{ $form['deskripsi'] ?: 'Isi pertanyaan pada bagian ini sesuai kondisi terbaru Anda.' }}
            </div>
        </div>
    @endunless

    @foreach ($form['fields'] ?? [] as $fieldIndex => $field)
        @include('assessment.show.partials.assessment-field', [
            'field' => $field,
            'assessmentIndex' => $assessmentIndex ?? 0,
            'displayQuestionNumber' => $questionNumberStart + $fieldIndex,
            'displayQuestionPrefix' => $displayQuestionPrefix ?? null,
        ])
    @endforeach
</x-assessment::ui.card>
