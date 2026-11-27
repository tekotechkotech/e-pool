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

class SimbaMuzakkiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $muzakkis;

    public function __construct($muzakkis)
    {
        $this->muzakkis = $muzakkis;
    }

    public function collection()
    {
        return $this->muzakkis;
    }

    public function headings(): array
    {
        return [
            'Tanggal Registrasi',
            'Nama',
            'NPWP',
            'NIK',
            'NIP',
            'Tempat Lahir',
            'tanggal lahir',
            'jenis kelamin',
            'pekerjaan',
            'alamat korespondensi',
            'alamat rumah',
            'alamat kantor',
            'telepon',
            'handphone',
            'email ',
            'UPZ',
            'zakat',
            'catatan',
        ];
    }

    public function map($m): array
    {
        $p = $m->person;
        $tglReg = $m->tanggal_registrasi ? Carbon::parse($m->tanggal_registrasi)->format('d/m/Y') : '';
        $tglLahir = ($p && $p->tanggal_lahir) ? Carbon::parse($p->tanggal_lahir)->format('d/m/Y') : '';
        $jk = '';
        if ($p) {
            if ($p->jenis_kelamin === 'L') $jk = 'Pria';
            elseif ($p->jenis_kelamin === 'P') $jk = 'Wanita';
        }

        $kodeUpz = $m->upz?->kode_simba ?? '';

        return [
            $tglReg,
            $p?->nama ?? '',
            $m->npwp ?? '',
            $p?->nik ? (string)$p->nik : '',
            $m->nip ?? '',
            $p?->tempat_lahir ?? '',
            $tglLahir,
            $jk,
            $m->pekerjaan ?? '',
            $m->alamat_korespondensi ?? '',
            $p?->alamat ?? '',
            $m->alamat_kantor ?? '',
            $p?->telepon ?? '',
            $p?->handphone ?? '',
            $p?->email ?? '',
            $kodeUpz,
            $m->jenis_zakat ?? '',
            $m->keterangan ?? '',
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
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'M' => NumberFormat::FORMAT_TEXT,
            'N' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
