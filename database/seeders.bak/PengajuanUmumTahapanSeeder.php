<?php

namespace Database\Seeders;

use App\Models\Asnaf;
use App\Models\Dana;
use App\Models\DanaTransaksi;
use App\Models\Lampiran;
use App\Models\Mustahik;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanDisposisi;
use App\Models\PengajuanLpjBa;
use App\Models\PengajuanMustahik;
use App\Models\PengajuanPenyaluran;
use App\Models\PengajuanPersetujuan;
use App\Models\PengajuanSurvey;
use App\Models\PengajuanSurveyAsesmen;
use App\Models\PengajuanSurveyHadKifayah;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PengajuanUmumTahapanSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $allMustahiks = Mustahik::with('person')->get();
        $programs = Program::with('subprograms')->where('is_active', true)->get();
        $asnafs = Asnaf::where('is_active', true)->get();
        $allDanas = Dana::orderBy('urutan', 'asc')->get();
        $danaZakat = Dana::where('kode', '2.1')->first() ?? $allDanas->first();
        $danaInfak = Dana::where('kode', '2.6')->first() ?? $allDanas->skip(1)->first();

        if ($allMustahiks->isEmpty()) {
            $this->command?->warn('Tidak ada data Mustahik untuk seeder ini.');
            return;
        }

        // Definisi 5 skenario tahapan:
        // 1. Berhenti di Draf (Menunggu Survey)
        // 2. Berhenti di Survey (Survey Selesai, Menunggu Disposisi)
        // 3. Berhenti di Disposisi (Disposisi Selesai, Menunggu Penyaluran)
        // 4. Berhenti di Penyaluran (Penyaluran Selesai, Menunggu LPJ)
        // 5. Selesai (LPJ & BAST Lengkap)
        $tahapans = [
            [
                'step_key' => 'draf',
                'nomor_prefix' => '021',
                'judul' => 'Permohonan Bantuan Biaya Berobat Pasien Kanker (Status: Draf / Menunggu Survey)',
                'tgl_offset' => 2,
            ],
            [
                'step_key' => 'survey',
                'nomor_prefix' => '022',
                'judul' => 'Permohonan Bantuan Modal Usaha Gerobak Kuliner (Status: Survey / Menunggu Disposisi)',
                'tgl_offset' => 7,
            ],
            [
                'step_key' => 'disposisi',
                'nomor_prefix' => '023',
                'judul' => 'Permohonan Bantuan Biaya Pendidikan Tunggakan SPP (Status: Disposisi / Menunggu Penyaluran)',
                'tgl_offset' => 14,
            ],
            [
                'step_key' => 'penyaluran',
                'nomor_prefix' => '024',
                'judul' => 'Permohonan Bantuan Bedah Rumah Tidak Layak Huni (Status: Penyaluran / Menunggu LPJ)',
                'tgl_offset' => 21,
            ],
            [
                'step_key' => 'selesai',
                'nomor_prefix' => '025',
                'judul' => 'Permohonan Bantuan Kursi Roda dan Alat Bantu Difabel (Status: Selesai)',
                'tgl_offset' => 28,
            ],
        ];

        $now = Carbon::now();
        $romawi = ['1'=>'I', '2'=>'II', '3'=>'III', '4'=>'IV', '5'=>'V', '6'=>'VI', '7'=>'VII', '8'=>'VIII', '9'=>'IX', '10'=>'X', '11'=>'XI', '12'=>'XII'];
        $bulanRomawi = $romawi[$now->format('n')];
        $tahun = $now->format('Y');

        foreach ($tahapans as $idx => $tahap) {
            $stepKey = $tahap['step_key'];
            $nomorPengajuan = sprintf("%s/E-POOL/UMUM/%s/%s", $tahap['nomor_prefix'], $bulanRomawi, $tahun);
            $tanggal = $now->copy()->subDays($tahap['tgl_offset']);

            $pemohonMustahik = $allMustahiks[$idx % $allMustahiks->count()];
            $personPemohon = $pemohonMustahik->person;

            // 1. Buat Header PengajuanBantuan
            $pengajuan = PengajuanBantuan::updateOrCreate(
                ['nomor_pengajuan' => $nomorPengajuan],
                [
                    'jenis_permohonan' => 'Umum',
                    'lembaga_id' => null,
                    'pic_lembaga_id' => null,
                    'mustahik_id' => $pemohonMustahik->id,
                    'created_by' => $admin?->id,
                    'mustahik_nama_snapshot' => $personPemohon?->nama,
                    'mustahik_nik_snapshot' => $personPemohon?->nik,
                    'mustahik_kontak_snapshot' => $personPemohon?->telepon ?: $personPemohon?->handphone,
                    'mustahik_alamat_snapshot' => $personPemohon?->alamat_lengkap,
                    'judul_surat' => $tahap['judul'],
                    'nomor_surat' => sprintf("SRT/%03d/UMUM/%s", $idx + 1, $tahun),
                    'tanggal_surat' => $tanggal->copy()->subDays(3)->toDateString(),
                    'tanggal_pelaksanaan' => $tanggal->copy()->addDays(5)->toDateString(),
                    'tanggal_diterima' => $tanggal->toDateString(),
                    'jumlah_orang' => 1,
                    'jumlah_kk' => 1,
                    'jumlah_unit' => 0,
                    'is_rekomendasi' => false,
                    'uraian_permohonan' => 'Pengajuan bantuan sosial kemanusiaan masyarakat tidak mampu untuk tahapan: ' . strtoupper($stepKey),
                    'ditolak_at' => null,
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]
            );

            // Lampiran proposal/surat
            Lampiran::updateOrCreate(
                ['lampiranable_id' => $pengajuan->id, 'lampiranable_type' => PengajuanBantuan::class, 'category' => 'proposal'],
                ['name' => 'Surat Permohonan & KTP Pemohon', 'file_path' => 'lampiran/proposal_dummy.pdf']
            );

            // 2. Buat PengajuanMustahik (Penerima Manfaat)
            $prog = $programs->isNotEmpty() ? $programs[$idx % $programs->count()] : null;
            $subprog = ($prog && $prog->subprograms->isNotEmpty()) ? $prog->subprograms->first() : null;
            $asnaf = $asnafs->isNotEmpty() ? $asnafs[$idx % $asnafs->count()] : null;

            $pm = PengajuanMustahik::updateOrCreate(
                [
                    'pengajuan_id' => $pengajuan->id,
                    'mustahik_id' => $pemohonMustahik->id,
                ],
                [
                    'asnaf_id' => $asnaf?->id,
                    'program_id' => $prog?->id,
                    'subprogram_id' => $subprog?->id,
                    'keterangan' => 'Penerima manfaat bantuan ' . ($prog?->nama ?? 'Sosial Kemasyarakatan'),
                    'sumber_dana_id' => null,
                    'bentuk_bantuan' => 'Uang',
                    'nama_barang' => null,
                    'jumlah_barang' => null,
                    'satuan_barang' => null,
                    'pengadaan_barang_id' => null,
                ]
            );

            // Daftar nama surveyor acak (bukan user login / auth)
            $randomSurveyors = [
                'Bambang Setyawan, S.Sos.',
                'Ahmad Fauzi Ridwan',
                'Hendra Prasetyo, S.E.',
                'Rahmat Hidayatullah',
                'Dedi Kurniawan',
                'Siti Nur Azizah, S.Tr.Sos.',
            ];
            $assignedSurveyor = $randomSurveyors[$idx % count($randomSurveyors)];

            // ==========================================
            // TAHAP 1: DRAF (Menunggu Survey / Belum Diisi)
            // ==========================================
            if ($stepKey === 'draf') {
                PengajuanSurvey::updateOrCreate(
                    ['pengajuan_mustahik_id' => $pm->id],
                    [
                        'token' => Str::random(32),
                        'tanggal_survey' => null, // Belum disurvey
                        'hasil_akhir' => null,   // Belum diisi
                        'catatan' => null,
                        'surveyor' => null,      // Belum ada surveyor
                        'nohp_surveyor' => null,
                    ]
                );

                PengajuanDisposisi::where('pengajuan_mustahik_id', $pm->id)->delete();
                PengajuanPersetujuan::where('pengajuan_id', $pengajuan->id)->delete();
                PengajuanPenyaluran::where('pengajuan_id', $pengajuan->id)->delete();
                PengajuanLpjBa::where('pengajuan_id', $pengajuan->id)->delete();
                $pm->update(['nominal' => null, 'sumber_dana_id' => null, 'tanggal_penyaluran' => null]);
                continue;
            }

            // ==========================================
            // TAHAP 2 dst: SURVEY (Survey terisi/selesai)
            // ==========================================
            $survey = PengajuanSurvey::updateOrCreate(
                ['pengajuan_mustahik_id' => $pm->id],
                [
                    'token' => Str::random(32),
                    'tanggal_survey' => $tanggal->copy()->addDay()->toDateString(),
                    'hasil_akhir' => 'layak', // Survey selesai
                    'catatan' => 'Kondisi ekonomi mustahik masuk kategori asnaf Fakir/Miskin dan memenuhi syarat kelayakan.',
                    'surveyor' => $assignedSurveyor,
                    'nohp_surveyor' => '0812' . rand(10000000, 99999999),
                ]
            );

            // Asesmen & Had Kifayah
            PengajuanSurveyAsesmen::updateOrCreate(
                ['pengajuan_survey_id' => $survey->id],
                [
                    'nama_surveyor' => $assignedSurveyor,
                    'nama_mustahik' => $personPemohon?->nama,
                    'nik' => $personPemohon?->nik,
                    'alamat_lengkap' => $personPemohon?->alamat_lengkap,
                    'bersedia_disurvei' => 'ya',
                    'total_penghasilan_perbulan' => '800.000',
                    'kondisi_tempat_tinggal' => 'Kurang Layak',
                    'status_rumah' => 'Menumpang',
                ]
            );

            PengajuanSurveyHadKifayah::updateOrCreate(
                ['pengajuan_survey_id' => $survey->id],
                [
                    'opz' => 'BAZNAS',
                    'nama_petugas' => $assignedSurveyor,
                    'nama_mustahik' => $personPemohon?->nama,
                    'alamat_mustahik' => $personPemohon?->alamat_lengkap,
                    'status_kelayakan' => 'Sangat Layak Dibantu',
                    'kategori_prioritas' => 'Prioritas 1',
                    'pendapatan_keluarga' => 800000,
                    'pengeluaran_kk' => 1500000,
                ]
            );

            Lampiran::updateOrCreate(
                ['lampiranable_id' => $survey->id, 'lampiranable_type' => PengajuanSurvey::class, 'category' => 'foto_survey'],
                ['name' => 'Foto Observasi Lapangan Mustahik', 'file_path' => 'lampiran/survey_dummy.jpg']
            );

            if ($stepKey === 'survey') {
                PengajuanDisposisi::where('pengajuan_mustahik_id', $pm->id)->delete();
                PengajuanPersetujuan::where('pengajuan_id', $pengajuan->id)->delete();
                PengajuanPenyaluran::where('pengajuan_id', $pengajuan->id)->delete();
                PengajuanLpjBa::where('pengajuan_id', $pengajuan->id)->delete();
                $pm->update(['nominal' => null, 'sumber_dana_id' => null, 'tanggal_penyaluran' => null]);
                continue;
            }

            // ==========================================
            // TAHAP 3: DISPOSISI (Disposisi Selesai, Menunggu Penyaluran)
            // ==========================================
            $nominalDisetujui = 1500000;
            $sumberDana = $allDanas->isNotEmpty() ? $allDanas[$idx % $allDanas->count()] : $danaZakat;

            PengajuanDisposisi::updateOrCreate(
                ['pengajuan_mustahik_id' => $pm->id],
                [
                    'pimpinan_id' => $admin?->id,
                    'instruksi' => 'Proses Langsung',
                    'diteruskan_kepada' => 'Bagian Pendistribusian & Pendayagunaan',
                    'isi_disposisi' => ['Disetujui bantuan sebesar Rp ' . number_format($nominalDisetujui, 0, ',', '.')],
                    'catatan' => 'Segera siapkan berkas administrasi dan nota pencairan.',
                ]
            );

            $pm->update([
                'nominal' => $nominalDisetujui,
                'sumber_dana_id' => $sumberDana?->id,
            ]);

            PengajuanPersetujuan::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                [
                    'tanggal_persetujuan' => $tanggal->copy()->addDays(2)->toDateString(),
                    'status' => 'disetujui',
                    'nominal_disetujui' => $nominalDisetujui,
                    'referensi_dana' => [
                        [
                            'dana_id' => $sumberDana?->id,
                            'nama' => $sumberDana?->nama,
                            'nominal' => $nominalDisetujui,
                        ]
                    ],
                    'catatan' => 'Persetujuan pimpinan telah ditetapkan.',
                    'approved_by' => $admin?->id,
                ]
            );

            if ($stepKey === 'disposisi') {
                PengajuanPenyaluran::where('pengajuan_id', $pengajuan->id)->delete();
                PengajuanLpjBa::where('pengajuan_id', $pengajuan->id)->delete();
                $pm->update(['tanggal_penyaluran' => null]);
                continue;
            }

            // ==========================================
            // TAHAP 4: PENYALURAN (Penyaluran Selesai, Menunggu LPJ)
            // ==========================================
            $tglSalur = $tanggal->copy()->addDays(3);
            $pm->update([
                'tanggal_penyaluran' => $tglSalur->toDateString(),
                'metode_penyaluran' => 'Transfer',
                'catatan_penyaluran' => 'Bantuan ditransfer ke rekening mustahik.',
            ]);

            PengajuanPenyaluran::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                [
                    'tanggal_penyaluran' => $tglSalur->toDateString(),
                    'nominal_penyaluran' => $nominalDisetujui,
                    'metode_penyaluran' => 'Transfer',
                    'catatan' => 'Dana bantuan telah ditransfer via bank.',
                    'created_by' => $admin?->id,
                ]
            );

            Lampiran::updateOrCreate(
                ['lampiranable_id' => $pm->id, 'lampiranable_type' => PengajuanMustahik::class, 'category' => 'bukti_penyaluran'],
                ['name' => 'Struk Transfer Bank Penyaluran', 'file_path' => 'lampiran/bukti_transfer_dummy.jpg']
            );

            if ($sumberDana) {
                DanaTransaksi::updateOrCreate(
                    [
                        'referensi_type' => PengajuanMustahik::class,
                        'referensi_id'   => $pm->id,
                        'dana_id'        => $sumberDana->id,
                    ],
                    [
                        'tanggal'    => $tglSalur->toDateString(),
                        'jenis'      => 'Keluar',
                        'nominal'    => $nominalDisetujui,
                        'uraian'     => 'Penyaluran Bantuan No. ' . $pengajuan->nomor_pengajuan . ' - ' . ($personPemohon?->nama ?? 'Mustahik'),
                        'created_at' => $tglSalur,
                        'updated_at' => $tglSalur,
                    ]
                );
            }

            if ($stepKey === 'penyaluran') {
                PengajuanLpjBa::where('pengajuan_id', $pengajuan->id)->delete();
                continue;
            }

            // ==========================================
            // TAHAP 5: SELESAI (LPJ & BAST Lengkap)
            // ==========================================
            $tglLpj = $tanggal->copy()->addDays(7);
            $lpj = PengajuanLpjBa::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                [
                    'tanggal_lpj' => $tglLpj->toDateString(),
                    'nominal_realisasi' => $nominalDisetujui,
                    'nominal_sisa' => 0,
                    'sisa_dikembalikan' => false,
                    'catatan' => 'LPJ, kwitansi, dan Berita Acara Serah Terima (BAST) telah lengkap dan diverifikasi.',
                    'created_by' => $admin?->id,
                ]
            );

            Lampiran::updateOrCreate(
                ['lampiranable_id' => $lpj->id, 'lampiranable_type' => PengajuanLpjBa::class, 'category' => 'dokumentasi'],
                ['name' => 'BAST & Foto Penyerahan Bantuan', 'file_path' => 'lampiran/lpj_dummy.pdf']
            );
        }

        $this->command?->info('Berhasil membuat 5 data seeder Pengajuan Umum untuk setiap tahapan (Draf, Survey, Disposisi, Penyaluran, Selesai).');
    }
}
