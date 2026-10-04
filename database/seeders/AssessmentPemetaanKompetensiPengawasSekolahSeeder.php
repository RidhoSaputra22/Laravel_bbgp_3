<?php

namespace Database\Seeders;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class AssessmentPemetaanKompetensiPengawasSekolahSeeder extends Seeder
{
    public function run(): void
    {
        $this->persistAssessment([
            'kode_assessment' => 'ASM-PENGAWAS-PORTOFOLIO-2026',
            'judul' => 'Portofolio',
            'deskripsi' => 'Instrumen portofolio pemetaan kompetensi pengawas sekolah BBGTK Sulawesi Selatan Tahun 2026.',
            'petunjuk' => 'Isilah data berikut secara jujur dan lengkap. Sertakan bukti dokumen pendukung pada bagian yang relevan.',
            'instrument_type' => AssessmentInstrumentType::PORTOFOLIO->value,
            'scoring_config' => $this->portfolioScoringConfig(),
            'forms' => $this->portfolioForms(),
        ]);

        $questions = $this->multipleChoiceQuestions();
        if (count($questions) !== 104) {
            throw new RuntimeException('Instrumen pilihan ganda pengawas harus memiliki 104 butir.');
        }

        $this->persistAssessment([
            'kode_assessment' => 'ASM-PENGAWAS-PGK-2026',
            'judul' => 'Pilihan Ganda Kompleks',
            'deskripsi' => 'Tes pilihan ganda kompleks pemetaan kompetensi kepribadian, sosial, dan profesional pengawas sekolah BBGTK Sulawesi Selatan Tahun 2026.',
            'petunjuk' => 'Pilihlah jawaban yang sesuai dengan kondisi atau pemahaman Anda saat ini secara jujur. Semua pilihan benar dan merepresentasikan Level 1 (Paham) sampai Level 5 (Ahli).',
            'instrument_type' => AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value,
            'scoring_config' => $this->multipleChoiceScoringConfig($questions),
            'forms' => $this->multipleChoiceForms($questions),
        ]);

        $this->persistAssessment([
            'kode_assessment' => 'ASM-PENGAWAS-STUDI-KASUS-2026',
            'judul' => 'Studi Kasus',
            'deskripsi' => 'Lima studi kasus untuk memetakan kemampuan analisis, pembinaan, kolaborasi, pengembangan satuan pendidikan, dan pemanfaatan teknologi pengawas sekolah.',
            'petunjuk' => 'Analisis setiap kasus dan rumuskan langkah Anda secara sistematis dengan memperhatikan konteks, etika, strategi pendampingan, serta tindak lanjut yang dapat dipantau.',
            'instrument_type' => AssessmentInstrumentType::STUDI_KASUS->value,
            'scoring_config' => $this->caseStudyScoringConfig(),
            'forms' => $this->caseStudyForms(),
        ]);
    }

    private function persistAssessment(array $config): void
    {
        $forms = $config['forms'];
        unset($config['forms']);

        $assessment = Assessment::updateOrCreate(
            ['kode_assessment' => $config['kode_assessment']],
            [
                'judul' => $config['judul'],
                'slug' => Str::slug($config['judul']),
                'deskripsi' => $config['deskripsi'],
                'petunjuk' => $config['petunjuk'],
                'instrument_type' => $config['instrument_type'],
                'target_ketenagaan' => AssessmentKetenagaanType::TENAGA_KEPENDIDIKAN->value,
                'target_jabatan' => ['Pengawas'],
                'scoring_config' => $config['scoring_config'],
                'status' => 'publish',
                'is_active' => true,
            ]
        );

        $assessment->forms()->delete();

        foreach ($forms as $formData) {
            $fields = $formData['fields'];
            unset($formData['fields']);
            $form = $assessment->forms()->create($formData);

            foreach ($fields as $fieldData) {
                $form->fields()->create($fieldData);
            }
        }
    }

    private function portfolioScoringConfig(): array
    {
        return [
            'profile' => AssessmentInstrumentType::PORTOFOLIO->value,
            'weight' => AssessmentInstrumentType::PORTOFOLIO->weight(),
            'verification_gap_threshold' => 1.5,
            'advanced_rules' => [
                'overall_formula' => 'Skor portofolio dihitung dari kelengkapan dan kualitas data portofolio serta verifikasi dokumen pendukung.',
                'sections' => ['P-2', 'P-3', 'P-4', 'P-5', 'P-6', 'P-7'],
            ],
        ];
    }

    private function multipleChoiceScoringConfig(array $questions): array
    {
        $domainCounts = collect($questions)->countBy('competency')->all();

        return [
            'profile' => AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value,
            'weight' => AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->weight(),
            'verification_gap_threshold' => 1.5,
            'empty_response_threshold_percent' => 10,
            'advanced_rules' => [
                'question_count' => count($questions),
                'response_scoring_rule' => 'Pilihan Level 1-5 langsung dikonversi menjadi skor 1-5 tanpa benar atau salah.',
                'domain_question_counts' => $domainCounts,
                'source_note' => 'Naskah lampiran memuat 104 blok soal meskipun judul bagian menyebut 50 soal; seluruh blok dipertahankan.',
            ],
        ];
    }

    private function caseStudyScoringConfig(): array
    {
        return [
            'profile' => AssessmentInstrumentType::STUDI_KASUS->value,
            'weight' => AssessmentInstrumentType::STUDI_KASUS->weight(),
            'verification_gap_threshold' => 1.5,
            'advanced_rules' => [
                'case_count' => 5,
                'task_count_per_case' => 4,
                'task_formula' => 'Setiap tugas studi kasus berbobot 25%.',
            ],
        ];
    }

    private function multipleChoiceForms(array $questions): array
    {
        $forms = [];
        foreach ($questions as $index => $question) {
            $key = $question['sub_code'];
            if (! isset($forms[$key])) {
                $forms[$key] = [
                    'judul_form' => $question['sub_code'].' '.$question['sub_label'],
                    'kode_form' => 'FORM-PENGAWAS-PG-'.strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $question['sub_code'])),
                    'deskripsi' => 'Kompetensi '.ucfirst($question['competency']).' — '.$question['sub_label'].'.',
                    'kompetensi' => $question['competency'],
                    'indikator_kode' => $question['sub_code'],
                    'indikator_label' => $question['sub_label'],
                    'is_scoreable' => true,
                    'scoring_config' => [
                        'profile' => AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value,
                        'weight' => 100,
                        'advanced_rules' => [
                            'question_count' => 0,
                            'response_rule' => 'Setiap pilihan merepresentasikan level kompetensi 1-5.',
                        ],
                    ],
                    'urutan' => count($forms) + 1,
                    'is_active' => true,
                    'fields' => [],
                ];
            }

            $form =& $forms[$key];
            $form['scoring_config']['advanced_rules']['question_count']++;
            $questionNumber = $index + 1;
            $form['fields'][] = [
                'label' => 'Soal '.$question['source_number'].' ('.$question['hots'].') — '.$question['prompt'],
                'deskripsi' => $question['stimulus'],
                'nama_field' => 'soal_'.str_pad((string) $questionNumber, 3, '0', STR_PAD_LEFT),
                'tipe_field' => 'radio',
                'placeholder' => null,
                'bantuan' => 'Pilih satu jawaban yang paling sesuai. Semua pilihan merepresentasikan level kompetensi 1-5; tidak ada jawaban salah.',
                'opsi_field' => $question['options'],
                'nilai_default' => null,
                'validasi' => ['required' => true],
                'scoring_config' => [
                    'enabled' => true,
                    'profile' => AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value,
                    'weight' => 100,
                    'scale_min' => 1,
                    'scale_max' => 5,
                ],
                'urutan' => count($form['fields']) + 1,
                'is_required' => true,
                'is_active' => true,
            ];
            unset($form);
        }

        return array_values($forms);
    }

    private function caseStudyForms(): array
    {
        return $this->caseStudyFormData();
    }

    private function portfolioForms(): array
    {
        $payload = <<<'JSON'
[
    {
        "judul_form": "1. Identitas Responden",
        "kode_form": "FORM-PENGAWAS-IDENTITAS",
        "deskripsi": "Data identitas responden sesuai instrumen portofolio.",
        "is_scoreable": false,
        "scoring_config": null,
        "urutan": 1,
        "is_active": true,
        "fields": [
            {
                "label": "Nama Lengkap",
                "deskripsi": "Nama Lengkap",
                "nama_field": "nama_lengkap",
                "tipe_field": "text",
                "placeholder": "Nama lengkap sesuai identitas resmi.",
                "bantuan": "Isi nama lengkap.",
                "opsi_field": null,
                "nilai_default": null,
                "autofill_source": "nama_lengkap",
                "validasi": {
                    "required": true
                },
                "scoring_config": null,
                "urutan": 1,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "NIP/NUPTK",
                "deskripsi": "NIP/NUPTK",
                "nama_field": "nip_nuptk",
                "tipe_field": "text",
                "placeholder": "NIP atau NUPTK.",
                "bantuan": "Isi NIP/NUPTK yang tersedia.",
                "opsi_field": null,
                "nilai_default": null,
                "autofill_source": "nip_nuptk",
                "validasi": {
                    "required": true
                },
                "scoring_config": null,
                "urutan": 2,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "Pangkat / Golongan",
                "deskripsi": "Pangkat / Golongan",
                "nama_field": "pangkat_golongan",
                "tipe_field": "text",
                "placeholder": "Contoh: Pembina / IV-a.",
                "bantuan": "Isi pangkat/golongan terakhir.",
                "opsi_field": null,
                "nilai_default": null,
                "autofill_source": "pangkat_golongan",
                "validasi": {
                    "required": true
                },
                "scoring_config": null,
                "urutan": 3,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "Jabatan (Guru/Kepala Sekolah/Pengawas)",
                "deskripsi": "Jabatan (Guru/Kepala Sekolah/Pengawas)",
                "nama_field": "jabatan",
                "tipe_field": "select",
                "placeholder": "Pilih jabatan.",
                "bantuan": "Pilih jabatan sesuai kondisi saat ini.",
                "opsi_field": [
                    "Guru",
                    "Kepala Sekolah",
                    "Pengawas Sekolah",
                    "Pengawas",
                    "GTK Lainnya"
                ],
                "nilai_default": null,
                "autofill_source": "jabatan",
                "validasi": {
                    "required": true
                },
                "scoring_config": null,
                "urutan": 4,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "Instansi",
                "deskripsi": "Instansi",
                "nama_field": "instansi",
                "tipe_field": "text",
                "placeholder": "Nama instansi.",
                "bantuan": "Isi instansi tempat bertugas.",
                "opsi_field": null,
                "nilai_default": null,
                "autofill_source": "satuan_pendidikan",
                "validasi": {
                    "required": true
                },
                "scoring_config": null,
                "urutan": 5,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "Kabupaten/Kota",
                "deskripsi": "Kabupaten/Kota",
                "nama_field": "kabupaten_kota",
                "tipe_field": "text",
                "placeholder": "Kabupaten/kota.",
                "bantuan": "Isi lokasi instansi.",
                "opsi_field": null,
                "nilai_default": null,
                "autofill_source": "kabupaten",
                "validasi": {
                    "required": true
                },
                "scoring_config": null,
                "urutan": 6,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "Lama menjadi Pengawas Sekolah (Tahun)",
                "deskripsi": "Lama menjadi Pengawas Sekolah (Tahun)",
                "nama_field": "lama_menjadi_pengawas_sekolah",
                "tipe_field": "number",
                "placeholder": "0",
                "bantuan": "Isi jumlah tahun menjadi pengawas sekolah.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min": 0
                },
                "scoring_config": null,
                "urutan": 7,
                "is_required": true,
                "is_active": true
            }
        ]
    },
    {
        "judul_form": "2. Riwayat Pendidikan Formal",
        "kode_form": "FORM-PENGAWAS-PENDIDIKAN",
        "deskripsi": "Riwayat pendidikan formal dengan jenjang S1, Sertifikasi, S2, dan S3.",
        "kompetensi": "profesional",
        "indikator_kode": "P-2",
        "indikator_label": "Riwayat pendidikan formal",
        "is_scoreable": true,
        "scoring_config": {
            "profile": "portofolio",
            "weight": 100,
            "advanced_rules": {
                "rubric_code": "P-2"
            }
        },
        "urutan": 2,
        "is_active": true,
        "fields": [
            {
                "label": "Riwayat Pendidikan Formal",
                "deskripsi": "Isi baris untuk jenjang S1, Sertifikasi, S2, dan/atau S3.",
                "nama_field": "riwayat_pendidikan_formal",
                "tipe_field": "repeater",
                "placeholder": null,
                "bantuan": "Tambahkan satu baris untuk setiap jenjang pendidikan atau sertifikasi.",
                "opsi_field": {
                    "min_rows": 1,
                    "max_rows": 4,
                    "columns": [
                        {
                            "label": "Jenjang",
                            "nama_field": "jenjang",
                            "tipe_field": "select",
                            "opsi_field": [
                                "S1",
                                "Sertifikasi",
                                "S2",
                                "S3"
                            ],
                            "is_required": true
                        },
                        {
                            "label": "Gelar",
                            "nama_field": "gelar",
                            "tipe_field": "text",
                            "placeholder": "Contoh: M.Pd.",
                            "is_required": false
                        },
                        {
                            "label": "Program Studi",
                            "nama_field": "program_studi",
                            "tipe_field": "text",
                            "placeholder": "Nama program studi.",
                            "is_required": true
                        },
                        {
                            "label": "Lembaga",
                            "nama_field": "lembaga",
                            "tipe_field": "text",
                            "placeholder": "Nama lembaga pendidikan.",
                            "is_required": true
                        },
                        {
                            "label": "Tahun Perolehan",
                            "nama_field": "tahun_perolehan",
                            "tipe_field": "number",
                            "placeholder": "2020",
                            "is_required": true
                        }
                    ]
                },
                "nilai_default": null,
                "validasi": {
                    "required": true
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "portofolio",
                    "method": "repeater_completeness",
                    "weight": 100,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 1,
                "is_required": true,
                "is_active": true
            }
        ]
    },
    {
        "judul_form": "3. Pengalaman Pelatihan yang Relevan dengan Profesi (5 Tahun Terakhir)",
        "kode_form": "FORM-PENGAWAS-PELATIHAN",
        "deskripsi": "Pengalaman pelatihan relevan dengan profesi dalam lima tahun terakhir.",
        "kompetensi": "profesional",
        "indikator_kode": "P-3",
        "indikator_label": "Pengalaman pelatihan relevan",
        "is_scoreable": true,
        "scoring_config": {
            "profile": "portofolio",
            "weight": 100,
            "advanced_rules": {
                "rubric_code": "P-3"
            }
        },
        "urutan": 3,
        "is_active": true,
        "fields": [
            {
                "label": "Pengalaman Pelatihan",
                "deskripsi": "Cantumkan pelatihan yang relevan dalam lima tahun terakhir.",
                "nama_field": "pengalaman_pelatihan",
                "tipe_field": "repeater",
                "placeholder": null,
                "bantuan": "Tambahkan satu baris untuk setiap pelatihan.",
                "opsi_field": {
                    "min_rows": 0,
                    "max_rows": 20,
                    "columns": [
                        {
                            "label": "Nama Pelatihan",
                            "nama_field": "nama_pelatihan",
                            "tipe_field": "text",
                            "placeholder": "Nama pelatihan.",
                            "is_required": true
                        },
                        {
                            "label": "Penyelenggara",
                            "nama_field": "penyelenggara",
                            "tipe_field": "text",
                            "placeholder": "Nama penyelenggara.",
                            "is_required": true
                        },
                        {
                            "label": "Tahun",
                            "nama_field": "tahun",
                            "tipe_field": "number",
                            "placeholder": "2026",
                            "is_required": true
                        },
                        {
                            "label": "Durasi (JP)",
                            "nama_field": "durasi_jp",
                            "tipe_field": "number",
                            "placeholder": "32",
                            "is_required": true
                        }
                    ]
                },
                "nilai_default": null,
                "validasi": {
                    "required": false
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "portofolio",
                    "method": "repeater_completeness",
                    "weight": 100,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 1,
                "is_required": false,
                "is_active": true
            }
        ]
    },
    {
        "judul_form": "4. Pengalaman Kerja",
        "kode_form": "FORM-PENGAWAS-PENGALAMAN-KERJA",
        "deskripsi": "Pengalaman kerja responden.",
        "kompetensi": "profesional",
        "indikator_kode": "P-4",
        "indikator_label": "Pengalaman kerja",
        "is_scoreable": true,
        "scoring_config": {
            "profile": "portofolio",
            "weight": 100,
            "advanced_rules": {
                "rubric_code": "P-4"
            }
        },
        "urutan": 4,
        "is_active": true,
        "fields": [
            {
                "label": "Pengalaman Kerja",
                "deskripsi": "Cantumkan pengalaman kerja yang relevan.",
                "nama_field": "pengalaman_kerja",
                "tipe_field": "repeater",
                "placeholder": null,
                "bantuan": "Tambahkan satu baris untuk setiap pengalaman kerja.",
                "opsi_field": {
                    "min_rows": 0,
                    "max_rows": 20,
                    "columns": [
                        {
                            "label": "Pengalaman",
                            "nama_field": "pengalaman",
                            "tipe_field": "text",
                            "placeholder": "Contoh: Pengawas Sekolah.",
                            "is_required": true
                        },
                        {
                            "label": "Lembaga",
                            "nama_field": "lembaga",
                            "tipe_field": "text",
                            "placeholder": "Nama lembaga.",
                            "is_required": true
                        },
                        {
                            "label": "Tahun",
                            "nama_field": "tahun",
                            "tipe_field": "text",
                            "placeholder": "Contoh: 2020–2026.",
                            "is_required": true
                        }
                    ]
                },
                "nilai_default": null,
                "validasi": {
                    "required": false
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "portofolio",
                    "method": "repeater_completeness",
                    "weight": 100,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 1,
                "is_required": false,
                "is_active": true
            }
        ]
    },
    {
        "judul_form": "5. Prestasi / Penghargaan",
        "kode_form": "FORM-PENGAWAS-PRESTASI",
        "deskripsi": "Prestasi atau penghargaan yang pernah diperoleh.",
        "kompetensi": "profesional",
        "indikator_kode": "P-5",
        "indikator_label": "Prestasi dan penghargaan",
        "is_scoreable": true,
        "scoring_config": {
            "profile": "portofolio",
            "weight": 100,
            "advanced_rules": {
                "rubric_code": "P-5"
            }
        },
        "urutan": 5,
        "is_active": true,
        "fields": [
            {
                "label": "Prestasi / Penghargaan",
                "deskripsi": "Cantumkan prestasi atau penghargaan beserta deskripsi singkat.",
                "nama_field": "prestasi_penghargaan",
                "tipe_field": "repeater",
                "placeholder": null,
                "bantuan": "Tambahkan satu baris untuk setiap prestasi/penghargaan.",
                "opsi_field": {
                    "min_rows": 0,
                    "max_rows": 20,
                    "columns": [
                        {
                            "label": "Nama Prestasi / Penghargaan",
                            "nama_field": "nama_prestasi",
                            "tipe_field": "text",
                            "placeholder": "Nama prestasi atau penghargaan.",
                            "is_required": true
                        },
                        {
                            "label": "Deskripsi Singkat",
                            "nama_field": "deskripsi_singkat",
                            "tipe_field": "textarea",
                            "placeholder": "Deskripsi singkat.",
                            "is_required": true
                        }
                    ]
                },
                "nilai_default": null,
                "validasi": {
                    "required": false
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "portofolio",
                    "method": "repeater_completeness",
                    "weight": 100,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 1,
                "is_required": false,
                "is_active": true
            }
        ]
    },
    {
        "judul_form": "6. Karya / Inovasi / Best Practice",
        "kode_form": "FORM-PENGAWAS-KARYA-INOVASI",
        "deskripsi": "Karya, inovasi, atau praktik baik yang pernah dikembangkan.",
        "kompetensi": "profesional",
        "indikator_kode": "P-6",
        "indikator_label": "Karya, inovasi, dan best practice",
        "is_scoreable": true,
        "scoring_config": {
            "profile": "portofolio",
            "weight": 100,
            "advanced_rules": {
                "rubric_code": "P-6"
            }
        },
        "urutan": 6,
        "is_active": true,
        "fields": [
            {
                "label": "Karya / Inovasi / Best Practice",
                "deskripsi": "Cantumkan karya, inovasi, atau best practice beserta deskripsi singkat.",
                "nama_field": "karya_inovasi_best_practice",
                "tipe_field": "repeater",
                "placeholder": null,
                "bantuan": "Tambahkan satu baris untuk setiap karya/inovasi/praktik baik.",
                "opsi_field": {
                    "min_rows": 0,
                    "max_rows": 20,
                    "columns": [
                        {
                            "label": "Judul Inovasi",
                            "nama_field": "judul_inovasi",
                            "tipe_field": "text",
                            "placeholder": "Judul karya/inovasi/praktik baik.",
                            "is_required": true
                        },
                        {
                            "label": "Deskripsi Singkat",
                            "nama_field": "deskripsi_singkat",
                            "tipe_field": "textarea",
                            "placeholder": "Deskripsi singkat karya/inovasi.",
                            "is_required": true
                        }
                    ]
                },
                "nilai_default": null,
                "validasi": {
                    "required": false
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "portofolio",
                    "method": "repeater_completeness",
                    "weight": 100,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 1,
                "is_required": false,
                "is_active": true
            }
        ]
    },
    {
        "judul_form": "7. Refleksi Diri",
        "kode_form": "FORM-PENGAWAS-REFLEKSI",
        "deskripsi": "Refleksi kekuatan dan area pengembangan responden sebagai calon/aktif pengawas sekolah.",
        "kompetensi": "kepribadian",
        "indikator_kode": "P-7",
        "indikator_label": "Refleksi diri",
        "is_scoreable": true,
        "scoring_config": {
            "profile": "portofolio",
            "weight": 100,
            "advanced_rules": {
                "rubric_code": "P-7"
            }
        },
        "urutan": 7,
        "is_active": true,
        "fields": [
            {
                "label": "Kekuatan dan Area Pengembangan Anda",
                "deskripsi": "Tuliskan kekuatan dan area pengembangan Anda sebagai calon/aktif kepala sekolah.",
                "nama_field": "refleksi_diri",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan refleksi diri secara jujur dan lengkap.",
                "bantuan": "Uraikan kekuatan, area pengembangan, pengalaman pendukung, dan rencana perbaikan.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 50
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "portofolio",
                    "method": "semantic_similarity",
                    "weight": 100,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 1,
                "is_required": true,
                "is_active": true
            }
        ]
    },
    {
        "judul_form": "Dokumen Pendukung",
        "kode_form": "FORM-PENGAWAS-DOKUMEN-PENDUKUNG",
        "deskripsi": "Bukti dokumen pendukung sesuai petunjuk instrumen portofolio.",
        "is_scoreable": false,
        "scoring_config": null,
        "urutan": 8,
        "is_active": true,
        "fields": [
            {
                "label": "Bukti Dokumen Pendukung",
                "deskripsi": "Unggah bukti dokumen pendukung yang relevan dengan data portofolio.",
                "nama_field": "bukti_dokumen_pendukung",
                "tipe_field": "file",
                "placeholder": null,
                "bantuan": "Format: PDF, DOC, DOCX, PNG, JPG/JPEG. Unggah satu dokumen utama, maksimal 10 MB.",
                "opsi_field": {
                    "accept": [
                        "application/pdf",
                        "application/msword",
                        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                        "image/png",
                        "image/jpeg"
                    ],
                    "max_size_kb": 10240,
                    "max_files": 1
                },
                "nilai_default": null,
                "validasi": {
                    "required": false,
                    "mimes": [
                        "pdf",
                        "doc",
                        "docx",
                        "png",
                        "jpg",
                        "jpeg"
                    ],
                    "max": 10240
                },
                "scoring_config": null,
                "urutan": 1,
                "is_required": false,
                "is_active": true
            }
        ]
    }
]
JSON;

        return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
    }

    private function caseStudyFormData(): array
    {
        $payload = <<<'JSON'
[
    {
        "judul_form": "STUDI KASUS 1 (KEPRIBADIAN — INTEGRITAS & REFLEKSI)",
        "kode_form": "FORM-PENGAWAS-STUDI-KASUS-01",
        "deskripsi": "Fokus: KEPRIBADIAN – INTEGRITAS & REFLEKSI.\n\nKasus:\nSeorang kepala sekolah dampingan Anda diketahui melakukan manipulasi data laporan capaian pembelajaran untuk memenuhi target kinerja. Hal ini terungkap dari hasil supervisi dan perbandingan data lapangan. Kepala sekolah tersebut beralasan bahwa tekanan dari pihak eksternal membuatnya harus menyesuaikan laporan.",
        "kompetensi": "kepribadian",
        "indikator_kode": "SK-01",
        "indikator_label": "Kepribadian – Integritas & Refleksi",
        "is_scoreable": true,
        "scoring_config": {
            "profile": "studi_kasus",
            "weight": 100,
            "advanced_rules": {
                "task_count": 4,
                "task_weight": 25
            }
        },
        "urutan": 1,
        "is_active": true,
        "fields": [
            {
                "label": "1. Sikap profesional dan integritas yang harus ditunjukkan",
                "deskripsi": "Sikap profesional dan integritas yang harus ditunjukkan",
                "nama_field": "kasus_1_tugas_1",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 1,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "2. Pendekatan emosional dan etis dalam menyikapi kasus",
                "deskripsi": "Pendekatan emosional dan etis dalam menyikapi kasus",
                "nama_field": "kasus_1_tugas_2",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 2,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "3. Strategi pembinaan yang berorientasi pada perbaikan",
                "deskripsi": "Strategi pembinaan yang berorientasi pada perbaikan",
                "nama_field": "kasus_1_tugas_3",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 3,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "4. Upaya refleksi diri dalam menjalankan peran pengawas",
                "deskripsi": "Upaya refleksi diri dalam menjalankan peran pengawas",
                "nama_field": "kasus_1_tugas_4",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 4,
                "is_required": true,
                "is_active": true
            }
        ]
    },
    {
        "judul_form": "STUDI KASUS 2 (SOSIAL — KOLABORASI & PEMANGKU KEPENTINGAN)",
        "kode_form": "FORM-PENGAWAS-STUDI-KASUS-02",
        "deskripsi": "Fokus: SOSIAL – KOLABORASI & PEMANGKU KEPENTINGAN.\n\nKasus:\nDi salah satu sekolah dampingan, program peningkatan literasi tidak berjalan optimal karena kurangnya dukungan dari orang tua dan masyarakat sekitar. Kepala sekolah merasa kesulitan membangun kemitraan yang efektif.",
        "kompetensi": "sosial",
        "indikator_kode": "SK-02",
        "indikator_label": "Sosial – Kolaborasi & Pemangku Kepentingan",
        "is_scoreable": true,
        "scoring_config": {
            "profile": "studi_kasus",
            "weight": 100,
            "advanced_rules": {
                "task_count": 4,
                "task_weight": 25
            }
        },
        "urutan": 2,
        "is_active": true,
        "fields": [
            {
                "label": "1. Upaya membangun kolaborasi dengan pemangku kepentingan",
                "deskripsi": "Upaya membangun kolaborasi dengan pemangku kepentingan",
                "nama_field": "kasus_2_tugas_1",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 1,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "2. Strategi komunikasi yang efektif",
                "deskripsi": "Strategi komunikasi yang efektif",
                "nama_field": "kasus_2_tugas_2",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 2,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "3. Bentuk keterlibatan masyarakat dalam program sekolah",
                "deskripsi": "Bentuk keterlibatan masyarakat dalam program sekolah",
                "nama_field": "kasus_2_tugas_3",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 3,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "4. Mekanisme monitoring dan evaluasi kolaborasi",
                "deskripsi": "Mekanisme monitoring dan evaluasi kolaborasi",
                "nama_field": "kasus_2_tugas_4",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 4,
                "is_required": true,
                "is_active": true
            }
        ]
    },
    {
        "judul_form": "STUDI KASUS 3 (PROFESIONAL — PENGEMBANGAN DIRI KEPALA SEKOLAH)",
        "kode_form": "FORM-PENGAWAS-STUDI-KASUS-03",
        "deskripsi": "Fokus: PROFESIONAL – PENGEMBANGAN DIRI KEPALA SEKOLAH.\n\nKasus:\nSeorang kepala sekolah dampingan memiliki kemampuan manajerial yang cukup baik, namun lemah dalam kepemimpinan pembelajaran. Hal ini berdampak pada rendahnya kualitas pembelajaran di kelas.",
        "kompetensi": "profesional",
        "indikator_kode": "SK-03",
        "indikator_label": "Profesional – Pengembangan Diri Kepala Sekolah",
        "is_scoreable": true,
        "scoring_config": {
            "profile": "studi_kasus",
            "weight": 100,
            "advanced_rules": {
                "task_count": 4,
                "task_weight": 25
            }
        },
        "urutan": 3,
        "is_active": true,
        "fields": [
            {
                "label": "1. Identifikasi kebutuhan pengembangan diri",
                "deskripsi": "Identifikasi kebutuhan pengembangan diri",
                "nama_field": "kasus_3_tugas_1",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 1,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "2. Penyusunan rencana pengembangan diri (RPD)",
                "deskripsi": "Penyusunan rencana pengembangan diri (RPD)",
                "nama_field": "kasus_3_tugas_2",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 2,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "3. Strategi implementasi pendampingan",
                "deskripsi": "Strategi implementasi pendampingan",
                "nama_field": "kasus_3_tugas_3",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 3,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "4. Indikator keberhasilan pengembangan",
                "deskripsi": "Indikator keberhasilan pengembangan",
                "nama_field": "kasus_3_tugas_4",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 4,
                "is_required": true,
                "is_active": true
            }
        ]
    },
    {
        "judul_form": "STUDI KASUS 4 (PROFESIONAL — PENGEMBANGAN SATUAN PENDIDIKAN)",
        "kode_form": "FORM-PENGAWAS-STUDI-KASUS-04",
        "deskripsi": "Fokus: PROFESIONAL – PENGEMBANGAN SATUAN PENDIDIKAN.\n\nKasus:\nProfil satuan pendidikan menunjukkan bahwa capaian numerasi siswa rendah. Program yang telah dirancang sebelumnya belum memberikan dampak signifikan.",
        "kompetensi": "profesional",
        "indikator_kode": "SK-04",
        "indikator_label": "Profesional – Pengembangan Satuan Pendidikan",
        "is_scoreable": true,
        "scoring_config": {
            "profile": "studi_kasus",
            "weight": 100,
            "advanced_rules": {
                "task_count": 4,
                "task_weight": 25
            }
        },
        "urutan": 4,
        "is_active": true,
        "fields": [
            {
                "label": "1. Analisis berbasis data profil satuan pendidikan",
                "deskripsi": "Analisis berbasis data profil satuan pendidikan",
                "nama_field": "kasus_4_tugas_1",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 1,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "2. Perencanaan program yang lebih tepat sasaran",
                "deskripsi": "Perencanaan program yang lebih tepat sasaran",
                "nama_field": "kasus_4_tugas_2",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 2,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "3. Strategi implementasi program",
                "deskripsi": "Strategi implementasi program",
                "nama_field": "kasus_4_tugas_3",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 3,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "4. Sistem monitoring dan evaluasi berkelanjutan",
                "deskripsi": "Sistem monitoring dan evaluasi berkelanjutan",
                "nama_field": "kasus_4_tugas_4",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 4,
                "is_required": true,
                "is_active": true
            }
        ]
    },
    {
        "judul_form": "STUDI KASUS 5 (PROFESIONAL — PEMANFAATAN TEKNOLOGI & KEBIJAKAN)",
        "kode_form": "FORM-PENGAWAS-STUDI-KASUS-05",
        "deskripsi": "Fokus: PROFESIONAL – PEMANFAATAN TEKNOLOGI & KEBIJAKAN.\n\nKasus:\nKementerian mengeluarkan kebijakan baru terkait penggunaan platform digital dalam pembelajaran. Namun, sebagian besar guru di sekolah dampingan belum mampu memanfaatkan teknologi secara optimal.",
        "kompetensi": "profesional",
        "indikator_kode": "SK-05",
        "indikator_label": "Profesional – Pemanfaatan Teknologi & Kebijakan",
        "is_scoreable": true,
        "scoring_config": {
            "profile": "studi_kasus",
            "weight": 100,
            "advanced_rules": {
                "task_count": 4,
                "task_weight": 25
            }
        },
        "urutan": 5,
        "is_active": true,
        "fields": [
            {
                "label": "1. Kajian kebijakan dan relevansinya dengan kondisi sekolah",
                "deskripsi": "Kajian kebijakan dan relevansinya dengan kondisi sekolah",
                "nama_field": "kasus_5_tugas_1",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 1,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "2. Strategi implementasi berbasis teknologi",
                "deskripsi": "Strategi implementasi berbasis teknologi",
                "nama_field": "kasus_5_tugas_2",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 2,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "3. Penguatan kapasitas guru dan kepala sekolah",
                "deskripsi": "Penguatan kapasitas guru dan kepala sekolah",
                "nama_field": "kasus_5_tugas_3",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 3,
                "is_required": true,
                "is_active": true
            },
            {
                "label": "4. Pengembangan sistem monitoring berbasis digital",
                "deskripsi": "Pengembangan sistem monitoring berbasis digital",
                "nama_field": "kasus_5_tugas_4",
                "tipe_field": "textarea",
                "placeholder": "Tuliskan analisis dan rencana Anda secara sistematis.",
                "bantuan": "Jelaskan langkah konkret, pertimbangan profesional/etis, pihak yang terlibat, dan tindak lanjut yang dapat dipantau.",
                "opsi_field": null,
                "nilai_default": null,
                "validasi": {
                    "required": true,
                    "min_length": 30
                },
                "scoring_config": {
                    "enabled": true,
                    "profile": "studi_kasus",
                    "method": "semantic_similarity",
                    "weight": 25,
                    "scale_min": 1,
                    "scale_max": 5
                },
                "urutan": 4,
                "is_required": true,
                "is_active": true
            }
        ]
    }
]
JSON;

        return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
    }

    private function multipleChoiceQuestions(): array
    {
        $payload = <<<'JSON'
[
    {
        "source_number": 1,
        "hots": "C4",
        "competency": "kepribadian",
        "sub_code": "1.1.1",
        "sub_label": "Makna, tujuan, dan pandangan hidup berdasarkan prinsip moral dan keyakinan terhadap Tuhan Yang Maha Esa dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Seorang pengawas sekolah menjalankan tugas supervisi dengan berupaya memahami perannya sebagai bagian dari pengabdian profesional yang dilandasi nilai moral dan keyakinan terhadap Tuhan Yang Maha Esa.",
        "prompt": "Cara pengawas memaknai perannya tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi peran pengawasan sebagai amanah moral yang perlu dijalankan secara bertanggung jawab",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan hubungan antara tugas kepengawasan dengan nilai moral dan keyakinan dalam kehidupan profesional",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan pelaksanaan supervisi dengan tujuan hidup yang berorientasi pada nilai-nilai kebaikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi nilai moral dalam setiap keputusan pembinaan yang dilakukan secara konsisten",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan nilai spiritual sebagai landasan utama dalam membimbing dan menginspirasi warga sekolah",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 2,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.1.1",
        "sub_label": "Makna, tujuan, dan pandangan hidup berdasarkan prinsip moral dan keyakinan terhadap Tuhan Yang Maha Esa dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Pengawas sekolah menghadapi dilema dalam pengambilan keputusan pembinaan guru, sehingga perlu mempertimbangkan nilai moral dan keyakinan dalam menentukan langkah yang tepat.",
        "prompt": "Pendekatan yang mencerminkan penerapan nilai moral dan keyakinan adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan kesadaran bahwa setiap keputusan memiliki konsekuensi moral dalam pelaksanaan tugas",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mempertimbangkan nilai moral sebagai dasar dalam memilih alternatif keputusan pembinaan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menggunakan prinsip keyakinan sebagai rujukan dalam menilai dampak keputusan terhadap guru",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyeimbangkan nilai moral, profesional, dan kebutuhan sekolah dalam pengambilan keputusan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan keputusan pembinaan sebagai bagian dari tanggung jawab spiritual dan profesional secara utuh",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 3,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.1.1",
        "sub_label": "Makna, tujuan, dan pandangan hidup berdasarkan prinsip moral dan keyakinan terhadap Tuhan Yang Maha Esa dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap praktik kepengawasan yang telah dilaksanakan untuk memastikan kesesuaian antara tindakan dengan nilai moral dan keyakinan yang dianut.",
        "prompt": "Bentuk refleksi yang paling menunjukkan kedalaman pemaknaan adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman pengawasan yang telah dilakukan dalam perspektif nilai moral",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menelaah kesesuaian antara tindakan pengawasan dengan prinsip moral yang diyakini",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi keputusan pembinaan berdasarkan nilai spiritual yang dianut secara pribadi",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan praktik kepengawasan berdasarkan refleksi nilai moral dan keyakinan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mendorong praktik refleksi kolektif dalam komunitas pengawas berbasis nilai moral dan spiritual",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 4,
        "hots": "C6",
        "competency": "kepribadian",
        "sub_code": "1.1.1",
        "sub_label": "Makna, tujuan, dan pandangan hidup berdasarkan prinsip moral dan keyakinan terhadap Tuhan Yang Maha Esa dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Pengawas sekolah telah menunjukkan kematangan moral dan spiritual yang kuat dalam menjalankan tugas, serta menjadi rujukan dalam praktik kepengawasan di wilayah binaannya.",
        "prompt": "Strategi yang mencerminkan pengembangan pandangan hidup berbasis nilai moral adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam perilaku pengawasan yang selaras dengan nilai moral yang diyakini",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan praktik pembinaan guru yang berorientasi pada nilai-nilai kebaikan dan kemanusiaan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan nilai spiritual dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi pengawas lain dalam memahami dan menerapkan nilai moral dalam tugas kepengawasan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi budaya kepengawasan berbasis nilai moral dan keyakinan dalam komunitas profesional",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 5,
        "hots": "C4",
        "competency": "kepribadian",
        "sub_code": "1.1.2",
        "sub_label": "Pengelolaan emosi dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Seorang pengawas sekolah menghadapi situasi supervisi di mana guru menunjukkan resistensi terhadap masukan yang diberikan. Pengawas tetap berupaya menjaga sikap profesional dalam merespons kondisi tersebut.",
        "prompt": "Cara pengawas mengelola emosinya dalam situasi tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Menyadari respons emosional pribadi saat menghadapi situasi supervisi yang menantang",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengendalikan ekspresi emosi agar tetap selaras dengan peran profesional sebagai pengawas",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan respons emosional dengan situasi komunikasi yang terjadi selama supervisi",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menunjukkan kestabilan emosi dalam memberikan pembinaan kepada guru secara konsisten",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menggunakan pengelolaan emosi sebagai sarana membangun hubungan pembinaan yang positif",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 6,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.1.2",
        "sub_label": "Pengelolaan emosi dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Dalam menjalankan tugas pembinaan, pengawas sekolah dihadapkan pada berbagai karakter guru yang memerlukan pendekatan emosional yang berbeda.",
        "prompt": "Pendekatan pengelolaan emosi yang paling mencerminkan kompetensi pengawas adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali variasi emosi yang muncul dalam interaksi dengan guru di berbagai situasi",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengatur respons emosional agar tetap objektif dalam proses pembinaan guru",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menggunakan empati dalam memahami kondisi emosional guru saat pembinaan berlangsung",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengelola keseimbangan emosi dalam mengambil keputusan pembinaan yang tepat",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan pengelolaan emosi dalam membangun hubungan profesional yang konstruktif",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 7,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.1.2",
        "sub_label": "Pengelolaan emosi dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap pengalaman dalam mengelola emosi ketika menghadapi berbagai dinamika di sekolah binaan.",
        "prompt": "Bentuk refleksi yang menunjukkan pengelolaan emosi secara optimal adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman emosional selama menjalankan tugas kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis faktor yang mempengaruhi munculnya emosi dalam situasi tertentu",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi respons emosional berdasarkan prinsip profesional dan nilai diri",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan strategi pengelolaan emosi untuk meningkatkan kualitas pembinaan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi praktik refleksi bersama terkait pengelolaan emosi dalam komunitas pengawas",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 8,
        "hots": "C6",
        "competency": "kepribadian",
        "sub_code": "1.1.2",
        "sub_label": "Pengelolaan emosi dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Pengawas sekolah telah menunjukkan kematangan emosi yang tinggi dan menjadi rujukan dalam membina hubungan profesional di lingkungan kerja.",
        "prompt": "Strategi yang mencerminkan pengelolaan emosi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam menjaga kestabilan emosi selama menjalankan tugas kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan pembinaan yang mempertimbangkan aspek emosional guru",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan pengelolaan emosi dalam sistem kerja dan interaksi profesional secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi pengawas lain dalam mengembangkan kemampuan pengelolaan emosi secara profesional",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun budaya kepengawasan yang menempatkan pengelolaan emosi sebagai bagian dari profesionalisme",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 9,
        "hots": "C4",
        "competency": "kepribadian",
        "sub_code": "1.1.3",
        "sub_label": "Penerapan kode etik dalam menjalankan tugas dan peran sebagai pengawas sekolah.",
        "stimulus": "Seorang pengawas sekolah melaksanakan supervisi dengan berpedoman pada kode etik profesi, serta berupaya menjaga sikap profesional dalam setiap interaksi dengan warga sekolah.",
        "prompt": "Bentuk penerapan kode etik dalam situasi tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi prinsip kode etik sebagai pedoman dalam menjalankan tugas kepengawasan secara profesional",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara kode etik dengan tanggung jawab pengawas dalam membina satuan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan penerapan kode etik dengan perilaku profesional dalam interaksi dengan guru dan kepala sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi prinsip kode etik dalam setiap pengambilan keputusan selama proses supervisi berlangsung",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan nilai kode etik sebagai dasar dalam membangun budaya profesional di lingkungan binaan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 10,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.1.3",
        "sub_label": "Penerapan kode etik dalam menjalankan tugas dan peran sebagai pengawas sekolah.",
        "stimulus": "Dalam menjalankan tugas, pengawas sekolah menghadapi berbagai situasi yang menuntut keputusan profesional yang sesuai dengan kode etik.",
        "prompt": "Pendekatan yang menunjukkan penerapan kode etik secara tepat adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali nilai-nilai dalam kode etik sebagai dasar dalam menjalankan tugas kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan prinsip kode etik dalam mempertimbangkan alternatif keputusan pembinaan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan penerapan kode etik dengan konteks permasalahan yang dihadapi di sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyeimbangkan kode etik dengan aspek profesional dan kebutuhan satuan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan pengambilan keputusan berdasarkan kode etik sebagai landasan profesional dan moral secara utuh",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 11,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.1.3",
        "sub_label": "Penerapan kode etik dalam menjalankan tugas dan peran sebagai pengawas sekolah.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap praktik penerapan kode etik dalam pelaksanaan tugas kepengawasan.",
        "prompt": "Bentuk refleksi yang menunjukkan kedalaman penerapan kode etik adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman dalam menerapkan kode etik selama menjalankan tugas kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis kesesuaian antara tindakan pengawasan dengan prinsip kode etik yang berlaku",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi keputusan pembinaan berdasarkan standar kode etik profesi pengawas",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan praktik kepengawasan berdasarkan refleksi penerapan kode etik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi refleksi kolektif tentang penerapan kode etik dalam komunitas pengawas sekolah",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 12,
        "hots": "C6",
        "competency": "kepribadian",
        "sub_code": "1.1.3",
        "sub_label": "Penerapan kode etik dalam menjalankan tugas dan peran sebagai pengawas sekolah.",
        "stimulus": "Pengawas sekolah telah menunjukkan konsistensi dalam menerapkan kode etik serta menjadi rujukan dalam praktik kepengawasan di wilayah binaannya.",
        "prompt": "Strategi yang mencerminkan penerapan kode etik pada tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam perilaku kepengawasan yang sesuai dengan prinsip kode etik profesi",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan praktik pembinaan yang berlandaskan nilai-nilai dalam kode etik secara berkelanjutan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan kode etik dalam sistem kerja dan interaksi profesional di lingkungan binaan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi pengawas lain dalam memahami dan menerapkan kode etik secara profesional",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun budaya kepengawasan yang menjadikan kode etik sebagai landasan utama dalam praktik profesional",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 13,
        "hots": "C4",
        "competency": "kepribadian",
        "sub_code": "1.2.1",
        "sub_label": "Refleksi untuk perencanaan pengembangan diri dalam peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Seorang pengawas sekolah melakukan refleksi terhadap kegiatan supervisi yang telah dilaksanakan untuk memahami kekuatan dan area pengembangan dalam meningkatkan mutu layanan satuan pendidikan.",
        "prompt": "Bentuk refleksi yang mencerminkan pengembangan diri tersebut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi pengalaman pelaksanaan supervisi sebagai dasar pemahaman terhadap praktik kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara hasil supervisi dengan peningkatan mutu layanan pendidikan di sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan hasil refleksi dengan kebutuhan pengembangan kompetensi dalam menjalankan tugas pengawasan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi hasil refleksi sebagai dasar dalam memperbaiki kualitas pembinaan kepada satuan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan hasil refleksi dalam merancang strategi peningkatan mutu layanan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 14,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.2.1",
        "sub_label": "Refleksi untuk perencanaan pengembangan diri dalam peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi secara berkala untuk merencanakan pengembangan diri dalam meningkatkan kualitas layanan pendidikan yang berpusat pada peserta didik.",
        "prompt": "Pendekatan refleksi yang paling mencerminkan pengembangan diri adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali hasil pelaksanaan tugas sebagai dasar dalam memahami kebutuhan pengembangan diri",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan hasil refleksi untuk merumuskan langkah perbaikan dalam praktik kepengawasan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan rencana pengembangan diri dengan hasil refleksi terhadap kebutuhan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan refleksi dengan tujuan peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan hasil refleksi sebagai landasan dalam perencanaan pengembangan diri yang berkelanjutan dan sistematis",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 15,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.2.1",
        "sub_label": "Refleksi untuk perencanaan pengembangan diri dalam peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi mendalam terhadap praktik kepengawasan dengan tujuan meningkatkan kualitas pembinaan dan layanan pendidikan.",
        "prompt": "Bentuk refleksi yang menunjukkan kedalaman pengembangan diri adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman pengawasan sebagai bahan refleksi dalam menjalankan tugas kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis hasil refleksi untuk memahami faktor yang mempengaruhi kualitas layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi praktik kepengawasan berdasarkan hasil refleksi yang berorientasi pada peserta didik",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan rencana tindak lanjut berdasarkan refleksi untuk meningkatkan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi refleksi kolaboratif dalam komunitas pengawas untuk memperkuat pengembangan diri secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 16,
        "hots": "C6",
        "competency": "kepribadian",
        "sub_code": "1.2.1",
        "sub_label": "Refleksi untuk perencanaan pengembangan diri dalam peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah membudayakan refleksi sebagai bagian dari pengembangan diri dan peningkatan mutu layanan pendidikan di wilayah binaannya.",
        "prompt": "Strategi yang mencerminkan refleksi pada tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam melakukan refleksi untuk meningkatkan kualitas pelaksanaan tugas kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan praktik refleksi sebagai dasar dalam perbaikan berkelanjutan terhadap layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan refleksi dalam sistem kerja kepengawasan yang berorientasi pada peningkatan mutu pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi pengawas lain dalam melakukan refleksi sebagai bagian dari pengembangan profesional",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun budaya refleksi yang berkelanjutan dalam komunitas kepengawasan untuk peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 17,
        "hots": "C4",
        "competency": "kepribadian",
        "sub_code": "1.2.2",
        "sub_label": "Cara adaptif melakukan pengembangan diri untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Seorang pengawas sekolah menghadapi perubahan kebijakan pembelajaran yang menuntut penyesuaian dalam praktik kepengawasan agar tetap relevan dengan kebutuhan peserta didik.",
        "prompt": "Cara adaptif pengawas dalam mengembangkan diri tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi perubahan kebijakan sebagai dasar untuk memahami kebutuhan pengembangan diri dalam kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara perubahan kebijakan dengan peningkatan mutu layanan pendidikan di satuan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan praktik kepengawasan berdasarkan kebutuhan yang muncul dari perubahan kebijakan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi perubahan sebagai bagian dari penguatan kompetensi dalam menjalankan tugas kepengawasan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan penyesuaian diri dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 18,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.2.2",
        "sub_label": "Cara adaptif melakukan pengembangan diri untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah berupaya meningkatkan kompetensi dirinya dengan menyesuaikan pendekatan pembinaan sesuai dengan dinamika dan kebutuhan satuan pendidikan.",
        "prompt": "Pendekatan adaptif dalam pengembangan diri yang paling tepat adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali kebutuhan perubahan sebagai dasar dalam merancang pengembangan diri secara berkelanjutan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan pengalaman sebagai referensi dalam menyesuaikan pendekatan pembinaan yang dilakukan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan strategi kepengawasan dengan karakteristik dan kebutuhan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan pengembangan diri dengan tujuan peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan pengembangan diri sebagai proses adaptif yang terencana untuk meningkatkan kualitas pembinaan secara menyeluruh",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 19,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.2.2",
        "sub_label": "Cara adaptif melakukan pengembangan diri untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan evaluasi terhadap praktik kepengawasan untuk menyesuaikan strategi pembinaan agar lebih efektif dalam meningkatkan mutu layanan pendidikan.",
        "prompt": "Bentuk pengembangan diri adaptif yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman kepengawasan sebagai dasar dalam melakukan penyesuaian pendekatan pembinaan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis kebutuhan perubahan dalam praktik kepengawasan berdasarkan hasil evaluasi yang dilakukan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas strategi pembinaan dalam meningkatkan mutu layanan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan strategi kepengawasan baru berdasarkan hasil refleksi dan evaluasi yang dilakukan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi praktik berbagi pengalaman adaptasi dalam komunitas pengawas untuk memperkuat pembelajaran bersama",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 20,
        "hots": "C6",
        "competency": "kepribadian",
        "sub_code": "1.2.2",
        "sub_label": "Cara adaptif melakukan pengembangan diri untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah menunjukkan kemampuan adaptif dalam pengembangan diri dan menjadi rujukan dalam meningkatkan mutu layanan pendidikan di wilayah binaannya.",
        "prompt": "Strategi pengembangan diri adaptif yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam menyesuaikan diri terhadap perubahan dalam praktik kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan pembinaan yang responsif terhadap kebutuhan satuan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan pengembangan diri adaptif dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi pengawas lain dalam mengembangkan kemampuan adaptif dalam menjalankan tugas kepengawasan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun budaya adaptif dalam komunitas kepengawasan untuk meningkatkan mutu layanan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 21,
        "hots": "C4",
        "competency": "kepribadian",
        "sub_code": "1.2.3",
        "sub_label": "Penerapan hasil pengembangan diri untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah mengikuti berbagai kegiatan pengembangan diri dan mulai memanfaatkan hasil pembelajaran tersebut dalam pelaksanaan tugas kepengawasan.",
        "prompt": "Penerapan hasil pengembangan diri tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi hasil pengembangan diri sebagai dasar dalam memahami peningkatan kualitas kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara hasil pengembangan diri dengan peningkatan mutu layanan satuan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menggunakan hasil pengembangan diri dalam menyesuaikan praktik pembinaan di satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi hasil pengembangan diri sebagai bagian dari peningkatan kualitas pelaksanaan tugas kepengawasan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan hasil pengembangan diri dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 22,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.2.3",
        "sub_label": "Penerapan hasil pengembangan diri untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah berupaya menerapkan hasil pelatihan dan refleksi dalam meningkatkan kualitas pembinaan kepada guru dan satuan pendidikan.",
        "prompt": "Pendekatan penerapan hasil pengembangan diri yang paling tepat adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali hasil pembelajaran sebagai dasar dalam merencanakan peningkatan kompetensi diri",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan hasil pengembangan diri untuk memperbaiki praktik pembinaan yang dilakukan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan penerapan hasil pengembangan diri dengan kebutuhan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan penerapan hasil pengembangan diri dengan tujuan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan penerapan hasil pengembangan diri sebagai bagian dari proses peningkatan mutu layanan yang berpusat pada peserta didik secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 23,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.2.3",
        "sub_label": "Penerapan hasil pengembangan diri untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan evaluasi terhadap dampak penerapan hasil pengembangan diri dalam praktik kepengawasan untuk meningkatkan mutu layanan pendidikan.",
        "prompt": "Bentuk penerapan yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan penerapan hasil pengembangan diri dalam pelaksanaan tugas kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis dampak penerapan hasil pengembangan diri terhadap kualitas layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas penerapan hasil pengembangan diri dalam meningkatkan mutu pembinaan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan praktik kepengawasan berdasarkan hasil evaluasi penerapan pengembangan diri",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi praktik berbagi pengalaman penerapan hasil pengembangan diri dalam komunitas pengawas",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 24,
        "hots": "C6",
        "competency": "kepribadian",
        "sub_code": "1.2.3",
        "sub_label": "Penerapan hasil pengembangan diri untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah mampu menerapkan hasil pengembangan diri secara konsisten dan menjadi rujukan dalam peningkatan mutu layanan pendidikan di wilayah binaannya.",
        "prompt": "Strategi penerapan hasil pengembangan diri pada tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam menerapkan hasil pengembangan diri dalam praktik kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan pembinaan berbasis hasil pengembangan diri secara berkelanjutan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan hasil pengembangan diri dalam sistem kerja kepengawasan yang berorientasi mutu",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi pengawas lain dalam menerapkan hasil pengembangan diri secara profesional",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun budaya penerapan hasil pengembangan diri dalam komunitas kepengawasan untuk peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 25,
        "hots": "C4",
        "competency": "kepribadian",
        "sub_code": "1.3.1",
        "sub_label": "Empati terhadap peserta didik dalam pengambilan keputusan pendampingan kepada kepala sekolah.",
        "stimulus": "Pengawas sekolah memberikan pendampingan kepada kepala sekolah dalam menangani permasalahan pembelajaran yang berdampak pada peserta didik.",
        "prompt": "Empati terhadap peserta didik dalam pengambilan keputusan tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi kondisi peserta didik sebagai dasar dalam memahami permasalahan pembelajaran di sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara keputusan kepala sekolah dengan dampaknya terhadap perkembangan peserta didik",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan kebutuhan peserta didik dengan pertimbangan dalam memberikan rekomendasi kepada kepala sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi kepentingan peserta didik sebagai dasar dalam proses pendampingan kepada kepala sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan perspektif peserta didik dalam merancang keputusan pendampingan yang berorientasi pada keberpihakan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 26,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.3.1",
        "sub_label": "Empati terhadap peserta didik dalam pengambilan keputusan pendampingan kepada kepala sekolah.",
        "stimulus": "Dalam proses pembinaan, pengawas sekolah dihadapkan pada berbagai keputusan strategis yang berdampak langsung terhadap pengalaman belajar peserta didik.",
        "prompt": "Pendekatan empatik dalam pengambilan keputusan yang paling tepat adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali kebutuhan peserta didik sebagai dasar dalam memahami konteks pengambilan keputusan di sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan perspektif peserta didik dalam mempertimbangkan alternatif keputusan pembinaan yang dilakukan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan rekomendasi kepada kepala sekolah dengan kondisi dan kebutuhan peserta didik yang beragam",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan keputusan pendampingan dengan prinsip keberpihakan pada kepentingan peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan pengambilan keputusan sebagai upaya memastikan layanan pendidikan yang berpihak pada peserta didik secara menyeluruh",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 27,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.3.1",
        "sub_label": "Empati terhadap peserta didik dalam pengambilan keputusan pendampingan kepada kepala sekolah.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap keputusan pendampingan yang telah diberikan kepada kepala sekolah, khususnya terkait dampaknya terhadap peserta didik.",
        "prompt": "Bentuk empati yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman pendampingan yang berkaitan dengan kebutuhan peserta didik di sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis dampak keputusan pendampingan terhadap perkembangan peserta didik secara menyeluruh",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi kesesuaian keputusan pembinaan dengan prinsip keberpihakan pada peserta didik",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi pendampingan berdasarkan kebutuhan peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi diskusi reflektif bersama kepala sekolah tentang keberpihakan keputusan terhadap peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 28,
        "hots": "C6",
        "competency": "kepribadian",
        "sub_code": "1.3.1",
        "sub_label": "Empati terhadap peserta didik dalam pengambilan keputusan pendampingan kepada kepala sekolah.",
        "stimulus": "Pengawas sekolah telah menunjukkan empati yang tinggi terhadap peserta didik dan menjadi rujukan dalam pengambilan keputusan pendampingan di wilayah binaannya.",
        "prompt": "Strategi yang mencerminkan empati tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam mempertimbangkan kebutuhan peserta didik dalam setiap keputusan pendampingan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan pembinaan yang menempatkan peserta didik sebagai pusat dalam pengambilan keputusan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan perspektif peserta didik dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam mengembangkan keputusan yang berpihak pada peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun budaya kepemimpinan pendidikan yang berorientasi pada kepentingan peserta didik dalam setiap praktik pembinaan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 29,
        "hots": "C4",
        "competency": "kepribadian",
        "sub_code": "1.3.2",
        "sub_label": "Respek terhadap hak peserta didik dalam pendampingan kepada kepala sekolah.",
        "stimulus": "Pengawas sekolah memberikan pendampingan kepada kepala sekolah dalam memastikan kebijakan sekolah menghargai hak-hak peserta didik.",
        "prompt": "Respek terhadap hak peserta didik tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi hak peserta didik sebagai dasar dalam memahami kebijakan yang diterapkan di sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara kebijakan sekolah dengan pemenuhan hak peserta didik",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan pemenuhan hak peserta didik dengan rekomendasi pendampingan kepada kepala sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi penghormatan terhadap hak peserta didik dalam proses pembinaan satuan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan pemenuhan hak peserta didik dalam setiap keputusan pendampingan yang berorientasi pada keberpihakan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 30,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.3.2",
        "sub_label": "Respek terhadap hak peserta didik dalam pendampingan kepada kepala sekolah.",
        "stimulus": "Dalam proses pembinaan, pengawas sekolah mempertimbangkan berbagai kebijakan yang berdampak pada pemenuhan hak peserta didik di satuan pendidikan.",
        "prompt": "Pendekatan yang mencerminkan respek terhadap hak peserta didik adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali berbagai hak peserta didik sebagai dasar dalam memahami kebijakan pendidikan di sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan prinsip pemenuhan hak peserta didik dalam mempertimbangkan keputusan pembinaan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan rekomendasi kepada kepala sekolah dengan kondisi pemenuhan hak peserta didik",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan kebijakan sekolah dengan prinsip penghormatan terhadap hak peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan keputusan pendampingan sebagai upaya menjamin terpenuhinya hak peserta didik secara menyeluruh",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 31,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.3.2",
        "sub_label": "Respek terhadap hak peserta didik dalam pendampingan kepada kepala sekolah.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap keputusan pendampingan yang telah diberikan, khususnya terkait pemenuhan hak peserta didik.",
        "prompt": "Bentuk respek yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman pendampingan yang berkaitan dengan pemenuhan hak peserta didik",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis dampak kebijakan sekolah terhadap pemenuhan hak peserta didik",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi kesesuaian keputusan pembinaan dengan prinsip penghormatan terhadap hak peserta didik",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi pendampingan untuk memastikan hak peserta didik terpenuhi",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi diskusi reflektif bersama kepala sekolah terkait pemenuhan hak peserta didik secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 32,
        "hots": "C6",
        "competency": "kepribadian",
        "sub_code": "1.3.2",
        "sub_label": "Respek terhadap hak peserta didik dalam pendampingan kepada kepala sekolah.",
        "stimulus": "Pengawas sekolah telah menunjukkan komitmen tinggi dalam menghormati hak peserta didik dan menjadi rujukan dalam praktik kepengawasan di wilayah binaannya.",
        "prompt": "Strategi yang mencerminkan respek tingkat lanjut terhadap hak peserta didik adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam mempertimbangkan hak peserta didik dalam setiap keputusan pendampingan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan pembinaan yang menempatkan hak peserta didik sebagai prioritas utama",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan prinsip penghormatan terhadap hak peserta didik dalam sistem kerja kepengawasan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam mengembangkan kebijakan yang menjamin pemenuhan hak peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun budaya sekolah yang menjunjung tinggi penghormatan terhadap hak peserta didik dalam setiap praktik pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 33,
        "hots": "C4",
        "competency": "kepribadian",
        "sub_code": "1.3.3",
        "sub_label": "Kepedulian terhadap keselamatan dan keamanan peserta didik sebagai individu dan kelompok dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Pengawas sekolah memberikan pendampingan kepada kepala sekolah dalam memastikan lingkungan belajar yang aman bagi peserta didik.",
        "prompt": "Kepedulian terhadap keselamatan peserta didik tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi kondisi keselamatan peserta didik sebagai dasar dalam memahami situasi lingkungan sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara lingkungan sekolah dengan keamanan dan kenyamanan peserta didik",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan aspek keselamatan peserta didik dengan rekomendasi pembinaan kepada kepala sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi pentingnya keselamatan peserta didik dalam proses pendampingan satuan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan aspek keselamatan peserta didik dalam setiap keputusan pendampingan yang berorientasi perlindungan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 34,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.3.3",
        "sub_label": "Kepedulian terhadap keselamatan dan keamanan peserta didik sebagai individu dan kelompok dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Pengawas sekolah menghadapi berbagai kondisi sekolah dengan tingkat keamanan yang berbeda dalam mendukung proses pembelajaran.",
        "prompt": "Pendekatan yang mencerminkan kepedulian terhadap keselamatan peserta didik adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali potensi risiko yang dapat mempengaruhi keselamatan peserta didik di lingkungan sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan prinsip keselamatan peserta didik dalam mempertimbangkan keputusan pembinaan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan rekomendasi dengan kondisi keamanan yang dihadapi oleh peserta didik di sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan kebijakan sekolah dengan prinsip perlindungan terhadap keselamatan peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan keputusan pendampingan sebagai upaya menjamin keselamatan peserta didik secara menyeluruh",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 35,
        "hots": "C5",
        "competency": "kepribadian",
        "sub_code": "1.3.3",
        "sub_label": "Kepedulian terhadap keselamatan dan keamanan peserta didik sebagai individu dan kelompok dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap kebijakan dan praktik sekolah yang berkaitan dengan keselamatan peserta didik.",
        "prompt": "Bentuk kepedulian yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan kondisi keselamatan peserta didik berdasarkan pengalaman kepengawasan di sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis faktor yang mempengaruhi keselamatan peserta didik dalam lingkungan sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi kebijakan sekolah dalam menjamin keamanan dan keselamatan peserta didik",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan strategi perbaikan untuk meningkatkan keselamatan peserta didik di satuan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi diskusi kolaboratif dengan kepala sekolah terkait penguatan budaya keselamatan peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 36,
        "hots": "C6",
        "competency": "kepribadian",
        "sub_code": "1.3.3",
        "sub_label": "Kepedulian terhadap keselamatan dan keamanan peserta didik sebagai individu dan kelompok dalam menjalankan peran sebagai pengawas sekolah.",
        "stimulus": "Pengawas sekolah telah menunjukkan kepedulian tinggi terhadap keselamatan peserta didik dan menjadi rujukan dalam pembinaan sekolah binaan.",
        "prompt": "Strategi yang mencerminkan kepedulian tingkat lanjut terhadap keselamatan peserta didik adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam memperhatikan keselamatan peserta didik dalam setiap keputusan pendampingan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan pembinaan yang mengutamakan keselamatan peserta didik dalam proses pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan prinsip keselamatan peserta didik dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam mengembangkan kebijakan yang mendukung keselamatan peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun budaya sekolah yang menjadikan keselamatan peserta didik sebagai prioritas utama dalam seluruh praktik pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 37,
        "hots": "C4",
        "competency": "sosial",
        "sub_code": "2.1.1",
        "sub_label": "Komunikasi efektif dengan kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan komunikasi dengan kepala sekolah dalam memberikan pendampingan untuk meningkatkan mutu layanan pendidikan.",
        "prompt": "Komunikasi efektif dalam situasi tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi kebutuhan komunikasi sebagai dasar dalam memahami kondisi satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan tujuan komunikasi dalam mendukung peningkatan mutu layanan pendidikan di sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan pesan komunikasi dengan kebutuhan kepala sekolah dalam pengembangan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi prinsip komunikasi efektif dalam setiap interaksi dengan kepala sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan komunikasi efektif dalam strategi pendampingan untuk peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 38,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.1.1",
        "sub_label": "Komunikasi efektif dengan kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Dalam proses pembinaan, pengawas sekolah berinteraksi dengan kepala sekolah untuk menyampaikan rekomendasi yang berdampak pada peningkatan mutu layanan pendidikan.",
        "prompt": "Pendekatan komunikasi yang mencerminkan efektivitas adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali karakteristik kepala sekolah sebagai dasar dalam membangun komunikasi yang tepat",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan teknik komunikasi yang jelas dalam menyampaikan rekomendasi pembinaan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan gaya komunikasi dengan situasi dan kebutuhan kepala sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan komunikasi dengan tujuan peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan komunikasi sebagai sarana membangun pemahaman bersama untuk peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 39,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.1.1",
        "sub_label": "Komunikasi efektif dengan kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap komunikasi yang telah dilakukan dengan kepala sekolah dalam proses pendampingan.",
        "prompt": "Bentuk komunikasi yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman komunikasi yang dilakukan dalam proses pembinaan kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis efektivitas komunikasi dalam mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi kesesuaian strategi komunikasi dengan kebutuhan kepala sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan komunikasi untuk meningkatkan kualitas pendampingan kepada kepala sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi komunikasi reflektif bersama kepala sekolah untuk memperkuat kolaborasi peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 40,
        "hots": "C6",
        "competency": "sosial",
        "sub_code": "2.1.1",
        "sub_label": "Komunikasi efektif dengan kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah menunjukkan kemampuan komunikasi yang efektif dan menjadi rujukan dalam membangun kolaborasi dengan kepala sekolah.",
        "prompt": "Strategi komunikasi yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam membangun komunikasi yang terbuka dengan kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan komunikasi yang mendukung kolaborasi dalam peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan komunikasi efektif dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam mengembangkan komunikasi yang mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun budaya komunikasi kolaboratif antara pengawas dan kepala sekolah untuk peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 37,
        "hots": "C4",
        "competency": "sosial",
        "sub_code": "2.1.2",
        "sub_label": "Kerja sama dengan seluruh kepala sekolah dampingan dan rekan sejawat untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah menjalin kerja sama dengan kepala sekolah dampingan dan rekan sejawat dalam meningkatkan mutu layanan pendidikan di wilayah binaannya.",
        "prompt": "Kerja sama tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi kebutuhan kerja sama sebagai dasar dalam memahami kondisi satuan pendidikan binaan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan peran kerja sama dalam mendukung peningkatan mutu layanan pendidikan di sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan kerja sama dengan kepala sekolah dan rekan sejawat dalam pelaksanaan pembinaan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi pentingnya kerja sama dalam mendukung keberhasilan program kepengawasan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan kerja sama dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 38,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.1.2",
        "sub_label": "Kerja sama dengan seluruh kepala sekolah dampingan dan rekan sejawat untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan koordinasi dengan kepala sekolah dampingan dan rekan sejawat untuk merancang program peningkatan mutu layanan pendidikan.",
        "prompt": "Pendekatan kerja sama yang mencerminkan kompetensi sosial adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali potensi kolaborasi sebagai dasar dalam membangun kerja sama antar pemangku kepentingan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan komunikasi dan koordinasi dalam menjalin kerja sama dengan kepala sekolah dan rekan sejawat",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan bentuk kerja sama dengan kebutuhan dan karakteristik satuan pendidikan binaan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan kerja sama dengan tujuan peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan kerja sama sebagai upaya membangun sinergi untuk peningkatan mutu layanan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 39,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.1.2",
        "sub_label": "Kerja sama dengan seluruh kepala sekolah dampingan dan rekan sejawat untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap efektivitas kerja sama yang telah dilakukan dengan kepala sekolah dampingan dan rekan sejawat.",
        "prompt": "Bentuk kerja sama yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman kerja sama dalam pelaksanaan pembinaan di satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis efektivitas kerja sama dalam meningkatkan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi kesesuaian strategi kerja sama dengan kebutuhan satuan pendidikan binaan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi kerja sama untuk meningkatkan kualitas pembinaan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi kolaborasi reflektif antar kepala sekolah dan rekan sejawat untuk penguatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 40,
        "hots": "C6",
        "competency": "sosial",
        "sub_code": "2.1.2",
        "sub_label": "Kerja sama dengan seluruh kepala sekolah dampingan dan rekan sejawat untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah menunjukkan kemampuan kerja sama yang kuat dan menjadi rujukan dalam membangun kolaborasi antar satuan pendidikan di wilayah binaannya.",
        "prompt": "Strategi kerja sama yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam membangun kerja sama dengan kepala sekolah dan rekan sejawat",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan kolaboratif dalam mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan kerja sama dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dan rekan sejawat dalam mengembangkan kerja sama profesional",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun budaya kolaborasi antar satuan pendidikan untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 41,
        "hots": "C4",
        "competency": "sosial",
        "sub_code": "2.2.1",
        "sub_label": "Pelibatan pemangku kepentingan dalam pendampingan kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah mendampingi kepala sekolah dalam meningkatkan mutu layanan pendidikan dengan melibatkan berbagai pemangku kepentingan.",
        "prompt": "Pelibatan pemangku kepentingan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi peran pemangku kepentingan sebagai dasar dalam memahami dukungan terhadap satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara pemangku kepentingan dengan peningkatan mutu layanan pendidikan di sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan pelibatan pemangku kepentingan dengan kebutuhan pengembangan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi pentingnya peran pemangku kepentingan dalam proses pendampingan kepada kepala sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan pelibatan pemangku kepentingan dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 42,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.2.1",
        "sub_label": "Pelibatan pemangku kepentingan dalam pendampingan kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah berupaya melibatkan berbagai pihak, seperti orang tua, masyarakat, dan instansi terkait dalam mendukung peningkatan mutu layanan pendidikan.",
        "prompt": "Pendekatan pelibatan pemangku kepentingan yang paling tepat adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali potensi kontribusi pemangku kepentingan sebagai dasar dalam membangun keterlibatan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan komunikasi dan koordinasi dalam melibatkan pemangku kepentingan secara efektif",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan bentuk pelibatan dengan kebutuhan dan karakteristik satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan pelibatan pemangku kepentingan dengan tujuan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan pelibatan pemangku kepentingan sebagai upaya membangun dukungan berkelanjutan bagi peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 43,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.2.1",
        "sub_label": "Pelibatan pemangku kepentingan dalam pendampingan kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap keterlibatan pemangku kepentingan dalam pendampingan kepala sekolah untuk meningkatkan mutu layanan pendidikan.",
        "prompt": "Bentuk pelibatan yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman pelibatan pemangku kepentingan dalam proses pendampingan kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis efektivitas keterlibatan pemangku kepentingan dalam meningkatkan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi kesesuaian strategi pelibatan dengan kebutuhan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi pelibatan pemangku kepentingan untuk meningkatkan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi kolaborasi reflektif antar pemangku kepentingan untuk memperkuat peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 44,
        "hots": "C6",
        "competency": "sosial",
        "sub_code": "2.2.1",
        "sub_label": "Pelibatan pemangku kepentingan dalam pendampingan kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah mampu melibatkan pemangku kepentingan secara optimal dan menjadi rujukan dalam membangun kemitraan pendidikan di wilayah binaannya.",
        "prompt": "Strategi pelibatan pemangku kepentingan pada tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam melibatkan pemangku kepentingan dalam pendampingan kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan kemitraan yang mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan pelibatan pemangku kepentingan dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam membangun kemitraan dengan pemangku kepentingan secara profesional",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun ekosistem kolaboratif antara sekolah dan pemangku kepentingan untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 45,
        "hots": "C4",
        "competency": "sosial",
        "sub_code": "2.2.2",
        "sub_label": "Berkoordinasi secara berkala dengan pemangku kepentingan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan koordinasi dengan pemangku kepentingan dalam mendukung peningkatan mutu layanan pendidikan di satuan pendidikan binaan.",
        "prompt": "Koordinasi tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi kebutuhan koordinasi sebagai dasar dalam memahami peran pemangku kepentingan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan tujuan koordinasi dalam mendukung peningkatan mutu layanan pendidikan di sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan koordinasi dengan pemangku kepentingan dalam pelaksanaan pendampingan kepala sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi pentingnya koordinasi dalam mendukung keberhasilan program kepengawasan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan koordinasi dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 46,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.2.2",
        "sub_label": "Berkoordinasi secara berkala dengan pemangku kepentingan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan koordinasi secara berkala dengan berbagai pemangku kepentingan untuk memastikan keberlanjutan program peningkatan mutu layanan pendidikan.",
        "prompt": "Pendekatan koordinasi yang mencerminkan kompetensi sosial adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali kebutuhan koordinasi sebagai dasar dalam membangun hubungan dengan pemangku kepentingan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan komunikasi yang terarah dalam melaksanakan koordinasi secara berkala",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan pola koordinasi dengan kebutuhan dan dinamika satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan koordinasi dengan tujuan peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan koordinasi sebagai upaya membangun kesinambungan program peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 47,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.2.2",
        "sub_label": "Berkoordinasi secara berkala dengan pemangku kepentingan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap efektivitas koordinasi yang telah dilakukan dengan pemangku kepentingan dalam mendukung peningkatan mutu layanan pendidikan.",
        "prompt": "Bentuk koordinasi yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman koordinasi dalam pelaksanaan pendampingan kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis efektivitas koordinasi dalam mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi kesesuaian strategi koordinasi dengan kebutuhan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan pola koordinasi untuk meningkatkan kualitas pendampingan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi koordinasi reflektif bersama pemangku kepentingan untuk penguatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 48,
        "hots": "C6",
        "competency": "sosial",
        "sub_code": "2.2.2",
        "sub_label": "Berkoordinasi secara berkala dengan pemangku kepentingan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah mampu membangun koordinasi yang efektif dan berkelanjutan dengan pemangku kepentingan serta menjadi rujukan dalam praktik kepengawasan.",
        "prompt": "Strategi koordinasi yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam melakukan koordinasi dengan pemangku kepentingan secara berkala",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan koordinasi yang mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan koordinasi dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi pemangku kepentingan dalam memperkuat koordinasi untuk peningkatan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun sistem koordinasi kolaboratif yang berkelanjutan antar pemangku kepentingan untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 49,
        "hots": "C4",
        "competency": "sosial",
        "sub_code": "2.3.1",
        "sub_label": "Berpartisipasi aktif dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah terlibat dalam kegiatan organisasi profesi dan jejaring pendidikan untuk mendukung peningkatan mutu layanan pendidikan di wilayah binaannya.",
        "prompt": "Partisipasi tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi peran organisasi profesi sebagai sarana pengembangan kompetensi kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara partisipasi dalam jejaring dengan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan keterlibatan dalam organisasi profesi dengan pelaksanaan tugas kepengawasan di sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi pentingnya partisipasi dalam organisasi profesi sebagai bagian dari pengembangan diri",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan partisipasi dalam organisasi profesi dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 50,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.3.1",
        "sub_label": "Berpartisipasi aktif dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah aktif mengikuti kegiatan organisasi profesi dan jejaring untuk meningkatkan kualitas pembinaan kepada satuan pendidikan.",
        "prompt": "Pendekatan partisipasi yang mencerminkan kompetensi sosial adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali peluang keterlibatan dalam organisasi profesi sebagai dasar dalam pengembangan kompetensi",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan hasil partisipasi untuk mendukung peningkatan kualitas pembinaan di satuan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan bentuk keterlibatan dengan kebutuhan pengembangan satuan pendidikan binaan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan partisipasi dalam jejaring dengan tujuan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan partisipasi sebagai upaya membangun kontribusi nyata dalam peningkatan mutu layanan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 51,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.3.1",
        "sub_label": "Berpartisipasi aktif dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap keterlibatannya dalam organisasi profesi dan jejaring dalam mendukung peningkatan mutu layanan pendidikan.",
        "prompt": "Bentuk partisipasi yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman keterlibatan dalam organisasi profesi dan jejaring pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis manfaat partisipasi dalam jejaring terhadap peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas keterlibatan dalam organisasi profesi terhadap praktik kepengawasan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan strategi peningkatan kontribusi dalam organisasi profesi dan jejaring pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi kolaborasi dalam jejaring profesi untuk memperkuat peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 52,
        "hots": "C6",
        "competency": "sosial",
        "sub_code": "2.3.1",
        "sub_label": "Berpartisipasi aktif dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah menunjukkan peran aktif dalam organisasi profesi dan jejaring serta menjadi rujukan dalam pengembangan praktik kepengawasan di wilayah binaannya.",
        "prompt": "Strategi partisipasi yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam berpartisipasi dalam organisasi profesi dan jejaring pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan kontribusi dalam organisasi profesi untuk mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan keterlibatan dalam jejaring dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi pengawas lain dalam meningkatkan partisipasi dalam organisasi profesi dan jejaring",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun jejaring profesional yang kuat untuk mendorong peningkatan mutu layanan pendidikan yang berpusat pada peserta didik secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 53,
        "hots": "C4",
        "competency": "sosial",
        "sub_code": "2.3.2",
        "sub_label": "Berbagi praktik baik dan karya pendampingan kepada kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah membagikan pengalaman pendampingan kepada kepala sekolah dalam forum profesional untuk mendukung peningkatan mutu layanan pendidikan.",
        "prompt": "Berbagi praktik baik tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi pengalaman pendampingan sebagai bahan dalam berbagi praktik kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara praktik pendampingan dengan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan pengalaman pendampingan dengan kebutuhan pengembangan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi praktik baik sebagai bagian dari penguatan kompetensi kepengawasan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan praktik baik dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 54,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.3.2",
        "sub_label": "Berbagi praktik baik dan karya pendampingan kepada kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah secara aktif berbagi praktik baik dan hasil karya pendampingan dalam kegiatan organisasi profesi dan jejaring pendidikan.",
        "prompt": "Pendekatan berbagi praktik baik yang mencerminkan kompetensi sosial adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali praktik baik sebagai dasar dalam pengembangan kompetensi profesional",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan hasil praktik pendampingan untuk mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan bentuk berbagi praktik baik dengan kebutuhan satuan pendidikan binaan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan praktik baik dengan tujuan peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan berbagi praktik baik sebagai upaya memperluas dampak peningkatan mutu layanan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 55,
        "hots": "C5",
        "competency": "sosial",
        "sub_code": "2.3.2",
        "sub_label": "Berbagi praktik baik dan karya pendampingan kepada kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap kegiatan berbagi praktik baik yang telah dilakukan dalam mendukung peningkatan mutu layanan pendidikan.",
        "prompt": "Bentuk berbagi praktik baik yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pengalaman berbagi praktik baik dalam forum profesional kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis manfaat berbagi praktik baik terhadap peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas praktik baik dalam mendukung pembinaan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi berbagi praktik baik untuk meningkatkan kualitas pembinaan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi kolaborasi berbagi praktik baik dalam jejaring profesional untuk memperkuat mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 56,
        "hots": "C6",
        "competency": "sosial",
        "sub_code": "2.3.2",
        "sub_label": "Berbagi praktik baik dan karya pendampingan kepada kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah menunjukkan peran aktif dalam berbagi praktik baik dan menjadi rujukan dalam pengembangan mutu layanan pendidikan di wilayah binaannya.",
        "prompt": "Strategi berbagi praktik baik yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam berbagi praktik baik dalam forum profesional kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan berbagi praktik baik untuk mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan praktik baik dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi pengawas lain dalam mengembangkan dan berbagi praktik baik secara profesional",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun ekosistem berbagi praktik baik dalam jejaring profesional untuk meningkatkan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 57,
        "hots": "C4",
        "competency": "profesional",
        "sub_code": "3.1.1",
        "sub_label": "Pendampingan kepada kepala sekolah dalam mengidentifikasi kebutuhan pengembangan diri kepala sekolah.",
        "stimulus": "Pengawas sekolah melakukan pendampingan kepada kepala sekolah untuk mengidentifikasi kebutuhan pengembangan diri dalam meningkatkan mutu layanan pendidikan.",
        "prompt": "Pendampingan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi kebutuhan pengembangan diri kepala sekolah sebagai dasar dalam memahami kondisi kepemimpinan satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara kebutuhan pengembangan diri dengan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan kebutuhan pengembangan diri kepala sekolah dengan hasil supervisi dan evaluasi kinerja",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi hasil identifikasi sebagai dasar dalam merancang pendampingan kepada kepala sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan hasil identifikasi kebutuhan dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 58,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.1.1",
        "sub_label": "Pendampingan kepada kepala sekolah dalam mengidentifikasi kebutuhan pengembangan diri kepala sekolah.",
        "stimulus": "Pengawas sekolah melakukan analisis terhadap kebutuhan pengembangan diri kepala sekolah berdasarkan hasil supervisi dan kondisi satuan pendidikan.",
        "prompt": "Pendekatan identifikasi kebutuhan yang mencerminkan kompetensi profesional adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali kebutuhan pengembangan diri sebagai dasar dalam memahami kapasitas kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan data hasil supervisi dalam mengidentifikasi kebutuhan pengembangan diri kepala sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan identifikasi kebutuhan dengan kondisi dan tantangan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan kebutuhan pengembangan diri dengan tujuan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan identifikasi kebutuhan sebagai dasar dalam merancang pengembangan diri kepala sekolah",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 59,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.1.1",
        "sub_label": "Pendampingan kepada kepala sekolah dalam mengidentifikasi kebutuhan pengembangan diri kepala sekolah.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap proses identifikasi kebutuhan pengembangan diri kepala sekolah dalam meningkatkan mutu layanan pendidikan.",
        "prompt": "Bentuk pendampingan yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan proses identifikasi kebutuhan pengembangan diri kepala sekolah dalam praktik kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis ketepatan identifikasi kebutuhan pengembangan diri berdasarkan kondisi satuan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi kesesuaian kebutuhan pengembangan diri dengan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi identifikasi kebutuhan pengembangan diri kepala sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi kolaborasi reflektif dengan kepala sekolah dalam mengidentifikasi kebutuhan pengembangan diri",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 60,
        "hots": "C6",
        "competency": "profesional",
        "sub_code": "3.1.1",
        "sub_label": "Pendampingan kepada kepala sekolah dalam mengidentifikasi kebutuhan pengembangan diri kepala sekolah.",
        "stimulus": "Pengawas sekolah telah menunjukkan kemampuan dalam mengidentifikasi kebutuhan pengembangan diri kepala sekolah secara komprehensif dan menjadi rujukan dalam praktik kepengawasan.",
        "prompt": "Strategi pendampingan yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam mengidentifikasi kebutuhan pengembangan diri kepala sekolah secara berkelanjutan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan identifikasi kebutuhan yang mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan identifikasi kebutuhan dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam mengidentifikasi kebutuhan pengembangan diri secara mandiri dan reflektif",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun sistem pengembangan diri kepala sekolah berbasis kebutuhan untuk meningkatkan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 61,
        "hots": "C4",
        "competency": "profesional",
        "sub_code": "3.1.2",
        "sub_label": "Pendampingan kepada kepala sekolah untuk menyusun rencana pengembangan diri.",
        "stimulus": "Pengawas sekolah mendampingi kepala sekolah dalam menyusun rencana pengembangan diri berdasarkan hasil identifikasi kebutuhan untuk meningkatkan mutu layanan pendidikan.",
        "prompt": "Pendampingan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi komponen rencana pengembangan diri sebagai dasar dalam memahami kebutuhan kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara rencana pengembangan diri dengan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan rencana pengembangan diri dengan hasil identifikasi kebutuhan kepala sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi penyusunan rencana pengembangan diri sebagai bagian dari peningkatan kualitas kepemimpinan sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan rencana pengembangan diri dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 62,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.1.2",
        "sub_label": "Pendampingan kepada kepala sekolah untuk menyusun rencana pengembangan diri.",
        "stimulus": "Pengawas sekolah membantu kepala sekolah dalam menyusun rencana pengembangan diri yang sesuai dengan kebutuhan dan kondisi satuan pendidikan.",
        "prompt": "Pendekatan penyusunan rencana yang mencerminkan kompetensi profesional adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali kebutuhan pengembangan diri sebagai dasar dalam menyusun rencana pengembangan kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan data hasil identifikasi dalam menyusun rencana pengembangan diri kepala sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan rencana pengembangan diri dengan kondisi dan tantangan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan rencana pengembangan diri dengan tujuan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan penyusunan rencana pengembangan diri sebagai bagian dari upaya peningkatan mutu layanan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 63,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.1.2",
        "sub_label": "Pendampingan kepada kepala sekolah untuk menyusun rencana pengembangan diri.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap proses pendampingan dalam penyusunan rencana pengembangan diri kepala sekolah.",
        "prompt": "Bentuk pendampingan yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan proses penyusunan rencana pengembangan diri dalam praktik kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis kesesuaian rencana pengembangan diri dengan kebutuhan kepala sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas rencana pengembangan diri dalam meningkatkan mutu layanan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi penyusunan rencana pengembangan diri kepala sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi kolaborasi reflektif dengan kepala sekolah dalam menyusun rencana pengembangan diri secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 64,
        "hots": "C6",
        "competency": "profesional",
        "sub_code": "3.1.2",
        "sub_label": "Pendampingan kepada kepala sekolah untuk menyusun rencana pengembangan diri.",
        "stimulus": "Pengawas sekolah telah menunjukkan kemampuan dalam mendampingi kepala sekolah menyusun rencana pengembangan diri secara sistematis dan menjadi rujukan dalam praktik kepengawasan.",
        "prompt": "Strategi pendampingan yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam mendampingi penyusunan rencana pengembangan diri kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan penyusunan rencana pengembangan diri yang mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan penyusunan rencana pengembangan diri dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam menyusun rencana pengembangan diri secara mandiri dan reflektif",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun sistem pengembangan diri kepala sekolah berbasis rencana yang berkelanjutan untuk meningkatkan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 65,
        "hots": "C4",
        "competency": "profesional",
        "sub_code": "3.1.3",
        "sub_label": "Pendampingan kepada kepala sekolah dalam melaksanakan pengembangan diri sesuai dengan rencana pengembangan diri.",
        "stimulus": "Pengawas sekolah mendampingi kepala sekolah dalam melaksanakan rencana pengembangan diri yang telah disusun untuk meningkatkan mutu layanan pendidikan.",
        "prompt": "Pendampingan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi langkah pelaksanaan pengembangan diri sebagai dasar dalam memahami proses peningkatan kompetensi kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara pelaksanaan pengembangan diri dengan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan pelaksanaan pengembangan diri dengan rencana yang telah disusun oleh kepala sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi pelaksanaan pengembangan diri sebagai bagian dari peningkatan kualitas kepemimpinan sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan pelaksanaan pengembangan diri dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 66,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.1.3",
        "sub_label": "Pendampingan kepada kepala sekolah dalam melaksanakan pengembangan diri sesuai dengan rencana pengembangan diri.",
        "stimulus": "Pengawas sekolah melakukan pendampingan kepada kepala sekolah dalam melaksanakan pengembangan diri sesuai dengan rencana yang telah ditetapkan.",
        "prompt": "Pendekatan pelaksanaan yang mencerminkan kompetensi profesional adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali tahapan pelaksanaan pengembangan diri sebagai dasar dalam mendampingi kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan rencana pengembangan diri sebagai acuan dalam pelaksanaan kegiatan peningkatan kompetensi",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan pelaksanaan pengembangan diri dengan kondisi dan kebutuhan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan pelaksanaan pengembangan diri dengan tujuan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan pelaksanaan pengembangan diri sebagai proses berkelanjutan untuk peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 67,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.1.3",
        "sub_label": "Pendampingan kepada kepala sekolah dalam melaksanakan pengembangan diri sesuai dengan rencana pengembangan diri.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap pelaksanaan pengembangan diri kepala sekolah untuk memastikan dampaknya terhadap peningkatan mutu layanan pendidikan.",
        "prompt": "Bentuk pendampingan yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pelaksanaan pengembangan diri kepala sekolah dalam praktik kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis kesesuaian pelaksanaan pengembangan diri dengan rencana yang telah disusun",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas pelaksanaan pengembangan diri dalam meningkatkan mutu layanan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi pelaksanaan pengembangan diri kepala sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi refleksi kolaboratif dengan kepala sekolah dalam melaksanakan pengembangan diri secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 68,
        "hots": "C6",
        "competency": "profesional",
        "sub_code": "3.1.3",
        "sub_label": "Pendampingan kepada kepala sekolah dalam melaksanakan pengembangan diri sesuai dengan rencana pengembangan diri.",
        "stimulus": "Pengawas sekolah telah menunjukkan kemampuan dalam mendampingi pelaksanaan pengembangan diri kepala sekolah secara konsisten dan menjadi rujukan dalam praktik kepengawasan.",
        "prompt": "Strategi pendampingan yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam mendampingi pelaksanaan pengembangan diri kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan pelaksanaan pengembangan diri yang mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan pelaksanaan pengembangan diri dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam melaksanakan pengembangan diri secara mandiri dan reflektif",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun sistem pelaksanaan pengembangan diri kepala sekolah yang berkelanjutan untuk meningkatkan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 69,
        "hots": "C4",
        "competency": "profesional",
        "sub_code": "3.2.1",
        "sub_label": "Pemetaaan komitmen perubahan kepala sekolah dampingan, strategi, dan metode pendampingan pada perencanaan pendampingan satuan pendidikan berbasis profil satuan pendidikan untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan pemetaan terhadap komitmen perubahan kepala sekolah dampingan serta profil satuan pendidikan sebagai dasar dalam perencanaan pendampingan.",
        "prompt": "Pemetaan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi komitmen perubahan kepala sekolah sebagai dasar dalam memahami kondisi satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara komitmen perubahan dengan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan profil satuan pendidikan dengan kebutuhan strategi pendampingan kepala sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi hasil pemetaan sebagai dasar dalam perencanaan pendampingan satuan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan hasil pemetaan dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 70,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.2.1",
        "sub_label": "Pemetaaan komitmen perubahan kepala sekolah dampingan, strategi, dan metode pendampingan pada perencanaan pendampingan satuan pendidikan berbasis profil satuan pendidikan untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah menyusun strategi pendampingan berdasarkan hasil pemetaan komitmen perubahan kepala sekolah dan profil satuan pendidikan.",
        "prompt": "Pendekatan perencanaan yang mencerminkan kompetensi profesional adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali komitmen perubahan sebagai dasar dalam merancang strategi pendampingan satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan hasil pemetaan sebagai acuan dalam menentukan metode pendampingan kepala sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan strategi pendampingan dengan kondisi dan karakteristik satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan strategi pendampingan dengan tujuan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan strategi pendampingan sebagai bagian dari upaya perubahan berkelanjutan untuk peningkatan mutu layanan pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 71,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.2.1",
        "sub_label": "Pemetaaan komitmen perubahan kepala sekolah dampingan, strategi, dan metode pendampingan pada perencanaan pendampingan satuan pendidikan berbasis profil satuan pendidikan untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap efektivitas strategi dan metode pendampingan yang telah dirancang berdasarkan pemetaan profil satuan pendidikan.",
        "prompt": "Bentuk pendampingan yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan proses pemetaan dan perencanaan pendampingan satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis kesesuaian strategi pendampingan dengan komitmen perubahan kepala sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas metode pendampingan dalam meningkatkan mutu layanan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi dan metode pendampingan berdasarkan hasil refleksi",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi refleksi kolaboratif dengan kepala sekolah dalam merancang strategi perubahan yang berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 72,
        "hots": "C6",
        "competency": "profesional",
        "sub_code": "3.2.1",
        "sub_label": "Pemetaaan komitmen perubahan kepala sekolah dampingan, strategi, dan metode pendampingan pada perencanaan pendampingan satuan pendidikan berbasis profil satuan pendidikan untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah mampu memetakan komitmen perubahan dan merancang strategi pendampingan berbasis profil satuan pendidikan secara komprehensif.",
        "prompt": "Strategi pendampingan yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam melakukan pemetaan dan perencanaan pendampingan satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan pemetaan dan strategi pendampingan yang adaptif terhadap kebutuhan satuan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan pemetaan dan perencanaan dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam melakukan pemetaan dan perencanaan perubahan secara mandiri dan reflektif",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun sistem pendampingan berbasis profil satuan pendidikan untuk mendorong perubahan berkelanjutan dalam peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 73,
        "hots": "C4",
        "competency": "profesional",
        "sub_code": "3.2.2",
        "sub_label": "Pendampingan kepada kepala sekolah dalam perencanaan program pengembangan satuan pendidikan berbasis profil satuan pendidikan untuk peningkatan mutu layanan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah mendampingi kepala sekolah dalam merencanakan program pengembangan satuan pendidikan berdasarkan profil satuan pendidikan.",
        "prompt": "Pendampingan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi profil satuan pendidikan sebagai dasar dalam memahami kebutuhan pengembangan program sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara program pengembangan dengan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan profil satuan pendidikan dengan perencanaan program pengembangan sekolah",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi perencanaan program sebagai bagian dari peningkatan kualitas satuan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan perencanaan program dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 74,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.2.2",
        "sub_label": "Pendampingan kepada kepala sekolah dalam perencanaan program pengembangan satuan pendidikan berbasis profil satuan pendidikan untuk peningkatan mutu layanan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah membantu kepala sekolah dalam menyusun program pengembangan satuan pendidikan yang sesuai dengan kebutuhan dan kondisi sekolah.",
        "prompt": "Pendekatan perencanaan yang mencerminkan kompetensi profesional adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali kebutuhan pengembangan sebagai dasar dalam menyusun program satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan data profil satuan pendidikan dalam menyusun program pengembangan sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan program pengembangan dengan kondisi dan tantangan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan program pengembangan dengan tujuan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan perencanaan program sebagai bagian dari upaya peningkatan mutu layanan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 75,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.2.2",
        "sub_label": "Pendampingan kepada kepala sekolah dalam perencanaan program pengembangan satuan pendidikan berbasis profil satuan pendidikan untuk peningkatan mutu layanan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap perencanaan program pengembangan satuan pendidikan yang telah disusun bersama kepala sekolah.",
        "prompt": "Bentuk pendampingan yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan proses penyusunan program pengembangan satuan pendidikan dalam praktik kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis kesesuaian program pengembangan dengan profil satuan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas program pengembangan dalam meningkatkan mutu layanan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan program pengembangan berdasarkan hasil refleksi yang dilakukan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi refleksi kolaboratif dengan kepala sekolah dalam menyusun program pengembangan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 76,
        "hots": "C6",
        "competency": "profesional",
        "sub_code": "3.2.2",
        "sub_label": "Pendampingan kepada kepala sekolah dalam perencanaan program pengembangan satuan pendidikan berbasis profil satuan pendidikan untuk peningkatan mutu layanan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah mampu mendampingi kepala sekolah dalam merencanakan program pengembangan satuan pendidikan secara komprehensif dan berkelanjutan.",
        "prompt": "Strategi pendampingan yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam mendampingi perencanaan program pengembangan satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan perencanaan program yang mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan perencanaan program dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam menyusun program pengembangan secara mandiri dan reflektif",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun sistem perencanaan program berbasis profil satuan pendidikan untuk mendorong peningkatan mutu layanan pendidikan yang berpusat pada peserta didik secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 77,
        "hots": "C4",
        "competency": "profesional",
        "sub_code": "3.2.3",
        "sub_label": "Pendampingan kepada kepala sekolah dalam pelaksanaan program pengembangan satuan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah mendampingi kepala sekolah dalam melaksanakan program pengembangan satuan pendidikan yang telah direncanakan untuk meningkatkan mutu layanan pendidikan.",
        "prompt": "Pendampingan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi langkah pelaksanaan program sebagai dasar dalam memahami proses pengembangan satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara pelaksanaan program dengan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan pelaksanaan program dengan rencana pengembangan satuan pendidikan yang telah disusun",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi pelaksanaan program sebagai bagian dari peningkatan kualitas satuan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan pelaksanaan program dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 78,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.2.3",
        "sub_label": "Pendampingan kepada kepala sekolah dalam pelaksanaan program pengembangan satuan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan pendampingan kepada kepala sekolah dalam melaksanakan program pengembangan satuan pendidikan sesuai dengan kondisi dan kebutuhan sekolah.",
        "prompt": "Pendekatan pelaksanaan yang mencerminkan kompetensi profesional adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali tahapan pelaksanaan program sebagai dasar dalam mendampingi kepala sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan rencana program sebagai acuan dalam pelaksanaan pengembangan satuan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan pelaksanaan program dengan kondisi dan kebutuhan satuan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan pelaksanaan program dengan tujuan peningkatan mutu layanan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan pelaksanaan program sebagai bagian dari upaya peningkatan mutu layanan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 79,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.2.3",
        "sub_label": "Pendampingan kepada kepala sekolah dalam pelaksanaan program pengembangan satuan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap pelaksanaan program pengembangan satuan pendidikan yang telah dilakukan bersama kepala sekolah.",
        "prompt": "Bentuk pendampingan yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pelaksanaan program pengembangan satuan pendidikan dalam praktik kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis kesesuaian pelaksanaan program dengan rencana yang telah disusun",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas pelaksanaan program dalam meningkatkan mutu layanan pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi pelaksanaan program pengembangan satuan pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi refleksi kolaboratif dengan kepala sekolah dalam pelaksanaan program secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 80,
        "hots": "C6",
        "competency": "profesional",
        "sub_code": "3.2.3",
        "sub_label": "Pendampingan kepada kepala sekolah dalam pelaksanaan program pengembangan satuan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah menunjukkan kemampuan dalam mendampingi pelaksanaan program pengembangan satuan pendidikan secara konsisten dan menjadi rujukan dalam praktik kepengawasan.",
        "prompt": "Strategi pendampingan yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam mendampingi pelaksanaan program pengembangan satuan pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan pelaksanaan program yang mendukung peningkatan mutu layanan pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan pelaksanaan program dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam melaksanakan program pengembangan secara mandiri dan reflektif",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun sistem pelaksanaan program pengembangan satuan pendidikan yang berkelanjutan untuk meningkatkan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 81,
        "hots": "C4",
        "competency": "profesional",
        "sub_code": "3.3.1",
        "sub_label": "Pendampingan kepada kepala sekolah dalam mengkaji kebijakan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah mendampingi kepala sekolah dalam mengkaji kebijakan pendidikan untuk mendukung peningkatan mutu layanan pendidikan di satuan pendidikan.",
        "prompt": "Pendampingan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi isi kebijakan pendidikan sebagai dasar dalam memahami arah pengembangan satuan Pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara kebijakan pendidikan dengan peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan kebijakan pendidikan dengan kondisi dan kebutuhan satuan Pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi hasil kajian kebijakan sebagai dasar dalam pengambilan keputusan di sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan hasil kajian kebijakan dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 82,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.3.1",
        "sub_label": "Pendampingan kepada kepala sekolah dalam mengkaji kebijakan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah membantu kepala sekolah dalam memahami dan menganalisis kebijakan pendidikan yang relevan dengan pengembangan satuan pendidikan.",
        "prompt": "Pendekatan kajian kebijakan yang mencerminkan kompetensi profesional adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali substansi kebijakan pendidikan sebagai dasar dalam memahami implementasinya di sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan hasil kajian kebijakan sebagai acuan dalam pengambilan keputusan di satuan Pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan implementasi kebijakan dengan kondisi dan kebutuhan satuan Pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan kebijakan pendidikan dengan tujuan peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan kajian kebijakan sebagai bagian dari upaya peningkatan mutu layanan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 83,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.3.1",
        "sub_label": "Pendampingan kepada kepala sekolah dalam mengkaji kebijakan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap proses pendampingan dalam mengkaji kebijakan pendidikan bersama kepala sekolah.",
        "prompt": "Bentuk pendampingan yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan proses kajian kebijakan pendidikan dalam praktik kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis kesesuaian kebijakan pendidikan dengan kebutuhan satuan Pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas implementasi kebijakan dalam meningkatkan mutu layanan Pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi kajian kebijakan pendidikan dalam pendampingan kepala sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi refleksi kolaboratif dengan kepala sekolah dalam mengkaji kebijakan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 84,
        "hots": "C6",
        "competency": "profesional",
        "sub_code": "3.3.1",
        "sub_label": "Pendampingan kepada kepala sekolah dalam mengkaji kebijakan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah menunjukkan kemampuan dalam mendampingi kepala sekolah mengkaji kebijakan pendidikan secara komprehensif dan menjadi rujukan dalam praktik kepengawasan.",
        "prompt": "Strategi pendampingan yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam mendampingi kajian kebijakan pendidikan di satuan Pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan kajian kebijakan yang mendukung peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan kajian kebijakan dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam mengkaji kebijakan pendidikan secara mandiri dan reflektif",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun sistem kajian kebijakan pendidikan berbasis kebutuhan satuan pendidikan untuk meningkatkan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 85,
        "hots": "C4",
        "competency": "profesional",
        "sub_code": "3.3.2",
        "sub_label": "Pendampingan kepada kepala sekolah dalam implementasi kebijakan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah mendampingi kepala sekolah dalam mengimplementasikan kebijakan pendidikan untuk meningkatkan mutu layanan pendidikan di satuan pendidikan.",
        "prompt": "Pendampingan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi langkah implementasi kebijakan sebagai dasar dalam memahami pelaksanaan kebijakan di sekolah",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan keterkaitan antara implementasi kebijakan dengan peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan implementasi kebijakan dengan kondisi dan kebutuhan satuan Pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi implementasi kebijakan sebagai bagian dari peningkatan kualitas pengelolaan satuan Pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan implementasi kebijakan dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 86,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.3.2",
        "sub_label": "Pendampingan kepada kepala sekolah dalam implementasi kebijakan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah membantu kepala sekolah dalam melaksanakan kebijakan pendidikan dengan mempertimbangkan kondisi dan karakteristik satuan pendidikan.",
        "prompt": "Pendekatan implementasi kebijakan yang mencerminkan kompetensi profesional adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali substansi kebijakan sebagai dasar dalam memahami implementasi di satuan Pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan kebijakan sebagai acuan dalam pelaksanaan program di sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan implementasi kebijakan dengan kondisi dan kebutuhan satuan Pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan implementasi kebijakan dengan tujuan peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan implementasi kebijakan sebagai bagian dari upaya peningkatan mutu layanan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 87,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.3.2",
        "sub_label": "Pendampingan kepada kepala sekolah dalam implementasi kebijakan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap implementasi kebijakan pendidikan yang telah dilakukan bersama kepala sekolah dalam meningkatkan mutu layanan pendidikan.",
        "prompt": "Bentuk pendampingan yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan pelaksanaan implementasi kebijakan pendidikan dalam praktik kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis kesesuaian implementasi kebijakan dengan kondisi satuan Pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas implementasi kebijakan dalam meningkatkan mutu layanan Pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi implementasi kebijakan Pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi refleksi kolaboratif dengan kepala sekolah dalam implementasi kebijakan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 88,
        "hots": "C6",
        "competency": "profesional",
        "sub_code": "3.3.2",
        "sub_label": "Pendampingan kepada kepala sekolah dalam implementasi kebijakan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.",
        "stimulus": "Pengawas sekolah telah menunjukkan kemampuan dalam mendampingi implementasi kebijakan pendidikan secara komprehensif dan menjadi rujukan dalam praktik kepengawasan.",
        "prompt": "Strategi pendampingan yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam mendampingi implementasi kebijakan pendidikan di satuan Pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan implementasi kebijakan yang mendukung peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan implementasi kebijakan dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam mengimplementasikan kebijakan secara mandiri dan reflektif",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun sistem implementasi kebijakan pendidikan berbasis kebutuhan satuan pendidikan untuk meningkatkan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 89,
        "hots": "C4",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah memanfaatkan teknologi informasi dalam mendukung pelaksanaan tugas kepengawasan.",
        "prompt": "Pemanfaatan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi jenis teknologi yang dapat digunakan dalam mendukung tugas kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan peran teknologi informasi dalam meningkatkan efektivitas kepengawasan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan penggunaan teknologi dengan kebutuhan pelaksanaan tugas kepengawasan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi pemanfaatan teknologi sebagai bagian dari peningkatan kualitas kinerja pengawas",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan teknologi dalam strategi peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 90,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah menggunakan teknologi informasi dalam kegiatan supervisi dan pendampingan kepala sekolah.",
        "prompt": "Pendekatan pemanfaatan teknologi yang mencerminkan kompetensi profesional adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali fungsi teknologi sebagai sarana pendukung dalam kegiatan kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan teknologi dalam pelaksanaan supervisi dan pendampingan kepala sekolah",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan penggunaan teknologi dengan kebutuhan dan kondisi satuan Pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan pemanfaatan teknologi dengan tujuan peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan penggunaan teknologi sebagai bagian dari peningkatan mutu layanan pendidikan secara berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 91,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah melakukan refleksi terhadap penggunaan teknologi dalam mendukung pelaksanaan tugas kepengawasan.",
        "prompt": "Bentuk pemanfaatan teknologi yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan penggunaan teknologi dalam pelaksanaan tugas kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis efektivitas penggunaan teknologi dalam mendukung supervisi dan pendampingan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi kesesuaian teknologi yang digunakan dengan kebutuhan satuan Pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi pemanfaatan teknologi dalam kepengawasan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi kolaborasi digital dalam pemanfaatan teknologi untuk peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 92,
        "hots": "C6",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah telah mampu memanfaatkan teknologi informasi secara optimal dalam mendukung pelaksanaan tugas kepengawasan.",
        "prompt": "Strategi pemanfaatan teknologi yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam menggunakan teknologi dalam pelaksanaan tugas kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan pemanfaatan teknologi untuk mendukung peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan teknologi dalam sistem kerja kepengawasan secara berkelanjutan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi kepala sekolah dalam menggunakan teknologi untuk peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun sistem kepengawasan berbasis teknologi untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 93,
        "hots": "C4",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah menggunakan platform digital untuk mengelola data hasil supervisi.",
        "prompt": "Pemanfaatan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi platform digital yang dapat digunakan dalam pengelolaan data supervise",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan manfaat penggunaan platform digital dalam pengelolaan data",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan penggunaan platform dengan kebutuhan pengelolaan data supervise",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi penggunaan platform sebagai bagian dari peningkatan kinerja pengawas",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan platform digital dalam sistem pengelolaan data kepengawasan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 94,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah menggunakan teknologi dalam menyusun laporan hasil supervisi.",
        "prompt": "Pendekatan yang mencerminkan kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali teknologi sebagai sarana dalam penyusunan laporan supervisi",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan teknologi dalam penyusunan laporan hasil supervisi",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan penggunaan teknologi dengan kebutuhan pelaporan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan penggunaan teknologi dengan tujuan peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan penggunaan teknologi sebagai bagian dari sistem pelaporan yang berkelanjutan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 95,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah mengembangkan inovasi dalam penggunaan teknologi untuk meningkatkan efektivitas kepengawasan.",
        "prompt": "Bentuk inovasi yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan penggunaan teknologi dalam kegiatan kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis kebutuhan inovasi teknologi dalam kepengawasan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi efektivitas inovasi teknologi dalam meningkatkan mutu layanan Pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan inovasi teknologi dalam pelaksanaan kepengawasan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi kolaborasi digital untuk penguatan inovasi kepengawasan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 96,
        "hots": "C6",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah memanfaatkan teknologi secara sistemik dalam mendukung transformasi digital pendidikan.",
        "prompt": "Strategi yang mencerminkan kompetensi tingkat lanjut adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam penggunaan teknologi dalam kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan pendekatan digital dalam pelaksanaan kepengawasan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan teknologi dalam sistem kerja kepengawasan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi pemanfaatan teknologi oleh kepala sekolah",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun sistem kepengawasan digital berbasis data untuk peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 97,
        "hots": "C4",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah menggunakan media digital dalam komunikasi dengan kepala sekolah.",
        "prompt": "Pemanfaatan tersebut tercermin melalui tindakan berikut…",
        "options": [
            {
                "label": "A",
                "value": "Mengidentifikasi media digital yang dapat digunakan dalam komunikasi professional",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menjelaskan manfaat media digital dalam komunikasi kepengawasan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengaitkan penggunaan media digital dengan kebutuhan komunikasi",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menginternalisasi penggunaan media digital sebagai bagian dari kinerja professional",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengintegrasikan media digital dalam sistem komunikasi kepengawasan",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 98,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah memanfaatkan teknologi untuk mendukung pembelajaran berbasis data.",
        "prompt": "Pendekatan yang mencerminkan kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Mengenali teknologi sebagai alat pendukung pembelajaran berbasis data",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menggunakan teknologi dalam mendukung pembelajaran berbasis data",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Menyesuaikan penggunaan teknologi dengan kebutuhan pembelajaran",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Menyelaraskan teknologi dengan tujuan peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Mengarahkan penggunaan teknologi sebagai bagian dari pengambilan keputusan berbasis data",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    },
    {
        "source_number": 99,
        "hots": "C5",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah mengevaluasi pemanfaatan teknologi dalam peningkatan mutu layanan pendidikan.",
        "prompt": "Bentuk evaluasi yang menunjukkan kedalaman kompetensi adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menggambarkan penggunaan teknologi dalam peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Menganalisis efektivitas teknologi dalam peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengevaluasi kesesuaian teknologi dengan kebutuhan satuan Pendidikan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Mengembangkan perbaikan strategi penggunaan teknologi",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Menginisiasi evaluasi kolaboratif berbasis teknologi dalam peningkatan mutu layanan Pendidikan",
                "level_kompetensi": 4,
                "score": 4
            }
        ]
    },
    {
        "source_number": 100,
        "hots": "C6",
        "competency": "profesional",
        "sub_code": "3.TI",
        "sub_label": "Pemanfaatan Teknologi dan Informasi",
        "stimulus": "Pengawas sekolah telah mampu memanfaatkan teknologi secara komprehensif dalam seluruh aspek kepengawasan.",
        "prompt": "Strategi yang mencerminkan kompetensi tingkat ahli adalah…",
        "options": [
            {
                "label": "A",
                "value": "Menunjukkan konsistensi dalam penggunaan teknologi dalam kepengawasan",
                "level_kompetensi": 1,
                "score": 1
            },
            {
                "label": "B",
                "value": "Mengembangkan inovasi berbasis teknologi dalam kepengawasan",
                "level_kompetensi": 2,
                "score": 2
            },
            {
                "label": "C",
                "value": "Mengintegrasikan teknologi dalam sistem kerja kepengawasan",
                "level_kompetensi": 3,
                "score": 3
            },
            {
                "label": "D",
                "value": "Memfasilitasi penggunaan teknologi oleh seluruh kepala sekolah dampingan",
                "level_kompetensi": 4,
                "score": 4
            },
            {
                "label": "E",
                "value": "Membangun ekosistem kepengawasan digital berbasis data untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik",
                "level_kompetensi": 5,
                "score": 5
            }
        ]
    }
]
JSON;

        return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
    }

}
