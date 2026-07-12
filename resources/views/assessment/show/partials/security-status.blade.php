@php
    $securityEnabled = (bool) data_get($securityPayload ?? [], 'enabled', false);
    $maxSeriousViolations = (int) data_get($securityPayload ?? [], 'maxSeriousViolations', 0);
    $seriousViolationCount = (int) data_get($securityPayload ?? [], 'seriousViolationCount', 0);
    $warningViolationCount = (int) data_get($securityPayload ?? [], 'warningViolationCount', 0);
    $remainingChances = max(0, $maxSeriousViolations - $seriousViolationCount);
@endphp

@if ($securityEnabled || ($stageFlowEnabled ?? false))
    <div x-show="currentSecurityEnabled()" class="rounded-sm border border-amber-200 bg-amber-50/80 px-4 py-3">
        <div class="flex flex-col gap-2">
            <div>
                <div class="text-sm font-semibold text-[#0d3557]">
                    Guard Ujian Aktif
                </div>
                <div class="text-xs leading-relaxed text-slate-600">
                    Shortcut navigasi, clipboard, fokus tab, dan mode fullscreen dipantau selama sesi berlangsung.
                </div>
            </div>

            <div class="space-y-1 text-xs">
                <div class="font-semibold text-slate-800" data-security-serious-indicator>
                    Pelanggaran: {{ $seriousViolationCount }}/{{ $maxSeriousViolations }}
                </div>
                <div class="text-slate-500" data-security-chances-indicator>
                    Sisa kesempatan: {{ $remainingChances }}
                </div>
                <div class="text-slate-500" data-security-warning-indicator>
                    Warning tidak sengaja: {{ $warningViolationCount }}
                </div>
            </div>
        </div>
    </div>
@endif
