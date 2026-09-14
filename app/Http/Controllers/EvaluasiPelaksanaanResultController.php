<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EvaluasiPelaksanaanResultController extends Controller
{
    private const MENU = 'evaluasi-hasil';

    private const SCORE_LABELS = [
        1 => 'Kurang',
        2 => 'Cukup',
        3 => 'Baik',
        4 => 'Sangat Baik',
    ];

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $evaluations = Assessment::query()
            ->where('kategori', Assessment::CATEGORY_EVALUASI_PELAKSANAAN)
            ->with(['forms.fields'])
            ->orderBy('judul')
            ->get();

        $selectedEvaluationId = (int) $request->input('assessment_id', 0);
        if (! $evaluations->contains('id', $selectedEvaluationId)) {
            $selectedEvaluationId = 0;
        }

        $assignments = AssessmentAssignment::query()
            ->withoutPreview()
            ->whereHas('assessments', function ($query) use ($selectedEvaluationId) {
                $query->where('kategori', Assessment::CATEGORY_EVALUASI_PELAKSANAAN)
                    ->when(
                        $selectedEvaluationId > 0,
                        fn ($evaluationQuery) => $evaluationQuery->whereKey($selectedEvaluationId)
                    );
            })
            ->with([
                'assessments' => fn ($query) => $query
                    ->where('kategori', Assessment::CATEGORY_EVALUASI_PELAKSANAAN)
                    ->orderBy('judul'),
                'targets' => fn ($query) => $query->with(['guru', 'attempt']),
            ])
            ->newestFirst()
            ->get();

        $targets = $assignments->flatMap(function (AssessmentAssignment $assignment) {
            return $assignment->targets->map(function (AssessmentAssignmentTarget $target) use ($assignment) {
                return $this->serializeTarget($target, $assignment);
            });
        })->values();

        $submittedTargets = $targets->filter(fn (array $target) => $target['is_submitted']);
        $scores = $submittedTargets
            ->pluck('score')
            ->filter(fn ($score) => $score !== null)
            ->values();

        $stats = [
            'bank_total' => $evaluations->count(),
            'form_total' => $evaluations->sum(fn (Assessment $assessment) => $assessment->forms->count()),
            'question_total' => $evaluations->sum(
                fn (Assessment $assessment) => $assessment->forms->sum(fn ($form) => $form->fields->count())
            ),
            'assignment_total' => $assignments->count(),
            'participant_total' => $targets->count(),
            'submitted_total' => $submittedTargets->count(),
            'in_progress_total' => $targets->where('status', 'in_progress')->count(),
            'not_started_total' => $targets->where('status', 'not_started')->count(),
            'average_score' => $scores->isNotEmpty() ? round((float) $scores->avg(), 2) : null,
            'completion_rate' => $targets->isNotEmpty()
                ? round(($submittedTargets->count() / $targets->count()) * 100, 1)
                : 0,
        ];

        return view('pages.admin.evaluasi-pelaksanaan.hasil.index', [
            'menu' => self::MENU,
            'evaluations' => $evaluations,
            'selectedEvaluationId' => $selectedEvaluationId,
            'stats' => $stats,
            'assignmentRows' => $this->buildAssignmentRows($assignments),
            'recentResults' => $targets->sortByDesc('submitted_at_sort')->take(25)->values(),
            'scoreDistribution' => $this->buildScoreDistribution($scores),
        ]);
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeTarget(
        AssessmentAssignmentTarget $target,
        AssessmentAssignment $assignment
    ): array {
        $attempt = $target->attempt;
        $scoreValue = data_get($attempt?->scoring_summary, 'overall.score');
        $score = is_numeric($scoreValue) ? round((float) $scoreValue, 2) : null;
        $submittedAt = $target->submitted_at ?: $attempt?->submitted_at;
        $isSubmitted = $target->status === 'selesai'
            || $target->submitted_at !== null
            || $attempt?->status === 'submitted'
            || $attempt?->submitted_at !== null;

        $status = $isSubmitted
            ? 'submitted'
            : ($attempt?->status === 'in_progress' || $target->status === 'dikerjakan'
                ? 'in_progress'
                : 'not_started');

        return [
            'target_id' => $target->id,
            'assignment_id' => $assignment->id,
            'assignment_title' => $assignment->judul_penugasan,
            'assignment_code' => $assignment->kode_penugasan,
            'evaluation_titles' => $assignment->assessments->pluck('judul')->implode(', '),
            'participant_name' => $target->guru?->nama_lengkap ?: 'Peserta tidak ditemukan',
            'school' => $target->guru?->satuan_pendidikan ?: '-',
            'kabupaten' => $target->guru?->kabupaten ?: '-',
            'status' => $status,
            'status_label' => match ($status) {
                'submitted' => 'Selesai',
                'in_progress' => 'Sedang dikerjakan',
                default => 'Belum mulai',
            },
            'is_submitted' => $isSubmitted,
            'score' => $score,
            'score_label' => $score !== null ? number_format($score, 2) : '-',
            'score_level' => $this->scoreLabel($score),
            'submitted_at' => $submittedAt?->format('d M Y H:i'),
            'submitted_at_sort' => $submittedAt?->timestamp ?? 0,
        ];
    }

    /**
     * @param Collection<int, AssessmentAssignment> $assignments
     * @return Collection<int, array<string, mixed>>
     */
    private function buildAssignmentRows(Collection $assignments): Collection
    {
        return $assignments->map(function (AssessmentAssignment $assignment) {
            $rows = $assignment->targets->map(fn ($target) => $this->serializeTarget($target, $assignment));
            $completed = $rows->where('is_submitted', true)->count();
            $scores = $rows->pluck('score')->filter(fn ($score) => $score !== null);

            return [
                'id' => $assignment->id,
                'code' => $assignment->kode_penugasan,
                'title' => $assignment->judul_penugasan,
                'evaluation_titles' => $assignment->assessments->pluck('judul')->implode(', '),
                'target_total' => $rows->count(),
                'completed_total' => $completed,
                'average_score' => $scores->isNotEmpty() ? round((float) $scores->avg(), 2) : null,
                'completion_rate' => $rows->isNotEmpty() ? round(($completed / $rows->count()) * 100, 1) : 0,
                'detail_url' => route('assessment.assignment.show', $assignment->id),
            ];
        })->values();
    }

    /**
     * @param Collection<int, mixed> $scores
     * @return array<int, array<string, mixed>>
     */
    private function buildScoreDistribution(Collection $scores): array
    {
        return collect(self::SCORE_LABELS)->map(function (string $label, int $scoreValue) use ($scores) {
            $count = $scores->filter(fn ($score) => $this->scoreBucket($score) === $scoreValue)->count();

            return [
                'label' => $label,
                'count' => $count,
                'percent' => $scores->isNotEmpty() ? round(($count / $scores->count()) * 100, 1) : 0,
            ];
        })->all();
    }

    private function scoreLabel(?float $score): ?string
    {
        $bucket = $this->scoreBucket($score);

        return $bucket ? self::SCORE_LABELS[$bucket] : null;
    }

    private function scoreBucket(?float $score): ?int
    {
        if ($score === null) {
            return null;
        }

        return min(max((int) round($score), 1), 4);
    }
}
