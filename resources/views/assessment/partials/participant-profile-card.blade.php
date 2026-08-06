@php
    $participantAction = is_array($participantAction ?? null) ? $participantAction : [];
    $participantActionMethod = strtoupper((string) ($participantAction['method'] ?? 'POST'));
    $participantActionUrl = $participantAction['url'] ?? route('assessment.portal.logout');
    $participantActionLabel = $participantAction['label'] ?? 'Logout';
    $participantActionIcon = $participantAction['icon'] ?? 'fas fa-sign-out-alt';
    $participantActionVariant = $participantAction['variant'] ?? 'outline';
    $birthPlaceDate = collect([
        $guru->tempat_lahir,
        filled($guru->tgl_lahir) ? \Illuminate\Support\Carbon::parse($guru->tgl_lahir)->format('d/m/Y') : null,
    ])
        ->filter()
        ->implode(', ');

    $contactNumber = collect([
        $guru->no_hp,
        filled($guru->no_wa) && $guru->no_wa !== $guru->no_hp ? $guru->no_wa : null,
    ])
        ->filter()
        ->implode(' / ');

    $participantDetails = [
        [
            'label' => 'Nama Lengkap',
            'value' => $guru->nama_lengkap ?: '-',
        ],
        [
            'label' => 'NIK',
            'value' => $guru->no_ktp ?: '-',
        ],
        [
            'label' => 'Tempat, Tanggal Lahir',
            'value' => $birthPlaceDate ?: '-',
        ],
        [
            'label' => 'Jenis Kelamin',
            'value' => $guru->gender ?: '-',
        ],
        [
            'label' => 'Jabatan',
            'value' => $guru->jabatan ?: '-',
        ],
        [
            'label' => 'Email',
            'value' => $guru->email ?: '-',
        ],
        [
            'label' => 'Nomor Kontak',
            'value' => $contactNumber ?: '-',
        ],
        [
            'label' => 'Satuan Pendidikan',
            'value' => $guru->satuan_pendidikan ?: '-',
        ],
        [
            'label' => 'Alamat Rumah',
            'value' => $guru->alamat_rumah ?: '-',
        ],
    ];
@endphp

<x-assessment::ui.card class="overflow-hidden" padding="p-0" rounded="rounded-sm">
    <div class="flex items-center justify-between gap-3 border-b border-slate-300 px-6 py-5">
        <div class="flex items-center gap-3">
            <i class="fa fa-user" aria-hidden="true"></i>
            <h1 class="text-md font-medium">Data Diri Peserta</h1>
        </div>

        @if ($participantActionMethod === 'GET')
            <x-assessment::ui.button :href="$participantActionUrl" :variant="$participantActionVariant" :icon="$participantActionIcon">
                {{ $participantActionLabel }}
            </x-assessment::ui.button>
        @else
            <form action="{{ $participantActionUrl }}" method="POST" class="shrink-0">
                @csrf

                <x-assessment::ui.button type="submit" :variant="$participantActionVariant" :icon="$participantActionIcon">
                    {{ $participantActionLabel }}
                </x-assessment::ui.button>
            </form>
        @endif
    </div>

    <div class="grid grid-cols-1 px-6 py-1 sm:grid-cols-2">
        @foreach ($participantDetails as $detail)
            <x-assessment::ui.detail-row :label="$detail['label']" :value="$detail['value']"
                valueClass="font-mono leading-relaxed text-slate-900" :first="$loop->first" />
        @endforeach
    </div>
</x-assessment::ui.card>
