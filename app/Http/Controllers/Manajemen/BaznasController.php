<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Person;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Storage;

class BaznasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Pengurus::where('jenis_pengurus', 'Baznas')
            ->whereHas('person', function($q) use ($search) {
                if ($search) {
                    $q->where('nama', 'like', '%' . $search . '%')
                      ->orWhere('nik', 'like', '%' . $search . '%');
                }
            })
            ->with(['person', 'pengurusJabatan'])
            ->orderBy('id', 'desc');

        $penguruses = $query->paginate(10)->withQueryString();
        $persons = Person::orderBy('nama')->get();
        $jabatans = PengurusJabatan::orderBy('nama')->get();

        $info = [];
        if (Storage::disk('local')->exists('baznas_info.json')) {
            $info = json_decode(Storage::disk('local')->get('baznas_info.json'), true) ?: [];
        }

        $provinsis = Wilayah::provinsis()->orderBy('nama')->get(['id_wilayah', 'nama']);

        return view('manajemen.baznas', compact('penguruses', 'persons', 'jabatans', 'info', 'search', 'provinsis'));
    }

    public function updateInfo(Request $request)
    {
        $idProvinsi = $request->input('id_provinsi');
        $idKabupaten = $request->input('id_kabupaten');
        $idKecamatan = $request->input('id_kecamatan');
        $idKelurahan = $request->input('id_kelurahan');

        $provinsiNama = $idProvinsi ? (Wilayah::where('id_wilayah', $idProvinsi)->value('nama') ?? '') : '';
        $kabupatenNama = $idKabupaten ? (Wilayah::where('id_wilayah', $idKabupaten)->value('nama') ?? '') : '';
        $kecamatanNama = $idKecamatan ? (Wilayah::where('id_wilayah', $idKecamatan)->value('nama') ?? '') : '';
        $kelurahanNama = $idKelurahan ? (Wilayah::where('id_wilayah', $idKelurahan)->value('nama') ?? '') : '';

        $data = [
            'nama_baznas' => $request->input('nama_baznas'),
            'email' => $request->input('email'),
            'telepon' => $request->input('telepon'),
            'whatsapp' => $request->input('whatsapp'),
            'website' => $request->input('website'),
            'alamat' => $request->input('alamat'),
            'id_provinsi' => $idProvinsi,
            'id_kabupaten' => $idKabupaten,
            'id_kecamatan' => $idKecamatan,
            'id_kelurahan' => $idKelurahan,
            'provinsi' => $provinsiNama,
            'kabupaten' => $kabupatenNama,
            'kecamatan' => $kecamatanNama,
            'desa_kelurahan' => $kelurahanNama,
            'kode_pos' => $request->input('kode_pos'),
            'visi' => $request->input('visi'),
            'misi' => $request->input('misi'),
        ];

        Storage::disk('local')->put('baznas_info.json', json_encode($data, JSON_PRETTY_PRINT));

        return redirect()->route('manajemen.baznas')->with('success', 'Informasi BAZNAS berhasil diperbarui.');
    }

    public function saveInfo(Request $request)
    {
        return $this->updateInfo($request);
    }

    public function storePengurus(Request $request)
    {
        $pengurusId = $request->input('pengurus_id');

        $validated = $request->validate([
            'person_id' => 'required|exists:persons,id',
            'pengurus_jabatan_id' => 'required|exists:pengurus_jabatans,id',
            'no_sk' => 'nullable|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        Pengurus::updateOrCreate(
            ['id' => $pengurusId],
            [
                'person_id' => $validated['person_id'],
                'lembaga_id' => null,
                'jenis_pengurus' => 'Baznas',
                'pengurus_jabatan_id' => $validated['pengurus_jabatan_id'],
                'no_sk' => $validated['no_sk'] ?? null,
                'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
                'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            ]
        );

        $msg = $pengurusId ? 'Data pengurus BAZNAS berhasil diperbarui.' : 'Pengurus BAZNAS baru berhasil ditambahkan.';
        return redirect()->route('manajemen.baznas')->with('success', $msg);
    }

    public function destroyPengurus($id)
    {
        $pengurus = Pengurus::findOrFail($id);
        $pengurus->delete();

        return redirect()->route('manajemen.baznas')->with('success', 'Data pengurus BAZNAS berhasil dihapus.');
    }
}
