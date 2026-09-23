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
            'petunjuk' => 'Lengkapi identitas kegiatan terlebih dahulu. Pada bagian B, pilih satu angka pada setiap pernyataan menggunakan skala 1 sampai 4. Saran/masukan pada bagian B bersifat opsional.',
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
                        '1. Kabupaten/Kota',
                        'kabupaten_kota',
                        $this->kabupatenOptions(),
                        'Pilih jawaban...',
                        'Data kabupaten/kota dapat terisi otomatis dari data SIM.',
                        false,
                        true,
                        'kabupaten'
                    ),
                    $this->selectField(
                        '2. Nama Narasumber',
                        'nama_narasumber',
                        [],
                        'Pilih narasumber atau Lainnya...',
                        'Pilih narasumber sesuai kabupaten/kota atau pilih Lainnya untuk menulis nama lain.',
                        true,
                        true,
                        null,
                        $this->narasumberByKabupatenConfig(),
                    ),
                    $this->textField(
                        '3. Nama Sekolah',
                        'nama_sekolah',
                        'Masukkan nama Sekolah',
                        'Nama sekolah dapat terisi otomatis dari data SIM.',
                        'satuan_pendidikan'
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
                'judul_form' => 'B. Evaluasi Narasumber',
                'kode_form' => 'FORM-EVALUASI-NARASUMBER',
                'deskripsi' => 'Berikan penilaian pada setiap pernyataan menggunakan skala angka 1 sampai 4. Saran/masukan dapat diisi pada bagian akhir.',
                'indikator_kode' => 'B',
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
        ?string $autofillSource = null,
        ?array $dependencyConfig = null
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
            $dependencyConfig,
        );
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    private function kabupatenOptions(): array
    {
        return [
            ['label' => 'Kab. Luwu Timur', 'value' => 'Kab. Luwu Timur'],
            ['label' => 'Kab. Luwu Utara', 'value' => 'Kab. Luwu Utara'],
            ['label' => 'Kab. Maros', 'value' => 'Kab. Maros'],
            ['label' => 'Kab. Enrekang', 'value' => 'Kab. Enrekang'],
            ['label' => 'Kota Parepare', 'value' => 'Kota Parepare'],
            ['label' => 'Kab. Sinjai', 'value' => 'Kab. Sinjai'],
            ['label' => 'Kab. Takalar', 'value' => 'Kab. Takalar'],
            ['label' => 'Kabupaten Wajo', 'value' => 'Kabupaten Wajo'],
            ['label' => 'Kab. Toraja Utara', 'value' => 'Kab. Toraja Utara'],
            ['label' => 'Kab. Gowa', 'value' => 'Kab. Gowa'],
            ['label' => 'Kab. Bantaeng', 'value' => 'Kab. Bantaeng'],
            ['label' => 'Kab. Kep. Selayar', 'value' => 'Kab. Kep. Selayar'],
            ['label' => 'Kab. Jeneponto', 'value' => 'Kab. Jeneponto'],
            ['label' => 'Kota Palopo', 'value' => 'Kota Palopo'],
        ];
    }

    /**
     * Mapping narasumber berdasarkan kabupaten/kota.
     *
     * @return array<string, mixed>
     */
    private function narasumberByKabupatenConfig(): array
    {
        return [
            'parent_field' => 'kabupaten_kota',
            'empty_behavior' => 'disabled',
            'reset_on_parent_change' => true,
            'options_by_parent' => [
                'Kabupaten Luwu Timur' => [
                    ['label' => 'Ninik', 'value' => 'ninik'],
                    ['label' => 'Nuraeni Amir', 'value' => 'nuraeni_amir'],
                ],
                'Kabupaten Luwu Utara' => [
                    ['label' => 'Sukimin', 'value' => 'sukimin'],
                    ['label' => 'Suparmin', 'value' => 'suparmin'],
                ],
                'Kabupaten Maros' => [
                    ['label' => 'Sitti Hajra', 'value' => 'sitti_hajra'],
                    ['label' => 'Mardiana Suyuti', 'value' => 'mardiana_suyuti'],
                ],
                'Kabupaten Enrekang' => [
                    ['label' => 'Miradiyah', 'value' => 'miradiyah'],
                    ['label' => 'Hairuddin', 'value' => 'hairuddin'],
                ],
                'Kota Parepare' => [
                    ['label' => 'Asmuddin', 'value' => 'asmuddin'],
                    ['label' => 'Syahriani Jarimollah', 'value' => 'syahriani_jarimollah'],
                ],
                'Kabupaten Sinjai' => [
                    ['label' => 'Adi Wijaya', 'value' => 'adi_wijaya'],
                    ['label' => 'Asrianingsih', 'value' => 'asrianingsih'],
                ],
                'Kabupaten Takalar' => [
                    ['label' => 'Abdul Azis', 'value' => 'abdul_azis'],
                    ['label' => 'Sri Rahayu PM', 'value' => 'sri_rahayu_pm'],
                ],
                'Kabupaten Wajo' => [
                    ['label' => 'Hernawati Syam', 'value' => 'hernawati_syam'],
                    ['label' => 'Firna Sari', 'value' => 'firna_sari'],
                ],
                'Kabupaten Toraja Utara' => [
                    ['label' => 'Marwa', 'value' => 'marwa'],
                    ['label' => 'Ilyas Kalla Lembang', 'value' => 'ilyas_kalla_lembang'],
                ],
                'Kabupaten Gowa' => [
                    ['label' => 'Dr. Irlidya', 'value' => 'dr_irlidya'],
                    ['label' => 'Hasanuddin Haris', 'value' => 'hasanuddin_haris'],
                ],
                'Kabupaten Bantaeng' => [
                    ['label' => 'Abdul Waqif', 'value' => 'abdul_waqif'],
                    ['label' => 'Bahtiar', 'value' => 'bahtiar'],
                ],
                'Kabupaten Kep. Selayar' => [
                    ['label' => 'Santy Arbi', 'value' => 'santy_arbi'],
                    ['label' => 'Herman', 'value' => 'herman'],
                ],
                'Kabupaten Jeneponto' => [
                    ['label' => 'Herawati', 'value' => 'herawati'],
                    ['label' => 'Syaharuddin', 'value' => 'syaharuddin'],
                ],
                'Kota Palopo' => [
                    ['label' => 'Nursaidawati', 'value' => 'nursaidawati'],
                    ['label' => 'Faliha', 'value' => 'faliha'],
                ],
            ],
        ];
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
        array $validationExtra = [],
        ?array $dependencyConfig = null
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
            'dependency_config' => $dependencyConfig,
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
