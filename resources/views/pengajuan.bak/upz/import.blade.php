@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('pengajuan.upz.index') }}" class="p-2 bg-white text-gray-600 hover:text-[#118243] hover:bg-[#eaf5ef] rounded-xl border border-slate-200 shadow-sm transition-all inline-flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Import Rekomendasi Mustahik UPZ</h1>
                <p class="text-sm text-slate-500">Unggah berkas excel rekap data mustahik yang direkomendasikan oleh UPZ.</p>
            </div>
        </div>

        <div>
            <a href="{{ route('pengajuan.upz.template') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold hover:bg-emerald-100 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Unduh Format Excel
            </a>
        </div>
    </div>

    <!-- Upload Card -->
    <div class="bg-white rounded-2xl p-8 border border-slate-100 shadow-sm">
        <form method="POST" action="{{ route('pengajuan.upz.import.store') }}" enctype="multipart/form-data" class="max-w-xl mx-auto space-y-6">
            @csrf

            <div class="space-y-2">
                <label class="block font-bold text-slate-700 text-xs uppercase">Pilih UPZ Pengaju <span class="text-red-500">*</span></label>
                <select name="upz_id" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    <option value="">-- Pilih UPZ --</option>
                    @foreach(\App\Models\Upz::orderBy('nama')->get() as $u)
                        <option value="{{ $u->id }}">{{ $u->nama }} ({{ $u->jenis }})</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-slate-700 text-xs uppercase">Judul / Perihal Rekomendasi <span class="text-red-500">*</span></label>
                <input type="text" name="judul_surat" required placeholder="Contoh: Rekomendasi Bantuan Mustahik Tahap I" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
            </div>

            <div class="space-y-2">
                <label class="block font-bold text-slate-700 text-xs uppercase">Program BAZNAS <span class="text-red-500">*</span></label>
                <select name="program_id" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    <option value="">-- Pilih Program --</option>
                    @foreach(\App\Models\Program::orderBy('sort_order')->get() as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-file-dropzone 
                    name="import_file" 
                    accept=".xlsx,.xls,.csv" 
                    :required="true"
                    icon="excel" 
                    label="File Excel Rekomendasi (.xlsx / .csv)"
                    help-text="Tarik & lepas file Excel sesuai template atau klik untuk pilih berkas (Maks 10 MB)"
                />
                <p class="text-[11px] text-slate-400 mt-1.5">Pastikan kolom NIK, Nama, dan Keterangan terisi sesuai format template.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('pengajuan.upz.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-600 font-bold rounded-xl text-xs hover:bg-slate-50">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl text-xs shadow-sm">Mulai Import</button>
            </div>
        </form>
    </div>
</div>
@endsection
