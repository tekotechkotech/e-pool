<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Wilayah;
use App\Models\Person;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $person = $user->person;
        
        $provinsis = Wilayah::provinsis()->get();
        $kabupatens = $person && $person->id_provinsi ? Wilayah::kabupatens($person->id_provinsi)->get() : collect();
        $kecamatans = $person && $person->id_kabupaten ? Wilayah::kecamatans($person->id_kabupaten)->get() : collect();
        $kelurahans = $person && $person->id_kecamatan ? Wilayah::kelurahans($person->id_kecamatan)->get() : collect();

        $penguruses = $person ? $person->penguruses()->with(['lembaga', 'pengurusJabatan'])->get() : collect();

        return view('profile', compact('user', 'person', 'provinsis', 'kabupatens', 'kecamatans', 'kelurahans', 'penguruses'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:255', Rule::unique('persons')->ignore($user->person_id)],
            'nomor_kk' => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'telepon' => ['nullable', 'string', 'max:255'],
            'handphone' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'id_provinsi' => ['nullable', 'string'],
            'id_kabupaten' => ['nullable', 'string'],
            'id_kecamatan' => ['nullable', 'string'],
            'id_kelurahan' => ['nullable', 'string'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        // Update User
        $userData = ['email' => $validated['email']];
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        $user->update($userData);

        // Update or Create Person
        $personData = [
            'nik' => $request->nik,
            'nomor_kk' => $request->nomor_kk,
            'nama' => $request->nama,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'telepon' => $request->telepon,
            'handphone' => $request->handphone,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'id_provinsi' => $request->id_provinsi,
            'id_kabupaten' => $request->id_kabupaten,
            'id_kecamatan' => $request->id_kecamatan,
            'id_kelurahan' => $request->id_kelurahan,
            'kode_pos' => $request->kode_pos,
        ];

        if ($user->person_id) {
            Person::where('id', $user->person_id)->update($personData);
        } else {
            $person = Person::create($personData);
            $user->update(['person_id' => $person->id]);
        }

        return redirect()->route('profile')->with('status', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('profile')->with('status', 'Kata sandi berhasil diubah.');
    }
}
