@php
    $showTitle = $showTitle ?? true;
@endphp

<div class="space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold text-slate-900">
                Navigasi Soal
            </h3>
            <p class="mt-1 text-sm text-slate-500">
                Klik nomor soal untuk berpindah dan pantau mana yang sudah dijawab.
            </p>
        </div>

        <div class="flex flex-wrap gap-2 text-xs">
            <span
                class="inline-flex items-center rounded-sm bg-[#eaf5fb] px-3 py-1 text-xs font-semibold text-[#0d5f98]">
                <span x-text="answeredQuestionCount()"></span>&nbsp;terjawab
            </span>
            <span
                class="inline-flex items-center rounded-sm bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                <span x-text="unansweredQuestionCount()"></span>&nbsp;belum
            </span>
            <span class="inline-flex items-center rounded-sm bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                <span x-text="flaggedQuestionCount()"></span>&nbsp;flag
            </span>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 text-xs text-slate-500">
        <span class="inline-flex items-center gap-2">
            <span class="h-3 w-3 rounded-sm border border-[#1376bd] bg-[#1376bd]"></span>
            Terjawab
        </span>
        <span class="inline-flex items-center gap-2">
            <span class="h-3 w-3 rounded-sm border border-[#d7e3ee] bg-white"></span>
            Belum dijawab
        </span>
        <span class="inline-flex items-center gap-2">
            <span class="h-3 w-3 rounded-sm border border-amber-300 bg-amber-200"></span>
            Ditandai
        </span>
    </div>

    <div class="h-full space-y-4  pr-1 text-xs">
        @foreach ($questionNavigationGroups as $group)
            <div class="space-y-2">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                            {{ $group['heading'] }}
                        </div>
                        <div class="truncate text-sm font-semibold text-slate-800">
                            {{ $group['title'] }}
                        </div>
                    </div>

                    <span class="shrink-0 rounded-sm bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                        {{ $group['question_count'] }} soal
                    </span>
                </div>

                <div class="grid grid-cols-5 gap-2 sm:grid-cols-12 2xl:grid-cols-10">
                    @foreach ($group['questions'] as $question)
                        <button type="button"
                            class="relative flex h-10 w-full items-center justify-center rounded-sm border text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-[#0d5f98]/30"
                            data-question-nav-field="{{ $question['field_id'] }}"
                            x-bind:class="questionButtonClass({{ $question['field_id'] }},
                                {{ $group['assessment_index'] }})"
                            x-bind:title="questionButtonTitle({{ $question['field_id'] }})"
                            @click="goToQuestion({{ $question['field_id'] }}, {{ $group['assessment_index'] }})">
                            <span>{{ $question['number'] }}</span>

                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
