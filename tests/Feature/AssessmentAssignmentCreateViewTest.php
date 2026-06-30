<?php

namespace Tests\Feature;

use App\Models\AssessmentAssignment;
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
                    'target_jabatan' => ['Guru', 'Kepala Sekolah'],
                    'target_kabupaten' => ['Kota Makassar'],
                    'durasi_sesi_jam' => '3',
                    'jam_mulai' => '08:00',
                ],
            ])
            ->withViewErrors([])
            ->view('pages.admin.assessment.assignment.create', [
                'menu' => 'assessment-penugasan',
                'assignment' => null,
                'isEditMode' => false,
                'pageTitle' => 'Buat Penugasan Assessment',
                'formAction' => route('assessment.assignment.store'),
                'formMethod' => 'POST',
                'submitLabel' => 'Simpan Penugasan',
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
                'jabatanOptionsByKetenagaan' => [
                    'tenaga_pendidik' => [
                        [
                            'id' => 'Guru',
                            'label' => 'Guru',
                            'description' => '30 user pada Tenaga Pendidik',
                            'cells' => ['Guru', '30 user'],
                            'payload' => [
                                'jenis_jabatan' => 'Guru',
                                'ketenagaan' => 'tenaga_pendidik',
                                'ketenagaan_label' => 'Tenaga Pendidik',
                                'user_count' => 30,
                            ],
                        ],
                        [
                            'id' => 'Kepala Sekolah',
                            'label' => 'Kepala Sekolah',
                            'description' => '12 user pada Tenaga Pendidik',
                            'cells' => ['Kepala Sekolah', '12 user'],
                            'payload' => [
                                'jenis_jabatan' => 'Kepala Sekolah',
                                'ketenagaan' => 'tenaga_pendidik',
                                'ketenagaan_label' => 'Tenaga Pendidik',
                                'user_count' => 12,
                            ],
                        ],
                    ],
                    'tenaga_kependidikan' => [],
                    'stakeholder' => [
                        [
                            'id' => 'Kepala Dinas',
                            'label' => 'Kepala Dinas',
                            'description' => '12 user pada Stakeholder',
                            'cells' => ['Kepala Dinas', '12 user'],
                            'payload' => [
                                'jenis_jabatan' => 'Kepala Dinas',
                                'ketenagaan' => 'stakeholder',
                                'ketenagaan_label' => 'Stakeholder',
                                'user_count' => 12,
                            ],
                        ],
                    ],
                ],
                'kabupatenOptionsByKetenagaan' => [
                    'tenaga_pendidik' => [
                        [
                            'id' => 'Kota Makassar',
                            'label' => 'Kota Makassar',
                            'description' => '22 user lintas jabatan pada Tenaga Pendidik',
                            'cells' => ['Kota Makassar', '22 user'],
                            'payload' => [
                                'kabupaten' => 'Kota Makassar',
                                'ketenagaan' => 'tenaga_pendidik',
                                'ketenagaan_label' => 'Tenaga Pendidik',
                                'user_count' => 22,
                                'counts_by_jabatan' => [
                                    'Guru' => 18,
                                    'Kepala Sekolah' => 4,
                                ],
                            ],
                        ],
                        [
                            'id' => 'Kabupaten Gowa',
                            'label' => 'Kabupaten Gowa',
                            'description' => '20 user lintas jabatan pada Tenaga Pendidik',
                            'cells' => ['Kabupaten Gowa', '20 user'],
                            'payload' => [
                                'kabupaten' => 'Kabupaten Gowa',
                                'ketenagaan' => 'tenaga_pendidik',
                                'ketenagaan_label' => 'Tenaga Pendidik',
                                'user_count' => 20,
                                'counts_by_jabatan' => [
                                    'Guru' => 12,
                                    'Kepala Sekolah' => 8,
                                ],
                            ],
                        ],
                    ],
                    'tenaga_kependidikan' => [],
                    'stakeholder' => [
                        [
                            'id' => 'Kota Parepare',
                            'label' => 'Kota Parepare',
                            'description' => '12 user lintas jabatan pada Stakeholder',
                            'cells' => ['Kota Parepare', '12 user'],
                            'payload' => [
                                'kabupaten' => 'Kota Parepare',
                                'ketenagaan' => 'stakeholder',
                                'ketenagaan_label' => 'Stakeholder',
                                'user_count' => 12,
                                'counts_by_jabatan' => [
                                    'Kepala Dinas' => 12,
                                ],
                            ],
                        ],
                    ],
                ],
                'batchThreshold' => 25,
                'sessionCapacity' => 41,
                'defaultSessionDurationHours' => 3,
                'sessionDurationOptions' => [1, 2, 3, 4],
            ]);

        $response->assertSee('name="target_ketenagaan"', false);
        $response->assertSee('id="auto-summary-assessment-list"', false);
        $response->assertSee('Jabatan Target');
        $response->assertSee('Kabupaten Target');
        $response->assertSee('const ketenagaanSummaries =', false);
        $response->assertSee('const jabatanOptionsByKetenagaan =', false);
        $response->assertSee('const kabupatenOptionsByKetenagaan =', false);
        $response->assertSee('id="assignment-ketenagaan-tenaga_pendidik"', false);
        $response->assertSee('data-table-id="assignment-jabatan-selector"', false);
        $response->assertSee('data-table-id="assignment-kabupaten-selector"', false);
        $response->assertSee('Kepala Sekolah');
        $response->assertSee('Kota Makassar');
        $response->assertDontSee('data-table-id="guru-selector"', false);
        $response->assertDontSee('data-table-id="assessment-selector"', false);
    }

    public function test_assignment_edit_view_shows_reset_warning_modal_for_admin(): void
    {
        $assignment = new AssessmentAssignment([
            'id' => 99,
            'judul_penugasan' => 'Penugasan Ulang Assessment',
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['Guru'],
            'target_kabupaten' => ['Kota Makassar'],
            'durasi_sesi_jam' => 3,
            'total_target' => 12,
        ]);

        $response = $this
            ->withSession([
                'role' => 'admin',
                'user_id' => 1,
                'name' => 'Admin Test',
            ])
            ->withViewErrors([])
            ->view('pages.admin.assessment.assignment.create', [
                'menu' => 'assessment-penugasan',
                'assignment' => $assignment,
                'isEditMode' => true,
                'pageTitle' => 'Edit Penugasan Assessment',
                'formAction' => route('assessment.assignment.update', 99),
                'formMethod' => 'PUT',
                'submitLabel' => 'Simpan Perubahan',
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
                        'assessment_items' => [],
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
                'jabatanOptionsByKetenagaan' => [
                    'tenaga_pendidik' => [
                        [
                            'id' => 'Guru',
                            'label' => 'Guru',
                            'description' => '30 user pada Tenaga Pendidik',
                            'cells' => ['Guru', '30 user'],
                            'payload' => [
                                'jenis_jabatan' => 'Guru',
                                'ketenagaan' => 'tenaga_pendidik',
                                'ketenagaan_label' => 'Tenaga Pendidik',
                                'user_count' => 30,
                            ],
                        ],
                    ],
                    'tenaga_kependidikan' => [],
                    'stakeholder' => [],
                ],
                'kabupatenOptionsByKetenagaan' => [
                    'tenaga_pendidik' => [
                        [
                            'id' => 'Kota Makassar',
                            'label' => 'Kota Makassar',
                            'description' => '22 user lintas jabatan pada Tenaga Pendidik',
                            'cells' => ['Kota Makassar', '22 user'],
                            'payload' => [
                                'kabupaten' => 'Kota Makassar',
                                'ketenagaan' => 'tenaga_pendidik',
                                'ketenagaan_label' => 'Tenaga Pendidik',
                                'user_count' => 22,
                                'counts_by_jabatan' => [
                                    'Guru' => 22,
                                ],
                            ],
                        ],
                    ],
                    'tenaga_kependidikan' => [],
                    'stakeholder' => [],
                ],
                'batchThreshold' => 25,
                'sessionCapacity' => 41,
                'defaultSessionDurationHours' => 3,
                'sessionDurationOptions' => [1, 2, 3, 4],
            ]);

        $response->assertSee('Mode edit akan menyusun ulang penugasan dari nol.');
        $response->assertSee('id="assignmentEditWarningModal"', false);
        $response->assertSee('Reset Penugasan Saat Edit');
        $response->assertSee('Ya, Reset dan Simpan');
        $response->assertSee('assignment-edit-confirm-button');
    }
}
