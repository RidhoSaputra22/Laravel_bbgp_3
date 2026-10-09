<?php

namespace Tests\Unit;

use App\Support\Assessment\AssessmentJsonStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AssessmentJsonStreamTest extends TestCase
{
    public function test_it_streams_forms_fields_and_assignment_configs(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'assessment-json-');
        file_put_contents($path, json_encode([
            'meta' => [
                'schema' => 'database-assessment-v1',
                'version' => 1,
            ],
            'data' => [
                'kode_assessment' => 'ASM-STREAM',
                'judul' => 'Assessment Streaming',
                'status' => 'draft',
                'is_active' => true,
                'forms' => [
                    [
                        'judul_form' => 'Form Satu',
                        'is_scoreable' => true,
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Pertanyaan 1',
                                'nama_field' => 'pertanyaan_1',
                                'tipe_field' => 'text',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Pertanyaan 2',
                                'nama_field' => 'pertanyaan_2',
                                'tipe_field' => 'number',
                                'urutan' => 2,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                'assignment_configs' => [[
                    'kode_penugasan' => 'TUGAS-STREAM',
                    'judul_penugasan' => 'Penugasan Streaming',
                    'is_active' => true,
                    'session_enabled' => true,
                    'assessments' => [[
                        'kode_assessment' => 'ASM-STREAM',
                        'urutan' => 1,
                    ]],
                ]],
            ],
        ], JSON_UNESCAPED_UNICODE));

        $forms = 0;
        $fields = 0;
        $assignments = 0;
        $assessmentCode = null;

        $result = AssessmentJsonStream::open($path)->scan(
            function (array $assessment) use (&$assessmentCode): void {
                $assessmentCode = $assessment['kode_assessment'];
            },
            function (array $form) use (&$forms): void {
                $forms++;
                $this->assertSame('Form Satu', $form['judul_form']);
            },
            function (array $field) use (&$fields): void {
                $fields++;
                $this->assertArrayHasKey('nama_field', $field);
            },
            function (array $assignment) use (&$assignments): void {
                $assignments++;
                $this->assertSame('TUGAS-STREAM', $assignment['kode_penugasan']);
            }
        );

        unlink($path);

        $this->assertSame('ASM-STREAM', $assessmentCode);
        $this->assertSame(1, $forms);
        $this->assertSame(2, $fields);
        $this->assertSame(1, $assignments);
        $this->assertSame(['forms' => 1, 'fields' => 2, 'assignments' => 1], $result['counts']);
    }

    public function test_it_rejects_duplicate_keys_and_trailing_json(): void
    {
        $this->assertInvalidJson(
            '{"meta":{},"meta":{},"data":{"kode_assessment":"ASM","forms":[]}}',
            'Properti root \'meta\' tidak boleh berulang.'
        );
        $this->assertInvalidJson(
            '{"meta":{},"data":{"kode_assessment":"ASM","forms":[]}} trailing',
            'Terdapat data setelah akhir JSON.'
        );
    }

    public function test_it_requires_forms_to_be_the_last_streamed_data_property(): void
    {
        $this->assertInvalidJson(
            '{"data":{"forms":[],"judul":"terlambat"}}',
            'Properti data.forms harus menjadi properti terakhir'
        );
    }

    private function assertInvalidJson(string $json, string $message): void
    {
        $path = tempnam(sys_get_temp_dir(), 'assessment-json-invalid-');
        file_put_contents($path, $json);

        try {
            AssessmentJsonStream::open($path)->scan();
            $this->fail('JSON seharusnya ditolak.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString($message, $exception->getMessage());
        } finally {
            unlink($path);
        }
    }
}
