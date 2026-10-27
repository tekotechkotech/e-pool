<?php

namespace Database\Seeders;

use App\Models\Lembaga;
use App\Models\Muzakki;
use App\Models\Person;
use App\Models\SetoranUpz;
use App\Models\SetoranUpzItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MuzakkiDanSetoranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada setidaknya satu user untuk pencatatan
        $user = User::first();
        if (!$user) {
            $personAdmin = Person::firstOrCreate(
                ['nik' => '3301010101900099'],
                [
                    'nomor_kk' => '3301010101900000',
                    'nama' => 'Petugas Setoran UPZ',
                    'jenis_kelamin' => 'L',
                    'tempat_lahir' => 'Cilacap',
                    'tanggal_lahir' => '1990-01-01',
                    'handphone' => '081234567899',
                    'email' => 'petugas.upz@baznas.id',
                    'alamat' => 'Kantor BAZNAS Cilacap',
                    'id_provinsi' => '33',
                    'id_kabupaten' => '33.01',
                ]
            );

            $user = User::firstOrCreate(
                ['email' => 'petugas.upz@baznas.id'],
                [
                    'person_id' => $personAdmin->id,
                    'password' => Hash::make('password'),
                ]
            );
        }

        // Ambil atau buat UPZ
        $upzList = Lembaga::where('is_upz', true)->take(3)->get();
        if ($upzList->isEmpty()) {
            $upzList = collect([
                Lembaga::create([
                    'is_upz' => true,
                    'kode_simba' => 'UPZ-001',
                    'nama' => 'UPZ Dinas Pendidikan dan Kebudayaan',
                    'id_provinsi' => '33',
                    'id_kabupaten' => '33.01',
                    'is_active' => true,
                ]),
                Lembaga::create([
                    'is_upz' => true,
                    'kode_simba' => 'UPZ-002',
                    'nama' => 'UPZ Kementerian Agama Cilacap',
                    'id_provinsi' => '33',
                    'id_kabupaten' => '33.01',
                    'is_active' => true,
                ]),
            ]);
        }

        $upzUtama = $upzList->first();
        $upzKedua = $upzList->count() > 1 ? $upzList->get(1) : $upzUtama;

        // Sample data Muzakki
        $muzakkiSamples = [
            [
                'person' => [
                    'nik' => '3301011205800001',
                    'nomor_kk' => '3301011205800000',
                    'nama' => 'Drs. H. Bambang Subagyo, M.Pd.',
                    'jenis_kelamin' => 'L',
                    'tempat_lahir' => 'Cilacap',
                    'tanggal_lahir' => '1980-05-12',
                    'handphone' => '081223344551',
                    'email' => 'bambang.subagyo@example.com',
                    'alamat' => 'Jl. Kalimantan No. 45, Cilacap',
                    'id_provinsi' => '33',
                    'id_kabupaten' => '33.01',
                ],
                'muzakki' => [
                    'lembaga_id' => $upzUtama->id,
                    'nrm' => 'NRM-2026-0001',
                    'npwz' => 'NPWZ-3301-0001',
                    'npwp' => '09.123.456.7-522.000',
                    'nip' => '198005122005011003',
                    'pekerjaan' => 'PNS / Guru',
                    'alamat_kantor' => 'Dinas P&K Cilacap, Jl. Kalimantan No. 51',
                    'alamat_korespondensi' => 'Jl. Kalimantan No. 45, Cilacap',
                    'jenis_zakat' => 'Zakat Profesi',
                    'tanggal_registrasi' => '2026-01-10',
                    'keterangan' => 'Muzakki rutin UPZ Dinas P&K',
                ],
            ],
            [
                'person' => [
                    'nik' => '3301012508850002',
                    'nomor_kk' => '3301012508850000',
                    'nama' => 'Hj. Siti Aminah, S.Ag.',
                    'jenis_kelamin' => 'P',
                    'tempat_lahir' => 'Banyumas',
                    'tanggal_lahir' => '1985-08-25',
                    'handphone' => '081223344552',
                    'email' => 'siti.aminah@example.com',
                    'alamat' => 'Jl. Rinjani No. 12, Cilacap',
                    'id_provinsi' => '33',
                    'id_kabupaten' => '33.01',
                ],
                'muzakki' => [
                    'lembaga_id' => $upzUtama->id,
                    'nrm' => 'NRM-2026-0002',
                    'npwz' => 'NPWZ-3301-0002',
                    'npwp' => '09.234.567.8-522.000',
                    'nip' => '198508252010012005',
                    'pekerjaan' => 'PNS / Pengawas Sekolah',
                    'alamat_kantor' => 'Dinas P&K Cilacap, Jl. Kalimantan No. 51',
                    'alamat_korespondensi' => 'Jl. Rinjani No. 12, Cilacap',
                    'jenis_zakat' => 'Zakat Profesi',
                    'tanggal_registrasi' => '2026-01-15',
                    'keterangan' => 'Muzakki rutin UPZ Dinas P&K',
                ],
            ],
            [
                'person' => [
                    'nik' => '3301011011780003',
                    'nomor_kk' => '3301011011780000',
                    'nama' => 'H. Agus Prasetyo, S.H.I.',
                    'jenis_kelamin' => 'L',
                    'tempat_lahir' => 'Cilacap',
                    'tanggal_lahir' => '1978-11-10',
                    'handphone' => '081223344553',
                    'email' => 'agus.prasetyo@example.com',
                    'alamat' => 'Jl. Gatot Subroto No. 88, Cilacap',
                    'id_provinsi' => '33',
                    'id_kabupaten' => '33.01',
                ],
                'muzakki' => [
                    'lembaga_id' => $upzKedua->id,
                    'nrm' => 'NRM-2026-0003',
                    'npwz' => 'NPWZ-3301-0003',
                    'npwp' => '09.345.678.9-522.000',
                    'nip' => '197811102003121002',
                    'pekerjaan' => 'PNS Kemenag',
                    'alamat_kantor' => 'Kantor Kemenag Cilacap',
                    'alamat_korespondensi' => 'Jl. Gatot Subroto No. 88, Cilacap',
                    'jenis_zakat' => 'Zakat Profesi',
                    'tanggal_registrasi' => '2026-01-20',
                    'keterangan' => 'Muzakki rutin UPZ Kemenag',
                ],
            ],
            [
                'person' => [
                    'nik' => '3301011502820004',
                    'nomor_kk' => '3301011502820000',
                    'nama' => 'Ir. Hendro Wijaya',
                    'jenis_kelamin' => 'L',
                    'tempat_lahir' => 'Semarang',
                    'tanggal_lahir' => '1982-02-15',
                    'handphone' => '081223344554',
                    'email' => 'hendro.wijaya@example.com',
                    'alamat' => 'Jl. Ahmad Yani No. 102, Cilacap',
                    'id_provinsi' => '33',
                    'id_kabupaten' => '33.01',
                ],
                'muzakki' => [
                    'lembaga_id' => $upzUtama->id,
                    'nrm' => 'NRM-2026-0004',
                    'npwz' => 'NPWZ-3301-0004',
                    'npwp' => '09.456.789.0-522.000',
                    'nip' => null,
                    'pekerjaan' => 'Wiraswasta / Konsultan',
                    'alamat_kantor' => 'Ruko Permata Indah B-5, Cilacap',
                    'alamat_korespondensi' => 'Jl. Ahmad Yani No. 102, Cilacap',
                    'jenis_zakat' => 'Zakat Maal',
                    'tanggal_registrasi' => '2026-02-01',
                    'keterangan' => 'Muzakki Zakat Maal & Infak',
                ],
            ],
        ];

        $savedMuzakkis = [];
        foreach ($muzakkiSamples as $sample) {
            $person = Person::firstOrCreate(
                ['nik' => $sample['person']['nik']],
                $sample['person']
            );

            $muzakki = Muzakki::updateOrCreate(
                ['person_id' => $person->id],
                array_merge(['person_id' => $person->id], $sample['muzakki'])
            );

            $savedMuzakkis[] = $muzakki;
        }

        // Buat Transaksi Setoran UPZ 1 (UPZ Utama - Periode Januari 2026)
        $m1 = $savedMuzakkis[0];
        $m2 = $savedMuzakkis[1];

        $itemsUpz1 = [
            [
                'muzakki_id' => $m1->id,
                'npwz' => $m1->npwz,
                'nama' => $m1->person->nama,
                'nominal_zakat' => 250000,
                'nominal_infak' => 50000,
                'nominal_fitrah' => 0,
                'nominal_titipan' => 0,
                'jumlah' => 300000,
                'keterangan' => 'Zakat profesi bulan Januari',
                'periode' => 'Januari 2026',
            ],
            [
                'muzakki_id' => $m2->id,
                'npwz' => $m2->npwz,
                'nama' => $m2->person->nama,
                'nominal_zakat' => 200000,
                'nominal_infak' => 50000,
                'nominal_fitrah' => 0,
                'nominal_titipan' => 0,
                'jumlah' => 250000,
                'keterangan' => 'Zakat profesi bulan Januari',
                'periode' => 'Januari 2026',
            ],
            [
                'muzakki_id' => null,
                'npwz' => null,
                'nama' => 'Hamba Allah (Kolektif Guru)',
                'nominal_zakat' => 550000,
                'nominal_infak' => 150000,
                'nominal_fitrah' => 0,
                'nominal_titipan' => 0,
                'jumlah' => 700000,
                'keterangan' => 'Titipan setoran kolektif staf guru',
                'periode' => 'Januari 2026',
            ],
        ];

        $totZakat1 = collect($itemsUpz1)->sum('nominal_zakat');
        $totInfak1 = collect($itemsUpz1)->sum('nominal_infak');
        $totFitrah1 = collect($itemsUpz1)->sum('nominal_fitrah');
        $totTitipan1 = collect($itemsUpz1)->sum('nominal_titipan');
        $totJumlah1 = collect($itemsUpz1)->sum('jumlah');

        $setoran1 = SetoranUpz::updateOrCreate(
            ['nomor_setoran' => 'SET-UPZ-202601-0001'],
            [
                'lembaga_id' => $upzUtama->id,
                'tanggal_setor' => '2026-01-28',
                'periode' => 'Januari 2026',
                'total_zakat' => $totZakat1,
                'total_infak' => $totInfak1,
                'total_fitrah' => $totFitrah1,
                'total_titipan' => $totTitipan1,
                'total_jumlah' => $totJumlah1,
                'keterangan' => 'Setoran zakat dan infak rutin bulan Januari 2026 dari UPZ',
                'bukti_setor' => 'bukti_setor/sample_transfer_jan2026.pdf',
                'status' => 'Terverifikasi',
                'user_id' => $user->id,
            ]
        );

        $setoran1->items()->delete();
        foreach ($itemsUpz1 as $itemData) {
            $setoran1->items()->create($itemData);
        }

        // Buat Transaksi Setoran UPZ 2 (UPZ Kedua - Periode Februari 2026)
        $m3 = $savedMuzakkis[2];
        $itemsUpz2 = [
            [
                'muzakki_id' => $m3->id,
                'npwz' => $m3->npwz,
                'nama' => $m3->person->nama,
                'nominal_zakat' => 350000,
                'nominal_infak' => 100000,
                'nominal_fitrah' => 0,
                'nominal_titipan' => 0,
                'jumlah' => 450000,
                'keterangan' => 'Zakat profesi bulan Februari',
                'periode' => 'Februari 2026',
            ],
            [
                'muzakki_id' => null,
                'npwz' => null,
                'nama' => 'Kolektif ASN Kemenag',
                'nominal_zakat' => 1200000,
                'nominal_infak' => 300000,
                'nominal_fitrah' => 0,
                'nominal_titipan' => 0,
                'jumlah' => 1500000,
                'keterangan' => 'Penerimaan infak dan zakat ASN Kemenag',
                'periode' => 'Februari 2026',
            ],
        ];

        $totZakat2 = collect($itemsUpz2)->sum('nominal_zakat');
        $totInfak2 = collect($itemsUpz2)->sum('nominal_infak');
        $totFitrah2 = collect($itemsUpz2)->sum('nominal_fitrah');
        $totTitipan2 = collect($itemsUpz2)->sum('nominal_titipan');
        $totJumlah2 = collect($itemsUpz2)->sum('jumlah');

        $setoran2 = SetoranUpz::updateOrCreate(
            ['nomor_setoran' => 'SET-UPZ-202602-0002'],
            [
                'lembaga_id' => $upzKedua->id,
                'tanggal_setor' => '2026-02-27',
                'periode' => 'Februari 2026',
                'total_zakat' => $totZakat2,
                'total_infak' => $totInfak2,
                'total_fitrah' => $totFitrah2,
                'total_titipan' => $totTitipan2,
                'total_jumlah' => $totJumlah2,
                'keterangan' => 'Setoran zakat dan infak rutin bulan Februari 2026',
                'bukti_setor' => 'bukti_setor/sample_transfer_feb2026.pdf',
                'status' => 'Terverifikasi',
                'user_id' => $user->id,
            ]
        );

        $setoran2->items()->delete();
        foreach ($itemsUpz2 as $itemData) {
            $setoran2->items()->create($itemData);
        }

        $this->command->info('MuzakkiDanSetoranSeeder selesai: data Muzakki dan Setoran UPZ berhasil di-seed.');
    }
}
