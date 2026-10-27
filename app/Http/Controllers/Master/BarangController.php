<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\BarangTransaksi;
use App\Models\PengadaanBarang;
use App\Models\Dana;
use App\Models\DanaTransaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $barangs = Barang::orderBy('nama', 'asc')->get();
        $selectedBarangId = $request->query('barang_id');
        $selectedJenis = $request->query('jenis');

        $query = BarangTransaksi::with(['barang', 'referensi', 'createdBy'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        $baseQuery = BarangTransaksi::query();

        if ($selectedBarangId) {
            $query->where('barang_id', $selectedBarangId);
            $baseQuery->where('barang_id', $selectedBarangId);
        }

        if ($selectedJenis && in_array($selectedJenis, ['Masuk', 'Keluar'])) {
            $query->where('jenis', $selectedJenis);
        }

        $totalMasuk = (clone $baseQuery)->where('jenis', 'Masuk')->sum('jumlah');
        $totalKeluar = (clone $baseQuery)->where('jenis', 'Keluar')->sum('jumlah');

        $transaksis = $query->paginate(15)->withQueryString();

        $danas = Dana::active()->ordered()->get();

        // Ambil pengajuan yang memiliki mustahik dengan disposisi berupa barang jika tabel/model tersedia
        $pengajuansWithBarang = collect();

        $widgets = [
            'totalBarang'     => Barang::count(),
            'totalStokAktif'  => Barang::sum('stok'),
            'totalMasuk'      => $totalMasuk,
            'totalKeluar'     => $totalKeluar,
        ];

        return view('master.barangs', compact(
            'barangs',
            'transaksis',
            'selectedBarangId',
            'selectedJenis',
            'widgets',
            'danas',
            'pengajuansWithBarang'
        ));
    }

    public function storeBarang(Request $request)
    {
        $barangId = $request->input('barang_id');

        $validated = $request->validate([
            'kode'       => ['required', 'string', 'max:50', Rule::unique('barangs')->ignore($barangId)],
            'nama'       => 'required|string|max:255',
            'kategori'   => 'required|string|max:100',
            'satuan'     => 'required|string|max:50',
            'keterangan' => 'nullable|string',
            'is_active'  => 'nullable|boolean',
        ]);

        $barang = Barang::updateOrCreate(
            ['id' => $barangId],
            [
                'kode'       => strtoupper(trim($validated['kode'])),
                'nama'       => trim($validated['nama']),
                'kategori'   => $validated['kategori'],
                'satuan'     => trim($validated['satuan']),
                'keterangan' => $validated['keterangan'] ?? null,
                'is_active'  => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            ]
        );

        $msg = $barangId ? 'Data barang berhasil diperbarui.' : 'Data master barang baru berhasil ditambahkan.';
        return redirect()->route('master.barangs', ['barang_id' => $barang->id])->with('success', $msg);
    }

    public function destroyBarang($id)
    {
        $barang = Barang::findOrFail($id);

        if ($barang->transaksis()->exists()) {
            return back()->withErrors(['error' => 'Barang tidak dapat dihapus karena sudah memiliki riwayat mutasi/transaksi stok.']);
        }

        $barang->delete();

        return redirect()->route('master.barangs')->with('success', 'Data barang berhasil dihapus.');
    }

    public function storePengadaan(Request $request)
    {
        if ($request->has('harga_satuan')) {
            $request->merge(['harga_satuan' => preg_replace('/\D/', '', (string) $request->harga_satuan)]);
        }
        if ($request->has('harga_total')) {
            $request->merge(['harga_total' => preg_replace('/\D/', '', (string) $request->harga_total)]);
        }

        $request->validate([
            'nomor_ba'              => 'required|string|max:255',
            'tanggal_ba'            => 'required|date',
            'barang_id'             => 'nullable|exists:barangs,id',
            'nama_barang_baru'      => 'nullable|string|max:255',
            'satuan_barang_baru'    => 'nullable|string|max:50',
            'kategori_barang_baru'  => 'nullable|string|max:100',
            'jumlah'                => 'required|integer|min:1',
            'harga_satuan'          => 'required|numeric|min:0',
            'harga_total'           => 'required|numeric|min:0',
            'penyedia'              => 'nullable|string|max:255',
            'sumber_dana_id'        => 'nullable|exists:danas,id',
            'potong_kas'            => 'nullable|boolean',
            'file_ba'               => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'keterangan'            => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Tentukan atau buat master barang
            $barangId = $request->barang_id;
            if (!$barangId && $request->filled('nama_barang_baru')) {
                $barang = Barang::create([
                    'kode'                  => 'BRG-' . strtoupper(Str::random(5)),
                    'nama'                  => trim($request->nama_barang_baru),
                    'satuan'                => $request->satuan_barang_baru ?: 'Paket',
                    'kategori'              => $request->kategori_barang_baru ?: 'Sembako',
                    'stok'                  => 0,
                    'harga_satuan_terakhir' => $request->harga_satuan,
                ]);
                $barangId = $barang->id;
            } else {
                $barang = Barang::findOrFail($barangId);
            }

            // Simpan berkas Berita Acara (BA) jika diunggah
            $filePath = null;
            if ($request->hasFile('file_ba')) {
                $filePath = $request->file('file_ba')->store('ba_pengadaan', 'public');
            }

            // Buat record PengadaanBarang
            $pengadaan = PengadaanBarang::create([
                'nomor_ba'              => trim($request->nomor_ba),
                'tanggal_ba'            => $request->tanggal_ba,
                'barang_id'             => $barang->id,
                'jumlah'                => $request->jumlah,
                'harga_satuan'          => $request->harga_satuan,
                'harga_total'           => $request->harga_total,
                'penyedia'              => $request->penyedia ? trim($request->penyedia) : null,
                'sumber_dana_id'        => $request->sumber_dana_id ?: null,
                'file_ba'               => $filePath,
                'keterangan'            => $request->keterangan,
                'created_by'            => Auth::id(),
            ]);

            // Tambahkan stok barang & perbarui harga satuan terakhir
            $barang->increment('stok', $request->jumlah);
            $barang->update(['harga_satuan_terakhir' => $request->harga_satuan]);

            // Buat uraian mutasi
            $uraian = 'Pengadaan barang sesuai BA ' . $request->nomor_ba;
            if ($request->filled('penyedia')) {
                $uraian .= ' dari ' . $request->penyedia;
            }

            // Catat mutasi BarangTransaksi (Masuk)
            BarangTransaksi::create([
                'barang_id'      => $barang->id,
                'tanggal'        => $request->tanggal_ba,
                'jenis'          => 'Masuk',
                'jumlah'         => $request->jumlah,
                'harga_satuan'   => $request->harga_satuan,
                'harga_total'    => $request->harga_total,
                'nomor_dokumen'  => $request->nomor_ba,
                'uraian'         => $uraian,
                'referensi_type' => PengadaanBarang::class,
                'referensi_id'   => $pengadaan->id,
                'created_by'     => Auth::id(),
            ]);

            // Opsi: Jika diminta otomatis memotong saldo kas pada modul Dana & Transaksi
            if ($request->boolean('potong_kas') && $request->sumber_dana_id && $request->harga_total > 0) {
                DanaTransaksi::create([
                    'dana_id'        => $request->sumber_dana_id,
                    'tanggal'        => $request->tanggal_ba,
                    'jenis'          => 'Keluar',
                    'nominal'        => $request->harga_total,
                    'uraian'         => 'Pengadaan ' . $barang->nama . ' (' . $request->jumlah . ' ' . $barang->satuan . ') - BA #' . $request->nomor_ba,
                    'referensi_type' => PengadaanBarang::class,
                    'referensi_id'   => $pengadaan->id,
                ]);
            }

            DB::commit();

            return redirect()->route('master.barangs', ['barang_id' => $barang->id])
                ->with('success', 'Data Berita Acara (BA) Pengadaan berhasil dicatat dan stok barang telah ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal mencatat pengadaan barang: ' . $e->getMessage()])->withInput();
        }
    }

    public function storeMutasiKeluar(Request $request)
    {
        $request->validate([
            'keluar_barang_id' => 'required|exists:barangs,id',
            'keluar_tanggal'   => 'required|date',
            'keluar_jumlah'    => 'required|integer|min:1',
            'keluar_dokumen'   => 'nullable|string|max:255',
            'keluar_uraian'    => 'required|string|max:500',
        ]);

        $barang = Barang::findOrFail($request->keluar_barang_id);

        if ($barang->stok < $request->keluar_jumlah) {
            return back()->withErrors(['error' => 'Stok tidak mencukupi! Sisa stok ' . $barang->nama . ' hanya ' . $barang->stok . ' ' . $barang->satuan . '.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $barang->decrement('stok', $request->keluar_jumlah);

            BarangTransaksi::create([
                'barang_id'      => $barang->id,
                'tanggal'        => $request->keluar_tanggal,
                'jenis'          => 'Keluar',
                'jumlah'         => $request->keluar_jumlah,
                'harga_satuan'   => $barang->harga_satuan_terakhir,
                'harga_total'    => ($barang->harga_satuan_terakhir ? $barang->harga_satuan_terakhir * $request->keluar_jumlah : null),
                'nomor_dokumen'  => $request->keluar_dokumen,
                'uraian'         => $request->keluar_uraian,
                'created_by'     => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('master.barangs', ['barang_id' => $barang->id])
                ->with('success', 'Pengeluaran barang berhasil dicatat dan stok telah dikurangi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal mencatat mutasi keluar: ' . $e->getMessage()]);
        }
    }

    public function destroyTransaksi($id)
    {
        $transaksi = BarangTransaksi::findOrFail($id);
        $barang = $transaksi->barang;

        DB::beginTransaction();
        try {
            // Sesuaikan kembali stok barang
            if ($transaksi->jenis === 'Masuk') {
                if ($barang->stok < $transaksi->jumlah) {
                    return back()->withErrors(['error' => 'Tidak dapat membatalkan transaksi ini karena sisa stok barang sudah berkurang melebihi kuantitas transaksi.']);
                }
                $barang->decrement('stok', $transaksi->jumlah);
            } else {
                $barang->increment('stok', $transaksi->jumlah);
            }

            // Jika transaksi berasal dari PengadaanBarang, hapus juga record pengadaan & transaksi dana terkait
            if ($transaksi->referensi_type === PengadaanBarang::class && $transaksi->referensi_id) {
                $pengadaan = PengadaanBarang::find($transaksi->referensi_id);
                if ($pengadaan) {
                    if ($pengadaan->file_ba && Storage::disk('public')->exists($pengadaan->file_ba)) {
                        Storage::disk('public')->delete($pengadaan->file_ba);
                    }
                    DanaTransaksi::where('referensi_type', PengadaanBarang::class)
                        ->where('referensi_id', $pengadaan->id)
                        ->delete();
                    $pengadaan->delete();
                }
            }

            $transaksi->delete();

            DB::commit();

            return redirect()->route('master.barangs', ['barang_id' => $barang->id])
                ->with('success', 'Transaksi mutasi berhasil dibatalkan dan posisi stok telah disesuaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan transaksi: ' . $e->getMessage()]);
        }
    }
}
