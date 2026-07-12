@extends('assessment.layouts.app')

@section('content')
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
            <div class="text-right text-sm hidden sm:block">
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
            @include('assessment.partials.participant-profile-card', ['guru' => $guru])
        </aside>
    </section>
@endsection
