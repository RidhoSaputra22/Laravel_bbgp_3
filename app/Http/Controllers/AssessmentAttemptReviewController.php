<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttemptAnswer;
use App\Services\Assessment\AssessmentAttemptLifecycleService;
use App\Services\Assessment\AssessmentAttemptService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssessmentAttemptReviewController extends Controller
{
    public function __construct(
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService,
        private readonly AssessmentAttemptService $attemptService
    ) {}

    public function show(string $targetId)
    {
        $this->authorizeAccess();

        $target = $this->findSubmittedTarget((int) $targetId);
        $attempt = $target->attempt;

        return view('pages.admin.assessment.assignment.review', [
            'menu' => 'assessment-penugasan',
            'target' => $target,
            'attempt' => $attempt,
            'summary' => $this->attemptService->buildResultSummary($attempt),
            'scoringSummary' => $this->attemptService->buildScoringSummary($attempt),
            'answerLookup' => $this->attemptService->buildAnswerLookup($attempt),
        ]);
    }

    public function file(AssessmentAttemptAnswer $answer): StreamedResponse
    {
        $this->authorizeAccess();
        $answer->loadMissing('attempt.target');

        $targetId = (int) ($answer->attempt?->assessment_assignment_target_id ?? 0);
        abort_unless($targetId > 0, 404);
        $target = $this->findSubmittedTarget($targetId);
        abort_unless((int) $target->attempt?->id === (int) $answer->assessment_attempt_id, 404);

        $path = trim((string) $answer->answer_file_path);
        abort_unless(
            $path !== '' && str_starts_with($path, 'assessment/attempts/') && ! str_contains($path, '..'),
            404
        );

        $disk = Storage::disk('assessment_private');

        if (! $disk->exists($path)) {
            $disk = Storage::disk('public');
        }

        abort_unless($disk->exists($path), 404);

        $stream = $disk->readStream($path);
        abort_unless(is_resource($stream), 404);

        $fileName = trim((string) data_get($answer->answer_payload, 'original_name')) ?: basename($path);
        $fileName = preg_replace('/[^A-Za-z0-9._ -]/', '_', Str::ascii($fileName)) ?: 'assessment-file';
        $fileName = trim($fileName, " .\t\n\r\0\x0B") ?: 'assessment-file';
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
        $contentDisposition = in_array($mimeType, [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ], true) ? 'inline' : 'attachment';

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => (string) $disk->size($path),
            'Content-Disposition' => $contentDisposition.'; filename="'.addcslashes($fileName, "\\\"").'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function update(string $targetId)
    {
        $this->authorizeAccess();

        $target = $this->findSubmittedTarget((int) $targetId);
        $attempt = $target->attempt;
        $this->attemptService->refreshScoringSummary($attempt);

        return redirect()
            ->route('assessment.assignment.review.show', $target->id)
            ->with('message', 'auto_scoring_only');
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }

    private function findSubmittedTarget(int $targetId): AssessmentAssignmentTarget
    {
        $target = AssessmentAssignmentTarget::with([
            'assignment.assessments.forms.fields',
            'assignment.combination',
            'session',
            'guru',
            'attempt.answers',
            'attempt.securityEvents',
        ])->findOrFail($targetId);

        $target = $this->attemptLifecycleService->syncExpiredTarget($target);

        abort_unless($target->attempt && $target->attempt->status === 'submitted', 404);

        return $target;
    }
}
