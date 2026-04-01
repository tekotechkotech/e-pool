<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lembaga;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Person;

class LembagaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Lembaga::where('is_upz', false)->with(['provinsi', 'kabupaten', 'kecamatan', 'kelurahan'])->withCount('penguruses')->orderBy('id', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('ketua_nama', 'like', '%' . $search . '%')
                  ->orWhere('jenis', 'like', '%' . $search . '%');
            });
        }

        $lembagas = $query->paginate(10)->withQueryString();

        return view('manajemen.lembaga', compact('lembagas', 'search'));
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

        $lembaga = Lembaga::where('is_upz', false)->with($relations)->findOrFail($id);

        $activeTab = $request->query('tab', 'pengurus');
        $persons = Person::orderBy('nama')->get();
        $jabatans = PengurusJabatan::orderBy('nama')->get();

        return view('manajemen.lembaga-detail', compact('lembaga', 'activeTab', 'persons', 'jabatans'));
    }

    public function store(Request $request)
    {
        $lembagaId = $request->input('lembaga_id');

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'ketua_nama' => 'nullable|string|max:255',
            'ketua_nik' => ['nullable', 'string', 'digits:16', 'regex:/^[0-9]{16}$/'],
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

        $lembaga = Lembaga::updateOrCreate(
            ['id' => $lembagaId],
            [
                'nama' => $validated['nama'],
                'ketua_nama' => $validated['ketua_nama'] ?? null,
                'ketua_nik' => $validated['ketua_nik'] ?? null,
                'jenis' => $validated['jenis'] ?? 'Masjid',
                'telepon' => $validated['telepon'] ?? null,
                'handphone' => $validated['handphone'] ?? null,
                'email' => $validated['email'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'id_provinsi' => $validated['id_provinsi'] ?? null,
                'id_kabupaten' => $validated['id_kabupaten'] ?? null,
                'id_kecamatan' => $validated['id_kecamatan'] ?? null,
                'id_kelurahan' => $validated['id_kelurahan'] ?? null,
                'kode_pos' => $validated['kode_pos'] ?? null,
                'is_upz' => false,
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            ]
        );

        $msg = $lembagaId ? 'Data lembaga berhasil diperbarui.' : 'Lembaga baru berhasil ditambahkan.';
        return redirect()->route('manajemen.lembaga')->with('success', $msg);
    }

    public function destroy($id)
    {
        $lembaga = Lembaga::where('is_upz', false)->findOrFail($id);
        $lembaga->delete();

        return redirect()->route('manajemen.lembaga')->with('success', 'Data lembaga berhasil dihapus.');
    }

    public function storePengurus(Request $request, $lembagaId)
    {
        $lembaga = Lembaga::where('is_upz', false)->findOrFail($lembagaId);

        $validated = $request->validate([
            'person_id' => 'required|exists:persons,id',
            'pengurus_jabatan_id' => 'required|exists:pengurus_jabatans,id',
            'no_sk' => 'nullable|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        Pengurus::create([
            'lembaga_id' => $lembaga->id,
            'person_id' => $validated['person_id'],
            'jenis_pengurus' => 'Lembaga',
            'pengurus_jabatan_id' => $validated['pengurus_jabatan_id'],
            'no_sk' => $validated['no_sk'] ?? null,
            'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
        ]);

        return redirect()->route('manajemen.lembaga.detail', ['id' => $lembagaId, 'tab' => 'pengurus'])->with('success', 'Pengurus Lembaga berhasil ditambahkan.');
    }

    public function destroyPengurus($lembagaId, $pengurusId)
    {
        $pengurus = Pengurus::where('id', $pengurusId)->where('lembaga_id', $lembagaId)->firstOrFail();
        $pengurus->delete();

        return redirect()->route('manajemen.lembaga.detail', ['id' => $lembagaId, 'tab' => 'pengurus'])->with('success', 'Pengurus Lembaga berhasil dihapus.');
    }
}
