<?php

namespace App\Http\Controllers\Pengajuan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengajuanBantuan;
use App\Models\Program;
use App\Models\Subprogram;
use App\Models\Person;
use App\Models\Wilayah;
use Illuminate\Support\Facades\DB;

class PengajuanEditController extends Controller
{
    public function edit($id)
    {
        $pengajuan = PengajuanBantuan::with(['mustahik.person', 'upz', 'pengajuanMustahiks.program'])->findOrFail($id);
        $programs = Program::orderBy('nama')->get();
        $programId = $pengajuan->pengajuanMustahiks->first()?->program_id;
        $subprograms = $programId ? Subprogram::where('program_id', $programId)->orderBy('nama')->get() : collect();

        return view('pengajuan.edit', compact('pengajuan', 'programs', 'subprograms'));
    }

    public function update(Request $request, $id)
    {
        $pengajuan = PengajuanBantuan::findOrFail($id);

        $validated = $request->validate([
            'judul_surat' => 'required|string|max:255',
            'nomor_surat' => 'nullable|string|max:255',
            'tanggal_surat' => 'required|date',
            'tanggal_pelaksanaan' => 'nullable|date',
            'tanggal_diterima' => 'required|date',
            'uraian_permohonan' => 'nullable|string',
            'file_surat' => 'nullable|file|max:10240',
        ]);

        $pengajuan->update([
            'judul_surat' => $validated['judul_surat'],
            'nomor_surat' => $validated['nomor_surat'] ?? null,
            'tanggal_surat' => $validated['tanggal_surat'],
            'tanggal_pelaksanaan' => $validated['tanggal_pelaksanaan'] ?? null,
            'tanggal_diterima' => $validated['tanggal_diterima'],
            'uraian_permohonan' => $validated['uraian_permohonan'] ?: ($pengajuan->uraian_permohonan ?: $validated['judul_surat']),
        ]);

        if ($request->hasFile('file_surat')) {
            $path = $request->file('file_surat')->store('lampiran_pengajuan', 'public');
            $pengajuan->lampirans()->updateOrCreate(
                ['category' => 'surat'],
                ['name' => 'Surat Permohonan / Proposal', 'file_path' => $path]
            );
        }

        $returnRoute = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']) ? 'pengajuan.upz.show' : 'pengajuan.umum.show';
        return redirect()->route($returnRoute, ['pengajuan' => $pengajuan->id])->with('success', 'Data pengajuan berhasil diperbarui.');
    }

    public function editPemohon($id)
    {
        $pengajuan = PengajuanBantuan::with(['mustahik.person', 'upz'])->findOrFail($id);
        $person = $pengajuan->mustahik?->person;
        $provinsis = Wilayah::provinsis()->orderBy('nama')->get();

        return view('pengajuan.edit-pemohon', compact('pengajuan', 'person', 'provinsis'));
    }

    public function updatePemohon(Request $request, $id)
    {
        $pengajuan = PengajuanBantuan::with(['mustahik.person', 'lembaga'])->findOrFail($id);

        if ($pengajuan->mustahik && $pengajuan->mustahik->person) {
            $person = $pengajuan->mustahik->person;

            $nama = $request->p_nama ?: $request->nama;
            $nik = $request->p_nik ?: $request->nik;

            $request->validate([
                'p_nama' => 'nullable|string|max:255',
                'nama' => 'nullable|string|max:255',
                'p_nik' => 'nullable|string|max:20|unique:persons,nik,' . $person->id,
                'nik' => 'nullable|string|max:20|unique:persons,nik,' . $person->id,
                'p_nomor_kk' => 'nullable|string|max:20',
                'p_telepon' => 'nullable|string|max:50',
                'p_alamat' => 'nullable|string',
                'p_id_provinsi' => 'nullable|string',
                'p_id_kabupaten' => 'nullable|string',
                'p_id_kecamatan' => 'nullable|string',
                'p_id_kelurahan' => 'nullable|string',
                'p_kode_pos' => 'nullable|string|max:10',
            ]);

            $person->update([
                'nama' => $nama ?: $person->nama,
                'nik' => $nik ?: $person->nik,
                'nomor_kk' => $request->p_nomor_kk ?: $request->nomor_kk ?: $person->nomor_kk,
                'telepon' => $request->p_telepon ?: $request->telepon ?: $person->telepon,
                'alamat' => $request->p_alamat ?: $request->alamat ?: $person->alamat,
                'id_provinsi' => $request->p_id_provinsi ?: $request->id_provinsi ?: $person->id_provinsi,
                'id_kabupaten' => $request->p_id_kabupaten ?: $request->id_kabupaten ?: $person->id_kabupaten,
                'id_kecamatan' => $request->p_id_kecamatan ?: $request->id_kecamatan ?: $person->id_kecamatan,
                'id_kelurahan' => $request->p_id_kelurahan ?: $request->id_kelurahan ?: $person->id_kelurahan,
                'kode_pos' => $request->p_kode_pos ?: $request->kode_pos ?: $person->kode_pos,
            ]);

            if ($request->filled('p_pekerjaan')) {
                $pengajuan->mustahik->update(['pekerjaan' => $request->p_pekerjaan]);
            }

            // Update snapshot on pengajuan
            $pengajuan->update([
                'mustahik_nama_snapshot' => $person->nama,
                'mustahik_nik_snapshot' => $person->nik,
                'mustahik_kontak_snapshot' => $person->telepon ?: $person->handphone,
                'mustahik_alamat_snapshot' => $person->alamat_lengkap ?: $person->alamat,
            ]);
        } elseif ($pengajuan->lembaga) {
            $lembaga = $pengajuan->lembaga;
            $nama = $request->u_nama ?: $request->nama;

            $request->validate([
                'u_nama' => 'nullable|string|max:255',
                'nama' => 'nullable|string|max:255',
                'u_telepon' => 'nullable|string|max:50',
                'u_alamat' => 'nullable|string',
                'u_id_provinsi' => 'nullable|string',
                'u_id_kabupaten' => 'nullable|string',
                'u_id_kecamatan' => 'nullable|string',
                'u_id_kelurahan' => 'nullable|string',
                'u_kode_pos' => 'nullable|string|max:10',
            ]);

            $lembaga->update([
                'nama' => $nama ?: $lembaga->nama,
                'telepon' => $request->u_telepon ?: $request->telepon ?: $lembaga->telepon,
                'alamat' => $request->u_alamat ?: $request->alamat ?: $lembaga->alamat,
                'id_provinsi' => $request->u_id_provinsi ?: $request->id_provinsi ?: $lembaga->id_provinsi,
                'id_kabupaten' => $request->u_id_kabupaten ?: $request->id_kabupaten ?: $lembaga->id_kabupaten,
                'id_kecamatan' => $request->u_id_kecamatan ?: $request->id_kecamatan ?: $lembaga->id_kecamatan,
                'id_kelurahan' => $request->u_id_kelurahan ?: $request->id_kelurahan ?: $lembaga->id_kelurahan,
                'kode_pos' => $request->u_kode_pos ?: $request->kode_pos ?: $lembaga->kode_pos,
            ]);

            $pengajuan->update([
                'lembaga_nama_snapshot' => $lembaga->nama,
                'lembaga_kontak_snapshot' => $lembaga->telepon ?: $lembaga->handphone,
                'lembaga_alamat_snapshot' => $lembaga->alamat_lengkap ?: $lembaga->alamat,
            ]);
        }

        $returnRoute = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']) ? 'pengajuan.upz.show' : 'pengajuan.umum.show';
        return redirect()->route($returnRoute, ['pengajuan' => $pengajuan->id])->with('success', 'Data pemohon berhasil diperbarui.');
    }
}
