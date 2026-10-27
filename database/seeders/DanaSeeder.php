<?php

namespace Database\Seeders;

use App\Models\Dana;
use App\Models\DanaTransaksi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $danas = [
            // 1. ZAKAT
            [
                'kode' => '2.1',
                'nama' => 'Zakat Maal Perorangan',
                'kategori' => 'Zakat',
                'via_upz' => false,
                'urutan' => 1,
                'keterangan' => 'Penerimaan Zakat Maal langsung dari perorangan/muzakki',
                'is_active' => true,
                'penerimaan_dummy' => 150000000,
            ],
            [
                'kode' => '2.2',
                'nama' => 'Zakat Maal-Perorangan via UPZ',
                'kategori' => 'Zakat',
                'via_upz' => true,
                'urutan' => 2,
                'keterangan' => 'Penerimaan Zakat Maal perorangan yang dihimpun melalui UPZ',
                'is_active' => true,
                'penerimaan_dummy' => 75000000,
            ],
            [
                'kode' => '2.3',
                'nama' => 'Zakat Maal Badan',
                'kategori' => 'Zakat',
                'via_upz' => false,
                'urutan' => 3,
                'keterangan' => 'Penerimaan Zakat Maal dari badan usaha / perusahaan',
                'is_active' => true,
                'penerimaan_dummy' => 200000000,
            ],
            [
                'kode' => '2.4',
                'nama' => 'Zakat Fitrah',
                'kategori' => 'Zakat',
                'via_upz' => false,
                'urutan' => 4,
                'keterangan' => 'Penerimaan Zakat Fitrah langsung',
                'is_active' => true,
                'penerimaan_dummy' => 50000000,
            ],
            [
                'kode' => '2.5',
                'nama' => 'Zakat Fitrah via UPZ',
                'kategori' => 'Zakat',
                'via_upz' => true,
                'urutan' => 5,
                'keterangan' => 'Penerimaan Zakat Fitrah yang dihimpun melalui UPZ',
                'is_active' => true,
                'penerimaan_dummy' => 35000000,
            ],

            // 2. INFAK/SEDEKAH
            [
                'kode' => '2.6',
                'nama' => 'Infak/Sedekah Tidak Terikat',
                'kategori' => 'Infak/Sedekah',
                'via_upz' => false,
                'urutan' => 6,
                'keterangan' => 'Penerimaan Infak/Sedekah yang tidak ditentukan peruntukannya secara spesifik',
                'is_active' => true,
                'penerimaan_dummy' => 80000000,
            ],
            [
                'kode' => '2.7',
                'nama' => 'Infak/Sedekah Tidak Terikat via UPZ',
                'kategori' => 'Infak/Sedekah',
                'via_upz' => true,
                'urutan' => 7,
                'keterangan' => 'Penerimaan Infak/Sedekah tidak terikat melalui UPZ',
                'is_active' => true,
                'penerimaan_dummy' => 40000000,
            ],
            [
                'kode' => '2.8',
                'nama' => 'Infak/Sedekah Terikat',
                'kategori' => 'Infak/Sedekah',
                'via_upz' => false,
                'urutan' => 8,
                'keterangan' => 'Penerimaan Infak/Sedekah dengan peruntukan program tertentu dari donatur',
                'is_active' => true,
                'penerimaan_dummy' => 60000000,
            ],
            [
                'kode' => '2.9',
                'nama' => 'Infak Penyaluran',
                'kategori' => 'Infak/Sedekah',
                'via_upz' => false,
                'urutan' => 9,
                'keterangan' => 'Infak yang dialokasikan khusus untuk program penyaluran',
                'is_active' => true,
                'penerimaan_dummy' => 30000000,
            ],
            [
                'kode' => '2.10',
                'nama' => 'Infak Operasional',
                'kategori' => 'Infak/Sedekah',
                'via_upz' => false,
                'urutan' => 10,
                'keterangan' => 'Infak yang dialokasikan untuk penunjang operasional BAZNAS',
                'is_active' => true,
                'penerimaan_dummy' => 25000000,
            ],

            // 3. CSR
            [
                'kode' => '2.11',
                'nama' => 'Corporate Social Responsibility',
                'kategori' => 'CSR',
                'via_upz' => false,
                'urutan' => 11,
                'keterangan' => 'Dana CSR / Tanggung Jawab Sosial dan Lingkungan Perusahaan',
                'is_active' => true,
                'penerimaan_dummy' => 120000000,
            ],

            // 4. DSKL (Dana Sosial Keagamaan Lainnya)
            [
                'kode' => '2.12',
                'nama' => 'Qurban',
                'kategori' => 'DSKL',
                'via_upz' => false,
                'urutan' => 12,
                'keterangan' => 'Penerimaan dana ibadah Qurban langsung',
                'is_active' => true,
                'penerimaan_dummy' => 90000000,
            ],
            [
                'kode' => '2.13',
                'nama' => 'Fidyah',
                'kategori' => 'DSKL',
                'via_upz' => false,
                'urutan' => 13,
                'keterangan' => 'Penerimaan pembayaran Fidyah langsung',
                'is_active' => true,
                'penerimaan_dummy' => 15000000,
            ],
            [
                'kode' => '2.14',
                'nama' => 'DSKL lainnya',
                'kategori' => 'DSKL',
                'via_upz' => false,
                'urutan' => 14,
                'keterangan' => 'Penerimaan dana sosial keagamaan lainnya (Kafarat, Nazar, dll)',
                'is_active' => true,
                'penerimaan_dummy' => 10000000,
            ],
            [
                'kode' => '2.15',
                'nama' => 'Qurban via UPZ',
                'kategori' => 'DSKL',
                'via_upz' => true,
                'urutan' => 15,
                'keterangan' => 'Penerimaan dana ibadah Qurban melalui UPZ',
                'is_active' => true,
                'penerimaan_dummy' => 45000000,
            ],
            [
                'kode' => '2.16',
                'nama' => 'Fidyah via UPZ',
                'kategori' => 'DSKL',
                'via_upz' => true,
                'urutan' => 16,
                'keterangan' => 'Penerimaan pembayaran Fidyah melalui UPZ',
                'is_active' => true,
                'penerimaan_dummy' => 8000000,
            ],
            [
                'kode' => '2.17',
                'nama' => 'DSKL lainnya via UPZ',
                'kategori' => 'DSKL',
                'via_upz' => true,
                'urutan' => 17,
                'keterangan' => 'Penerimaan DSKL lainnya melalui UPZ',
                'is_active' => true,
                'penerimaan_dummy' => 5000000,
            ],

            // 5. DANA TITIPAN
            [
                'kode' => '2.18',
                'nama' => 'Dana Titipan',
                'kategori' => 'Dana Titipan',
                'via_upz' => false,
                'urutan' => 18,
                'keterangan' => 'Penerimaan dana titipan pihak ketiga',
                'is_active' => true,
                'penerimaan_dummy' => 20000000,
            ],
        ];

        $now = Carbon::now();

        foreach ($danas as $data) {
            $dummyNominal = $data['penerimaan_dummy'] ?? 0;
            unset($data['penerimaan_dummy']);

            $dana = Dana::updateOrCreate(
                ['kode' => $data['kode']],
                $data
            );

            // Buat transaksi penerimaan awal (Masuk) jika belum ada transaksi masuk untuk dana ini
            $hasMasuk = DanaTransaksi::where('dana_id', $dana->id)->where('jenis', 'Masuk')->exists();
            if (!$hasMasuk && $dummyNominal > 0) {
                DanaTransaksi::create([
                    'tanggal' => $now->copy()->startOfYear()->addDays(5)->toDateString(),
                    'dana_id' => $dana->id,
                    'jenis' => 'Masuk',
                    'nominal' => $dummyNominal,
                    'uraian' => 'Penerimaan Saldo Awal / Penghimpunan ' . $dana->nama,
                    'referensi_type' => null,
                    'referensi_id' => null,
                ]);
            }
        }
    }
}