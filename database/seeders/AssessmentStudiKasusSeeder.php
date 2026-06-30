<?php

namespace Database\Seeders;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Enum\KompetensiGuru;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentStudiKasusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assessments = [
            [
                'kode_assessment' => 'ASM-STUDI-KASUS-004',
                'judul' => 'Studi Kasus Pemetaan Kompetensi Guru',
                'deskripsi' => 'Assessment berbasis studi kasus untuk memetakan kompetensi pedagogik, kepribadian, sosial, dan profesional guru.',
                'petunjuk' => 'Bacalah setiap kasus dengan saksama. Jawablah seluruh pertanyaan secara analitis, sistematis, dan sesuai dengan konteks tugas seorang guru.',
                'instrument_type' => AssessmentInstrumentType::STUDI_KASUS->value,
                'status' => 'publish',
                'is_active' => true,
                'forms' => [
                    [
                        'judul_form' => 'Studi Kasus 1 – Kompetensi Pedagogik',
                        'kode_form' => 'FORM-SK-PEDAGOGIK',
                        'deskripsi' => "Fokus: Pembelajaran berpusat pada peserta didik.\n\nKasus:\nSeorang guru mengajar dengan metode ceramah hampir di setiap pertemuan. Siswa cenderung pasif, hanya mencatat dan mendengarkan. Hasil asesmen menunjukkan sebagian besar siswa memahami materi secara dangkal dan kesulitan menerapkan konsep dalam situasi nyata. Guru merasa metode tersebut sudah efektif karena materi dapat disampaikan dengan cepat.",
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => 'SK-PED',
                        'indikator_label' => 'Analisis studi kasus pedagogik',
                        'is_scoreable' => true,
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => '1. Identifikasi Permasalahan Utama',
                                'deskripsi' => 'Identifikasi masalah utama yang terjadi dalam proses pembelajaran pada kasus tersebut.',
                                'nama_field' => 'identifikasi_masalah_pedagogik',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan permasalahan utama yang Anda identifikasi.',
                                'bantuan' => 'Jelaskan masalah yang berkaitan dengan metode pembelajaran, keterlibatan peserta didik, dan hasil belajar.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '2. Analisis Penyebab Berdasarkan Prinsip Pedagogik',
                                'deskripsi' => 'Analisis faktor penyebab masalah berdasarkan prinsip pembelajaran yang berpusat pada peserta didik.',
                                'nama_field' => 'analisis_penyebab_pedagogik',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan penyebab masalah berdasarkan prinsip pedagogik.',
                                'bantuan' => 'Uraikan keterkaitan metode ceramah, aktivitas belajar, pemahaman konsep, dan kebutuhan peserta didik.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '3. Rancang Strategi Pembelajaran Berpusat pada Peserta Didik',
                                'deskripsi' => 'Rancang strategi pembelajaran alternatif yang lebih aktif dan berpusat pada peserta didik.',
                                'nama_field' => 'strategi_pembelajaran_pedagogik',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan rancangan strategi pembelajaran yang Anda usulkan.',
                                'bantuan' => 'Sertakan metode, aktivitas peserta didik, peran guru, media atau sumber belajar, serta tahapan pelaksanaan.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '4. Jelaskan Indikator Keberhasilan Strategi',
                                'deskripsi' => 'Jelaskan indikator yang menunjukkan bahwa strategi pembelajaran yang dirancang berhasil.',
                                'nama_field' => 'indikator_keberhasilan_pedagogik',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan indikator keberhasilan strategi pembelajaran.',
                                'bantuan' => 'Pertimbangkan keterlibatan peserta didik, pemahaman konsep, penerapan konsep, serta hasil asesmen.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 4,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Studi Kasus 2 – Kompetensi Kepribadian',
                        'kode_form' => 'FORM-SK-KEPRIBADIAN',
                        'deskripsi' => "Fokus: Integritas, emosi, dan refleksi diri.\n\nKasus:\nSeorang guru diketahui memberikan perlakuan berbeda kepada siswa tertentu karena faktor kedekatan pribadi. Selain itu, guru tersebut mudah terpancing emosi ketika siswa melakukan kesalahan kecil di kelas. Meskipun demikian, guru merasa tindakannya wajar dan belum melakukan refleksi terhadap perilakunya.",
                        'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                        'indikator_kode' => 'SK-KEP',
                        'indikator_label' => 'Analisis studi kasus kepribadian',
                        'is_scoreable' => true,
                        'urutan' => 2,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => '1. Identifikasi Aspek Kepribadian yang Perlu Diperbaiki',
                                'deskripsi' => 'Identifikasi aspek kompetensi kepribadian guru yang perlu diperbaiki berdasarkan kasus.',
                                'nama_field' => 'identifikasi_aspek_kepribadian',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan aspek kepribadian yang perlu diperbaiki.',
                                'bantuan' => 'Pertimbangkan integritas, keadilan, pengendalian emosi, objektivitas, dan kesadaran diri.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '2. Analisis Dampak Perilaku Guru terhadap Peserta Didik',
                                'deskripsi' => 'Analisis dampak perlakuan berbeda dan pengelolaan emosi yang kurang tepat terhadap peserta didik.',
                                'nama_field' => 'analisis_dampak_perilaku_kepribadian',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan dampak perilaku guru terhadap peserta didik.',
                                'bantuan' => 'Bahas dampak terhadap rasa aman, motivasi, kepercayaan, keadilan, serta iklim belajar di kelas.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '3. Rancang Langkah Refleksi Diri Guru',
                                'deskripsi' => 'Rancang langkah refleksi diri yang dapat dilakukan guru untuk memperbaiki perilakunya.',
                                'nama_field' => 'langkah_refleksi_diri_kepribadian',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan langkah refleksi diri yang Anda sarankan.',
                                'bantuan' => 'Sertakan langkah mengenali perilaku, menerima umpan balik, mengevaluasi dampak, dan menyusun perbaikan.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '4. Jelaskan Penerapan Perilaku Sesuai Kode Etik',
                                'deskripsi' => 'Jelaskan cara guru menunjukkan perilaku yang sesuai dengan kode etik profesi guru.',
                                'nama_field' => 'penerapan_kode_etik_kepribadian',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan perilaku profesional yang sesuai kode etik.',
                                'bantuan' => 'Uraikan prinsip objektivitas, keadilan, penghormatan kepada peserta didik, profesionalitas, dan pengendalian emosi.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 4,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Studi Kasus 3 – Kompetensi Sosial',
                        'kode_form' => 'FORM-SK-SOSIAL',
                        'deskripsi' => "Fokus: Kolaborasi dan keterlibatan pihak lain.\n\nKasus:\nDi sebuah sekolah, guru jarang berkolaborasi dengan rekan sejawat. Komunikasi dengan orang tua hanya dilakukan saat pembagian rapor. Selain itu, potensi lingkungan sekitar belum dimanfaatkan sebagai sumber belajar. Akibatnya, pembelajaran kurang kontekstual dan dukungan terhadap siswa menjadi terbatas.",
                        'kompetensi' => KompetensiGuru::SOSIAL->value,
                        'indikator_kode' => 'SK-SOS',
                        'indikator_label' => 'Analisis studi kasus sosial',
                        'is_scoreable' => true,
                        'urutan' => 3,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => '1. Identifikasi Masalah dalam Kompetensi Sosial Guru',
                                'deskripsi' => 'Identifikasi permasalahan kompetensi sosial guru dalam kasus tersebut.',
                                'nama_field' => 'identifikasi_masalah_sosial',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan masalah kompetensi sosial yang Anda identifikasi.',
                                'bantuan' => 'Fokuskan pada kolaborasi rekan sejawat, komunikasi orang tua, dan keterlibatan masyarakat.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '2. Analisis Pentingnya Kolaborasi dan Kemitraan',
                                'deskripsi' => 'Analisis pentingnya kolaborasi dengan guru, orang tua, dan masyarakat dalam mendukung pembelajaran.',
                                'nama_field' => 'analisis_kolaborasi_kemitraan',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan pentingnya kolaborasi dan kemitraan.',
                                'bantuan' => 'Uraikan kontribusi tiap pihak terhadap pembelajaran kontekstual, dukungan belajar, dan perkembangan peserta didik.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '3. Rancang Strategi Peningkatan Kolaborasi',
                                'deskripsi' => 'Rancang strategi untuk meningkatkan kolaborasi guru dengan rekan sejawat, orang tua, dan masyarakat.',
                                'nama_field' => 'strategi_kolaborasi_sosial',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan rancangan strategi kolaborasi.',
                                'bantuan' => 'Sertakan kegiatan, bentuk komunikasi, peran para pihak, sumber daya, dan jadwal pelaksanaan.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '4. Jelaskan Dampak yang Diharapkan',
                                'deskripsi' => 'Jelaskan dampak yang diharapkan setelah strategi kolaborasi diterapkan.',
                                'nama_field' => 'dampak_strategi_sosial',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan dampak yang diharapkan.',
                                'bantuan' => 'Pertimbangkan dampak terhadap kualitas pembelajaran, dukungan peserta didik, hubungan sekolah-keluarga, dan keterlibatan masyarakat.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 4,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Studi Kasus 4 – Kompetensi Profesional',
                        'kode_form' => 'FORM-SK-PROFESIONAL',
                        'deskripsi' => "Fokus: Penguasaan materi dan implementasi kurikulum.\n\nKasus:\nSeorang guru mengajar sesuai buku teks tanpa mengembangkan materi lebih lanjut. Pembelajaran tidak dikaitkan dengan konteks kehidupan siswa dan tidak sepenuhnya mengacu pada capaian pembelajaran dalam kurikulum. Akibatnya, siswa kurang memahami relevansi materi yang dipelajari.",
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'indikator_kode' => 'SK-PRO',
                        'indikator_label' => 'Analisis studi kasus profesional',
                        'is_scoreable' => true,
                        'urutan' => 4,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => '1. Identifikasi Kelemahan Kompetensi Profesional Guru',
                                'deskripsi' => 'Identifikasi kelemahan kompetensi profesional guru berdasarkan kasus.',
                                'nama_field' => 'identifikasi_kelemahan_profesional',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan kelemahan kompetensi profesional yang Anda identifikasi.',
                                'bantuan' => 'Perhatikan penguasaan materi, pengembangan bahan ajar, relevansi konteks, dan pemahaman kurikulum.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '2. Analisis Keterkaitan Penguasaan Materi dan Kurikulum',
                                'deskripsi' => 'Analisis hubungan antara penguasaan materi, capaian pembelajaran, dan implementasi kurikulum.',
                                'nama_field' => 'analisis_materi_kurikulum_profesional',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan keterkaitan penguasaan materi dan kurikulum.',
                                'bantuan' => 'Uraikan pentingnya menurunkan capaian pembelajaran ke tujuan, aktivitas, asesmen, dan materi kontekstual.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '3. Rancang Strategi Peningkatan Kualitas Pembelajaran',
                                'deskripsi' => 'Rancang strategi yang dapat meningkatkan kualitas pembelajaran pada kasus tersebut.',
                                'nama_field' => 'strategi_peningkatan_pembelajaran_profesional',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan strategi peningkatan kualitas pembelajaran.',
                                'bantuan' => 'Sertakan pengembangan materi, penggunaan konteks nyata, variasi metode, sumber belajar, dan asesmen.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => '4. Jelaskan Integrasi Materi, Strategi, dan Kebutuhan Peserta Didik',
                                'deskripsi' => 'Jelaskan cara guru mengintegrasikan materi ajar, strategi pembelajaran, dan kebutuhan peserta didik.',
                                'nama_field' => 'integrasi_materi_strategi_kebutuhan_siswa',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Jelaskan bentuk integrasi materi, strategi, dan kebutuhan peserta didik.',
                                'bantuan' => 'Jelaskan kesesuaian antara capaian pembelajaran, karakteristik peserta didik, konteks kehidupan nyata, aktivitas, dan asesmen.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 4,
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
                    'target_ketenagaan' => AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
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
                    'K1' => 20,
                    'K2' => 25,
                    'K3' => 30,
                    'K4' => 15,
                    'K5' => 10,
                ],
                'competency_source_map' => [
                    KompetensiGuru::PEDAGOGIK->value => 'Kasus 1',
                    KompetensiGuru::KEPRIBADIAN->value => 'Kasus 2',
                    KompetensiGuru::SOSIAL->value => 'Kasus 3',
                    KompetensiGuru::PROFESIONAL->value => 'Kasus 4',
                ],
            ],
        ];
    }

    private function formScoringConfig(array $formData): array
    {
        $focusMap = [
            KompetensiGuru::PEDAGOGIK->value => 'Pembelajaran berpusat pada peserta didik',
            KompetensiGuru::KEPRIBADIAN->value => 'Integritas, emosi, dan refleksi diri',
            KompetensiGuru::SOSIAL->value => 'Kolaborasi dan keterlibatan pihak lain',
            KompetensiGuru::PROFESIONAL->value => 'Penguasaan materi dan implementasi kurikulum',
        ];

        return [
            'profile' => 'study_case_default',
            'weight' => 100,
            'advanced_rules' => [
                'case_formula' => 'K1 20% + K2 25% + K3 30% + K4 15% + K5 10%',
                'focus' => $focusMap[$formData['kompetensi'] ?? ''] ?? null,
                'synthetic_k5' => [
                    'weight' => 10,
                    'reference_answer' => 'Jawaban etis, adil, inklusif, layak diterapkan, jelas, dan mampu membangun dukungan dari pihak terkait.',
                    'keyword_groups' => [
                        ['etika', 'adil'],
                        ['inklusif', 'aman'],
                        ['komunikasi', 'kolaborasi'],
                        ['layak', 'realistis'],
                    ],
                    'synonyms' => [
                        'kolaborasi' => ['kerja sama', 'kemitraan'],
                        'adil' => ['objektif', 'setara'],
                    ],
                    'signal_keywords' => ['etika', 'keadilan', 'inklusif', 'komunikasi', 'dukungan'],
                    'min_words' => 25,
                ],
            ],
        ];
    }

    private function fieldScoringConfig(array $formData, int $fieldIndex): array
    {
        $kompetensi = $formData['kompetensi'] ?? null;
        $weights = [20, 25, 30, 15];
        $rubricCodes = ['K1', 'K2', 'K3', 'K4'];
        $references = $this->studyCaseReferenceLibrary();
        $reference = $references[$kompetensi][$fieldIndex] ?? [
            'reference_answer' => null,
            'keyword_groups' => [],
        ];

        return [
            'enabled' => true,
            'profile' => 'study_case_default',
            'method' => 'semantic_similarity',
            'weight' => $weights[$fieldIndex] ?? 10,
            'rubric_code' => $rubricCodes[$fieldIndex] ?? 'K'.($fieldIndex + 1),
            'scale_min' => 1,
            'scale_max' => 5,
            'reference_answer' => $reference['reference_answer'] ?? null,
            'keyword_groups' => $reference['keyword_groups'] ?? [],
            'synonyms' => $reference['synonyms'] ?? [],
            'min_words' => 18,
            'confidence_threshold' => 0.55,
            'manual_review_below_confidence' => true,
            'advanced_rules' => [
                'min_words' => 18,
                'signal_keywords' => $reference['signal_keywords'] ?? [],
                'structure_markers' => [
                    'analysis' => ['masalah', 'penyebab', 'dampak', 'konteks'],
                    'strategy' => ['strategi', 'langkah', 'solusi', 'peran'],
                    'evaluation' => ['indikator', 'monitoring', 'evaluasi', 'umpan balik'],
                ],
            ],
        ];
    }

    private function studyCaseReferenceLibrary(): array
    {
        return [
            KompetensiGuru::PEDAGOGIK->value => [
                [
                    'reference_answer' => 'Masalah utama terletak pada pembelajaran yang masih berpusat pada guru, partisipasi aktif peserta didik rendah, dan pemahaman konsep hanya dangkal.',
                    'keyword_groups' => [
                        ['berpusat', 'guru'],
                        ['partisipasi', 'aktif'],
                        ['pemahaman', 'konsep'],
                    ],
                    'signal_keywords' => ['berpusat', 'partisipasi', 'konsep'],
                ],
                [
                    'reference_answer' => 'Penyebab utama berkaitan dengan dominasi metode ceramah, kurangnya asesmen atau umpan balik, dan tidak terpenuhinya kebutuhan belajar peserta didik.',
                    'keyword_groups' => [
                        ['metode', 'ceramah'],
                        ['asesmen', 'umpan'],
                        ['kebutuhan', 'belajar'],
                    ],
                    'signal_keywords' => ['ceramah', 'asesmen', 'kebutuhan'],
                ],
                [
                    'reference_answer' => 'Strategi perbaikan perlu merancang pembelajaran aktif, kolaboratif, berbasis masalah atau proyek, disertai peran guru sebagai fasilitator dan tahapan yang jelas.',
                    'keyword_groups' => [
                        ['pembelajaran', 'aktif'],
                        ['guru', 'fasilitator'],
                        ['langkah', 'jelas'],
                    ],
                    'signal_keywords' => ['aktif', 'kolaboratif', 'fasilitator'],
                ],
                [
                    'reference_answer' => 'Indikator keberhasilan harus terukur pada partisipasi aktif, pemahaman konsep, kualitas hasil asesmen, dan tindak lanjut evaluasi.',
                    'keyword_groups' => [
                        ['indikator', 'terukur'],
                        ['partisipasi', 'aktif'],
                        ['hasil', 'asesmen'],
                    ],
                    'signal_keywords' => ['terukur', 'partisipasi', 'asesmen'],
                ],
            ],
            KompetensiGuru::KEPRIBADIAN->value => [
                [
                    'reference_answer' => 'Masalah utama adalah kurangnya integritas, objektivitas, keadilan, dan pengendalian emosi guru.',
                    'keyword_groups' => [
                        ['integritas'],
                        ['objektivitas', 'adil'],
                        ['emosi'],
                    ],
                    'signal_keywords' => ['integritas', 'objektif', 'emosi'],
                ],
                [
                    'reference_answer' => 'Perilaku guru berdampak pada rasa aman, kepercayaan, motivasi belajar, dan iklim kelas yang tidak adil.',
                    'keyword_groups' => [
                        ['rasa', 'aman'],
                        ['kepercayaan'],
                        ['motivasi', 'belajar'],
                    ],
                    'signal_keywords' => ['aman', 'kepercayaan', 'motivasi'],
                ],
                [
                    'reference_answer' => 'Refleksi diri perlu mencakup pengenalan perilaku, penerimaan umpan balik, evaluasi dampak, dan rencana perbaikan profesional.',
                    'keyword_groups' => [
                        ['refleksi'],
                        ['umpan', 'balik'],
                        ['rencana', 'perbaikan'],
                    ],
                    'signal_keywords' => ['refleksi', 'umpan balik', 'perbaikan'],
                ],
                [
                    'reference_answer' => 'Perilaku sesuai kode etik harus objektif, adil, menghormati peserta didik, profesional, dan mampu mengelola emosi.',
                    'keyword_groups' => [
                        ['kode', 'etik'],
                        ['profesional'],
                        ['kelola', 'emosi'],
                    ],
                    'signal_keywords' => ['kode etik', 'profesional', 'emosi'],
                ],
            ],
            KompetensiGuru::SOSIAL->value => [
                [
                    'reference_answer' => 'Masalah sosial guru tampak pada lemahnya kolaborasi sejawat, komunikasi orang tua, dan pemanfaatan masyarakat sebagai mitra belajar.',
                    'keyword_groups' => [
                        ['kolaborasi', 'sejawat'],
                        ['komunikasi', 'orang'],
                        ['masyarakat'],
                    ],
                    'signal_keywords' => ['kolaborasi', 'orang tua', 'masyarakat'],
                ],
                [
                    'reference_answer' => 'Kolaborasi dan kemitraan penting untuk mendukung pembelajaran kontekstual, dukungan belajar peserta didik, dan kesinambungan intervensi.',
                    'keyword_groups' => [
                        ['kolaborasi'],
                        ['pembelajaran', 'kontekstual'],
                        ['dukungan', 'belajar'],
                    ],
                    'signal_keywords' => ['kemitraan', 'kontekstual', 'dukungan'],
                ],
                [
                    'reference_answer' => 'Strategi perlu memuat komunikasi terencana, pembagian peran, kegiatan kolaboratif, sumber daya, dan jadwal pelaksanaan.',
                    'keyword_groups' => [
                        ['komunikasi', 'terencana'],
                        ['pembagian', 'peran'],
                        ['jadwal'],
                    ],
                    'signal_keywords' => ['komunikasi', 'peran', 'jadwal'],
                ],
                [
                    'reference_answer' => 'Dampak yang diharapkan terlihat pada peningkatan kualitas pembelajaran, dukungan peserta didik, hubungan sekolah-keluarga, dan keterlibatan masyarakat.',
                    'keyword_groups' => [
                        ['kualitas', 'pembelajaran'],
                        ['hubungan', 'keluarga'],
                        ['keterlibatan', 'masyarakat'],
                    ],
                    'signal_keywords' => ['kualitas', 'keluarga', 'masyarakat'],
                ],
            ],
            KompetensiGuru::PROFESIONAL->value => [
                [
                    'reference_answer' => 'Kelemahan profesional tampak pada penguasaan materi yang terbatas, ketergantungan pada buku teks, dan tidak terhubung dengan kurikulum maupun konteks peserta didik.',
                    'keyword_groups' => [
                        ['penguasaan', 'materi'],
                        ['buku', 'teks'],
                        ['kurikulum'],
                    ],
                    'signal_keywords' => ['materi', 'buku teks', 'kurikulum'],
                ],
                [
                    'reference_answer' => 'Hubungan materi dan kurikulum harus menurunkan capaian pembelajaran ke tujuan, aktivitas, asesmen, dan materi yang relevan.',
                    'keyword_groups' => [
                        ['capaian', 'pembelajaran'],
                        ['tujuan', 'aktivitas'],
                        ['asesmen'],
                    ],
                    'signal_keywords' => ['capaian', 'tujuan', 'asesmen'],
                ],
                [
                    'reference_answer' => 'Strategi peningkatan mutu perlu mengembangkan materi, konteks nyata, variasi metode, sumber belajar, dan asesmen yang sesuai.',
                    'keyword_groups' => [
                        ['materi'],
                        ['konteks', 'nyata'],
                        ['variasi', 'metode'],
                    ],
                    'signal_keywords' => ['materi', 'konteks', 'metode'],
                ],
                [
                    'reference_answer' => 'Integrasi yang baik menyelaraskan materi ajar, strategi pembelajaran, karakteristik peserta didik, konteks, dan asesmen.',
                    'keyword_groups' => [
                        ['materi', 'ajar'],
                        ['karakteristik', 'peserta'],
                        ['strategi', 'asesmen'],
                    ],
                    'signal_keywords' => ['materi ajar', 'karakteristik', 'asesmen'],
                ],
            ],
        ];
    }
}
