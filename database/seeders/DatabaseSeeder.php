<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(AdminSeeder::class);
        $this->call(LoginRoleSeeder::class);
        $this->call(StatusKepegawaianSeeder::class);
        $this->call(StatusPendidikanSeeder::class);
        $this->call(PendidikanSeeder::class);
        $this->call(JabatanSeeder::class);
        $this->call(ProvinsiSeeder::class);
        $this->call(KabupatenSeeder::class);
        $this->call(KecamatanSeeder::class);
        $this->call(SekolahSeeder::class);
        $this->call(JabatanPendidikSeeder::class);
        $this->call(JabatanKependidikanSeeder::class);
        $this->call(JabatanStakeHolderSeeder::class);
        $this->call(JenisJabatanSeeder::class);
        $this->call(JenisTugasSeeder::class);
        $this->call(LatarJabatanSeeder::class);
        $this->call(JabatanPenugasanSeeder::class);
        $this->call(GuruSeeder::class);
        $this->call(ValidatorUserSeeder::class);
        $this->call(AssessmentProtofolioSeeder::class);
        $this->call(AssessmentPilihanGandaSeeder::class);
        $this->call(AssessmentStudiKasusSeeder::class);
        $this->call(AssessmentEvaluasiLikertGuruSeeder::class);
        $this->call(AssessmentEvaluasiPelaksanaanSeeder::class);
        $this->call(AssessmentValidasiAhliSeeder::class);

    }
}
