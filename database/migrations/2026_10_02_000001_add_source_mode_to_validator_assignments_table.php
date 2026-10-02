<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('validator_assignments')
            || Schema::hasColumn('validator_assignments', 'source_mode')) {
            return;
        }

        Schema::table('validator_assignments', function (Blueprint $table) {
            $table->string('source_mode', 30)
                ->default('combination')
                ->after('validator_form_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('validator_assignments')
            && Schema::hasColumn('validator_assignments', 'source_mode')) {
            Schema::table('validator_assignments', function (Blueprint $table) {
                $table->dropColumn('source_mode');
            });
        }
    }
};
