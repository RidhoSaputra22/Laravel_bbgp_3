<?php

namespace Tests\Unit;

use App\Enum\AssessmentInstrumentType;
use App\Support\Assessment\AssessmentStageConfig;
use Tests\TestCase;

class AssessmentStageConfigTest extends TestCase
{
    public function test_assignment_stage_order_matches_default_penugasan_flow(): void
    {
        $this->assertSame(1, AssessmentInstrumentType::assignmentStageOrderFor('portofolio'));
        $this->assertSame(2, AssessmentInstrumentType::assignmentStageOrderFor('studi_kasus'));
        $this->assertSame(3, AssessmentInstrumentType::assignmentStageOrderFor('pilihan_ganda_kompleks'));
        $this->assertGreaterThan(
            AssessmentInstrumentType::assignmentStageOrderFor('pilihan_ganda_kompleks'),
            AssessmentInstrumentType::assignmentStageOrderFor('monitoring_observasi_eviden')
        );
    }

    public function test_default_stage_config_matches_portofolio_rules(): void
    {
        $config = AssessmentStageConfig::defaultForAssessment('portofolio');

        $this->assertTrue($config['enabled']);
        $this->assertSame(AssessmentStageConfig::ENTRY_DIRECT, $config['entry_mode']);
        $this->assertTrue($config['allow_draft']);
        $this->assertSame(AssessmentStageConfig::FINALIZE_MANUAL, $config['finalize_mode']);
        $this->assertFalse($config['admin_gate_enabled']);
        $this->assertFalse($config['lock_until_previous_stages_completed']);
        $this->assertNull($config['time_limit_minutes']);
        $this->assertFalse((bool) data_get($config, 'security.enabled'));
        $this->assertFalse((bool) data_get($config, 'security.require_fullscreen'));
    }

    public function test_default_stage_config_matches_studi_kasus_rules(): void
    {
        $config = AssessmentStageConfig::defaultForAssessment('studi_kasus', 1);

        $this->assertTrue($config['enabled']);
        $this->assertSame(AssessmentStageConfig::ENTRY_START_BUTTON, $config['entry_mode']);
        $this->assertFalse($config['allow_draft']);
        $this->assertSame(AssessmentStageConfig::FINALIZE_AUTO, $config['finalize_mode']);
        $this->assertTrue($config['admin_gate_enabled']);
        $this->assertTrue($config['lock_until_previous_stages_completed']);
        $this->assertNull($config['time_limit_minutes']);
        $this->assertTrue((bool) data_get($config, 'security.enabled'));
        $this->assertTrue((bool) data_get($config, 'security.require_fullscreen'));
    }

    public function test_default_stage_config_matches_pilihan_ganda_kompleks_rules(): void
    {
        $config = AssessmentStageConfig::defaultForAssessment('pilihan_ganda_kompleks', 2);

        $this->assertTrue($config['enabled']);
        $this->assertSame(AssessmentStageConfig::ENTRY_DIRECT, $config['entry_mode']);
        $this->assertFalse($config['allow_draft']);
        $this->assertSame(AssessmentStageConfig::FINALIZE_AUTO, $config['finalize_mode']);
        $this->assertTrue($config['admin_gate_enabled']);
        $this->assertTrue($config['lock_until_previous_stages_completed']);
        $this->assertSame(90, $config['time_limit_minutes']);
        $this->assertTrue((bool) data_get($config, 'security.enabled'));
        $this->assertTrue((bool) data_get($config, 'security.require_fullscreen'));
    }

    public function test_mark_opened_by_admin_preserves_reset_gate_configuration(): void
    {
        $config = AssessmentStageConfig::normalize([
            'enabled' => true,
            'admin_gate_enabled' => true,
            'lock_until_previous_stages_completed' => true,
        ]);

        $openedConfig = AssessmentStageConfig::markOpenedByAdmin($config);

        $this->assertTrue($openedConfig['admin_gate_enabled']);
        $this->assertFalse($openedConfig['lock_until_previous_stages_completed']);
    }
}
