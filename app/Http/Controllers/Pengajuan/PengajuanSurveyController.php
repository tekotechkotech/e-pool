<?php

namespace App\Http\Controllers\Pengajuan;

use App\Http\Controllers\Controller;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanMustahik;
use App\Models\PengajuanSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PengajuanSurveyController extends Controller
{
    /**
     * Show survey data for specific mustahik in pengajuan.
     */
    public function show(Request $request, $pengajuanId, $mustahikId)
    {
        $pengajuan = PengajuanBantuan::findOrFail($pengajuanId);

        $mustahik = PengajuanMustahik::where('id', $mustahikId)
            ->where('pengajuan_id', $pengajuan->id)
            ->with([
                'mustahik.person',
                'mustahik.lembaga',
                'survey.hadKifayah',
                'survey.asesmen',
                'survey.lampirans',
                'program',
                'subprogram',
            ])
            ->firstOrFail();

        if (!$mustahik->survey) {
            $mustahik->survey()->firstOrCreate([], ['token' => Str::random(32)]);
            $mustahik->load(['survey.hadKifayah', 'survey.asesmen', 'survey.lampirans']);
        } elseif (empty($mustahik->survey->token)) {
            $mustahik->survey->update(['token' => Str::random(32)]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'pengajuan' => [
                    'id'              => $pengajuan->id,
                    'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                    'judul'           => $pengajuan->judul_surat,
                    'status'          => $pengajuan->status_tahapan,
                ],
                'mustahik'  => $mustahik,
            ]);
        }

        $routeName = $this->resolveShowRouteName($pengajuan);

        return redirect()->route($routeName, [
            'pengajuan' => $pengajuan->id,
            'tab'       => 'survey',
            'mustahik'  => $mustahik->id,
        ]);
    }

    /**
     * Store or update survey data for specific mustahik.
     */
    public function store(Request $request, $pengajuanId, $mustahikId)
    {
        $pengajuan = PengajuanBantuan::findOrFail($pengajuanId);

        $pm = PengajuanMustahik::where('id', $mustahikId)
            ->where('pengajuan_id', $pengajuan->id)
            ->firstOrFail();

        $request->validate([
            'status_kelayakan' => 'required|string',
            'petugas_survey'   => 'required|string|max:255',
            'nohp_surveyor'    => 'required|string|max:50',
            'tanggal_survey'   => 'required|date',
            'catatan'          => 'nullable|string',
            'foto_survey'      => 'nullable|file|image|max:10240',
        ], [
            'petugas_survey.required' => 'Nama petugas surveyor wajib diisi.',
            'nohp_surveyor.required'  => 'Nomor WhatsApp / HP surveyor wajib diisi.',
            'tanggal_survey.required' => 'Tanggal survey wajib diisi.',
            'tanggal_survey.date'     => 'Format tanggal survey tidak valid.',
        ]);

        $hasilAkhir = in_array(strtolower($request->status_kelayakan), ['layak', 'tidak layak', 'tidak_layak', 'dipertimbangkan'])
            ? (str_contains(strtolower($request->status_kelayakan), 'tidak') ? 'tidak layak' : (str_contains(strtolower($request->status_kelayakan), 'dipertimbangkan') ? 'dipertimbangkan' : 'layak'))
            : 'layak';

        $survey = PengajuanSurvey::updateOrCreate(
            ['pengajuan_mustahik_id' => $pm->id],
            [
                'surveyor'         => trim($request->petugas_survey),
                'nohp_surveyor'    => trim($request->nohp_surveyor),
                'tanggal_survey'   => $request->tanggal_survey,
                'hasil_akhir'      => $hasilAkhir,
                'catatan'          => $request->catatan,
                'token'            => $pm->survey?->token ?: Str::random(32),
            ]
        );

        if ($request->hasFile('foto_survey')) {
            $path = $request->file('foto_survey')->store('lampiran_survey', 'public');
            $survey->lampirans()->create([
                'name'      => 'Foto Dokumentasi Survey Lapangan',
                'category'  => 'survey',
                'file_path' => $path,
            ]);
        }

        $routeName = $this->resolveShowRouteName($pengajuan);

        return redirect()->route($routeName, [
            'pengajuan' => $pengajuan->id,
            'tab'       => 'survey',
            'mustahik'  => $pm->id,
        ])->with('success', 'Hasil kesimpulan survey kelayakan berhasil disimpan.');
    }

    /**
     * Resolve show route name based on pengajuan type.
     */
    protected function resolveShowRouteName(PengajuanBantuan $pengajuan): string
    {
        $isUpz = $pengajuan->is_upz || in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']) || $pengajuan->lembaga_id !== null;

        return $isUpz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';
    }
}
