<x-assessment::ui.card>
    <div class="mb-2 text-sm uppercase text-slate-500">
        {{ $assessment['kode_assessment'] }}
    </div>

    <h3 class="mb-1.5 text-xl font-bold text-[#0d3557]">
        {{ $assessment['judul'] }}
    </h3>

    <p class="mb-2.5 leading-[1.8] text-[#6a7e90]">
        {{ $assessment['deskripsi'] ?: 'Silakan kerjakan seluruh form pada assessment ini.' }}
    </p>

    @if (!empty($assessment['petunjuk']))
        <div class="rounded-sm border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-relaxed text-sky-800">
            <strong>Petunjuk:</strong> {{ $assessment['petunjuk'] }}
        </div>
    @endif
</x-assessment::ui.card>
