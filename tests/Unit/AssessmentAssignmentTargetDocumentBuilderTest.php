<?php

namespace Tests\Unit;

use App\Jobs\SyncAssessmentTargetsToMongoJob;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\Guru;
use App\Services\Assessment\AssessmentAssignmentTargetDocumentBuilder;
use App\Services\Assessment\AssessmentQuestionRandomizerService;
use App\Support\Assessment\ScoringConfigNormalizer;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class AssessmentAssignmentTargetDocumentBuilderTest extends TestCase
{
    public function test_it_builds_one_target_document_with_grouped_forms_and_user_data(): void
    {
        $randomizer = Mockery::mock(AssessmentQuestionRandomizerService::class);
        $normalizer = Mockery::mock(ScoringConfigNormalizer::class);
        $randomizer->shouldReceive('buildSnapshot')->once()->andReturn([
            'assessments' => [
                [
                    'instrument_type' => 'portofolio',
                    'instrument_label' => 'Portofolio',
                    'forms' => [],
                ],
                [
                    'instrument_type' => 'studi_kasus',
                    'instrument_label' => 'Studi Kasus',
                    'forms' => [],
                ],
            ],
            'meta' => ['total_questions' => 2],
        ]);
        $randomizer->shouldReceive('randomizeSnapshotForTarget')->once()->andReturnUsing(
            fn (array $snapshot): array => $snapshot
        );

        $assignment = new AssessmentAssignment([
            'kode_penugasan' => 'TGS-001',
            'judul_penugasan' => 'Assessment Guru',
            'deskripsi' => 'Penugasan test',
            'target_ketenagaan' => 'tenaga_pendidik',
            'status_distribusi' => 'selesai',
            'is_active' => true,
            'tanggal_mulai' => '2026-09-29',
            'tanggal_selesai' => '2026-10-01',
        ]);
        $assignment->id = 34;
        $assignment->setRelation('combination', null);

        $guru = new Guru([
            'nama_lengkap' => 'A. NUR ERNY, S.Pd',
            'no_ktp' => '7312345678901234',
            'nip' => '198001012010012001',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru Kelas',
            'kabupaten' => 'Kabupaten Bone',
        ]);
        $guru->id = 12726;

        $target = new AssessmentAssignmentTarget([
            'status' => 'ditugaskan',
            'assigned_at' => '2026-09-29 10:00:00',
            'assessment_assignment_session_id' => 2,
        ]);
        $target->id = 115844;
        $target->setRelation('assignment', $assignment);
        $target->setRelation('guru', $guru);
        $target->setRelation('combination', null);
        $target->setRelation('attempt', null);

        $document = (new AssessmentAssignmentTargetDocumentBuilder($randomizer, $normalizer))
            ->document($target, $assignment);

        $this->assertSame('assessment-target:115844', $document['_id']);
        $this->assertSame(115844, $document['assignment_target_id']);
        $this->assertSame('A. NUR ERNY, S.Pd', $document['user']['nama']);
        $this->assertSame(2, count($document['forms']));
        $this->assertSame('portofolio', $document['forms'][0]['instrument_type']);
        $this->assertSame('studi_kasus', $document['forms'][1]['instrument_type']);
        $this->assertSame('assignment', $document['meta']['snapshot_source']);
        $this->assertTrue($document['sync']['is_active']);
    }

    public function test_it_adds_autofill_values_only_to_portfolio_identity_form_in_mongo_documents(): void
    {
        $randomizer = Mockery::mock(AssessmentQuestionRandomizerService::class);
        $randomizer->shouldReceive('buildSnapshot')->once()->andReturn([
            'assessments' => [[
                'instrument_type' => 'portofolio',
                'instrument_label' => 'Portofolio',
                'forms' => [[
                    'judul_form' => 'Identitas Responden',
                    'kode_form' => 'FORM-IDENTITAS',
                    'fields' => [
                        [
                            'id' => 355,
                            'tipe_field' => 'text',
                            'autofill_source' => 'nama_lengkap',
                        ],
                        [
                            'id' => 356,
                            'tipe_field' => 'text',
                            'autofill_source' => null,
                        ],
                        [
                            'id' => 357,
                            'label' => 'Kabupaten/Kota',
                            'nama_field' => 'kabupaten_kota',
                            'tipe_field' => 'text',
                            'autofill_source' => null,
                        ],
                    ],
                ], [
                    'judul_form' => 'Riwayat Pendidikan Formal',
                    'kode_form' => 'FORM-PENDIDIKAN',
                    'fields' => [[
                        'id' => 358,
                        'tipe_field' => 'text',
                        'autofill_source' => 'nama_lengkap',
                    ]],
                ]],
            ]],
            'meta' => [],
        ]);
        $randomizer->shouldReceive('randomizeSnapshotForTarget')
            ->once()
            ->andReturnUsing(fn (array $snapshot): array => $snapshot);

        $assignment = new AssessmentAssignment(['target_ketenagaan' => 'tenaga_pendidik']);
        $assignment->id = 34;
        $assignment->setRelation('combination', null);

        $guru = new Guru([
            'nama_lengkap' => 'A. NUR ERNY, S.Pd',
            'kabupaten' => 'Kota Makassar',
        ]);
        $guru->id = 12726;

        $target = new AssessmentAssignmentTarget(['status' => 'ditugaskan']);
        $target->id = 115844;
        $target->setRelation('assignment', $assignment);
        $target->setRelation('guru', $guru);
        $target->setRelation('combination', null);
        $target->setRelation('attempt', null);

        $document = (new AssessmentAssignmentTargetDocumentBuilder($randomizer, Mockery::mock(ScoringConfigNormalizer::class)))
            ->document($target, $assignment);
        $fields = $document['forms'][0]['assessments'][0]['forms'][0]['fields'];
        $nonIdentityFields = $document['forms'][0]['assessments'][0]['forms'][1]['fields'];

        $this->assertSame('A. NUR ERNY, S.Pd', $fields[0]['default_value']);
        $this->assertNull($fields[1]['default_value']);
        $this->assertSame('kabupaten', $fields[2]['autofill_source']);
        $this->assertSame('Kota Makassar', $fields[2]['default_value']);
        $this->assertNull($nonIdentityFields[0]['default_value']);
    }

    public function test_attempt_snapshot_is_used_for_target_forms(): void
    {
        $randomizer = Mockery::mock(AssessmentQuestionRandomizerService::class);
        $randomizer->shouldReceive('buildSnapshot')->never();
        $normalizer = Mockery::mock(ScoringConfigNormalizer::class);

        $assignment = new AssessmentAssignment([
            'kode_penugasan' => 'TGS-002',
            'judul_penugasan' => 'Assessment Guru',
            'target_ketenagaan' => 'tenaga_pendidik',
        ]);
        $assignment->id = 35;
        $assignment->setRelation('combination', null);

        $target = new AssessmentAssignmentTarget(['status' => 'dikerjakan']);
        $target->id = 99;
        $target->setRelation('assignment', $assignment);
        $target->setRelation('guru', null);
        $target->setRelation('combination', null);
        $target->setRelation('attempt', new \App\Models\AssessmentAttempt([
            'structure_snapshot' => [
                'assessments' => [[
                    'instrument_type' => 'portofolio',
                    'instrument_label' => 'Portofolio',
                    'forms' => [],
                ]],
                'meta' => ['total_questions' => 1],
            ],
        ]));

        $payload = (new AssessmentAssignmentTargetDocumentBuilder($randomizer, $normalizer))
            ->participant($target, $assignment);

        $this->assertSame('attempt', $payload['meta']['snapshot_source']);
        $this->assertSame('portofolio', $payload['forms'][0]['instrument_type']);
    }

    public function test_reuses_the_same_assignment_schema_for_multiple_targets(): void
    {
        $randomizer = Mockery::mock(AssessmentQuestionRandomizerService::class);
        $randomizer->shouldReceive('buildSnapshot')->once()->andReturn([
            'assessments' => [[
                'instrument_type' => 'portofolio',
                'instrument_label' => 'Portofolio',
                'forms' => [],
            ]],
            'meta' => [],
        ]);
        $randomizer->shouldReceive('randomizeSnapshotForTarget')
            ->twice()
            ->andReturnUsing(fn (array $snapshot): array => $snapshot);

        $assignment = new AssessmentAssignment([
            'kode_penugasan' => 'TGS-CACHE',
            'judul_penugasan' => 'Assessment Cache',
        ]);
        $assignment->id = 36;
        $assignment->setRelation('combination', null);

        $builder = new AssessmentAssignmentTargetDocumentBuilder(
            $randomizer,
            Mockery::mock(ScoringConfigNormalizer::class)
        );

        $documents = [];
        foreach ([101, 102] as $targetId) {
            $target = new AssessmentAssignmentTarget([
                'assessment_assignment_id' => $assignment->id,
            ]);
            $target->id = $targetId;
            $target->setRelation('assignment', $assignment);
            $target->setRelation('combination', null);
            $target->setRelation('attempt', null);
            $target->setRelation('guru', null);

            $documents[] = $builder->document($target, $assignment);
        }

        $this->assertCount(2, $documents);
    }

    public function test_reuses_each_assignment_schema_when_target_order_interleaves_assignments(): void
    {
        $randomizer = Mockery::mock(AssessmentQuestionRandomizerService::class);
        $randomizer->shouldReceive('buildSnapshot')
            ->twice()
            ->andReturn(
                ['assessments' => [], 'meta' => ['source' => 'assignment-1']],
                ['assessments' => [], 'meta' => ['source' => 'assignment-2']]
            );
        $randomizer->shouldReceive('randomizeSnapshotForTarget')
            ->times(3)
            ->andReturnUsing(fn (array $snapshot): array => $snapshot);

        $assignmentOne = new AssessmentAssignment(['judul_penugasan' => 'Satu']);
        $assignmentOne->id = 1;
        $assignmentOne->setRelation('combination', null);

        $assignmentTwo = new AssessmentAssignment(['judul_penugasan' => 'Dua']);
        $assignmentTwo->id = 2;
        $assignmentTwo->setRelation('combination', null);

        $builder = new AssessmentAssignmentTargetDocumentBuilder(
            $randomizer,
            Mockery::mock(ScoringConfigNormalizer::class)
        );

        foreach ([$assignmentOne, $assignmentTwo, $assignmentOne] as $index => $assignment) {
            $target = new AssessmentAssignmentTarget([
                'assessment_assignment_id' => $assignment->id,
            ]);
            $target->id = $index + 1;
            $target->setRelation('assignment', $assignment);
            $target->setRelation('combination', null);
            $target->setRelation('attempt', null);
            $target->setRelation('guru', null);

            $builder->document($target, $assignment);
        }
    }

    public function test_sync_dispatcher_splits_target_ids_into_bounded_queue_batches(): void
    {
        config()->set('assessment_mongodb.enabled', true);
        config()->set('assessment_mongodb.batch_size', 50);
        Queue::fake();

        SyncAssessmentTargetsToMongoJob::dispatchIds(range(1, 101));

        Queue::assertPushed(SyncAssessmentTargetsToMongoJob::class, 3);
        Queue::assertPushed(SyncAssessmentTargetsToMongoJob::class, function ($job) {
            return count($job->targetIds) <= 50;
        });
    }
}
