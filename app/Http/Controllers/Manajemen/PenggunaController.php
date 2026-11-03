<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Person;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Person::with(['user', 'mustahik.upzs', 'penguruses.upz', 'penguruses.pengurusJabatan', 'provinsi', 'kabupaten', 'kecamatan', 'kelurahan'])->orderBy('id', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        $persons = $query->paginate(10)->withQueryString();

        return view('manajemen.pengguna', compact('persons', 'search'));
    }

    public function store(Request $request)
    {
        $personId = $request->input('person_id');

        $validated = $request->validate([
            'nik' => ['required', 'string', 'digits:16', 'regex:/^[0-9]{16}$/', Rule::unique('persons')->ignore($personId)],
            'nomor_kk' => ['nullable', 'string', 'digits:16', 'regex:/^[0-9]{16}$/'],
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'telepon' => 'nullable|string|max:20',
            'handphone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'nullable|string',
            'id_provinsi' => 'nullable|string',
            'id_kabupaten' => 'nullable|string',
            'id_kecamatan' => 'nullable|string',
            'id_kelurahan' => 'nullable|string',
            'kode_pos' => 'nullable|string|max:10',
        ]);

        $person = Person::updateOrCreate(
            ['id' => $personId],
            $validated
        );

        $msg = $personId ? 'Data person berhasil diperbarui.' : 'Data person baru berhasil ditambahkan.';
        return redirect()->route('manajemen.pengguna')->with('success', $msg);
    }

    public function destroy($id)
    {
        $person = Person::findOrFail($id);
        $person->delete();

        return redirect()->route('manajemen.pengguna')->with('success', 'Data person berhasil dihapus.');
    }
}
