<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dana;
use App\Models\DanaTransaksi;
use Illuminate\Validation\Rule;

class DanaController extends Controller
{
    public function index(Request $request)
    {
        $danas = Dana::orderBy('urutan', 'asc')->orderBy('kode', 'asc')->get();
        $selectedDanaId = $request->query('dana_id');

        $query = DanaTransaksi::with(['dana', 'referensi'])->orderBy('tanggal', 'desc')->orderBy('id', 'desc');
        $baseQuery = DanaTransaksi::query();

        if ($selectedDanaId) {
            $query->where('dana_id', $selectedDanaId);
            $baseQuery->where('dana_id', $selectedDanaId);
        }

        $totalMasuk = (clone $baseQuery)->where('jenis', 'Masuk')->sum('nominal');
        $totalKeluar = (clone $baseQuery)->where('jenis', 'Keluar')->sum('nominal');
        $saldoAkhir = $totalMasuk - $totalKeluar;

        $transaksis = $query->paginate(10)->withQueryString();

        $widgets = [
            'totalDana' => Dana::count(),
            'totalDanaAktif' => Dana::where('is_active', true)->count(),
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'saldoAkhir' => $saldoAkhir,
        ];

        return view('master.danas', compact('danas', 'transaksis', 'selectedDanaId', 'widgets'));
    }

    public function store(Request $request)
    {
        $danaId = $request->input('dana_id');

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:255', Rule::unique('danas')->ignore($danaId)],
            'nama' => 'required|string|max:255',
            'kategori' => 'required|in:Zakat,Infak/Sedekah,CSR,DSKL,Dana Titipan,Non Halal',
            'via_upz' => 'nullable|boolean',
            'urutan' => 'nullable|integer',
            'keterangan' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $dana = Dana::updateOrCreate(
            ['id' => $danaId],
            [
                'kode' => $validated['kode'],
                'nama' => $validated['nama'],
                'kategori' => $validated['kategori'],
                'via_upz' => $request->has('via_upz') ? (bool) $request->input('via_upz') : false,
                'urutan' => $validated['urutan'] ?? 0,
                'keterangan' => $validated['keterangan'] ?? null,
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            ]
        );

        $msg = $danaId ? 'Data jenis dana berhasil diperbarui.' : 'Jenis dana baru berhasil ditambahkan.';
        return redirect()->route('master.danas', ['dana_id' => $dana->id])->with('success', $msg);
    }

    public function destroy($id)
    {
        $dana = Dana::findOrFail($id);
        $dana->delete();

        return redirect()->route('master.danas')->with('success', 'Data jenis dana berhasil dihapus.');
    }

    public function storePenerimaan(Request $request)
    {
        if ($request->has('penerimaan_nominal')) {
            $request->merge(['penerimaan_nominal' => preg_replace('/\D/', '', (string) $request->penerimaan_nominal)]);
        }

        $request->validate([
            'penerimaan_dana_id' => 'required|exists:danas,id',
            'penerimaan_tanggal' => 'required|date',
            'penerimaan_nominal' => 'required|numeric|min:1',
            'penerimaan_uraian' => 'nullable|string|max:500',
            'penerimaan_sumber' => 'nullable|string|max:255',
        ]);

        $uraianLengkap = $request->penerimaan_uraian;
        if ($request->filled('penerimaan_sumber')) {
            $uraianLengkap = ($uraianLengkap ? $uraianLengkap . ' ' : '') . '[Dari: ' . $request->penerimaan_sumber . ']';
        }

        DanaTransaksi::create([
            'dana_id' => $request->penerimaan_dana_id,
            'tanggal' => $request->penerimaan_tanggal,
            'jenis' => 'Masuk',
            'nominal' => $request->penerimaan_nominal,
            'uraian' => $uraianLengkap,
        ]);

        return redirect()->route('master.danas', ['dana_id' => $request->penerimaan_dana_id])->with('success', 'Penerimaan dana berhasil dicatat.');
    }

    public function storeTransaksi(Request $request)
    {
        return $this->storePenerimaan($request);
    }

    public function destroyTransaksi($id)
    {
        $transaksi = DanaTransaksi::findOrFail($id);
        $danaId = $transaksi->dana_id;

        if ($transaksi->referensi_type) {
            return back()->withErrors(['error' => 'Transaksi otomatis dari ' . class_basename($transaksi->referensi_type) . ' tidak dapat dihapus langsung dari sini.']);
        }

        $transaksi->delete();

        return redirect()->route('master.danas', ['dana_id' => $danaId])->with('success', 'Transaksi penerimaan dana berhasil dihapus.');
    }
}
