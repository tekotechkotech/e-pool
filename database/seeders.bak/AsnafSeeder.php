<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asnaf;

class AsnafSeeder extends Seeder
{
    public function run(): void
    {
        $asnafs = [
            ['nama' => 'Fakir', 'kode' => 'A01', 'sort_order' => 1],
            ['nama' => 'Miskin', 'kode' => 'A02', 'sort_order' => 2],
            ['nama' => 'Amil', 'kode' => 'A03', 'sort_order' => 3],
            ['nama' => 'Muallaf', 'kode' => 'A04', 'sort_order' => 4],
            ['nama' => 'Riqab', 'kode' => 'A05', 'sort_order' => 5],
            ['nama' => 'Gharimin', 'kode' => 'A06', 'sort_order' => 6],
            ['nama' => 'Fisabilillah', 'kode' => 'A07', 'sort_order' => 7],
            ['nama' => 'Ibnu Sabil', 'kode' => 'A08', 'sort_order' => 8],
        ];

        foreach ($asnafs as $asnaf) {
            Asnaf::firstOrCreate(['nama' => $asnaf['nama']], $asnaf);
        }
    }
}
