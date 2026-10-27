<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PengajuanSurvey;
use App\Models\PengajuanSurveyHadKifayah;
use Exception;

class HadKifayahController extends Controller
{
    /**
     * Hitung Had Kifayah via API BAZNAS dan simpan hasilnya
     */
    public function hitungDanSimpan(Request $request, $token)
    {
        $survey = PengajuanSurvey::where('token', $token)->firstOrFail();

        $request->validate([
            'opz' => 'nullable|string',
            'nama_petugas' => 'nullable|string',
            'nama_mustahik' => 'required|string',
            'alamat_mustahik' => 'required|string',
            'propinsi' => 'nullable|string',
        ]);

        $cleanNumber = function ($val) {
            if ($val === null || $val === '') return 0;
            return (int) preg_replace('/\D/', '', (string) $val);
        };

        $payload = [
            'opz' => $request->input('opz') ?: 'BAZNAS KABUPATEN CILACAP',
            'nama_petugas' => $request->input('nama_petugas') ?: ($survey->surveyor ?? 'Surveyor BAZNAS'),
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
            $response = Http::asForm()->post('https://www.puskasbaznas.com/application/hadkifayah/controller/calculate/', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                $filesCode = $data['files'] ?? null;
                $linkHadKifayah = $filesCode 
                    ? "https://www.puskasbaznas.com/application/hadkifayah/docs/{$filesCode}.html"
                    : null;

                $hadKifayahData = array_merge($payload, [
                    'pengajuan_survey_id' => $survey->id,
                    'link_had_kifayah' => $linkHadKifayah,
                    'status_kelayakan' => $data['status'] ?? null,
                    'kategori_prioritas' => $data['prioritas'] ?? null,
                    'pendapatan_txt' => $data['pendapatan'] ?? null,
                    'pengeluaran_txt' => $data['pengeluaran'] ?? null,
                    'selisih_txt' => $data['selisih'] ?? null,
                    'files_code' => $filesCode,
                ]);

                PengajuanSurveyHadKifayah::updateOrCreate(
                    ['pengajuan_survey_id' => $survey->id],
                    $hadKifayahData
                );

                return redirect()->back()->with([
                    'success_had_kifayah' => 'Kalkulasi Had Kifayah berhasil disimpan!',
                    'link_had_kifayah' => $linkHadKifayah,
                ]);
            } else {
                return redirect()->back()->withErrors(['had_kifayah' => 'Gagal menghubungi server API BAZNAS (Status: ' . $response->status() . ')']);
            }
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['had_kifayah' => 'Terjadi kesalahan saat memproses Had Kifayah: ' . $e->getMessage()]);
        }
    }
}
