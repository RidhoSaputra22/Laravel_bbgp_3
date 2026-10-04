<?php

namespace Tests\Unit;

use App\Models\Assessment;
use PHPUnit\Framework\TestCase;

class AssessmentTargetJabatanTest extends TestCase
{
    public function test_all_jabatan_is_a_wildcard_and_old_empty_values_stay_compatible(): void
    {
        $assessment = new Assessment([
            'target_jabatan' => [Assessment::TARGET_JABATAN_ALL, 'Guru'],
        ]);

        $this->assertSame([Assessment::TARGET_JABATAN_ALL], $assessment->targetJabatanSelections());
        $this->assertSame(['Semua Jabatan'], $assessment->target_jabatan_labels);
        $this->assertTrue($assessment->appliesToJabatanSelections(['Kepala Sekolah']));
        $this->assertTrue((new Assessment)->appliesToJabatanSelections(['Guru']));
    }

    public function test_specific_jabatan_only_matches_the_related_jabatan(): void
    {
        $assessment = new Assessment(['target_jabatan' => ['Guru', 'Kepala Sekolah']]);

        $this->assertTrue($assessment->appliesToJabatanSelections(['Guru']));
        $this->assertFalse($assessment->appliesToJabatanSelections(['Pengawas']));
        $this->assertTrue($assessment->coversJabatanSelections(['Guru', 'Kepala Sekolah']));
        $this->assertFalse($assessment->coversJabatanSelections(['Guru', 'Pengawas']));
        $this->assertTrue($assessment->matchesTargetJabatanSelections(['Kepala Sekolah', 'Guru']));
        $this->assertFalse($assessment->matchesTargetJabatanSelections(['Guru']));
    }
}
