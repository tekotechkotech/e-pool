@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Import Data Muzakki UPZ</h1>
            <p class="text-sm text-slate-500">Unggah file CSV sesuai format resmi UPZ BAZNAS.</p>
        </div>
        <a href="{{ route('muzakki.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition-all">
            Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 text-xs text-emerald-800 space-y-2">
            <div class="font-bold text-sm text-emerald-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Panduan Format CSV
            </div>
            <p>1. Header CSV wajib memuat: <code>Nama, NPWP, NIK *, NIP, Tempat Lahir, tanggal lahir, jenis kelamin, pekerjaan, alamat korespondensi, alamat rumah*, alamat kantor, telepon, handphone *, email *, UPZ, zakat, catatan</code>.</p>
            <p>2. NIK, Nama, dan Alamat Rumah wajib diisi pada setiap baris data.</p>
            <p>3. Jika kolom UPZ diisi kode atau nama UPZ, sistem akan otomatis menautkannya.</p>
            <div class="pt-1">
                <a href="{{ route('muzakki.template') }}" class="inline-flex items-center text-emerald-700 font-bold hover:underline">
                    Unduh Contoh Template CSV Resmi &rarr;
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('muzakki.import.process') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Target UPZ Default (Opsional jika di CSV kosong)</label>
                <select name="lembaga_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    <option value="">-- Ambil Otomatis Dari Kolom CSV UPZ --</option>
                    @foreach($upzs as $u)
                        <option value="{{ $u->id }}">{{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih File CSV *</label>
                <input type="file" name="file" accept=".csv,text/csv,text/plain" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('muzakki.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-all">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-[#118243] hover:bg-[#0d6835] text-white font-semibold rounded-xl text-sm shadow-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Proses Import
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
