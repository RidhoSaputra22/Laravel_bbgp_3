<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuruDashboardRoutesTest extends TestCase
{
    public function test_guru_dashboard_has_its_own_route(): void
    {
        $this->assertSame('/guru/dashboard', route('guru.dashboard', absolute: false));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('guru.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_cannot_open_guru_dashboard(): void
    {
        $this->withSession([
            'cek' => true,
            'role' => 'admin',
            'guru_id' => 1,
        ])->get(route('guru.dashboard'))
            ->assertForbidden();
    }
}
