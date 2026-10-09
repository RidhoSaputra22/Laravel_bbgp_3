<?php

namespace Tests\Feature;

use App\Jobs\ProcessAssessmentImportJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssessmentPackageImportValidationTest extends TestCase
{
    public function test_invalid_json_is_rejected_before_import_job_is_created(): void
    {
        Storage::fake('assessment_private');
        Queue::fake();

        $response = $this
            ->withSession([
                'cek' => true,
                'role' => 'admin',
            ])
            ->post(route('assessment.import'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'invalid.json',
                    '{"meta":{"schema":"wrong-schema"},"data":{'
                ),
            ]);

        $response->assertSessionHasErrors('file');
        Queue::assertNothingPushed(ProcessAssessmentImportJob::class);
        Storage::disk('assessment_private')->assertDirectoryEmpty('assessment-imports');
    }
}
