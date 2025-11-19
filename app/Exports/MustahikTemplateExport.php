<?php

namespace App\Exports;

use App\Models\Asnaf;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MustahikTemplateExport implements WithHeadings, WithEvents, WithStyles
{
    public function headings(): array
    {
        return [
            'NIK (16 Digit)',
            'Nomor KK (16 Digit)',
            'Nama Lengkap',
            'Jenis Kelamin (L/P)',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Pekerjaan',
            'Telepon / HP',
            'Alamat Lengkap',
            'Kategori Asnaf',
            'Keterangan Bantuan',
            'Jumlah Jiwa',
            'Jumlah KK',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $asnafs = Asnaf::where('is_active', true)->pluck('nama')->toArray();
                $asnafList = implode(',', $asnafs);

                // Set validation for Jenis Kelamin (D2:D1000)
                $validationGender = $sheet->getCell('D2')->getDataValidation();
                $validationGender->setType(DataValidation::TYPE_LIST);
                $validationGender->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validationGender->setAllowBlank(false);
                $validationGender->setShowInputMessage(true);
                $validationGender->setShowErrorMessage(true);
                $validationGender->setShowDropDown(true);
                $validationGender->setErrorTitle('Input Error');
                $validationGender->setError('Pilih L atau P');
                $validationGender->setPromptTitle('Pilih Jenis Kelamin');
                $validationGender->setPrompt('Pilih L atau P dari dropdown.');
                $validationGender->setFormula1('"L,P"');
                for ($i = 2; $i <= 1000; $i++) {
                    $sheet->getCell("D{$i}")->setDataValidation(clone $validationGender);
                }

                // Set validation for Kategori Asnaf (J2:J1000)
                $validationAsnaf = $sheet->getCell('J2')->getDataValidation();
                $validationAsnaf->setType(DataValidation::TYPE_LIST);
                $validationAsnaf->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validationAsnaf->setAllowBlank(false);
                $validationAsnaf->setShowInputMessage(true);
                $validationAsnaf->setShowErrorMessage(true);
                $validationAsnaf->setShowDropDown(true);
                $validationAsnaf->setErrorTitle('Input Error');
                $validationAsnaf->setError('Pilih kategori asnaf dari list.');
                $validationAsnaf->setPromptTitle('Pilih Asnaf');
                $validationAsnaf->setPrompt('Pilih Asnaf dari dropdown.');
                $validationAsnaf->setFormula1('"' . $asnafList . '"');
                for ($i = 2; $i <= 1000; $i++) {
                    $sheet->getCell("J{$i}")->setDataValidation(clone $validationAsnaf);
                }

                // Auto size columns
                foreach (range('A', 'K') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
