<?php

namespace Tests\Feature;

use Tests\TestCase;

class AssessmentStructureSearchTest extends TestCase
{
    private function assessmentBuilderViewSource(): string
    {
        $source = file_get_contents(resource_path('views/pages/admin/assessment/partials/form/page.blade.php'));

        $this->assertIsString($source);

        return $source;
    }

    public function test_structure_search_is_client_side_and_does_not_submit_the_builder_form(): void
    {
        $source = $this->assessmentBuilderViewSource();

        preg_match(
            '/<input\\b(?=[^>]*\\btype="search")(?=[^>]*\\bid="assessment-structure-search-input")[^>]*>/s',
            $source,
            $matches
        );

        $this->assertNotEmpty($matches);
        $this->assertStringNotContainsString('name=', $matches[0]);
        $this->assertStringContainsString("if (event.key === 'Enter')", $source);
        $this->assertStringContainsString('event.preventDefault();', $source);
        $this->assertStringContainsString("$('#assessment-structure-search-input').on('input'", $source);
    }

    public function test_question_search_hides_form_settings_and_keeps_only_matching_question_cards(): void
    {
        $source = $this->assessmentBuilderViewSource();

        $this->assertStringContainsString('class="assessment-form-settings"', $source);
        $this->assertStringContainsString('assessment-form-field-actions', $source);
        $this->assertStringContainsString('const fieldOnlyMatch = hasQuery && !formMatches && fieldMatchCount > 0;', $source);
        $this->assertStringContainsString(
            "\$form.find('.assessment-form-settings').toggleClass('d-none', fieldOnlyMatch);",
            $source
        );
        $this->assertStringContainsString(
            "\$form.find('.assessment-form-field-actions').toggleClass('d-none', fieldOnlyMatch);",
            $source
        );
        $this->assertStringContainsString("\$field.toggleClass('d-none', !showField);", $source);
    }
}
