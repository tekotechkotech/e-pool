<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Muzakki;
use App\Models\Upz;
use App\Models\DanaTransaksi;
use App\Models\SetoranUpz;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $roles = [];

        if ($user->person) {
            $roles = $user->person->penguruses()
                ->where('is_active', true)
                ->with('pengurusJabatan')
                ->get()
                ->pluck('pengurusJabatan.nama')
                ->filter()
                ->values()
                ->toArray();
        } else {
            if ($user->email === 'admin@baznas.go.id' || $user->email === 'admin@baznas.id') {
                $roles = ['Admin System'];
            }
        }

        $isAdmin = in_array('Admin System', $roles) || in_array('Admin', $roles) || in_array('Admin BAZNAS', $roles) || $user->email === 'admin@baznas.go.id' || $user->email === 'admin@baznas.id';
        $isPimpinan = in_array('Pimpinan', $roles) || in_array('Pimpinan BAZNAS', $roles);
        $isUPZ = count(array_intersect(['Ketua', 'Sekretaris', 'Bendahara'], $roles)) > 0;
        $isStaff = in_array('Staff', $roles) || in_array('Staff Pelaksana', $roles) || in_array('Staff Penyaluran', $roles);

        $totalMuzakki = Muzakki::count();
        $totalUpz = Upz::whereNotNull('kode_simba')->count();
        $totalZisTerkumpul = DanaTransaksi::where('jenis', 'Masuk')->sum('nominal');
        $totalSetoranUpz = SetoranUpz::count();
        $nominalSetoranUpz = SetoranUpz::sum('total_jumlah');

        $stats = [
            'totalMuzakki' => $totalMuzakki,
            'totalUpz' => $totalUpz,
            'totalZisTerkumpul' => $totalZisTerkumpul,
            'totalSetoranUpz' => $totalSetoranUpz,
            'nominalSetoranUpz' => $nominalSetoranUpz,
        ];

        return view('dashboard', compact('user', 'roles', 'isAdmin', 'isPimpinan', 'isUPZ', 'isStaff', 'stats'));
    }
}
