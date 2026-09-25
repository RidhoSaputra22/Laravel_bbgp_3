<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExternalRegistrationFormTest extends TestCase
{
    private const MASTER_TABLES = [
        'kepegawaians',
        'satuan_pendidikans',
        'pendidikans',
        'jabatans',
        'kabupatens',
        'kecamatans',
        'jabatan_pendidiks',
        'jabatan_kependidikans',
        'jabatan_stake_holders',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createRegistrationTables();
        $this->seedFormOptions();
    }

    protected function tearDown(): void
    {
        foreach (array_merge(['gurus', 'admins', 'users'], array_reverse(self::MASTER_TABLES)) as $table) {
            Schema::connection('sqlite')->dropIfExists($table);
        }

        parent::tearDown();
    }

    /**
     * @dataProvider externalTypes
     */
    public function test_each_external_form_displays_all_input_fields(string $jenis): void
    {
        $response = $this->get(route('user.form_guru', $jenis));

        $response->assertOk();

        foreach ([
            'nama_lengkap',
            'email',
            'no_ktp',
            'nip',
            'npwp',
            'nuptk',
            'status_kepegawaian',
            'tempat_lahir',
            'tgl_lahir',
            'gender',
            'alamat_rumah',
            'agama',
            'pendidikan',
            'satuan_pendidikan',
            'kabupaten',
            'diluarKab',
            'no_hp',
            'no_wa',
            'jenis_bank',
            'no_rek',
            'jenisJabatan',
            'jabJenis',
            'jabLainnya',
            'npsn_sekolah',
            'kabupaten_sekolah',
            'alamat_satuan',
        ] as $field) {
            $response->assertSee('name="'.$field.'"', false);
        }

        $response->assertSee('Form Eksternal');
        $response->assertSee($jenis);
        $response->assertSee('value="'.$jenis.'"', false);
    }

    /**
     * @dataProvider externalTypes
     */
    public function test_each_external_form_displays_indonesian_validation_messages(string $jenis): void
    {
        $response = $this
            ->from(route('user.form_guru', $jenis))
            ->post(route('user.daftar_guru'), ['jenisJabatan' => $jenis]);

        $expectedErrors = [
            'nama_lengkap',
            'no_ktp',
            'jabJenis',
            'kabupaten',
            'email',
            'nip',
            'tempat_lahir',
            'tgl_lahir',
            'gender',
            'status_kepegawaian',
            'agama',
            'pendidikan',
            'satuan_pendidikan',
            'alamat_rumah',
            'no_hp',
            'no_wa',
            'no_rek',
            'jenis_bank',
            'npwp',
        ];

        if ($jenis !== 'Stakeholder') {
            $expectedErrors[] = 'nuptk';
        }

        $response
            ->assertRedirect(route('user.form_guru', $jenis))
            ->assertSessionHasErrors($expectedErrors);

        $this->get(route('user.form_guru', $jenis))
            ->assertSee('Periksa kembali data yang diisi:')
            ->assertSee('Nama lengkap wajib diisi.')
            ->assertSee('Email wajib diisi.')
            ->assertSee('Nomor KTP wajib diisi.');
    }

    /**
     * @dataProvider externalTypes
     */
    public function test_each_external_form_rejects_invalid_field_formats(string $jenis): void
    {
        $data = array_merge($this->completeFormData($jenis), [
            'no_ktp' => '123',
            'nip' => '123',
            'npwp' => '123',
            'nuptk' => '123',
            'status_kepegawaian' => 'Status Palsu',
            'jabJenis' => 'Jabatan Palsu',
            'kabupaten' => 'Kabupaten Palsu',
            'tgl_lahir' => '2999-01-01',
            'gender' => 'Tidak diketahui',
            'agama' => 'Agama Palsu',
            'pendidikan' => 'Pendidikan Palsu',
            'satuan_pendidikan' => 'Satuan Palsu',
            'no_hp' => '123',
            'no_wa' => '123',
            'no_rek' => 'abc',
            'jenis_bank' => 'Bank Palsu',
            'npsn_sekolah' => '123',
        ]);

        $response = $this
            ->from(route('user.form_guru', $jenis))
            ->post(route('user.daftar_guru'), $data);

        $response
            ->assertRedirect(route('user.form_guru', $jenis))
            ->assertSessionHasErrors([
                'no_ktp',
                'nip',
                'npwp',
                'nuptk',
                'status_kepegawaian',
                'jabJenis',
                'kabupaten',
                'tgl_lahir',
                'gender',
                'agama',
                'pendidikan',
                'satuan_pendidikan',
                'no_hp',
                'no_wa',
                'no_rek',
                'jenis_bank',
                'npsn_sekolah',
            ]);

        $this->get(route('user.form_guru', $jenis))
            ->assertSee('Nomor KTP harus terdiri dari 16 angka.')
            ->assertSee('NIP harus terdiri dari 18 angka.')
            ->assertSee('NPWP harus terdiri dari 15 sampai 16 angka.')
            ->assertSee('Tanggal lahir harus sebelum hari ini.')
            ->assertSee('Nomor handphone harus terdiri dari 10 sampai 15 angka.');
    }

    /**
     * @dataProvider externalTypes
     */
    public function test_each_external_form_accepts_a_complete_submission(string $jenis): void
    {
        $data = $this->completeFormData($jenis);

        $this->assertSame([], array_keys(array_filter(
            $data,
            static fn ($value): bool => $value === null || $value === ''
        )));

        $response = $this->post(route('user.daftar_guru'), $data);

        $response
            ->assertRedirect(route('user.guru'))
            ->assertSessionHas('message', 'user daftar');

        $this->assertDatabaseHas('gurus', [
            'nama_lengkap' => $data['nama_lengkap'],
            'no_ktp' => $data['no_ktp'],
            'email' => $data['email'],
            'eksternal_jabatan' => $jenis,
            'jenis_jabatan' => $data['jabLainnya'],
            'kabupaten' => $data['kabupaten'],
            'npsn_sekolah' => $data['npsn_sekolah'],
            'alamat_satuan' => $data['alamat_satuan'],
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $data['nama_lengkap'],
            'no_ktp' => $data['no_ktp'],
            'role' => $this->roleFor($jenis),
        ]);

        $this->assertDatabaseHas('admins', [
            'name' => $data['nama_lengkap'],
            'no_ktp' => $data['no_ktp'],
            'role' => $this->roleFor($jenis),
        ]);
    }

    /**
     * @dataProvider externalTypes
     */
    public function test_duplicate_ktp_returns_to_the_same_form_with_an_indonesian_error(string $jenis): void
    {
        $data = $this->completeFormData($jenis);

        $this->post(route('user.daftar_guru'), $data)
            ->assertRedirect(route('user.guru'));

        $response = $this->post(route('user.daftar_guru'), $data);

        $response
            ->assertRedirect(route('user.form_guru', $jenis))
            ->assertSessionHasErrors(['no_ktp']);

        $this->get(route('user.form_guru', $jenis))
            ->assertSee('Nomor KTP sudah terdaftar.');
    }

    public static function externalTypes(): array
    {
        return [
            'tenaga pendidik' => ['Tenaga Pendidik'],
            'tenaga kependidikan' => ['Tenaga Kependidikan'],
            'stakeholder' => ['Stakeholder'],
        ];
    }

    private function completeFormData(string $jenis): array
    {
        return [
            'nama_lengkap' => 'Tester '.$jenis,
            'email' => strtolower(str_replace(' ', '.', $jenis)).'@example.test',
            'no_ktp' => '3273012609260001',
            'nip' => '199001012026010001',
            'npwp' => '123456789012345',
            'nuptk' => '1234567890123456',
            'status_kepegawaian' => 'ASN',
            'tempat_lahir' => 'Makassar',
            'tgl_lahir' => '1990-01-01',
            'gender' => 'Laki-laki',
            'alamat_rumah' => 'Jl. Pendidikan No. 1, Makassar',
            'agama' => 'Islam',
            'pendidikan' => 'S1',
            'satuan_pendidikan' => 'SD',
            'kabupaten' => 'Kota Makassar',
            'diluarKab' => 'Kabupaten Gowa',
            'no_hp' => '081234567890',
            'no_wa' => '081234567890',
            'jenis_bank' => 'Bank BRI',
            'no_rek' => '1234567890',
            'jenisJabatan' => $jenis,
            'jabJenis' => 'Lainnya',
            'jabLainnya' => 'Jabatan Penguji '.$jenis,
            'jabKategori' => 'GP (Guru Penggerak)',
            'jabTugas' => 'PP (Pengajar Praktik)',
            'jabLatar' => 'Sertifikat GP (Guru Penggerak)',
            'npsn_sekolah' => '12345678',
            'kabupaten_sekolah' => 'Kota Makassar',
            'alamat_satuan' => 'Kecamatan Rappocini',
        ];
    }

    private function roleFor(string $jenis): string
    {
        return match ($jenis) {
            'Tenaga Pendidik' => 'tenaga pendidik',
            'Tenaga Kependidikan' => 'tenaga kependidikan',
            default => 'stakeholder',
        };
    }

    private function createRegistrationTables(): void
    {
        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('no_ktp')->nullable();
            $table->string('password')->nullable();
            $table->string('role');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('username');
            $table->string('no_ktp')->nullable();
            $table->string('password')->nullable();
            $table->string('role');
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('gurus', function (Blueprint $table): void {
            $table->id();

            foreach ([
                'nama_lengkap',
                'username',
                'email',
                'no_ktp',
                'nip',
                'tempat_lahir',
                'gender',
                'jabatan',
                'status',
                'status_kepegawaian',
                'agama',
                'pendidikan',
                'kabupaten',
                'satuan_pendidikan',
                'alamat_satuan',
                'alamat_rumah',
                'no_hp',
                'no_wa',
                'pas_foto',
                'no_rek',
                'jenis_bank',
                'npsn_sekolah',
                'npwp',
                'nuptk',
                'eksternal_jabatan',
                'jenis_jabatan',
                'kategori_jabatan',
                'tugas_jabatan',
                'latar_jabatan',
                'is_verif',
                'jenis_data',
            ] as $column) {
                $table->string($column)->nullable();
            }

            $table->date('tgl_lahir')->nullable();
            $table->timestamps();
        });

        foreach (self::MASTER_TABLES as $tableName) {
            Schema::connection('sqlite')->create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    private function seedFormOptions(): void
    {
        foreach (self::MASTER_TABLES as $table) {
            DB::table($table)->insert(['name' => 'Lainnya']);
        }

        DB::table('kepegawaians')->insert(['name' => 'ASN']);
        DB::table('satuan_pendidikans')->insert(['name' => 'SD']);
        DB::table('pendidikans')->insert(['name' => 'S1']);
        DB::table('kabupatens')->insert(['name' => 'Kota Makassar']);
    }
}
