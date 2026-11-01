<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AssessmentPortalRemovalTest extends TestCase
{
    public function test_participant_assessment_portal_routes_are_removed(): void
    {
        foreach ([
            'assessment.portal.index',
            'assessment.portal.auth',
            'assessment.portal.login',
            'assessment.portal.dashboard',
            'assessment.portal.result',
            'assessment.portal.result.download',
            'assessment.portal.file',
            'assessment.portal.show',
            'assessment.portal.start',
            'assessment.portal.autosave',
            'assessment.portal.submit',
            'assessment.portal.logout',
            'assessment.portal.validator.tasks.draft',
            'assessment.portal.validator.tasks.submit',
            'assessment.portal.confirm',
            'assessment.portal.security.violation',
            'assessment.portal.security.disqualify',
            'assessment.preview.launch',
            'assessment.preview.file',
            'assessment.preview.show',
            'assessment.preview.start',
            'assessment.preview.autosave',
            'assessment.preview.security.violation',
            'assessment.preview.security.disqualify',
            'assessment.preview.submit',
            'assessment.preview.result',
        ] as $routeName) {
            $this->assertFalse(Route::has($routeName), "Route peserta {$routeName} masih terdaftar.");
        }

        $this->get('/assessment')->assertNotFound();
    }

    public function test_admin_assessment_routes_remain_registered(): void
    {
        foreach ([
            'assessment.index',
            'assessment.show',
            'assessment.edit',
            'assessment.combination.index',
            'assessment.assignment.index',
            'assessment.assignment.review.show',
            'assessment.assignment.review.file',
            'assessment.validator.form.index',
            'assessment.validator.assignment.index',
            'assessment.validator.task.index',
            'evaluasi.pelaksanaan.bank-soal.index',
        ] as $routeName) {
            $this->assertTrue(Route::has($routeName), "Route admin {$routeName} tidak terdaftar.");
        }
    }
}
