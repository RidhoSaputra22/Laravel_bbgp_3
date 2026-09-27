<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssessmentPortalRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::connection('sqlite')->create('gurus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_assignment_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_id')->nullable();
            $table->unsignedBigInteger('guru_id')->nullable();
            $table->string('status')->default('ditugaskan');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_assignment_target_id');
            $table->string('status')->default('in_progress');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('assessment_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_attempt_id');
            $table->json('answer_payload')->nullable();
            $table->string('answer_file_path')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::connection('sqlite')->dropIfExists('assessment_attempt_answers');
        Schema::connection('sqlite')->dropIfExists('assessment_attempts');
        Schema::connection('sqlite')->dropIfExists('assessment_assignment_targets');
        Schema::connection('sqlite')->dropIfExists('gurus');

        parent::tearDown();
    }

    public function test_assessment_portal_named_routes_do_not_collide_with_admin_routes(): void
    {
        $portalUrl = route('assessment.portal.index', absolute: false);
        $adminUrl = route('assessment.index', absolute: false);
        $autosaveUrl = route('assessment.portal.autosave', 5, absolute: false);
        $downloadResultUrl = route('assessment.portal.result.download', 5, absolute: false);
        $validatorDraftUrl = route('assessment.portal.validator.tasks.draft', 7, absolute: false);
        $validatorSubmitUrl = route('assessment.portal.validator.tasks.submit', 7, absolute: false);

        $this->assertSame('/assessment', $portalUrl);
        $this->assertSame('/dashboard/assessment', $adminUrl);
        $this->assertSame('/assessment/show/5/autosave', $autosaveUrl);
        $this->assertSame('/assessment/result/5/download', $downloadResultUrl);
        $this->assertSame('/assessment/validator-tasks/7/draft', $validatorDraftUrl);
        $this->assertSame('/assessment/validator-tasks/7/submit', $validatorSubmitUrl);
        $this->assertNotSame($portalUrl, $adminUrl);
    }

    public function test_guest_assessment_landing_redirects_to_portal_login(): void
    {
        $response = $this->get(route('assessment.portal.index'));

        $response->assertRedirect(route('assessment.portal.auth'));
    }

    public function test_unknown_assessment_portal_url_redirects_to_dashboard(): void
    {
        $response = $this->get('/assessment/show/999999/unknown');

        $response->assertRedirect(route('assessment.portal.dashboard'));
        $response->assertSessionHasErrors([
            'portal' => 'Halaman assessment tidak ditemukan. Anda diarahkan kembali ke dashboard.',
        ]);
    }

    public function test_missing_assessment_portal_target_redirects_to_dashboard(): void
    {
        $this->createPortalGuru();

        $response = $this
            ->withSession([
                'assessment_portal_auth' => [
                    'guru_id' => 1,
                ],
            ])
            ->get(route('assessment.portal.show', 999999));

        $response->assertRedirect(route('assessment.portal.dashboard'));
        $response->assertSessionHasErrors([
            'portal' => 'Halaman assessment tidak ditemukan. Anda diarahkan kembali ke dashboard.',
        ]);
    }

    public function test_json_assessment_portal_not_found_response_includes_dashboard_redirect(): void
    {
        $this->createPortalGuru();

        $response = $this
            ->withSession([
                'assessment_portal_auth' => [
                    'guru_id' => 1,
                ],
            ])
            ->postJson(route('assessment.portal.autosave', 999999));

        $response
            ->assertStatus(404)
            ->assertJson([
                'status' => 'not_found',
                'redirect_url' => route('assessment.portal.dashboard'),
            ]);
    }

    public function test_assessment_file_route_streams_private_file_only_to_target_owner(): void
    {
        Storage::fake('assessment_private');
        $this->createPortalGuru();

        DB::table('assessment_assignment_targets')->insert([
            'id' => 10,
            'assessment_assignment_id' => 1,
            'guru_id' => 1,
            'status' => 'dikerjakan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assessment_attempts')->insert([
            'id' => 20,
            'assessment_assignment_target_id' => 10,
            'status' => 'in_progress',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('assessment_attempt_answers')->insert([
            'id' => 30,
            'assessment_attempt_id' => 20,
            'answer_payload' => json_encode([
                'original_name' => 'ktp.png',
                'mime_type' => 'image/png',
            ]),
            'answer_file_path' => 'assessment/attempts/20/ktp.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Storage::disk('assessment_private')->put('assessment/attempts/20/ktp.png', 'private-ktp');

        $response = $this->withSession([
            'assessment_portal_auth' => ['guru_id' => 1],
        ])->get(route('assessment.portal.file', 30));

        $response
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Content-Disposition', 'inline; filename="ktp.png"')
            ->assertStreamedContent('private-ktp');

        $this->withSession([
            'assessment_portal_auth' => ['guru_id' => 2],
        ])->get(route('assessment.portal.file', 30))->assertForbidden();
    }

    private function createPortalGuru(): void
    {
        DB::table('gurus')->insert([
            'id' => 1,
            'nama_lengkap' => 'Peserta Assessment',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
