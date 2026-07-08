<div>
    <div class="flex justify-between bg-[#1376BD] px-5 py-4 text-white">
        <div>
            <h1 class="text-xl font-medium">
                Sesi Assessment Dimulai
            </h1>
            <p class="text-xs font-ligth">
                Baca soal dengan baik lalu isi sesuai keninginan anda
            </p>
        </div>
        <div class="text-right text-sm hidden sm:block">
            <div class="font-bold">{{ $guru->nama_lengkap }}</div>
            <div>
                {{ $guru->satuan_pendidikan ?: '-' }}
            </div>
        </div>
    </div>
</div>
