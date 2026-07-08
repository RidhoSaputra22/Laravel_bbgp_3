<x-assessment::ui.modal show="showFinishModal"
    close-action="if (!isSubmitting) { showFinishModal = false }" title="Konfirmasi Selesai Assessment"
    description="Setelah dikirim, semua jawaban akan diproses dan halaman akan beralih ke hasil assessment.">
    <div class="space-y-4 **:text-sm ">
        <div class=" rounded-sm border border-[#dce9f4] bg-[#f8fbfe] p-4">
            <div class="flex flex-col items-center justify-center text-center sm:text-start sm:flex-row sm:items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-sm bg-[#1376bd] text-white">
                    <i class="fas fa-paper-plane"></i>
                </div>

                <div>
                    <h4 class="text-base font-bold text-slate-900">
                        Pastikan semua jawaban sudah final
                    </h4>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                        Sistem akan mengirim seluruh jawaban dari {{ $assessmentCount }} assessment sekaligus. Modal ini
                        hanya ditampilkan setelah isian wajib yang perlu dijawab, termasuk batas 25-100 kata pada area
                        teks, sudah lolos pengecekan.
                    </p>
                </div>
            </div>
        </div>

        <p class="text-sm leading-relaxed text-slate-500">
            Tekan tombol kirim jika Anda siap menyelesaikan seluruh assessment pada penugasan ini.
        </p>



        <div x-show="flaggedUnansweredQuestionCount() > 0"
            class="rounded-sm border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Masih ada <span class="font-semibold" x-text="flaggedUnansweredQuestionCount()"></span> soal yang
            ditandai tetapi belum dijawab. Soal tersebut harus diisi sebelum jawaban dapat dikirim.
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-assessment::ui.button type="button" variant="outline"
                x-bind:disabled="isBusy()" @click="showFinishModal = false">
                Kembali Cek Jawaban
            </x-assessment::ui.button>

            <x-assessment::ui.button type="button"  x-bind:disabled="isBusy()"
                @click="submitConfirmedForm()">
                <span x-show="!isSubmitting" class="inline-flex items-center">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Ya, Kirim Jawaban
                </span>

                <span x-show="isSubmitting" class="inline-flex items-center">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Mengirim jawaban...
                </span>
            </x-assessment::ui.button>
        </div>
    </x-slot>
</x-assessment::ui.modal>
