<?php

namespace App\Http\Controllers;

use App\Enum\AssessmentKetenagaanType;
use App\Models\AssessmentAssignment;
use App\Models\ValidatorAssignment;
use App\Models\ValidatorForm;
use App\Services\Assessment\ValidatorAssignmentService;
use App\Support\Assessment\ValidatorAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ValidatorAssignmentController extends Controller
{
    public function __construct(
        private readonly ValidatorAssignmentService $assignmentService
    ) {}

    public function index()
    {
        ValidatorAccess::authorizeAdmin();

        return view('pages.admin.assessment.validator.assignment.index', [
            'menu' => 'assessment-validator',
            'assignments' => ValidatorAssignment::with([
                'validatorForm',
                'assessment',
                'assessmentAssignments',
                'validator.guru',
            ])->withSummaryColumns()->newestFirst()->paginate(20),
        ]);
    }

    public function create()
    {
        ValidatorAccess::authorizeAdmin();

        return view('pages.admin.assessment.validator.assignment.create', [
            'menu' => 'assessment-validator',
            'forms' => ValidatorForm::where('status', 'published')
                ->where('is_active', true)
                ->withCount('sections')
                ->orderBy('title')
                ->get(),
            'assessmentAssignments' => AssessmentAssignment::active()
                ->whereIn('target_ketenagaan', [
                    AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
                    AssessmentKetenagaanType::TENAGA_KEPENDIDIKAN->value,
                ])
                ->has('assessments')
                ->newestFirst()
                ->get(['id']),
            'validators' => ValidatorAccess::eligibleUsersQuery()
                ->with('guru')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        ValidatorAccess::authorizeAdmin();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'validator_form_id' => ['required', 'integer', 'exists:validator_forms,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
        ], [
            'validator_form_id.required' => 'Form validator wajib dipilih.',
        ]);

        if (
            ! empty($validated['start_date'])
            && ! empty($validated['due_date'])
            && Carbon::parse($validated['due_date'])->lt(Carbon::parse($validated['start_date']))
        ) {
            throw ValidationException::withMessages([
                'due_date' => 'Batas waktu tidak boleh lebih awal dari tanggal mulai.',
            ]);
        }

        $result = $this->assignmentService->createForAllEligibleValidators(
            $validated,
            session('user_id') ? (int) session('user_id') : null
        );

        $message = $result['created'].' penugasan validator berhasil dibuat. '
            .'Validator juga otomatis ditambahkan sebagai peserta pada penugasan assessment aktif terkait.';

        if ($result['skipped'] > 0) {
            $message .= ' '.$result['skipped'].' validator dilewati karena sudah memiliki QA aktif yang sama.';
        }

        return redirect()
            ->route('assessment.validator.assignment.index')
            ->with('validator_success', $message);
    }

    public function show(ValidatorAssignment $assignment)
    {
        ValidatorAccess::authorizeAdmin();

        $assignment->load([
            'validatorForm.sections.fields',
            'assessment',
            'assessmentAssignments.assessments',
            'validator.guru',
            'responses.field',
        ]);

        return view('pages.admin.assessment.validator.assignment.show', [
            'menu' => 'assessment-validator',
            'assignment' => $assignment,
            'responseLookup' => $assignment->responses->keyBy('validator_form_field_id'),
        ]);
    }

    public function destroy(ValidatorAssignment $assignment)
    {
        ValidatorAccess::authorizeAdmin();

        if ($assignment->status === 'submitted') {
            throw ValidationException::withMessages([
                'assignment' => 'Hasil validasi yang sudah dikirim tidak dapat dihapus agar jejak audit tetap terjaga.',
            ]);
        }

        $assignment->delete();

        return redirect()
            ->route('assessment.validator.assignment.index')
            ->with('validator_success', 'Penugasan validator berhasil dihapus.');
    }
}
