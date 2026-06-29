<?php

namespace Database\Seeders;

use App\Enum\AssessmentInstrumentType;
use App\Enum\KompetensiGuru;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentStudiKasusKepalaSekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assessments = [
                [
                    'kode_assessment' => 'ASM-KS-STUDI-KASUS-001',
                    'judul' => 'Studi Kasus Pemetaan Kompetensi Kepala Sekolah',
                    'deskripsi' => 'Assessment berbasis studi kasus untuk memetakan kompetensi kepribadian, sosial, dan profesional kepala sekolah.',
                    'petunjuk' => 'Bacalah setiap kasus dengan saksama. Jawablah seluruh pertanyaan secara analitis, sistematis, dan sesuai dengan konteks tugas kepala sekolah.',
                    'instrument_type' => AssessmentInstrumentType::STUDI_KASUS->value,
                    'status' => 'publish',
                    'is_active' => true,
                    'forms' => [
                        [
                            'judul_form' => 'Kasus 1 – Integritas Dan Kepemimpinan',
                            'kode_form' => 'FORM-KS-SK-01',
                            'deskripsi' => "Fokus: INTEGRITAS DAN KEPEMIMPINAN.\n\nKasus:\nSeorang kepala sekolah menerima tekanan dari oknum tertentu untuk memanipulasi laporan penggunaan dana BOS agar terlihat sesuai dengan harapan pihak luar. Jika tidak mengikuti permintaan tersebut, kepala sekolah diancam akan dipersulit dalam urusan administrasi di tingkat atas.",
                            'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                            'indikator_kode' => 'CASE-1',
                            'indikator_label' => 'INTEGRITAS DAN KEPEMIMPINAN',
                            'is_scoreable' => true,
                            'urutan' => 1,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => '1. Analisis permasalahan yang terjadi',
                                    'deskripsi' => 'Tanggapi tugas C4 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_1_tugas_1',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 1.',
                                    'bantuan' => 'Indikator jawaban: Menunjukkan pemahaman tentang integritas dan etika; Mengidentifikasi risiko hukum dan moral; Menawarkan solusi berbasis transparansi dan regulasi. Rubrik penilaian: Level 1: Jawaban deskriptif tanpa analisis; Level 2: Analisis sederhana, solusi belum jelas; Level 3: Analisis cukup, solusi normatif; Level 4: Solusi jelas, berbasis prinsip integritas; Level 5: Solusi strategis, sistemik, dan berani',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => '2. Evaluasi dampak jika kepala sekolah mengikuti atau menolak tekanan tersebut',
                                    'deskripsi' => 'Tanggapi tugas C5 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_1_tugas_2',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 2.',
                                    'bantuan' => 'Indikator jawaban: Menunjukkan pemahaman tentang integritas dan etika; Mengidentifikasi risiko hukum dan moral; Menawarkan solusi berbasis transparansi dan regulasi. Rubrik penilaian: Level 1: Jawaban deskriptif tanpa analisis; Level 2: Analisis sederhana, solusi belum jelas; Level 3: Analisis cukup, solusi normatif; Level 4: Solusi jelas, berbasis prinsip integritas; Level 5: Solusi strategis, sistemik, dan berani',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => '3. Rancang strategi penyelesaian yang tepat dan berintegritas',
                                    'deskripsi' => 'Tanggapi tugas C6 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_1_tugas_3',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 3.',
                                    'bantuan' => 'Indikator jawaban: Menunjukkan pemahaman tentang integritas dan etika; Mengidentifikasi risiko hukum dan moral; Menawarkan solusi berbasis transparansi dan regulasi. Rubrik penilaian: Level 1: Jawaban deskriptif tanpa analisis; Level 2: Analisis sederhana, solusi belum jelas; Level 3: Analisis cukup, solusi normatif; Level 4: Solusi jelas, berbasis prinsip integritas; Level 5: Solusi strategis, sistemik, dan berani',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => 'Kasus 2 – Kolaborasi Dan Hubungan Sosial',
                            'kode_form' => 'FORM-KS-SK-02',
                            'deskripsi' => "Fokus: KOLABORASI DAN HUBUNGAN SOSIAL.\n\nKasus:\nPartisipasi orang tua dan masyarakat dalam kegiatan sekolah sangat rendah. Program sekolah sering tidak mendapat dukungan, bahkan beberapa kebijakan ditolak karena kurangnya komunikasi.",
                            'kompetensi' => KompetensiGuru::SOSIAL->value,
                            'indikator_kode' => 'CASE-2',
                            'indikator_label' => 'KOLABORASI DAN HUBUNGAN SOSIAL',
                            'is_scoreable' => true,
                            'urutan' => 2,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => '1. Analisis penyebab rendahnya partisipasi masyarakat',
                                    'deskripsi' => 'Tanggapi tugas C4 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_2_tugas_1',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 1.',
                                    'bantuan' => 'Indikator jawaban: Identifikasi faktor komunikasi, kepercayaan, dan keterlibatan; Evaluasi pendekatan yang kurang partisipatif; Strategi kemitraan berbasis stakeholder engagement. Rubrik penilaian: Level 1: Jawaban umum tanpa analisis; Level 2: Analisis terbatas; Level 3: Solusi ada namun belum sistematis; Level 4: Solusi kolaboratif dan realistis; Level 5: Strategi inovatif dan berkelanjutan',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => '2. Evaluasi kelemahan pendekatan yang telah dilakukan sekolah',
                                    'deskripsi' => 'Tanggapi tugas C5 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_2_tugas_2',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 2.',
                                    'bantuan' => 'Indikator jawaban: Identifikasi faktor komunikasi, kepercayaan, dan keterlibatan; Evaluasi pendekatan yang kurang partisipatif; Strategi kemitraan berbasis stakeholder engagement. Rubrik penilaian: Level 1: Jawaban umum tanpa analisis; Level 2: Analisis terbatas; Level 3: Solusi ada namun belum sistematis; Level 4: Solusi kolaboratif dan realistis; Level 5: Strategi inovatif dan berkelanjutan',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => '3. Rancang strategi kolaborasi yang efektif dan berkelanjutan',
                                    'deskripsi' => 'Tanggapi tugas C6 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_2_tugas_3',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 3.',
                                    'bantuan' => 'Indikator jawaban: Identifikasi faktor komunikasi, kepercayaan, dan keterlibatan; Evaluasi pendekatan yang kurang partisipatif; Strategi kemitraan berbasis stakeholder engagement. Rubrik penilaian: Level 1: Jawaban umum tanpa analisis; Level 2: Analisis terbatas; Level 3: Solusi ada namun belum sistematis; Level 4: Solusi kolaboratif dan realistis; Level 5: Strategi inovatif dan berkelanjutan',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => 'Kasus 3 – Kepemimpinan Pembelajaran',
                            'kode_form' => 'FORM-KS-SK-03',
                            'deskripsi' => "Fokus: KEPEMIMPINAN PEMBELAJARAN.\n\nKasus:\nHasil belajar siswa di sekolah menunjukkan penurunan selama dua tahun terakhir. Observasi menunjukkan bahwa pembelajaran masih berpusat pada guru dan kurang inovatif.",
                            'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                            'indikator_kode' => 'CASE-3',
                            'indikator_label' => 'KEPEMIMPINAN PEMBELAJARAN',
                            'is_scoreable' => true,
                            'urutan' => 3,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => '1. Analisis penyebab penurunan hasil belajar',
                                    'deskripsi' => 'Tanggapi tugas C4 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_3_tugas_1',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 1.',
                                    'bantuan' => 'Indikator jawaban: Mengidentifikasi faktor pedagogik dan kepemimpinan; Evaluasi metode pembelajaran; Strategi seperti PLC, supervisi reflektif, dan inovasi pembelajaran. Rubrik penilaian: Level 1: Identifikasi masalah terbatas; Level 2: Analisis belum mendalam; Level 3: Solusi umum; Level 4: Solusi berbasis strategi pembelajaran; Level 5: Solusi inovatif dan sistemik',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => '2. Evaluasi praktik pembelajaran yang berlangsung',
                                    'deskripsi' => 'Tanggapi tugas C5 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_3_tugas_2',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 2.',
                                    'bantuan' => 'Indikator jawaban: Mengidentifikasi faktor pedagogik dan kepemimpinan; Evaluasi metode pembelajaran; Strategi seperti PLC, supervisi reflektif, dan inovasi pembelajaran. Rubrik penilaian: Level 1: Identifikasi masalah terbatas; Level 2: Analisis belum mendalam; Level 3: Solusi umum; Level 4: Solusi berbasis strategi pembelajaran; Level 5: Solusi inovatif dan sistemik',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => '3. Rancang intervensi peningkatan kualitas pembelajaran',
                                    'deskripsi' => 'Tanggapi tugas C6 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_3_tugas_3',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 3.',
                                    'bantuan' => 'Indikator jawaban: Mengidentifikasi faktor pedagogik dan kepemimpinan; Evaluasi metode pembelajaran; Strategi seperti PLC, supervisi reflektif, dan inovasi pembelajaran. Rubrik penilaian: Level 1: Identifikasi masalah terbatas; Level 2: Analisis belum mendalam; Level 3: Solusi umum; Level 4: Solusi berbasis strategi pembelajaran; Level 5: Solusi inovatif dan sistemik',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => 'Kasus 4 – Manajerial Dan Akuntabilitas',
                            'kode_form' => 'FORM-KS-SK-04',
                            'deskripsi' => "Fokus: MANAJERIAL DAN AKUNTABILITAS.\n\nKasus:\nTerdapat keluhan dari guru dan komite sekolah terkait kurangnya transparansi dalam pengelolaan dana sekolah. Laporan keuangan tidak dipublikasikan secara terbuka.",
                            'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                            'indikator_kode' => 'CASE-4',
                            'indikator_label' => 'MANAJERIAL DAN AKUNTABILITAS',
                            'is_scoreable' => true,
                            'urutan' => 4,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => '1. Analisis permasalahan dalam pengelolaan keuangan',
                                    'deskripsi' => 'Tanggapi tugas C4 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_4_tugas_1',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 1.',
                                    'bantuan' => 'Indikator jawaban: Identifikasi masalah tata kelola; Dampak pada kepercayaan publik; Sistem transparansi dan pelaporan berbasis partisipasi. Rubrik penilaian: Level 1: Jawaban deskriptif; Level 2: Analisis terbatas; Level 3: Solusi administratif; Level 4: Solusi sistematis; Level 5: Solusi berbasis sistem dan budaya organisasi',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => '2. Evaluasi dampak terhadap kepercayaan stakeholder',
                                    'deskripsi' => 'Tanggapi tugas C5 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_4_tugas_2',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 2.',
                                    'bantuan' => 'Indikator jawaban: Identifikasi masalah tata kelola; Dampak pada kepercayaan publik; Sistem transparansi dan pelaporan berbasis partisipasi. Rubrik penilaian: Level 1: Jawaban deskriptif; Level 2: Analisis terbatas; Level 3: Solusi administratif; Level 4: Solusi sistematis; Level 5: Solusi berbasis sistem dan budaya organisasi',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => '3. Rancang sistem pengelolaan keuangan yang transparan dan akuntabel',
                                    'deskripsi' => 'Tanggapi tugas C6 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_4_tugas_3',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 3.',
                                    'bantuan' => 'Indikator jawaban: Identifikasi masalah tata kelola; Dampak pada kepercayaan publik; Sistem transparansi dan pelaporan berbasis partisipasi. Rubrik penilaian: Level 1: Jawaban deskriptif; Level 2: Analisis terbatas; Level 3: Solusi administratif; Level 4: Solusi sistematis; Level 5: Solusi berbasis sistem dan budaya organisasi',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => 'Kasus 5 – Kewirausahaan Dan Inovasi',
                            'kode_form' => 'FORM-KS-SK-05',
                            'deskripsi' => "Fokus: KEWIRAUSAHAAN DAN INOVASI.\n\nKasus:\nSekolah tidak memiliki program unggulan dan cenderung stagnan. Tidak ada inovasi yang berdampak signifikan terhadap kualitas pembelajaran maupun citra sekolah.",
                            'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                            'indikator_kode' => 'CASE-5',
                            'indikator_label' => 'KEWIRAUSAHAAN DAN INOVASI',
                            'is_scoreable' => true,
                            'urutan' => 5,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => '1. Analisis penyebab stagnasi inovasi di sekolah',
                                    'deskripsi' => 'Tanggapi tugas C4 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_5_tugas_1',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 1.',
                                    'bantuan' => 'Indikator jawaban: Identifikasi budaya organisasi; Evaluasi kepemimpinan inovatif; Program berbasis potensi lokal dan kebutuhan siswa. Rubrik penilaian: Level 1: Jawaban umum; Level 2: Analisis terbatas; Level 3: Solusi normatif; Level 4: Solusi inovatif; Level 5: Solusi strategis dan berkelanjutan',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => '2. Evaluasi budaya organisasi sekolah terkait inovasi',
                                    'deskripsi' => 'Tanggapi tugas C5 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_5_tugas_2',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 2.',
                                    'bantuan' => 'Indikator jawaban: Identifikasi budaya organisasi; Evaluasi kepemimpinan inovatif; Program berbasis potensi lokal dan kebutuhan siswa. Rubrik penilaian: Level 1: Jawaban umum; Level 2: Analisis terbatas; Level 3: Solusi normatif; Level 4: Solusi inovatif; Level 5: Solusi strategis dan berkelanjutan',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => '3. Rancang program inovasi berbasis kebutuhan dan potensi sekolah',
                                    'deskripsi' => 'Tanggapi tugas C6 berdasarkan kasus yang diberikan.',
                                    'nama_field' => 'kasus_5_tugas_3',
                                    'tipe_field' => 'textarea',
                                    'placeholder' => 'Tuliskan jawaban Anda untuk tugas 3.',
                                    'bantuan' => 'Indikator jawaban: Identifikasi budaya organisasi; Evaluasi kepemimpinan inovatif; Program berbasis potensi lokal dan kebutuhan siswa. Rubrik penilaian: Level 1: Jawaban umum; Level 2: Analisis terbatas; Level 3: Solusi normatif; Level 4: Solusi inovatif; Level 5: Solusi strategis dan berkelanjutan',
                                    'opsi_field' => null,
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                        'min_length' => 50,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ];

        foreach ($assessments as $item) {
            $forms = $item['forms'];
            unset($item['forms']);

            $assessment = Assessment::updateOrCreate(
                ['kode_assessment' => $item['kode_assessment']],
                [
                    'judul' => $item['judul'],
                    'slug' => Str::slug($item['judul']),
                    'deskripsi' => $item['deskripsi'],
                    'petunjuk' => $item['petunjuk'],
                    'instrument_type' => $item['instrument_type'] ?? null,
                    'scoring_config' => $this->assessmentScoringConfig(),
                    'status' => $item['status'],
                    'is_active' => $item['is_active'],
                ]
            );

            $assessment->forms()->delete();

            foreach ($forms as $formData) {
                $fields = $formData['fields'];
                unset($formData['fields']);
                $formData['scoring_config'] = $this->formScoringConfig($formData);

                $form = $assessment->forms()->create($formData);

                foreach (array_values($fields) as $fieldIndex => $fieldData) {
                    $fieldData['scoring_config'] = $this->fieldScoringConfig($formData, $fieldIndex);
                    $form->fields()->create($fieldData);
                }
            }
        }
    }

    private function assessmentScoringConfig(): array
    {
        return [
            'profile' => AssessmentInstrumentType::STUDI_KASUS->value,
            'weight' => AssessmentInstrumentType::STUDI_KASUS->weight(),
            'verification_gap_threshold' => 1.5,
            'advanced_rules' => [
                'overall_formula' => 'Level kasus = jumlah(level kriteria x bobot) / 100.',
                'case_structure' => [
                    'K1' => 33,
                    'K2' => 33,
                    'K3' => 34,
                ],
                'competency_source_map' => [
                    [
                        'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                        'source' => 'Kasus 1',
                    ],
                    [
                        'kompetensi' => KompetensiGuru::SOSIAL->value,
                        'source' => 'Kasus 2',
                    ],
                    [
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'source' => 'Kasus 3-5',
                    ],
                ],
            ],
        ];
    }

    private function formScoringConfig(array $formData): array
    {
        return [
            'profile' => 'study_case_kepala_sekolah',
            'weight' => 100,
            'advanced_rules' => [
                'case_formula' => 'K1 33% + K2 33% + K3 34%',
                'focus' => $formData['indikator_label'] ?? null,
                'rubric_summary' => 'Jawaban dinilai pada skala 1-5 berdasarkan kualitas analisis, evaluasi, dan rancangan strategi yang kontekstual.',
            ],
        ];
    }

    private function fieldScoringConfig(array $formData, int $fieldIndex): array
    {
        $weights = [33, 33, 34];
        $rubricCodes = ['K1', 'K2', 'K3'];
        $references = $this->studyCaseReferenceLibrary();
        $caseKey = $formData['indikator_kode'] ?? '';
        $reference = $references[$caseKey][$fieldIndex] ?? [
            'reference_answer' => null,
            'keyword_groups' => [],
            'signal_keywords' => [],
        ];

        return [
            'enabled' => true,
            'profile' => 'study_case_kepala_sekolah',
            'method' => 'semantic_similarity',
            'weight' => $weights[$fieldIndex] ?? 33,
            'rubric_code' => $rubricCodes[$fieldIndex] ?? 'K'.($fieldIndex + 1),
            'scale_min' => 1,
            'scale_max' => 5,
            'reference_answer' => $reference['reference_answer'] ?? null,
            'keyword_groups' => $reference['keyword_groups'] ?? [],
            'min_words' => 20,
            'confidence_threshold' => 0.55,
            'manual_review_below_confidence' => true,
            'advanced_rules' => [
                'signal_keywords' => $reference['signal_keywords'] ?? [],
                'structure_markers' => [
                    'analysis' => ['analisis', 'masalah', 'penyebab', 'konteks'],
                    'evaluation' => ['evaluasi', 'dampak', 'konsekuensi', 'risiko'],
                    'strategy' => ['strategi', 'langkah', 'solusi', 'rancang'],
                ],
            ],
        ];
    }

    private function studyCaseReferenceLibrary(): array
    {
        return [
            'CASE-1' => [
                [
                    'reference_answer' => 'Analisis permasalahan yang terjadi. Jawaban ideal menampilkan: Menunjukkan pemahaman tentang integritas dan etika; Mengidentifikasi risiko hukum dan moral; Menawarkan solusi berbasis transparansi dan regulasi.',
                    'keyword_groups' => [
                        [
                            'Menunjukkan pemahaman tentang integritas dan etika',
                        ],
                        [
                            'Mengidentifikasi risiko hukum dan moral',
                        ],
                        [
                            'Menawarkan solusi berbasis transparansi dan regulasi',
                        ],
                    ],
                    'signal_keywords' => [
                        'menunjukkan',
                        'mengidentifikasi',
                        'menawarkan',
                    ],
                ],
                [
                    'reference_answer' => 'Evaluasi dampak jika kepala sekolah mengikuti atau menolak tekanan tersebut. Jawaban ideal menampilkan: Menunjukkan pemahaman tentang integritas dan etika; Mengidentifikasi risiko hukum dan moral; Menawarkan solusi berbasis transparansi dan regulasi.',
                    'keyword_groups' => [
                        [
                            'Menunjukkan pemahaman tentang integritas dan etika',
                        ],
                        [
                            'Mengidentifikasi risiko hukum dan moral',
                        ],
                        [
                            'Menawarkan solusi berbasis transparansi dan regulasi',
                        ],
                    ],
                    'signal_keywords' => [
                        'menunjukkan',
                        'mengidentifikasi',
                        'menawarkan',
                    ],
                ],
                [
                    'reference_answer' => 'Rancang strategi penyelesaian yang tepat dan berintegritas. Jawaban ideal menampilkan: Menunjukkan pemahaman tentang integritas dan etika; Mengidentifikasi risiko hukum dan moral; Menawarkan solusi berbasis transparansi dan regulasi.',
                    'keyword_groups' => [
                        [
                            'Menunjukkan pemahaman tentang integritas dan etika',
                        ],
                        [
                            'Mengidentifikasi risiko hukum dan moral',
                        ],
                        [
                            'Menawarkan solusi berbasis transparansi dan regulasi',
                        ],
                    ],
                    'signal_keywords' => [
                        'menunjukkan',
                        'mengidentifikasi',
                        'menawarkan',
                    ],
                ],
            ],
            'CASE-2' => [
                [
                    'reference_answer' => 'Analisis penyebab rendahnya partisipasi masyarakat. Jawaban ideal menampilkan: Identifikasi faktor komunikasi, kepercayaan, dan keterlibatan; Evaluasi pendekatan yang kurang partisipatif; Strategi kemitraan berbasis stakeholder engagement.',
                    'keyword_groups' => [
                        [
                            'Identifikasi faktor komunikasi, kepercayaan, dan keterlibatan',
                        ],
                        [
                            'Evaluasi pendekatan yang kurang partisipatif',
                        ],
                        [
                            'Strategi kemitraan berbasis stakeholder engagement',
                        ],
                    ],
                    'signal_keywords' => [
                        'identifikasi',
                        'evaluasi',
                        'strategi',
                    ],
                ],
                [
                    'reference_answer' => 'Evaluasi kelemahan pendekatan yang telah dilakukan sekolah. Jawaban ideal menampilkan: Identifikasi faktor komunikasi, kepercayaan, dan keterlibatan; Evaluasi pendekatan yang kurang partisipatif; Strategi kemitraan berbasis stakeholder engagement.',
                    'keyword_groups' => [
                        [
                            'Identifikasi faktor komunikasi, kepercayaan, dan keterlibatan',
                        ],
                        [
                            'Evaluasi pendekatan yang kurang partisipatif',
                        ],
                        [
                            'Strategi kemitraan berbasis stakeholder engagement',
                        ],
                    ],
                    'signal_keywords' => [
                        'identifikasi',
                        'evaluasi',
                        'strategi',
                    ],
                ],
                [
                    'reference_answer' => 'Rancang strategi kolaborasi yang efektif dan berkelanjutan. Jawaban ideal menampilkan: Identifikasi faktor komunikasi, kepercayaan, dan keterlibatan; Evaluasi pendekatan yang kurang partisipatif; Strategi kemitraan berbasis stakeholder engagement.',
                    'keyword_groups' => [
                        [
                            'Identifikasi faktor komunikasi, kepercayaan, dan keterlibatan',
                        ],
                        [
                            'Evaluasi pendekatan yang kurang partisipatif',
                        ],
                        [
                            'Strategi kemitraan berbasis stakeholder engagement',
                        ],
                    ],
                    'signal_keywords' => [
                        'identifikasi',
                        'evaluasi',
                        'strategi',
                    ],
                ],
            ],
            'CASE-3' => [
                [
                    'reference_answer' => 'Analisis penyebab penurunan hasil belajar. Jawaban ideal menampilkan: Mengidentifikasi faktor pedagogik dan kepemimpinan; Evaluasi metode pembelajaran; Strategi seperti PLC, supervisi reflektif, dan inovasi pembelajaran.',
                    'keyword_groups' => [
                        [
                            'Mengidentifikasi faktor pedagogik dan kepemimpinan',
                        ],
                        [
                            'Evaluasi metode pembelajaran',
                        ],
                        [
                            'Strategi seperti PLC, supervisi reflektif, dan inovasi pembelajaran',
                        ],
                    ],
                    'signal_keywords' => [
                        'mengidentifikasi',
                        'evaluasi',
                        'strategi',
                    ],
                ],
                [
                    'reference_answer' => 'Evaluasi praktik pembelajaran yang berlangsung. Jawaban ideal menampilkan: Mengidentifikasi faktor pedagogik dan kepemimpinan; Evaluasi metode pembelajaran; Strategi seperti PLC, supervisi reflektif, dan inovasi pembelajaran.',
                    'keyword_groups' => [
                        [
                            'Mengidentifikasi faktor pedagogik dan kepemimpinan',
                        ],
                        [
                            'Evaluasi metode pembelajaran',
                        ],
                        [
                            'Strategi seperti PLC, supervisi reflektif, dan inovasi pembelajaran',
                        ],
                    ],
                    'signal_keywords' => [
                        'mengidentifikasi',
                        'evaluasi',
                        'strategi',
                    ],
                ],
                [
                    'reference_answer' => 'Rancang intervensi peningkatan kualitas pembelajaran. Jawaban ideal menampilkan: Mengidentifikasi faktor pedagogik dan kepemimpinan; Evaluasi metode pembelajaran; Strategi seperti PLC, supervisi reflektif, dan inovasi pembelajaran.',
                    'keyword_groups' => [
                        [
                            'Mengidentifikasi faktor pedagogik dan kepemimpinan',
                        ],
                        [
                            'Evaluasi metode pembelajaran',
                        ],
                        [
                            'Strategi seperti PLC, supervisi reflektif, dan inovasi pembelajaran',
                        ],
                    ],
                    'signal_keywords' => [
                        'mengidentifikasi',
                        'evaluasi',
                        'strategi',
                    ],
                ],
            ],
            'CASE-4' => [
                [
                    'reference_answer' => 'Analisis permasalahan dalam pengelolaan keuangan. Jawaban ideal menampilkan: Identifikasi masalah tata kelola; Dampak pada kepercayaan publik; Sistem transparansi dan pelaporan berbasis partisipasi.',
                    'keyword_groups' => [
                        [
                            'Identifikasi masalah tata kelola',
                        ],
                        [
                            'Dampak pada kepercayaan publik',
                        ],
                        [
                            'Sistem transparansi dan pelaporan berbasis partisipasi',
                        ],
                    ],
                    'signal_keywords' => [
                        'identifikasi',
                        'dampak',
                        'sistem',
                    ],
                ],
                [
                    'reference_answer' => 'Evaluasi dampak terhadap kepercayaan stakeholder. Jawaban ideal menampilkan: Identifikasi masalah tata kelola; Dampak pada kepercayaan publik; Sistem transparansi dan pelaporan berbasis partisipasi.',
                    'keyword_groups' => [
                        [
                            'Identifikasi masalah tata kelola',
                        ],
                        [
                            'Dampak pada kepercayaan publik',
                        ],
                        [
                            'Sistem transparansi dan pelaporan berbasis partisipasi',
                        ],
                    ],
                    'signal_keywords' => [
                        'identifikasi',
                        'dampak',
                        'sistem',
                    ],
                ],
                [
                    'reference_answer' => 'Rancang sistem pengelolaan keuangan yang transparan dan akuntabel. Jawaban ideal menampilkan: Identifikasi masalah tata kelola; Dampak pada kepercayaan publik; Sistem transparansi dan pelaporan berbasis partisipasi.',
                    'keyword_groups' => [
                        [
                            'Identifikasi masalah tata kelola',
                        ],
                        [
                            'Dampak pada kepercayaan publik',
                        ],
                        [
                            'Sistem transparansi dan pelaporan berbasis partisipasi',
                        ],
                    ],
                    'signal_keywords' => [
                        'identifikasi',
                        'dampak',
                        'sistem',
                    ],
                ],
            ],
            'CASE-5' => [
                [
                    'reference_answer' => 'Analisis penyebab stagnasi inovasi di sekolah. Jawaban ideal menampilkan: Identifikasi budaya organisasi; Evaluasi kepemimpinan inovatif; Program berbasis potensi lokal dan kebutuhan siswa.',
                    'keyword_groups' => [
                        [
                            'Identifikasi budaya organisasi',
                        ],
                        [
                            'Evaluasi kepemimpinan inovatif',
                        ],
                        [
                            'Program berbasis potensi lokal dan kebutuhan siswa',
                        ],
                    ],
                    'signal_keywords' => [
                        'identifikasi',
                        'evaluasi',
                        'program',
                    ],
                ],
                [
                    'reference_answer' => 'Evaluasi budaya organisasi sekolah terkait inovasi. Jawaban ideal menampilkan: Identifikasi budaya organisasi; Evaluasi kepemimpinan inovatif; Program berbasis potensi lokal dan kebutuhan siswa.',
                    'keyword_groups' => [
                        [
                            'Identifikasi budaya organisasi',
                        ],
                        [
                            'Evaluasi kepemimpinan inovatif',
                        ],
                        [
                            'Program berbasis potensi lokal dan kebutuhan siswa',
                        ],
                    ],
                    'signal_keywords' => [
                        'identifikasi',
                        'evaluasi',
                        'program',
                    ],
                ],
                [
                    'reference_answer' => 'Rancang program inovasi berbasis kebutuhan dan potensi sekolah. Jawaban ideal menampilkan: Identifikasi budaya organisasi; Evaluasi kepemimpinan inovatif; Program berbasis potensi lokal dan kebutuhan siswa.',
                    'keyword_groups' => [
                        [
                            'Identifikasi budaya organisasi',
                        ],
                        [
                            'Evaluasi kepemimpinan inovatif',
                        ],
                        [
                            'Program berbasis potensi lokal dan kebutuhan siswa',
                        ],
                    ],
                    'signal_keywords' => [
                        'identifikasi',
                        'evaluasi',
                        'program',
                    ],
                ],
            ],
        ];
    }
}
