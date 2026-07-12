<?php

namespace Tests\Unit;

use App\Support\Assessment\AssessmentFieldLookupResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AssessmentFieldLookupResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        foreach ([
            'jabatan_penugasan_golongans',
            'golongan_p3ks',
            'jabatans',
            'jabatan_kependidikans',
            'kepegawaians',
        ] as $tableName) {
            Schema::connection('sqlite')->create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        foreach ([
            'kepegawaians',
            'jabatan_kependidikans',
            'jabatans',
            'golongan_p3ks',
            'jabatan_penugasan_golongans',
        ] as $tableName) {
            Schema::connection('sqlite')->dropIfExists($tableName);
        }

        parent::tearDown();
    }

    public function test_it_merges_golongan_lookup_options_from_pns_and_pppk_tables(): void
    {
        DB::table('jabatan_penugasan_golongans')->insert([
            ['name' => 'III/a', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IV/a', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('golongan_p3ks')->insert([
            ['name' => 'Ahli Pertama', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'III/a', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $options = app(AssessmentFieldLookupResolver::class)->resolveOptions('master_golongan');

        $this->assertSame(
            ['Ahli Pertama', 'III/a', 'IV/a'],
            collect($options)->pluck('label')->all()
        );
    }

    public function test_it_infers_target_specific_jabatan_lookup_source(): void
    {
        $resolver = app(AssessmentFieldLookupResolver::class);

        $this->assertSame(
            'master_jabatan_kependidikan',
            $resolver->inferSourceFromField('Jabatan', 'jabatan', 'tenaga_kependidikan')
        );
        $this->assertSame(
            'master_status_kepegawaian',
            $resolver->inferSourceFromField('Status Kepegawaian', 'status_kepegawaian')
        );
    }
}
