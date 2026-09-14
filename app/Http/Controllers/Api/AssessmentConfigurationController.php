<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentConfigurationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $assessments = $this->publishedQuery()
            ->when($request->filled('kategori'), fn ($query) => $query->where('kategori', $request->string('kategori')))
            ->when($request->filled('kode_assessment'), fn ($query) => $query->where('kode_assessment', $request->string('kode_assessment')))
            ->orderBy('id')
            ->get();

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
        $assessment = $this->publishedQuery()
            ->where(function ($query) use ($identifier) {
                $query->where('slug', $identifier)
                    ->orWhere('kode_assessment', $identifier);
            })
            ->first();

        if (! $assessment) {
            return response()->json(['message' => 'Assessment tidak ditemukan.'], 404);
        }

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
}
