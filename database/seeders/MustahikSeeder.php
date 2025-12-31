<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;
use App\Models\Mustahik;
use App\Models\Lembaga;

class MustahikSeeder extends Seeder
{
    public function run(): void
    {
        $mustahiksData = [
            [
                'person' => [
                    'nik' => '3201050101900010', 'nomor_kk' => '3201050101900000', 'nama' => 'Budi Santoso',
                    'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bandung',
                    'tanggal_lahir' => '1990-01-15', 'handphone' => '085100000001',
                    'alamat' => 'Jl. Kebon Jeruk No. 3, RT 01/02',
                    'id_provinsi' => '32', 'id_kabupaten' => '32.73',
                ],
                'mustahik' => ['pekerjaan' => 'Pedagang Kaki Lima'],
                'upzs' => [],
            ],
            [
                'person' => [
                    'nik' => '3201050202950011', 'nomor_kk' => '3201050101900000', 'nama' => 'Dewi Anggraini',
                    'jenis_kelamin' => 'P', 'tempat_lahir' => 'Sumedang',
                    'tanggal_lahir' => '1995-02-20', 'handphone' => '085100000002',
                    'alamat' => 'Jl. Cihampelas No. 45',
                    'id_provinsi' => '32', 'id_kabupaten' => '32.11',
                ],
                'mustahik' => ['pekerjaan' => 'Ibu Rumah Tangga', 'keterangan' => 'Janda dengan 2 anak'],
                'upzs' => [],
            ],
            [
                'person' => [
                    'nik' => '3201050303020012', 'nomor_kk' => '3201050303020000', 'nama' => 'Ahmad Rizki',
                    'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bandung',
                    'tanggal_lahir' => '2002-03-10', 'handphone' => '085100000003',
                    'alamat' => 'Jl. Rancaekek No. 7',
                    'id_provinsi' => '32', 'id_kabupaten' => '32.04',
                ],
                'mustahik' => ['pekerjaan' => 'Mahasiswa', 'nim' => 'MHS20220001'],
                'upzs' => [],
            ],
            [
                'person' => [
                    'nik' => '3201050404880013', 'nomor_kk' => '3201050404880000', 'nama' => 'Suherman',
                    'jenis_kelamin' => 'L', 'tempat_lahir' => 'Cianjur',
                    'tanggal_lahir' => '1988-04-05', 'handphone' => '085100000004',
                    'alamat' => 'Kp. Cisitu RT 03/05',
                    'id_provinsi' => '32', 'id_kabupaten' => '32.03',
                ],
                'mustahik' => ['pekerjaan' => 'Buruh Harian', 'keterangan' => 'Kepala keluarga dengan 4 tanggungan'],
                'upzs' => [],
            ],
            [
                'person' => [
                    'nik' => '3201050505920014', 'nomor_kk' => '3201050505920000', 'nama' => 'Neng Maryam',
                    'jenis_kelamin' => 'P', 'tempat_lahir' => 'Garut',
                    'tanggal_lahir' => '1992-05-17', 'handphone' => '085100000005',
                    'alamat' => 'Jl. Antapani No. 12',
                    'id_provinsi' => '32', 'id_kabupaten' => '32.05',
                ],
                'mustahik' => ['pekerjaan' => 'Penjahit', 'keterangan' => 'Pengusaha kecil yang terdampak'],
                'upzs' => [],
            ],
            [
                'person' => [
                    'nik' => '3201050606850015', 'nomor_kk' => '3201050606850000', 'nama' => 'Rudi Hermawan',
                    'jenis_kelamin' => 'L', 'tempat_lahir' => 'Tasikmalaya',
                    'tanggal_lahir' => '1985-06-25', 'handphone' => '085100000006',
                    'alamat' => 'Jl. Masjid Raya No. 2',
                    'id_provinsi' => '32', 'id_kabupaten' => '32.06',
                ],
                'mustahik' => ['pekerjaan' => 'Nelayan'],
                'upzs' => ['UPZ Masjid Al-Ikhlas'],
            ],
            [
                'person' => [
                    'nik' => '3201050707790016', 'nomor_kk' => '3201050707790000', 'nama' => 'Imas Kurniasih',
                    'jenis_kelamin' => 'P', 'tempat_lahir' => 'Cirebon',
                    'tanggal_lahir' => '1979-07-12', 'handphone' => '085100000007',
                    'alamat' => 'Jl. Pasar Baru No. 8',
                    'id_provinsi' => '32', 'id_kabupaten' => '32.09',
                ],
                'mustahik' => ['pekerjaan' => 'Pedagang', 'keterangan' => 'Warga binaan masjid Al-Ikhlas'],
                'upzs' => ['UPZ Masjid Al-Ikhlas'],
            ],
            [
                'person' => [
                    'nik' => '3201050808030017', 'nomor_kk' => '3201050808030000', 'nama' => 'Ilham Maulana',
                    'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bandung',
                    'tanggal_lahir' => '2003-08-20', 'handphone' => '085100000008',
                    'alamat' => 'Jl. Setiabudhi No. 100',
                    'id_provinsi' => '32', 'id_kabupaten' => '32.73',
                ],
                'mustahik' => ['pekerjaan' => 'Pelajar', 'nim' => 'SMK2023001'],
                'upzs' => ['UPZ SMK Negeri 1'],
            ],
        ];

        // Tambah mustahik tambahan hingga 45 orang
        $firstNames = ['Agus', 'Bambang', 'Cahyono', 'Dedi', 'Eko', 'Fajar', 'Gunawan', 'Hadi', 'Indra', 'Joko', 'Kurniawan', 'Lukman', 'Mulyadi', 'Nanang', 'Oki', 'Prasetyo', 'Qomar', 'Rian', 'Siti', 'Tri', 'Umar', 'Wahyu', 'Yanto', 'Zainal', 'Aminah', 'Bella', 'Citra', 'Dina', 'Endang', 'Fitri', 'Gita', 'Hani', 'Intan', 'Jamilah', 'Kartika', 'Lestari', 'Maya'];
        $lastNames = ['Saputra', 'Hidayat', 'Kusuma', 'Pratama', 'Nugroho', 'Wibowo', 'Setiawan', 'Siregar', 'Lestari', 'Utami', 'Rahayu', 'Wijaya', 'Purnama', 'Firmansyah', 'Hakim', 'Mahendra', 'Susanto', 'Syahputra', 'Ramadhan', 'Fauzi'];
        $jobs = ['Buruh Harian', 'Pedagang Keliling', 'Petani', 'Buruh Tani', 'Tukang Ojek', 'Pekerja Serabutan', 'Penjual Gorengan', 'Ibu Rumah Tangga', 'Guru Ngaji Honorer', 'Marbot Masjid', 'Tukang Jahit', 'Pemulung', 'Supir Angkot'];

        for ($k = 9; $k <= 45; $k++) {
            $isL = ($k % 2 === 1);
            $nama = $firstNames[($k - 9) % count($firstNames)] . ' ' . $lastNames[($k * 3) % count($lastNames)];
            $mustahiksData[] = [
                'person' => [
                    'nik' => sprintf('320105%02d%02d85%04d', rand(1, 28), rand(1, 12), $k),
                    'nomor_kk' => sprintf('320105010185%04d', intdiv($k, 3) * 3),
                    'nama' => $nama,
                    'jenis_kelamin' => $isL ? 'L' : 'P',
                    'tempat_lahir' => 'Cilacap',
                    'tanggal_lahir' => sprintf('19%02d-%02d-%02d', rand(70, 99), rand(1, 12), rand(1, 28)),
                    'handphone' => sprintf('0852%08d', rand(10000000, 99999999)),
                    'alamat' => 'Desa Karangmangu RT ' . sprintf('%02d/%02d', rand(1, 10), rand(1, 10)) . ', Kec. Kroya',
                    'id_provinsi' => '33', // Jawa Tengah
                    'id_kabupaten' => '33.01', // Kab. Cilacap
                ],
                'mustahik' => [
                    'pekerjaan' => $jobs[$k % count($jobs)],
                    'keterangan' => 'Mustahik binaan berhak menerima zakat & infak',
                ],
                'upzs' => [],
            ];
        }

        $lembagaMap = Lembaga::pluck('id', 'nama');

        foreach ($mustahiksData as $data) {
            $person = Person::firstOrCreate(
                ['nik' => $data['person']['nik']],
                $data['person']
            );
            $mustahik = Mustahik::firstOrCreate(
                ['person_id' => $person->id],
                array_merge(['person_id' => $person->id], $data['mustahik'])
            );

            // Kaitkan ke Lembaga jika ada
            foreach ($data['upzs'] as $upzNama) {
                if (isset($lembagaMap[$upzNama])) {
                    $mustahik->lembagas()->syncWithoutDetaching([$lembagaMap[$upzNama]]);
                }
            }
        }
    }
}
