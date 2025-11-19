<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Upz;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Person;

class UpzController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Upz::with(['provinsi', 'kabupaten', 'kecamatan', 'kelurahan'])->withCount('penguruses')->orderBy('id', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('ketua_nama', 'like', '%' . $search . '%')
                  ->orWhere('jenis', 'like', '%' . $search . '%');
            });
        }

        $upzs = $query->paginate(10)->withQueryString();

        return view('manajemen.upz', compact('upzs', 'search'));
    }

    public function show($id, Request $request)
    {
        $relations = [
            'provinsi',
            'kabupaten',
            'kecamatan',
            'kelurahan',
            'penguruses.person',
            'penguruses.pengurusJabatan',
            'mustahiks.person'
        ];

        if (class_exists(\App\Models\PengajuanBantuan::class)) {
            $relations[] = 'pengajuanBantuans.pengajuanMustahiks.program';
            $relations[] = 'pengajuanBantuans.pengajuanMustahiks.subprogram';
        }

        $upz = Upz::with($relations)->findOrFail($id);

        $activeTab = $request->query('tab', 'pengurus');
        $persons = Person::orderBy('nama')->get();
        $jabatans = PengurusJabatan::orderBy('nama')->get();

        return view('manajemen.upz-detail', compact('upz', 'activeTab', 'persons', 'jabatans'));
    }

    public function store(Request $request)
    {
        $upzId = $request->input('upz_id');

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'ketua_nama' => 'nullable|string|max:255',
            'ketua_nik' => 'nullable|string|max:20',
            'jenis' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:50',
            'handphone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'id_provinsi' => 'nullable|string',
            'id_kabupaten' => 'nullable|string',
            'id_kecamatan' => 'nullable|string',
            'id_kelurahan' => 'nullable|string',
            'kode_pos' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        $upz = Upz::updateOrCreate(
            ['id' => $upzId],
            [
                'nama' => $validated['nama'],
                'ketua_nama' => $validated['ketua_nama'] ?? null,
                'ketua_nik' => $validated['ketua_nik'] ?? null,
                'jenis' => $validated['jenis'] ?? 'UPZ Masjid',
                'telepon' => $validated['telepon'] ?? null,
                'handphone' => $validated['handphone'] ?? null,
                'email' => $validated['email'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'id_provinsi' => $validated['id_provinsi'] ?? null,
                'id_kabupaten' => $validated['id_kabupaten'] ?? null,
                'id_kecamatan' => $validated['id_kecamatan'] ?? null,
                'id_kelurahan' => $validated['id_kelurahan'] ?? null,
                'kode_pos' => $validated['kode_pos'] ?? null,
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            ]
        );

        $msg = $upzId ? 'Data UPZ berhasil diperbarui.' : 'UPZ baru berhasil ditambahkan.';
        return redirect()->route('manajemen.upz')->with('success', $msg);
    }

    public function destroy($id)
    {
        $upz = Upz::findOrFail($id);
        $upz->delete();

        return redirect()->route('manajemen.upz')->with('success', 'Data UPZ berhasil dihapus.');
    }

    public function storePengurus(Request $request, $upzId)
    {
        $upz = Upz::findOrFail($upzId);

        $validated = $request->validate([
            'person_id' => 'required|exists:persons,id',
            'pengurus_jabatan_id' => 'required|exists:pengurus_jabatans,id',
            'no_sk' => 'nullable|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        Pengurus::create([
            'lembaga_id' => $upz->id,
            'person_id' => $validated['person_id'],
            'jenis_pengurus' => 'Lembaga',
            'pengurus_jabatan_id' => $validated['pengurus_jabatan_id'],
            'no_sk' => $validated['no_sk'] ?? null,
            'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        return redirect()->route('manajemen.upz.detail', ['id' => $upzId, 'tab' => 'pengurus'])->with('success', 'Pengurus UPZ berhasil ditambahkan.');
    }

    public function destroyPengurus($upzId, $pengurusId)
    {
        $pengurus = Pengurus::where('id', $pengurusId)->where('lembaga_id', $upzId)->firstOrFail();
        $pengurus->delete();

        return redirect()->route('manajemen.upz.detail', ['id' => $upzId, 'tab' => 'pengurus'])->with('success', 'Pengurus UPZ berhasil dihapus.');
    }
}
