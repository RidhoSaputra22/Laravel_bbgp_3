<?php

namespace Tests\Feature;

use Tests\TestCase;

class PegawaiDataRoutesSecurityTest extends TestCase
{
    public function test_guest_is_redirected_to_login_for_pegawai_data_endpoints(): void
    {
        foreach ([
            route('user.pegawai.all'),
            route('user.pegawai.detail', ['id' => 1]),
            route('user.pegawai.detail.loka', ['id' => 1]),
            route('user.pegawai.detail.eksternal', ['id' => 1]),
        ] as $endpoint) {
            $this->get($endpoint)->assertRedirect(route('login'));
        }
    }
}
