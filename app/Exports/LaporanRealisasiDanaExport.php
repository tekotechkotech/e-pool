<?php

namespace App\Exports;

use App\Models\Dana;
use App\Models\DanaTransaksi;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanRealisasiDanaExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $filterSumber;
    protected $periodeTipe;
    protected $mode; // 'semua', 'masuk', 'keluar'

    public function __construct($startDate = null, $endDate = null, $filterSumber = 'all', $periodeTipe = 'all', $mode = 'semua')
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterSumber = $filterSumber;
        $this->periodeTipe = $periodeTipe;
        $this->mode = $mode;
    }

    public function view(): View
    {
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
        if ($this->periodeTipe !== 'all') {
            if ($this->startDate) {
                $transaksiQuery->whereDate('tanggal', '>=', $this->startDate);
            }
            if ($this->endDate) {
                $transaksiQuery->whereDate('tanggal', '<=', $this->endDate);
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
                if ($this->filterSumber === 'langsung' && $item['via_upz']) {
                    continue;
                }
                if ($this->filterSumber === 'upz' && !$item['via_upz']) {
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

        return view('exports.laporan-realisasi-dana-excel', [
            'groupedData' => $groupedData,
            'grandTotalMasuk' => $grandTotalMasuk,
            'grandTotalKeluar' => $grandTotalKeluar,
            'grandTotalSaldo' => $grandTotalSaldo,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'periodeTipe' => $this->periodeTipe,
            'mode' => $this->mode,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['italic' => true, 'size' => 10]],
        ];
    }
}
