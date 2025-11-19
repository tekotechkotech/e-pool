<?php

namespace Database\Seeders;

use App\Models\Lembaga;
use Illuminate\Database\Seeder;

class UpzSimbaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('referensi/Kode UPZ di Aplikasi Simba - Sheet1.csv');

        if (!file_exists($filePath)) {
            $this->command->error("File CSV tidak ditemukan: {$filePath}");
            return;
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            $this->command->error("Gagal membuka file CSV: {$filePath}");
            return;
        }

        $headerFound = false;
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Bersihkan setiap kolom dari whitespace
            $cleanRow = array_values(array_map('trim', $row));

            // Lewati baris kosong sama sekali
            if (empty(array_filter($cleanRow))) {
                continue;
            }

            // Cari header [..., "KODE", "UPZ"]
            if (!$headerFound) {
                if (in_array('KODE', $cleanRow, true) && in_array('UPZ', $cleanRow, true)) {
                    $headerFound = true;
                }
                continue;
            }

            // Lewati jika jumlah kolom tidak cukup
            if (count($cleanRow) < 4) {
                continue;
            }

            $kode = $cleanRow[2];
            $upz = $cleanRow[3];

            // Validasi KODE dan UPZ tidak boleh kosong
            if ($kode === '' || $upz === '') {
                continue;
            }

            Lembaga::updateOrCreate(
                ['kode_simba' => $kode],
                [
                    'is_upz' => true,
                    'nama' => $upz,
                    'id_provinsi' => '33',
                    'id_kabupaten' => '33.01',
                    'is_active' => true,
                ]
            );

            $count++;
        }

        fclose($handle);

        $this->command->info("UpzSimbaSeeder selesai: {$count} UPZ berhasil diproses.");
    }
}
