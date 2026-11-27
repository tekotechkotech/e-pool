<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Muzakki;
use App\Models\Upz;
use App\Models\DanaTransaksi;
use App\Models\SetoranUpz;
use App\Models\SetoranUpzItem;

class DashboardController extends Controller
{
    public function index(Request $request)
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
        $isUPZ = count(array_intersect(['Ketua', 'Sekretaris', 'Bendahara'], $roles)) > 0 || ($user && $user->isUpzRole());
        $isStaff = in_array('Staff', $roles) || in_array('Staff Pelaksana', $roles) || in_array('Staff Penyaluran', $roles);
        $upzLembagaId = $isUPZ ? $user->upzLembagaId() : null;

        $daftarBulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $daftarTahun = range(min((int)date('Y') - 2, 2024), max((int)date('Y') + 4, 2030));

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $bulanKeAngka = array_flip($namaBulan);

        $bulan = $request->query('bulan', 'semua');
        $tahun = $request->query('tahun', (string)date('Y'));

        $bulanNama = ($bulan && $bulan !== 'semua') ? (is_numeric($bulan) ? ($namaBulan[(int)$bulan] ?? null) : $bulan) : null;
        $bulanNum = ($bulan && $bulan !== 'semua') ? (is_numeric($bulan) ? (int)$bulan : ($bulanKeAngka[$bulan] ?? null)) : null;

        // Query Setoran UPZ terverifikasi sesuai filter & scope role
        $setoranQuery = SetoranUpz::where('status', 'Terverifikasi');
        if ($isUPZ && $upzLembagaId) {
            $setoranQuery->where('lembaga_id', $upzLembagaId);
        }

        if ($bulanNama) {
            $setoranQuery->where(function ($bq) use ($bulanNama, $bulanNum) {
                $bq->where('periode', 'like', "%{$bulanNama}%");
                if ($bulanNum) {
                    $bq->orWhereMonth('tanggal_setor', $bulanNum);
                }
            });
        }

        if ($tahun && $tahun !== 'semua') {
            $setoranQuery->where(function ($tq) use ($tahun) {
                $tq->where('periode', 'like', "%{$tahun}%")
                    ->orWhereYear('tanggal_setor', $tahun);
            });
        }

        $filteredSetoranIds = (clone $setoranQuery)->pluck('id');

        // Query Items Setoran untuk perhitungan riil Muzakki vs Munfiq
        $items = SetoranUpzItem::whereIn('setoran_upz_id', $filteredSetoranIds)->get();

        $muzakkiSet = [];
        $munfiqSet = [];

        foreach ($items as $item) {
            $personKey = $item->muzakki_id ? 'ID_' . $item->muzakki_id : 'NM_' . strtolower(trim($item->nama));
            if (empty($personKey) || $personKey === 'NM_') {
                continue;
            }

            $hasZakat = ($item->nominal_zakat > 0 || $item->nominal_fitrah > 0);
            $hasInfak = ($item->nominal_infak > 0);

            if ($hasZakat) {
                $muzakkiSet[$personKey] = true;
            } elseif ($hasInfak) {
                $munfiqSet[$personKey] = true;
            }
        }

        // Aturan De-duplikasi Klien:
        // Jika dalam 1 periode ZIS ada zakat dan infak masuk dari 1 orang,
        // orang yang bersangkutan HANYA masuk sebagai Muzakki (TIDAK + Munfiq).
        foreach (array_keys($muzakkiSet) as $mKey) {
            unset($munfiqSet[$mKey]);
        }

        $calcMuzakki = count($muzakkiSet);
        $totalMunfiq = count($munfiqSet);

        // Fallback jika belum ada data transaksi pada tahun berjalan
        $totalMuzakki = $calcMuzakki > 0 ? $calcMuzakki : ($bulan === 'semua' ? Muzakki::count() : 0);

        $totalUpz = Upz::whereNotNull('kode_simba')->count();
        $totalSetoranUpz = $filteredSetoranIds->count();
        $nominalSetoranUpz = (clone $setoranQuery)->sum('total_jumlah');

        // ZIS terkumpul
        $danaQuery = DanaTransaksi::where('jenis', 'Masuk');
        if ($bulanNum) {
            $danaQuery->whereMonth('tanggal', $bulanNum);
        }
        if ($tahun && $tahun !== 'semua') {
            $danaQuery->whereYear('tanggal', $tahun);
        }
        $totalZisTerkumpul = $danaQuery->sum('nominal');

        $stats = [
            'totalMuzakki' => $totalMuzakki,
            'totalMunfiq' => $totalMunfiq,
            'totalUpz' => $totalUpz,
            'totalZisTerkumpul' => $totalZisTerkumpul,
            'totalSetoranUpz' => $totalSetoranUpz,
            'nominalSetoranUpz' => $nominalSetoranUpz,
        ];

        return view('dashboard', compact(
            'user',
            'roles',
            'isAdmin',
            'isPimpinan',
            'isUPZ',
            'isStaff',
            'stats',
            'daftarBulan',
            'daftarTahun',
            'bulan',
            'tahun',
            'bulanNama'
        ));
    }
}
