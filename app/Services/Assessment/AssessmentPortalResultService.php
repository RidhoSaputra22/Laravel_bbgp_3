<?php

namespace App\Services\Assessment;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\Guru;
use App\Support\Assessment\AssessmentAnswerViewHelper;
use App\Support\Assessment\AssessmentCertificateLinkHelper;
use App\Support\Assessment\AssessmentPdfPreviewImageHelper;
use App\Support\Assessment\AssessmentTrainingSummaryHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssessmentPortalResultService
{
    private const ADMIN_RESULT_ROLES = ['admin', 'superadmin', 'kepala', 'database'];

    public function __construct(
        private readonly AssessmentPortalAuthService $authService,
        private readonly AssessmentPortalService $portalService,
        private readonly AssessmentAttemptLifecycleService $attemptLifecycleService,
        private readonly AssessmentAttemptService $attemptService
    ) {}

    /**
     * @return array<string, mixed>|RedirectResponse
     */
    public function resolveContext(Request $request, int $targetId): array|RedirectResponse
    {
        $viewerMode = 'participant';

        if ($this->canAdminViewParticipantResult()) {
            $viewerMode = 'admin';
            $target = $this->findTargetForAdminResult($targetId);
            $guru = $target->guru;

            abort_unless($guru, 404);
        } elseif ($this->authService->isAuthenticated()) {
            $guru = $this->authService->currentGuru();

            abort_unless($guru, 403);

            $target = $this->portalService->findTargetForGuru($guru, $targetId);
        } else {
            return redirect()
                ->route('assessment.portal.auth')
                ->with('assessment_portal_notice', 'Silakan login terlebih dahulu untuk melihat hasil assessment.');
        }

        $target = $this->attemptLifecycleService->syncExpiredTarget($target);
        $attempt = $target->attempt;

        if (! $attempt) {
            if ($viewerMode === 'admin') {
                return redirect()
                    ->to($this->resolveAdminResultBackUrl($request, $target))
                    ->withErrors([
                        'portal' => 'Peserta ini belum memulai assessment.',
                    ]);
            }

            return redirect()->route('assessment.portal.show', $target->id);
        }

        if ($attempt->status !== 'submitted') {
            if ($viewerMode === 'admin') {
                return redirect()
                    ->to($this->resolveAdminResultBackUrl($request, $target))
                    ->withErrors([
                        'portal' => 'Assessment peserta ini belum selesai dikirim.',
                    ]);
            }

            return redirect()
                ->route('assessment.portal.show', $target->id)
                ->withErrors([
                    'portal' => 'Assessment ini belum selesai dikirim.',
                ]);
        }

        return [
            'viewerMode' => $viewerMode,
            'guru' => $guru,
            'target' => $target,
            'attempt' => $attempt,
        ];
    }

    /**
     * @param  array<string, mixed>  $resultContext
     * @return array<string, mixed>
     */
    public function buildResultViewData(Request $request, array $resultContext): array
    {
        $viewerMode = (string) $resultContext['viewerMode'];
        /** @var Guru $guru */
        $guru = $resultContext['guru'];
        /** @var AssessmentAssignmentTarget $target */
        $target = $resultContext['target'];
        /** @var AssessmentAttempt $attempt */
        $attempt = $resultContext['attempt'];
        $answerLookup = $this->attemptService->buildAnswerLookup($attempt);
        $structureSnapshot = $this->resolveStructureSnapshot($attempt);
        $isStakeholderDownloadAvailable = $this->canDownloadStakeholderResult($target);

        return [
            'menu' => $viewerMode === 'admin' ? 'assessment-monitoring' : 'assessment-portal',
            'guru' => $guru,
            'target' => $target,
            'attempt' => $attempt,
            'meta' => $this->portalService->buildTargetMeta($target),
            'summary' => $this->attemptService->buildResultSummary($attempt),
            'scoringSummary' => $this->attemptService->buildScoringSummary($attempt),
            'answerLookup' => $answerLookup,
            'trainingSummary' => AssessmentTrainingSummaryHelper::buildAttemptSummaryFromSnapshot(
                $structureSnapshot,
                $answerLookup
            ),
            'certificateLinks' => AssessmentCertificateLinkHelper::collectFromSnapshot(
                $structureSnapshot,
                $answerLookup
            ),
            'viewerMode' => $viewerMode,
            'backUrl' => $viewerMode === 'admin'
                ? $this->resolveAdminResultBackUrl($request, $target)
                : route('assessment.portal.dashboard'),
            'backLabel' => $viewerMode === 'admin'
                ? 'Kembali ke Monitoring'
                : 'Kembali ke Dashboard',
            'isStakeholderDownloadAvailable' => $isStakeholderDownloadAvailable,
            'stakeholderResultDownloadUrl' => $isStakeholderDownloadAvailable
                ? route('assessment.portal.result.download', $target->id)
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $resultContext
     * @return array<string, mixed>
     */
    public function buildStakeholderPdfViewData(array $resultContext): array
    {
        /** @var Guru $guru */
        $guru = $resultContext['guru'];
        /** @var AssessmentAssignmentTarget $target */
        $target = $resultContext['target'];
        /** @var AssessmentAttempt $attempt */
        $attempt = $resultContext['attempt'];
        $answerLookup = $this->attemptService->buildAnswerLookup($attempt);

        return [
            'guru' => $guru,
            'target' => $target,
            'attempt' => $attempt,
            'summary' => $this->attemptService->buildResultSummary($attempt),
            'scoringSummary' => $this->attemptService->buildScoringSummary($attempt),
            'generatedAt' => now(),
            'assessmentSections' => $this->buildStakeholderPdfSections($attempt, $answerLookup),
            'targetKetenagaanLabel' => $target->assignment->target_ketenagaan_label ?: 'Stakeholder',
        ];
    }

    public function canDownloadStakeholderResult(AssessmentAssignmentTarget $target): bool
    {
        return AssessmentKetenagaanType::tryFromMixed($target->assignment->target_ketenagaan)
            === AssessmentKetenagaanType::STAKEHOLDER;
    }

    public function buildStakeholderPdfFilename(AssessmentAssignmentTarget $target, Guru $guru): string
    {
        $assignmentCode = Str::slug((string) ($target->assignment->kode_penugasan ?: 'assignment'));
        $participantName = Str::slug((string) ($guru->nama_lengkap ?: 'peserta'));

        return sprintf('hasil-assessment-stakeholder-%s-%s.pdf', $assignmentCode, $participantName);
    }

    private function canAdminViewParticipantResult(): bool
    {
        return session('cek') && in_array(session('role'), self::ADMIN_RESULT_ROLES, true);
    }

    private function findTargetForAdminResult(int $targetId): AssessmentAssignmentTarget
    {
        return AssessmentAssignmentTarget::with([
            'assignment.assessments.forms.fields',
            'assignment.combination',
            'combination',
            'session',
            'guru',
            'attempt.answers',
            'attempt.securityEvents',
        ])->findOrFail($targetId);
    }

    private function resolveAdminResultBackUrl(Request $request, AssessmentAssignmentTarget $target): string
    {
        $referer = (string) $request->headers->get('referer', '');

        if (
            $referer !== ''
            && $referer !== $request->fullUrl()
            && str_contains($referer, '/dashboard/assessment/')
        ) {
            return $referer;
        }

        return route('assessment.assignment.show', $target->assessment_assignment_id).'#monitoring-explorer';
    }

    /**
     * @param  array<int, array<string, mixed>>  $answerLookup
     * @return array<int, array<string, mixed>>
     */
    private function buildStakeholderPdfSections(AssessmentAttempt $attempt, array $answerLookup): array
    {
        $snapshot = $this->resolveStructureSnapshot($attempt);

        return collect($snapshot['assessments'] ?? [])
            ->values()
            ->map(function (array $assessment) use ($answerLookup) {
                $isMultipleChoiceAssessment = ($assessment['instrument_type'] ?? null)
                    === AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value;
                $questionNumber = 1;

                $forms = collect($assessment['forms'] ?? [])
                    ->values()
                    ->map(function (array $form) use (&$questionNumber, $answerLookup, $isMultipleChoiceAssessment) {
                        $questions = collect($form['fields'] ?? [])
                            ->values()
                            ->map(function (array $field) use (&$questionNumber, $answerLookup, $isMultipleChoiceAssessment) {
                                $fieldId = (int) ($field['id'] ?? 0);

                                if ($fieldId <= 0) {
                                    return null;
                                }

                                $answer = $answerLookup[$fieldId] ?? null;
                                $questionItem = [
                                    'field_id' => $fieldId,
                                    'type' => $field['tipe_field'] ?? 'text',
                                    'label' => $this->buildDisplayFieldLabel(
                                        $field,
                                        $questionNumber,
                                        $isMultipleChoiceAssessment ? 'Soal' : null
                                    ),
                                    'description' => trim((string) ($field['deskripsi'] ?? '')),
                                    'help' => trim((string) ($field['bantuan'] ?? '')),
                                    'is_required' => (bool) ($field['is_required'] ?? false),
                                    'has_answer' => AssessmentAnswerViewHelper::hasAnswer($field, $answer),
                                    'answer_text' => AssessmentAnswerViewHelper::resolveAnswerText($field, $answer),
                                    'answered_at' => data_get($answer, 'answered_at'),
                                    'repeater_columns' => AssessmentAnswerViewHelper::resolveRepeaterColumns($field, $answer),
                                    'repeater_rows' => AssessmentAnswerViewHelper::resolveRepeaterRows($answer),
                                    'file_name' => trim((string) (data_get($answer, 'payload.original_name')
                                        ?: data_get($answer, 'text', ''))),
                                    'file_url' => data_get($answer, 'file_url'),
                                    'file_preview_data_uri' => AssessmentPdfPreviewImageHelper::buildDataUri(
                                        data_get($answer, 'file_path')
                                    ),
                                ];

                                $questionNumber++;

                                return $questionItem;
                            })
                            ->filter()
                            ->values()
                            ->all();

                        if ($questions === []) {
                            return null;
                        }

                        return [
                            'title' => $form['judul_form'] ?? 'Form Tanpa Judul',
                            'code' => $form['kode_form'] ?? null,
                            'description' => trim((string) ($form['deskripsi'] ?? '')),
                            'competency_label' => $form['kompetensi_label'] ?? null,
                            'indicator_code' => $form['indikator_kode'] ?? null,
                            'indicator_label' => $form['indikator_label'] ?? null,
                            'questions' => $questions,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                if ($forms === []) {
                    return null;
                }

                return [
                    'title' => $assessment['judul'] ?? 'Assessment',
                    'code' => $assessment['kode_assessment'] ?? null,
                    'description' => trim((string) ($assessment['deskripsi'] ?? '')),
                    'instrument_label' => $assessment['instrument_label'] ?? null,
                    'forms' => $forms,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function buildDisplayFieldLabel(
        array $field,
        ?int $displayQuestionNumber = null,
        ?string $displayQuestionPrefix = null
    ): string {
        $normalizedLabel = $this->normalizeFieldLabel($field);

        if (! $displayQuestionNumber || $normalizedLabel === '') {
            return $normalizedLabel;
        }

        $displayLead = filled($displayQuestionPrefix)
            ? trim($displayQuestionPrefix).' '.$displayQuestionNumber
            : (string) $displayQuestionNumber;

        return trim($displayLead.'. '.$normalizedLabel);
    }

    private function normalizeFieldLabel(array $field): string
    {
        $fieldLabel = trim((string) ($field['label'] ?? ''));

        if ($fieldLabel === '') {
            return $fieldLabel;
        }

        return preg_replace(
            '/^\s*(?:soal\s*)?\d+\s*[\.\)\-:]?\s*/iu',
            '',
            $fieldLabel,
            1
        ) ?? $fieldLabel;
    }

    private function resolveStructureSnapshot(AssessmentAttempt $attempt): array
    {
        return is_array($attempt->structure_snapshot ?? null) ? $attempt->structure_snapshot : [];
    }
}
