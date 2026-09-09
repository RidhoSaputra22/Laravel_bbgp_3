<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ValidatorAssignment;
use App\Services\Assessment\AssessmentPortalAuthService;
use App\Services\Assessment\ValidatorAssignmentService;
use App\Support\Assessment\ValidatorAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ValidatorTaskController extends Controller
{
    public function __construct(
        private readonly ValidatorAssignmentService $assignmentService,
        private readonly AssessmentPortalAuthService $portalAuthService
    ) {}

    public function index(): RedirectResponse
    {
        $user = ValidatorAccess::authorizeValidator();

        return $this->redirectToPortal($user);
    }

    public function show(ValidatorAssignment $assignment): RedirectResponse
    {
        $user = $this->authorizeAssignment($assignment);

        return $this->redirectToPortal($user, $assignment);
    }

    public function saveDraft(Request $request, ValidatorAssignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $this->ensureEditable($assignment);
        $assignment->load('validatorForm.sections.fields');
        $answers = $this->validateAnswers($request, $assignment, false);
        $conclusion = $request->validate([
            'recommendation' => ['nullable', Rule::in(array_keys(ValidatorAssignment::RECOMMENDATIONS))],
            'final_notes' => ['nullable', 'string', 'max:10000'],
        ]);

        $this->assignmentService->saveResponses($assignment, $answers);
        $assignment->update([
            'recommendation' => $conclusion['recommendation'] ?? null,
            'final_notes' => $conclusion['final_notes'] ?? null,
        ]);

        return redirect()
            ->route('assessment.portal.dashboard', ['validator_task' => $assignment->id])
            ->with('validator_success', 'Draf validasi berhasil disimpan.');
    }

    public function submit(Request $request, ValidatorAssignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $this->ensureEditable($assignment);
        $assignment->load('validatorForm.sections.fields');
        $answers = $this->validateAnswers($request, $assignment, true);

        $validatedConclusion = $request->validate([
            'recommendation' => ['required', Rule::in(array_keys(ValidatorAssignment::RECOMMENDATIONS))],
            'final_notes' => ['nullable', 'string', 'max:10000'],
        ], [
            'recommendation.required' => 'Rekomendasi akhir wajib dipilih.',
        ]);

        if (
            $validatedConclusion['recommendation'] !== 'approved'
            && blank($validatedConclusion['final_notes'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'final_notes' => 'Catatan akhir wajib diisi apabila assessment membutuhkan revisi atau belum layak.',
            ]);
        }

        $this->assignmentService->submit(
            $assignment,
            $answers,
            $validatedConclusion['recommendation'],
            $validatedConclusion['final_notes'] ?? null
        );

        return redirect()
            ->route('assessment.portal.dashboard', ['validator_task' => $assignment->id])
            ->with('validator_success', 'Hasil quality assurance berhasil dikirim dan dikunci.');
    }

    private function validateAnswers(
        Request $request,
        ValidatorAssignment $assignment,
        bool $requireComplete
    ): array {
        $fields = $assignment->validatorForm->sections
            ->flatMap->fields
            ->where('is_active', true);
        $rules = ['answers' => ['nullable', 'array']];
        $messages = [];

        foreach ($fields as $field) {
            $key = 'answers.'.$field->id;
            $required = $requireComplete && $field->is_required ? 'required' : 'nullable';
            $options = $field->resolvedOptions();

            $rules[$key] = match ($field->field_type) {
                'number' => [$required, 'numeric', 'min:0', $field->max_score ? 'max:'.$field->max_score : 'max:1000000000'],
                'date' => [$required, 'date'],
                'select', 'radio', 'likert' => [$required, 'string', Rule::in($options)],
                'checkbox' => [$required, 'array', $field->is_required && $requireComplete ? 'min:1' : 'max:100'],
                'textarea' => [$required, 'string', 'max:10000'],
                default => [$required, 'string', 'max:1000'],
            };

            if ($field->field_type === 'checkbox') {
                $rules[$key.'.*'] = ['string', Rule::in($options)];
            }

            $messages[$key.'.required'] = 'Butir “'.$field->label.'” wajib diisi.';
            $messages[$key.'.in'] = 'Jawaban untuk “'.$field->label.'” tidak valid.';
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        return (array) ($validator->validate()['answers'] ?? []);
    }

    private function authorizeAssignment(ValidatorAssignment $assignment): User
    {
        if (request()->routeIs('assessment.portal.validator.*')) {
            $user = $this->portalAuthService->currentUser()?->loadMissing('guru');
            abort_unless(ValidatorAccess::isEligibleUser($user), 403);
        } else {
            $user = ValidatorAccess::authorizeValidator();
        }

        abort_unless((int) $assignment->validator_user_id === (int) $user->id, 403);

        return $user;
    }

    private function redirectToPortal(
        User $user,
        ?ValidatorAssignment $assignment = null
    ): RedirectResponse {
        $guru = $user->relationLoaded('guru') ? $user->guru : $user->guru()->first();
        abort_unless($guru, 403);
        $this->portalAuthService->storeSession($user, $guru);

        return redirect()->route(
            'assessment.portal.dashboard',
            $assignment ? ['validator_task' => $assignment->id] : []
        );
    }

    private function ensureEditable(ValidatorAssignment $assignment): void
    {
        if ($assignment->status === 'submitted') {
            throw ValidationException::withMessages([
                'assignment' => 'Hasil validasi ini sudah dikirim dan tidak dapat diubah.',
            ]);
        }

        if ($assignment->status === 'cancelled') {
            throw ValidationException::withMessages([
                'assignment' => 'Penugasan ini telah dibatalkan.',
            ]);
        }

        if ($assignment->start_date && $assignment->start_date->isFuture()) {
            throw ValidationException::withMessages([
                'assignment' => 'Penugasan baru dapat dikerjakan mulai '.$assignment->start_date->format('d-m-Y').'.',
            ]);
        }
    }
}
