<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class KeluargaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $selectedKk = $request->query('kk');

        $query = DB::table('persons')
            ->select('nomor_kk', DB::raw('count(*) as jumlah_anggota'))
            ->whereNotNull('nomor_kk')
            ->where('nomor_kk', '!=', '');

        if (!empty($search)) {
            $query->where('nomor_kk', 'like', '%' . $search . '%');
        }

        $keluargas = $query->groupBy('nomor_kk')
                           ->orderBy('nomor_kk')
                           ->paginate(10)
                           ->withQueryString();

        $anggotaKeluarga = collect();
        if ($selectedKk) {
            $anggotaKeluarga = Person::with('user')->where('nomor_kk', $selectedKk)->get();
        }

        return view('manajemen.keluarga', compact('keluargas', 'search', 'selectedKk', 'anggotaKeluarga'));
    }
}
