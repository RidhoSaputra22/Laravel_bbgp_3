<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
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
                'validator.guru',
            ])->newestFirst()->get(),
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
            'assessments' => Assessment::withCount(['forms', 'validatorAssignments'])
                ->where('is_active', true)
                ->orderBy('judul')
                ->get(),
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
            'assessment_id' => ['required', 'integer', 'exists:assessments,id'],
            'validator_user_id' => ['required', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
        ], [
            'validator_form_id.required' => 'Form validator wajib dipilih.',
            'assessment_id.required' => 'Assessment yang akan dijamin mutunya wajib dipilih.',
            'validator_user_id.required' => 'Validator wajib dipilih.',
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

        $assignment = $this->assignmentService->create(
            $validated,
            session('user_id') ? (int) session('user_id') : null
        );

        return redirect()->route('assessment.validator.assignment.show', $assignment)->with('message', 'store');
    }

    public function show(ValidatorAssignment $assignment)
    {
        ValidatorAccess::authorizeAdmin();

        $assignment->load([
            'validatorForm.sections.fields',
            'assessment',
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
