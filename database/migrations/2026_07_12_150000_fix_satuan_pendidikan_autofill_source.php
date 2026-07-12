<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (
            Schema::hasTable('assessment_form_fields') &&
            Schema::hasColumn('assessment_form_fields', 'autofill_source')
        ) {
            DB::table('assessment_form_fields')
                ->where('autofill_source', 'pendidikan')
                ->where(function ($query) {
                    $query->where('nama_field', 'satuan_pendidikan')
                        ->orWhere('label', 'Satuan Pendidikan');
                })
                ->update([
                    'autofill_source' => 'satuan_pendidikan',
                    'updated_at' => now(),
                ]);
        }

        if (
            Schema::hasTable('assessment_combination_items') &&
            Schema::hasColumn('assessment_combination_items', 'field_autofill_source')
        ) {
            DB::table('assessment_combination_items')
                ->where('field_autofill_source', 'pendidikan')
                ->where(function ($query) {
                    $query->where('field_name', 'satuan_pendidikan')
                        ->orWhere('field_label', 'Satuan Pendidikan');
                })
                ->update([
                    'field_autofill_source' => 'satuan_pendidikan',
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('assessment_combination_items') &&
            Schema::hasColumn('assessment_combination_items', 'field_autofill_source')
        ) {
            DB::table('assessment_combination_items')
                ->where('field_autofill_source', 'satuan_pendidikan')
                ->where(function ($query) {
                    $query->where('field_name', 'satuan_pendidikan')
                        ->orWhere('field_label', 'Satuan Pendidikan');
                })
                ->update([
                    'field_autofill_source' => 'pendidikan',
                    'updated_at' => now(),
                ]);
        }

        if (
            Schema::hasTable('assessment_form_fields') &&
            Schema::hasColumn('assessment_form_fields', 'autofill_source')
        ) {
            DB::table('assessment_form_fields')
                ->where('autofill_source', 'satuan_pendidikan')
                ->where(function ($query) {
                    $query->where('nama_field', 'satuan_pendidikan')
                        ->orWhere('label', 'Satuan Pendidikan');
                })
                ->update([
                    'autofill_source' => 'pendidikan',
                    'updated_at' => now(),
                ]);
        }
    }
};
