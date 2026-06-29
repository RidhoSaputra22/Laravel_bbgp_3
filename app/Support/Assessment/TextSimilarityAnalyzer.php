<?php

namespace App\Support\Assessment;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TextSimilarityAnalyzer
{
    /**
     * @var array<int, string>
     */
    private array $stopWords = [
        'yang', 'dan', 'atau', 'dengan', 'untuk', 'dari', 'pada', 'dalam', 'ke', 'di', 'sebagai',
        'agar', 'serta', 'adalah', 'karena', 'bahwa', 'oleh', 'terhadap', 'jika', 'maka', 'itu',
        'ini', 'juga', 'saat', 'ketika', 'dapat', 'akan', 'lebih', 'masih', 'sudah', 'belum',
        'sebuah', 'seorang', 'para', 'guru', 'peserta', 'didik', 'siswa', 'jawaban', 'langkah',
    ];

    public function analyze(
        string $answer,
        ?string $referenceAnswer = null,
        array $keywordGroups = [],
        array $synonyms = [],
        array $options = []
    ): array {
        $normalizedAnswer = $this->normalizeText($answer);
        $normalizedReference = $this->normalizeText((string) ($referenceAnswer ?? ''));

        $answerTokens = $this->tokenize($normalizedAnswer, $synonyms);
        $referenceTokens = $this->tokenize($normalizedReference, $synonyms);
        $wordCount = str_word_count($normalizedAnswer);
        $minWords = max((int) ($options['min_words'] ?? 15), 1);
        $keywordCoverage = $this->calculateKeywordCoverage($answerTokens, $keywordGroups, $synonyms);
        $phraseCoverage = $this->calculatePhraseCoverage(
            $normalizedAnswer,
            $referenceAnswer,
            $options['reference_phrases'] ?? []
        );
        $tokenJaccard = $this->jaccard($answerTokens, $referenceTokens);
        $cosine = $this->cosineSimilarity($answerTokens, $referenceTokens);
        $lengthScore = min($wordCount / $minWords, 1.0);
        $structureScore = $this->detectStructureSignals($normalizedAnswer, $options);
        $signalScore = $this->detectSignalKeywords($answerTokens, $options['signal_keywords'] ?? []);

        return [
            'normalized_answer' => $normalizedAnswer,
            'normalized_reference' => $normalizedReference,
            'answer_tokens' => $answerTokens,
            'reference_tokens' => $referenceTokens,
            'word_count' => $wordCount,
            'min_words' => $minWords,
            'keyword_coverage' => $keywordCoverage,
            'phrase_coverage' => $phraseCoverage,
            'token_jaccard' => $tokenJaccard,
            'cosine_similarity' => $cosine,
            'length_score' => round($lengthScore, 4),
            'structure_score' => $structureScore,
            'signal_score' => $signalScore,
        ];
    }

    public function normalizeText(string $text): string
    {
        $normalized = Str::of(Str::ascii($text))
            ->lower()
            ->replaceMatches('/[^a-z0-9\s]+/u', ' ')
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->value();

        return $normalized;
    }

    /**
     * @return array<int, string>
     */
    public function tokenize(string $text, array $synonyms = []): array
    {
        $synonymMap = $this->normalizeSynonymMap($synonyms);

        return collect(explode(' ', $text))
            ->map(fn ($token) => $this->normalizeToken($token))
            ->filter(fn ($token) => $token !== '' && ! in_array($token, $this->stopWords, true))
            ->map(function ($token) use ($synonymMap) {
                return $synonymMap[$token] ?? $token;
            })
            ->values()
            ->all();
    }

    private function normalizeToken(string $token): string
    {
        $token = trim($token);

        if ($token === '') {
            return '';
        }

        $token = preg_replace('/(kah|lah|pun|nya|ku|mu)$/', '', $token) ?: $token;
        $token = preg_replace('/(kan|an|i)$/', '', $token) ?: $token;
        $token = preg_replace('/^(meng|meny|men|mem|me|ber|bel|be|per|pel|pe|ter|te|se|ke|di)/', '', $token) ?: $token;

        return trim((string) $token);
    }

    /**
     * @return array<string, string>
     */
    private function normalizeSynonymMap(array $synonyms): array
    {
        $normalized = [];

        foreach ($synonyms as $baseWord => $variants) {
            $baseToken = $this->normalizeToken($this->normalizeText((string) $baseWord));

            if ($baseToken === '') {
                continue;
            }

            $normalized[$baseToken] = $baseToken;

            foreach ((array) $variants as $variant) {
                $variantToken = $this->normalizeToken($this->normalizeText((string) $variant));

                if ($variantToken === '') {
                    continue;
                }

                $normalized[$variantToken] = $baseToken;
            }
        }

        return $normalized;
    }

    private function calculateKeywordCoverage(array $answerTokens, array $keywordGroups, array $synonyms): float
    {
        if ($keywordGroups === []) {
            return 0.0;
        }

        $coveredGroups = collect($keywordGroups)->filter(function ($group) use ($answerTokens, $synonyms) {
            $variants = collect((array) $group)
                ->flatMap(fn ($candidate) => $this->expandKeywordCandidate((string) $candidate, $synonyms))
                ->filter()
                ->values();

            return $variants->contains(function (array $candidateTokens) use ($answerTokens) {
                return $candidateTokens !== [] && $this->containsAllTokens($answerTokens, $candidateTokens);
            });
        })->count();

        return round($coveredGroups / max(count($keywordGroups), 1), 4);
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function expandKeywordCandidate(string $keyword, array $synonyms): array
    {
        $tokens = $this->tokenize($this->normalizeText($keyword), $synonyms);

        return $tokens !== [] ? [$tokens] : [];
    }

    private function calculatePhraseCoverage(string $normalizedAnswer, ?string $referenceAnswer, array $phrases = []): float
    {
        $referencePhrases = collect($phrases);

        if ($referencePhrases->isEmpty() && filled($referenceAnswer)) {
            $referencePhrases = $this->extractReferencePhrases((string) $referenceAnswer);
        }

        $referencePhrases = $referencePhrases
            ->map(fn ($phrase) => $this->normalizeText((string) $phrase))
            ->filter()
            ->unique()
            ->values();

        if ($referencePhrases->isEmpty()) {
            return 0.0;
        }

        $covered = $referencePhrases->filter(fn ($phrase) => Str::contains($normalizedAnswer, $phrase))->count();

        return round($covered / max($referencePhrases->count(), 1), 4);
    }

    private function extractReferencePhrases(string $referenceAnswer): Collection
    {
        $tokens = $this->tokenize($this->normalizeText($referenceAnswer));
        $phrases = collect();

        foreach ([2, 3] as $size) {
            for ($index = 0; $index <= count($tokens) - $size; $index++) {
                $phraseTokens = array_slice($tokens, $index, $size);

                if (count(array_filter($phraseTokens, fn ($token) => strlen($token) > 2)) < $size) {
                    continue;
                }

                $phrases->push(implode(' ', $phraseTokens));
            }
        }

        return $phrases->unique()->take(8)->values();
    }

    private function detectStructureSignals(string $normalizedAnswer, array $options): float
    {
        $patterns = $options['structure_markers'] ?? [
            'analysis' => ['karena', 'sebab', 'akar', 'masalah', 'dampak', 'analisis'],
            'strategy' => ['strategi', 'langkah', 'rencana', 'solusi', 'tindak', 'pendekatan'],
            'evaluation' => ['indikator', 'monitoring', 'evaluasi', 'umpan balik', 'refleksi'],
        ];

        $scores = collect($patterns)->map(function ($markers) use ($normalizedAnswer) {
            $hits = collect((array) $markers)
                ->filter(fn ($marker) => Str::contains($normalizedAnswer, $this->normalizeText((string) $marker)))
                ->count();

            return min($hits / max(count((array) $markers), 1), 1.0);
        });

        return round((float) $scores->avg(), 4);
    }

    private function detectSignalKeywords(array $answerTokens, array $keywords): float
    {
        $normalizedKeywords = collect($keywords)
            ->map(fn ($keyword) => $this->normalizeToken($this->normalizeText((string) $keyword)))
            ->filter()
            ->unique()
            ->values();

        if ($normalizedKeywords->isEmpty()) {
            return 0.0;
        }

        $hits = $normalizedKeywords->filter(fn ($keyword) => in_array($keyword, $answerTokens, true))->count();

        return round($hits / max($normalizedKeywords->count(), 1), 4);
    }

    private function jaccard(array $answerTokens, array $referenceTokens): float
    {
        if ($answerTokens === [] || $referenceTokens === []) {
            return 0.0;
        }

        $answerSet = array_values(array_unique($answerTokens));
        $referenceSet = array_values(array_unique($referenceTokens));
        $intersection = array_intersect($answerSet, $referenceSet);
        $union = array_unique(array_merge($answerSet, $referenceSet));

        return round(count($intersection) / max(count($union), 1), 4);
    }

    private function cosineSimilarity(array $answerTokens, array $referenceTokens): float
    {
        if ($answerTokens === [] || $referenceTokens === []) {
            return 0.0;
        }

        $answerVector = array_count_values($answerTokens);
        $referenceVector = array_count_values($referenceTokens);
        $allTerms = array_unique(array_merge(array_keys($answerVector), array_keys($referenceVector)));
        $dotProduct = 0.0;
        $answerMagnitude = 0.0;
        $referenceMagnitude = 0.0;

        foreach ($allTerms as $term) {
            $answerValue = (float) ($answerVector[$term] ?? 0);
            $referenceValue = (float) ($referenceVector[$term] ?? 0);
            $dotProduct += $answerValue * $referenceValue;
            $answerMagnitude += $answerValue ** 2;
            $referenceMagnitude += $referenceValue ** 2;
        }

        if ($answerMagnitude <= 0 || $referenceMagnitude <= 0) {
            return 0.0;
        }

        return round($dotProduct / (sqrt($answerMagnitude) * sqrt($referenceMagnitude)), 4);
    }

    private function containsAllTokens(array $haystackTokens, array $requiredTokens): bool
    {
        foreach ($requiredTokens as $token) {
            if (! in_array($token, $haystackTokens, true)) {
                return false;
            }
        }

        return true;
    }
}
