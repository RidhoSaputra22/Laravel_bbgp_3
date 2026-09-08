<?php

namespace App\Http\Controllers;

use App\Models\ValidatorAssignment;
use App\Models\ValidatorForm;
use App\Support\Assessment\ValidatorAccess;

class ValidatorPanelController extends Controller
{
    public function index()
    {
        ValidatorAccess::authorizeAdmin();

        return view('pages.admin.assessment.validator.panel', [
            'menu' => 'assessment-validator',
            'formCount' => ValidatorForm::count(),
            'activeFormCount' => ValidatorForm::where('status', 'published')->where('is_active', true)->count(),
            'assignmentCount' => ValidatorAssignment::count(),
            'pendingCount' => ValidatorAssignment::whereIn('status', ['assigned', 'in_progress'])->count(),
            'submittedCount' => ValidatorAssignment::where('status', 'submitted')->count(),
            'recentAssignments' => ValidatorAssignment::with(['assessment', 'validator.guru'])
                ->newestFirst()
                ->limit(5)
                ->get(),
        ]);
    }
}
