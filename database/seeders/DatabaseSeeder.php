<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WilayahSeeder::class,
            PengurusJabatanSeeder::class,
            UserPengurusSeeder::class,
            AsnafSeeder::class,
            ProgramSubprogramSeeder::class,
            DanaSeeder::class,
            LembagaSeeder::class,
            UpzSimbaSeeder::class,
            PengurusUpzSeeder::class,
            MustahikSeeder::class,
            PengajuanBantuanSeeder::class,
            PengajuanUmumTahapanSeeder::class,
            MuzakkiDanSetoranSeeder::class,
        ]);
    }
}
