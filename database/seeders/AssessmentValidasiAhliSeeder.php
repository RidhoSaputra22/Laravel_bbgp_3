<?php

namespace Database\Seeders;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentValidasiAhliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assessments = [
            [
                'kode_assessment' => 'ASM-VALIDASI-AHLI-001',
                'judul' => 'Instrumen Validasi Ahli Instrumen Pemetaan Kompetensi Guru',
                'deskripsi' => 'Instrumen validasi ahli untuk menilai kelayakan Instrumen Pemetaan Kompetensi Guru berdasarkan aspek desain instrumen, konten/substansi, dan kebahasaan.',
                'petunjuk' => 'Bapak/Ibu Validator dimohon memberikan penilaian terhadap Instrumen Pemetaan Kompetensi Guru berdasarkan aspek desain instrumen, konten/substansi, dan kebahasaan. Berikan skor 1 sampai 5 sesuai dengan tingkat kesesuaian setiap indikator. Kriteria skor: 1 Sangat Tidak Sesuai, 2 Tidak Sesuai, 3 Cukup Sesuai, 4 Sesuai, dan 5 Sangat Sesuai.',
                'instrument_type' => $this->instrumentTypeValue(),
                'status' => 'publish',
                'is_active' => true,
                'forms' => [
                    [
                        'judul_form' => 'Identitas Validator',
                        'kode_form' => 'FORM-VALIDATOR',
                        'deskripsi' => 'Data identitas ahli atau validator yang melakukan penilaian.',
                        'is_scoreable' => false,
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Nama Validator',
                                'deskripsi' => 'Tuliskan nama lengkap validator.',
                                'nama_field' => 'nama_validator',
                                'tipe_field' => 'text',
                                'placeholder' => 'Masukkan nama lengkap validator',
                                'bantuan' => 'Isi sesuai identitas resmi validator.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Jabatan/Profesi',
                                'deskripsi' => 'Tuliskan jabatan atau profesi validator.',
                                'nama_field' => 'jabatan_profesi',
                                'tipe_field' => 'text',
                                'placeholder' => 'Contoh: Dosen, Pengawas Sekolah, Praktisi Pendidikan',
                                'bantuan' => 'Isi jabatan atau profesi saat ini.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Instansi',
                                'deskripsi' => 'Tuliskan nama instansi asal validator.',
                                'nama_field' => 'instansi',
                                'tipe_field' => 'text',
                                'placeholder' => 'Masukkan nama instansi',
                                'bantuan' => 'Isi nama perguruan tinggi, sekolah, lembaga, atau instansi.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Bidang Keahlian',
                                'deskripsi' => 'Tuliskan bidang keahlian validator.',
                                'nama_field' => 'bidang_keahlian',
                                'tipe_field' => 'text',
                                'placeholder' => 'Contoh: Evaluasi Pendidikan',
                                'bantuan' => 'Isi bidang keahlian yang relevan dengan proses validasi.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 4,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Tanggal Validasi',
                                'deskripsi' => 'Pilih tanggal pelaksanaan validasi.',
                                'nama_field' => 'tanggal_validasi',
                                'tipe_field' => 'date',
                                'placeholder' => null,
                                'bantuan' => 'Gunakan tanggal saat instrumen dinilai.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true, 'date' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 5,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => 'Aspek Desain Instrumen',
                        'kode_form' => 'FORM-VALIDASI-DESAIN',
                        'deskripsi' => 'Penilaian terhadap tampilan, format, sistematika, petunjuk, layout, skala, identitas, dan kemudahan pelaksanaan asesmen.',
                        'indikator_kode' => 'VA-DESAIN',
                        'indikator_label' => 'Kelayakan desain instrumen',
                        'is_scoreable' => true,
                        'urutan' => 2,
                        'is_active' => true,
                        'fields' => $this->likertFields('desain', [
                            'Tampilan instrumen rapi dan sistematis',
                            'Format instrumen mudah dipahami',
                            'Sistematika penyajian instrumen logis',
                            'Petunjuk pengisian jelas dan mudah dipahami',
                            'Urutan indikator disusun secara sistematis',
                            'Layout memudahkan responden mengisi instrumen',
                            'Skala penilaian disajikan secara konsisten',
                            'Instrumen memiliki identitas yang lengkap',
                            'Kode atau penomoran butir tersusun secara sistematis',
                            'Desain instrumen mendukung kemudahan pelaksanaan asesmen',
                        ]),
                    ],
                    [
                        'judul_form' => 'Aspek Konten/Substansi',
                        'kode_form' => 'FORM-VALIDASI-KONTEN',
                        'deskripsi' => 'Penilaian terhadap kesesuaian tujuan, keterwakilan indikator, ketepatan pengukuran kompetensi, relevansi isi, kebebasan bias, dan dukungan pengambilan keputusan.',
                        'indikator_kode' => 'VA-KONTEN',
                        'indikator_label' => 'Kelayakan konten atau substansi',
                        'is_scoreable' => true,
                        'urutan' => 3,
                        'is_active' => true,
                        'fields' => $this->likertFields('konten', [
                            'Instrumen sesuai dengan tujuan pemetaan kompetensi guru',
                            'Butir mengacu pada model kompetensi guru yang berlaku',
                            'Setiap indikator telah terwakili oleh butir instrumen',
                            'Butir mampu mengukur kompetensi yang dimaksud',
                            'Isi instrumen relevan dengan tugas profesional guru',
                            'Tidak terdapat indikator yang tumpang tindih',
                            'Setiap butir memiliki fokus yang jelas',
                            'Butir bebas dari bias gender, budaya, maupun wilayah',
                            'Instrumen mampu membedakan tingkat kompetensi guru',
                            'Kedalaman materi sesuai dengan tujuan pemetaan',
                            'Indikator mencerminkan kompetensi profesional',
                            'Indikator mencerminkan kompetensi pedagogik',
                            'Indikator mencerminkan kompetensi sosial',
                            'Indikator mencerminkan kompetensi kepribadian',
                            'Instrumen mendukung pengambilan keputusan pengembangan kompetensi',
                        ]),
                    ],
                    [
                        'judul_form' => 'Aspek Kebahasaan',
                        'kode_form' => 'FORM-VALIDASI-BAHASA',
                        'deskripsi' => 'Penilaian terhadap ketepatan kaidah bahasa, kejelasan kalimat, pilihan kata, konsistensi istilah, ejaan, tanda baca, dan sifat komunikatif redaksi.',
                        'indikator_kode' => 'VA-BAHASA',
                        'indikator_label' => 'Kelayakan kebahasaan',
                        'is_scoreable' => true,
                        'urutan' => 4,
                        'is_active' => true,
                        'fields' => $this->likertFields('bahasa', [
                            'Bahasa menggunakan kaidah Bahasa Indonesia yang baik dan benar',
                            'Kalimat mudah dipahami responden',
                            'Pilihan kata tepat',
                            'Tidak menimbulkan makna ganda',
                            'Kalimat efektif dan tidak berbelit',
                            'Istilah yang digunakan konsisten',
                            'Bahasa sesuai dengan karakteristik guru sebagai responden',
                            'Tidak terdapat kesalahan ejaan maupun tanda baca',
                            'Redaksi setiap butir komunikatif',
                            'Bahasa tidak mengarahkan jawaban responden',
                        ]),
                    ],
                    [
                        'judul_form' => 'Saran, Masukan, dan Rekomendasi Validator',
                        'kode_form' => 'FORM-SARAN-VALIDATOR',
                        'deskripsi' => 'Catatan perbaikan dan rekomendasi akhir dari validator.',
                        'is_scoreable' => false,
                        'urutan' => 5,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Saran dan Masukan Aspek Desain',
                                'deskripsi' => 'Tuliskan saran atau masukan terkait aspek desain instrumen.',
                                'nama_field' => 'saran_aspek_desain',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan saran dan masukan aspek desain',
                                'bantuan' => 'Kosongkan apabila tidak terdapat saran.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Saran dan Masukan Aspek Konten/Substansi',
                                'deskripsi' => 'Tuliskan saran atau masukan terkait aspek konten atau substansi.',
                                'nama_field' => 'saran_aspek_konten',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan saran dan masukan aspek konten/substansi',
                                'bantuan' => 'Kosongkan apabila tidak terdapat saran.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Saran dan Masukan Aspek Kebahasaan',
                                'deskripsi' => 'Tuliskan saran atau masukan terkait aspek kebahasaan.',
                                'nama_field' => 'saran_aspek_kebahasaan',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan saran dan masukan aspek kebahasaan',
                                'bantuan' => 'Kosongkan apabila tidak terdapat saran.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Rekomendasi Umum',
                                'deskripsi' => 'Pilih rekomendasi akhir berdasarkan hasil validasi.',
                                'nama_field' => 'rekomendasi_umum',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Pilih satu rekomendasi yang paling sesuai.',
                                'opsi_field' => [
                                    'Dapat digunakan tanpa revisi',
                                    'Dapat digunakan dengan revisi kecil',
                                    'Dapat digunakan dengan revisi besar',
                                    'Belum layak digunakan',
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 4,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Nama Validator pada Pengesahan',
                                'deskripsi' => 'Tuliskan kembali nama validator untuk bagian pengesahan.',
                                'nama_field' => 'nama_validator_pengesahan',
                                'tipe_field' => 'text',
                                'placeholder' => 'Masukkan nama validator',
                                'bantuan' => 'Isi sesuai nama validator pada identitas.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 5,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Tanda Tangan Validator',
                                'deskripsi' => 'Unggah tanda tangan validator.',
                                'nama_field' => 'tanda_tangan_validator',
                                'tipe_field' => 'file',
                                'placeholder' => null,
                                'bantuan' => 'Format yang disarankan: PNG, JPG, atau JPEG.',
                                'opsi_field' => [
                                    'accept' => ['image/png', 'image/jpeg'],
                                    'max_size_kb' => 2048,
                                    'max_files' => 1,
                                ],
                                'nilai_default' => null,
                                'validasi' => [
                                    'required' => true,
                                    'mimes' => ['png', 'jpg', 'jpeg'],
                                    'max' => 2048,
                                ],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 6,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Tanggal Pengesahan',
                                'deskripsi' => 'Pilih tanggal pengesahan hasil validasi.',
                                'nama_field' => 'tanggal_pengesahan',
                                'tipe_field' => 'date',
                                'placeholder' => null,
                                'bantuan' => 'Tanggal dapat disamakan dengan tanggal validasi.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true, 'date' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 7,
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
                    'instrument_type' => $item['instrument_type'],
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
                    $fieldData['scoring_config'] = $this->fieldScoringConfig(
                        $formData,
                        $fieldData,
                        $fieldIndex
                    );

                    $form->fields()->create($fieldData);
                }
            }
        }
    }

    /**
     * Menggunakan enum apabila value validasi_ahli telah tersedia.
     * Fallback string dipakai agar seeder tetap mudah disesuaikan.
     */
    private function instrumentTypeValue(): string
    {
        return AssessmentInstrumentType::tryFrom('validasi_ahli')?->value
            ?? 'validasi_ahli';
    }

    /**
     * Membentuk field skala Likert 1-5.
     *
     * @param  array<int, string>  $indicators
     * @return array<int, array<string, mixed>>
     */
    private function likertFields(string $prefix, array $indicators): array
    {
        $fields = [];

        foreach (array_values($indicators) as $index => $indicator) {
            $number = $index + 1;

            $fields[] = [
                'label' => $indicator,
                'deskripsi' => 'Berikan skor kesesuaian untuk indikator ini.',
                'nama_field' => sprintf('%s_%02d', $prefix, $number),
                'tipe_field' => 'radio',
                'placeholder' => null,
                'bantuan' => '1 = Sangat Tidak Sesuai; 2 = Tidak Sesuai; 3 = Cukup Sesuai; 4 = Sesuai; 5 = Sangat Sesuai.',
                'opsi_field' => [1, 2, 3, 4, 5],
                'nilai_default' => null,
                'validasi' => [
                    'required' => true,
                    'in' => [1, 2, 3, 4, 5],    
                ],
                'lebar_kolom' => 'col-md-12',
                'urutan' => $number,
                'is_required' => true,
                'is_active' => true,
            ];
        }

        return $fields;
    }

    private function assessmentScoringConfig(): array
    {
        return [
            'profile' => 'validasi_ahli',
            'scale_min' => 1,
            'scale_max' => 5,
            'total_items' => 35,
            'minimum_score' => 35,
            'maximum_score' => 175,
            'advanced_rules' => [
                'overall_formula' => 'Persentase kelayakan = (total skor diperoleh / 175) x 100.',
                'aspect_formula' => 'Skor aspek = jumlah skor seluruh butir pada aspek.',
                'aspects' => [
                    'desain' => [
                        'item_count' => 10,
                        'minimum_score' => 10,
                        'maximum_score' => 50,
                    ],
                    'konten_substansi' => [
                        'item_count' => 15,
                        'minimum_score' => 15,
                        'maximum_score' => 75,
                    ],
                    'kebahasaan' => [
                        'item_count' => 10,
                        'minimum_score' => 10,
                        'maximum_score' => 50,
                    ],
                ],
                'interpretation' => [
                    [
                        'minimum_percentage' => 86,
                        'maximum_percentage' => 100,
                        'category' => 'Sangat Valid',
                    ],
                    [
                        'minimum_percentage' => 71,
                        'maximum_percentage' => 85,
                        'category' => 'Valid',
                    ],
                    [
                        'minimum_percentage' => 56,
                        'maximum_percentage' => 70,
                        'category' => 'Cukup Valid',
                    ],
                    [
                        'minimum_percentage' => 41,
                        'maximum_percentage' => 55,
                        'category' => 'Kurang Valid',
                    ],
                    [
                        'minimum_percentage' => 0,
                        'maximum_percentage' => 40,
                        'category' => 'Tidak Valid',
                    ],
                ],
                'recommendation_options' => [
                    'Dapat digunakan tanpa revisi',
                    'Dapat digunakan dengan revisi kecil',
                    'Dapat digunakan dengan revisi besar',
                    'Belum layak digunakan',
                ],
            ],
        ];
    }

    private function formScoringConfig(array $formData): ?array
    {
        if (! ($formData['is_scoreable'] ?? false)) {
            return null;
        }

        $rubrics = [
            'FORM-VALIDASI-DESAIN' => [
                'aspect_code' => 'DESAIN',
                'aspect_name' => 'Aspek Desain Instrumen',
                'item_count' => 10,
                'minimum_score' => 10,
                'maximum_score' => 50,
            ],
            'FORM-VALIDASI-KONTEN' => [
                'aspect_code' => 'KONTEN',
                'aspect_name' => 'Aspek Konten/Substansi',
                'item_count' => 15,
                'minimum_score' => 15,
                'maximum_score' => 75,
            ],
            'FORM-VALIDASI-BAHASA' => [
                'aspect_code' => 'BAHASA',
                'aspect_name' => 'Aspek Kebahasaan',
                'item_count' => 10,
                'minimum_score' => 10,
                'maximum_score' => 50,
            ],
        ];

        $config = $rubrics[$formData['kode_form'] ?? ''] ?? null;

        if ($config === null) {
            return null;
        }

        return [
            'profile' => 'validasi_ahli',
            'weight' => $config['maximum_score'],
            'advanced_rules' => [
                'aspect_code' => $config['aspect_code'],
                'aspect_name' => $config['aspect_name'],
                'item_count' => $config['item_count'],
                'minimum_score' => $config['minimum_score'],
                'maximum_score' => $config['maximum_score'],
                'form_formula' => 'Jumlahkan nilai seluruh butir pada aspek.',
            ],
        ];
    }

    private function fieldScoringConfig(
        array $formData,
        array $fieldData,
        int $fieldIndex
    ): ?array {
        if (! ($formData['is_scoreable'] ?? false)) {
            return null;
        }

        return [
            'enabled' => true,
            'profile' => 'validasi_ahli',
            'method' => 'direct_scale',
            'weight' => 1,
            'scale_min' => 1,
            'scale_max' => 5,
            'score_map' => [
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
            ],
            'advanced_rules' => [
                'item_number' => $fieldIndex + 1,
                'aspect_code' => data_get(
                    $this->formScoringConfig($formData),
                    'advanced_rules.aspect_code'
                ),
                'indicator' => $fieldData['label'] ?? null,
                'scoring_note' => 'Nilai jawaban digunakan langsung sebagai skor butir.',
            ],
        ];
    }
}
