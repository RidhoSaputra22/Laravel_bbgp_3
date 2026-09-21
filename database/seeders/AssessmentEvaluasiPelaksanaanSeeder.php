<?php

namespace Database\Seeders;

use App\Enum\AssessmentInstrumentType;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AssessmentEvaluasiPelaksanaanSeeder extends Seeder
{
    private const ASSESSMENT_CODE = 'ASM-EVAL-PELAKSANAAN-HBG-001';

    /**
     * Membuat bank soal Evaluasi Pelaksanaan Hari Belajar Guru.
     */
    public function run(): void
    {
        $forms = $this->forms();
        $totalScoredItems = collect($forms)
            ->flatMap(fn(array $form) => $form['fields'])
            ->filter(fn(array $field) => (bool) data_get($field, 'scoring_config.enabled', false))
            ->count();

        $assessmentData = [
            'judul' => 'Evaluasi Pelaksanaan Hari Belajar Guru',
            'slug' => Str::slug('Evaluasi Pelaksanaan Hari Belajar Guru'),
            'deskripsi' => 'Instrumen untuk mengevaluasi pelaksanaan kegiatan pelatihan dan kinerja narasumber berdasarkan Kuesioner Evaluasi Pelaksanaan Kegiatan.',
            'petunjuk' => 'Lengkapi identitas kegiatan terlebih dahulu. Pada bagian B dan C, pilih satu angka pada setiap pernyataan menggunakan skala 1 sampai 4. Saran/masukan pada bagian C bersifat opsional.',
            'instrument_type' => AssessmentInstrumentType::SKALA_LIKERT->value,
            'target_ketenagaan' => null,
            'scoring_config' => $this->assessmentScoringConfig($totalScoredItems),
            'status' => 'publish',
            'is_active' => true,
        ];

        if (Schema::hasColumn('assessments', 'kategori')) {
            $assessmentData['kategori'] = Assessment::CATEGORY_EVALUASI_PELAKSANAAN;
        }

        $assessment = Assessment::updateOrCreate(
            ['kode_assessment' => self::ASSESSMENT_CODE],
            $assessmentData
        );

        $assessment->forms()->delete();

        foreach ($forms as $formIndex => $formData) {
            $fields = $formData['fields'];

            $form = $assessment->forms()->create([
                'judul_form' => $formData['judul_form'],
                'kode_form' => $formData['kode_form'],
                'deskripsi' => $formData['deskripsi'],
                'kompetensi' => null,
                'indikator_kode' => $formData['indikator_kode'] ?? null,
                'indikator_label' => $formData['indikator_label'] ?? null,
                'is_scoreable' => (bool) $formData['is_scoreable'],
                'scoring_config' => $formData['is_scoreable']
                    ? $this->formScoringConfig($fields)
                    : null,
                'urutan' => $formIndex + 1,
                'is_active' => true,
            ]);

            foreach (array_values($fields) as $fieldIndex => $fieldData) {
                $fieldData['urutan'] = $fieldIndex + 1;
                $form->fields()->create($fieldData);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function forms(): array
    {
        return [
            [
                'judul_form' => 'A. Identitas Kegiatan',
                'kode_form' => 'FORM-IDENTITAS-KEGIATAN',
                'deskripsi' => 'Data identitas peserta dan informasi kegiatan Hari Belajar Guru yang diikuti.',
                'is_scoreable' => false,
                'fields' => [
                    $this->selectField(
                        '1. Nama Narasumber',
                        'nama_narasumber',
                        ['Narasumber 1', 'Narasumber 2', 'Narasumber 3'],
                        'Pilih narasumber...',
                        '',
                        true,
                    ),
                    $this->textField(
                        '2. Nama Sekolah',
                        'nama_sekolah',
                        'Masukkan nama Sekolah',
                        'Nama sekolah dapat terisi otomatis dari data SIM.',
                        'satuan_pendidikan'
                    ),
                    $this->selectField(
                        '3. Kabupaten/Kota',
                        'kabupaten_kota',
                        [],
                        'Pilih jawaban...',
                        'Data kabupaten/kota dapat terisi otomatis dari data SIM.',
                        true,
                        true,
                        'Kabupaten / Kota'
                    ),
                    $this->textField(
                        '4. Materi/Topik',
                        'materi_topik',
                        'Masukan materi/topik kegiatan',
                        'Masukan materi/topik kegiatan',

                    ),
                    $this->dateField(
                        '5. Tanggal Pelaksanaan',
                        'tanggal_pelaksanaan',
                        'Masukkan tanggal pelaksanaan kegiatan.'
                    ),
                    $this->selectField(
                        '6. Jabatan',
                        'jabatan',
                        [
                            'Guru',
                            'Kepala Sekolah',
                            'KKG',
                            'MGMP',
                            'K3S/ MKKS',
                            'Kombel',
                            'Pengawas sekolah',
                            'Pelatih dari balai-balai',
                            'Pemerintah Daerah & Pusat',
                        ],
                        'Pilih jawaban...',
                        'Pilih jabatan peserta.',
                        true,
                        false
                    ),
                    $this->textField(
                        '7. Tempat Pelaksanaan',
                        'tempat_pelaksanaan',
                        'Masukkan tempat pelaksanaan kegiatan',
                        'Masukkan tempat pelaksanaan kegiatan'
                    ),
                ],
            ],
            [
                'judul_form' => 'B. Evaluasi Pelaksanaan Kegiatan Pelatihan',
                'kode_form' => 'FORM-EVALUASI-PELAKSANAAN',
                'deskripsi' => 'Berikan penilaian pada setiap pernyataan menggunakan skala angka 1 sampai 4.',
                'indikator_kode' => 'B',
                'indikator_label' => 'Evaluasi pelaksanaan kegiatan pelatihan',
                'is_scoreable' => true,
                'fields' => $this->scaledFields('evaluasi_pelaksanaan', [
                    [
                        'group' => '1. Aspek Perencanaan & Persiapan',
                        'indicator' => 'Kesesuaian jadwal dengan rundown/rencana awal',
                        'statement' => 'Pelaksanaan kegiatan sesuai dengan jadwal/rundown yang telah ditetapkan',
                    ],
                    [
                        'group' => '1. Aspek Perencanaan & Persiapan',
                        'indicator' => 'Ketepatan waktu pelaksanaan (mulai, istirahat, selesai)',
                        'statement' => 'Kegiatan dimulai tepat waktu sesuai jadwal',
                    ],
                    [
                        'group' => '1. Aspek Perencanaan & Persiapan',
                        'indicator' => 'Kesiapan tempat/venue (ruang, tata letak, kapasitas)',
                        'statement' => 'Ruangan yang digunakan nyaman dan sesuai dengan kapasitas peserta',
                    ],
                    [
                        'group' => '1. Aspek Perencanaan & Persiapan',
                        'indicator' => 'Kesiapan sarana & prasarana (proyektor, sound system, internet, dll)',
                        'statement' => 'Peralatan pendukung (proyektor, sound system, internet) berfungsi dengan baik',
                    ],
                    [
                        'group' => '1. Aspek Perencanaan & Persiapan',
                        'indicator' => 'Ketersediaan bahan ajar/modul/materi cetak atau digital',
                        'statement' => 'Modul/materi pelatihan tersedia tepat waktu bagi peserta',
                    ],
                    [
                        'group' => '2. Aspek Administrasi & Logistik',
                        'indicator' => 'Proses registrasi peserta',
                        'statement' => 'Proses registrasi peserta berjalan lancar dan tidak memakan waktu lama',
                    ],
                    [
                        'group' => '2. Aspek Administrasi & Logistik',
                        'indicator' => 'Ketersediaan konsumsi (jika ada)',
                        'statement' => 'Konsumsi yang disediakan memadai dari segi jumlah',
                    ],
                    [
                        'group' => '2. Aspek Administrasi & Logistik',
                        'indicator' => 'Kelengkapan ATK dan perlengkapan peserta',
                        'statement' => 'ATK dan perlengkapan peserta tersedia dengan lengkap',
                    ],
                    [
                        'group' => '2. Aspek Administrasi & Logistik',
                        'indicator' => 'Sertifikat/dokumentasi kelulusan',
                        'statement' => 'Proses penerbitan sertifikat dilakukan dengan tertib dan tepat waktu',
                    ],
                    [
                        'group' => '2. Aspek Administrasi & Logistik',
                        'indicator' => 'Akomodasi & transportasi (jika relevan)',
                        'statement' => 'Akomodasi yang disediakan (jika ada) memadai dan nyaman',
                    ],
                    [
                        'group' => '3. Aspek Substansi Kegiatan',
                        'indicator' => 'Kesesuaian materi dengan tujuan/kompetensi yang ditargetkan',
                        'statement' => 'Materi yang disampaikan sesuai dengan tujuan/kompetensi yang ditargetkan',
                    ],
                    [
                        'group' => '3. Aspek Substansi Kegiatan',
                        'indicator' => 'Relevansi materi dengan kebutuhan guru di lapangan',
                        'statement' => 'Materi pelatihan relevan dengan kebutuhan guru di lapangan',
                    ],
                    [
                        'group' => '3. Aspek Substansi Kegiatan',
                        'indicator' => 'Keseimbangan antara teori dan praktik',
                        'statement' => 'Porsi antara teori dan praktik dalam pelatihan sudah seimbang',
                    ],
                    [
                        'group' => '3. Aspek Substansi Kegiatan',
                        'indicator' => 'Metode pelatihan (ceramah, diskusi, simulasi, praktik langsung)',
                        'statement' => 'Metode pelatihan yang digunakan bervariasi dan tidak membosankan',
                    ],
                    [
                        'group' => '4. Aspek Partisipasi Peserta',
                        'indicator' => 'Tingkat kehadiran peserta',
                        'statement' => 'Peserta hadir secara penuh selama rangkaian kegiatan pelatihan',
                    ],
                    [
                        'group' => '4. Aspek Partisipasi Peserta',
                        'indicator' => 'Keaktifan peserta selama sesi',
                        'statement' => 'Peserta aktif bertanya dan berdiskusi selama sesi berlangsung',
                    ],
                    [
                        'group' => '4. Aspek Partisipasi Peserta',
                        'indicator' => 'Pemahaman/penyerapan materi (pre-test & post-test)',
                        'statement' => 'Terdapat peningkatan pemahaman peserta setelah mengikuti pelatihan',
                    ],
                    [
                        'group' => '4. Aspek Partisipasi Peserta',
                        'indicator' => 'Kepuasan peserta terhadap keseluruhan kegiatan',
                        'statement' => 'Peserta merasa puas terhadap keseluruhan pelaksanaan kegiatan',
                    ],
                    [
                        'group' => '5. Aspek Panitia/Penyelenggara',
                        'indicator' => 'Koordinasi panitia selama acara',
                        'statement' => 'Panitia menunjukkan koordinasi yang baik selama pelaksanaan kegiatan',
                    ],
                    [
                        'group' => '5. Aspek Panitia/Penyelenggara',
                        'indicator' => 'Responsivitas terhadap kendala teknis',
                        'statement' => 'Panitia sigap dalam menangani kendala teknis yang muncul',
                    ],
                    [
                        'group' => '5. Aspek Panitia/Penyelenggara',
                        'indicator' => 'Kualitas pelayanan kepada peserta dan narasumber',
                        'statement' => 'Panitia memberikan pelayanan yang ramah kepada peserta',
                    ],
                    [
                        'group' => '6. Aspek Dampak/Tindak Lanjut',
                        'indicator' => 'Rencana implementasi hasil pelatihan di sekolah',
                        'statement' => 'Saya memiliki rencana konkret untuk menerapkan hasil pelatihan di sekolah',
                    ],
                    [
                        'group' => '6. Aspek Dampak/Tindak Lanjut',
                        'indicator' => 'Mekanisme monitoring pasca-pelatihan',
                        'statement' => 'Terdapat mekanisme tindak lanjut/monitoring pasca-pelatihan yang jelas',
                    ],
                    [
                        'group' => '6. Aspek Dampak/Tindak Lanjut',
                        'indicator' => 'Umpan balik untuk perbaikan pelatihan berikutnya',
                        'statement' => 'Kegiatan ini memberikan ruang bagi peserta untuk menyampaikan masukan',
                    ],
                ]),
            ],
            [
                'judul_form' => 'C. Evaluasi Narasumber',
                'kode_form' => 'FORM-EVALUASI-NARASUMBER',
                'deskripsi' => 'Berikan penilaian pada setiap pernyataan menggunakan skala angka 1 sampai 4. Saran/masukan dapat diisi pada bagian akhir.',
                'indikator_kode' => 'C',
                'indikator_label' => 'Evaluasi narasumber',
                'is_scoreable' => true,
                'fields' => array_merge(
                    $this->scaledFields('evaluasi_narasumber', [
                        [
                            'group' => 'Kesesuaian materi',
                            'statement' => 'Materi yang disampaikan sesuai dengan topik pelatihan',
                        ],
                        [
                            'group' => 'Penguasaan materi',
                            'statement' => 'Narasumber menguasai materi yang disampaikan dengan baik',
                        ],
                        [
                            'group' => 'Sistematika Penyajian',
                            'statement' => 'Narasumber mampu menjelaskan konsep secara sistematis',
                        ],
                        [
                            'group' => 'Kedalaman dan keluasan wawasan',
                            'statement' => 'Narasumber memiliki wawasan luas terkait topik yang dibahas',
                        ],
                        [
                            'group' => 'Kejelasan Materi',
                            'statement' => 'Narasumber mampu menjelaskan menggunakan bahasa yang lugas dan mudah dipahami',
                        ],
                        [
                            'group' => 'Teknik Penyampaian',
                            'statement' => 'Narasumber mampu menyampaikan materi dengan menarik',
                        ],
                        [
                            'group' => 'Penggunaan media/alat bantu',
                            'statement' => 'Narasumber menggunakan media/alat bantu pembelajaran secara efektif',
                        ],
                        [
                            'group' => 'Kemampuan menjawab',
                            'statement' => 'Narasumber mampu menjawab pertanyaan peserta dengan baik',
                        ],
                        [
                            'group' => 'Sikap/Pelayanan',
                            'statement' => 'Narasumber menunjukkan sikap sopan dan profesional selama pelatihan berlangsung',
                        ],
                        [
                            'group' => 'Pembelajaran Mendalam',
                            'statement' => 'Narasumber menggugah Kesadaran, menghadirkan Kebermaknaan, dan Menggembirakan',
                        ],
                        [
                            'group' => 'Kelayakan',
                            'statement' => 'Narasumber layak diundang kembali pada kegiatan serupa di masa mendatang',
                        ],
                    ]),
                    [
                        $this->field(
                            'Saran/Masukan',
                            'saran_masukan',
                            'text',
                            null,
                            false,
                            'Tuliskan saran atau masukan',
                            null,
                            'Saran/masukan bersifat opsional.'
                        ),
                    ]
                ),
            ],
        ];
    }

    /**
     * @param array<int, array{group: string, indicator?: string, statement: string}> $items
     * @return array<int, array<string, mixed>>
     */
    private function scaledFields(string $prefix, array $items): array
    {
        return collect($items)
            ->values()
            ->map(fn(array $item, int $index) => $this->scoredChoiceField(
                $item['statement'],
                $prefix . '_' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                $this->scaleOptions(),
                collect([$item['group'], $item['indicator'] ?? null])->filter()->implode(' — '),
            ))
            ->all();
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function scaleOptions(): array
    {
        return [

            ['label' => '1', 'value' => '1', 'score' => 1, 'level_kompetensi' => 1],
            ['label' => '2', 'value' => '2', 'score' => 2, 'level_kompetensi' => 2],
            ['label' => '3', 'value' => '3', 'score' => 3, 'level_kompetensi' => 3],
            ['label' => '4', 'value' => '4', 'score' => 4, 'level_kompetensi' => 4],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $options
     * @return array<string, mixed>
     */
    private function scoredChoiceField(
        string $label,
        string $name,
        array $options,
        ?string $description = null
    ): array {
        return $this->field(
            $label,
            $name,
            'radio',
            $options,
            true,
            null,
            $description,
            'Pilih nilai 1–4 sesuai penilaian Anda: 1 = Kurang, 2 = Cukup, 3 = Baik, 4 = Sangat Baik.',
            null,
            [
                'enabled' => true,
                'profile' => AssessmentInstrumentType::SKALA_LIKERT->value,
                'method' => 'choice_option_score',
                'weight' => 1,
                'scale_min' => 1,
                'scale_max' => 4,
                'advanced_rules' => [
                    'scale' => [
                        'min' => 1,
                        'max' => 4,
                    ],
                    'scoring_note' => 'Skor diambil langsung dari pilihan angka 1 sampai 4.',
                ],
            ],
        );
    }

    /**
     * @param array<int, string> $options
     * @return array<string, mixed>
     */
    private function selectField(
        string $label,
        string $name,
        array $options,
        string $placeholder,
        string $help,
        bool $allowOtherInput = false,
        bool $required = true,
        ?string $autofillSource = null
    ): array {
        return $this->field(
            $label,
            $name,
            'select',
            $options,
            $required,
            $placeholder,
            null,
            $help,
            $autofillSource,
            null,
            $allowOtherInput ? ['allow_other_input' => true] : [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function textField(
        string $label,
        string $name,
        string $placeholder,
        string $help,
        ?string $autofillSource = null
    ): array {
        return $this->field(
            $label,
            $name,
            'text',
            null,
            true,
            $placeholder,
            null,
            $help,
            $autofillSource,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function dateField(string $label, string $name, string $help): array
    {
        return $this->field($label, $name, 'date', null, true, null, null, $help);
    }

    /**
     * @param array<int, mixed>|null $options
     * @param array<string, mixed>|null $scoringConfig
     * @param array<string, mixed> $validationExtra
     * @return array<string, mixed>
     */
    private function field(
        string $label,
        string $name,
        string $type,
        ?array $options = null,
        bool $required = true,
        ?string $placeholder = null,
        ?string $description = null,
        ?string $help = null,
        ?string $autofillSource = null,
        ?array $scoringConfig = null,
        array $validationExtra = []
    ): array {
        return [
            'label' => $label,
            'deskripsi' => $description,
            'nama_field' => $name,
            'tipe_field' => $type,
            'placeholder' => $placeholder,
            'bantuan' => $help,
            'opsi_field' => $options,
            'nilai_default' => null,
            'autofill_source' => $autofillSource,
            'lookup_source' => null,
            'validasi' => array_merge(['required' => $required], $validationExtra),
            'scoring_config' => $scoringConfig,
            'urutan' => 1,
            'is_required' => $required,
            'is_active' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function assessmentScoringConfig(int $totalItems): array
    {
        return [
            'profile' => AssessmentInstrumentType::SKALA_LIKERT->value,
            'weight' => AssessmentInstrumentType::SKALA_LIKERT->weight(),
            'scale_min' => 1,
            'scale_max' => 4,
            'total_items' => $totalItems,
            'minimum_score' => $totalItems,
            'maximum_score' => $totalItems * 4,
            'empty_response_threshold_percent' => 0,
            'advanced_rules' => [
                'scale' => [
                    'min' => 1,
                    'max' => 4,
                    'labels' => [
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                    ],
                ],
                'aggregation' => 'Rata-rata skor seluruh butir penilaian.',
                'source' => 'Kuesioner Evaluasi Pelaksanaan Kegiatan',
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @return array<string, mixed>
     */
    private function formScoringConfig(array $fields): array
    {
        $scoredItemCount = collect($fields)
            ->filter(fn(array $field) => (bool) data_get($field, 'scoring_config.enabled', false))
            ->count();

        return [
            'profile' => AssessmentInstrumentType::SKALA_LIKERT->value,
            'weight' => $scoredItemCount,
            'advanced_rules' => [
                'scale' => [
                    'min' => 1,
                    'max' => 4,
                ],
                'item_count' => $scoredItemCount,
                'form_formula' => 'Rata-rata skor seluruh jawaban bernilai pada form.',
            ],
        ];
    }
}
