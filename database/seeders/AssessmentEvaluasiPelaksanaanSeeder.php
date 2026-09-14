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
            ->where('is_scoreable', true)
            ->sum(fn (array $form) => count($form['fields']));

        $assessmentData = [
            'judul' => 'Evaluasi Pelaksanaan Hari Belajar Guru',
            'slug' => Str::slug('Evaluasi Pelaksanaan Hari Belajar Guru'),
            'deskripsi' => 'Instrumen untuk mengevaluasi pelaksanaan kegiatan Hari Belajar Guru, kinerja narasumber, kebermanfaatan kegiatan, dan rencana tindak lanjut peserta.',
            'petunjuk' => 'Lengkapi identitas kegiatan terlebih dahulu. Untuk bagian evaluasi, pilih satu jawaban pada setiap pernyataan. Skala penilaian: 1 = Kurang, 2 = Cukup, 3 = Baik, dan 4 = Sangat Baik. Pada bagian kebermanfaatan dan tindak lanjut, pilih jawaban yang paling sesuai dengan pengalaman Anda.',
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
                    $this->textField(
                        '1. Nama Peserta',
                        'nama_peserta',
                        'Masukkan nama lengkap peserta',
                        'Nama peserta dapat terisi otomatis dari data SIM.',
                        'nama_lengkap'
                    ),
                    $this->textField(
                        '2. NIK/NIP/NUPTK (disesuaikan dengan data pada SIM)',
                        'nik_nip_nuptk',
                        'Masukkan NIK/NIP/NUPTK',
                        'Periksa kembali nomor identitas sesuai data pada SIM.',
                        'nip_nuptk'
                    ),
                    $this->textField(
                        '3. Instansi/Satuan Pendidikan',
                        'instansi_satuan_pendidikan',
                        'Masukkan instansi atau satuan pendidikan',
                        'Data satuan pendidikan dapat terisi otomatis dari data SIM.',
                        'satuan_pendidikan'
                    ),
                    $this->textField(
                        '4. Kabupaten/Kota',
                        'kabupaten_kota',
                        'Masukkan kabupaten/kota',
                        'Data kabupaten/kota dapat terisi otomatis dari data SIM.',
                        'kabupaten'
                    ),
                    $this->selectField(
                        '5. Jenjang',
                        'jenjang',
                        ['PAUD/TK', 'SD', 'SMP', 'SMA', 'SMK', 'SLB'],
                        'Pilih jenjang',
                        'Pilih jenjang satuan pendidikan peserta.',
                        true
                    ),
                    $this->selectField(
                        '6. Komunitas/Forum',
                        'komunitas_forum',
                        ['KKG', 'MGMP', 'KKKS', 'MKKS', 'Komunitas Belajar'],
                        'Pilih komunitas/forum',
                        'Pilih komunitas atau forum tempat kegiatan diikuti.',
                        true
                    ),
                    $this->textField(
                        '7. Judul/Topik Hari Belajar Guru',
                        'judul_topik_hari_belajar_guru',
                        'Masukkan judul atau topik kegiatan',
                        'Tuliskan judul atau topik utama Hari Belajar Guru.'
                    ),
                    $this->dateField(
                        '8. Tanggal Pelaksanaan',
                        'tanggal_pelaksanaan',
                        'Pilih tanggal pelaksanaan kegiatan.'
                    ),
                    $this->textField(
                        '9. Nama Narasumber',
                        'nama_narasumber',
                        'Masukkan nama narasumber',
                        'Tuliskan nama narasumber kegiatan.'
                    ),
                    $this->selectField(
                        '10. Moda Kegiatan',
                        'moda_kegiatan',
                        ['Luring', 'Daring', 'Hybrid'],
                        'Pilih moda kegiatan',
                        'Pilih moda pelaksanaan kegiatan.'
                    ),
                ],
            ],
            [
                'judul_form' => 'B. Evaluasi Pelaksanaan Kegiatan',
                'kode_form' => 'FORM-EVALUASI-PELAKSANAAN',
                'deskripsi' => 'Berikan penilaian terhadap setiap pernyataan berikut dengan skala 1 = Kurang, 2 = Cukup, 3 = Baik, dan 4 = Sangat Baik.',
                'indikator_kode' => 'B',
                'indikator_label' => 'Kualitas pelaksanaan kegiatan',
                'is_scoreable' => true,
                'fields' => $this->scaledFields('evaluasi_pelaksanaan', [
                    '1. Kesesuaian kegiatan dengan tujuan Hari Belajar Guru.',
                    '2. Kesesuaian materi dengan kebutuhan pengembangan kompetensi guru.',
                    '3. Kejelasan tujuan kegiatan yang disampaikan kepada peserta.',
                    '4. Ketepatan waktu dan keteraturan pelaksanaan kegiatan.',
                    '5. Kesesuaian durasi kegiatan dengan materi yang diberikan.',
                    '6. Kualitas sarana, prasarana, atau platform yang digunakan.',
                    '7. Kesempatan peserta untuk berdiskusi dan berpartisipasi aktif.',
                    '8. Kegiatan memberikan ruang untuk berbagi pengalaman dan praktik baik.',
                    '9. Kegiatan mendorong peserta melakukan refleksi terhadap praktik pembelajaran.',
                    '10. Materi yang diberikan dapat diterapkan dalam tugas atau pembelajaran di satuan pendidikan.',
                    '11. Kegiatan mendorong kolaborasi antarpendidik.',
                    '12. Kegiatan memberikan wawasan atau pengetahuan baru bagi peserta.',
                    '13. Kegiatan memberikan manfaat nyata terhadap peningkatan kompetensi peserta.',
                    '14. Pelaksanaan kegiatan secara keseluruhan berjalan dengan baik.',
                ]),
            ],
            [
                'judul_form' => 'C. Evaluasi Narasumber',
                'kode_form' => 'FORM-EVALUASI-NARASUMBER',
                'deskripsi' => 'Gunakan skala yang sama: 1 = Kurang, 2 = Cukup, 3 = Baik, dan 4 = Sangat Baik.',
                'indikator_kode' => 'C',
                'indikator_label' => 'Kinerja narasumber',
                'is_scoreable' => true,
                'fields' => $this->scaledFields('evaluasi_narasumber', [
                    '1. Narasumber menguasai materi yang disampaikan.',
                    '2. Narasumber menyampaikan materi secara sistematis dan terstruktur.',
                    '3. Narasumber menggunakan bahasa yang jelas dan mudah dipahami.',
                    '4. Narasumber mampu mengaitkan materi dengan kebutuhan dan kondisi nyata peserta.',
                    '5. Narasumber memberikan contoh atau praktik yang relevan.',
                    '6. Narasumber mampu menciptakan suasana belajar yang interaktif.',
                    '7. Narasumber memberikan kesempatan kepada peserta untuk bertanya dan berdiskusi.',
                    '8. Narasumber mampu memberikan jawaban atau tanggapan yang jelas terhadap pertanyaan peserta.',
                    '9. Narasumber memanfaatkan media atau bahan pembelajaran secara efektif.',
                    '10. Narasumber mampu mengelola waktu penyampaian materi dengan baik.',
                    '11. Narasumber mampu memotivasi peserta untuk terus belajar dan mengembangkan kompetensi.',
                    '12. Narasumber mendorong peserta untuk menerapkan hasil kegiatan dalam praktik pembelajaran.',
                    '13. Secara keseluruhan, kinerja narasumber dalam kegiatan ini sangat baik.',
                ]),
            ],
            [
                'judul_form' => 'D. Kebermanfaatan dan Tindak Lanjut',
                'kode_form' => 'FORM-KEBERMANFAATAN-TINDAK-LANJUT',
                'deskripsi' => 'Pilih jawaban yang paling sesuai dengan manfaat yang dirasakan dan rencana tindak lanjut setelah mengikuti kegiatan.',
                'indikator_kode' => 'D',
                'indikator_label' => 'Kebermanfaatan dan tindak lanjut kegiatan',
                'is_scoreable' => true,
                'fields' => [
                    $this->scoredChoiceField(
                        '1. Setelah mengikuti Hari Belajar Guru ini, seberapa besar kegiatan memberikan manfaat bagi Anda?',
                        'manfaat_kegiatan',
                        [
                            ['label' => 'Sangat Bermanfaat', 'value' => '4', 'score' => 4, 'level_kompetensi' => 4],
                            ['label' => 'Bermanfaat', 'value' => '3', 'score' => 3, 'level_kompetensi' => 3],
                            ['label' => 'Cukup Bermanfaat', 'value' => '2', 'score' => 2, 'level_kompetensi' => 2],
                            ['label' => 'Kurang Bermanfaat', 'value' => '1', 'score' => 1, 'level_kompetensi' => 1],
                        ],
                    ),
                    $this->scoredChoiceField(
                        '2. Apakah Anda memperoleh pengetahuan atau keterampilan baru dari kegiatan ini?',
                        'pengetahuan_keterampilan_baru',
                        [
                            ['label' => 'Ya, sangat banyak', 'value' => '4', 'score' => 4, 'level_kompetensi' => 4],
                            ['label' => 'Ya, cukup banyak', 'value' => '3', 'score' => 3, 'level_kompetensi' => 3],
                            ['label' => 'Sedikit', 'value' => '2', 'score' => 2, 'level_kompetensi' => 2],
                            ['label' => 'Tidak', 'value' => '1', 'score' => 1, 'level_kompetensi' => 1],
                        ],
                    ),
                    $this->scoredChoiceField(
                        '3. Apakah materi yang diperoleh akan Anda terapkan dalam pembelajaran/tugas?',
                        'rencana_penerapan_materi',
                        [
                            ['label' => 'Ya, akan segera diterapkan', 'value' => '4', 'score' => 4, 'level_kompetensi' => 4],
                            ['label' => 'Ya, tetapi perlu penyesuaian', 'value' => '3', 'score' => 3, 'level_kompetensi' => 3],
                            ['label' => 'Belum tahu', 'value' => '2', 'score' => 2, 'level_kompetensi' => 2],
                            ['label' => 'Tidak', 'value' => '1', 'score' => 1, 'level_kompetensi' => 1],
                        ],
                    ),
                    $this->scoredChoiceField(
                        '4. Apakah kegiatan ini perlu dilanjutkan secara berkala?',
                        'kelanjutan_kegiatan',
                        [
                            ['label' => 'Sangat Perlu', 'value' => '4', 'score' => 4, 'level_kompetensi' => 4],
                            ['label' => 'Perlu', 'value' => '3', 'score' => 3, 'level_kompetensi' => 3],
                            ['label' => 'Cukup Perlu', 'value' => '2', 'score' => 2, 'level_kompetensi' => 2],
                            ['label' => 'Tidak Perlu', 'value' => '1', 'score' => 1, 'level_kompetensi' => 1],
                        ],
                    ),
                ],
            ],
            [
                'judul_form' => 'F. Penilaian Keseluruhan',
                'kode_form' => 'FORM-PENILAIAN-KESELURUHAN',
                'deskripsi' => 'Berikan penilaian secara keseluruhan terhadap kegiatan Hari Belajar Guru ini.',
                'indikator_kode' => 'F',
                'indikator_label' => 'Penilaian keseluruhan kegiatan',
                'is_scoreable' => true,
                'fields' => [
                    $this->scoredChoiceField(
                        'Bagaimana penilaian Anda secara keseluruhan terhadap kegiatan Hari Belajar Guru ini?',
                        'penilaian_keseluruhan',
                        [
                            ['label' => '⭐⭐⭐⭐ Sangat Baik', 'value' => '4', 'score' => 4, 'level_kompetensi' => 4],
                            ['label' => '⭐⭐⭐ Baik', 'value' => '3', 'score' => 3, 'level_kompetensi' => 3],
                            ['label' => '⭐⭐ Cukup', 'value' => '2', 'score' => 2, 'level_kompetensi' => 2],
                            ['label' => '⭐ Kurang', 'value' => '1', 'score' => 1, 'level_kompetensi' => 1],
                        ],
                    ),
                ],
            ],
        ];
    }

    /**
     * @param array<int, string> $labels
     * @return array<int, array<string, mixed>>
     */
    private function scaledFields(string $prefix, array $labels): array
    {
        return collect($labels)
            ->values()
            ->map(fn (string $label, int $index) => $this->scoredChoiceField(
                $label,
                $prefix.'_'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                $this->scaleOptions(),
            ))
            ->all();
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function scaleOptions(): array
    {
        return [
            ['label' => 'Kurang', 'value' => '1', 'score' => 1, 'level_kompetensi' => 1],
            ['label' => 'Cukup', 'value' => '2', 'score' => 2, 'level_kompetensi' => 2],
            ['label' => 'Baik', 'value' => '3', 'score' => 3, 'level_kompetensi' => 3],
            ['label' => 'Sangat Baik', 'value' => '4', 'score' => 4, 'level_kompetensi' => 4],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $options
     * @return array<string, mixed>
     */
    private function scoredChoiceField(string $label, string $name, array $options): array
    {
        return $this->field(
            $label,
            $name,
            'radio',
            $options,
            true,
            null,
            null,
            'Pilih satu jawaban. Skor: 1 = Kurang, 2 = Cukup, 3 = Baik, dan 4 = Sangat Baik.',
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
                    'scoring_note' => 'Skor diambil dari opsi jawaban pada skala 1 sampai 4.',
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
        bool $allowOtherInput = false
    ): array {
        return $this->field(
            $label,
            $name,
            'select',
            $options,
            true,
            $placeholder,
            null,
            $help,
            null,
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
                        '1' => 'Kurang',
                        '2' => 'Cukup',
                        '3' => 'Baik',
                        '4' => 'Sangat Baik',
                    ],
                ],
                'aggregation' => 'Rata-rata skor seluruh butir penilaian.',
                'source' => 'Evaluasi Pelaksanaan Hari Belajar Guru',
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @return array<string, mixed>
     */
    private function formScoringConfig(array $fields): array
    {
        return [
            'profile' => AssessmentInstrumentType::SKALA_LIKERT->value,
            'weight' => count($fields),
            'advanced_rules' => [
                'scale' => [
                    'min' => 1,
                    'max' => 4,
                ],
                'item_count' => count($fields),
                'form_formula' => 'Rata-rata skor seluruh jawaban pada form.',
            ],
        ];
    }
}
