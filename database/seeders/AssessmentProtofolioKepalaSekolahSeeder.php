<?php

namespace Database\Seeders;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Enum\KompetensiGuru;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentProtofolioKepalaSekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assessments = [
            [
                'kode_assessment' => 'ASM-KS-PORTOFOLIO-001',
                'judul' => 'Instrumen Portofolio Kompetensi Kepala Sekolah',
                'deskripsi' => 'Instrumen portofolio untuk memetakan identitas, riwayat pendidikan, pengalaman, prestasi, karya, dan refleksi diri kepala sekolah.',
                'petunjuk' => 'Isilah data berikut secara jujur dan lengkap. Sertakan bukti dokumen pendukung pada setiap bagian yang relevan.',
                'instrument_type' => AssessmentInstrumentType::PORTOFOLIO->value,
                'status' => 'publish',
                'is_active' => true,
                'forms' => [
                    [
                        'judul_form' => 'Identitas Responden',
                        'kode_form' => 'FORM-KS-IDENTITAS',
                        'deskripsi' => 'Data identitas dasar responden pada instrumen portofolio kepala sekolah.',
                        'is_scoreable' => false,
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Nama Lengkap',
                                'deskripsi' => 'Tuliskan nama lengkap sesuai identitas resmi.',
                                'nama_field' => 'nama_lengkap',
                                'tipe_field' => 'text',
                                'placeholder' => 'Masukkan nama lengkap',
                                'bantuan' => 'Gunakan nama sesuai data administrasi.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => true,
                                ],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'NIP/NUPTK',
                                'deskripsi' => 'Masukkan Nomor Induk Pegawai atau Nomor Unik Pendidik dan Tenaga Kependidikan.',
                                'nama_field' => 'nip_nuptk',
                                'tipe_field' => 'text',
                                'placeholder' => 'Masukkan NIP atau NUPTK',
                                'bantuan' => 'Isi salah satu nomor identitas kepegawaian yang tersedia.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => true,
                                ],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Pangkat / Golongan',
                                'deskripsi' => 'Tuliskan pangkat atau golongan terakhir, jika ada.',
                                'nama_field' => 'pangkat_golongan',
                                'tipe_field' => 'text',
                                'placeholder' => 'Contoh: Pembina / IV-a',
                                'bantuan' => 'Isi sesuai data kepegawaian terbaru.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => false,
                                ],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 3,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Jabatan (Guru/Kepala Sekolah/GTK lainnya)',
                                'deskripsi' => 'Pilih jabatan utama yang saat ini paling menggambarkan peran Anda.',
                                'nama_field' => 'jabatan',
                                'tipe_field' => 'select',
                                'placeholder' => 'Pilih jabatan',
                                'bantuan' => 'Pilih jabatan yang paling sesuai.',
                                'opsi_field' => [
                                    'Guru',
                                    'Kepala Sekolah',
                                    'GTK lainnya',
                                ],
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => true,
                                ],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 4,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Satuan Pendidikan',
                                'deskripsi' => 'Tuliskan nama satuan pendidikan tempat bertugas.',
                                'nama_field' => 'satuan_pendidikan',
                                'tipe_field' => 'text',
                                'placeholder' => 'Contoh: SMP Negeri 1 Makassar',
                                'bantuan' => 'Isi nama sekolah atau instansi saat ini.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => true,
                                ],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 5,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Kabupaten/Kota',
                                'deskripsi' => 'Tuliskan kabupaten atau kota lokasi satuan pendidikan.',
                                'nama_field' => 'kabupaten_kota',
                                'tipe_field' => 'text',
                                'placeholder' => 'Contoh: Kota Makassar',
                                'bantuan' => 'Gunakan nama wilayah secara lengkap.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => true,
                                ],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 6,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Lama Mengajar (Tahun)',
                                'deskripsi' => 'Masukkan total pengalaman mengajar dalam tahun.',
                                'nama_field' => 'lama_mengajar',
                                'tipe_field' => 'number',
                                'placeholder' => '0',
                                'bantuan' => 'Isi jumlah tahun pengalaman mengajar.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => true,
                                    'min' => 0,
                                ],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 7,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => 'Riwayat Pendidikan Formal',
                        'kode_form' => 'FORM-KS-PENDIDIKAN',
                        'deskripsi' => 'Riwayat pendidikan formal dan sertifikasi profesional kepala sekolah.',
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'indikator_kode' => 'P1',
                        'indikator_label' => 'Kualifikasi akademik dan sertifikasi',
                        'is_scoreable' => true,
                        'urutan' => 2,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Riwayat Pendidikan Formal',
                                'deskripsi' => 'Masukkan data pendidikan S1, sertifikasi, S2, S3, atau pendidikan relevan lainnya.',
                                'nama_field' => 'riwayat_pendidikan_formal',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap jenjang pendidikan atau sertifikasi.',
                                'opsi_field' => [
                                    'min_rows' => 1,
                                    'max_rows' => 10,
                                    'columns' => [
                                        [
                                            'label' => 'Jenjang',
                                            'nama_field' => 'jenjang',
                                            'tipe_field' => 'select',
                                            'opsi_field' => [
                                                'S1',
                                                'Sertifikasi',
                                                'S2',
                                                'S3',
                                                'Lainnya',
                                            ],
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Gelar',
                                            'nama_field' => 'gelar',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: S.Pd., M.Pd.',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Program Studi',
                                            'nama_field' => 'program_studi',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: Manajemen Pendidikan',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Lembaga',
                                            'nama_field' => 'lembaga',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama perguruan tinggi/lembaga',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Tahun Perolehan',
                                            'nama_field' => 'tahun_perolehan',
                                            'tipe_field' => 'number',
                                            'placeholder' => '2024',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => true,
                                ],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => 'Pengalaman Pelatihan yang Relevan dengan Profesi',
                        'kode_form' => 'FORM-KS-PELATIHAN',
                        'deskripsi' => 'Pengalaman pelatihan yang relevan dengan profesi dalam lima tahun terakhir.',
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'indikator_kode' => 'P2',
                        'indikator_label' => 'Pengembangan profesi berkelanjutan',
                        'is_scoreable' => true,
                        'urutan' => 3,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Pengalaman Pelatihan yang Relevan dengan Profesi (5 tahun terakhir)',
                                'deskripsi' => 'Cantumkan pelatihan, workshop, bimtek, seminar, atau program pengembangan profesi dalam lima tahun terakhir.',
                                'nama_field' => 'pengalaman_pelatihan',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap pelatihan yang relevan.',
                                'opsi_field' => [
                                    'min_rows' => 0,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Nama Pelatihan',
                                            'nama_field' => 'nama_pelatihan',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Masukkan nama pelatihan',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Penyelenggara',
                                            'nama_field' => 'penyelenggara',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama penyelenggara',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Tahun',
                                            'nama_field' => 'tahun',
                                            'tipe_field' => 'number',
                                            'placeholder' => '2025',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Durasi (JP)',
                                            'nama_field' => 'durasi_jp',
                                            'tipe_field' => 'number',
                                            'placeholder' => '32',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Dampak terhadap sekolah',
                                            'nama_field' => 'dampak_sekolah',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan dampak pelatihan terhadap sekolah',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Link Google Drive Sertifikat',
                                            'nama_field' => 'link_google_drive_sertifikat',
                                            'tipe_field' => 'url',
                                            'placeholder' => 'https://drive.google.com/...',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => false,
                                ],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => 'Pengalaman Mengajar',
                        'kode_form' => 'FORM-KS-PENGALAMAN-MENGAJAR',
                        'deskripsi' => 'Riwayat pengalaman mengajar responden pada satuan pendidikan atau lembaga terkait.',
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'indikator_kode' => 'P3',
                        'indikator_label' => 'Pengalaman praktik pendidikan',
                        'is_scoreable' => true,
                        'urutan' => 4,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Riwayat Pengalaman Mengajar',
                                'deskripsi' => 'Masukkan pengalaman mengajar yang pernah atau sedang dijalankan.',
                                'nama_field' => 'pengalaman_mengajar',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap pengalaman mengajar.',
                                'opsi_field' => [
                                    'min_rows' => 1,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Pengalaman',
                                            'nama_field' => 'pengalaman',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: Guru Bahasa Indonesia / Guru Mapel / Wali Kelas',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Lembaga',
                                            'nama_field' => 'lembaga',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama sekolah/lembaga',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Tahun',
                                            'nama_field' => 'tahun',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: 2019–2024',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Link Google Drive Sertifikat / SK',
                                            'nama_field' => 'link_google_drive_sertifikat_sk',
                                            'tipe_field' => 'url',
                                            'placeholder' => 'https://drive.google.com/...',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => true,
                                ],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => 'Prestasi / Penghargaan',
                        'kode_form' => 'FORM-KS-PRESTASI',
                        'deskripsi' => 'Prestasi dan penghargaan profesional yang pernah diperoleh responden.',
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'indikator_kode' => 'P4',
                        'indikator_label' => 'Prestasi profesional dan kontribusi',
                        'is_scoreable' => true,
                        'urutan' => 5,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Prestasi / Penghargaan',
                                'deskripsi' => 'Cantumkan prestasi atau penghargaan yang relevan dengan profesi.',
                                'nama_field' => 'prestasi_penghargaan',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap prestasi atau penghargaan.',
                                'opsi_field' => [
                                    'min_rows' => 0,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Nama Prestasi / Penghargaan',
                                            'nama_field' => 'nama_prestasi',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama prestasi atau penghargaan',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Deskripsi Singkat',
                                            'nama_field' => 'deskripsi_singkat',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan prestasi secara singkat',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Dampak terhadap sekolah',
                                            'nama_field' => 'dampak_sekolah',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan dampak terhadap sekolah',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Link Google Drive Sertifikat / SK',
                                            'nama_field' => 'link_google_drive_sertifikat_sk',
                                            'tipe_field' => 'url',
                                            'placeholder' => 'https://drive.google.com/...',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => false,
                                ],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => 'Karya/Inovasi/Best Practice',
                        'kode_form' => 'FORM-KS-KARYA-INOVASI',
                        'deskripsi' => 'Dokumentasi karya, inovasi, atau praktik baik yang dihasilkan responden.',
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'indikator_kode' => 'P5',
                        'indikator_label' => 'Karya, inovasi, dan best practice',
                        'is_scoreable' => true,
                        'urutan' => 6,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Karya / Inovasi / Best Practice',
                                'deskripsi' => 'Masukkan karya, inovasi, atau best practice yang pernah diterapkan.',
                                'nama_field' => 'karya_inovasi_best_practice',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap karya, inovasi, atau praktik baik.',
                                'opsi_field' => [
                                    'min_rows' => 0,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Judul Karya',
                                            'nama_field' => 'judul_karya',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Masukkan judul karya atau inovasi',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Deskripsi Singkat',
                                            'nama_field' => 'deskripsi_singkat',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan karya atau inovasi',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Dampak terhadap sekolah',
                                            'nama_field' => 'dampak_sekolah',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan dampak terhadap sekolah',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => false,
                                ],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => 'Refleksi Diri',
                        'kode_form' => 'FORM-KS-REFLEKSI-DIRI',
                        'deskripsi' => 'Refleksi responden mengenai kekuatan dan area pengembangan sebagai kepala sekolah.',
                        'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                        'indikator_kode' => 'P6',
                        'indikator_label' => 'Refleksi dan pengembangan diri',
                        'is_scoreable' => true,
                        'urutan' => 7,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Kekuatan dan Area Pengembangan Diri',
                                'deskripsi' => 'Tuliskan kekuatan dan area pengembangan Anda sebagai kepala sekolah.',
                                'nama_field' => 'refleksi_diri',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan kekuatan, tantangan, dan kebutuhan pengembangan diri Anda.',
                                'bantuan' => 'Jelaskan secara reflektif berdasarkan pengalaman kepemimpinan sekolah dan kebutuhan pengembangan profesional.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => true,
                                    'min_length' => 100,
                                ],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
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
                    $fieldData['scoring_config'] = $this->fieldScoringConfig($formData, $fieldData, $fieldIndex);
                    $form->fields()->create($fieldData);
                }
            }
        }
    }

    private function assessmentScoringConfig(): array
    {
        return [
            'profile' => AssessmentInstrumentType::PORTOFOLIO->value,
            'weight' => AssessmentInstrumentType::PORTOFOLIO->weight(),
            'verification_gap_threshold' => 1.5,
            'advanced_rules' => [
                'overall_formula' => 'Skor aspek = (level / 5) x bobot. Total portofolio = jumlah skor seluruh aspek.',
                'competency_source_map' => [
                    [
                        'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                        'sources' => ['P6'],
                    ],
                    [
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'sources' => ['P1', 'P2', 'P3', 'P4', 'P5'],
                    ],
                ],
                'verification_note' => 'Dokumen pendukung tetap memerlukan verifikasi assessor atas keabsahan, kelengkapan, dan keterlacakan bukti.',
            ],
        ];
    }

    private function formScoringConfig(array $formData): ?array
    {
        if (! ($formData['is_scoreable'] ?? false)) {
            return null;
        }

        $config = $this->formRubricMap()[$formData['kode_form'] ?? ''] ?? [
            'rubric_code' => null,
            'weight' => 10,
            'exclude_from_competency' => false,
            'aspect_name' => 'Aspek portofolio umum',
            'accepted_evidence' => [],
            'scoring_note' => null,
        ];

        return [
            'profile' => 'portofolio',
            'weight' => $config['weight'],
            'exclude_from_competency' => $config['exclude_from_competency'],
            'advanced_rules' => [
                'rubric_code' => $config['rubric_code'],
                'aspect_name' => $config['aspect_name'],
                'accepted_evidence' => $config['accepted_evidence'],
                'scoring_note' => $config['scoring_note'],
                'competency_target' => $formData['kompetensi'] ?? null,
                'form_formula' => 'Level form dihasilkan dari auto scoring field utama, lalu dikonversi ke skala 1-5 sesuai rubrik portofolio.',
            ],
        ];
    }

    private function fieldScoringConfig(array $formData, array $fieldData, int $fieldIndex): ?array
    {
        if (! ($formData['is_scoreable'] ?? false)) {
            return null;
        }

        $formCode = $formData['kode_form'] ?? '';
        $baseConfig = [
            'enabled' => true,
            'profile' => 'portofolio',
            'weight' => 100,
            'rubric_code' => data_get($this->formScoringConfig($formData), 'advanced_rules.rubric_code'),
            'scale_min' => 1,
            'scale_max' => 5,
        ];

        $library = $this->fieldRubricLibrary();

        if (isset($library[$formCode])) {
            return array_merge($baseConfig, $library[$formCode]);
        }

        return array_merge($baseConfig, [
            'method' => ($fieldData['tipe_field'] ?? '') === 'textarea' ? 'semantic_similarity' : 'repeater_completeness',
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function formRubricMap(): array
    {
        return [
            'FORM-KS-PENDIDIKAN' => [
                'rubric_code' => 'P1',
                'weight' => 15,
                'exclude_from_competency' => false,
                'aspect_name' => 'Relevansi riwayat pendidikan formal',
                'accepted_evidence' => ['ijazah', 'transkrip', 'sertifikat', 'bukti studi lanjut'],
                'scoring_note' => 'Menilai keterkaitan pendidikan formal dan sertifikasi dengan kebutuhan kepemimpinan sekolah.',
            ],
            'FORM-KS-PELATIHAN' => [
                'rubric_code' => 'P2',
                'weight' => 20,
                'exclude_from_competency' => false,
                'aspect_name' => 'Relevansi pelatihan dan penerapan',
                'accepted_evidence' => ['sertifikat pelatihan', 'agenda', 'rencana tindak lanjut', 'bukti penerapan'],
                'scoring_note' => 'Menilai relevansi pelatihan, bukti penerapan, dan dampaknya terhadap sekolah.',
            ],
            'FORM-KS-PENGALAMAN-MENGAJAR' => [
                'rubric_code' => 'P3',
                'weight' => 15,
                'exclude_from_competency' => false,
                'aspect_name' => 'Pengalaman praktik pendidikan',
                'accepted_evidence' => ['surat tugas', 'riwayat kerja', 'portofolio mengajar', 'dokumen pengalaman'],
                'scoring_note' => 'Menilai relevansi pengalaman pendidikan sebagai bekal kepemimpinan sekolah.',
            ],
            'FORM-KS-PRESTASI' => [
                'rubric_code' => 'P4',
                'weight' => 15,
                'exclude_from_competency' => false,
                'aspect_name' => 'Prestasi dan penghargaan profesional',
                'accepted_evidence' => ['sertifikat', 'piagam', 'surat keputusan', 'berita acara'],
                'scoring_note' => 'Menilai kualitas prestasi, pengakuan profesional, dan dampaknya terhadap sekolah.',
            ],
            'FORM-KS-KARYA-INOVASI' => [
                'rubric_code' => 'P5',
                'weight' => 20,
                'exclude_from_competency' => false,
                'aspect_name' => 'Karya, inovasi, dan best practice',
                'accepted_evidence' => ['naskah', 'produk', 'laporan implementasi', 'dokumentasi kegiatan', 'data dampak'],
                'scoring_note' => 'Menilai kualitas karya, inovasi, dan praktik baik yang mendukung peningkatan mutu sekolah.',
            ],
            'FORM-KS-REFLEKSI-DIRI' => [
                'rubric_code' => 'P6',
                'weight' => 15,
                'exclude_from_competency' => false,
                'aspect_name' => 'Kepribadian, integritas, dan refleksi diri',
                'accepted_evidence' => ['refleksi tertulis', 'rencana tindak lanjut', 'catatan evaluasi diri'],
                'scoring_note' => 'Menilai kualitas refleksi, kesadaran diri, dan arah pengembangan kepemimpinan.',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function fieldRubricLibrary(): array
    {
        return [
            'FORM-KS-PENDIDIKAN' => [
                'method' => 'repeater_completeness',
                'reference_answer' => 'Riwayat pendidikan menunjukkan relevansi jenjang, program studi, sertifikasi, lembaga, dan kaitannya dengan kepemimpinan sekolah.',
                'keyword_groups' => [
                    ['pendidikan', 'formal'],
                    ['sertifikasi'],
                    ['program', 'studi'],
                    ['kepemimpinan', 'sekolah'],
                ],
                'advanced_rules' => [
                    'target_rows' => 1,
                    'min_words' => 12,
                    'signal_keywords' => ['ijazah', 'sertifikasi', 'lembaga', 'program studi'],
                ],
            ],
            'FORM-KS-PELATIHAN' => [
                'method' => 'repeater_completeness',
                'reference_answer' => 'Pelatihan yang dicantumkan relevan dengan kebutuhan sekolah, memiliki penyelenggara dan tahun yang jelas, serta menunjukkan dampak penerapan.',
                'keyword_groups' => [
                    ['pelatihan', 'relevan'],
                    ['penyelenggara'],
                    ['dampak', 'sekolah'],
                    ['penerapan', 'program'],
                ],
                'advanced_rules' => [
                    'target_rows' => 1,
                    'min_words' => 18,
                    'signal_keywords' => ['pelatihan', 'penyelenggara', 'dampak', 'implementasi'],
                ],
            ],
            'FORM-KS-PENGALAMAN-MENGAJAR' => [
                'method' => 'repeater_completeness',
                'reference_answer' => 'Pengalaman pendidikan menunjukkan kesinambungan peran dan relevansi pengalaman terhadap pengembangan kepemimpinan sekolah.',
                'keyword_groups' => [
                    ['pengalaman', 'mengajar'],
                    ['lembaga'],
                    ['tahun'],
                    ['kepemimpinan', 'pendidikan'],
                ],
                'advanced_rules' => [
                    'target_rows' => 1,
                    'min_words' => 12,
                    'signal_keywords' => ['guru', 'wali kelas', 'pengalaman', 'sekolah'],
                ],
            ],
            'FORM-KS-PRESTASI' => [
                'method' => 'repeater_completeness',
                'reference_answer' => 'Prestasi atau penghargaan memiliki konteks profesional yang jelas dan menunjukkan dampak terhadap sekolah.',
                'keyword_groups' => [
                    ['prestasi', 'penghargaan'],
                    ['deskripsi'],
                    ['dampak', 'sekolah'],
                ],
                'advanced_rules' => [
                    'target_rows' => 1,
                    'min_words' => 15,
                    'signal_keywords' => ['prestasi', 'penghargaan', 'dampak'],
                ],
            ],
            'FORM-KS-KARYA-INOVASI' => [
                'method' => 'repeater_completeness',
                'reference_answer' => 'Karya, inovasi, atau best practice menunjukkan ide, implementasi, dan dampak terhadap mutu sekolah.',
                'keyword_groups' => [
                    ['karya', 'inovasi'],
                    ['best practice'],
                    ['dampak', 'sekolah'],
                ],
                'advanced_rules' => [
                    'target_rows' => 1,
                    'min_words' => 18,
                    'signal_keywords' => ['inovasi', 'best practice', 'dampak', 'sekolah'],
                ],
            ],
            'FORM-KS-REFLEKSI-DIRI' => [
                'method' => 'semantic_similarity',
                'reference_answer' => 'Refleksi menunjukkan kekuatan kepemimpinan, area pengembangan, integritas, dan langkah tindak lanjut yang realistis.',
                'keyword_groups' => [
                    ['kekuatan', 'pengembangan'],
                    ['kepemimpinan', 'sekolah'],
                    ['integritas', 'refleksi'],
                    ['tindak lanjut', 'perbaikan'],
                ],
                'advanced_rules' => [
                    'min_words' => 40,
                    'signal_keywords' => ['kekuatan', 'pengembangan', 'refleksi', 'tindak lanjut'],
                ],
            ],
        ];
    }
}
