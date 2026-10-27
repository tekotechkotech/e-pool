<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengajuanSurvey;
use App\Models\PengajuanSurveyHadKifayah;
use App\Models\PengajuanSurveyAsesmen;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PublicSurveyController extends Controller
{
    public function show($token)
    {
        $survey = PengajuanSurvey::with([
            'pengajuanMustahik.mustahik.person',
            'pengajuanMustahik.program',
            'pengajuanMustahik.subprogram',
            'pengajuanMustahik.pengajuan.persetujuan',
            'pengajuanMustahik.disposisi',
            'hadKifayah',
            'asesmen',
            'lampirans'
        ])
            ->where('token', $token)
            ->firstOrFail();

        $provList = [
            '11|ACEH' => 'ACEH',
            '51|BALI' => 'BALI',
            '36|BANTEN' => 'BANTEN',
            '17|BENGKULU' => 'BENGKULU',
            '34|DI YOGYAKARTA' => 'DI YOGYAKARTA',
            '31|DKI JAKARTA' => 'DKI JAKARTA',
            '75|GORONTALO' => 'GORONTALO',
            '15|JAMBI' => 'JAMBI',
            '32|JAWA BARAT' => 'JAWA BARAT',
            '33|JAWA TENGAH' => 'JAWA TENGAH',
            '35|JAWA TIMUR' => 'JAWA TIMUR',
            '61|KALIMANTAN BARAT' => 'KALIMANTAN BARAT',
            '63|KALIMANTAN SELATAN' => 'KALIMANTAN SELATAN',
            '62|KALIMANTAN TENGAH' => 'KALIMANTAN TENGAH',
            '64|KALIMANTAN TIMUR' => 'KALIMANTAN TIMUR',
            '65|KALIMANTAN UTARA' => 'KALIMANTAN UTARA',
            '19|KEP. BANGKA BELITUNG' => 'KEP. BANGKA BELITUNG',
            '21|KEP. RIAU' => 'KEP. RIAU',
            '18|LAMPUNG' => 'LAMPUNG',
            '81|MALUKU' => 'MALUKU',
            '82|MALUKU UTARA' => 'MALUKU UTARA',
            '52|NUSA TENGGARA BARAT' => 'NUSA TENGGARA BARAT',
            '53|NUSA TENGGARA TIMUR' => 'NUSA TENGGARA TIMUR',
            '94|PAPUA' => 'PAPUA',
            '91|PAPUA BARAT' => 'PAPUA BARAT',
            '14|RIAU' => 'RIAU',
            '76|SULAWESI BARAT' => 'SULAWESI BARAT',
            '73|SULAWESI SELATAN' => 'SULAWESI SELATAN',
            '72|SULAWESI TENGAH' => 'SULAWESI TENGAH',
            '74|SULAWESI TENGGARA' => 'SULAWESI TENGGARA',
            '71|SULAWESI UTARA' => 'SULAWESI UTARA',
            '13|SUMATERA BARAT' => 'SUMATERA BARAT',
            '16|SUMATERA SELATAN' => 'SUMATERA SELATAN',
            '12|SUMATERA UTARA' => 'SUMATERA UTARA',
        ];

        $provinsis = Wilayah::provinsis()->orderBy('nama')->get(['id_wilayah', 'nama']);

        try {
            foreach ($provinsis as $p) {
                $val = $p->id_wilayah . '|' . strtoupper($p->nama);
                if (!isset($provList[$val])) {
                    $provList[$val] = strtoupper($p->nama);
                }
            }
        } catch (\Throwable $e) {}

        $pm = $survey->pengajuanMustahik;
        $pengajuan = $pm?->pengajuan;
        
        // Survey dapat diedit selama disposisi pimpinan belum ditetapkan/disetujui
        $hasDisposisi = $pm?->disposisi !== null || ($pm?->nominal > 0 && $pm?->sumber_dana_id !== null);
        $isDisetujuiPimpinan = $pengajuan?->persetujuan && $pengajuan->persetujuan->status === 'disetujui';
        $canEditSurvey = !$hasDisposisi && !$isDisetujuiPimpinan;

        return view('public-survey', compact('survey', 'token', 'provList', 'provinsis', 'canEditSurvey', 'hasDisposisi', 'isDisetujuiPimpinan'));
    }

    private function _canEditSurvey($survey) {
        $pm = $survey->pengajuanMustahik;
        $pengajuan = $pm?->pengajuan;
        $hasDisposisi = $pm?->disposisi !== null || ($pm?->nominal > 0 && $pm?->sumber_dana_id !== null);
        $isDisetujuiPimpinan = $pengajuan?->persetujuan && $pengajuan->persetujuan->status === 'disetujui';
        return !$hasDisposisi && !$isDisetujuiPimpinan;
    }

    public function saveSurveyorInfo($token, Request $request)
    {
        $survey = PengajuanSurvey::where('token', $token)->firstOrFail();
        if(!$this->_canEditSurvey($survey)){
            return redirect()->route('public.survey', ['token' => $token])->withErrors(['survey_locked' => 'Survey tidak dapat diubah lagi.'])->withInput();
        }

        $request->validate([
            'surveyor_nama'    => 'required|string|max:255',
            'surveyor_nohp'    => 'required|string|max:50',
            'surveyor_tanggal' => 'required|date',
        ], [
            'surveyor_nama.required'    => 'Nama Petugas Surveyor wajib diisi.',
            'surveyor_nohp.required'    => 'Nomor WhatsApp / HP Surveyor wajib diisi.',
            'surveyor_tanggal.required' => 'Tanggal pelaksanaan survey wajib diisi.',
            'surveyor_tanggal.date'     => 'Format tanggal survey tidak valid.',
        ]);

        $survey->update([
            'surveyor'       => trim($request->surveyor_nama),
            'nohp_surveyor'  => trim($request->surveyor_nohp),
            'tanggal_survey' => $request->surveyor_tanggal,
        ]);

        return redirect()->route('public.survey', ['token' => $token])->with('success_surveyor', 'Data Petugas Surveyor berhasil disimpan!');
    }

    public function submitHadKifayah($token, Request $request)
    {
        $survey = PengajuanSurvey::where('token', $token)->firstOrFail();
        if(!$this->_canEditSurvey($survey)){
            return redirect()->route('public.survey', ['token' => $token])->withErrors(['survey_locked' => 'Survey tidak dapat diubah lagi.'])->withInput();
        }

        if (empty(trim($survey->surveyor ?? '')) || empty(trim($survey->nohp_surveyor ?? '')) || empty($survey->tanggal_survey)) {
            return redirect()->route('public.survey', ['token' => $token])
                ->withErrors(['surveyor_nama' => 'Silakan lengkapi Data Surveyor (Nama, No. HP/WA, dan Tanggal Pelaksanaan Survey) terlebih dahulu sebelum mengisi Survey Had Kifayah.'])
                ->withInput();
        }

        $request->validate([
            'nama_mustahik' => 'required|string',
            'alamat_mustahik' => 'required|string',
        ]);

        $cleanNumber = function ($val) {
            if ($val === null || $val === '') return 0;
            return (int) preg_replace('/\D/', '', (string) $val);
        };

        $payload = [
            'opz' => $request->input('opz') ?: 'BAZNAS KABUPATEN CILACAP',
            'nama_petugas' => $survey->surveyor ?: 'Surveyor BAZNAS',
            'nama_mustahik' => $request->input('nama_mustahik'),
            'alamat_mustahik' => $request->input('alamat_mustahik'),
            'propinsi' => $request->input('propinsi') ?: '33|JAWA TENGAH',
            'pendapatan_keluarga' => $cleanNumber($request->input('pendapatan_keluarga')),
            'pendapatan_anggotakel' => $cleanNumber($request->input('pendapatan_anggotakel')),
            'pengeluaran_kk' => $cleanNumber($request->input('pengeluaran_kk')),
            'pengeluaran_ibu' => $cleanNumber($request->input('pengeluaran_ibu')),
            'pengeluaran_anakDewasaBekerja' => $cleanNumber($request->input('pengeluaran_anakDewasaBekerja')),
            'pengeluaran_anakDewasaTidakBekerja' => $cleanNumber($request->input('pengeluaran_anakDewasaTidakBekerja')),
            'pengeluaran_sma' => $cleanNumber($request->input('pengeluaran_sma')),
            'pengeluaran_smp' => $cleanNumber($request->input('pengeluaran_smp')),
            'pengeluaran_sd' => $cleanNumber($request->input('pengeluaran_sd')),
            'pengeluaran_bayi' => $cleanNumber($request->input('pengeluaran_bayi')),
            'pengeluaran_disabilitas' => $cleanNumber($request->input('pengeluaran_disabilitas')),
            'pengeluaran_ibuMenyusui' => $cleanNumber($request->input('pengeluaran_ibuMenyusui')),
            'pengeluaran_ibuHamil' => $cleanNumber($request->input('pengeluaran_ibuHamil')),
            'pengeluaran_janda' => $cleanNumber($request->input('pengeluaran_janda')),
            'pengeluaran_pt' => $cleanNumber($request->input('pengeluaran_pt')),
            'tempat_rumah' => $request->input('tempat_rumah') ?: '5',
            'tempat_listrik' => $request->input('tempat_listrik') ?: '3',
            'pemerintah_bansos' => $request->input('pemerintah_bansos') ?: '1',
            'pemerintah_bpjs' => $request->input('pemerintah_bpjs') ?: '5',
            'asset_motor' => $request->input('asset_motor') ?: '1',
            'asset_tabungan' => $request->input('asset_tabungan') ?: '1',
            'beban_hutang' => $request->input('beban_hutang') ?: '1',
            'mode' => 'new',
        ];

        try {
            $response = Http::timeout(8)->asForm()->post('https://www.puskasbaznas.com/application/hadkifayah/controller/calculate/', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $filesCode = $data['files'] ?? null;
                $linkHadKifayah = $filesCode 
                    ? "https://www.puskasbaznas.com/application/hadkifayah/docs/{$filesCode}.html"
                    : null;

                PengajuanSurveyHadKifayah::updateOrCreate(
                    ['pengajuan_survey_id' => $survey->id],
                    array_merge($payload, [
                        'link_had_kifayah' => $linkHadKifayah,
                        'status_kelayakan' => $data['status'] ?? null,
                        'kategori_prioritas' => $data['prioritas'] ?? null,
                        'pendapatan_txt' => $data['pendapatan'] ?? null,
                        'pengeluaran_txt' => $data['pengeluaran'] ?? null,
                        'selisih_txt' => $data['selisih'] ?? null,
                        'files_code' => $filesCode,
                    ])
                );

                return redirect()->route('public.survey', ['token' => $token])->with('success_hk', 'Survey Had Kifayah berhasil dikalkulasi & disimpan!')->with('link_had_kifayah', $linkHadKifayah);
            } else {
                return back()->withErrors(['hk_error' => 'Gagal memproses API BAZNAS (Status Code: ' . $response->status() . ')']);
            }
        } catch (\Exception $e) {
            \Log::error('submitHadKifayah', ['error' => $e->getMessage(), 'exception'=> (string)$e]);
            return back()->withErrors(['hk_error' => 'Terjadi kesalahan tak terduga memproses request.']);
        }
    }

    public function submitAsesmen($token, Request $request)
    {
        $survey = PengajuanSurvey::where('token', $token)->firstOrFail();
        if(!$this->_canEditSurvey($survey)){
            return redirect()->route('public.survey', ['token' => $token])->withErrors(['survey_locked' => 'Survey tidak dapat diubah lagi.'])->withInput();
        }

        if (empty(trim($survey->surveyor ?? '')) || empty(trim($survey->nohp_surveyor ?? '')) || empty($survey->tanggal_survey)) {
            return redirect()->route('public.survey', ['token' => $token])
                ->withErrors(['surveyor_nama' => 'Silakan lengkapi Data Surveyor (Nama, No. HP/WA, dan Tanggal Pelaksanaan Survey) terlebih dahulu sebelum mengisi Survey Asesmen.'])
                ->withInput();
        }

        $request->validate([
            'as_nama_surveyor' => 'nullable|string',
            'as_nama_mustahik' => 'required|string',
            'as_bersedia_disurvei' => 'required|in:YA,TIDAK',
        ]);

        $normJk = match (strtoupper($request->as_jenis_kelamin ?? '')) {
            'L', 'LAKI-LAKI', 'LAKI - LAKI' => 'Laki-laki',
            'P', 'PEREMPUAN' => 'Perempuan',
            default => ($request->as_jenis_kelamin ?: 'Laki-laki'),
        };

        $pm = $survey->pengajuanMustahik;
        $namaSurveyor = trim($request->as_nama_surveyor ?: ($survey->surveyor ?? ''));
        $jenisProgram = trim($request->as_jenis_program ?: ($pm?->program?->nama ?? ''));
        $subProgram = trim($request->as_sub_program ?: ($pm?->subprogram?->nama ?? ''));

        // Explicit image file validation (max 4MB, must be image)
        $fileRules = ['image','max:4096']; // 4MB per file
        $fileInputs = [
            'as_foto_tempat_tinggal', 'as_foto_atap', 'as_foto_lantai', 'as_foto_dinding', 'as_foto_dapur', 'as_foto_kamar_mandi', 'as_foto_usaha'
        ];
        $validateFiles = [];
        foreach($fileInputs as $field){
            if($request->hasFile($field)) {
                $validateFiles[$field] = $fileRules;
            }
        }
        if(count($validateFiles)) $request->validate($validateFiles);

        $dataToSave = [
            'nama_surveyor' => $namaSurveyor,
            'jenis_program' => $jenisProgram,
            'sub_program' => $subProgram,
            'nama_mustahik' => $request->as_nama_mustahik,
            'nkk' => $request->as_nkk,
            'nik' => $request->as_nik,
            'tempat_lahir' => $request->as_tempat_lahir,
            'tanggal_lahir' => $request->as_tanggal_lahir ?: null,
            'jenis_kelamin' => $normJk,
            'alamat_lengkap' => $request->as_alamat_lengkap,
            'id_provinsi' => $request->as_id_provinsi,
            'id_kabupaten' => $request->as_id_kabupaten,
            'id_kecamatan' => $request->as_id_kecamatan,
            'id_kelurahan' => $request->as_id_kelurahan,
            'kode_pos' => $request->as_kode_pos,
            'desa' => $request->as_desa ?: ($request->as_id_kelurahan ? Wilayah::where('id_wilayah', $request->as_id_kelurahan)->value('nama') : null),
            'kecamatan' => $request->as_kecamatan ?: ($request->as_id_kecamatan ? Wilayah::where('id_wilayah', $request->as_id_kecamatan)->value('nama') : null),
            'nohp' => $request->as_nohp,
            'bersedia_disurvei' => $request->as_bersedia_disurvei,

            // Bagian 2
            'agama' => $request->as_agama ?? 'Islam',
            'jumlah_tanggungan' => (int) ($request->as_jumlah_tanggungan ?? 0),
            'jumlah_sepeda' => $request->as_jumlah_sepeda ?? 'TIDAK ADA',
            'jumlah_sepeda_motor' => $request->as_jumlah_sepeda_motor ?? 'TIDAK ADA',
            'jumlah_mobil' => $request->as_jumlah_mobil ?? 'TIDAK ADA',
            'total_penghasilan_perbulan' => (string) ((int) preg_replace('/\D/', '', (string) ($request->as_total_penghasilan_perbulan ?? '0'))),

            // Bagian 3
            'status_tanah' => $request->as_status_tanah ?? 'Hak Milik',
            'status_rumah' => $request->as_status_rumah ?? 'Hak Milik',
            'bukti_dokumen' => $request->as_bukti_dokumen ?? 'Ada',
            'kondisi_tempat_tinggal' => $request->as_kondisi_tempat_tinggal ?? 'Permanen',
            'kondisi_atap' => $request->as_kondisi_atap ?? 'Genteng',
            'kondisi_lantai' => $request->as_kondisi_lantai ?? 'Keramik',
            'kondisi_dinding' => $request->as_kondisi_dinding ?? 'Permanen',
            'kondisi_dapur' => $request->as_kondisi_dapur ?? 'Ada',
            'kondisi_kamar_mandi' => $request->as_kondisi_kamar_mandi ?? 'Ada',
            'sumber_air_bersih' => $request->as_sumber_air_bersih ?? 'PDAM',
            'daya_listrik' => $request->as_daya_listrik ?? '900 W',

            // Bagian 4
            'punya_usaha' => $request->as_punya_usaha ?: 'Tidak Ada',
            'lama_usaha' => ($request->as_punya_usaha === 'Ada') ? ($request->as_lama_usaha ?: null) : null,
            'hasil_akhir_permohonan' => $request->as_hasil_akhir_permohonan ?: 'MODAL USAHA',
            'bpjs_kesehatan' => $request->as_bpjs_kesehatan ?: 'Tidak Punya',
            'status_bpjs_kesehatan' => ($request->as_bpjs_kesehatan === 'Punya') ? ($request->as_status_bpjs_kesehatan ?: 'Aktif') : null,
            'riwayat_penyakit' => $request->as_riwayat_penyakit,

            // Bagian 5
            'skor_penilaian' => (int) ($request->as_skor_penilaian ?? 3),
            'hasil_survei' => $request->as_hasil_survei ?? 'LAYAK',
        ];

        if ($request->hasFile('as_foto_tempat_tinggal')) {
            $dataToSave['foto_tempat_tinggal'] = $request->file('as_foto_tempat_tinggal')->store('survey_asesmen', 'public');
        }
        if ($request->hasFile('as_foto_atap')) {
            $dataToSave['foto_atap'] = $request->file('as_foto_atap')->store('survey_asesmen', 'public');
        }
        if ($request->hasFile('as_foto_lantai')) {
            $dataToSave['foto_lantai'] = $request->file('as_foto_lantai')->store('survey_asesmen', 'public');
        }
        if ($request->hasFile('as_foto_dinding')) {
            $dataToSave['foto_dinding'] = $request->file('as_foto_dinding')->store('survey_asesmen', 'public');
        }
        if ($request->hasFile('as_foto_dapur')) {
            $dataToSave['foto_dapur'] = $request->file('as_foto_dapur')->store('survey_asesmen', 'public');
        }
        if ($request->hasFile('as_foto_kamar_mandi')) {
            $dataToSave['foto_kamar_mandi'] = $request->file('as_foto_kamar_mandi')->store('survey_asesmen', 'public');
        }
        if ($request->hasFile('as_foto_usaha')) {
            $dataToSave['foto_usaha'] = $request->file('as_foto_usaha')->store('survey_asesmen', 'public');
        }

        PengajuanSurveyAsesmen::updateOrCreate(
            ['pengajuan_survey_id' => $survey->id],
            $dataToSave
        );

        return redirect()->route('public.survey', ['token' => $token])->with('success_asesmen', 'Survey Asesmen berhasil disimpan!');
    }

    public function submitFinalSurvey($token, Request $request)
    {
        $survey = PengajuanSurvey::where('token', $token)->firstOrFail();

        $request->validate([
            'final_hasil'   => 'required|in:layak,tidak layak',
            'final_catatan' => 'nullable|string',
        ]);

        if (empty(trim($survey->surveyor ?? '')) || empty(trim($survey->nohp_surveyor ?? '')) || empty($survey->tanggal_survey)) {
            return redirect()->route('public.survey', ['token' => $token])
                ->withErrors(['surveyor_nama' => 'Nama Surveyor, No. WhatsApp/HP, dan Tanggal Survey wajib diisi sebelum menyelesaikan kesimpulan survey.'])
                ->withInput();
        }

        DB::transaction(function () use ($survey, $request) {
            $survey->update([
                'hasil_akhir' => $request->final_hasil,
                'catatan' => $request->final_catatan,
            ]);

            $pm = $survey->pengajuanMustahik;
            if ($pm && $pm->pengajuan) {
                // If all mustahiks rejected, mark rejected or keep track
                if ($request->final_hasil === 'tidak layak') {
                    // Update if individual or check other mustahiks
                }
            }
        });

        return redirect()->route('public.survey', ['token' => $token])->with('success', 'Survey Kelayakan berhasil diselesaikan!');
    }
}
