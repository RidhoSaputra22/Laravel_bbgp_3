<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Support\Assessment\ScoringConfigNormalizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentConfigurationController extends Controller
{
    public function __construct(private readonly ScoringConfigNormalizer $scoringConfigNormalizer) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'kategori' => 'nullable|string|max:100',
            'kode_assessment' => 'nullable|string|max:100',
        ]);

        $assessments = $this->publishedQuery()
            ->when($request->filled('kategori'), fn ($query) => $query->where('kategori', $request->string('kategori')))
            ->when($request->filled('kode_assessment'), fn ($query) => $query->where('kode_assessment', $request->string('kode_assessment')))
            ->orderBy('id')
            ->get();

        $this->normalizeAdvancedRulesText($assessments);

        return response()->json([
            'data' => $assessments,
            'meta' => [
                'count' => $assessments->count(),
                'schema' => 'database-assessment-v1',
            ],
        ]);
    }

    public function show(string $identifier): JsonResponse
    {
        abort_if(strlen($identifier) > 100, 404);

        $assessment = $this->publishedQuery()
            ->where(function ($query) use ($identifier) {
                $query->where('slug', $identifier)
                    ->orWhere('kode_assessment', $identifier);
            })
            ->first();

        if (! $assessment) {
            return response()->json(['message' => 'Assessment tidak ditemukan.'], 404);
        }

        $this->normalizeAdvancedRulesText([$assessment]);

        return response()->json([
            'data' => $assessment,
            'meta' => ['schema' => 'database-assessment-v1'],
        ]);
    }

    private function publishedQuery()
    {
        return Assessment::query()
            ->where('status', 'publish')
            ->where('is_active', true)
            ->with(['forms' => fn ($query) => $query
                ->where('is_active', true)
                ->with(['fields' => fn ($fieldQuery) => $fieldQuery->where('is_active', true)])]);
    }

    private function normalizeAdvancedRulesText(iterable $assessments): void
    {
        foreach ($assessments as $assessment) {
            $this->normalizeModelScoringConfig($assessment);

            foreach ($assessment->forms as $form) {
                $this->normalizeModelScoringConfig($form);

                foreach ($form->fields as $field) {
                    $this->normalizeModelScoringConfig($field);
                }
            }
        }
    }

    private function normalizeModelScoringConfig(Model $model): void
    {
        $config = $model->getAttribute('scoring_config');

        if (! is_array($config) || ! array_key_exists('advanced_rules_text', $config)) {
            return;
        }

        $config['advanced_rules_text'] = $this->scoringConfigNormalizer->parseAdvancedRules(
            $config['advanced_rules_text']
        );

        $model->setAttribute('scoring_config', $config);
    }
}
