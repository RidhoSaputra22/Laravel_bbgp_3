@extends('assessment.layouts.app')

@section('content')
    <section class="bg-center bg-cover pb-[52px] pt-[72px] text-white lg:pb-[72px] lg:pt-24"
        style="background-image: linear-gradient(135deg, rgba(19, 118, 189, 0.95), rgba(8, 58, 97, 0.95)), url('{{ asset('landing/images/slider-main/bg1.jpg') }}');">
        <div class="container mx-auto px-4">
            <div class="grid items-center gap-8 lg:grid-cols-12 lg:gap-10">
                <div class="lg:col-span-6">
                    <div class="mb-8 lg:mb-0 lg:pr-6">
                        <span
                            class="mb-6 inline-flex items-center gap-2.5 rounded-sm bg-white/[0.14] px-4 py-2 text-sm font-semibold text-white sm:text-base">
                            <i class="fas fa-layer-group"></i>
                            Portal Assessment BBGTK Sulawesi Selatan
                        </span>

                        <h1 class="mb-[18px] text-[34px] font-bold leading-[1.15] text-white lg:text-[44px]">
                            Masuk ke portal assessment untuk mulai ujian.
                        </h1>

                        <p class="text-[17px] leading-[1.8] text-white/[0.88]">
                            Login menggunakan NIK, password akun, dan peran peserta yang sesuai.
                        </p>

                        <ul class="mt-8 space-y-6">
                            <p>
                                <i class="fa fa-check"></i>
                                Baca dan jawab soal secara baik dan benar
                            </p>
                            <p>
                                <i class="fas fa-clipboard-check"></i>
                                Setelah selesai, hasil pengisian dapat dibuka kembali melalui portal yang sama.
                            </p>
                            <p>
                                <i class="fas fa-clock"></i>
                                Waktu pengerjaan soal 3 jam setelah tombol mulai di klik
                            </p>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-5 lg:col-start-8">
                    <x-assessment::ui.card padding="p-0" rounded="rounded-sm"
                        shadow="shadow-sm" class="overflow-hidden">
                        <div class="p-[34px]">
                            <div class="mb-2.5 text-[28px] font-bold text-[#0b3557]">
                                Login Assessment
                            </div>

                            <div class="mb-6 leading-[1.7] text-[#6a7c8f]">
                                Masukkan data akun yang aktif untuk melihat daftar assessment yang ditugaskan kepada Anda.
                            </div>

                            @if (session('assessment_portal_notice'))
                                <x-assessment::ui.alert type="warning" class="mb-5">
                                    {{ session('assessment_portal_notice') }}
                                </x-assessment::ui.alert>
                            @endif

                            @if (session('assessment_portal_success'))
                                <x-assessment::ui.alert type="success" class="mb-5">
                                    {{ session('assessment_portal_success') }}
                                </x-assessment::ui.alert>
                            @endif

                            @if ($errors->any())
                                <x-assessment::ui.alert type="danger" class="mb-5">
                                    <ul class="list-disc space-y-1 pl-5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </x-assessment::ui.alert>
                            @endif

                            <form method="POST" action="{{ route('assessment.portal.login') }}" class="space-y-5">
                                @csrf

                                <x-assessment::form.input id="assessment-nik" name="nik" label="NIK"
                                    :value="old('nik')" placeholder="Masukkan NIK" :required="true" :error="$errors->first('nik')" />

                                <x-assessment::form.input id="assessment-password" type="password" name="password"
                                    label="Password" placeholder="Masukkan password akun" :required="true"
                                    :error="$errors->first('password')" />

                                <x-assessment::form.select id="assessment-role" name="role" label="Peran Peserta"
                                    placeholder="Pilih peran peserta" :required="true" :error="$errors->first('role')">
                                    @foreach ($roleOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('role') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </x-assessment::form.select>

                                <x-assessment::ui.button type="submit" icon="fas fa-sign-in-alt" minHeight=""
                                    rounded="rounded-sm" paddingX="px-5" class="w-full font-bold tracking-[0.2px]">
                                    Masuk ke Portal
                                </x-assessment::ui.button>
                            </form>
                        </div>

                        <div
                            class="flex flex-wrap justify-between gap-x-4 gap-y-2 bg-[#f5f9fc] px-[34px] pb-7 pt-[18px] text-sm text-[#61778a]">
                            <span>Gunakan akun peserta yang sama dengan sistem BBGTK.</span>

                            <a href="{{ route('user.index') }}"
                                class="font-semibold text-[#1376bd] transition hover:text-[#0f619c]">
                                Kembali ke beranda
                            </a>
                        </div>
                    </x-assessment::ui.card>
                </div>
            </div>
        </div>
    </section>

    <section class="relative z-10 -mt-[26px]">
        <div class="container mx-auto px-4">
            <x-assessment::ui.card padding="px-7 py-[26px]" rounded="rounded-md"
                shadow="shadow-[0_18px_48px_rgba(12,53,87,0.14)]">
                <div class="grid items-center gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <h4 class="mb-2 text-xl font-semibold text-[#0b3557]">
                            Belum bisa login?
                        </h4>

                        <p class="leading-relaxed text-[#607489]">
                            Pastikan NIK, password, dan peran peserta sesuai dengan akun yang didaftarkan. Jika belum
                            mendapatkan penugasan assessment, silakan hubungi admin BBGTK terlebih dahulu.
                        </p>
                    </div>

                    <div class="lg:col-span-4 lg:text-right">
                        <x-assessment::ui.button :href="route('login')" variant="outline" paddingX="px-5" paddingY="py-2.5">
                            Login Dashboard
                        </x-assessment::ui.button>
                    </div>
                </div>
            </x-assessment::ui.card>
        </div>
    </section>
@endsection
