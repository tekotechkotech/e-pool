<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserPengurusSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nik' => '3301010000000001',
                'nomor_kk' => '3301010000000000',
                'nama' => 'Super Administrator BAZNAS',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Cilacap',
                'tanggal_lahir' => '1980-01-01',
                'handphone' => '081111111111',
                'email' => 'superuser@baznas.id',
                'alamat' => 'Kantor BAZNAS',
                'jabatan' => 'Superuser',
            ],
            [
                'nik' => '3301010101800001',
                'nomor_kk' => '3301010101800000',
                'nama' => 'Ahmad Fauzi',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Cilacap',
                'tanggal_lahir' => '1980-01-01',
                'handphone' => '081234567890',
                'email' => 'admin@baznas.id',
                'alamat' => 'Jl. Masjid No. 1, Cilacap',
                'jabatan' => 'Admin BAZNAS',
            ],
            [
                'nik' => '3301010202850002',
                'nomor_kk' => '3301010101800000',
                'nama' => 'Siti Rahmawati',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Cilacap',
                'tanggal_lahir' => '1985-02-02',
                'handphone' => '082345678901',
                'email' => 'staff@baznas.id',
                'alamat' => 'Jl. Jenderal Sudirman No. 5, Cilacap',
                'jabatan' => 'Staff Penyaluran',
            ],
            [
                'nik' => '3301010303750003',
                'nomor_kk' => '3301010303750000',
                'nama' => 'Dr. H. Ridwan Hakim',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Cilacap',
                'tanggal_lahir' => '1975-03-03',
                'handphone' => '083456789012',
                'email' => 'pimpinan@baznas.id',
                'alamat' => 'Jl. Gatot Subroto No. 10, Cilacap',
                'jabatan' => 'Pimpinan BAZNAS',
            ],
        ];

        foreach ($data as $item) {
            $person = Person::updateOrCreate(
                ['nik' => $item['nik']],
                [
                    'nomor_kk' => $item['nomor_kk'],
                    'nama' => $item['nama'],
                    'jenis_kelamin' => $item['jenis_kelamin'],
                    'tempat_lahir' => $item['tempat_lahir'],
                    'tanggal_lahir' => $item['tanggal_lahir'],
                    'handphone' => $item['handphone'],
                    'email' => $item['email'],
                    'alamat' => $item['alamat'],
                    'id_provinsi' => '33',
                    'id_kabupaten' => '3301',
                ]
            );

            User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'person_id' => $person->id,
                    'password' => Hash::make('password'),
                ]
            );

            $jabatanModel = PengurusJabatan::where('nama', $item['jabatan'])->first();

            Pengurus::updateOrCreate(
                [
                    'jenis_pengurus' => 'Baznas',
                    'person_id' => $person->id,
                    'pengurus_jabatan_id' => $jabatanModel ? $jabatanModel->id : null,
                ],
                [
                    'lembaga_id' => null,
                    'no_sk' => null,
                    'file_sk' => null,
                    'tanggal_mulai' => '2026-01-01',
                    'tanggal_selesai' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}