<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('assessments', 'kategori')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->string('kategori', 50)
                    ->default('assessment')
                    ->after('instrument_type')
                    ->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('assessments', 'kategori')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->dropIndex(['kategori']);
                $table->dropColumn('kategori');
            });
        }
    }
};
