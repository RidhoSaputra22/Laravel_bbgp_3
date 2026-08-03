<?php

namespace Tests\Unit;

use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Services\Assessment\AssessmentScoringService;
use App\Support\Assessment\LikertScale;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AssessmentScoringServiceTest extends TestCase
{
    public function test_it_builds_weighted_competency_scores_with_rubric_30_40_30_weights(): void
    {
        $attempt = new AssessmentAttempt([
            'structure_snapshot' => [
                'assessments' => [
                    [
                        'id' => 101,
                        'kode_assessment' => 'ASM-PORT',
                        'judul' => 'Portofolio Kompetensi Guru',
                        'instrument_type' => 'portofolio',
                        'scoring_config' => ['weight' => 0.30],
                        'forms' => [
                            [
                                'id' => 201,
                                'judul_form' => 'Portofolio Pedagogik',
                                'kode_form' => 'PORT-PED',
                                'kompetensi' => 'pedagogik',
                                'indikator_kode' => 'P2',
                                'indikator_label' => 'Praktik pedagogik',
                                'is_scoreable' => true,
                                'scoring_config' => ['profile' => 'generic'],
                                'fields' => [
                                    [
                                        'id' => 301,
                                        'label' => 'Portofolio Pedagogik',
                                        'tipe_field' => 'textarea',
                                        'scoring_config' => ['weight' => 100],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 102,
                        'kode_assessment' => 'ASM-PG',
                        'judul' => 'Pilihan Ganda Kompleks Kompetensi Guru',
                        'instrument_type' => 'pilihan_ganda_kompleks',
                        'scoring_config' => ['weight' => 0.40],
                        'forms' => [
                            [
                                'id' => 202,
                                'judul_form' => 'PG Pedagogik',
                                'kode_form' => 'PG-PED',
                                'kompetensi' => 'pedagogik',
                                'indikator_kode' => 'I-PED',
                                'indikator_label' => 'Indeks Pedagogik',
                                'is_scoreable' => true,
                                'scoring_config' => ['profile' => 'generic'],
                                'fields' => [
                                    ['id' => 302, 'label' => 'Soal 1', 'tipe_field' => 'radio', 'scoring_config' => ['weight' => 1]],
                                    ['id' => 303, 'label' => 'Soal 2', 'tipe_field' => 'radio', 'scoring_config' => ['weight' => 1]],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 103,
                        'kode_assessment' => 'ASM-SK',
                        'judul' => 'Studi Kasus Kompetensi Guru',
                        'instrument_type' => 'studi_kasus',
                        'scoring_config' => ['weight' => 0.30],
                        'forms' => [
                            [
                                'id' => 203,
                                'judul_form' => 'Kasus Pedagogik',
                                'kode_form' => 'SK-PED',
                                'kompetensi' => 'pedagogik',
                                'indikator_kode' => 'KASUS-PED',
                                'indikator_label' => 'Kasus Pedagogik',
                                'is_scoreable' => true,
                                'scoring_config' => ['profile' => 'generic'],
                                'fields' => [
                                    ['id' => 304, 'label' => 'Analisis Kasus', 'tipe_field' => 'textarea', 'scoring_config' => ['weight' => 100]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $attempt->setRelation('answers', new Collection([
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 301,
                'answer_text' => 'Portofolio pedagogik lengkap',
                'auto_score' => 4.0,
            ]),
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 302,
                'answer_text' => 'A',
                'answer_payload' => ['value' => 'A'],
                'auto_score' => 3.0,
            ]),
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 303,
                'answer_text' => 'B',
                'answer_payload' => ['value' => 'B'],
                'auto_score' => 4.0,
            ]),
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 304,
                'answer_text' => 'Analisis kasus pedagogik lengkap',
                'auto_score' => 5.0,
            ]),
        ]));

        $summary = $this->makeService()->buildSummary($attempt);
        $pedagogik = collect($summary['competencies'])->firstWhere('key', 'pedagogik');

        $this->assertSame('complete', $summary['status']);
        $this->assertSame('4.10', data_get($pedagogik, 'formatted_score'));
        $this->assertSame('82.00', number_format((float) data_get($pedagogik, 'percent_score'), 2));
        $this->assertSame('4.10', data_get($summary, 'overall.formatted_score'));
        $this->assertSame('Mumpuni', data_get($summary, 'overall.level.short_label'));
    }

    public function test_it_keeps_system_score_even_if_assessor_score_exists(): void
    {
        $attempt = new AssessmentAttempt([
            'structure_snapshot' => [
                'assessments' => [
                    [
                        'id' => 101,
                        'kode_assessment' => 'ASM-SK',
                        'judul' => 'Studi Kasus Kompetensi Guru',
                        'instrument_type' => 'studi_kasus',
                        'forms' => [
                            [
                                'id' => 201,
                                'judul_form' => 'Kasus Pedagogik',
                                'kode_form' => 'SK-PED',
                                'kompetensi' => 'pedagogik',
                                'indikator_kode' => 'K1',
                                'indikator_label' => 'Kasus Pedagogik',
                                'is_scoreable' => true,
                                'scoring_config' => ['profile' => 'generic'],
                                'fields' => [
                                    ['id' => 301, 'label' => 'Analisis', 'tipe_field' => 'textarea'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $attempt->setRelation('answers', new Collection([
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 301,
                'answer_text' => 'Jawaban peserta',
                'auto_score' => 4.5,
                'assessor_score' => 2,
            ]),
        ]));

        $summary = $this->makeService()->buildSummary($attempt);
        $form = $summary['forms'][0];

        $this->assertSame('4.50', $form['formatted_score']);
        $this->assertSame('auto_score', data_get($form, 'items.0.score_source'));
        $this->assertSame('Ahli', data_get($summary, 'overall.level.short_label'));
    }

    public function test_it_auto_scores_semantic_text_without_waiting_assessor_when_configuration_is_available(): void
    {
        $attempt = new AssessmentAttempt([
            'structure_snapshot' => [
                'assessments' => [
                    [
                        'id' => 101,
                        'kode_assessment' => 'ASM-SK',
                        'judul' => 'Studi Kasus Kompetensi Guru',
                        'instrument_type' => 'studi_kasus',
                        'forms' => [
                            [
                                'id' => 201,
                                'judul_form' => 'Kasus Pedagogik',
                                'kode_form' => 'SK-PED',
                                'kompetensi' => 'pedagogik',
                                'indikator_kode' => 'K1',
                                'indikator_label' => 'Identifikasi masalah',
                                'is_scoreable' => true,
                                'scoring_config' => ['profile' => 'generic'],
                                'fields' => [
                                    [
                                        'id' => 301,
                                        'label' => 'Identifikasi Masalah',
                                        'tipe_field' => 'textarea',
                                        'scoring_config' => [
                                            'enabled' => true,
                                            'method' => 'semantic_similarity',
                                            'weight' => 20,
                                            'reference_answer' => 'Jawaban menyoroti pembelajaran berpusat pada peserta didik, partisipasi aktif, asesmen, dan umpan balik.',
                                            'keyword_groups' => [
                                                ['pembelajaran', 'peserta'],
                                                ['partisipasi', 'aktif'],
                                                ['asesmen'],
                                                ['umpan', 'balik'],
                                            ],
                                            'min_words' => 12,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $attempt->setRelation('answers', new Collection([
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 301,
                'answer_text' => 'Guru perlu merancang pembelajaran yang berpusat pada peserta didik, mendorong partisipasi aktif, serta menyiapkan asesmen dan umpan balik yang jelas.',
                'answer_payload' => [
                    'type' => 'textarea',
                    'value' => 'Guru perlu merancang pembelajaran yang berpusat pada peserta didik, mendorong partisipasi aktif, serta menyiapkan asesmen dan umpan balik yang jelas.',
                ],
            ]),
        ]));

        $summary = $this->makeService()->buildSummary($attempt);
        $form = $summary['forms'][0];

        $this->assertSame('complete', $summary['status']);
        $this->assertSame(0, data_get($summary, 'manual_review.pending_items'));
        $this->assertNotNull($form['score']);
        $this->assertGreaterThan(3.40, (float) $form['score']);
        $this->assertSame('auto_semantic_similarity', data_get($form, 'items.0.score_source'));
    }

    public function test_it_gives_zero_score_for_study_case_when_semantic_similarity_is_below_ten_percent(): void
    {
        $attempt = new AssessmentAttempt([
            'structure_snapshot' => [
                'assessments' => [
                    [
                        'id' => 101,
                        'kode_assessment' => 'ASM-SK',
                        'judul' => 'Studi Kasus Kompetensi Guru',
                        'instrument_type' => 'studi_kasus',
                        'forms' => [
                            [
                                'id' => 201,
                                'judul_form' => 'Kasus Pedagogik',
                                'kode_form' => 'SK-PED',
                                'kompetensi' => 'pedagogik',
                                'indikator_kode' => 'K1',
                                'indikator_label' => 'Identifikasi masalah',
                                'is_scoreable' => true,
                                'scoring_config' => ['profile' => 'study_case_default'],
                                'fields' => [
                                    [
                                        'id' => 301,
                                        'label' => 'Identifikasi Masalah',
                                        'tipe_field' => 'textarea',
                                        'scoring_config' => [
                                            'enabled' => true,
                                            'profile' => 'study_case_default',
                                            'method' => 'semantic_similarity',
                                            'weight' => 20,
                                            'reference_answer' => 'Guru perlu menerapkan pembelajaran aktif, asesmen formatif, dan umpan balik yang jelas bagi peserta didik.',
                                            'keyword_groups' => [
                                                ['pembelajaran', 'aktif'],
                                                ['asesmen', 'formatif'],
                                                ['umpan', 'balik'],
                                            ],
                                            'min_words' => 12,
                                            'advanced_rules' => [
                                                'semantic_zero_threshold' => 0.10,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $attempt->setRelation('answers', new Collection([
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 301,
                'answer_text' => 'trado depromo caceus',
                'answer_payload' => [
                    'type' => 'textarea',
                    'value' => 'trado depromo caceus',
                ],
            ]),
        ]));

        $summary = $this->makeService()->buildSummary($attempt);
        $form = $summary['forms'][0];

        $this->assertSame('complete', $summary['status']);
        $this->assertSame('0.00', $form['formatted_score']);
        $this->assertSame(0.0, (float) $form['score']);
        $this->assertNull(data_get($form, 'level.short_label'));
        $this->assertStringContainsString('skor otomatis menjadi 0', (string) data_get($form, 'items.0.auto_reason'));
    }

    public function test_it_scores_likert_negative_statements_with_reverse_formula(): void
    {
        $attempt = new AssessmentAttempt([
            'structure_snapshot' => [
                'assessments' => [
                    [
                        'id' => 101,
                        'kode_assessment' => 'ASM-LIKERT',
                        'judul' => 'Refleksi Kompetensi Guru',
                        'instrument_type' => 'skala_likert',
                        'forms' => [
                            [
                                'id' => 201,
                                'judul_form' => 'Refleksi Pedagogik',
                                'kode_form' => 'LIKERT-PED',
                                'kompetensi' => 'pedagogik',
                                'indikator_kode' => 'P1',
                                'indikator_label' => 'Refleksi Pedagogik',
                                'is_scoreable' => true,
                                'fields' => [
                                    [
                                        'id' => 301,
                                        'label' => 'Saya menyusun pembelajaran sesuai kebutuhan peserta didik.',
                                        'tipe_field' => LikertScale::FIELD_TYPE,
                                        'opsi_field' => LikertScale::defaultOptions(),
                                        'scoring_config' => [
                                            'enabled' => true,
                                            'method' => LikertScale::SCORING_METHOD,
                                        ],
                                    ],
                                    [
                                        'id' => 302,
                                        'label' => 'Saya jarang menyesuaikan pembelajaran dengan kondisi kelas.',
                                        'tipe_field' => LikertScale::FIELD_TYPE,
                                        'opsi_field' => LikertScale::defaultOptions(),
                                        'scoring_config' => [
                                            'enabled' => true,
                                            'method' => LikertScale::SCORING_METHOD,
                                            'is_negative_statement' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $attempt->setRelation('answers', new Collection([
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 301,
                'answer_text' => '4',
                'answer_payload' => [
                    'type' => LikertScale::FIELD_TYPE,
                    'value' => '4',
                    'label' => 'Setuju',
                    'score' => 4,
                ],
            ]),
            new AssessmentAttemptAnswer([
                'assessment_form_field_id' => 302,
                'answer_text' => '4',
                'answer_payload' => [
                    'type' => LikertScale::FIELD_TYPE,
                    'value' => '4',
                    'label' => 'Setuju',
                    'score' => 4,
                ],
            ]),
        ]));

        $summary = $this->makeService()->buildSummary($attempt);

        $this->assertSame('3.00', data_get($summary, 'forms.0.formatted_score'));
        $this->assertSame(4.0, data_get($summary, 'forms.0.items.0.score'));
        $this->assertSame(2.0, data_get($summary, 'forms.0.items.1.score'));
        $this->assertSame('6 - X', data_get($summary, 'forms.0.items.1.auto_metadata.formula'));
        $this->assertSame('50.00', data_get($summary, 'overall.formatted_index_score'));
        $this->assertSame('Sedang', data_get($summary, 'overall.likert_category.label'));
    }

    private function makeService(): AssessmentScoringService
    {
        return app(AssessmentScoringService::class);
    }
}
