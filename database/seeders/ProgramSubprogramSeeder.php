<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Program;
use App\Models\Subprogram;

class ProgramSubprogramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            ['old_id' => '2a700a8d-dd49-46d3-9e25-2953266cf9a5', 'nama' => 'Cilacap Cerdas'],
            ['old_id' => '30746c18-3f7a-4736-ae47-ea91154a5a00', 'nama' => 'Cilacap Makmur'],
            ['old_id' => '9e2ea277-9550-4ff7-bd6a-5fb36ef30633', 'nama' => 'Cilacap Peduli'],
            ['old_id' => 'ce2ac72c-02bc-4d8c-b143-9d526b1edd2b', 'nama' => 'Cilacap Sehat'],
            ['old_id' => 'd578e2e4-23d4-4cc6-9657-2415ba633420', 'nama' => 'Cilacap Taqwa'],
            ['old_id' => 'e47c6712-98b5-42b9-9b47-22f7b843745f', 'nama' => 'Layanan Aktif Baznas'],
            ['old_id' => 'e47c6722-98b5-42b9-9b37-22f7b8437450', 'nama' => 'Baznas Peduli Bencana']
        ];
$subprograms = [
    ['old_program_id' => '30746c18-3f7a-4736-ae47-ea91154a5a00', 'nama' => 'Pengembangan Usaha dan Pemasaran', 'jenis' => 'Pendayagunaan', 'sort_order' => 1],
    ['old_program_id' => '9e2ea277-9550-4ff7-bd6a-5fb36ef30633', 'nama' => 'Bantuan Sosial', 'jenis' => 'Pendistribusian', 'sort_order' => 3],
    ['old_program_id' => '9e2ea277-9550-4ff7-bd6a-5fb36ef30633', 'nama' => 'Bahagia Bersama Adik Juara', 'jenis' => 'Pendistribusian', 'sort_order' => 8],
    ['old_program_id' => 'd578e2e4-23d4-4cc6-9657-2415ba633420', 'nama' => 'Bina Dakwah', 'jenis' => 'Pendayagunaan', 'sort_order' => 1],
    ['old_program_id' => 'ce2ac72c-02bc-4d8c-b143-9d526b1edd2b', 'nama' => 'Sarana Kesehatan', 'jenis' => 'Pendayagunaan', 'sort_order' => 2],
    ['old_program_id' => '30746c18-3f7a-4736-ae47-ea91154a5a00', 'nama' => 'Pelatihan Keterampilan Kerja/Usaha', 'jenis' => 'Pendayagunaan', 'sort_order' => 3],
    ['old_program_id' => '9e2ea277-9550-4ff7-bd6a-5fb36ef30633', 'nama' => 'Paket Sembako', 'jenis' => 'Pendistribusian', 'sort_order' => 4],
    ['old_program_id' => 'ce2ac72c-02bc-4d8c-b143-9d526b1edd2b', 'nama' => 'Klinik Keliling (KLIK)', 'jenis' => 'Pendayagunaan', 'sort_order' => 5],
    ['old_program_id' => '30746c18-3f7a-4736-ae47-ea91154a5a00', 'nama' => 'Rukun Tani', 'jenis' => 'Pendayagunaan', 'sort_order' => 5],
    ['old_program_id' => '30746c18-3f7a-4736-ae47-ea91154a5a00', 'nama' => 'Rukun Ternak', 'jenis' => 'Pendayagunaan', 'sort_order' => 4],
    ['old_program_id' => '2a700a8d-dd49-46d3-9e25-2953266cf9a5', 'nama' => 'Qur’an Center Baznas Cilacap (QCBC)', 'jenis' => 'Pendayagunaan', 'sort_order' => 3],
    ['old_program_id' => '9e2ea277-9550-4ff7-bd6a-5fb36ef30633', 'nama' => 'Rumah Layak Huni BAZNAS', 'jenis' => 'Pendayagunaan', 'sort_order' => 1],
    ['old_program_id' => '9e2ea277-9550-4ff7-bd6a-5fb36ef30633', 'nama' => 'Biaya Hidup', 'jenis' => 'Pendistribusian', 'sort_order' => 2],
    ['old_program_id' => 'ce2ac72c-02bc-4d8c-b143-9d526b1edd2b', 'nama' => 'Air dan Sanitasi', 'jenis' => 'Pendayagunaan', 'sort_order' => 7],
    ['old_program_id' => '2a700a8d-dd49-46d3-9e25-2953266cf9a5', 'nama' => 'Bantuan Infrastruktur Pendidikan', 'jenis' => 'Pendayagunaan', 'sort_order' => 2],
    ['old_program_id' => '2a700a8d-dd49-46d3-9e25-2953266cf9a5', 'nama' => 'Beasiswa Qur’ani', 'jenis' => 'Pendayagunaan', 'sort_order' => 1],
    ['old_program_id' => '2a700a8d-dd49-46d3-9e25-2953266cf9a5', 'nama' => 'Beasiswa Menengah (SMA/MA)', 'jenis' => 'Pendistribusian', 'sort_order' => 6],
    ['old_program_id' => '2a700a8d-dd49-46d3-9e25-2953266cf9a5', 'nama' => 'Beasiswa Prestasi', 'jenis' => 'Pendistribusian', 'sort_order' => 8],
    ['old_program_id' => '9e2ea277-9550-4ff7-bd6a-5fb36ef30633', 'nama' => 'Respon Kebencanaan', 'jenis' => 'Pendistribusian', 'sort_order' => 5],
    ['old_program_id' => 'd578e2e4-23d4-4cc6-9657-2415ba633420', 'nama' => 'Syiar Dakwah', 'jenis' => 'Pendistribusian', 'sort_order' => 4],
    ['old_program_id' => 'ce2ac72c-02bc-4d8c-b143-9d526b1edd2b', 'nama' => 'Biaya Pengobatan', 'jenis' => 'Pendistribusian', 'sort_order' => 1],
    ['old_program_id' => '9e2ea277-9550-4ff7-bd6a-5fb36ef30633', 'nama' => 'Penanggulangan Resiko Bencana', 'jenis' => 'Pendayagunaan', 'sort_order' => 9],
    ['old_program_id' => '2a700a8d-dd49-46d3-9e25-2953266cf9a5', 'nama' => 'Beasiswa Santri', 'jenis' => 'Pendistribusian', 'sort_order' => 7],
    ['old_program_id' => '2a700a8d-dd49-46d3-9e25-2953266cf9a5', 'nama' => 'Beasiswa Tinggi', 'jenis' => 'Pendistribusian', 'sort_order' => 4],
    ['old_program_id' => 'd578e2e4-23d4-4cc6-9657-2415ba633420', 'nama' => 'Cahaya Ilmu', 'jenis' => 'Pendistribusian', 'sort_order' => 5],
    ['old_program_id' => '2a700a8d-dd49-46d3-9e25-2953266cf9a5', 'nama' => 'Beasiswa Dasar (SD/MI, SMP, MTs)', 'jenis' => 'Pendistribusian', 'sort_order' => 5],
    ['old_program_id' => '30746c18-3f7a-4736-ae47-ea91154a5a00', 'nama' => 'Kewirausahaan Santri (Santripreneur)', 'jenis' => 'Pendayagunaan', 'sort_order' => 7],
    ['old_program_id' => 'ce2ac72c-02bc-4d8c-b143-9d526b1edd2b', 'nama' => 'Kerjasama Penanggulangan Penyakit', 'jenis' => 'Pendayagunaan', 'sort_order' => 6],
    ['old_program_id' => '9e2ea277-9550-4ff7-bd6a-5fb36ef30633', 'nama' => 'Kurban', 'jenis' => 'Pendistribusian', 'sort_order' => 7],
    ['old_program_id' => 'd578e2e4-23d4-4cc6-9657-2415ba633420', 'nama' => 'Cahaya Masjid', 'jenis' => 'Pendistribusian', 'sort_order' => 2],
    ['old_program_id' => 'ce2ac72c-02bc-4d8c-b143-9d526b1edd2b', 'nama' => 'Rumah Layak Sehat BAZNAS', 'jenis' => 'Pendayagunaan', 'sort_order' => 8],
    ['old_program_id' => 'd578e2e4-23d4-4cc6-9657-2415ba633420', 'nama' => 'Mualaf Center', 'jenis' => 'Pendayagunaan', 'sort_order' => 3],
    ['old_program_id' => '30746c18-3f7a-4736-ae47-ea91154a5a00', 'nama' => 'Warung Baznas', 'jenis' => 'Pendayagunaan', 'sort_order' => 6],
    ['old_program_id' => '30746c18-3f7a-4736-ae47-ea91154a5a00', 'nama' => 'Modal Usaha', 'jenis' => 'Pendayagunaan', 'sort_order' => 2],
    ['old_program_id' => '30746c18-3f7a-4736-ae47-ea91154a5a00', 'nama' => 'Rukun Nelayan', 'jenis' => 'Pendayagunaan', 'sort_order' => 6],
    ['old_program_id' => 'ce2ac72c-02bc-4d8c-b143-9d526b1edd2b', 'nama' => 'Jaminan Kesehatan', 'jenis' => 'Pendistribusian', 'sort_order' => 3],
    ['old_program_id' => 'ce2ac72c-02bc-4d8c-b143-9d526b1edd2b', 'nama' => 'Ambulan Baznas', 'jenis' => 'Pendistribusian', 'sort_order' => 4],
];

        DB::transaction(function () use ($programs, $subprograms) {
            $programMap = [];
            foreach ($programs as $index => $progData) {
                $program = Program::firstOrCreate(
                    ['nama' => $progData['nama']],
                    ['sort_order' => $index + 1, 'is_active' => true]
                );
                $programMap[$progData['old_id']] = $program->id;
            }

            foreach ($subprograms as $index => $subData) {
                if (isset($programMap[$subData['old_program_id']])) {
                    Subprogram::firstOrCreate(
                        [
                            'program_id' => $programMap[$subData['old_program_id']],
                            'nama' => $subData['nama'],
                        ],
                        [
                            'jenis' => $subData['jenis'],
                            'sort_order' => $subData['sort_order'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        });
    }
}
