@extends('assessment.layouts.app')

@section('content')
    @php
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

    <div>
        <div class="py-4 px-5 bg-[#1376BD] flex justify-between text-white">
            <div class="">
                <h1 class="text-xl font-medium">
                    Dashboard Assessment Peserta
                </h1>
                <p class="text-xs font-ligth">
                    Lihat semua penugasan assessment yang sedang aktif untuk Anda, lanjutkan ujian yang sudah dimulai,
                    atau buka kembali hasil yang sudah dikirim.
                </p>
            </div>
            <div class="text-right text-sm ">
                <div class="font-bold">{{ $guru->nama_lengkap }}</div>

                <div class="">
                     {{ $guru->satuan_pendidikan ?: '-' }}
                </div>
            </div>
        </div>
    </div>
    <section class="grid gap-8 p-6 lg:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)] lg:gap-10 lg:p-14">
        <div class="space-y-8 lg:space-y-12">
            <x-assessment::ui.card>
                <h3 class="mb-1.5 text-xl font-bold text-[#0c3556]">
                    Daftar Penugasan Assessment
                </h3>

                <p class="text-[#6d8092]">
                    Pilih penugasan yang ingin Anda kerjakan. Status dan akses akan menyesuaikan tanggal, jam sesi,
                    serta progres pengerjaan.
                </p>

            </x-assessment::ui.card>

            @forelse ($dashboardCards as $item)
                @php
                    $target = $item['target'];
                    $meta = $item['meta'];
                @endphp

                <x-assessment::ui.assignment-card :target="$target" :meta="$meta" />
            @empty
                <x-assessment::ui.empty-state icon="far fa-folder-open" title="Belum ada assessment yang ditugaskan"
                    description="Saat admin menambahkan assignment baru untuk akun Anda, daftar assessment akan muncul di halaman ini." />
            @endforelse
        </div>

        <aside class="min-w-0">
            <x-assessment::ui.card class="overflow-hidden" padding="p-0" rounded="rounded-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-300 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        <h1 class="text-md font-medium">Data Diri Peserta</h1>
                    </div>

                    <form action="{{ route('assessment.portal.logout') }}" method="POST" class="shrink-0">
                        @csrf

                        <x-assessment::ui.button type="submit" variant="outline" icon="fas fa-sign-out-alt">
                            Logout
                        </x-assessment::ui.button>
                    </form>
                </div>

                <div class="px-6 py-1 grid grid-cols-2">

                    @foreach ($participantDetails as $detail)
                        <x-assessment::ui.detail-row :label="$detail['label']" :value="$detail['value']"
                            valueClass="font-mono  leading-relaxed text-slate-900" :first="$loop->first" />
                    @endforeach
                </div>
            </x-assessment::ui.card>
        </aside>
    </section>
@endsection
