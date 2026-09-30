<?php

namespace App\Http\Controllers\Api;

use App\Enum\AssessmentKetenagaanType;
use App\Http\Controllers\Controller;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Services\Assessment\AssessmentAssignmentTargetDocumentBuilder;
use App\Services\Assessment\AssessmentQuestionRandomizerService;
use App\Support\Assessment\ScoringConfigNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AssessmentAssignmentController extends Controller
{
    private const PARTICIPANTS_PER_PAGE = 10;

    public function __construct(
        private readonly AssessmentQuestionRandomizerService $randomizer,
        private readonly ScoringConfigNormalizer $scoringConfigNormalizer,
        ?AssessmentAssignmentTargetDocumentBuilder $documentBuilder = null
    ) {
        $this->documentBuilder = $documentBuilder
            ?: new AssessmentAssignmentTargetDocumentBuilder($randomizer, $scoringConfigNormalizer);
    }

    private readonly AssessmentAssignmentTargetDocumentBuilder $documentBuilder;

    /**
     * Daftar ringkas penugasan, dipisahkan menurut ketenagaan.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'target_ketenagaan' => ['nullable', 'string', 'max:100'],
        ]);

        $assignments = AssessmentAssignment::query()
            ->withoutPreview()
            ->with('combination')
            ->withCount('targets')
            ->when(
                $request->filled('target_ketenagaan'),
                fn ($query) => $query->where('target_ketenagaan', $request->string('target_ketenagaan'))
            )
            ->newestFirst()
            ->get();

        return response()->json([
            'data' => $this->groupAssignmentsByKetenagaan($assignments),
            'meta' => [
                'count' => $assignments->count(),
                'schema' => 'assessment_assignment-v1',
            ],
        ]);
    }

    /**
     * Detail satu penugasan beserta form yang benar-benar ditugaskan per peserta.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        abort_if(! ctype_digit($id), 404);

        $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $perPage = min((int) $request->input('per_page', self::PARTICIPANTS_PER_PAGE), 50);

        $assignment = AssessmentAssignment::query()
            ->withoutPreview()
            ->with($this->documentBuilder->assignmentBaseRelations())
            ->find($id);

        if (! $assignment) {
            return response()->json(['message' => 'Penugasan tidak ditemukan.'], 404);
        }
        $this->documentBuilder->rememberAssignment($assignment);

        $participants = AssessmentAssignmentTarget::query()
            ->select(AssessmentAssignmentTargetDocumentBuilder::targetColumns())
            ->where('assessment_assignment_id', $assignment->id)
            ->with($this->documentBuilder->targetRelations())
            ->orderBy('id')
            ->paginate($perPage);
        $this->documentBuilder->hydrateAssignments($participants->getCollection());

        return response()->json([
            'data' => [
                'id' => $assignment->id,
                'kode_penugasan' => $assignment->kode_penugasan,
                'judul_penugasan' => $assignment->judul_penugasan,
                'deskripsi' => $assignment->deskripsi,
                'is_active' => (bool) $assignment->is_active,
                'status_distribusi' => $assignment->status_distribusi,
                'ketenagaan' => $this->ketenagaan($assignment->target_ketenagaan),
                'tanggal_mulai' => $assignment->tanggal_mulai?->toDateString(),
                'tanggal_selesai' => $assignment->tanggal_selesai?->toDateString(),
                'participants' => $participants->getCollection()
                    ->map(fn (AssessmentAssignmentTarget $target) => $this->participant($target, $assignment))
                    ->values()
                    ->all(),
            ],
            'meta' => [
                'participant_count' => $participants->total(),
                'pagination' => [
                    'current_page' => $participants->currentPage(),
                    'last_page' => $participants->lastPage(),
                    'per_page' => $participants->perPage(),
                    'total' => $participants->total(),
                    'from' => $participants->firstItem() ?? 0,
                    'to' => $participants->lastItem() ?? 0,
                ],
                'schema' => 'assessment_assignment-v1',
            ],
        ]);
    }

    /**
     * Daftar penugasan per guru, satu item untuk setiap assessment_assignment_targets.
     */
    public function targets(Request $request): JsonResponse
    {
        $request->validate([
            'guru_id' => ['nullable', 'integer', 'min:1'],
            'assessment_assignment_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $perPage = min((int) $request->input('per_page', self::PARTICIPANTS_PER_PAGE), 50);

        $targets = AssessmentAssignmentTarget::query()
            ->select(AssessmentAssignmentTargetDocumentBuilder::targetColumns())
            ->whereHas('assignment', fn ($query) => $query->withoutPreview())
            ->when($request->filled('guru_id'), fn ($query) => $query->where('guru_id', $request->integer('guru_id')))
            ->when(
                $request->filled('assessment_assignment_id'),
                fn ($query) => $query->where('assessment_assignment_id', $request->integer('assessment_assignment_id'))
            )
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->with($this->documentBuilder->targetRelations())
            ->latestAssignmentFirst()
            ->paginate($perPage);
        $this->documentBuilder->hydrateAssignments($targets->getCollection());

        return response()->json([
            'data' => $targets->getCollection()
                ->map(function (AssessmentAssignmentTarget $target) {
                    $assignment = $target->assignment;

                    return array_merge([
                        'assignment' => [
                            'id' => $assignment->id,
                            'kode_penugasan' => $assignment->kode_penugasan,
                            'judul_penugasan' => $assignment->judul_penugasan,
                            'ketenagaan' => $this->ketenagaan($assignment->target_ketenagaan),
                            'status_distribusi' => $assignment->status_distribusi,
                        ],
                    ], $this->participant($target, $assignment));
                })
                ->values()
                ->all(),
            'meta' => [
                'pagination' => [
                    'current_page' => $targets->currentPage(),
                    'last_page' => $targets->lastPage(),
                    'per_page' => $targets->perPage(),
                    'total' => $targets->total(),
                    'from' => $targets->firstItem() ?? 0,
                    'to' => $targets->lastItem() ?? 0,
                ],
                'schema' => 'assessment_assignment-target-v1',
            ],
        ]);
    }

    private function groupAssignmentsByKetenagaan(Collection $assignments): array
    {
        $byKetenagaan = $assignments->groupBy(fn (AssessmentAssignment $assignment) => (string) $assignment->target_ketenagaan);

        return collect(AssessmentKetenagaanType::cases())
            ->map(function (AssessmentKetenagaanType $type) use ($byKetenagaan) {
                return [
                    'ketenagaan' => $this->ketenagaan($type->value),
                    'assignments' => $byKetenagaan->get($type->value, collect())
                        ->map(fn (AssessmentAssignment $assignment) => $this->assignmentSummary($assignment))
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn (array $group) => $group['assignments'] !== [])
            ->values()
            ->all();
    }

    private function assignmentSummary(AssessmentAssignment $assignment): array
    {
        return [
            'id' => $assignment->id,
            'kode_penugasan' => $assignment->kode_penugasan,
            'judul_penugasan' => $assignment->judul_penugasan,
            'status_distribusi' => $assignment->status_distribusi,
            'is_active' => (bool) $assignment->is_active,
            'participant_count' => (int) ($assignment->targets_count ?? 0),
            'combination' => $this->combination($assignment->combination),
        ];
    }

    private function participant(AssessmentAssignmentTarget $target, AssessmentAssignment $assignment): array
    {
        return $this->documentBuilder->participant($target, $assignment);
    }

    private function ketenagaan(?string $value): array
    {
        $type = AssessmentKetenagaanType::tryFromMixed($value);

        return [
            'kode' => $value,
            'label' => $type?->label() ?? $value,
        ];
    }

    private function combination($combination): ?array
    {
        if (! $combination) {
            return null;
        }

        return [
            'id' => $combination->id,
            'kode_kombinasi' => $combination->kode_kombinasi,
            'judul' => $combination->judul,
            'target_ketenagaan' => $combination->target_ketenagaan,
        ];
    }
}
