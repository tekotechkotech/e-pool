<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin utama BAZNAS
        $adminPerson = Person::create([
            'nik'           => '3201010101800001',
            'nomor_kk'      => '3201010101800000',
            'nama'          => 'Ahmad Fauzi',
            'jenis_kelamin' => 'L',
            'tempat_lahir'  => 'Bandung',
            'tanggal_lahir' => '1980-01-01',
            'handphone'     => '081234567890',
            'email'         => 'admin@baznas.go.id',
            'alamat'        => 'Jl. Merdeka No. 1, Bandung',
            'id_provinsi'   => '32',
            'id_kabupaten'  => '32.73', // Kota Bandung
        ]);

        User::create([
            'person_id' => $adminPerson->id,
            'email'     => 'admin@baznas.go.id',
            'password'  => bcrypt('password'),
        ]);

        // Staff penyaluran
        $staffPerson = Person::create([
            'nik'           => '3201010202850002',
            'nomor_kk'      => '3201010101800000',
            'nama'          => 'Siti Rahmawati',
            'jenis_kelamin' => 'P',
            'tempat_lahir'  => 'Garut',
            'tanggal_lahir' => '1985-02-02',
            'handphone'     => '082345678901',
            'email'         => 'staff@baznas.go.id',
            'alamat'        => 'Jl. Sudirman No. 5, Bandung',
            'id_provinsi'   => '32',
            'id_kabupaten'  => '32.05', // Kab. Garut
        ]);

        User::create([
            'person_id' => $staffPerson->id,
            'email'     => 'staff@baznas.go.id',
            'password'  => bcrypt('password'),
        ]);

        // Pimpinan (approver)
        $pimpinanPerson = Person::create([
            'nik'           => '3201010303750003',
            'nomor_kk'      => '3201010303750000',
            'nama'          => 'Dr. H. Ridwan Hakim',
            'jenis_kelamin' => 'L',
            'tempat_lahir'  => 'Jakarta',
            'tanggal_lahir' => '1975-03-03',
            'handphone'     => '083456789012',
            'email'         => 'pimpinan@baznas.go.id',
            'alamat'        => 'Jl. Gatot Subroto No. 10, Bandung',
            'id_provinsi'   => '32',
            'id_kabupaten'  => '32.73', // Kota Bandung
        ]);

        User::create([
            'person_id' => $pimpinanPerson->id,
            'email'     => 'pimpinan@baznas.go.id',
            'password'  => bcrypt('password'),
        ]);
    }
}
