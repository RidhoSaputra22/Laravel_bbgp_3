<?php

namespace Tests\Feature;

use Tests\TestCase;

class AssessmentAssignmentCreateViewTest extends TestCase
{
    public function test_assignment_create_view_uses_ketenagaan_auto_assignment_markup(): void
    {
        $response = $this
            ->withSession([
                'role' => 'admin',
                'user_id' => 1,
                'name' => 'Admin Test',
                '_old_input' => [
                    'target_ketenagaan' => 'tenaga_pendidik',
                    'durasi_sesi_jam' => '3',
                    'jam_mulai' => '08:00',
                ],
            ])
            ->withViewErrors([])
            ->view('pages.admin.assessment.assignment.create', [
                'menu' => 'assessment-penugasan',
                'ketenagaanOptions' => [
                    'tenaga_pendidik' => 'Tenaga Pendidik',
                    'tenaga_kependidikan' => 'Tenaga Kependidikan',
                    'stakeholder' => 'Stakeholder',
                ],
                'ketenagaanSummaries' => [
                    'tenaga_pendidik' => [
                        'value' => 'tenaga_pendidik',
                        'label' => 'Tenaga Pendidik',
                        'badge_class' => 'primary',
                        'icon_class' => 'fas fa-chalkboard-teacher',
                        'assessment_count' => 2,
                        'form_count' => 5,
                        'field_count' => 17,
                        'user_count' => 42,
                        'assessment_items' => [
                            [
                                'id' => 5,
                                'kode' => 'ASM-001',
                                'judul' => 'Assessment Monitoring',
                                'status' => 'Publish',
                                'forms' => 3,
                                'fields' => 9,
                            ],
                        ],
                    ],
                    'tenaga_kependidikan' => [
                        'value' => 'tenaga_kependidikan',
                        'label' => 'Tenaga Kependidikan',
                        'badge_class' => 'info',
                        'icon_class' => 'fas fa-school',
                        'assessment_count' => 0,
                        'form_count' => 0,
                        'field_count' => 0,
                        'user_count' => 0,
                        'assessment_items' => [],
                    ],
                    'stakeholder' => [
                        'value' => 'stakeholder',
                        'label' => 'Stakeholder',
                        'badge_class' => 'warning',
                        'icon_class' => 'fas fa-layer-group',
                        'assessment_count' => 1,
                        'form_count' => 2,
                        'field_count' => 6,
                        'user_count' => 12,
                        'assessment_items' => [],
                    ],
                ],
                'batchThreshold' => 25,
                'sessionCapacity' => 41,
                'defaultSessionDurationHours' => 3,
                'sessionDurationOptions' => [1, 2, 3, 4],
            ]);

        $response->assertSee('name="target_ketenagaan"', false);
        $response->assertSee('id="auto-summary-assessment-list"', false);
        $response->assertSee('Pilih ketenagaan terlebih dahulu');
        $response->assertSee('const ketenagaanSummaries =', false);
        $response->assertDontSee('data-table-id="guru-selector"', false);
        $response->assertDontSee('data-table-id="assessment-selector"', false);
    }
}
