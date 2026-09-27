<?php

namespace Tests\Feature;

use Tests\TestCase;

class PegawaiDashboardRoutesTest extends TestCase
{
    public function test_pegawai_dashboard_has_its_own_route(): void
    {
        $this->assertSame('/pegawai/dashboard', route('pegawai.dashboard', absolute: false));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('pegawai.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_non_pegawai_cannot_open_pegawai_dashboard(): void
    {
        $this->withSession([
            'cek' => true,
            'role' => 'admin',
            'no_ktp' => '123',
        ])->get(route('pegawai.dashboard'))
            ->assertForbidden();
    }
}
