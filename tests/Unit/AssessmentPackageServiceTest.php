<?php

namespace Tests\Unit;

use App\Services\Assessment\AssessmentPackageService;
use Tests\TestCase;

class AssessmentPackageServiceTest extends TestCase
{
    public function test_it_accepts_the_project_assessment_schema(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'assessment-valid-');
        file_put_contents($path, json_encode([
            'meta' => ['schema' => 'database-assessment-v1', 'version' => 1],
            'data' => [
                'kode_assessment' => 'ASM-VALID',
                'judul' => 'Valid',
                'instrument_type' => 'monitoring_observasi_eviden',
                'target_ketenagaan' => 'tenaga_pendidik',
                'target_jabatan' => ['__all__'],
                'scoring_config' => ['weight' => 0.2],
                'status' => 'draft',
                'is_active' => true,
                'forms' => [
                    [
                        'kode_form' => 'FORM-01',
                        'judul_form' => 'Form',
                        'kompetensi' => 'profesional',
                        'is_scoreable' => true,
                        'scoring_config' => ['weight' => 1],
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Pertanyaan',
                                'nama_field' => 'pertanyaan',
                                'tipe_field' => 'textarea',
                                'validasi' => ['required' => true],
                                'scoring_config' => ['enabled' => true, 'method' => 'presence'],
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Skala',
                                'nama_field' => 'skala',
                                'tipe_field' => 'likert',
                                'opsi_field' => [
                                    ['label' => 'Sangat Setuju', 'value' => '5', 'score' => 5],
                                ],
                                'urutan' => 2,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'included' => ['assignment_configs' => []],
        ], JSON_UNESCAPED_UNICODE));

        $result = app(AssessmentPackageService::class)->validateFile($path);
        unlink($path);

        $this->assertTrue($result['valid'], implode(' ', $result['errors']));
        $this->assertSame(2, $result['summary']['fields']);
    }

    public function test_it_rejects_invalid_structure_before_import(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'assessment-invalid-');
        file_put_contents($path, json_encode([
            'meta' => ['schema' => 'wrong-schema', 'version' => 1],
            'data' => [
                'kode_assessment' => 'ASM-INVALID',
                'judul' => 'Invalid',
                'status' => 'draft',
                'is_active' => true,
                'forms' => [[
                    'judul_form' => 'Form',
                    'is_scoreable' => true,
                    'urutan' => 1,
                    'is_active' => true,
                    'fields' => [[
                        'label' => 'Pertanyaan',
                        'nama_field' => 'pertanyaan',
                        'tipe_field' => 'text',
                        'urutan' => 1,
                        'is_required' => false,
                        'is_active' => true,
                    ]],
                ]],
            ],
        ], JSON_UNESCAPED_UNICODE));

        $result = app(AssessmentPackageService::class)->validateFile($path);
        unlink($path);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('meta.schema', implode(' ', $result['errors']));
    }

    public function test_it_rejects_unknown_and_distorted_assignment_schema(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'assessment-invalid-assignment-');
        file_put_contents($path, json_encode([
            'meta' => ['schema' => 'database-assessment-v1', 'version' => 1],
            'data' => [
                'kode_assessment' => 'ASM-INVALID-ASSIGNMENT',
                'judul' => 'Invalid assignment',
                'status' => 'draft',
                'is_active' => true,
                'forms' => [[
                    'judul_form' => 'Form',
                    'is_scoreable' => true,
                    'urutan' => 1,
                    'is_active' => true,
                    'fields' => [[
                        'label' => 'Pertanyaan',
                        'nama_field' => 'pertanyaan',
                        'tipe_field' => 'text',
                        'urutan' => 1,
                        'is_required' => false,
                        'is_active' => true,
                    ]],
                ]],
            ],
            'included' => [
                'assignment_configs' => [[
                    'kode_penugasan' => 'TUGAS-INVALID',
                    'judul_penugasan' => 'Invalid assignment',
                    'is_active' => true,
                    'session_enabled' => true,
                    'assessments' => [[
                        'kode_assessment' => 'ASM-LAIN',
                        'urutan' => 1,
                        'stage_config' => [],
                        'unexpected' => true,
                    ]],
                    'sessions' => [[
                        'nomor_sesi' => 1,
                        'label_sesi' => 'Sesi 1',
                        'kapasitas_peserta' => 10,
                    ], [
                        'nomor_sesi' => 1,
                        'label_sesi' => 'Duplikat',
                    ]],
                ]],
            ],
        ], JSON_UNESCAPED_UNICODE));

        $result = app(AssessmentPackageService::class)->validateFile($path);
        unlink($path);

        $errors = implode(' ', $result['errors']);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('unexpected', $errors);
        $this->assertStringContainsString('Stage harus merujuk', $errors);
        $this->assertStringContainsString('Nomor sesi tidak boleh berulang', $errors);
    }
}
