<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('internals')) {
            return;
        }

        $this->normalizeIsVerifValues();

        $this->withRelaxedSqlMode(function () {
            $this->addColumnIfMissing('internals', 'nik', fn (Blueprint $table) => $table->string('nik')->nullable());
            $this->addColumnIfMissing('internals', 'kota', fn (Blueprint $table) => $table->string('kota')->nullable());
            $this->addColumnIfMissing('internals', 'tgl_selesai_kegiatan', fn (Blueprint $table) => $table->date('tgl_selesai_kegiatan')->nullable());
            $this->addColumnIfMissing('internals', 'jam_mulai', fn (Blueprint $table) => $table->time('jam_mulai')->nullable());
            $this->addColumnIfMissing('internals', 'jam_selesai', fn (Blueprint $table) => $table->time('jam_selesai')->nullable());
            $this->addColumnIfMissing('internals', 'deskripsi', fn (Blueprint $table) => $table->text('deskripsi')->nullable());
            $this->addColumnIfMissing('internals', 'hotel', fn (Blueprint $table) => $table->string('hotel')->nullable());
            $this->addColumnIfMissing('internals', 'transport_pergi', fn (Blueprint $table) => $table->unsignedBigInteger('transport_pergi')->default(0));
            $this->addColumnIfMissing('internals', 'transport_pulang', fn (Blueprint $table) => $table->unsignedBigInteger('transport_pulang')->default(0));
            $this->addColumnIfMissing('internals', 'bill_penginapan', fn (Blueprint $table) => $table->unsignedBigInteger('bill_penginapan')->default(0));
            $this->addColumnIfMissing('internals', 'hari_1', fn (Blueprint $table) => $table->unsignedBigInteger('hari_1')->default(0));
            $this->addColumnIfMissing('internals', 'hari_2', fn (Blueprint $table) => $table->unsignedBigInteger('hari_2')->default(0));
            $this->addColumnIfMissing('internals', 'hari_3', fn (Blueprint $table) => $table->unsignedBigInteger('hari_3')->default(0));
            $this->addColumnIfMissing('internals', 'hari_4', fn (Blueprint $table) => $table->unsignedBigInteger('hari_4')->default(0));
            $this->addColumnIfMissing('internals', 'hari_5', fn (Blueprint $table) => $table->unsignedBigInteger('hari_5')->default(0));
            $this->addColumnIfMissing('internals', 'hari_6', fn (Blueprint $table) => $table->unsignedBigInteger('hari_6')->default(0));
            $this->addColumnIfMissing('internals', 'hari_7', fn (Blueprint $table) => $table->unsignedBigInteger('hari_7')->default(0));
            $this->addColumnIfMissing('internals', 'bukti_bill', fn (Blueprint $table) => $table->string('bukti_bill')->nullable());
            $this->addColumnIfMissing('internals', 'is_verif', fn (Blueprint $table) => $table->string('is_verif', 20)->default('belum'));
        });

        if (Schema::hasColumn('internals', 'kota') && Schema::hasColumn('internals', 'tempat')) {
            DB::table('internals')
                ->whereNull('kota')
                ->update(['kota' => DB::raw('tempat')]);
        }

        if (
            Schema::hasColumn('internals', 'tgl_selesai_kegiatan') &&
            Schema::hasColumn('internals', 'tgl_kegiatan')
        ) {
            DB::table('internals')
                ->whereNull('tgl_selesai_kegiatan')
                ->update(['tgl_selesai_kegiatan' => DB::raw('tgl_kegiatan')]);
        }

        if (Schema::hasColumn('internals', 'jam_mulai')) {
            DB::table('internals')
                ->whereNull('jam_mulai')
                ->update(['jam_mulai' => '00:00:00']);
        }

        if (Schema::hasColumn('internals', 'jam_selesai')) {
            DB::table('internals')
                ->whereNull('jam_selesai')
                ->update(['jam_selesai' => '23:59:59']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op. The base internals migration has been updated to match the
        // runtime schema, so dropping these columns here would break fresh
        // installs that already created them in the original table definition.
    }

    private function normalizeIsVerifValues(): void
    {
        if (! Schema::hasColumn('internals', 'is_verif')) {
            return;
        }

        DB::statement("
            UPDATE internals
            SET is_verif = 'belum'
            WHERE is_verif IS NULL
               OR TRIM(is_verif) = ''
               OR is_verif NOT IN ('sudah', 'belum')
        ");
    }

    private function addColumnIfMissing(string $tableName, string $column, callable $definition): void
    {
        if (Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($definition) {
            $definition($table);
        });
    }

    private function withRelaxedSqlMode(callable $callback): void
    {
        $originalMode = (string) (DB::selectOne('SELECT @@SESSION.sql_mode AS sql_mode')->sql_mode ?? '');

        $relaxedModes = array_filter(
            explode(',', $originalMode),
            static fn (string $mode): bool => ! in_array($mode, [
                'STRICT_TRANS_TABLES',
                'STRICT_ALL_TABLES',
                'NO_ZERO_DATE',
                'NO_ZERO_IN_DATE',
            ], true)
        );

        $relaxedMode = implode(',', $relaxedModes);

        DB::statement("SET SESSION sql_mode = '".$this->escapeSqlString($relaxedMode)."'");

        try {
            $callback();
        } finally {
            DB::statement("SET SESSION sql_mode = '".$this->escapeSqlString($originalMode)."'");
        }
    }

    private function escapeSqlString(string $value): string
    {
        return str_replace("'", "''", $value);
    }
};
