<?php

namespace Database\Seeders;

use App\Models\Lembaga;
use App\Models\Person;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PengurusUpzSeeder extends Seeder
{
    public function run(): void
    {
        $upzList = Lembaga::where('is_upz', true)->get();
        $jabatanIds = [
            'Ketua'      => PengurusJabatan::where('kode', 'JU-001')->value('id'),
            'Sekretaris' => PengurusJabatan::where('kode', 'JU-002')->value('id'),
            'Bendahara'  => PengurusJabatan::where('kode', 'JU-003')->value('id'),
        ];

        $nikCounter = 3301012600000000;

        foreach ($upzList as $upz) {
            // Skip if already has active pengurus
            $existingCount = Pengurus::where('jenis_pengurus', 'Lembaga')
                ->where('lembaga_id', $upz->id)
                ->where('is_active', true)
                ->count();

            if ($existingCount >= 3) {
                $this->command->info("UPZ {$upz->id} ({$upz->nama}) sudah punya 3 pengurus aktif, skip.");
                continue;
            }

            $positions = ['Ketua', 'Sekretaris', 'Bendahara'];

            foreach ($positions as $index => $pos) {
                $nik = str_pad((string)$nikCounter, 16, '0', STR_PAD_LEFT);
                $nikCounter++;

                $person = Person::firstOrCreate(
                    ['nik' => $nik],
                    [
                        'nomor_kk'    => str_pad((string)($nikCounter - 1000), 16, '0', STR_PAD_LEFT),
                        'nama'        => "{$pos} {$upz->nama}",
                        'jenis_kelamin' => $index === 0 ? 'L' : ($index === 1 ? 'P' : 'L'),
                        'tempat_lahir' => 'Cilacap',
                        'tanggal_lahir' => '1980-01-01',
                        'handphone'   => '08' . str_pad((string)rand(100000000, 999999999), 10, '0', STR_PAD_LEFT),
                        'email'       => strtolower(str_replace(' ', '', $pos)) . '.' . strtolower(str_replace(' ', '', $upz->nama)) . '@upz.baznas.id',
                        'alamat'      => $upz->alamat ?: 'Kantor ' . $upz->nama,
                        'id_provinsi' => '33',
                        'id_kabupaten' => '33.01',
                    ]
                );

                Pengurus::updateOrCreate(
                    [
                        'jenis_pengurus'     => 'Lembaga',
                        'lembaga_id'         => $upz->id,
                        'person_id'          => $person->id,
                        'pengurus_jabatan_id'=> $jabatanIds[$pos],
                    ],
                    [
                        'no_sk'           => "SK-UPZ/{$upz->id}/2026",
                        'tanggal_mulai'   => '2026-01-01',
                        'tanggal_selesai' => null,
                        'is_active'       => true,
                    ]
                );

                // Sync ketua to lembaga
                if ($pos === 'Ketua') {
                    $upz->update([
                        'ketua_nama' => $person->nama,
                        'ketua_nik'  => $person->nik,
                    ]);
                }
            }

            $this->command->info("Created 3 pengurus for UPZ {$upz->id} ({$upz->nama})");
        }

        $this->command->info("PengurusUpzSeeder selesai. Total UPZ: {$upzList->count()}");
    }
}