<?php

namespace Tests\Feature;

use App\Models\Assessment;
use Tests\TestCase;

class AssessmentAssignmentCreateViewTest extends TestCase
{
    public function test_assignment_create_view_uses_paginated_guru_table_markup(): void
    {
        $assessment = new Assessment([
            'kode_assessment' => 'ASM-001',
            'judul' => 'Assessment Monitoring',
            'status' => 'publish',
        ]);
        $assessment->id = 5;
        $assessment->forms_count = 3;
        $assessment->fields_count = 9;

        $response = $this
            ->withSession([
                'role' => 'admin',
                'user_id' => 1,
                'name' => 'Admin Test',
                '_old_input' => [
                    'assessment_ids' => ['5'],
                    'durasi_sesi_jam' => '3',
                    'jam_mulai' => '08:00',
                ],
            ])
            ->withViewErrors([])
            ->view('pages.admin.assessment.assignment.create', [
                'menu' => 'assessment-penugasan',
                'assessmentList' => collect([$assessment]),
                'selectedGuruIds' => [12, 18],
                'selectedGuruItems' => [
                    [
                        'id' => '12',
                        'label' => 'Guru Pertama',
                        'description' => 'guru1@example.com | Instansi A | Kota A | Terverifikasi',
                        'cells' => ['Guru Pertama', 'guru1@example.com', 'Instansi A', 'Kota A', 'Terverifikasi'],
                        'payload' => [
                            'nama' => 'Guru Pertama',
                        ],
                    ],
                    [
                        'id' => '18',
                        'label' => 'Guru Kedua',
                        'description' => 'guru2@example.com | Instansi B | Kota B | Terverifikasi',
                        'cells' => ['Guru Kedua', 'guru2@example.com', 'Instansi B', 'Kota B', 'Terverifikasi'],
                        'payload' => [
                            'nama' => 'Guru Kedua',
                        ],
                    ],
                ],
                'batchThreshold' => 25,
                'sessionCapacity' => 41,
                'defaultSessionDurationHours' => 3,
                'sessionDurationOptions' => [1, 2, 3, 4],
            ]);

        $response->assertSee('data-table-id="guru-selector"', false);
        $response->assertSee('guru-options', false);
        $response->assertSee('data-ajax-url=', false);
        $response->assertSee('3 form / 9 pertanyaan');
        $response->assertDontSee('assignment-select2', false);
    }
}
