<?php

namespace App\Services;

use App\Models\PengajuanBantuan;
use App\Models\PengajuanSurvey;
use App\Models\PengajuanDisposisi;
use Illuminate\Support\Facades\DB;
use Exception;

use Illuminate\Support\Str;

class PengajuanService
{
    /**
     * Pindah ke tahap Survey
     */
    public function lanjutKeSurvey(PengajuanBantuan $pengajuan)
    {
        DB::transaction(function () use ($pengajuan) {
            // Create draft survey record for each mustahik on this application
            foreach ($pengajuan->pengajuanMustahiks as $pm) {
                if (!$pm->survey) {
                    $pm->survey()->create([
                        'token' => Str::random(32),
                    ]);
                }
            }
        });
    }

    /**
     * Langsung disposisi tanpa survey
     */
    public function lanjutKeDisposisiLangsung(PengajuanBantuan $pengajuan)
    {
        // No status column to update
    }

    /**
     * Submit hasil survey
     */
    public function submitHasilSurvey(PengajuanBantuan $pengajuan, bool $isLayak, $catatan = null)
    {
        DB::transaction(function () use ($pengajuan, $isLayak, $catatan) {
            foreach ($pengajuan->pengajuanMustahiks as $pm) {
                if ($pm->survey) {
                    $pm->survey->update([
                        'hasil_akhir'      => $isLayak ? 'layak' : 'tidak layak',
                        'catatan'          => $catatan,
                    ]);
                }
            }

            if ($isLayak) {
                $pengajuan->update(['ditolak_at' => null]);
            } else {
                $pengajuan->update(['ditolak_at' => now()]);
            }
        });
    }

    /**
     * Proses Disposisi (Input Nominal & Approve) per Mustahik
     */
    public function prosesDisposisi(PengajuanBantuan $pengajuan, array $mustahikData, $pimpinanId, $instruksi, $catatan = null)
    {
        DB::transaction(function () use ($pengajuan, $mustahikData, $pimpinanId, $instruksi, $catatan) {
            foreach ($pengajuan->pengajuanMustahiks as $pm) {
                if (isset($mustahikData[$pm->id])) {
                    $pm->update([
                        'nominal' => $mustahikData[$pm->id]['nominal'] ?? $pm->nominal,
                        'sumber_dana_id' => $mustahikData[$pm->id]['sumber_dana_id'] ?? $pm->sumber_dana_id,
                    ]);
                }

                PengajuanDisposisi::updateOrCreate(
                    ['pengajuan_mustahik_id' => $pm->id],
                    [
                        'pimpinan_id' => $pimpinanId,
                        'instruksi' => $instruksi,
                        'catatan' => $catatan
                    ]
                );
            }

            if (strtolower($instruksi) === 'tolak') {
                $pengajuan->update(['ditolak_at' => now()]);
            } else {
                $pengajuan->update(['ditolak_at' => null]);
            }
        });
    }
}
