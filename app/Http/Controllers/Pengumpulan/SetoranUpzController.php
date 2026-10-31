<?php

namespace App\Http\Controllers\Pengumpulan;

use App\Http\Controllers\Controller;
use App\Models\Dana;
use App\Models\DanaTransaksi;
use App\Models\Muzakki;
use App\Models\SetoranUpz;
use App\Models\SetoranUpzItem;
use App\Models\Upz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetoranUpzController extends Controller
{
    private function generateNomorSetoran(Carbon $tanggal): string
    {
        $tahun = $tanggal->format('Y');
        $bulan = $tanggal->format('m');

        $lastSetoran = SetoranUpz::whereYear('tanggal_setor', $tahun)
            ->whereMonth('tanggal_setor', $bulan)
            ->orderBy('id', 'desc')
            ->first();

        $increment = 1;
        if ($lastSetoran && $lastSetoran->nomor_setoran) {
            $parts = explode('/', $lastSetoran->nomor_setoran);
            $lastNum = end($parts);
            if (is_numeric($lastNum)) {
                $increment = (int)$lastNum + 1;
            }
        }

        return sprintf("SET-UPZ/%s/%s/%04d", $tahun, $bulan, $increment);
    }

    public function index(Request $request)
    {
        $lembagaId = $request->query('lembaga_id');
        $periode = $request->query('periode');
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');
        $status = $request->query('status');
        $search = $request->query('search');

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

        $query = SetoranUpz::with(['upz', 'user'])->latest('tanggal_setor');

        if ($lembagaId) {
            $query->where('lembaga_id', $lembagaId);
        }

        if ($periode) {
            $query->where('periode', 'like', "%{$periode}%");
        }

        if ($bulan) {
            $bulanNama = is_numeric($bulan) ? ($namaBulan[(int)$bulan] ?? null) : $bulan;
            $bulanNum = is_numeric($bulan) ? (int)$bulan : ($bulanKeAngka[$bulan] ?? null);

            $query->where(function ($q) use ($bulanNama, $bulanNum) {
                if ($bulanNama) {
                    $q->where('periode', 'like', "%{$bulanNama}%");
                }
                if ($bulanNum) {
                    $q->orWhereMonth('tanggal_setor', $bulanNum);
                }
            });
        }

        if ($tahun && $tahun !== 'semua') {
            $query->where(function ($q) use ($tahun) {
                $q->where('periode', 'like', "%{$tahun}%")
                    ->orWhereYear('tanggal_setor', $tahun);
            });
        }

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_setoran', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('upz', function ($uq) use ($search) {
                        $uq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $setorans = $query->paginate(15)->withQueryString();
        $upzs = Upz::active()->orderBy('nama')->get();

        return view('pengumpulan.upz.index', compact(
            'setorans',
            'upzs',
            'lembagaId',
            'periode',
            'bulan',
            'tahun',
            'status',
            'search',
            'daftarBulan',
            'daftarTahun'
        ));
    }

    public function create()
    {
        $upzs = Upz::active()->orderBy('nama')->get();
        $muzakkis = Muzakki::with('person')->get();

        $daftarBulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $daftarTahun = range(min((int)date('Y') - 2, 2024), max((int)date('Y') + 4, 2030));

        return view('pengumpulan.upz.create', compact('upzs', 'muzakkis', 'daftarBulan', 'daftarTahun'));
    }

    public function store(Request $request)
    {
        if ($request->filled('bulan') && $request->filled('tahun')) {
            $request->merge([
                'periode' => trim($request->input('bulan') . ' ' . $request->input('tahun')),
            ]);
        }

        $validated = $request->validate([
            'lembaga_id' => 'required|exists:lembagas,id',
            'tanggal_setor' => 'required|date',
            'periode' => 'required|string|max:100',
            'bulan' => 'nullable|string|max:50',
            'tahun' => 'nullable|string|max:10',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.muzakki_id' => 'nullable|exists:muzakkis,id',
            'items.*.nama' => 'required|string|max:255',
            'items.*.npwz' => 'nullable|string|max:50',
            'items.*.nominal_zakat' => 'nullable|numeric|min:0',
            'items.*.nominal_infak' => 'nullable|numeric|min:0',
            'items.*.nominal_fitrah' => 'nullable|numeric|min:0',
            'items.*.nominal_titipan' => 'nullable|numeric|min:0',
            'items.*.keterangan' => 'nullable|string|max:255',
            'items.*.periode' => 'nullable|string|max:100',
        ]);

        $tanggal = Carbon::parse($validated['tanggal_setor']);
        $nomorSetoran = $this->generateNomorSetoran($tanggal);

        DB::transaction(function () use ($validated, $nomorSetoran, $tanggal) {
            $totalZakat = 0;
            $totalInfak = 0;
            $totalFitrah = 0;
            $totalTitipan = 0;

            foreach ($validated['items'] as $item) {
                $totalZakat += (int)($item['nominal_zakat'] ?? 0);
                $totalInfak += (int)($item['nominal_infak'] ?? 0);
                $totalFitrah += (int)($item['nominal_fitrah'] ?? 0);
                $totalTitipan += (int)($item['nominal_titipan'] ?? 0);
            }

            $totalJumlah = $totalZakat + $totalInfak + $totalFitrah + $totalTitipan;

            $setoran = SetoranUpz::create([
                'nomor_setoran' => $nomorSetoran,
                'lembaga_id' => $validated['lembaga_id'],
                'tanggal_setor' => $validated['tanggal_setor'],
                'periode' => $validated['periode'],
                'total_zakat' => $totalZakat,
                'total_infak' => $totalInfak,
                'total_fitrah' => $totalFitrah,
                'total_titipan' => $totalTitipan,
                'total_jumlah' => $totalJumlah,
                'keterangan' => $validated['keterangan'] ?? null,
                'status' => 'Terverifikasi',
                'user_id' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $z = (int)($item['nominal_zakat'] ?? 0);
                $i = (int)($item['nominal_infak'] ?? 0);
                $f = (int)($item['nominal_fitrah'] ?? 0);
                $t = (int)($item['nominal_titipan'] ?? 0);
                $subtotal = $z + $i + $f + $t;

                $muzakkiId = !empty($item['muzakki_id']) ? $item['muzakki_id'] : null;
                $npwz = $item['npwz'] ?? null;
                if ($muzakkiId && empty($npwz)) {
                    $m = Muzakki::find($muzakkiId);
                    $npwz = $m?->npwz;
                }

                SetoranUpzItem::create([
                    'setoran_upz_id' => $setoran->id,
                    'muzakki_id' => $muzakkiId,
                    'npwz' => $npwz,
                    'nama' => $item['nama'],
                    'nominal_zakat' => $z,
                    'nominal_infak' => $i,
                    'nominal_fitrah' => $f,
                    'nominal_titipan' => $t,
                    'jumlah' => $subtotal,
                    'keterangan' => $item['keterangan'] ?? null,
                    'periode' => $item['periode'] ?? $validated['periode'],
                ]);
            }

            // Sinkronkan otomatis ke DanaTransaksi (Masuk)
            // Pos dana Zakat via UPZ (kode 2.2) dan Infak via UPZ (kode 2.7)
            $danaZakat = Dana::where('kode', '2.2')->first();
            $danaInfak = Dana::where('kode', '2.7')->first();

            $upzNama = $setoran->upz?->nama ?? 'UPZ';

            if ($totalZakat > 0 && $danaZakat) {
                DanaTransaksi::create([
                    'tanggal' => $setoran->tanggal_setor,
                    'dana_id' => $danaZakat->id,
                    'jenis' => 'Masuk',
                    'nominal' => $totalZakat,
                    'uraian' => "Penerimaan Zakat via {$upzNama} ({$setoran->nomor_setoran}) - Periode {$setoran->periode}",
                    'referensi_type' => SetoranUpz::class,
                    'referensi_id' => $setoran->id,
                ]);
            }

            if ($totalInfak > 0 && $danaInfak) {
                DanaTransaksi::create([
                    'tanggal' => $setoran->tanggal_setor,
                    'dana_id' => $danaInfak->id,
                    'jenis' => 'Masuk',
                    'nominal' => $totalInfak,
                    'uraian' => "Penerimaan Infak via {$upzNama} ({$setoran->nomor_setoran}) - Periode {$setoran->periode}",
                    'referensi_type' => SetoranUpz::class,
                    'referensi_id' => $setoran->id,
                ]);
            }
        });

        return redirect()->route('pengumpulan.upz.index')->with('success', "Setoran UPZ berhasil disimpan dengan nomor {$nomorSetoran}.");
    }

    public function show($id)
    {
        $setoran = SetoranUpz::with(['upz', 'items.muzakki.person', 'user'])->findOrFail($id);
        return view('pengumpulan.upz.show', compact('setoran'));
    }

    public function cetakLampiran($id)
    {
        $setoran = SetoranUpz::with(['upz.ketua.person', 'items'])->findOrFail($id);
        return view('pengumpulan.upz.cetak-lampiran', compact('setoran'));
    }

    public function destroy($id)
    {
        $setoran = SetoranUpz::findOrFail($id);

        DB::transaction(function () use ($setoran) {
            // Hapus referensi transaksi dana
            DanaTransaksi::where('referensi_type', SetoranUpz::class)
                ->where('referensi_id', $setoran->id)
                ->delete();

            // SetoranUpzItem terhapus cascade di DB atau manual
            $setoran->items()->delete();
            $setoran->delete();
        });

        return redirect()->route('pengumpulan.upz.index')->with('success', 'Setoran UPZ dan transaksi dana terkait berhasil dihapus.');
    }

    public function kasMasuk(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $search = $request->query('search');

        $query = DanaTransaksi::with(['dana', 'referensi'])
            ->where('jenis', 'Masuk')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('uraian', 'like', "%{$search}%")
                    ->orWhereHas('dana', function ($dq) use ($search) {
                        $dq->where('nama', 'like', "%{$search}%")
                            ->orWhere('kode', 'like', "%{$search}%");
                    });
            });
        }

        $transaksis = $query->paginate(20)->withQueryString();
        $totalMasuk = (clone $query)->sum('nominal');

        return view('pengumpulan.kas.index', compact('transaksis', 'totalMasuk', 'startDate', 'endDate', 'search'));
    }
}
