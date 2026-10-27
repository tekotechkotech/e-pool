<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\Program;
use App\Models\Subprogram;

class PengajuanBantuanImportTemplate implements WithHeadings, WithTitle, WithEvents, ShouldAutoSize
{
    protected $jenis;

    public function __construct($jenis = 'Individu')
    {
        $this->jenis = $jenis;
    }

    public function headings(): array
    {
        $base = [
            $this->jenis === 'Individu' ? 'NIK Pemohon' : 'Nama Lembaga / UPZ',
            $this->jenis === 'Individu' ? 'Nama Lengkap' : 'Nama Ketua Lembaga',
            'Nama Program',
            'Nama Sub Program',
            'Judul Surat',
            'Tanggal Surat (YYYY-MM-DD)',
            'Tanggal Diterima (YYYY-MM-DD)',
            'Nominal Diajukan (Angka)', // H
            'Status Pengajuan', // I
            'Instruksi Disposisi', // J
            'Catatan Disposisi', // K
            'Surveyor', // L
            'Tanggal Survey (YYYY-MM-DD)', // M
            'Kelayakan Survey', // N
            'Catatan Survey', // O
            'Status Persetujuan', // P
            'Nominal Disetujui (Angka)', // Q
            'Catatan Persetujuan', // R
            'Disetujui Oleh (Email/Nama)', // S
            'Tanggal Persetujuan (YYYY-MM-DD)', // T
            'Tanggal Penyaluran (YYYY-MM-DD)', // U
            'Nominal Penyaluran (Angka)', // V
            'Metode Penyaluran', // W
            'Catatan Penyaluran', // X
            'Yang Menyalurkan (Email/Nama)', // Y
            'Tanggal LPJ (YYYY-MM-DD)', // Z
            'Nominal Realisasi (Angka)', // AA
            'Nominal Sisa (Angka)', // AB
            'Sisa Dikembalikan (Ya/Tidak)', // AC
            'Catatan LPJ' // AD
        ];

        return $base;
    }

    public function title(): string
    {
        return 'Import ' . $this->jenis;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $workbook = $sheet->getParent();

                // 1. Style Header
                $sheet->getStyle('A1:AD1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FF118243',
                        ],
                    ],
                ]);

                // 2. Set Hidden Metadata at AE1 instead of Z1 (karena Z sekarang dipakai)
                $sheet->setCellValue('AE1', 'TEMPLATE_TYPE=' . $this->jenis);
                $sheet->getColumnDimension('AE')->setVisible(false);

                // 3. Create Hidden Sheet for Dropdowns
                $dropdownSheet = $workbook->createSheet();
                $dropdownSheet->setTitle('DataDropdowns');
                $dropdownSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

                // Populate Programs
                $programs = Program::pluck('nama')->toArray();
                foreach($programs as $idx => $prog) {
                    $dropdownSheet->setCellValue('A'.($idx+1), $prog);
                }
                // Populate Subprograms (for Dependent Dropdown)
                // Kita letakkan di K (Program) dan L (Subprogram) agar bisa pakai formula OFFSET & MATCH
                $subprogramsList = Subprogram::with('program')->get()->sortBy('program.nama');
                $subIdx = 1;
                foreach($subprogramsList as $sub) {
                    if($sub->program) {
                        $dropdownSheet->setCellValue('K'.$subIdx, $sub->program->nama);
                        $dropdownSheet->setCellValue('L'.$subIdx, $sub->nama);
                        $subIdx++;
                    }
                }
                
                // Static Enums
                $statuses = ['draf','permohonan','disposisi','survey','disetujui','penyaluran','lpj_ba','selesai','ditolak'];
                foreach($statuses as $idx => $s) { $dropdownSheet->setCellValue('C'.($idx+1), $s); }
                
                $instruksi = ['Perlu Survey','Proses Langsung','Revisi','Tolak'];
                foreach($instruksi as $idx => $s) { $dropdownSheet->setCellValue('D'.($idx+1), $s); }

                $kelayakan = ['layak','tidak layak'];
                foreach($kelayakan as $idx => $s) { $dropdownSheet->setCellValue('E'.($idx+1), $s); }

                $statusSetuju = ['disetujui','ditolak','tidak_dicairkan'];
                foreach($statusSetuju as $idx => $s) { $dropdownSheet->setCellValue('F'.($idx+1), $s); }

                $metodePenyaluran = ['Tunai','Transfer','Barang'];
                foreach($metodePenyaluran as $idx => $s) { $dropdownSheet->setCellValue('G'.($idx+1), $s); }

                $sisaDikembalikan = ['Ya','Tidak'];
                foreach($sisaDikembalikan as $idx => $s) { $dropdownSheet->setCellValue('H'.($idx+1), $s); }


                // 4. Set Data Validations
                $rowCount = 1000;

                // Function to create list validation
                $createValidation = function($formula) {
                    $validation = new DataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1($formula);
                    return $validation;
                };

                for ($i = 2; $i <= $rowCount; $i++) {
                    $validations = [
                        'C' => $createValidation('DataDropdowns!$A$1:$A$'.count($programs)),
                        // Dependent Dropdown formula for Subprogram (D) based on Program (C)
                        'D' => $createValidation('=OFFSET(DataDropdowns!$L$1, MATCH($C'.$i.', DataDropdowns!$K$1:$K$1000, 0)-1, 0, COUNTIF(DataDropdowns!$K$1:$K$1000, $C'.$i.'), 1)'),
                        'I' => $createValidation('DataDropdowns!$C$1:$C$'.count($statuses)),
                        'J' => $createValidation('DataDropdowns!$D$1:$D$'.count($instruksi)),
                        'N' => $createValidation('DataDropdowns!$E$1:$E$'.count($kelayakan)),
                        'P' => $createValidation('DataDropdowns!$F$1:$F$'.count($statusSetuju)),
                        'W' => $createValidation('DataDropdowns!$G$1:$G$'.count($metodePenyaluran)),
                        'AC' => $createValidation('DataDropdowns!$H$1:$H$'.count($sisaDikembalikan)),
                    ];

                    foreach($validations as $col => $val) {
                        $sheet->getCell($col.$i)->setDataValidation(clone $val);
                    }
                }

                // Focus back to main sheet
                $workbook->setActiveSheetIndex(0);
            },
        ];
    }
}
