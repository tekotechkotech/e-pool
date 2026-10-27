<?php

namespace Database\Seeders;

use App\Models\Pengurus;
use Illuminate\Database\Seeder;
use App\Models\Person;
use App\Models\Lembaga;
use App\Models\PengurusJabatan;

class LembagaSeeder extends Seeder
{
    public function run(): void
    {
        $lembagasData = [
            // Normal Lembaga
            [
                'is_upz'  => false,
                'nama' => 'Masjid Raya Al-Bayan',
                'alamat' => 'Jl. Raya Cimahi No. 12',
                'telepon' => '081111111111',
                'ketua_nama' => 'H. Asep Saepudin',
                'handphone' => '081111111111',
                'jenis' => 'Masjid',
                'email' => 'masjidalbayan@mail.id',
                'id_provinsi' => '32',
                'id_kabupaten' => '32.77',
                'pengurus' => [
                    ['nama' => 'H. Asep Saepudin', 'jabatan' => 'Ketua', 'nik' => '3201020101700001', 'nomor_kk' => '3201020101700000'],
                    ['nama' => 'Rina Kartika', 'jabatan' => 'Sekretaris', 'nik' => '3201020404920008', 'nomor_kk' => '3201020101700000'],
                ]
            ],
            // UPZ Lembaga
            [
                'is_upz'  => true,
                'nama'    => 'UPZ Masjid Al-Ikhlas',
                'jenis'   => 'Masjid',
                'ketua_nama' => 'H. Asep Saepudin',
                'ketua_nik'  => '3201020101700001',
                'alamat'  => 'Jl. Raya Cimahi No. 12',
                'handphone' => '081111111111',
                'email'   => 'alikhlas@upz.id',
                'id_provinsi'  => '32',
                'id_kabupaten' => '32.77', // Kota Cimahi
                'pengurus' => [
                    ['nama' => 'H. Asep Saepudin',   'jabatan' => 'Ketua',      'nik' => '3201020101700001', 'nomor_kk' => '3201020101700000'],
                    ['nama' => 'Deden Suryadi',       'jabatan' => 'Sekretaris', 'nik' => '3201020202800002', 'nomor_kk' => '3201020101700000'],
                    ['nama' => 'Yanti Nurfitri',      'jabatan' => 'Bendahara',  'nik' => '3201020303850003', 'nomor_kk' => '3201020303850000'],
                ],
            ],
            [
                'is_upz'  => true,
                'nama'    => 'UPZ RSUD Kota',
                'jenis'   => 'Instansi Kesehatan',
                'ketua_nama' => 'dr. Wahyu Santoso',
                'ketua_nik'  => '3201030101780004',
                'alamat'  => 'Jl. Rumah Sakit No. 5',
                'handphone' => '082222222222',
                'email'   => 'upz.rsud@mail.id',
                'id_provinsi'  => '32',
                'id_kabupaten' => '32.73', // Kota Bandung
                'pengurus' => [
                    ['nama' => 'dr. Wahyu Santoso',   'jabatan' => 'Ketua',      'nik' => '3201030101780004', 'nomor_kk' => '3201030101780000'],
                    ['nama' => 'Fitri Amalia',         'jabatan' => 'Sekretaris', 'nik' => '3201030202900005', 'nomor_kk' => '3201030101780000'],
                ],
            ],
            [
                'is_upz'  => true,
                'nama'    => 'UPZ SMK Negeri 1',
                'jenis'   => 'Lembaga Pendidikan',
                'ketua_nama' => 'Drs. Bambang Sutrisno',
                'ketua_nik'  => '3201040101720006',
                'alamat'  => 'Jl. Pendidikan No. 7',
                'handphone' => '083333333333',
                'email'   => 'upz.smkn1@mail.id',
                'id_provinsi'  => '32',
                'id_kabupaten' => '32.73', // Kota Bandung
                'pengurus' => [
                    ['nama' => 'Drs. Bambang Sutrisno','jabatan' => 'Ketua',      'nik' => '3201040101720006', 'nomor_kk' => '3201040101720000'],
                    ['nama' => 'Sri Mulyani',           'jabatan' => 'Bendahara',  'nik' => '3201040202880007', 'nomor_kk' => '3201040101720000'],
                ],
            ],
        ];

        foreach ($lembagasData as $lembagaData) {
            $pengurusData = $lembagaData['pengurus'];
            unset($lembagaData['pengurus']);

            $lembaga = Lembaga::create(array_merge($lembagaData, [
                'jumlah_anggota' => count($pengurusData),
                'is_active'      => true,
            ]));

            foreach ($pengurusData as $pData) {
                // Cari atau buat person untuk pengurus
                $person = Person::firstOrCreate(
                    ['nik' => $pData['nik']],
                    [
                        'nomor_kk'      => $pData['nomor_kk'],
                        'nama'          => $pData['nama'],
                        'jenis_kelamin' => 'L',
                        'id_provinsi'   => '32',
                        'id_kabupaten'  => '32.73', // Default Kota Bandung
                    ]
                );

                $jabatanName = $pData['jabatan'];
                $jabatanModel = PengurusJabatan::where('nama', $jabatanName)->first();

                Pengurus::create([
                    'jenis_pengurus' => "Lembaga",
                    'lembaga_id'    => $lembaga->id,
                    'person_id' => $person->id,
                    'pengurus_jabatan_id' => $jabatanModel ? $jabatanModel->id : null,
                    'is_active' => true,
                ]);
            }
        }
    }
}
