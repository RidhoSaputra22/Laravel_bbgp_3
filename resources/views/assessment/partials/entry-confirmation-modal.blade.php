@once
    @push('scripts')
        <script>
            window.assessmentEntryGate = window.assessmentEntryGate || function() {
                return {
                    entryModalOpen: false,
                    entryModal: {
                        action: '',
                        entryAction: 'open',
                        scope: 'assessment',
                        stageIndex: '',
                        title: 'Assessment',
                        stageLabel: '',
                        stageTotal: 0,
                        currentStageLabel: '',
                        instrumentType: '',
                        instrumentLabel: '',
                        questionTotal: 0,
                        durationMinutes: 0,
                        customInstruction: '',
                        instructionItems: [],
                    },
                    openEntryModal(payload = {}) {
                        const instrumentType = this.normalizeInstrumentType(payload.instrumentType);
                        const modal = {
                            action: typeof payload.action === 'string' ? payload.action : '',
                            entryAction: payload.entryAction === 'start' ? 'start' : 'open',
                            scope: payload.scope === 'assignment' ? 'assignment' : 'assessment',
                            stageIndex: payload.stageIndex === undefined || payload.stageIndex === null || payload.stageIndex === ''
                                ? ''
                                : String(payload.stageIndex),
                            title: typeof payload.title === 'string' && payload.title.trim() !== ''
                                ? payload.title.trim()
                                : 'Assessment',
                            stageLabel: typeof payload.stageLabel === 'string' ? payload.stageLabel.trim() : '',
                            stageTotal: this.normalizePositiveNumber(payload.stageTotal),
                            currentStageLabel: typeof payload.currentStageLabel === 'string'
                                ? payload.currentStageLabel.trim()
                                : '',
                            instrumentType: instrumentType,
                            instrumentLabel: typeof payload.instrumentLabel === 'string' && payload.instrumentLabel.trim() !== ''
                                ? payload.instrumentLabel.trim()
                                : this.instrumentLabelFor(instrumentType),
                            questionTotal: this.normalizePositiveNumber(payload.questionTotal),
                            durationMinutes: this.normalizePositiveNumber(payload.durationMinutes),
                            customInstruction: typeof payload.customInstruction === 'string'
                                ? payload.customInstruction.trim()
                                : '',
                            instructionItems: [],
                        };

                        modal.instructionItems = this.buildInstructionItems(modal);
                        this.entryModal = modal;
                        this.entryModalOpen = true;
                    },
                    closeEntryModal() {
                        this.entryModalOpen = false;
                    },
                    submitEntryModal() {
                        if (! this.entryModal.action) {
                            return;
                        }

                        this.$refs.entryConfirmForm.submit();
                    },
                    normalizeInstrumentType(value) {
                        return typeof value === 'string' ? value.trim().toLowerCase() : '';
                    },
                    normalizePositiveNumber(value) {
                        const parsedValue = Number(value);

                        if (! Number.isFinite(parsedValue) || parsedValue <= 0) {
                            return 0;
                        }

                        return Math.round(parsedValue);
                    },
                    instrumentLabelFor(type) {
                        switch (this.normalizeInstrumentType(type)) {
                            case 'portofolio':
                                return 'Portofolio';
                            case 'pilihan_ganda_kompleks':
                                return 'Pilihan Ganda Kompleks';
                            case 'skala_likert':
                                return 'Skala Likert';
                            case 'studi_kasus':
                                return 'Studi Kasus';
                            case 'monitoring_observasi_eviden':
                                return 'Monitoring / Observasi / Eviden';
                            default:
                                return '';
                        }
                    },
                    questionUnitFor(type) {
                        switch (this.normalizeInstrumentType(type)) {
                            case 'portofolio':
                                return 'isian portofolio';
                            case 'skala_likert':
                                return 'pernyataan';
                            case 'monitoring_observasi_eviden':
                                return 'butir observasi/eviden';
                            default:
                                return 'soal';
                        }
                    },
                    selectionInstructionFor(type) {
                        switch (this.normalizeInstrumentType(type)) {
                            case 'pilihan_ganda_kompleks':
                                return 'Setiap soal memiliki empat pilihan jawaban. Pilih satu jawaban yang paling tepat.';
                            case 'skala_likert':
                                return 'Pilih satu skala jawaban yang paling menggambarkan kondisi atau pengalaman Anda pada setiap pernyataan.';
                            case 'portofolio':
                                return 'Lengkapi setiap isian portofolio dan siapkan dokumen pendukung yang diminta pada tiap bagian.';
                            case 'studi_kasus':
                                return 'Baca setiap studi kasus dengan cermat, lalu tentukan jawaban atau analisis yang paling tepat sesuai instruksi.';
                            case 'monitoring_observasi_eviden':
                                return 'Lengkapi setiap butir observasi atau unggah eviden sesuai instruksi yang tersedia pada form.';
                            default:
                                return 'Jawab setiap butir assessment sesuai instruksi yang tersedia pada halaman pengerjaan.';
                        }
                    },
                    timeManagementInstruction(questionTotal, durationMinutes) {
                        if (questionTotal > 0 && durationMinutes > 0) {
                            const averageMinutes = durationMinutes / questionTotal;
                            const averageLabel = Number.isInteger(averageMinutes)
                                ? String(averageMinutes)
                                : averageMinutes.toFixed(1).replace('.', ',');

                            return 'Kelola waktu dengan baik. Rata-rata waktu yang tersedia sekitar ' + averageLabel + ' menit untuk setiap butir.';
                        }

                        return 'Kerjakan assessment secara bertahap dan pastikan setiap jawaban atau eviden sudah terisi dengan lengkap sebelum berpindah.';
                    },
                    modalDescription() {
                        if (this.entryModal.scope === 'assignment') {
                            return 'Baca petunjuk berikut terlebih dahulu sebelum halaman penugasan dibuka.';
                        }

                        return 'Baca petunjuk berikut terlebih dahulu sebelum assessment dimulai atau dibuka.';
                    },
                    durationBadgeText() {
                        if (this.entryModal.durationMinutes > 0) {
                            return 'Durasi ' + this.formatDuration(this.entryModal.durationMinutes);
                        }

                        if (this.entryModal.scope === 'assignment') {
                            return 'Cek durasi per tahap';
                        }

                        return 'Tanpa timer khusus';
                    },
                    buildAssignmentInstructionItems(modal) {
                        const items = [];

                        if (modal.stageTotal > 0 && modal.questionTotal > 0) {
                            items.push({
                                text: 'Penugasan terdiri atas ' + modal.stageTotal + ' tahap assessment dengan total ' + modal.questionTotal + ' butir.',
                            });
                        } else if (modal.stageTotal > 0) {
                            items.push({
                                text: 'Penugasan terdiri atas ' + modal.stageTotal + ' tahap assessment yang dikerjakan secara bertahap.',
                            });
                        } else if (modal.questionTotal > 0) {
                            items.push({
                                text: 'Penugasan memuat ' + modal.questionTotal + ' butir assessment yang siap dikerjakan.',
                            });
                        } else {
                            items.push({
                                text: 'Buka penugasan dan ikuti tahap assessment yang tersedia sampai selesai.',
                            });
                        }

                        items.push({
                            text: 'Buka tahap yang berstatus siap dikerjakan atau lanjutkan tahap yang sedang berjalan dari halaman penugasan.',
                        });

                        if (modal.durationMinutes > 0) {
                            items.push({
                                text: 'Durasi total penugasan yang tercatat adalah ' + this.formatDuration(modal.durationMinutes) + '.',
                            });
                        } else {
                            items.push({
                                text: 'Setiap tahap dapat memiliki aturan durasi yang berbeda. Periksa keterangan waktu pada kartu tahap sebelum memulai.',
                            });
                        }

                        items.push({
                            text: modal.durationMinutes > 0
                                ? this.timeManagementInstruction(modal.questionTotal, modal.durationMinutes)
                                : 'Selesaikan satu tahap terlebih dahulu sebelum berpindah ke tahap berikutnya yang sudah tersedia.',
                        });

                        items.push({
                            text: 'Persiapan teknis yang perlu diperhatikan:',
                            children: [
                                'Pastikan memiliki koneksi internet yang stabil selama assessment berlangsung.',
                                'Gunakan komputer/laptop yang tetap aktif selama proses assessment berjalan.',
                                'Jangan menutup browser sebelum semua jawaban atau unggahan selesai tersimpan.',
                            ],
                        });

                        items.push({
                            text: 'Bacalah instruksi khusus pada tiap tahap sebelum menjawab atau mengunggah dokumen pendukung.',
                        });

                        items.push({
                            text: 'Jawaban yang dipilih otomatis tersimpan oleh sistem.',
                        });

                        items.push({
                            text: 'Jika satu tahap sudah disimpan permanen, lanjutkan ke tahap berikutnya sesuai urutan yang tersedia.',
                        });

                        items.push({
                            text: 'Disarankan menggunakan laptop/PC agar tampilan assessment lebih optimal dan mudah dibaca.',
                        });

                        return items;
                    },
                    buildAssessmentInstructionItems(modal) {
                        const items = [];

                        if (modal.questionTotal > 0) {
                            items.push({
                                text: 'Asesmen terdiri atas ' + modal.questionTotal + ' ' + this.questionUnitFor(modal.instrumentType) + '.',
                            });
                        } else {
                            items.push({
                                text: 'Ikuti seluruh butir assessment yang tersedia pada tahap ini sampai selesai.',
                            });
                        }

                        items.push({
                            text: this.selectionInstructionFor(modal.instrumentType),
                        });

                        if (modal.durationMinutes > 0) {
                            items.push({
                                text: 'Waktu yang disediakan untuk mengerjakan seluruh assessment adalah ' + this.formatDuration(modal.durationMinutes) + '.',
                            });
                        } else {
                            items.push({
                                text: 'Tahap ini tidak memiliki timer khusus. Tetap selesaikan assessment dalam periode penugasan yang tersedia.',
                            });
                        }

                        items.push({
                            text: this.timeManagementInstruction(modal.questionTotal, modal.durationMinutes),
                        });

                        items.push({
                            text: 'Persiapan teknis yang perlu diperhatikan:',
                            children: modal.durationMinutes > 0
                                ? [
                                    'Pastikan memiliki koneksi internet yang stabil selama assessment berlangsung.',
                                    'Gunakan komputer/laptop yang tetap aktif selama proses assessment berjalan.',
                                    'Waktu pengerjaan tetap berjalan meskipun koneksi internet terputus atau perangkat mengalami kendala.',
                                ]
                                : [
                                    'Pastikan memiliki koneksi internet yang stabil selama assessment berlangsung.',
                                    'Gunakan komputer/laptop yang tetap aktif selama proses assessment berjalan.',
                                    'Jangan menutup browser sebelum semua jawaban atau unggahan selesai tersimpan.',
                                ],
                        });

                        items.push({
                            text: 'Bacalah setiap butir secara cermat sebelum menentukan jawaban atau mengunggah dokumen pendukung.',
                        });

                        items.push({
                            text: 'Jawaban yang dipilih otomatis tersimpan oleh sistem.',
                        });

                        items.push({
                            text: 'Jika masih memiliki waktu, lakukan peninjauan kembali terhadap jawaban sebelum mengakhiri assessment.',
                        });

                        items.push({
                            text: 'Disarankan menggunakan laptop/PC agar tampilan assessment lebih optimal dan mudah dibaca.',
                        });

                        return items;
                    },
                    buildInstructionItems(modal) {
                        return modal.scope === 'assignment'
                            ? this.buildAssignmentInstructionItems(modal)
                            : this.buildAssessmentInstructionItems(modal);
                    },
                    formatDuration(totalMinutes) {
                        if (! totalMinutes || totalMinutes <= 0) {
                            return 'tanpa batas waktu';
                        }

                        const hours = Math.floor(totalMinutes / 60);
                        const minutes = totalMinutes % 60;
                        const parts = [];

                        if (hours > 0) {
                            parts.push(hours + ' jam');
                        }

                        if (minutes > 0) {
                            parts.push(minutes + ' menit');
                        }

                        return parts.join(' ') || '0 menit';
                    },
                };
            };
        </script>
    @endpush
@endonce

<div
    x-show="entryModalOpen"
    x-cloak
    style="display: none;"
    class="fixed  inset-0 z-50 flex items-center justify-center "
    @keydown.escape.window="closeEntryModal()"
>
    <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" x-transition.opacity @click="closeEntryModal()"></div>

    <div class="relative h-full w-full max-w-4xl" x-transition>
        <x-assessment::ui.card class=" rounded-[28px] p-0 shadow-[0_28px_90px_rgba(15,23,42,0.35)] ">
            <form x-ref="entryConfirmForm" method="POST" x-bind:action="entryModal.action" class="hidden">
                @csrf
                <input type="hidden" name="entry_action" x-bind:value="entryModal.entryAction">
                <input type="hidden" name="entry_scope" x-bind:value="entryModal.scope">
                <input type="hidden" name="stage_index" x-bind:value="entryModal.stageIndex">
            </form>

            <div class="border-b border-slate-200 px-3 py-2">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">
                            Konfirmasi Pengerjaan
                        </div>
                        <h3 class="mt-2 text-xl font-bold text-slate-900" x-text="entryModal.title"></h3>
                        <p class="mt-1 text-sm leading-relaxed text-slate-500">
                            <span x-text="modalDescription()"></span>
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                            <template x-if="entryModal.stageLabel">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-slate-700" x-text="entryModal.stageLabel"></span>
                            </template>

                            <template x-if="entryModal.scope === 'assignment' && entryModal.stageTotal > 0">
                                <span class="inline-flex items-center rounded-full bg-[#eaf5fb] px-3 py-1 text-[#0d5f98]">
                                    <span x-text="entryModal.stageTotal"></span>&nbsp;tahap
                                </span>
                            </template>

                            <template x-if="entryModal.scope === 'assignment' && entryModal.currentStageLabel">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-emerald-700" x-text="entryModal.currentStageLabel"></span>
                            </template>

                            <template x-if="entryModal.instrumentLabel">
                                <span class="inline-flex items-center rounded-full bg-[#eaf5fb] px-3 py-1 text-[#0d5f98]" x-text="entryModal.instrumentLabel"></span>
                            </template>

                            <template x-if="entryModal.questionTotal > 0">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">
                                    <span x-text="entryModal.questionTotal"></span>&nbsp;butir
                                </span>
                            </template>

                            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-amber-700" x-text="durationBadgeText()"></span>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex h-10 w-10 items-center justify-center text-slate-500 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#0d5f98]/30 focus:ring-offset-2"
                        @click="closeEntryModal()"
                        aria-label="Tutup modal"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="max-h-[72vh]  px-3 py-2 mt-3">
                <div class="flex items-end gap-4 border-b border-[#dce8f1] pb-4">

                    <div>
                        <div class="text-2xl font-black uppercase tracking-[0.18em] ">
                            Petunjuk Umum
                        </div>
                    </div>
                </div>

                <ol class="mt-6 list-decimal space-y-4 pl-6 text-[12pt] leading-7 text-slate-700">
                    <template x-for="(item, index) in entryModal.instructionItems" :key="index">
                        <li>
                            <span class="font-medium" x-text="item.text"></span>

                            <template x-if="item.children && item.children.length">
                                <ul class="mt-2 list-disc space-y-1 pl-6 text-slate-600">
                                    <template x-for="(child, childIndex) in item.children" :key="childIndex">
                                        <li x-text="child"></li>
                                    </template>
                                </ul>
                            </template>
                        </li>
                    </template>
                </ol>

                <template x-if="entryModal.customInstruction">
                    <div class="mt-6 rounded-sm border border-sky-200 bg-sky-50 px-5 py-4 text-sm leading-7 text-sky-900">
                        <div class="font-semibold" x-text="entryModal.scope === 'assignment' ? 'Informasi penugasan' : 'Catatan khusus assessment'"></div>
                        <div class="mt-1 whitespace-pre-line" x-text="entryModal.customInstruction"></div>
                    </div>
                </template>
            </div>

            <div class="border-t border-slate-200 px-6 py-4">
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <x-assessment::ui.button type="button" variant="outline" @click="closeEntryModal()">
                        Batal
                    </x-assessment::ui.button>

                    <x-assessment::ui.button type="button" icon="fas fa-play-circle" @click="submitEntryModal()">
                        Lanjutkan
                    </x-assessment::ui.button>
                </div>
            </div>
        </x-assessment::ui.card>
    </div>
</div>
