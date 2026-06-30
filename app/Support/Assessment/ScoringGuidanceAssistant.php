<?php

namespace App\Support\Assessment;

use Illuminate\Support\Str;

class ScoringGuidanceAssistant
{
    /**
     * @var array<int, string>
     */
    private const STOP_WORDS = [
        'yang', 'dan', 'atau', 'dengan', 'untuk', 'dari', 'pada', 'dalam', 'ke', 'di', 'sebagai',
        'agar', 'serta', 'adalah', 'karena', 'bahwa', 'oleh', 'terhadap', 'jika', 'maka', 'itu',
        'ini', 'juga', 'saat', 'ketika', 'dapat', 'akan', 'lebih', 'masih', 'sudah', 'belum',
        'sebuah', 'seorang', 'para', 'guru', 'peserta', 'didik', 'siswa', 'jawaban', 'langkah',
        'dapatkan', 'pernah', 'telah', 'sesuai', 'terkait', 'berkaitan', 'melalui', 'tentang',
        'diperlukan', 'perlu', 'dalamnya', 'maupun', 'setelah', 'sebelum', 'antara', 'menjadi',
        'adanya', 'dapat', 'harus', 'cukup', 'supaya', 'bila', 'jika', 'sehingga', 'tersebut',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const SYNONYM_LIBRARY = [
        'asesmen' => ['penilaian', 'evaluasi'],
        'bukti' => ['dokumen', 'lampiran'],
        'implementasi' => ['penerapan', 'praktik'],
        'instansi' => ['lembaga', 'institusi'],
        'kolaborasi' => ['kerja sama'],
        'kompetensi' => ['kemampuan'],
        'monitoring' => ['pemantauan'],
        'pelatihan' => ['diklat', 'bimtek', 'workshop'],
        'pembelajaran' => ['pengajaran', 'proses belajar'],
        'portofolio' => ['hasil karya', 'dokumen kerja'],
        'refleksi' => ['evaluasi diri'],
        'sertifikat' => ['sertifikasi', 'piagam'],
        'umpan balik' => ['masukan', 'feedback'],
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private const PHRASE_LIBRARY = [
        'bukti dukung' => ['dokumen pendukung'],
        'kebutuhan mengajar' => ['tugas mengajar'],
        'program studi' => ['bidang studi'],
        'riwayat pendidikan' => ['pendidikan formal'],
        'rencana tindak lanjut' => ['rtl'],
        'strategi pembelajaran' => ['strategi mengajar'],
    ];

    public static function clientPreset(): array
    {
        return [
            'stop_words' => array_values(array_unique(self::STOP_WORDS)),
            'synonym_library' => self::SYNONYM_LIBRARY,
            'phrase_library' => self::PHRASE_LIBRARY,
        ];
    }

    /**
     * @return array{
     *     keyword_groups_text:?string,
     *     synonym_map_text:?string,
     *     min_words:?int,
     *     advanced_rules:array<string,mixed>
     * }
     */
    public function suggest(?string $sourceText, string $fieldType = 'text', array $context = []): array
    {
        $normalized = $this->normalizeText((string) $sourceText);

        if ($normalized === '') {
            return [
                'keyword_groups_text' => null,
                'synonym_map_text' => null,
                'min_words' => null,
                'advanced_rules' => [],
            ];
        }

        $terms = collect()
            ->merge($this->extractCuratedPhrases($normalized))
            ->merge($this->extractAdjacentPhrases($normalized))
            ->merge($this->extractSignificantWords($normalized))
            ->filter()
            ->unique()
            ->take(6)
            ->values();

        $keywordGroups = [];
        $synonymLines = [];

        foreach ($terms as $term) {
            $group = $this->buildKeywordGroup((string) $term);

            if ($group === []) {
                continue;
            }

            $keywordGroups[] = $group;

            if (count($group) > 1) {
                $synonymLines[] = sprintf('%s: %s', $group[0], implode(', ', array_slice($group, 1)));
            }
        }

        $signalKeywords = collect($keywordGroups)
            ->map(fn (array $group) => $group[0] ?? null)
            ->filter()
            ->take(5)
            ->values()
            ->all();

        $advancedRules = array_filter([
            'signal_keywords' => $signalKeywords !== [] ? $signalKeywords : null,
            'target_rows' => $fieldType === 'repeater'
                ? max((int) ($context['target_rows'] ?? $context['min_rows'] ?? 1), 1)
                : null,
        ], fn ($value) => $value !== null && $value !== []);

        return [
            'keyword_groups_text' => $keywordGroups === []
                ? null
                : collect($keywordGroups)
                    ->map(fn (array $group) => $group[0] ?? null)
                    ->filter()
                    ->implode(', '),
            'synonym_map_text' => $synonymLines === [] ? null : implode("\n", array_slice($synonymLines, 0, 4)),
            'min_words' => $this->estimateMinWords($normalized, $fieldType),
            'advanced_rules' => $advancedRules,
        ];
    }

    private function normalizeText(string $text): string
    {
        return Str::of(Str::ascii($text))
            ->lower()
            ->replaceMatches('/[^a-z0-9\s]+/u', ' ')
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->value();
    }

    /**
     * @return array<int, string>
     */
    private function extractCuratedPhrases(string $normalizedText): array
    {
        return collect(array_keys(self::PHRASE_LIBRARY))
            ->filter(fn ($phrase) => Str::contains($normalizedText, $phrase))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function extractAdjacentPhrases(string $normalizedText): array
    {
        $tokens = preg_split('/\s+/', $normalizedText) ?: [];
        $phrases = [];

        for ($index = 0; $index < count($tokens) - 1; $index++) {
            $first = trim((string) ($tokens[$index] ?? ''));
            $second = trim((string) ($tokens[$index + 1] ?? ''));

            if (! $this->isSignificantWord($first) || ! $this->isSignificantWord($second)) {
                continue;
            }

            $phrases[] = sprintf('%s %s', $first, $second);
        }

        return collect($phrases)
            ->unique()
            ->reject(fn ($phrase) => array_key_exists($phrase, self::PHRASE_LIBRARY))
            ->take(4)
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function extractSignificantWords(string $normalizedText): array
    {
        return collect(preg_split('/\s+/', $normalizedText) ?: [])
            ->map(fn ($word) => trim((string) $word))
            ->filter(fn ($word) => $this->isSignificantWord($word))
            ->unique()
            ->take(8)
            ->values()
            ->all();
    }

    private function isSignificantWord(string $word): bool
    {
        return $word !== '' && strlen($word) >= 4 && ! in_array($word, self::STOP_WORDS, true);
    }

    /**
     * @return array<int, string>
     */
    private function buildKeywordGroup(string $term): array
    {
        $normalizedTerm = $this->normalizeText($term);

        if ($normalizedTerm === '') {
            return [];
        }

        $group = [$term];

        if (array_key_exists($normalizedTerm, self::PHRASE_LIBRARY)) {
            $group = array_merge([$normalizedTerm], self::PHRASE_LIBRARY[$normalizedTerm]);
        } else {
            [$baseTerm, $variants] = $this->resolveSynonymVariants($normalizedTerm);
            $group = array_merge([$baseTerm], $variants);
        }

        return collect($group)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array{0:string,1:array<int,string>}
     */
    private function resolveSynonymVariants(string $term): array
    {
        if (array_key_exists($term, self::SYNONYM_LIBRARY)) {
            return [$term, self::SYNONYM_LIBRARY[$term]];
        }

        foreach (self::SYNONYM_LIBRARY as $baseWord => $variants) {
            $normalizedVariants = collect($variants)
                ->map(fn ($variant) => $this->normalizeText((string) $variant))
                ->filter()
                ->values()
                ->all();

            if (in_array($term, $normalizedVariants, true)) {
                return [$baseWord, array_values(array_diff($variants, [$term]))];
            }
        }

        return [$term, []];
    }

    private function estimateMinWords(string $normalizedText, string $fieldType): int
    {
        $wordCount = max(str_word_count($normalizedText), 1);
        $baseline = match ($fieldType) {
            'textarea' => 18,
            'repeater' => 12,
            'text' => 8,
            default => 5,
        };

        $suggested = match ($fieldType) {
            'textarea' => (int) round($wordCount * 0.45),
            'repeater' => (int) round($wordCount * 0.30),
            'text' => (int) round($wordCount * 0.35),
            default => (int) round($wordCount * 0.25),
        };

        return max($baseline, min($suggested, 60));
    }
}
