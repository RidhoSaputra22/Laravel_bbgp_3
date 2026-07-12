@php
    $securityEnabled = (bool) data_get($securityPayload ?? [], 'enabled', false);
    $maxSeriousViolations = (int) data_get($securityPayload ?? [], 'maxSeriousViolations', 0);
    $seriousViolationCount = (int) data_get($securityPayload ?? [], 'seriousViolationCount', 0);
    $warningViolationCount = (int) data_get($securityPayload ?? [], 'warningViolationCount', 0);
    $remainingChances = max(0, $maxSeriousViolations - $seriousViolationCount);
@endphp

@if ($securityEnabled || ($stageFlowEnabled ?? false))
    <div x-show="currentSecurityEnabled()" class="fixed inset-0 z-[90] hidden items-center justify-center bg-slate-950/75 p-4" data-security-overlay>
        <div class="w-full max-w-xl rounded-sm bg-white p-6 shadow-2xl sm:p-7">
            <div class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">
                Guard Ujian
            </div>

            <h2 class="mt-4 text-xl font-bold text-[#0d3557]">
                Aktivitas Anda sedang dibatasi
            </h2>

            <p class="mt-3 text-sm leading-relaxed text-slate-600" data-security-warning-message>
                Sistem mendeteksi aktivitas yang tidak sesuai aturan ujian.
            </p>

            <div class="mt-4 text-sm font-semibold text-amber-700" data-security-warning-type>
                Tipe: Tidak Sengaja - warning saja
            </div>

            <div class="mt-5 grid gap-3 rounded-sm border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-700 sm:grid-cols-3">
                <div data-security-overlay-violations>
                    Pelanggaran: {{ $seriousViolationCount }}/{{ $maxSeriousViolations }}
                </div>
                <div data-security-overlay-chances>
                    Sisa kesempatan: {{ $remainingChances }}
                </div>
                <div data-security-overlay-warning-count>
                    Warning tidak sengaja: {{ $warningViolationCount }}
                </div>
            </div>

            <div class="mt-5 rounded-sm bg-slate-100 px-4 py-3 text-sm text-slate-700" data-security-warning-timer>
                Mohon tunggu sebentar...
            </div>

            <div class="mt-5 flex justify-end">
                <button type="button"
                    class="inline-flex items-center justify-center rounded-sm bg-[#1376BD] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0d5f98] disabled:cursor-not-allowed disabled:bg-slate-400"
                    data-security-warning-button disabled>
                    Tunggu...
                </button>
            </div>
        </div>
    </div>
@endif
