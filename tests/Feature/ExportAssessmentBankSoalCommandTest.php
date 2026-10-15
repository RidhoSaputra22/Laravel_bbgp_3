<?php

namespace Tests\Feature;

use App\Models\Assessment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExportAssessmentBankSoalCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_command_replaces_existing_json_file(): void
    {
        $assessment = Assessment::query()->create([
            'kode_assessment' => 'ASM-COMMAND-EXPORT',
            'judul' => 'Assessment command export',
            'slug' => 'assessment-command-export',
            'instrument_type' => 'monitoring_observasi_eviden',
            'kategori' => Assessment::CATEGORY_STANDARD,
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['__all__'],
            'status' => 'draft',
            'is_active' => false,
        ]);
        $form = $assessment->forms()->create([
            'judul_form' => 'Form command export',
            'kode_form' => 'FORM-COMMAND-EXPORT',
            'urutan' => 1,
            'is_scoreable' => false,
            'is_active' => true,
        ]);
        $form->fields()->create([
            'label' => 'Pertanyaan command export',
            'nama_field' => 'pertanyaan_command_export',
            'tipe_field' => 'text',
            'urutan' => 1,
            'is_required' => false,
            'is_active' => true,
        ]);

        $directory = sys_get_temp_dir().'/bank-soal-command-'.uniqid();
        mkdir($directory, 0775, true);
        $path = $directory.'/asm-command-export.json';
        file_put_contents($path, '{"old":true}');

        try {
            $this->artisan('assessment:export-bank-soal', [
                '--path' => $directory,
            ])
                ->expectsConfirmation(
                    "Aksi ini akan mengambil semua soal dari database dan menyimpannya ke folder {$directory}. File JSON yang sama akan ditimpa. Lanjutkan?",
                    'yes'
                )
                ->assertExitCode(0);
            $json = (string) file_get_contents($path);
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            $this->assertSame('ASM-COMMAND-EXPORT', $payload['data']['kode_assessment']);
            $this->assertArrayNotHasKey('old', $payload);
            $this->assertStringContainsString("{\n  \"meta\": {", $json);
            $this->assertStringContainsString("\n    \"forms\": [", $json);
        } finally {
            @unlink($path);
            @rmdir($directory);
        }
    }
}
