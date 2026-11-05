<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Guru;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        $this->withoutMiddleware(VerifyCsrfToken::class);

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createRegistrationTables();
        $this->createSchoolTable();
        $this->seedFormOptions();
        DB::table('sekolahs')->insert([
            'npsn_sekolah' => '12345678',
            'nama_sekolah' => 'SD Negeri Pengujian',
            'kecamatan' => 'Rappocini',
            'kabupaten' => 'Kota Makassar',
        ]);
    }

    protected function tearDown(): void
    {
        foreach (array_merge(['gurus', 'admins', 'users', 'sekolahs'], array_reverse(self::MASTER_TABLES)) as $table) {
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
            ->assertDontSee('Periksa kembali data yang diisi:')
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
     * @dataProvider educationLevels
     */
    public function test_each_education_level_can_be_registered(string $pendidikan): void
    {
        $data = $this->completeFormData('Tenaga Pendidik');
        $data['pendidikan'] = $pendidikan;

        $this->post(route('user.daftar_guru'), $data)
            ->assertRedirect(route('user.guru'));

        $this->assertDatabaseHas('gurus', [
            'no_ktp' => $data['no_ktp'],
            'pendidikan' => $pendidikan,
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

    public function test_external_index_displays_registration_buttons_search_filters_and_table(): void
    {
        $response = $this->get(route('user.guru'));

        $response
            ->assertOk()
            ->assertSee('Registrasi Data Eksternal')
            ->assertSee('Tenaga Pendidik')
            ->assertSee('Tenaga Kependidikan')
            ->assertSee('Stakeholder')
            ->assertSee('Cari berdasarkan nama anda')
            ->assertSee('Cari berdasarkan No. KTP anda')
            ->assertSee('Kabupaten/Kota')
            ->assertSee('Detail Data Eksternal');
    }

    public function test_public_search_returns_only_verified_records_and_supports_filters(): void
    {
        $this->createGuru('Sari Pendidik', '3273012609260002', [
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
            'kabupaten' => 'Kota Makassar',
            'is_verif' => 'sudah',
        ]);
        $this->createGuru('Budi Kependidikan', '3273012609260003', [
            'eksternal_jabatan' => 'Tenaga Kependidikan',
            'jenis_jabatan' => 'Operator',
            'kabupaten' => 'Kabupaten Gowa',
            'is_verif' => 'sudah',
        ]);
        $this->createGuru('Tidak Tampil', '3273012609260004', [
            'eksternal_jabatan' => 'Stakeholder',
            'jenis_jabatan' => 'Komite',
            'is_verif' => 'belum',
        ]);

        $params = $this->dataTableParams();
        $response = $this->getJson(route('user.guru.data', $params));

        $response
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(2, 'data');

        $this->getJson(route('user.guru.data', array_merge($params, [
            'nik' => '0003',
            'kabupaten' => 'Kabupaten Gowa',
        ])))
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.nama_lengkap', 'Budi Kependidikan');

        $byName = $params;
        $byName['columns'][2]['search']['value'] = 'Sari';
        $this->getJson(route('user.guru.data', $byName))
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.nama_lengkap', 'Sari Pendidik');

        $byCategory = $params;
        $byCategory['columns'][4]['search']['value'] = 'Tenaga Kependidikan';
        $this->getJson(route('user.guru.data', $byCategory))
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.eksternal_jabatan', 'Tenaga Kependidikan');

        $byPosition = $params;
        $byPosition['columns'][6]['search']['value'] = 'Guru';
        $this->getJson(route('user.guru.data', $byPosition))
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.jenis_jabatan', 'Guru');
    }

    public function test_public_search_handles_empty_results_and_pagination(): void
    {
        $this->createGuru('Satu', '3273012609260005');
        $this->createGuru('Dua', '3273012609260006');

        $params = array_merge($this->dataTableParams(), ['start' => 1, 'length' => 1]);
        $response = $this->getJson(route('user.guru.data', $params));

        $response
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.DT_RowIndex', 2);

        $empty = $this->dataTableParams();
        $empty['columns'][2]['search']['value'] = 'Tidak Ada';
        $this->getJson(route('user.guru.data', $empty))
            ->assertJsonPath('recordsFiltered', 0)
            ->assertJsonCount(0, 'data');
    }

    public function test_public_detail_returns_safe_detail_and_school_data(): void
    {
        $guru = $this->createGuru('Detail Pengujian', '3273012609260007', [
            'npsn_sekolah' => '12345678',
            'gender' => 'Perempuan',
            'status_kepegawaian' => 'ASN',
            'kabupaten' => 'Kota Makassar',
        ]);

        $this->getJson(route('public.guru.detail', ['id' => $guru->id]))
            ->assertOk()
            ->assertJsonPath('data.nama_lengkap', 'Detail Pengujian')
            ->assertJsonPath('data.gender', 'Perempuan')
            ->assertJsonPath('data.status_kepegawaian', 'ASN')
            ->assertJsonPath('sekolah.nama_sekolah', 'SD Negeri Pengujian')
            ->assertJsonMissingPath('data.password');
    }

    public function test_public_detail_rejects_invalid_or_missing_id(): void
    {
        $this->getJson(route('public.guru.detail'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('id');

        $this->getJson(route('public.guru.detail', ['id' => 999]))
            ->assertNotFound();
    }

    public function test_school_lookup_searches_by_name_and_npsn_and_paginates(): void
    {
        DB::table('sekolahs')->insert([
            'npsn_sekolah' => '87654321',
            'nama_sekolah' => 'SMP Negeri Pengujian',
            'kecamatan' => 'Panakkukang',
            'kabupaten' => 'Kota Makassar',
        ]);

        $this->getJson(route('getSekolahs', ['q' => 'SMP Negeri', 'per_page' => 1]))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.npsn_sekolah', '87654321')
            ->assertJsonPath('per_page', 1);

        $this->getJson(route('getSekolahs', ['q' => '12345678']))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.nama_sekolah', 'SD Negeri Pengujian');

        $this->getJson(route('getSekolahs', ['q' => 'Tidak Ada']))
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonCount(0, 'data');
    }

    public function test_registration_returns_credentials_and_hashes_default_password(): void
    {
        $data = $this->completeFormData('Tenaga Pendidik');
        $data['no_ktp'] = '3273012609260008';

        $response = $this->post(route('user.daftar_guru'), $data);

        $response
            ->assertRedirect(route('user.guru'))
            ->assertSessionHas('registration_credentials', [
                'username' => $data['no_ktp'],
                'password' => '12345',
            ]);

        $user = DB::table('users')->where('no_ktp', $data['no_ktp'])->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('12345', $user->password));
        $this->assertSame('sudah', Guru::where('no_ktp', $data['no_ktp'])->value('is_verif'));
    }

    public function test_registration_supports_outside_region_and_no_bank_account(): void
    {
        $data = $this->completeFormData('Stakeholder');
        $data['no_ktp'] = '3273012609260009';
        $data['nuptk'] = '';
        $data['kabupaten'] = 'Tidak ada';
        $data['diluarKab'] = 'Kabupaten Sleman';
        $data['jenis_bank'] = 'Tidak ada';
        $data['no_rek'] = '0';

        $this->post(route('user.daftar_guru'), $data)
            ->assertRedirect(route('user.guru'));

        $this->assertDatabaseHas('gurus', [
            'no_ktp' => $data['no_ktp'],
            'kabupaten' => 'Kabupaten Sleman',
            'jenis_bank' => 'Tidak ada',
            'no_rek' => '0',
            'nuptk' => null,
        ]);
    }

    public function test_registration_rejects_a_position_from_another_external_category(): void
    {
        $data = $this->completeFormData('Tenaga Pendidik');
        $data['no_ktp'] = '3273012609260010';
        $data['jabJenis'] = 'Jabatan Kependidikan';
        DB::table('jabatan_kependidikans')->insert(['name' => 'Jabatan Kependidikan']);

        $this->from(route('user.form_guru', 'Tenaga Pendidik'))
            ->post(route('user.daftar_guru'), $data)
            ->assertRedirect(route('user.form_guru', 'Tenaga Pendidik'))
            ->assertSessionHasErrors('jabJenis');

        $this->assertDatabaseMissing('gurus', ['no_ktp' => $data['no_ktp']]);
    }

    public static function externalTypes(): array
    {
        return [
            'tenaga pendidik' => ['Tenaga Pendidik'],
            'tenaga kependidikan' => ['Tenaga Kependidikan'],
            'stakeholder' => ['Stakeholder'],
        ];
    }

    public static function educationLevels(): array
    {
        return [
            'SMA / SMK' => ['SMA / SMK'],
            'D3' => ['D3'],
            'S1' => ['S1'],
            'S2' => ['S2'],
            'S3' => ['S3'],
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

    private function createGuru(string $name, string $noKtp, array $overrides = []): Guru
    {
        return Guru::create(array_merge([
            'nama_lengkap' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'no_ktp' => $noKtp,
            'nip' => '199001012026010001',
            'npwp' => '123456789012345',
            'nuptk' => '1234567890123456',
            'status_kepegawaian' => 'ASN',
            'tempat_lahir' => 'Makassar',
            'tgl_lahir' => '1990-01-01',
            'gender' => 'Laki-laki',
            'alamat_rumah' => 'Jl. Pengujian',
            'agama' => 'Islam',
            'pendidikan' => 'S1',
            'kabupaten' => 'Kota Makassar',
            'satuan_pendidikan' => 'SD',
            'alamat_satuan' => 'Rappocini',
            'no_hp' => '081234567890',
            'no_wa' => '081234567890',
            'jenis_bank' => 'Bank BRI',
            'no_rek' => '1234567890',
            'npsn_sekolah' => '12345678',
            'eksternal_jabatan' => 'Tenaga Pendidik',
            'jenis_jabatan' => 'Guru',
            'kategori_jabatan' => 'GP (Guru Penggerak)',
            'tugas_jabatan' => 'PP (Pengajar Praktik)',
            'latar_jabatan' => 'Sertifikat GP (Guru Penggerak)',
            'is_verif' => 'sudah',
        ], $overrides));
    }

    private function dataTableParams(): array
    {
        return [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => ''],
            'columns' => array_fill(0, 10, ['search' => ['value' => '']]),
        ];
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

    private function createSchoolTable(): void
    {
        Schema::connection('sqlite')->create('sekolahs', function (Blueprint $table): void {
            $table->id();
            $table->string('npsn_sekolah');
            $table->string('nama_sekolah');
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->timestamps();
        });
    }

    private function seedFormOptions(): void
    {
        foreach (self::MASTER_TABLES as $table) {
            DB::table($table)->insert(['name' => 'Lainnya']);
        }

        DB::table('kepegawaians')->insert(['name' => 'ASN']);
        DB::table('satuan_pendidikans')->insert(['name' => 'SD']);
        foreach (array_keys(self::educationLevels()) as $pendidikan) {
            DB::table('pendidikans')->insert(['name' => $pendidikan]);
        }
        DB::table('kabupatens')->insert(['name' => 'Kota Makassar']);
    }
}
