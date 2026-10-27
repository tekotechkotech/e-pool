<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mustahik;
use App\Models\Person;
use App\Models\Wilayah;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PengajuanBantuanImportTemplate;

class MustahikController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Mustahik::with(['person.provinsi', 'person.kabupaten', 'person.kecamatan', 'person.kelurahan', 'upzs'])->orderBy('id', 'desc');

        if ($search) {
            $query->whereHas('person', function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        $mustahiks = $query->paginate(10)->withQueryString();
        $provinsis = Wilayah::provinsis()->orderBy('nama')->get();

        return view('manajemen.mustahik', compact('mustahiks', 'search', 'provinsis'));
    }

    public function store(Request $request)
    {
        $mustahikId = $request->input('mustahik_id');
        $personId = $request->input('person_id');
        $isNewPerson = $request->has('is_new_person') && $request->input('is_new_person') == '1';

        if ($mustahikId) {
            $request->validate([
                'person_id' => 'required|exists:persons,id',
                'nik' => 'required|string|max:16|unique:persons,nik,' . $personId,
                'nomor_kk' => 'nullable|string|max:16',
                'nama' => 'required|string|max:255',
                'jenis_kelamin' => 'nullable|in:L,P',
                'tempat_lahir' => 'nullable|string|max:255',
                'tanggal_lahir' => 'nullable|date',
                'telepon' => 'nullable|string|max:50',
                'alamat' => 'nullable|string',
                'id_provinsi' => 'nullable|string',
                'id_kabupaten' => 'nullable|string',
                'id_kecamatan' => 'nullable|string',
                'id_kelurahan' => 'nullable|string',
                'kode_pos' => 'nullable|string|max:10',
                'pekerjaan' => 'nullable|string|max:255',
                'nim' => 'nullable|string|max:100',
                'keterangan' => 'nullable|string',
            ]);

            $person = Person::findOrFail($personId);
            $person->update([
                'nik' => $request->nik,
                'nomor_kk' => $request->nomor_kk,
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'telepon' => $request->telepon,
                'alamat' => $request->alamat,
                'id_provinsi' => $request->id_provinsi,
                'id_kabupaten' => $request->id_kabupaten,
                'id_kecamatan' => $request->id_kecamatan,
                'id_kelurahan' => $request->id_kelurahan,
                'kode_pos' => $request->kode_pos,
            ]);
        } else {
            if ($isNewPerson) {
                $request->validate([
                    'nik' => 'required|string|max:16|unique:persons,nik',
                    'nomor_kk' => 'nullable|string|max:16',
                    'nama' => 'required|string|max:255',
                    'jenis_kelamin' => 'nullable|in:L,P',
                    'tempat_lahir' => 'nullable|string|max:255',
                    'tanggal_lahir' => 'nullable|date',
                    'telepon' => 'nullable|string|max:50',
                    'alamat' => 'nullable|string',
                    'id_provinsi' => 'nullable|string',
                    'id_kabupaten' => 'nullable|string',
                    'id_kecamatan' => 'nullable|string',
                    'id_kelurahan' => 'nullable|string',
                    'kode_pos' => 'nullable|string|max:10',
                    'pekerjaan' => 'nullable|string|max:255',
                    'nim' => 'nullable|string|max:100',
                    'keterangan' => 'nullable|string',
                ]);

                $person = Person::create([
                    'nik' => $request->nik,
                    'nomor_kk' => $request->nomor_kk,
                    'nama' => $request->nama,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'tempat_lahir' => $request->tempat_lahir,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'telepon' => $request->telepon,
                    'alamat' => $request->alamat,
                    'id_provinsi' => $request->id_provinsi,
                    'id_kabupaten' => $request->id_kabupaten,
                    'id_kecamatan' => $request->id_kecamatan,
                    'id_kelurahan' => $request->id_kelurahan,
                    'kode_pos' => $request->kode_pos,
                ]);
                $personId = $person->id;
            } else {
                $request->validate([
                    'person_id' => 'required|exists:persons,id',
                    'pekerjaan' => 'nullable|string|max:255',
                    'nim' => 'nullable|string|max:100',
                    'keterangan' => 'nullable|string',
                ]);
            }
        }

        Mustahik::updateOrCreate(
            ['id' => $mustahikId],
            [
                'person_id' => $personId,
                'pekerjaan' => $request->pekerjaan,
                'nim' => $request->nim,
                'keterangan' => $request->keterangan,
            ]
        );

        $msg = $mustahikId ? 'Data mustahik berhasil diperbarui.' : 'Mustahik baru berhasil ditambahkan.';
        return redirect()->route('manajemen.mustahik')->with('success', $msg);
    }

    public function destroy($id)
    {
        $mustahik = Mustahik::findOrFail($id);
        $mustahik->delete();

        return redirect()->route('manajemen.mustahik')->with('success', 'Data mustahik berhasil dihapus.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new PengajuanBantuanImportTemplate('Individu'), 'template_import_mustahik.xlsx');
    }
}
