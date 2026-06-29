@props([
    'title' => 'Sisa Waktu',
    'targetAt' => null,
    'caption' => null,
    'expiredLabel' => 'Waktu sesi telah berakhir.',
    'fallbackLabel' => 'Timer belum tersedia untuk sesi ini.',
])

@php
    $deadline = match (true) {
        $targetAt instanceof \Carbon\CarbonInterface => $targetAt->copy(),
        filled($targetAt) => \Illuminate\Support\Carbon::parse($targetAt),
        default => null,
    };

    $deadlineIso = $deadline?->toIso8601String();
    $deadlineText = $deadline?->format('d M Y H:i').' WITA';
@endphp

<div
    x-data="{
        targetAt: @js($deadlineIso),
        unavailable: {{ $deadlineIso ? 'false' : 'true' }},
        expired: false,
        intervalId: null,
        segments: {
            days: '00',
            hours: '00',
            minutes: '00',
            seconds: '00',
        },
        init() {
            if (this.unavailable) {
                return;
            }

            this.updateCountdown();
            this.intervalId = setInterval(() => this.updateCountdown(), 1000);
        },
        destroy() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
            }
        },
        updateCountdown() {
            const diffSeconds = Math.floor((new Date(this.targetAt).getTime() - Date.now()) / 1000);

            if (diffSeconds <= 0) {
                this.expired = true;
                this.segments = {
                    days: '00',
                    hours: '00',
                    minutes: '00',
                    seconds: '00',
                };

                if (this.intervalId) {
                    clearInterval(this.intervalId);
                }

                return;
            }

            this.expired = false;

            const days = Math.floor(diffSeconds / 86400);
            const hours = Math.floor((diffSeconds % 86400) / 3600);
            const minutes = Math.floor((diffSeconds % 3600) / 60);
            const seconds = diffSeconds % 60;

            this.segments = {
                days: String(days).padStart(2, '0'),
                hours: String(hours).padStart(2, '0'),
                minutes: String(minutes).padStart(2, '0'),
                seconds: String(seconds).padStart(2, '0'),
            };
        },
    }"
    {{ $attributes->class(['']) }}
>


    <template x-if="unavailable">
        <div class="">
            {{ $fallbackLabel }}
        </div>
    </template>

    <template x-if="!unavailable">
        <div class="flex gap-2">

            <div class="" x-text="segments.hours"></div>
            <div class="" x-text="segments.minutes"></div>
            <div class="" x-text="segments.seconds"></div>
        </div>
    </template>

</div>
