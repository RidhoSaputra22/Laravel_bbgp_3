<?php

namespace Tests\Feature;

use App\Jobs\ProcessAssessmentImportJob;
use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentImport;
use App\Services\Assessment\AssessmentPackageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class AssessmentPackageFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['assessment_mongodb.enabled' => false]);
    }

    public function test_export_route_contains_the_complete_database_schema_and_is_reimportable(): void
    {
        $fixture = $this->createCompleteAssessment('ASM-EXPORT-COMPLETE');
        $assessment = $fixture['assessment'];
        $form = $fixture['form'];
        $field = $fixture['field'];
        $assignment = $fixture['assignment'];

        $response = $this->withSession(['cek' => true, 'role' => 'admin'])
            ->get(route('assessment.export', $assessment));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json; charset=UTF-8');

        $payload = json_decode($response->streamedContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('database-assessment-v1', $payload['meta']['schema']);
        $this->assertSame(1, $payload['meta']['version']);
        $this->assertTrue($payload['meta']['streaming']);

        $this->assertEqualsCanonicalizing([
            'id', 'kode_assessment', 'judul', 'slug', 'deskripsi', 'petunjuk',
            'instrument_type', 'kategori', 'target_ketenagaan', 'target_jabatan',
            'scoring_config', 'status', 'is_active', 'created_at', 'updated_at', 'forms',
        ], array_keys($payload['data']));
        $this->assertSame($assessment->kode_assessment, $payload['data']['kode_assessment']);
        $this->assertSame($assessment->target_jabatan, $payload['data']['target_jabatan']);
        $this->assertSame($assessment->scoring_config, $payload['data']['scoring_config']);
        $this->assertFalse($payload['data']['is_active']);

        $exportedForm = $payload['data']['forms'][0];
        $this->assertEqualsCanonicalizing([
            'id', 'assessment_id', 'judul_form', 'kode_form', 'deskripsi',
            'kompetensi', 'indikator_kode', 'indikator_label', 'is_scoreable',
            'scoring_config', 'urutan', 'is_active', 'created_at', 'updated_at',
            'fields',
        ], array_keys($exportedForm));
        $this->assertSame($form->kode_form, $exportedForm['kode_form']);
        $this->assertFalse($exportedForm['is_scoreable']);
        $this->assertEquals($form->scoring_config, $exportedForm['scoring_config']);

        $exportedField = $exportedForm['fields'][0];
        $this->assertEqualsCanonicalizing([
            'id', 'assessment_form_id', 'label', 'deskripsi', 'nama_field',
            'tipe_field', 'placeholder', 'bantuan', 'opsi_field', 'nilai_default',
            'autofill_source', 'lookup_source', 'dependency_config', 'validasi',
            'scoring_config', 'urutan', 'is_required', 'is_active',
            'created_at', 'updated_at',
        ], array_keys($exportedField));
        $this->assertSame($field->dependency_config, $exportedField['dependency_config']);
        $this->assertSame('0', $exportedField['nilai_default']);
        $this->assertFalse($exportedField['is_required']);

        $exportedAssignment = $payload['included']['assignment_configs'][0];
        $this->assertEqualsCanonicalizing([
            'kode_penugasan', 'judul_penugasan', 'is_active', 'session_enabled',
            'target_ketenagaan', 'target_jabatan', 'target_kabupaten',
            'target_satuan_pendidikan', 'deskripsi', 'tanggal_mulai', 'jam_mulai',
            'tanggal_selesai', 'kapasitas_per_sesi', 'durasi_sesi_jam',
            'security_config', 'status_distribusi', 'assessments', 'sessions',
        ], array_keys($exportedAssignment));
        $this->assertSame($assignment->security_config, $exportedAssignment['security_config']);
        $this->assertFalse($exportedAssignment['is_active']);
        $this->assertEquals([
            'kode_assessment' => $assessment->kode_assessment,
            'urutan' => 2,
            'stage_config' => ['require_admin_gate' => true, 'lock_previous' => false],
        ], $exportedAssignment['assessments'][0]);
        $this->assertCount(1, $exportedAssignment['sessions']);
        $this->assertSame(2, $exportedAssignment['sessions'][0]['nomor_sesi']);

        $path = $this->writePackage($payload);
        try {
            $validation = app(AssessmentPackageService::class)->validateFile($path);
        } finally {
            unlink($path);
        }

        $this->assertTrue($validation['valid'], implode(' ', $validation['errors']));
        $this->assertSame(['forms' => 1, 'fields' => 2, 'assignments' => 1], [
            'forms' => $validation['summary']['forms'],
            'fields' => $validation['summary']['fields'],
            'assignments' => $validation['summary']['assignments'],
        ]);
    }

    public function test_admin_import_validates_before_dispatching_to_the_default_queue(): void
    {
        $fixture = $this->createCompleteAssessment('ASM-IMPORT-HTTP');
        $payload = $this->exportPayload($fixture['assessment']);
        Storage::fake('assessment_private');
        Queue::fake();

        $response = $this->withSession(['cek' => true, 'role' => 'admin'])
            ->post(route('assessment.import'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'assessment.json',
                    json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
                ),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('assessment_import_notice');

        $import = AssessmentImport::query()->latest('id')->firstOrFail();
        $this->assertSame('queued', $import->status);
        $this->assertSame(1, $import->summary['forms']);
        Storage::disk('assessment_private')->assertExists($import->path);

        Queue::assertPushed(ProcessAssessmentImportJob::class, function (ProcessAssessmentImportJob $job) use ($import): bool {
            return $job->importId === $import->id && $job->queue === null;
        });
    }

    public function test_queued_job_imports_every_configuration_value_without_distortion(): void
    {
        $fixture = $this->createCompleteAssessment('ASM-IMPORT-JOB');
        $assessment = $fixture['assessment'];
        $payload = $this->exportPayload($assessment);
        $assessment->update(['petunjuk' => 'Nilai lama yang harus dihapus', 'is_active' => true]);

        Storage::fake('assessment_private');
        $path = 'assessment-imports/job.json';
        Storage::disk('assessment_private')->put(
            $path,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );
        $import = AssessmentImport::query()->create([
            'disk' => 'assessment_private',
            'path' => $path,
            'original_name' => 'job.json',
            'status' => 'queued',
            'summary' => ['forms' => 1, 'fields' => 2, 'assignments' => 1],
        ]);

        (new ProcessAssessmentImportJob($import->id))->handle(app(AssessmentPackageService::class));

        $import->refresh();
        $assessment->refresh();
        $form = $assessment->forms()->firstOrFail();
        $fields = $form->fields()->get()->keyBy('nama_field');
        $assignment = AssessmentAssignment::query()
            ->where('kode_penugasan', 'TUGAS-IMPORT-JOB')
            ->firstOrFail();
        $session = $assignment->sessions()->firstOrFail();
        $stage = DB::table('assessment_assignment_assessments')
            ->where('assessment_assignment_id', $assignment->id)
            ->where('assessment_id', $assessment->id)
            ->first();

        $this->assertSame('completed', $import->status);
        $this->assertNull($import->error_message);
        Storage::disk('assessment_private')->assertMissing($path);
        $this->assertFalse($assessment->is_active);
        $this->assertNull($assessment->petunjuk);
        $this->assertFalse($form->is_scoreable);
        $this->assertFalse($form->is_active);
        $this->assertSame('0', $fields['nilai_nol']->nilai_default);
        $this->assertFalse($fields['nilai_nol']->is_required);
        $this->assertFalse($fields['nilai_nol']->is_active);
        $this->assertSame(['parent' => 'kategori', 'operator' => 'equals'], $fields['nilai_nol']->dependency_config);
        $this->assertSame(['enabled' => false, 'max_serious_violations' => 4], $assignment->security_config);
        $this->assertFalse($assignment->is_active);
        $this->assertFalse($assignment->session_enabled);
        $this->assertSame(2, $session->nomor_sesi);
        $this->assertSame(30, $session->kapasitas_peserta);
        $this->assertEquals(['require_admin_gate' => true, 'lock_previous' => false], json_decode($stage->stage_config, true));
    }

    public function test_failed_job_marks_import_failed_and_removes_the_uploaded_file(): void
    {
        Storage::fake('assessment_private');
        $path = 'assessment-imports/failed.json';
        Storage::disk('assessment_private')->put($path, '{}');
        $import = AssessmentImport::query()->create([
            'disk' => 'assessment_private',
            'path' => $path,
            'original_name' => 'failed.json',
            'status' => 'processing',
        ]);

        (new ProcessAssessmentImportJob($import->id))->failed(new RuntimeException('Import gagal'));

        $import->refresh();
        $this->assertSame('failed', $import->status);
        $this->assertSame('Import gagal', $import->error_message);
        $this->assertNotNull($import->completed_at);
        Storage::disk('assessment_private')->assertMissing($path);
    }

    public function test_admin_can_read_import_status(): void
    {
        $import = AssessmentImport::query()->create([
            'disk' => 'assessment_private',
            'path' => 'assessment-imports/status.json',
            'original_name' => 'status.json',
            'status' => 'completed',
            'summary' => ['forms' => 2, 'fields' => 5, 'assignments' => 1],
        ]);

        $response = $this->withSession(['cek' => true, 'role' => 'admin'])
            ->getJson(route('assessment.import.status', $import));

        $response->assertOk()
            ->assertJsonPath('meta.schema', 'assessment-import-status-v1')
            ->assertJsonPath('data.id', $import->id)
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.summary.fields', 5);
    }

    /** @return array{assessment:Assessment,form:\App\Models\AssessmentForm,field:\App\Models\AssessmentFormField,assignment:AssessmentAssignment} */
    private function createCompleteAssessment(string $code): array
    {
        $assessment = Assessment::query()->create([
            'kode_assessment' => $code,
            'judul' => 'Assessment lengkap '.$code,
            'slug' => strtolower($code),
            'deskripsi' => 'Deskripsi lengkap',
            'petunjuk' => null,
            'instrument_type' => 'monitoring_observasi_eviden',
            'kategori' => 'assessment',
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['__all__'],
            'scoring_config' => ['method' => 'weighted', 'weight' => 0.75],
            'status' => 'draft',
            'is_active' => false,
        ]);

        $form = $assessment->forms()->create([
            'judul_form' => 'Form lengkap',
            'kode_form' => 'FORM-'.$code,
            'deskripsi' => 'Deskripsi form',
            'kompetensi' => 'profesional',
            'indikator_kode' => 'IND-01',
            'indikator_label' => 'Indikator lengkap',
            'is_scoreable' => false,
            'scoring_config' => ['weight' => 1, 'method' => 'sum'],
            'urutan' => 1,
            'is_active' => false,
        ]);

        $field = $form->fields()->create([
            'label' => 'Nilai nol',
            'deskripsi' => 'Field lengkap',
            'nama_field' => 'nilai_nol',
            'tipe_field' => 'text',
            'placeholder' => null,
            'bantuan' => 'Bantuan field',
            'opsi_field' => [['label' => 'Nol', 'value' => '0', 'score' => 0]],
            'nilai_default' => '0',
            'autofill_source' => 'nama',
            'lookup_source' => 'jabatan',
            'dependency_config' => ['parent' => 'kategori', 'operator' => 'equals'],
            'validasi' => ['required' => false, 'min' => 0],
            'scoring_config' => ['enabled' => true, 'method' => 'presence', 'weight' => 1],
            'urutan' => 1,
            'is_required' => false,
            'is_active' => false,
        ]);

        $form->fields()->create([
            'label' => 'Skala',
            'deskripsi' => 'Field skala',
            'nama_field' => 'skala',
            'tipe_field' => 'likert',
            'opsi_field' => [['label' => 'Setuju', 'value' => '4', 'score' => 4]],
            'validasi' => ['required' => true],
            'scoring_config' => ['enabled' => true, 'method' => 'likert'],
            'urutan' => 2,
            'is_required' => true,
            'is_active' => true,
        ]);

        $assignment = AssessmentAssignment::query()->create([
            'kode_penugasan' => 'TUGAS-'.str_replace('ASM-', '', $code),
            'judul_penugasan' => 'Penugasan lengkap',
            'is_active' => false,
            'session_enabled' => false,
            'target_ketenagaan' => 'tenaga_pendidik',
            'target_jabatan' => ['__all__'],
            'target_kabupaten' => ['Makassar'],
            'target_satuan_pendidikan' => ['all'],
            'deskripsi' => 'Deskripsi penugasan',
            'tanggal_mulai' => '2026-10-10',
            'jam_mulai' => '08:30:00',
            'tanggal_selesai' => '2026-10-11',
            'kapasitas_per_sesi' => 30,
            'durasi_sesi_jam' => 3,
            'security_config' => ['enabled' => false, 'max_serious_violations' => 4],
            'status_distribusi' => 'draft',
        ]);

        DB::table('assessment_assignment_assessments')->insert([
            'assessment_assignment_id' => $assignment->id,
            'assessment_id' => $assessment->id,
            'urutan' => 2,
            'stage_config' => json_encode(['require_admin_gate' => true, 'lock_previous' => false]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $assignment->sessions()->create([
            'nomor_sesi' => 2,
            'label_sesi' => 'Sesi dua',
            'waktu_mulai' => '2026-10-10 08:30:00',
            'waktu_selesai' => '2026-10-10 11:30:00',
            'kapasitas_peserta' => 30,
            'durasi_sesi_jam' => 3,
        ]);

        return compact('assessment', 'form', 'field', 'assignment');
    }

    /** @return array<string,mixed> */
    private function exportPayload(Assessment $assessment): array
    {
        $response = app(AssessmentPackageService::class)->export($assessment->fresh());
        ob_start();
        $response->sendContent();
        $json = ob_get_clean();

        return json_decode((string) $json, true, 512, JSON_THROW_ON_ERROR);
    }

    /** @param array<string,mixed> $payload */
    private function writePackage(array $payload): string
    {
        $path = tempnam(sys_get_temp_dir(), 'assessment-package-');
        file_put_contents(
            $path,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );

        return $path;
    }
}
