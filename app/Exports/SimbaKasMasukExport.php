<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SimbaKasMasukExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $items;
    protected $counter = 0;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'No',
            'tgl_transaksi',
            'NPWZ',
            'nama',
            'zakat',
            'zakat fitrah',
            'infak',
            'titipan',
            'Keterangan',
            'Periode',
        ];
    }

    public function map($item): array
    {
        $this->counter++;

        $tgl = $item->setoranUpz?->tanggal_setor
            ? Carbon::parse($item->setoranUpz->tanggal_setor)->format('d/m/Y')
            : '';

        $npwz = $item->npwz ?: ($item->muzakki?->npwz ?? '');
        $nama = $item->nama ?: ($item->muzakki?->person?->nama ?? '');

        return [
            $this->counter,
            $tgl,
            $npwz,
            $nama,
            $item->nominal_zakat > 0 ? (int)$item->nominal_zakat : 0,
            $item->nominal_fitrah > 0 ? (int)$item->nominal_fitrah : 0,
            $item->nominal_infak > 0 ? (int)$item->nominal_infak : 0,
            $item->nominal_titipan > 0 ? (int)$item->nominal_titipan : 0,
            $item->keterangan ?? '',
            $item->periode ?? ($item->setoranUpz?->periode ?? ''),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0284C7'], // Sky 600
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
        ];
    }
}
