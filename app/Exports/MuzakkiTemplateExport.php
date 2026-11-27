<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MuzakkiTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    public function headings(): array
    {
        return [
            'Nama',
            'NPWP',
            'NIK *',
            'NIP',
            'Tempat Lahir',
            'tanggal lahir',
            'jenis kelamin',
            'pekerjaan',
            'alamat korespondensi',
            'alamat rumah*',
            'alamat kantor',
            'telepon',
            'handphone *',
            'email *',
            'UPZ',
            'zakat',
            'catatan',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Ahmad Fulan',
                '12313212432132',
                '3301000000000001',
                '198501012010011001',
                'Cilacap',
                '12/12/1990',
                'Pria',
                'PNS',
                'Jl. Mawar No. 10',
                'Jl. Mawar No. 10',
                'Kantor Dinas',
                '0282123456',
                '081234567890',
                'fulan@example.com',
                'UPZ Masjid Al-Ikhlas',
                'Zakat Penghasilan',
                'Data contoh pengisian',
            ],
            [
                'Siti Aminah',
                '',
                '3301000000000002',
                '',
                'Cilacap',
                '01/01/1992',
                'Wanita',
                'Wiraswasta',
                'Jl. Melati No. 5',
                'Jl. Melati No. 5',
                '',
                '',
                '081987654321',
                'siti@example.com',
                'UPZ Masjid Al-Ikhlas',
                'Zakat Maal',
                'Data contoh pengisian 2',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '118243'],
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
            'M' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
