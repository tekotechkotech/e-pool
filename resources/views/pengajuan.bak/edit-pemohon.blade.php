@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('pengajuan.edit', $pengajuan->id) }}" class="p-2 bg-white text-gray-600 hover:text-[#118243] hover:bg-[#eaf5ef] rounded-xl border border-slate-200 shadow-sm transition-all inline-flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Edit Identitas Pemohon</h1>
                <p class="text-sm text-slate-500">Perbarui rincian identitas pemohon utama untuk berkas #{{ $pengajuan->nomor_pengajuan }}</p>
            </div>
        </div>
    </div>

    <!-- Form Edit Pemohon -->
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
        <form method="POST" action="{{ route('pengajuan.update-pemohon', $pengajuan->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @if($pengajuan->jenis_permohonan == 'Individu' || $pengajuan->jenis_permohonan == 'Umum')
                @php $person = $pengajuan->mustahik?->person; @endphp
                <div class="space-y-4">
                    <x-form.person-fields
                        prefix="p_"
                        :person="$person"
                        :nik="old('p_nik', $person?->nik ?? $pengajuan->mustahik_nik_snapshot)"
                        :nama="old('p_nama', $person?->nama ?? $pengajuan->mustahik_nama_snapshot)"
                        :nomor-kk="old('p_nomor_kk', $person?->nomor_kk)"
                        :pekerjaan="old('p_pekerjaan', $pengajuan->mustahik?->pekerjaan)"
                        :telepon="old('p_telepon', $person?->telepon ?? $person?->handphone)"
                    />

                    <x-form.wilayah-select
                        prefix="p_"
                        :provinsis="$provinsis ?? []"
                        :provinsi="old('p_id_provinsi', $person?->id_provinsi)"
                        :kabupaten="old('p_id_kabupaten', $person?->id_kabupaten)"
                        :kecamatan="old('p_id_kecamatan', $person?->id_kecamatan)"
                        :kelurahan="old('p_id_kelurahan', $person?->id_kelurahan)"
                        :kode-pos="old('p_kode_pos', $person?->kode_pos)"
                        :alamat="old('p_alamat', $person?->alamat)"
                        title="Alamat & Wilayah Pemohon"
                    />
                </div>
            @else
                @php $upz = $pengajuan->upz ?? $pengajuan->lembaga; @endphp
                <div class="space-y-4">
                    <x-form.lembaga-fields
                        prefix="u_"
                        :lembaga="$upz"
                        :nama="old('u_nama', $upz?->nama ?? $pengajuan->lembaga_nama_snapshot)"
                        :telepon="old('u_telepon', $upz?->telepon ?? $upz?->handphone)"
                    />

                    <x-form.wilayah-select
                        prefix="u_"
                        :provinsis="$provinsis ?? []"
                        :provinsi="old('u_id_provinsi', $upz?->id_provinsi)"
                        :kabupaten="old('u_id_kabupaten', $upz?->id_kabupaten)"
                        :kecamatan="old('u_id_kecamatan', $upz?->id_kecamatan)"
                        :kelurahan="old('u_id_kelurahan', $upz?->id_kelurahan)"
                        :kode-pos="old('u_kode_pos', $upz?->kode_pos)"
                        :alamat="old('u_alamat', $upz?->alamat)"
                        title="Alamat & Wilayah UPZ / Lembaga"
                        grid-class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                    />
                </div>
            @endif

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('pengajuan.edit', $pengajuan->id) }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 font-bold rounded-xl text-xs hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl text-xs shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
