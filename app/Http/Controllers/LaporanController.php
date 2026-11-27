<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dana;
use App\Models\DanaTransaksi;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanRealisasiDanaExport;

class LaporanController extends Controller
{
    public function realisasiDana(Request $request)
    {
        $mode = $request->query('mode', 'masuk');
        if (!in_array($mode, ['semua', 'masuk', 'keluar'], true)) {
            $mode = 'masuk';
        }

        $periodeTipe = $request->query('periode_tipe', 'all');
        $startDate = $request->query('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $filterSumber = $request->query('filter_sumber', 'all');

        if ($periodeTipe === 'this_month') {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        } elseif ($periodeTipe === 'this_year') {
            $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
            $endDate = Carbon::now()->endOfYear()->format('Y-m-d');
        }

        // 18 Akun Standar BAZNAS sesuai bagan akun standar (COA)
        $standardCOA = [
            'ZAKAT' => [
                ['kode' => '2.1', 'nama' => 'Zakat Maal Perorangan', 'via_upz' => false],
                ['kode' => '2.2', 'nama' => 'Zakat Maal-Perorangan via UPZ', 'via_upz' => true],
                ['kode' => '2.3', 'nama' => 'Zakat Maal Badan', 'via_upz' => false],
                ['kode' => '2.4', 'nama' => 'Zakat Fitrah', 'via_upz' => false],
                ['kode' => '2.5', 'nama' => 'Zakat Fitrah via UPZ', 'via_upz' => true],
            ],
            'INFAK/SEDEKAH' => [
                ['kode' => '2.6', 'nama' => 'Infak/Sedekah Tidak Terikat', 'via_upz' => false],
                ['kode' => '2.7', 'nama' => 'Infak/Sedekah Tidak Terikat via UPZ', 'via_upz' => true],
                ['kode' => '2.8', 'nama' => 'Infak/Sedekah Terikat', 'via_upz' => false],
                ['kode' => '2.9', 'nama' => 'Infak Penyaluran', 'via_upz' => false],
                ['kode' => '2.10', 'nama' => 'Infak Operasional', 'via_upz' => false],
            ],
            'CSR' => [
                ['kode' => '2.11', 'nama' => 'Corporate Social Responsibility', 'via_upz' => false],
            ],
            'DSKL' => [
                ['kode' => '2.12', 'nama' => 'Qurban', 'via_upz' => false],
                ['kode' => '2.13', 'nama' => 'Fidyah', 'via_upz' => false],
                ['kode' => '2.14', 'nama' => 'DSKL lainnya', 'via_upz' => false],
                ['kode' => '2.15', 'nama' => 'Qurban via UPZ', 'via_upz' => true],
                ['kode' => '2.16', 'nama' => 'Fidyah via UPZ', 'via_upz' => true],
                ['kode' => '2.17', 'nama' => 'DSKL lainnya via UPZ', 'via_upz' => true],
            ],
            'DANA TITIPAN' => [
                ['kode' => '2.18', 'nama' => 'Dana Titipan', 'via_upz' => false],
            ],
        ];

        $dbDanas = Dana::all();
        $transaksiQuery = DanaTransaksi::query();
        if ($periodeTipe !== 'all') {
            if ($startDate) {
                $transaksiQuery->whereDate('tanggal', '>=', $startDate);
            }
            if ($endDate) {
                $transaksiQuery->whereDate('tanggal', '<=', $endDate);
            }
        }
        $transaksis = $transaksiQuery->get();

        $groupedData = [];
        $grandTotalMasuk = 0;
        $grandTotalKeluar = 0;

        foreach ($standardCOA as $categoryName => $items) {
            $categoryItems = [];
            $subMasuk = 0;
            $subKeluar = 0;

            foreach ($items as $item) {
                if ($filterSumber === 'langsung' && $item['via_upz']) {
                    continue;
                }
                if ($filterSumber === 'upz' && !$item['via_upz']) {
                    continue;
                }

                $matchingDana = $dbDanas->firstWhere('kode', $item['kode']);
                $danaId = $matchingDana ? $matchingDana->id : null;

                $masuk = 0;
                $keluar = 0;
                if ($danaId) {
                    $masuk = (float) $transaksis->where('dana_id', $danaId)->where('jenis', 'Masuk')->sum('nominal');
                    $keluar = (float) $transaksis->where('dana_id', $danaId)->where('jenis', 'Keluar')->sum('nominal');
                }
                $saldo = $masuk - $keluar;

                $categoryItems[] = [
                    'id' => $danaId,
                    'kode' => $item['kode'],
                    'nama' => $item['nama'],
                    'via_upz' => $item['via_upz'],
                    'masuk' => $masuk,
                    'keluar' => $keluar,
                    'saldo' => $saldo,
                ];

                $subMasuk += $masuk;
                $subKeluar += $keluar;
            }

            $subSaldo = $subMasuk - $subKeluar;
            $grandTotalMasuk += $subMasuk;
            $grandTotalKeluar += $subKeluar;

            $groupedData[$categoryName] = [
                'label' => $categoryName,
                'items' => $categoryItems,
                'total_masuk' => $subMasuk,
                'total_keluar' => $subKeluar,
                'total_saldo' => $subSaldo,
            ];
        }

        $grandTotalSaldo = $grandTotalMasuk - $grandTotalKeluar;

        return view('laporan.realisasi-dana', compact(
            'mode',
            'periodeTipe',
            'startDate',
            'endDate',
            'filterSumber',
            'groupedData',
            'grandTotalMasuk',
            'grandTotalKeluar',
            'grandTotalSaldo'
        ));
    }

    public function exportRealisasiDana(Request $request)
    {
        $mode = $request->query('mode', 'masuk');
        if (!in_array($mode, ['semua', 'masuk', 'keluar'], true)) {
            $mode = 'masuk';
        }

        $periodeTipe = $request->query('periode_tipe', 'all');
        $startDate = $request->query('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $filterSumber = $request->query('filter_sumber', 'all');

        $prefix = 'Laporan_Realisasi_Dana_';
        if ($mode === 'masuk') $prefix = 'Laporan_Penerimaan_Dana_';
        if ($mode === 'keluar') $prefix = 'Laporan_Penyaluran_Dana_';

        $fileName = $prefix . date('Ymd_His') . '.xlsx';
        return Excel::download(
            new LaporanRealisasiDanaExport(
                $startDate,
                $endDate,
                $filterSumber,
                $periodeTipe,
                $mode
            ),
            $fileName
        );
    }

    public function bukuKas(Request $request)
    {
        $jenisTransaksi = $request->query('jenis_transaksi', 'Masuk');
        if (!in_array($jenisTransaksi, ['all', 'Masuk', 'Keluar'], true)) {
            $jenisTransaksi = 'Masuk';
        }

        $selectedDanaId = $request->query('dana_id');
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $search = $request->query('search');

        $danas = Dana::active()->ordered()->get();

        // Hitung Saldo Awal sebelum startDate
        $saldoAwal = 0;
        if ($startDate) {
            $saldoAwalQuery = DanaTransaksi::whereDate('tanggal', '<', $startDate);
            if ($selectedDanaId) {
                $saldoAwalQuery->where('dana_id', $selectedDanaId);
            }
            $masukSebelumnya = (clone $saldoAwalQuery)->where('jenis', 'Masuk')->sum('nominal');
            $keluarSebelumnya = (clone $saldoAwalQuery)->where('jenis', 'Keluar')->sum('nominal');
            $saldoAwal = $masukSebelumnya - $keluarSebelumnya;
        }

        $transaksiQuery = DanaTransaksi::with(['dana', 'referensi'])
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc');

        if ($selectedDanaId) {
            $transaksiQuery->where('dana_id', $selectedDanaId);
        }

        if ($startDate) {
            $transaksiQuery->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $transaksiQuery->whereDate('tanggal', '<=', $endDate);
        }

        if ($jenisTransaksi !== 'all') {
            $transaksiQuery->where('jenis', $jenisTransaksi);
        }

        if ($search) {
            $transaksiQuery->where(function($q) use ($search) {
                $q->where('uraian', 'like', '%' . $search . '%')
                  ->orWhereHas('dana', function($dq) use ($search) {
                      $dq->where('nama', 'like', '%' . $search . '%')
                         ->orWhere('kode', 'like', '%' . $search . '%');
                  });
            });
        }

        $allPeriodTransaksis = $transaksiQuery->get();
        $totalMasukPeriode = $allPeriodTransaksis->where('jenis', 'Masuk')->sum('nominal');
        $totalKeluarPeriode = $allPeriodTransaksis->where('jenis', 'Keluar')->sum('nominal');
        $saldoAkhirPeriode = $saldoAwal + $totalMasukPeriode - $totalKeluarPeriode;

        $runningBalance = $saldoAwal;
        $runningBalances = [];
        foreach ($allPeriodTransaksis as $t) {
            if ($t->jenis === 'Masuk') {
                $runningBalance += $t->nominal;
            } else {
                $runningBalance -= $t->nominal;
            }
            $runningBalances[$t->id] = $runningBalance;
        }

        $selectedDana = $selectedDanaId ? Dana::find($selectedDanaId) : null;

        return view('laporan.buku-kas', compact(
            'danas',
            'selectedDana',
            'selectedDanaId',
            'startDate',
            'endDate',
            'jenisTransaksi',
            'search',
            'saldoAwal',
            'totalMasukPeriode',
            'totalKeluarPeriode',
            'saldoAkhirPeriode',
            'runningBalances',
            'allPeriodTransaksis'
        ));
    }
}
