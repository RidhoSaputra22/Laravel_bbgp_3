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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'no_ktp')) {
                    $table->string('no_ktp')->nullable()->after('username');
                }
            });
        }

        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                if (!Schema::hasColumn('admins', 'no_ktp')) {
                    $table->string('no_ktp')->nullable()->after('username');
                }
            });
        }

        if (Schema::hasTable('gurus')) {
            Schema::table('gurus', function (Blueprint $table) {
                if (!Schema::hasColumn('gurus', 'jenis_bank')) {
                    $table->string('jenis_bank')->nullable()->after('no_rek');
                }

                if (!Schema::hasColumn('gurus', 'latar_jabatan')) {
                    $table->string('latar_jabatan')->nullable()->after('tugas_jabatan');
                }
            });
        }

        if (Schema::hasTable('pegawais')) {
            Schema::table('pegawais', function (Blueprint $table) {
                if (!Schema::hasColumn('pegawais', 'username')) {
                    $table->string('username')->nullable()->after('id');
                }

                if (!Schema::hasColumn('pegawais', 'jenis_pegawai')) {
                    $table->string('jenis_pegawai')->nullable()->after('jabatan');
                }

                if (!Schema::hasColumn('pegawais', 'golongan')) {
                    $table->string('golongan')->nullable()->after('instansi');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'no_ktp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('no_ktp');
            });
        }

        if (Schema::hasTable('admins') && Schema::hasColumn('admins', 'no_ktp')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('no_ktp');
            });
        }

        if (Schema::hasTable('gurus')) {
            $columns = [];

            if (Schema::hasColumn('gurus', 'jenis_bank')) {
                $columns[] = 'jenis_bank';
            }

            if (Schema::hasColumn('gurus', 'latar_jabatan')) {
                $columns[] = 'latar_jabatan';
            }

            if ($columns !== []) {
                Schema::table('gurus', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }

        if (Schema::hasTable('pegawais')) {
            $columns = [];

            foreach (['username', 'jenis_pegawai', 'golongan'] as $column) {
                if (Schema::hasColumn('pegawais', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                Schema::table('pegawais', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
