<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $formId = DB::table('validator_forms')
            ->where('code', 'VF-VALIDASI-AHLI-001')
            ->value('id');

        if (! $formId) {
            return;
        }

        $sectionIds = DB::table('validator_form_sections')
            ->where('validator_form_id', $formId)
            ->where('title', 'Identitas Validator')
            ->pluck('id');

        if ($sectionIds->isEmpty()) {
            return;
        }

        $fieldIds = DB::table('validator_form_fields')
            ->whereIn('validator_form_section_id', $sectionIds)
            ->pluck('id');

        DB::table('validator_assignment_responses')
            ->whereIn('validator_form_field_id', $fieldIds)
            ->delete();

        DB::table('validator_form_fields')
            ->whereIn('id', $fieldIds)
            ->delete();

        DB::table('validator_form_sections')
            ->whereIn('id', $sectionIds)
            ->delete();
    }

    public function down(): void
    {
        // The removed fields are obsolete and cannot be restored safely.
    }
};
