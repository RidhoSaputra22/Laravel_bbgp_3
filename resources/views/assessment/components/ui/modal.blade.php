@props([
    'show' => 'false',
    'title' => null,
    'description' => null,
    'closeAction' => null,
    'maxWidth' => 'max-w-xl',
])

@php
    $closeAction = $closeAction ?: "{$show} = false";
@endphp

<div
    x-show="{{ $show }}"
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
    @keydown.escape.window="{{ $closeAction }}"
>
    <div
        class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm"
        x-transition.opacity
        @click="{{ $closeAction }}"
    ></div>

    <div class="relative w-full {{ $maxWidth }}" x-transition>
        <x-assessment::ui.card class="overflow-hidden rounded-[28px] p-0 shadow-[0_28px_90px_rgba(15,23,42,0.35)]">
            <div class="border-b border-slate-200 px-2 py-3 sm:px-6 sm:py-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        @if ($title)
                            <h3 class="text-xl font-bold text-slate-900">
                                {{ $title }}
                            </h3>
                        @endif

                        @if ($description)
                            <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                {{ $description }}
                            </p>
                        @endif
                    </div>

                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full  text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                        @click="{{ $closeAction }}"
                        aria-label="Tutup modal"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="sm:px-6 sm:py-5 px-2 py-3">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="border-t border-slate-200 px-2 py-3 sm:px-6 sm:py-5">
                    {{ $footer }}
                </div>
            @endisset
        </x-assessment::ui.card>
    </div>
</div>
