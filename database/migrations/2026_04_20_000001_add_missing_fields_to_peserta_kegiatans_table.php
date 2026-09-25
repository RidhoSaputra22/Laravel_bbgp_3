<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('peserta_kegiatans')) {
            return;
        }

        Schema::table('peserta_kegiatans', function (Blueprint $table) {
            if (!Schema::hasColumn('peserta_kegiatans', 'id_pegawai')) {
                $table->string('id_pegawai')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'nama')) {
                $table->string('nama')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'nip')) {
                $table->string('nip')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'alamat')) {
                $table->string('alamat')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'mata_pelajaran')) {
                $table->string('mata_pelajaran')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'status')) {
                $table->string('status')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'tgl_lahir')) {
                $table->date('tgl_lahir')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'agama')) {
                $table->string('agama')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'pendidikan')) {
                $table->string('pendidikan')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'alamat_rumah')) {
                $table->string('alamat_rumah')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'kabupaten_rumah')) {
                $table->string('kabupaten_rumah')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'npwp')) {
                $table->string('npwp')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'signature')) {
                $table->string('signature')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'jam_mengajar')) {
                $table->time('jam_mengajar')->nullable();
            }
            if (!Schema::hasColumn('peserta_kegiatans', 'jam_selesai')) {
                $table->time('jam_selesai')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('peserta_kegiatans')) {
            return;
        }

        $columns = [
            'id_pegawai',
            'nama',
            'nip',
            'alamat',
            'email',
            'mata_pelajaran',
            'status',
            'tempat_lahir',
            'tgl_lahir',
            'agama',
            'pendidikan',
            'alamat_rumah',
            'kabupaten_rumah',
            'npwp',
            'signature',
            'jam_mengajar',
            'jam_selesai',
        ];

        $existingColumns = array_values(array_filter(
            $columns,
            fn (string $column): bool => Schema::hasColumn('peserta_kegiatans', $column)
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table('peserta_kegiatans', function (Blueprint $table) use ($existingColumns) {
            $table->dropColumn($existingColumns);
        });
    }
};
