<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
    }

    protected function tearDown(): void
    {
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

        $this->assertSame('/assessment', $portalUrl);
        $this->assertSame('/dashboard/assessment', $adminUrl);
        $this->assertSame('/assessment/show/5/autosave', $autosaveUrl);
        $this->assertSame('/assessment/result/5/download', $downloadResultUrl);
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
