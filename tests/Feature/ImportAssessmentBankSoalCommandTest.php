<?php

namespace Tests\Feature;

use App\Models\Assessment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ImportAssessmentBankSoalCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_command_imports_every_json_file_in_the_directory(): void
    {
        $directory = sys_get_temp_dir().'/bank-soal-import-'.uniqid();
        mkdir($directory, 0775, true);

        try {
            foreach (['ASM-IMPORT-ONE', 'ASM-IMPORT-TWO'] as $code) {
                file_put_contents(
                    $directory.'/'.strtolower($code).'.json',
                    json_encode($this->package($code), JSON_THROW_ON_ERROR)
                );
            }

            $this->artisan('assessment:import-bank-soal', [
                '--path' => $directory,
            ])
                ->expectsConfirmation(
                    "Aksi ini akan mengambil soal dari folder {$directory} dan mengimpornya ke database. Lanjutkan?",
                    'yes'
                )
                ->assertExitCode(0);

            $this->assertDatabaseHas('assessments', ['kode_assessment' => 'ASM-IMPORT-ONE']);
            $this->assertDatabaseHas('assessments', ['kode_assessment' => 'ASM-IMPORT-TWO']);
            $this->assertDatabaseHas('assessment_assignments', [
                'kode_penugasan' => 'PREVIEW-ASM-IMPORT-ONE',
                'durasi_sesi_jam' => 0,
            ]);
            $this->assertSame(2, Assessment::query()->whereIn('kode_assessment', [
                'ASM-IMPORT-ONE', 'ASM-IMPORT-TWO',
            ])->count());
        } finally {
            foreach (glob($directory.'/*.json') ?: [] as $file) {
                @unlink($file);
            }

            @rmdir($directory);
        }
    }

    /** @return array<string,mixed> */
    private function package(string $code): array
    {
        return [
            'meta' => [
                'schema' => 'database-assessment-v1',
                'version' => 1,
            ],
            'data' => [
                'kode_assessment' => $code,
                'judul' => 'Assessment '.$code,
                'slug' => strtolower($code),
                'instrument_type' => 'monitoring_observasi_eviden',
                'kategori' => Assessment::CATEGORY_STANDARD,
                'target_ketenagaan' => 'tenaga_pendidik',
                'target_jabatan' => ['__all__'],
                'status' => 'draft',
                'is_active' => false,
                'forms' => [[
                    'judul_form' => 'Form '.$code,
                    'kode_form' => 'FORM-'.$code,
                    'is_scoreable' => false,
                    'urutan' => 1,
                    'is_active' => true,
                    'fields' => [[
                        'label' => 'Pertanyaan '.$code,
                        'nama_field' => 'pertanyaan_'.strtolower(str_replace('-', '_', $code)),
                        'tipe_field' => 'text',
                        'urutan' => 1,
                        'is_required' => false,
                        'is_active' => true,
                    ]],
                ]],
            ],
            'included' => [
                'assignment_configs' => [[
                    'kode_penugasan' => 'PREVIEW-'.$code,
                    'judul_penugasan' => 'Preview '.$code,
                    'is_active' => true,
                    'session_enabled' => false,
                    'kapasitas_per_sesi' => 0,
                    'durasi_sesi_jam' => 0,
                    'assessments' => [[
                        'kode_assessment' => $code,
                        'urutan' => 1,
                    ]],
                    'sessions' => [],
                ]],
            ],
        ];
    }
}
