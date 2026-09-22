<?php

namespace Tests\Unit;

use App\Support\Assessment\AssessmentDependentOptionResolver;
use Tests\TestCase;

class AssessmentDependentOptionResolverTest extends TestCase
{
    public function test_it_resolves_options_by_parent_value(): void
    {
        $resolver = new AssessmentDependentOptionResolver();
        $field = [
            'dependency_config' => [
                'parent_field' => 'kabupaten_kota',
                'options_by_parent' => [
                    'Kabupaten Gowa' => [
                        ['label' => 'Narasumber Gowa', 'value' => 'narasumber_gowa'],
                    ],
                ],
            ],
        ];

        $this->assertSame(
            [['label' => 'Narasumber Gowa', 'value' => 'narasumber_gowa', 'aliases' => ['narasumber_gowa', 'Narasumber Gowa'], 'score' => null, 'level_kompetensi' => null, 'level_kompetensi_label' => null]],
            $resolver->resolveOptions($field, ['kabupaten_kota' => 'Kabupaten Gowa'])
        );
        $this->assertSame([], $resolver->resolveOptions($field, ['kabupaten_kota' => 'Kabupaten Maros']));
    }

    public function test_it_rejects_empty_or_malformed_mapping(): void
    {
        $resolver = new AssessmentDependentOptionResolver();

        $this->assertNull($resolver->normalizeConfig([
            'parent_field' => 'kabupaten_kota',
            'options_by_parent' => [],
        ]));
        $this->assertNull($resolver->normalizeConfig([
            'parent_field' => 'kabupaten_kota',
            'options_by_parent' => [
                'Kabupaten Gowa' => 'not-an-array',
            ],
        ]));
    }
}
