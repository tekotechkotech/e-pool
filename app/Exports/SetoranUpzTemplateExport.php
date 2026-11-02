<?php

namespace App\Exports;

use App\Models\Muzakki;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SetoranUpzTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected ?int $lembagaId;

    public function __construct(?int $lembagaId = null)
    {
        $this->lembagaId = $lembagaId;
    }

    public function headings(): array
    {
        return [
            'ID Muzakki',
            'NPWZ',
            'Nama Lengkap',
            'NIK',
            'Zakat (Rp)',
            'Infak (Rp)',
            'Fitrah (Rp)',
            'Titipan (Rp)',
            'Keterangan',
        ];
    }

    public function array(): array
    {
        $query = Muzakki::with(['person', 'upz']);

        if ($this->lembagaId) {
            $query->where('lembaga_id', $this->lembagaId);
        }

        $muzakkis = $query->get();

        $rows = [];
        foreach ($muzakkis as $m) {
            $rows[] = [
                $m->id,
                $m->npwz ?? '',
                $m->person?->nama ?? '',
                $m->person?->nik ? (string)$m->person->nik : '',
                0,
                0,
                0,
                0,
                '',
            ];
        }

        // Tambahkan 5 baris kosong cadangan di bawahnya untuk muzakki baru
        for ($i = 0; $i < 5; $i++) {
            $rows[] = [
                '',
                '',
                '',
                '',
                0,
                0,
                0,
                0,
                '',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
