<?php

namespace Database\Seeders;

use App\Models\Lembaga;
use App\Models\Muzakki;
use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MuzakkiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $upzs = Lembaga::where('is_upz', true)->get();

        if ($upzs->isEmpty()) {
            $this->command->warn('Tidak ada UPZ ditemukan. Membuat 2 UPZ default...');
            $upzs = collect([
                Lembaga::create([
                    'is_upz' => true,
                    'kode_simba' => 'UPZ-DEFAULT-001',
                    'nama' => 'UPZ Default Satu',
                    'id_provinsi' => '33',
                    'id_kabupaten' => '33.01',
                    'is_active' => true,
                ]),
                Lembaga::create([
                    'is_upz' => true,
                    'kode_simba' => 'UPZ-DEFAULT-002',
                    'nama' => 'UPZ Default Dua',
                    'id_provinsi' => '33',
                    'id_kabupaten' => '33.01',
                    'is_active' => true,
                ]),
            ]);
        }

        $faker = \Faker\Factory::create('id_ID');
        $minPerUpz = 10;
        $totalCreated = 0;

        foreach ($upzs as $upz) {
            $existingCount = Muzakki::where('lembaga_id', $upz->id)->count();
            $need = max(0, $minPerUpz - $existingCount);

            if ($need === 0) {
                $this->command->info("UPZ {$upz->nama} sudah memiliki {$existingCount} muzakki (min {$minPerUpz}). Lewati.");
                continue;
            }

            $this->command->info("UPZ {$upz->nama}: perlu {$need} muzakki tambahan (sudah ada {$existingCount}).");

            $createdForUpz = 0;
            $counter = $existingCount + 1;

            while ($createdForUpz < $need) {
                try {
                    DB::transaction(function () use ($upz, $faker, &$counter, &$createdForUpz) {
                        // Generate unique NIK (format: 3301 + DDMMYY + 4 digit urut)
                        $tglLahir = $faker->dateTimeBetween('-60 years', '-18 years');
                        $nik = '3301' . $tglLahir->format('dm') . $tglLahir->format('y') . str_pad($counter, 4, '0', STR_PAD_LEFT);

                        // Ensure NIK unique
                        while (Person::where('nik', $nik)->exists()) {
                            $counter++;
                            $nik = '3301' . $tglLahir->format('dm') . $tglLahir->format('y') . str_pad($counter, 4, '0', STR_PAD_LEFT);
                        }

                        $jenisKelamin = $faker->randomElement(['L', 'P']);
                        $nama = $jenisKelamin === 'L'
                            ? $faker->name('male')
                            : $faker->name('female');

                        $email = null;
                        if ($faker->boolean(70)) {
                            $email = $faker->unique()->safeEmail();
                        }

                        $person = Person::create([
                            'nik' => $nik,
                            'nomor_kk' => '3301' . $faker->numerify('##########'),
                            'nama' => $nama,
                            'jenis_kelamin' => $jenisKelamin,
                            'tempat_lahir' => $faker->randomElement(['Cilacap', 'Banyumas', 'Purwokerto', 'Kebumen', 'Banjarnegara']),
                            'tanggal_lahir' => $tglLahir->format('Y-m-d'),
                            'telepon' => $faker->optional(0.3)->phoneNumber(),
                            'handphone' => $faker->phoneNumber(),
                            'email' => $email,
                            'alamat' => $faker->streetAddress() . ', ' . $faker->randomElement(['Cilacap', 'Banyumas', 'Kroya', 'Maos', 'Kawunganten']),
                            'id_provinsi' => '33',
                            'id_kabupaten' => '33.01',
                            'id_kecamatan' => $faker->randomElement(['330101', '330102', '330103', '330104', '330105']),
                            'id_kelurahan' => $faker->numerify('330101####'),
                        ]);

                        $pekerjaan = $faker->randomElement([
                            'PNS / Guru',
                            'PNS / Pegawai Negeri',
                            'PNS / TNI/POLRI',
                            'Pegawai BUMD/BUMN',
                            'Karyawan Swasta',
                            'Wiraswasta / Pedagang',
                            'Petani / Nelayan',
                            'Pensiunan',
                            'Lainnya',
                        ]);

                        $jenisZakat = $faker->randomElement(['Zakat Profesi', 'Zakat Maal', 'Zakat Fitrah', 'Infak', 'Sedekah']);

                        $npwzNum = str_pad($counter, 4, '0', STR_PAD_LEFT);
                        $npwz = "NPWZ-3301-{$upz->id}-{$npwzNum}";

                        $nrmNum = str_pad($counter, 4, '0', STR_PAD_LEFT);
                        $nrm = "NRM-2026-{$nrmNum}";

                        Muzakki::create([
                            'person_id' => $person->id,
                            'lembaga_id' => $upz->id,
                            'nrm' => $nrm,
                            'npwz' => $npwz,
                            'npwp' => $faker->optional(0.5)->numerify('##.###.###.#-###.###'),
                            'nip' => $faker->optional(0.4)->numerify('################'),
                            'pekerjaan' => $pekerjaan,
                            'alamat_kantor' => $faker->optional(0.6)->streetAddress() . ', Cilacap',
                            'alamat_korespondensi' => $person->alamat,
                            'jenis_zakat' => $jenisZakat,
                            'tanggal_registrasi' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                            'keterangan' => "Muzakki {$upz->nama} - {$pekerjaan}",
                        ]);

                        $createdForUpz++;
                        $counter++;
                    });

                    $totalCreated++;
                } catch (\Exception $e) {
                    $this->command->error("Gagal membuat muzakki untuk {$upz->nama}: " . $e->getMessage());
                    $counter++; // avoid infinite loop
                }
            }

            $this->command->info("UPZ {$upz->nama}: berhasil membuat {$createdForUpz} muzakki baru.");
        }

        $this->command->info("MuzakkiSeeder selesai: total {$totalCreated} muzakki baru dibuat di semua UPZ (min {$minPerUpz} per UPZ).");
    }
}