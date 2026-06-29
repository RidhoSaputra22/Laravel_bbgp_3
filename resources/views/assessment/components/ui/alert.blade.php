@props([
    'type' => 'info',
])

@php
    $styles = [
        'success' => 'border-green-200 bg-green-50 text-green-800',
        'danger' => 'border-red-200 bg-red-50 text-red-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
        'info' => 'border-sky-200 bg-sky-50 text-sky-800',
    ];
@endphp

<div x-data="{
    open: true,
    init() {
        setTimeout(() => this.open = false, 4000)
    }
}" x-show="open" x-cloak x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-x-4 opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0 opacity-100"
    x-transition:leave-end="translate-x-4 opacity-0"
    {{ $attributes->class([
        'absolute bottom-2 left-5 z-50 flex items-center gap-3 rounded-sm min-w-32 max-w-96 border px-4 py-3 text-sm shadow-sm',
        $styles[$type] ?? $styles['info'],
    ]) }}
    role="alert">
    <div class=" shrink-0">
        @switch($type)
            @case('success')
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z" />
                </svg>
            @break

            @case('danger')
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.95 3.374H4.647c-1.733 0-2.816-1.874-1.95-3.374L10.05 3.374c.866-1.5 3.034-1.5 3.9 0l7.353 12.752ZM12 16.5h.008v.008H12V16.5Z" />
                </svg>
            @break

            @case('warning')
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.95 3.374H4.647c-1.733 0-2.816-1.874-1.95-3.374L10.05 3.374c.866-1.5 3.034-1.5 3.9 0l7.353 12.752ZM12 16.5h.008v.008H12V16.5Z" />
                </svg>
            @break

            @default
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v.01M12 12v4.5m9.75-4.5a9.75 9.75 0 1 1-19.5 0 9.75 9.75 0 0 1 19.5 0Z" />
                </svg>
        @endswitch
    </div>

    <div class="min-w-0 flex-1">
        {{ $slot }}
    </div>

    <button type="button" @click="open = false" class=" rounded p-1 opacity-70 transition hover:opacity-100"
        aria-label="Tutup notifikasi">
        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
        </svg>
    </button>
</div>
