<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentInstrumentType;
use App\Enum\KompetensiGuru;
use App\Enum\LevelKompetensi;
use App\Models\AssessmentAttempt;
use App\Services\Assessment\Engine\BaseInstrumentScoringEngine;
use App\Services\Assessment\Engine\PilihanGandaKompleksScoringEngine;
use App\Services\Assessment\Engine\PortofolioScoringEngine;
use App\Services\Assessment\Engine\StudiKasusScoringEngine;
use App\Support\Assessment\AssessmentStructureMetadataResolver;
use App\Support\Assessment\ScoringConfigNormalizer;
use Illuminate\Support\Collection;

class AssessmentScoringService
{
    private const SUMMARY_VERSION = 2;

    public function __construct(
        private readonly AssessmentStructureMetadataResolver $metadataResolver,
        private readonly ScoringConfigNormalizer $configNormalizer,
        private readonly PilihanGandaKompleksScoringEngine $pilihanGandaKompleksScoringEngine,
        private readonly PortofolioScoringEngine $portofolioScoringEngine,
        private readonly StudiKasusScoringEngine $studiKasusScoringEngine
    ) {}

    public function summaryVersion(): int
    {
        return self::SUMMARY_VERSION;
    }

    public function buildSummary(AssessmentAttempt $attempt): array
    {
        $snapshot = $attempt->structure_snapshot ?? [];
        $answers = $attempt->relationLoaded('answers')
            ? $attempt->answers
            : $attempt->answers()->get();
        $answerMap = $answers->keyBy('assessment_form_field_id');
        $formSummaries = [];
        $totalAnsweredScoreableItems = 0;
        $scoredItems = 0;
        $pendingManualItems = 0;

        foreach ($snapshot['assessments'] ?? [] as $assessmentData) {
            $assessmentMeta = $this->metadataResolver->decorateAssessment($assessmentData);
            $assessmentConfig = $this->configNormalizer->normalizeAssessment($assessmentMeta);
            $instrument = AssessmentInstrumentType::tryFromMixed($assessmentMeta['instrument_type'] ?? null);

            if (! $instrument) {
                continue;
            }

            $engine = $this->resolveInstrumentEngine($instrument);

            foreach ($assessmentMeta['forms'] ?? [] as $formData) {
                $formMeta = $this->metadataResolver->decorateForm($formData, $assessmentMeta);

                if (! ($formMeta['is_scoreable'] ?? false)) {
                    continue;
                }

                $kompetensi = KompetensiGuru::tryFromMixed($formMeta['kompetensi'] ?? null);
                $engineSummary = $engine->buildFormSummary($assessmentMeta, $formMeta, $answerMap);
                $score = $engineSummary['score'];
                $displayScore = $engineSummary['display_score'];
                $items = $engineSummary['items'] ?? [];
                $answeredItems = (int) ($engineSummary['answered_items'] ?? 0);
                $availableItemsCount = collect($items)
                    ->filter(fn (array $item) => $item['answered'] || $item['score'] !== null)
                    ->count();
                $scoredCount = collect($items)
                    ->filter(fn (array $item) => $item['score'] !== null && ! ($item['manual_pending'] ?? false))
                    ->count();
                $manualPendingCount = (int) collect($items)
                    ->filter(function (array $item) {
                        $expectsSystemScore = (bool) data_get($item, 'scoring_config.enabled', false);

                        return $item['answered'] && (($item['manual_pending'] ?? false) || ($expectsSystemScore && $item['score'] === null));
                    })
                    ->count();

                $totalAnsweredScoreableItems += max($availableItemsCount, $answeredItems);
                $scoredItems += $scoredCount;
                $pendingManualItems += $manualPendingCount;

                $formSummaries[] = [
                    'assessment_id' => $assessmentMeta['id'] ?? null,
                    'assessment_title' => $assessmentMeta['judul'] ?? 'Assessment',
                    'assessment_code' => $assessmentMeta['kode_assessment'] ?? null,
                    'instrument_type' => $instrument->value,
                    'instrument_label' => $instrument->label(),
                    'instrument_weight' => (float) ($assessmentConfig['weight'] ?? $instrument->weight()),
                    'kompetensi' => $kompetensi?->value,
                    'kompetensi_label' => $kompetensi?->label(),
                    'indikator_kode' => $formMeta['indikator_kode'] ?? null,
                    'indikator_label' => $formMeta['indikator_label'] ?? null,
                    'form_id' => $formMeta['id'] ?? null,
                    'form_title' => $formMeta['judul_form'] ?? 'Form',
                    'form_code' => $formMeta['kode_form'] ?? null,
                    'score' => $score,
                    'display_score' => $displayScore,
                    'formatted_score' => $this->formatScore($score),
                    'display_formatted_score' => $this->formatScore($displayScore),
                    'level' => $this->serializeLevel($score),
                    'form_weight' => (float) data_get($engineSummary, 'form_config.weight', 1),
                    'answered_items' => $answeredItems,
                    'total_items' => (int) ($engineSummary['total_items'] ?? count($items)),
                    'scored_items' => $scoredCount,
                    'unanswered_items' => max((int) ($engineSummary['total_items'] ?? count($items)) - $answeredItems, 0),
                    'pending_manual_items' => $manualPendingCount,
                    'exclude_from_competency' => (bool) data_get($engineSummary, 'form_config.exclude_from_competency', false),
                    'is_complete' => (bool) ($engineSummary['is_complete'] ?? false),
                    'verification_gap_threshold' => (float) ($assessmentConfig['verification_gap_threshold'] ?? 1.5),
                    'empty_response_threshold_percent' => (float) ($assessmentConfig['empty_response_threshold_percent'] ?? 10),
                    'items' => $items,
                    'form_config' => $engineSummary['form_config'] ?? [],
                ];
            }
        }

        $indicatorSummaries = $this->buildIndicatorSummaries($formSummaries);
        $instrumentSummaries = $this->buildInstrumentSummaries($indicatorSummaries);
        $competencySummaries = $this->buildCompetencySummaries($instrumentSummaries);
        $overallSummary = $this->buildOverallSummary($competencySummaries);
        $verificationAlerts = $this->buildVerificationAlerts($competencySummaries);

        return [
            'summary_version' => self::SUMMARY_VERSION,
            'status' => $this->resolveScoringStatus($totalAnsweredScoreableItems, $pendingManualItems),
            'status_label' => $this->resolveScoringStatusLabel($totalAnsweredScoreableItems, $pendingManualItems),
            'status_description' => $this->resolveScoringStatusDescription(
                $totalAnsweredScoreableItems,
                $pendingManualItems,
                $verificationAlerts
            ),
            'manual_review' => [
                'total_items' => $totalAnsweredScoreableItems,
                'scored_items' => $scoredItems,
                'pending_items' => $pendingManualItems,
                'completed_items' => max($totalAnsweredScoreableItems - $pendingManualItems, 0),
            ],
            'system_scoring' => [
                'total_items' => $totalAnsweredScoreableItems,
                'scored_items' => $scoredItems,
                'pending_items' => $pendingManualItems,
                'completed_items' => max($totalAnsweredScoreableItems - $pendingManualItems, 0),
            ],
            'verification_alerts' => $verificationAlerts,
            'weight_reference' => collect(AssessmentInstrumentType::cases())
                ->map(fn (AssessmentInstrumentType $instrument) => [
                    'key' => $instrument->value,
                    'label' => $instrument->label(),
                    'weight' => $instrument->weight(),
                    'weight_percent' => (int) round($instrument->weight() * 100),
                ])
                ->values()
                ->all(),
            'overall' => $overallSummary,
            'competencies' => $competencySummaries,
            'forms' => array_values($formSummaries),
            'indicators' => array_values($indicatorSummaries),
            'instruments' => array_values($instrumentSummaries),
            'development_recommendations' => $this->buildDevelopmentRecommendations($competencySummaries),
            'narrative' => $this->buildNarrative($overallSummary, $competencySummaries, $pendingManualItems, $verificationAlerts),
            'career_recommendations' => $this->buildCareerRecommendations($overallSummary, $competencySummaries),
            'radar_chart' => [
                'max_score' => 5,
                'labels' => collect(KompetensiGuru::cases())->map(fn ($case) => $case->label())->all(),
                'datasets' => collect(KompetensiGuru::cases())->map(function (KompetensiGuru $kompetensi) use ($competencySummaries) {
                    $summary = collect($competencySummaries)->firstWhere('key', $kompetensi->value);

                    return [
                        'key' => $kompetensi->value,
                        'label' => $kompetensi->label(),
                        'score' => $summary['score'] ?? 0.0,
                        'formatted_score' => $summary['formatted_score'] ?? null,
                        'is_available' => $summary['score'] !== null,
                    ];
                })->all(),
            ],
        ];
    }

    private function buildIndicatorSummaries(array $formSummaries): array
    {
        return collect($formSummaries)
            ->groupBy(function (array $formSummary) {
                return implode('|', [
                    $formSummary['instrument_type'],
                    $formSummary['kompetensi'] ?: 'tanpa-kompetensi',
                    $formSummary['indikator_kode'] ?: 'form-'.$formSummary['form_id'],
                ]);
            })
            ->map(function (Collection $forms) {
                $first = $forms->first();
                $score = $this->weightedAverageItems($forms, 'score', 'form_weight');
                $displayScore = $this->weightedAverageItems($forms, 'display_score', 'form_weight');
                $aggregationWeight = $forms
                    ->filter(fn ($form) => $form['score'] !== null)
                    ->sum(fn ($form) => max((float) ($form['form_weight'] ?? 1), 0.01));

                return [
                    'instrument_type' => $first['instrument_type'],
                    'instrument_label' => $first['instrument_label'],
                    'instrument_weight' => $first['instrument_weight'],
                    'kompetensi' => $first['kompetensi'],
                    'kompetensi_label' => $first['kompetensi_label'],
                    'indikator_kode' => $first['indikator_kode'],
                    'indikator_label' => $first['indikator_label'] ?: $first['form_title'],
                    'score' => $score,
                    'display_score' => $displayScore,
                    'formatted_score' => $this->formatScore($score),
                    'display_formatted_score' => $this->formatScore($displayScore),
                    'level' => $this->serializeLevel($score),
                    'forms' => $forms->values()->all(),
                    'aggregation_weight' => round((float) $aggregationWeight, 2),
                    'pending_manual_items' => (int) $forms->sum('pending_manual_items'),
                    'answered_items' => (int) $forms->sum('answered_items'),
                    'total_items' => (int) $forms->sum('total_items'),
                    'scored_items' => (int) $forms->sum('scored_items'),
                    'exclude_from_competency' => $forms->every(fn ($form) => (bool) ($form['exclude_from_competency'] ?? false)),
                    'is_complete' => $forms->every(fn ($form) => (bool) $form['is_complete']),
                    'verification_gap_threshold' => (float) ($first['verification_gap_threshold'] ?? 1.5),
                    'empty_response_threshold_percent' => (float) ($first['empty_response_threshold_percent'] ?? 10),
                ];
            })
            ->values()
            ->all();
    }

    private function buildInstrumentSummaries(array $indicatorSummaries): array
    {
        return collect($indicatorSummaries)
            ->groupBy(function (array $indicatorSummary) {
                return implode('|', [
                    $indicatorSummary['instrument_type'],
                    $indicatorSummary['kompetensi'] ?: 'tanpa-kompetensi',
                ]);
            })
            ->map(function (Collection $indicators) {
                $first = $indicators->first();
                $score = $this->weightedAverageItems($indicators, 'score', 'aggregation_weight');
                $displayScore = $this->weightedAverageItems($indicators, 'display_score', 'aggregation_weight');

                return [
                    'instrument_type' => $first['instrument_type'],
                    'instrument_label' => $first['instrument_label'],
                    'kompetensi' => $first['kompetensi'],
                    'kompetensi_label' => $first['kompetensi_label'],
                    'base_weight' => (float) ($first['instrument_weight'] ?? AssessmentInstrumentType::tryFromMixed($first['instrument_type'])?->weight() ?? 0),
                    'score' => $score,
                    'display_score' => $displayScore,
                    'formatted_score' => $this->formatScore($score),
                    'display_formatted_score' => $this->formatScore($displayScore),
                    'level' => $this->serializeLevel($score),
                    'indicators' => $indicators->values()->all(),
                    'pending_manual_items' => (int) $indicators->sum('pending_manual_items'),
                    'answered_items' => (int) $indicators->sum('answered_items'),
                    'total_items' => (int) $indicators->sum('total_items'),
                    'scored_items' => (int) $indicators->sum('scored_items'),
                    'unanswered_items' => max((int) $indicators->sum('total_items') - (int) $indicators->sum('answered_items'), 0),
                    'exclude_from_competency' => $indicators->every(fn ($indicator) => (bool) ($indicator['exclude_from_competency'] ?? false)),
                    'verification_gap_threshold' => (float) ($first['verification_gap_threshold'] ?? 1.5),
                    'empty_response_threshold_percent' => (float) ($first['empty_response_threshold_percent'] ?? 10),
                ];
            })
            ->values()
            ->all();
    }

    private function buildCompetencySummaries(array $instrumentSummaries): array
    {
        return collect(KompetensiGuru::cases())
            ->map(function (KompetensiGuru $kompetensi) use ($instrumentSummaries) {
                $items = collect($instrumentSummaries)
                    ->where('kompetensi', $kompetensi->value)
                    ->values();
                $availableItems = $items
                    ->filter(fn ($item) => $item['score'] !== null && ! ($item['exclude_from_competency'] ?? false))
                    ->values();
                $activeWeightTotal = (float) $availableItems->sum('base_weight');
                $weightedScore = $activeWeightTotal > 0
                    ? $availableItems->sum(function ($item) use ($activeWeightTotal) {
                        return ((float) $item['score']) * (((float) $item['base_weight']) / $activeWeightTotal);
                    })
                    : null;
                $verificationReasons = $this->buildCompetencyVerificationReasons($availableItems);
                $recommendation = $this->resolveRecommendationDetails($weightedScore);

                return [
                    'key' => $kompetensi->value,
                    'label' => $kompetensi->label(),
                    'score' => $weightedScore !== null ? round((float) $weightedScore, 2) : null,
                    'formatted_score' => $this->formatScore($weightedScore),
                    'percent_score' => $weightedScore !== null ? round((((float) $weightedScore) / 5) * 100, 2) : null,
                    'level' => $this->serializeLevel($weightedScore),
                    'active_weight_total' => $activeWeightTotal > 0 ? round($activeWeightTotal, 2) : 0.0,
                    'active_weight_percent' => $activeWeightTotal > 0 ? (int) round($activeWeightTotal * 100) : 0,
                    'recommendation_category' => $recommendation['category'],
                    'recommendation_focus' => $recommendation['focus'],
                    'recommendation_support' => $recommendation['support'],
                    'recommendation_description' => $recommendation['description'],
                    'needs_verification' => $verificationReasons !== [],
                    'verification_reasons' => $verificationReasons,
                    'instruments' => $items->map(function (array $item) use ($activeWeightTotal) {
                        $normalizedWeight = $activeWeightTotal > 0 && $item['score'] !== null
                            ? round(((float) $item['base_weight']) / $activeWeightTotal, 4)
                            : null;

                        return array_merge($item, [
                            'active_weight' => $normalizedWeight,
                            'active_weight_percent' => $normalizedWeight !== null
                                ? (int) round($normalizedWeight * 100)
                                : null,
                        ]);
                    })->values()->all(),
                    'pending_manual_items' => (int) $items->sum('pending_manual_items'),
                ];
            })
            ->values()
            ->all();
    }

    private function buildOverallSummary(array $competencySummaries): array
    {
        $availableScores = collect($competencySummaries)
            ->pluck('score')
            ->filter(fn ($score) => $score !== null)
            ->map(fn ($score) => (float) $score)
            ->values()
            ->all();
        $overallScore = $this->average($availableScores);

        return [
            'score' => $overallScore,
            'formatted_score' => $this->formatScore($overallScore),
            'percent_score' => $overallScore !== null ? round(($overallScore / 5) * 100, 2) : null,
            'level' => $this->serializeLevel($overallScore),
            'available_competencies' => count($availableScores),
        ];
    }

    private function buildDevelopmentRecommendations(array $competencySummaries): array
    {
        return collect($competencySummaries)
            ->filter(fn ($item) => $item['score'] !== null)
            ->sortBy('score')
            ->map(function (array $item) {
                return [
                    'kompetensi' => $item['key'],
                    'label' => $item['label'],
                    'score' => $item['score'],
                    'formatted_score' => $item['formatted_score'],
                    'category' => $item['recommendation_category'],
                    'focus' => $item['recommendation_focus'],
                    'support' => $item['recommendation_support'],
                    'description' => $item['recommendation_description'],
                    'needs_verification' => $item['needs_verification'],
                ];
            })
            ->values()
            ->all();
    }

    private function buildNarrative(
        array $overallSummary,
        array $competencySummaries,
        int $pendingManualItems,
        array $verificationAlerts
    ): string {
        if ($overallSummary['score'] === null) {
            return 'Hasil penilaian belum dapat dihitung karena belum ada komponen skor yang tersedia pada assessment ini.';
        }

        $availableCompetencies = collect($competencySummaries)
            ->filter(fn ($item) => $item['score'] !== null)
            ->sortBy('score')
            ->values();
        $lowest = $availableCompetencies->first();
        $highest = $availableCompetencies->last();
        $overallLevel = $overallSummary['level']['short_label'] ?? 'Belum terpetakan';
        $overallScore = $overallSummary['formatted_score'] ?? '-';
        $parts = [
            "Secara umum profil kompetensi guru berada pada {$overallLevel} dengan skor {$overallScore}.",
        ];

        if ($highest && $lowest) {
            $parts[] = "Area terkuat saat ini berada pada kompetensi {$highest['label']}, sedangkan penguatan utama perlu difokuskan pada kompetensi {$lowest['label']}.";
        }

        if ($pendingManualItems > 0) {
            $parts[] = "Masih ada {$pendingManualItems} jawaban yang belum berhasil dihitung otomatis oleh sistem.";
        }

        if ($verificationAlerts !== []) {
            $parts[] = 'Beberapa kompetensi menunjukkan selisih antarinstrumen atau respons kosong yang cukup tinggi sehingga tetap perlu klarifikasi lanjutan.';
        }

        return implode(' ', $parts);
    }

    private function buildCareerRecommendations(array $overallSummary, array $competencySummaries): array
    {
        if ($overallSummary['score'] === null) {
            return [];
        }

        $availableCompetencies = collect($competencySummaries)
            ->filter(fn ($item) => $item['score'] !== null)
            ->sortByDesc('score')
            ->values();
        $topLabels = $availableCompetencies->take(2)->pluck('key')->all();
        $recommendations = [];

        if (in_array(KompetensiGuru::PEDAGOGIK->value, $topLabels, true) && in_array(KompetensiGuru::PROFESIONAL->value, $topLabels, true)) {
            $recommendations[] = [
                'title' => 'Guru Inti / Fasilitator Pembelajaran',
                'reason' => 'Kekuatan pedagogik dan profesional mendukung peran pembinaan pembelajaran, pendampingan sejawat, dan penguatan praktik kelas.',
            ];
        }

        if (in_array(KompetensiGuru::SOSIAL->value, $topLabels, true) && in_array(KompetensiGuru::KEPRIBADIAN->value, $topLabels, true)) {
            $recommendations[] = [
                'title' => 'Wali Kelas / Mentor Guru',
                'reason' => 'Kombinasi kompetensi sosial dan kepribadian yang baik cocok untuk peran pendampingan, komunikasi, dan penguatan budaya belajar yang sehat.',
            ];
        }

        if ($overallSummary['score'] !== null && $overallSummary['score'] >= 4.20) {
            $recommendations[] = [
                'title' => 'Calon Kepala Sekolah / Pengembang Program',
                'reason' => 'Profil keseluruhan menunjukkan kesiapan untuk mengambil peran kepemimpinan akademik dan pengembangan program pendidikan.',
            ];
        }

        if ($recommendations === []) {
            $recommendations[] = [
                'title' => 'Penggerak Komunitas Belajar',
                'reason' => 'Profil kompetensi dapat diarahkan untuk memperkuat komunitas belajar, kolaborasi sejawat, dan perbaikan praktik secara bertahap.',
            ];
        }

        return collect($recommendations)
            ->unique('title')
            ->values()
            ->all();
    }

    private function buildVerificationAlerts(array $competencySummaries): array
    {
        return collect($competencySummaries)
            ->filter(fn ($summary) => (bool) ($summary['needs_verification'] ?? false))
            ->map(fn ($summary) => [
                'kompetensi' => $summary['key'],
                'label' => $summary['label'],
                'reasons' => $summary['verification_reasons'] ?? [],
            ])
            ->values()
            ->all();
    }

    private function buildCompetencyVerificationReasons(Collection $availableItems): array
    {
        if ($availableItems->isEmpty()) {
            return [];
        }

        $scores = $availableItems
            ->pluck('score')
            ->filter(fn ($score) => $score !== null)
            ->map(fn ($score) => (float) $score)
            ->values();
        $reasons = [];

        if ($scores->count() >= 2) {
            $gap = (float) $scores->max() - (float) $scores->min();
            $verificationThreshold = max(
                (float) $availableItems
                    ->pluck('verification_gap_threshold')
                    ->filter(fn ($value) => is_numeric($value))
                    ->map(fn ($value) => (float) $value)
                    ->max(),
                1.50
            );

            if ($gap >= $verificationThreshold) {
                $reasons[] = 'Selisih skor antarinstrumen mencapai atau melebihi '.number_format($verificationThreshold, 2).' level.';
            }
        }

        $pgSummary = $availableItems->firstWhere('instrument_type', AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value);

        if ($pgSummary) {
            $blankRatio = ((int) ($pgSummary['unanswered_items'] ?? 0)) / max((int) ($pgSummary['total_items'] ?? 0), 1);
            $emptyThreshold = max(((float) ($pgSummary['empty_response_threshold_percent'] ?? 10)) / 100, 0);

            if ($blankRatio > $emptyThreshold) {
                $reasons[] = 'Respons kosong pada domain pilihan ganda kompleks melebihi '.number_format($emptyThreshold * 100, 0).' persen dan perlu klarifikasi.';
            }
        }

        return $reasons;
    }

    private function resolveScoringStatus(int $totalAnsweredItems, int $pendingManualItems): string
    {
        if ($totalAnsweredItems === 0) {
            return 'not_ready';
        }

        if ($pendingManualItems > 0) {
            return 'partial';
        }

        return 'complete';
    }

    private function resolveScoringStatusLabel(int $totalAnsweredItems, int $pendingManualItems): string
    {
        return match ($this->resolveScoringStatus($totalAnsweredItems, $pendingManualItems)) {
            'complete' => 'Penilaian Sistem Selesai',
            'partial' => 'Sebagian Belum Terskor Sistem',
            default => 'Belum Ada Skor',
        };
    }

    private function resolveScoringStatusDescription(
        int $totalAnsweredItems,
        int $pendingManualItems,
        array $verificationAlerts = []
    ): string {
        return match ($this->resolveScoringStatus($totalAnsweredItems, $pendingManualItems)) {
            'complete' => $verificationAlerts === []
                ? 'Semua komponen skor yang tersedia sudah dihitung otomatis sesuai konfigurasi builder.'
                : 'Semua komponen skor sudah dihitung, tetapi beberapa kompetensi masih ditandai perlu verifikasi karena selisih antarinstrumen atau respons kosong.',
            'partial' => "Masih ada {$pendingManualItems} jawaban yang belum berhasil dikonversi menjadi skor otomatis.",
            default => 'Instrumen yang ada belum menghasilkan komponen skor yang bisa dihitung.',
        };
    }

    private function resolveRecommendationDetails(float|int|string|null $score): array
    {
        if (! is_numeric($score)) {
            return [
                'category' => null,
                'focus' => null,
                'support' => null,
                'description' => null,
            ];
        }

        $numericScore = round((float) $score, 2);

        return match (true) {
            $numericScore < 1.80 => [
                'category' => 'Level 1 - Paham',
                'focus' => 'Penguatan konsep dasar',
                'support' => 'Orientasi kompetensi, contoh praktik, modul mandiri terstruktur, observasi kelas, pendampingan intensif.',
                'description' => 'Perlu penguatan konsep dasar, contoh praktik, dan pendampingan awal.',
            ],
            $numericScore < 2.60 => [
                'category' => 'Level 2 - Dasar',
                'focus' => 'Penguatan prosedur dan keterampilan awal',
                'support' => 'Lokakarya praktik, simulasi kasus, umpan balik coach, perangkat ajar atau asesmen terarah.',
                'description' => 'Perlu latihan penerapan prosedur, simulasi, dan umpan balik terarah.',
            ],
            $numericScore < 3.40 => [
                'category' => 'Level 3 - Menengah',
                'focus' => 'Pendalaman analisis dan adaptasi strategi',
                'support' => 'Peer coaching, lesson study, analisis data hasil belajar, diferensiasi, dan aksi perbaikan kelas.',
                'description' => 'Mampu menerapkan strategi; perlu penguatan analisis data, evaluasi, dan diferensiasi konteks.',
            ],
            $numericScore < 4.20 => [
                'category' => 'Level 4 - Mumpuni',
                'focus' => 'Penguatan kepemimpinan pembelajaran',
                'support' => 'Menjadi mentor, fasilitator komunitas belajar, penelitian tindakan, dan pengembangan sistem sekolah.',
                'description' => 'Mampu mengevaluasi dan menyesuaikan praktik; perlu perluasan peran sebagai penggerak atau mentor.',
            ],
            default => [
                'category' => 'Level 5 - Ahli',
                'focus' => 'Diseminasi dan pengembangan ekosistem',
                'support' => 'Master trainer, pengembangan model praktik baik, jejaring lintas sekolah, pendampingan replikasi, dan evaluasi dampak.',
                'description' => 'Mampu mengembangkan sistem atau inovasi; diarahkan pada diseminasi, jejaring, dan penguatan kapasitas sekolah.',
            ],
        };
    }

    private function serializeLevel(float|int|string|null $score): ?array
    {
        $level = LevelKompetensi::fromScore($score);

        if (! $level) {
            return null;
        }

        return [
            'value' => $level->value,
            'label' => $level->label(),
            'short_label' => $level->shortLabel(),
        ];
    }

    private function formatScore(float|int|string|null $score): ?string
    {
        if (! is_numeric($score)) {
            return null;
        }

        return number_format((float) $score, 2);
    }

    /**
     * @param  array<int, float|int>  $scores
     */
    private function average(array $scores): ?float
    {
        if ($scores === []) {
            return null;
        }

        return round(array_sum(array_map('floatval', $scores)) / count($scores), 2);
    }

    private function weightedAverageItems(Collection $items, string $scoreKey, string $weightKey): ?float
    {
        $weightedItems = $items
            ->filter(fn ($item) => $item[$scoreKey] !== null)
            ->map(fn ($item) => [
                'score' => (float) $item[$scoreKey],
                'weight' => max((float) ($item[$weightKey] ?? 1), 0.01),
            ])
            ->values();

        if ($weightedItems->isEmpty()) {
            return null;
        }

        $totalWeight = (float) $weightedItems->sum('weight');

        if ($totalWeight <= 0) {
            return null;
        }

        return round(
            (float) $weightedItems->sum(fn (array $item) => $item['score'] * ($item['weight'] / $totalWeight)),
            2
        );
    }

    private function resolveInstrumentEngine(AssessmentInstrumentType $instrument): BaseInstrumentScoringEngine
    {
        return match ($instrument) {
            AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS => $this->pilihanGandaKompleksScoringEngine,
            AssessmentInstrumentType::STUDI_KASUS => $this->studiKasusScoringEngine,
            AssessmentInstrumentType::PORTOFOLIO,
            AssessmentInstrumentType::MONITORING_OBSERVASI_EVIDEN => $this->portofolioScoringEngine,
        };
    }
}
