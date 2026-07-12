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
        $golongans = [
            'Golongan I',
            'Golongan II',
            'Golongan III',
            'Golongan IV',
            'Golongan V',
            'Golongan VI',
            'Golongan VII',
            'Golongan VIII',
            'Golongan IX',
            'Golongan X',
            'Golongan XI',
            'Golongan XII',
            'Golongan XIII',
            'Golongan XIV',
            'Golongan XV',
            'Golongan XVI',
            'Golongan XVII',
        ];

        $this->backfillGolonganTable('jabatan_penugasan_golongans', $golongans);
        $this->backfillGolonganTable('golongan_p3ks', $golongans);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op to avoid removing rows that may have existed before this backfill.
    }

    private function backfillGolonganTable(string $table, array $golongans): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($golongans as $golongan) {
            if (DB::table($table)->where('name', $golongan)->exists()) {
                continue;
            }

            DB::table($table)->insert([
                'name' => $golongan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
