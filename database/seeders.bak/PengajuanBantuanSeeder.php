<?php

namespace Database\Seeders;

use App\Models\Asnaf;
use App\Models\Dana;
use App\Models\DanaTransaksi;
use App\Models\Lampiran;
use App\Models\Mustahik;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanLpjBa;
use App\Models\PengajuanMustahik;
use App\Models\PengajuanPenyaluran;
use App\Models\PengajuanPersetujuan;
use App\Models\PengajuanSurvey;
use App\Models\PengajuanDisposisi;
use App\Models\Program;
use App\Models\Subprogram;
use App\Models\Lembaga;
use App\Models\Pengurus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PengajuanBantuanSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        $lembagas = Lembaga::query()->take(3)->get();
        $allMustahiks = Mustahik::all();
        $programs = Program::with('subprograms')->where('is_active', true)->get();
        $asnafs = Asnaf::where('is_active', true)->get();
        $allDanas = Dana::orderBy('urutan', 'asc')->get();
        $danaZakat = Dana::where('kode', '2.1')->first() ?? $allDanas->first();
        $danaInfak = Dana::where('kode', '2.6')->first() ?? $allDanas->skip(1)->first();

        $startDate = Carbon::now()->subMonths(3)->startOfWeek();

        for ($i = 1; $i <= 12; $i++) {
            $tanggal = $startDate->copy()->addWeeks($i);

            $lembaga = $lembagas[($i - 1) % max($lembagas->count(), 1)] ?? null;

            $pic = $lembaga
                ? Pengurus::where('lembaga_id', $lembaga->id)
                    ->where('jenis_pengurus', 'Lembaga')
                    ->where('is_active', true)
                    ->first()
                : null;

            $status = match ($i) {
                1, 2, 3, 4 => 'selesai',
                5, 6 => 'lpj_ba',
                7, 8 => 'pencairan',
                9 => 'disposisi',
                10 => 'survey',
                11 => 'verifikasi',
                12 => 'ditolak',
            };

            $jenisPermohonan = $i % 3 === 0 ? 'Umum' : 'Lembaga';
            $jenisStr = ($jenisPermohonan === 'Umum' || $jenisPermohonan === 'Individu') ? 'UMUM' : 'UPZ';
            $romawi = ['1'=>'I', '2'=>'II', '3'=>'III', '4'=>'IV', '5'=>'V', '6'=>'VI', '7'=>'VII', '8'=>'VIII', '9'=>'IX', '10'=>'X', '11'=>'XI', '12'=>'XII'];
            $bulanRomawi = $romawi[$tanggal->format('n')];
            $prefix = "E-POOL/{$jenisStr}/{$bulanRomawi}/{$tanggal->format('Y')}";
            $nomorPengajuan = sprintf("%03d/%s", $i, $prefix);

            // Tentukan mustahik pemohon awal (jika umum)
            $pemohonMustahik = $allMustahiks[($i * 3) % max($allMustahiks->count(), 1)] ?? null;

            $pengajuan = PengajuanBantuan::updateOrCreate(
                [
                    'nomor_pengajuan' => $nomorPengajuan,
                ],
                [
                    'jenis_permohonan' => $jenisPermohonan,

                    'lembaga_id' => $jenisPermohonan === 'Umum' ? null : $lembaga?->id,
                    'pic_lembaga_id' => $jenisPermohonan === 'Umum' ? null : $pic?->id,
                    'mustahik_id' => $pemohonMustahik?->id,

                    'created_by' => $admin?->id,

                    'lembaga_nama_snapshot' => $lembaga?->nama,
                    'lembaga_kontak_snapshot' => $lembaga?->handphone,
                    'lembaga_alamat_snapshot' => $lembaga?->alamat,

                    'pic_nama_snapshot' => $pic?->person?->nama,
                    'pic_kontak_snapshot' => $pic?->person?->handphone,

                    'judul_surat' => 'Permohonan Bantuan Sosial Minggu Ke-' . $i,
                    'nomor_surat' => '00' . $i . '/UPZ/VI/2026',
                    'tanggal_surat' => $tanggal->copy()->subDays(2)->toDateString(),
                    'tanggal_pelaksanaan' => $tanggal->copy()->addDays(3)->toDateString(),
                    'tanggal_diterima' => $tanggal->toDateString(),

                    'jumlah_orang' => rand(2, 5),
                    'jumlah_kk' => rand(2, 4),
                    'jumlah_unit' => 0,

                    'is_rekomendasi' => false,
                    'uraian_permohonan' => 'Pengajuan bantuan rutin mingguan berdasarkan praktik operasional 3 bulan terakhir.',

                    'ditolak_at' => $status === 'ditolak' ? $tanggal->copy()->addDays(5) : null,
                ]
            );

            Lampiran::updateOrCreate(
                ['lampiranable_id' => $pengajuan->id, 'lampiranable_type' => PengajuanBantuan::class, 'category' => 'proposal'],
                ['name' => 'Proposal Pengajuan', 'file_path' => 'lampiran/proposal_dummy.pdf']
            );
            Lampiran::updateOrCreate(
                ['lampiranable_id' => $pengajuan->id, 'lampiranable_type' => PengajuanBantuan::class, 'category' => 'ktp'],
                ['name' => 'KTP Pengaju', 'file_path' => 'lampiran/ktp_dummy.jpg']
            );

            // 1. Mustahik Pengajuan (Minimal 2, Maksimal 5 mustahik per pengajuan)
            $mustahikCount = rand(2, 5);
            $selectedMustahiks = $allMustahiks->slice((($i - 1) * 3) % max($allMustahiks->count() - 5, 1), $mustahikCount);
            if ($selectedMustahiks->count() < 2) {
                $selectedMustahiks = $allMustahiks->take($mustahikCount);
            }

            $pms = [];
            foreach ($selectedMustahiks as $idx => $m) {
                $prog = $programs->isNotEmpty() ? $programs->random() : null;
                $subprog = ($prog && $prog->subprograms->isNotEmpty() && rand(0, 1) === 1) 
                    ? $prog->subprograms->random() 
                    : null;
                $asnaf = $asnafs->isNotEmpty() ? $asnafs->random() : null;

                $pm = PengajuanMustahik::updateOrCreate(
                    [
                        'pengajuan_id' => $pengajuan->id,
                        'mustahik_id' => $m->id,
                    ],
                    [
                        'asnaf_id' => $asnaf?->id,
                        'program_id' => $prog?->id,
                        'subprogram_id' => $subprog?->id,
                        'keterangan' => 'Penerima manfaat bantuan ' . ($prog?->nama ?? 'Baznas') . ' #' . ($idx + 1),
                        'sumber_dana_id' => null,
                        'bentuk_bantuan' => 'Uang',
                        'nama_barang' => null,
                        'jumlah_barang' => null,
                        'satuan_barang' => null,
                        'pengadaan_barang_id' => null,
                    ]
                );
                $pms[] = $pm;
            }

            // Update jumlah_orang snapshot
            $pengajuan->update(['jumlah_orang' => count($pms)]);

            // 2. Survey
            $randomSurveyorList = [
                'Bambang Setyawan, S.Sos.',
                'Ahmad Fauzi Ridwan',
                'Hendra Prasetyo, S.E.',
                'Rahmat Hidayatullah',
                'Dedi Kurniawan',
                'Siti Nur Azizah, S.Tr.Sos.',
            ];
            $surveyorName = $randomSurveyorList[($i - 1) % count($randomSurveyorList)];

            if (in_array($status, ['survey', 'disposisi', 'pencairan', 'lpj_ba', 'selesai', 'ditolak'])) {
                foreach ($pms as $pm) {
                    $isSurveyBelumSelesai = ($status === 'survey'); // Masih dalam proses survey
                    $survey = PengajuanSurvey::updateOrCreate(
                        ['pengajuan_mustahik_id' => $pm->id],
                        [
                            'tanggal_survey' => $isSurveyBelumSelesai ? null : $tanggal->copy()->addDays(1)->toDateString(),
                            'hasil_akhir' => $status === 'ditolak' ? 'tidak layak' : ($isSurveyBelumSelesai ? null : 'layak'),
                            'catatan' => $status === 'ditolak'
                                ? 'Belum memenuhi kriteria kelayakan.'
                                : ($isSurveyBelumSelesai ? null : 'Layak dibantu berdasarkan hasil survey.'),
                            'surveyor' => $isSurveyBelumSelesai ? null : $surveyorName,
                            'nohp_surveyor' => $isSurveyBelumSelesai ? null : '081234567890',
                        ]
                    );

                    if (!$isSurveyBelumSelesai) {
                        Lampiran::updateOrCreate(
                            ['lampiranable_id' => $survey->id, 'lampiranable_type' => PengajuanSurvey::class, 'category' => 'foto_survey'],
                            ['name' => 'Foto Dokumentasi Survey', 'file_path' => 'lampiran/survey_dummy.jpg']
                        );
                    }
                }
            }

            // 3. Disposisi
            $totalDisposisiNominal = 0;
            if (in_array($status, ['disposisi', 'pencairan', 'lpj_ba', 'selesai', 'ditolak'])) {
                foreach ($pms as $pm) {
                    if ($status === 'ditolak') {
                        $instruksiDisposisi = 'Tolak';
                        $pmNominal = 0;
                        $pmSumberDanaId = null;
                    } else {
                        $instruksiDisposisi = ($status === 'survey') ? 'Perlu Survey' : 'Proses Langsung';
                        $pmNominal = rand(5, 12) * 100000; // 500rb - 1.2jt per mustahik
                        $pmSumberDanaId = $allDanas->isNotEmpty() ? $allDanas[($i + $idx) % $allDanas->count()]->id : null;
                    }

                    PengajuanDisposisi::updateOrCreate(
                        ['pengajuan_mustahik_id' => $pm->id],
                        [
                            'pimpinan_id' => $admin?->id,
                            'instruksi' => $instruksiDisposisi,
                            'catatan' => $status === 'ditolak' ? 'Ditolak pimpinan' : 'Disposisi pimpinan untuk realisasi bantuan.',
                        ]
                    );

                    $pm->update([
                        'nominal' => $pmNominal,
                        'sumber_dana_id' => $pmSumberDanaId,
                    ]);

                    $totalDisposisiNominal += $pmNominal;
                }
            }

            // 4. Persetujuan Pimpinan
            if (in_array($status, ['disposisi', 'pencairan', 'lpj_ba', 'selesai', 'ditolak'])) {
                $nominalPersetujuan = ($status === 'ditolak') ? 0 : $totalDisposisiNominal;

                $persetujuan = PengajuanPersetujuan::updateOrCreate(
                    ['pengajuan_id' => $pengajuan->id],
                    [
                        'tanggal_persetujuan' => $tanggal->copy()->addDays(2)->toDateString(),
                        'status' => $status === 'ditolak' ? 'ditolak' : 'disetujui',
                        'nominal_disetujui' => $nominalPersetujuan,
                        'referensi_dana' => json_encode([
                            [
                                'dana_id' => $danaZakat?->id,
                                'nama' => $danaZakat?->nama,
                                'nominal' => $nominalPersetujuan * 0.7,
                            ],
                            [
                                'dana_id' => $danaInfak?->id,
                                'nama' => $danaInfak?->nama,
                                'nominal' => $nominalPersetujuan * 0.3,
                            ],
                        ]),
                        'catatan' => $status === 'ditolak' ? 'Pengajuan ditolak.' : 'Pengajuan disetujui.',
                        'approved_by' => $admin?->id,
                    ]
                );

                if ($persetujuan->status === 'disetujui' && $nominalPersetujuan > 0) {
                    $daftarReferensiDana = json_decode($persetujuan->referensi_dana, true);

                    foreach ($daftarReferensiDana as $itemDana) {
                        if (!empty($itemDana['dana_id']) && $itemDana['nominal'] > 0) {
                            DanaTransaksi::updateOrInsert(
                                [
                                    'referensi_type' => PengajuanPersetujuan::class,
                                    'referensi_id'   => $persetujuan->id,
                                    'dana_id'        => $itemDana['dana_id'],
                                ],
                                [
                                    'tanggal'    => $persetujuan->tanggal_persetujuan,
                                    'jenis'      => 'Keluar',
                                    'nominal'    => $itemDana['nominal'],
                                    'uraian'     => 'Penyaluran bantuan ' . $pengajuan->nomor_pengajuan . ' via ' . ($itemDana['nama'] ?? 'Sumber Dana'),
                                    'created_at' => \Carbon\Carbon::now(),
                                    'updated_at' => \Carbon\Carbon::now(),
                                ]
                            );
                        }
                    }
                } else {
                    DanaTransaksi::query()
                        ->where('referensi_type', PengajuanPersetujuan::class)
                        ->where('referensi_id', $persetujuan->id)
                        ->delete();
                }
            }

            // 5. Penyaluran (Per Mustahik)
            if (in_array($status, ['pencairan', 'lpj_ba', 'selesai'])) {
                foreach ($pms as $pm) {
                    if (($pm->nominal ?? 0) > 0) {
                        $pm->update([
                            'tanggal_penyaluran' => $tanggal->copy()->addDays(4)->toDateString(),
                            'metode_penyaluran' => $i % 2 === 0 ? 'Transfer' : 'Tunai',
                            'catatan_penyaluran' => 'Dana telah disalurkan kepada penerima manfaat.',
                        ]);

                        Lampiran::updateOrCreate(
                            [
                                'lampiranable_id' => $pm->id, 
                                'lampiranable_type' => PengajuanMustahik::class, 
                                'category' => 'bukti_penyaluran'
                            ],
                            [
                                'name' => 'Bukti Penyaluran - ' . ($pm->mustahik?->person?->nama ?? $pm->mustahik?->lembaga?->nama ?? 'Mustahik'), 
                                'file_path' => 'lampiran/bukti_transfer_dummy.jpg'
                            ]
                        );

                        if ($pm->sumber_dana_id) {
                            $mNama = $pm->mustahik?->person?->nama ?? $pm->mustahik?->lembaga?->nama ?? 'Mustahik';
                            DanaTransaksi::updateOrCreate(
                                [
                                    'referensi_type' => PengajuanMustahik::class,
                                    'referensi_id'   => $pm->id,
                                    'dana_id'        => $pm->sumber_dana_id,
                                ],
                                [
                                    'tanggal'    => $pm->tanggal_penyaluran,
                                    'jenis'      => 'Keluar',
                                    'nominal'    => $pm->nominal,
                                    'uraian'     => 'Penyaluran Bantuan No. ' . $pengajuan->nomor_pengajuan . ' - Mustahik: ' . $mNama,
                                    'created_at' => \Carbon\Carbon::now(),
                                    'updated_at' => \Carbon\Carbon::now(),
                                ]
                            );
                        }
                    }
                }
            }

            // 6. LPJ
            if (in_array($status, ['lpj_ba', 'selesai'])) {
                $lpj = PengajuanLpjBa::updateOrCreate(
                    ['pengajuan_id' => $pengajuan->id],
                    [
                        'tanggal_lpj' => $tanggal->copy()->addDays(7)->toDateString(),
                        'nominal_realisasi' => $totalDisposisiNominal,
                        'nominal_sisa' => 0,
                        'catatan' => 'LPJ dan BA sudah diterima.',
                        'created_by' => $admin?->id,
                    ]
                );

                Lampiran::updateOrCreate(
                    ['lampiranable_id' => $lpj->id, 'lampiranable_type' => PengajuanLpjBa::class, 'category' => 'laporan_kegiatan'],
                    ['name' => 'Laporan Kegiatan dan BA', 'file_path' => 'lampiran/lpj_dummy.pdf']
                );
            }
        }
    }
}
