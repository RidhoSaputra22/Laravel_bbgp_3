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

                if (!Schema::hasColumn('gurus', 'username')) {
                    $table->string('username')->nullable()->after('nama_lengkap');
                }

                if (!Schema::hasColumn('gurus', 'jenis_data')) {
                    $table->string('jenis_data')->nullable()->after('is_verif');
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

                if (!Schema::hasColumn('pegawais', 'pas_foto')) {
                    $table->string('pas_foto')->nullable()->after('no_wa');
                }
            });
        }

        $this->addColumnIfMissing('honors', 'kode_anggaran', fn (Blueprint $table) => $table->string('kode_anggaran')->nullable());
        $this->addColumnIfMissing('internals', 'jenis_data', fn (Blueprint $table) => $table->string('jenis_data')->nullable());
        $this->addColumnIfMissing('internal_ppnpns', 'nik', fn (Blueprint $table) => $table->string('nik')->nullable());
        $this->addColumnIfMissing('kuitansis', 'biaya_penginapan', fn (Blueprint $table) => $table->integer('biaya_penginapan')->default(0)->nullable());
        $this->addColumnIfMissing('kuitansis', 'uang_harian', fn (Blueprint $table) => $table->integer('uang_harian')->default(0)->nullable());
        $this->addColumnIfMissing('kuitansis', 'jumlah_malam', fn (Blueprint $table) => $table->integer('jumlah_malam')->nullable());
        $this->addColumnIfMissing('kuitansis', 'bill_malam', fn (Blueprint $table) => $table->integer('bill_malam')->default(0)->nullable());
        $this->addColumnIfMissing('kuitansi_lokas', 'pegawai_id', fn (Blueprint $table) => $table->string('pegawai_id')->nullable());
        $this->addColumnIfMissing('kuitansi_lokas', 'no_bukti', fn (Blueprint $table) => $table->string('no_bukti')->default('-'));
        $this->addColumnIfMissing('pegawaiPpnpns', 'nama', fn (Blueprint $table) => $table->string('nama', 100)->nullable());
        $this->addColumnIfMissing('pegawaiPpnpns', 'jabatan', fn (Blueprint $table) => $table->string('jabatan', 100)->nullable());
        $this->addColumnIfMissing('pegawaiPpnpns', 'nip', fn (Blueprint $table) => $table->string('nip')->nullable());
        $this->addColumnIfMissing('pegawaiPpnpns', 'nik', fn (Blueprint $table) => $table->string('nik')->nullable());
        $this->addColumnIfMissing('pendampings', 'nip', fn (Blueprint $table) => $table->string('nip')->nullable());
        $this->addColumnIfMissing('pendampings', 'nik', fn (Blueprint $table) => $table->string('nik')->nullable());
        $this->addColumnIfMissing('pendampings', 'tgl_kegiatan', fn (Blueprint $table) => $table->date('tgl_kegiatan')->nullable());
        $this->addColumnIfMissing('pendampings', 'kabupaten', fn (Blueprint $table) => $table->string('kabupaten')->nullable());
        $this->addColumnIfMissing('penomoran_kegiatans', 'no_surat', fn (Blueprint $table) => $table->string('no_surat')->nullable());
        $this->addColumnIfMissing('penomoran_kegiatans', 'tgl_surat', fn (Blueprint $table) => $table->date('tgl_surat')->nullable());
        $this->addColumnIfMissing('penomoran_kegiatans', 'kode_anggaran', fn (Blueprint $table) => $table->string('kode_anggaran')->nullable());
        $this->addColumnIfMissing('penomoran_kegiatans', 'kegiatan_id', fn (Blueprint $table) => $table->string('kegiatan_id')->nullable());
        $this->addColumnIfMissing('sekolahs', 'fasilitas_it_tambahan', fn (Blueprint $table) => $table->string('fasilitas_it_tambahan')->nullable());

        if (Schema::hasTable('berkas') && !Schema::hasColumn('berkas', 'metode_upload')) {
            if (Schema::hasColumn('berkas', 'metode_uploS')) {
                Schema::table('berkas', function (Blueprint $table) {
                    $table->renameColumn('metode_uploS', 'metode_upload');
                });
            } else {
                $this->addColumnIfMissing('berkas', 'metode_upload', fn (Blueprint $table) => $table->string('metode_upload')->nullable());
            }
        }
    }

    private function addColumnIfMissing(string $tableName, string $column, callable $definition): void
    {
        if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($definition) {
            $definition($table);
        });
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
