<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wilayah;

class WilayahController extends Controller
{
    /**
     * Get list of provinces
     */
    public function getProvinsi()
    {
        $provinsis = Wilayah::provinsis()
            ->orderBy('nama')
            ->get(['id_wilayah', 'nama']);

        return response()->json($provinsis);
    }

    /**
     * Get list of regencies/cities in a province
     */
    public function getKabupaten(Request $request, $idProvinsi = null)
    {
        $id = $idProvinsi ?: $request->query('provinsi_id') ?: $request->query('id');
        $kabupatens = Wilayah::kabupatens($id)
            ->orderBy('nama')
            ->get(['id_wilayah', 'nama']);

        return response()->json($kabupatens);
    }

    /**
     * Get list of districts in a regency/city
     */
    public function getKecamatan(Request $request, $idKabupaten = null)
    {
        $id = $idKabupaten ?: $request->query('kabupaten_id') ?: $request->query('id');
        $kecamatans = Wilayah::kecamatans($id)
            ->orderBy('nama')
            ->get(['id_wilayah', 'nama']);

        return response()->json($kecamatans);
    }

    /**
     * Get list of villages/subdistricts in a district (including postal code)
     */
    public function getKelurahan(Request $request, $idKecamatan = null)
    {
        $id = $idKecamatan ?: $request->query('kecamatan_id') ?: $request->query('id');
        $kelurahans = Wilayah::kelurahans($id)
            ->orderBy('nama')
            ->get(['id_wilayah', 'nama', 'kode_pos']);

        return response()->json($kelurahans);
    }

    /**
     * Get postal code of a village/subdistrict
     */
    public function getKodePos(Request $request)
    {
        $id = $request->query('kelurahan_id') ?: $request->query('id');
        $wilayah = Wilayah::where('id_wilayah', $id)->first(['id_wilayah', 'kode_pos']);

        return response()->json([
            'kelurahan_id' => $id,
            'kode_pos' => $wilayah?->kode_pos ?? ''
        ]);
    }
}
