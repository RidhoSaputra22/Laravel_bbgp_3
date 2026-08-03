<?php

namespace Tests\Unit;

use App\Support\Assessment\ScoringConfigNormalizer;
use App\Support\Assessment\LikertScale;
use Tests\TestCase;

class ScoringConfigNormalizerTest extends TestCase
{
    public function test_it_parses_comma_separated_keywords_as_distinct_groups(): void
    {
        $normalizer = new ScoringConfigNormalizer;

        $config = $normalizer->normalizeField([
            'tipe_field' => 'textarea',
            'scoring_config' => [
                'keyword_groups_text' => 'sertifikat, program studi, lembaga',
            ],
        ]);

        $this->assertSame([
            ['sertifikat'],
            ['program studi'],
            ['lembaga'],
        ], $config['keyword_groups']);
    }

    public function test_it_still_supports_legacy_keyword_group_separators_for_existing_configs(): void
    {
        $normalizer = new ScoringConfigNormalizer;

        $config = $normalizer->normalizeField([
            'tipe_field' => 'textarea',
            'scoring_config' => [
                'keyword_groups_text' => "sertifikat | piagam\nprogram studi | bidang studi",
            ],
        ]);

        $this->assertSame([
            ['sertifikat', 'piagam'],
            ['program studi', 'bidang studi'],
        ], $config['keyword_groups']);
    }

    public function test_it_defaults_likert_fields_to_likert_scale_method(): void
    {
        $normalizer = new ScoringConfigNormalizer;

        $config = $normalizer->normalizeField([
            'tipe_field' => LikertScale::FIELD_TYPE,
            'scoring_config' => [
                'enabled' => true,
                'is_negative_statement' => true,
            ],
        ]);

        $this->assertSame(LikertScale::SCORING_METHOD, $config['method']);
        $this->assertTrue($config['is_negative_statement']);
        $this->assertSame(1.0, $config['scale']['min']);
        $this->assertSame(5.0, $config['scale']['max']);
    }
}
