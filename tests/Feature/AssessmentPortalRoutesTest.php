<?php

namespace Tests\Feature;

use Tests\TestCase;

class AssessmentPortalRoutesTest extends TestCase
{
    public function test_assessment_portal_named_routes_do_not_collide_with_admin_routes(): void
    {
        $portalUrl = route('assessment.portal.index', absolute: false);
        $adminUrl = route('assessment.index', absolute: false);
        $autosaveUrl = route('assessment.portal.autosave', 5, absolute: false);

        $this->assertSame('/assessment', $portalUrl);
        $this->assertSame('/dashboard/assessment', $adminUrl);
        $this->assertSame('/assessment/show/5/autosave', $autosaveUrl);
        $this->assertNotSame($portalUrl, $adminUrl);
    }

    public function test_guest_assessment_landing_redirects_to_portal_login(): void
    {
        $response = $this->get(route('assessment.portal.index'));

        $response->assertRedirect(route('assessment.portal.auth'));
    }
}
