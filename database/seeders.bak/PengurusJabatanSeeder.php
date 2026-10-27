<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PengurusJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatans = [
            ['kode' => 'JB-000', 'nama' => 'Superuser', 'tingkat' => 'baznas'],
            ['kode' => 'JB-001', 'nama' => 'Pimpinan BAZNAS', 'tingkat' => 'baznas'],
            ['kode' => 'JB-002', 'nama' => 'Admin BAZNAS', 'tingkat' => 'baznas'],
            ['kode' => 'JB-003', 'nama' => 'Staff Penyaluran', 'tingkat' => 'baznas'],
            ['kode' => 'JU-001', 'nama' => 'Ketua', 'tingkat' => 'lembaga'],
            ['kode' => 'JU-002', 'nama' => 'Sekretaris', 'tingkat' => 'lembaga'],
            ['kode' => 'JU-003', 'nama' => 'Bendahara', 'tingkat' => 'lembaga'],
            ['kode' => 'JU-004', 'nama' => 'Anggota', 'tingkat' => 'lembaga'],
        ];

        foreach ($jabatans as $jabatan) {
            \App\Models\PengurusJabatan::updateOrCreate(
                ['kode' => $jabatan['kode']],
                [
                    'nama' => $jabatan['nama'],
                    'tingkat' => $jabatan['tingkat'],
                    'is_active' => true,
                ]
            );
        }
    }
}
