@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ $pengajuan->is_upz ? route('pengajuan.upz.show', $pengajuan->id) : route('pengajuan.umum.show', $pengajuan->id) }}" class="p-2 bg-white text-gray-600 hover:text-[#118243] hover:bg-[#eaf5ef] rounded-xl border border-slate-200 shadow-sm transition-all inline-flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Edit Berkas Pengajuan Bantuan</h1>
                <p class="text-sm text-slate-500">Ubah informasi surat, tanggal, uraian, dan berkas lampiran pengajuan #{{ $pengajuan->nomor_pengajuan }}</p>
            </div>
        </div>

        <a href="{{ route('pengajuan.edit-pemohon', $pengajuan->id) }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold rounded-xl text-xs shadow-sm flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Edit Data Pemohon / Lembaga
        </a>
    </div>

    <!-- Form Edit -->
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
        <form method="POST" action="{{ route('pengajuan.update', $pengajuan->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Perihal / Judul Surat Permohonan <span class="text-red-500">*</span></label>
                    <input type="text" name="judul_surat" value="{{ old('judul_surat', $pengajuan->judul_surat) }}" required placeholder="Contoh: Permohonan Bantuan Biaya Berobat / Operasi..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nomor Surat Masuk</label>
                    <input type="text" name="nomor_surat" value="{{ old('nomor_surat', $pengajuan->nomor_surat) }}" placeholder="001/..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Surat <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', $pengajuan->tanggal_surat ? $pengajuan->tanggal_surat->format('Y-m-d') : '') }}" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Pelaksanaan / Agenda</label>
                    <input type="date" name="tanggal_pelaksanaan" value="{{ old('tanggal_pelaksanaan', $pengajuan->tanggal_pelaksanaan ? $pengajuan->tanggal_pelaksanaan->format('Y-m-d') : '') }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Opsional (Rencana kegiatan/agenda)</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Diterima BAZNAS <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_diterima" value="{{ old('tanggal_diterima', $pengajuan->tanggal_diterima ? $pengajuan->tanggal_diterima->format('Y-m-d') : '') }}" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Uraian / Ringkasan Permohonan</label>
                    <textarea name="uraian_permohonan" rows="3" placeholder="Ringkasan atau keterangan berkas permohonan..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">{{ old('uraian_permohonan', $pengajuan->uraian_permohonan) }}</textarea>
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <x-file-dropzone 
                        name="file_surat" 
                        accept=".pdf,.jpg,.jpeg,.png"
                        label="Ganti Berkas Surat (PDF / JPG / PNG)"
                        :existing-file-url="$pengajuan->file_surat ? Storage::url($pengajuan->file_surat) : null"
                        existing-file-name="{{ $pengajuan->file_surat ? basename($pengajuan->file_surat) : '' }}"
                        help-text="Tarik & lepas file baru ke sini untuk menggantikan berkas surat saat ini (Maks 10 MB)"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ $pengajuan->is_upz ? route('pengajuan.upz.show', $pengajuan->id) : route('pengajuan.umum.show', $pengajuan->id) }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 font-bold rounded-xl text-xs hover:bg-slate-50">
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
