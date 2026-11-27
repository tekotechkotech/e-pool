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
            DanaSeeder::class,
            LembagaSeeder::class,
            UpzSimbaSeeder::class,
            PengurusUpzSeeder::class,
            MuzakkiSeeder::class,
            MuzakkiDanSetoranSeeder::class,
        ]);
    }
}
