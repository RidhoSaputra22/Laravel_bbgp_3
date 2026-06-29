<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const RTL_FOREIGN_KEY = 'rtl_documents_rtl_id_foreign';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (
            ! Schema::hasTable('rtl_documents') ||
            ! Schema::hasTable('rtls') ||
            ! Schema::hasColumn('rtl_documents', 'rtl_id') ||
            $this->foreignKeyExists('rtl_documents', self::RTL_FOREIGN_KEY)
        ) {
            return;
        }

        Schema::table('rtl_documents', function (Blueprint $table) {
            $table->foreign('rtl_id', self::RTL_FOREIGN_KEY)
                ->references('id')
                ->on('rtls')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            ! Schema::hasTable('rtl_documents') ||
            ! $this->foreignKeyExists('rtl_documents', self::RTL_FOREIGN_KEY)
        ) {
            return;
        }

        Schema::table('rtl_documents', function (Blueprint $table) {
            $table->dropForeign(self::RTL_FOREIGN_KEY);
        });
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $constraintName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};
