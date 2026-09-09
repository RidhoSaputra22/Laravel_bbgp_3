@php
    $validatorErrorBag = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $panelStartsOpen = true;
    $taskIsLocked = $selectedValidatorTask?->status === 'submitted';
    $taskIsUpcoming = $selectedValidatorTask?->start_date?->isFuture() ?? false;
    $validatorTaskListUrl = request()->fullUrlWithQuery(['validator_task' => 0]);
@endphp

<div
    x-data="{ open: {{ $panelStartsOpen ? 'true' : 'false' }}, modalOpen: false }"
    x-show="!modalOpen"
    x-cloak
    @assessment-entry-modal-toggle.window="modalOpen = $event.detail.open"
    class="fixed bottom-4 right-4 z-50"
>
    <button x-show="!open" x-cloak type="button" @click="open = true"
        class="relative flex items-center gap-3 rounded-sm bg-[#1376BD] px-5 py-3 font-semibold text-white shadow-xl transition hover:bg-indigo-700">
        <i class="fas fa-clipboard-check"></i>
        Tugas Validasi
        @if ($pendingValidatorTaskCount > 0)
            <span class="absolute -right-2 -top-2 flex h-6 min-w-6 items-center justify-center rounded-md bg-red-500 px-1 text-xs">
                {{ $pendingValidatorTaskCount }}
            </span>
        @endif
    </button>

    <section x-show="open" x-cloak
        class="flex max-h-[calc(100vh-2rem)] w-[min(92vw,36rem)] flex-col overflow-hidden rounded-md border border-slate-200 bg-white shadow-2xl">
        <header class="flex shrink-0 items-center justify-between bg-[#1376BD] px-5 py-4 text-white">
            <div>
                <h2 class="font-bold">Quality Assurance Assessment</h2>
                <p class="text-xs text-indigo-100">
                    {{ $pendingValidatorTaskCount }} perlu dikerjakan · {{ $validatorTaskCount }} total
                </p>
            </div>
            <button type="button" @click="open = false"
                class="rounded-sm p-2 text-indigo-100 transition hover:bg-indigo-500 hover:text-white"
                aria-label="Minimalkan panel validasi">
                <i class="fas fa-minus"></i>
            </button>
        </header>

        <div class="min-h-0 flex-1 overflow-y-auto p-5">
            @if (session('validator_success'))
                <div class="mb-4 rounded-sm border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
                    {{ session('validator_success') }}
                </div>
            @endif

            @if ($validatorErrorBag->any())
                <div class="mb-4 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    <strong>Periksa kembali isian validasi.</strong>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($validatorErrorBag->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($selectedValidatorTask)
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <small class="text-slate-400">{{ $selectedValidatorTask->code }}</small>
                        <h3 class="font-bold text-slate-800">{{ $selectedValidatorTask->title }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $selectedValidatorTask->validatorForm->title }}</p>
                    </div>
                    <a href="{{ $validatorTaskListUrl }}"
                        class="shrink-0 rounded-sm border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        <i class="fas fa-arrow-left"></i> Daftar
                    </a>
                </div>

                <details class="mb-4 rounded-sm border border-slate-200 bg-slate-50 p-3">
                    <summary class="cursor-pointer text-sm font-semibold text-slate-700">Penugasan yang diperiksa</summary>
                    <div class="mt-3 space-y-2">
                        @foreach ($selectedValidatorTask->resolved_assignment_snapshots as $sourceSnapshot)
                            <div class="rounded-md bg-white p-2 text-xs text-slate-600">
                                <strong class="block text-slate-800">{{ data_get($sourceSnapshot, 'title', '-') }}</strong>
                                {{ collect(data_get($sourceSnapshot, 'assessments', []))->pluck('title')->filter()->implode(', ') }}
                            </div>
                        @endforeach
                    </div>
                </details>

                @if ($taskIsUpcoming)
                    <div class="mb-4 rounded-sm bg-blue-50 p-3 text-sm text-blue-700">
                        Tugas dapat diisi mulai {{ $selectedValidatorTask->start_date->format('d-m-Y') }}.
                    </div>
                @endif

                @if ($taskIsLocked)
                    <div class="mb-4 rounded-sm bg-emerald-50 p-3 text-sm text-emerald-700">
                        Hasil sudah dikirim dan dikunci pada {{ $selectedValidatorTask->submitted_at?->format('d-m-Y H:i') }}.
                    </div>
                @endif

                <form method="POST" action="{{ route('assessment.portal.validator.tasks.submit', $selectedValidatorTask) }}"
                    class="space-y-5">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ request()->fullUrlWithQuery(['validator_task' => $selectedValidatorTask->id]) }}">
                    @if ($selectedValidatorTask->validatorForm->instructions)
                        <div class="rounded-sm bg-blue-50 p-3 text-sm text-blue-700">
                            {{ $selectedValidatorTask->validatorForm->instructions }}
                        </div>
                    @endif

                    @foreach ($selectedValidatorTask->validatorForm->sections as $section)
                        <fieldset class="rounded-sm border border-slate-200 p-4">
                            <legend class="px-2 font-bold text-slate-800">{{ $loop->iteration }}. {{ $section->title }}</legend>
                            @if ($section->description)
                                <p class="mb-4 text-xs text-slate-500">{{ $section->description }}</p>
                            @endif

                            <div class="space-y-4">
                                @foreach ($section->fields->where('is_active', true) as $field)
                                    @php
                                        $response = $validatorResponseLookup->get($field->id);
                                        $oldValue = old(
                                            'answers.'.$field->id,
                                            $field->field_type === 'checkbox'
                                                ? ($response?->answer_payload ?? [])
                                                : $response?->answer_text,
                                        );
                                        $inputName = 'answers['.$field->id.']';
                                        $inputClass = 'mt-2 w-full rounded-sm border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none';
                                    @endphp
                                    <div>
                                        <label class="text-sm font-semibold text-slate-700">
                                            {{ $loop->iteration }}. {{ $field->label }}
                                            @if ($field->is_required)<span class="text-red-500">*</span>@endif
                                        </label>
                                        @if ($field->description)
                                            <p class="mt-1 text-xs text-slate-500">{{ $field->description }}</p>
                                        @endif

                                        @switch($field->field_type)
                                            @case('textarea')
                                                <textarea name="{{ $inputName }}" rows="3" class="{{ $inputClass }}"
                                                    @disabled($taskIsLocked || $taskIsUpcoming)>{{ $oldValue }}</textarea>
                                                @break
                                            @case('number')
                                                <input type="number" step="0.01" min="0"
                                                    @if ($field->max_score) max="{{ $field->max_score }}" @endif
                                                    name="{{ $inputName }}" value="{{ $oldValue }}" class="{{ $inputClass }}"
                                                    @disabled($taskIsLocked || $taskIsUpcoming)>
                                                @break
                                            @case('date')
                                                <input type="date" name="{{ $inputName }}" value="{{ $oldValue }}"
                                                    class="{{ $inputClass }}" @disabled($taskIsLocked || $taskIsUpcoming)>
                                                @break
                                            @case('select')
                                                <select name="{{ $inputName }}" class="{{ $inputClass }}"
                                                    @disabled($taskIsLocked || $taskIsUpcoming)>
                                                    <option value="">-- Pilih Jawaban --</option>
                                                    @foreach ($field->resolvedOptions() as $option)
                                                        <option value="{{ $option }}" @selected((string) $oldValue === (string) $option)>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                                @break
                                            @case('checkbox')
                                                <div class="mt-2 space-y-2">
                                                    @foreach ($field->resolvedOptions() as $option)
                                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                                            <input type="checkbox" name="{{ $inputName }}[]" value="{{ $option }}"
                                                                @checked(in_array((string) $option, array_map('strval', (array) $oldValue), true))
                                                                @disabled($taskIsLocked || $taskIsUpcoming)>
                                                            {{ $option }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                                @break
                                            @case('radio')
                                            @case('likert')
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    @foreach ($field->resolvedOptions() as $option)
                                                        <label class="cursor-pointer rounded-sm border border-slate-300 px-3 py-2 text-sm text-slate-700 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:checked]:text-indigo-700">
                                                            <input type="radio" name="{{ $inputName }}" value="{{ $option }}"
                                                                class="mr-1" @checked((string) $oldValue === (string) $option)
                                                                @disabled($taskIsLocked || $taskIsUpcoming)>
                                                            {{ $option }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                                @break
                                            @default
                                                <input type="text" name="{{ $inputName }}" value="{{ $oldValue }}"
                                                    class="{{ $inputClass }}" @disabled($taskIsLocked || $taskIsUpcoming)>
                                        @endswitch
                                        @if ($field->is_scored)
                                            <small class="mt-1 block text-emerald-600">Skor maksimum: {{ $field->max_score }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach

                    <fieldset class="rounded-sm border border-slate-200 p-4">
                        <legend class="px-2 font-bold text-slate-800">Kesimpulan QA</legend>
                        <label class="text-sm font-semibold text-slate-700">Rekomendasi Akhir <span class="text-red-500">*</span></label>
                        <select name="recommendation" class="mt-2 w-full rounded-sm border border-slate-300 px-3 py-2 text-sm"
                            @disabled($taskIsLocked || $taskIsUpcoming)>
                            <option value="">-- Pilih Rekomendasi --</option>
                            @foreach ($validatorRecommendations as $value => $label)
                                <option value="{{ $value }}" @selected(old('recommendation', $selectedValidatorTask->recommendation) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <label class="mt-4 block text-sm font-semibold text-slate-700">Catatan Akhir</label>
                        <textarea name="final_notes" rows="4" class="mt-2 w-full rounded-sm border border-slate-300 px-3 py-2 text-sm"
                            @disabled($taskIsLocked || $taskIsUpcoming)>{{ old('final_notes', $selectedValidatorTask->final_notes) }}</textarea>
                    </fieldset>

                    @unless ($taskIsLocked || $taskIsUpcoming)
                        <div class="sticky bottom-0 flex justify-end gap-2 border-t border-slate-200 bg-white py-3">
                            <button type="submit"
                                formaction="{{ route('assessment.portal.validator.tasks.draft', $selectedValidatorTask) }}"
                                class="rounded-sm border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Simpan Draf
                            </button>
                            <button type="submit"
                                onclick="return confirm('Kirim hasil QA? Setelah dikirim hasil akan dikunci.')"
                                class="rounded-sm bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                Kirim Hasil QA
                            </button>
                        </div>
                    @endunless
                </form>
            @else
                @forelse ($validatorTasks as $task)
                    <a href="{{ request()->fullUrlWithQuery(['validator_task' => $task->id]) }}"
                        class="mb-3 block rounded-sm border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <small class="text-slate-400">{{ $task->code }}</small>
                                <h3 class="font-semibold text-slate-800">{{ $task->title }}</h3>
                                <p class="mt-1 text-xs text-slate-500">{{ $task->assessment_assignments_label }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold
                                {{ $task->status === 'submitted' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }}">
                                {{ $task->status_label }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="py-10 text-center text-sm text-slate-500">
                        <i class="far fa-folder-open mb-3 block text-3xl text-slate-300"></i>
                        Belum ada tugas validasi.
                    </div>
                @endforelse
            @endif
        </div>
    </section>
</div>
