@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tambah Muzakki Baru</h1>
            <p class="text-sm text-slate-500">Input data perorangan muzakki / munfiq ke dalam sistem.</p>
        </div>
        <a href="{{ route('muzakki.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition-all">
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('muzakki.store') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        @csrf

        <div class="border-b border-slate-100 pb-4">
            <h2 class="text-base font-bold text-slate-900">1. Data Pribadi</h2>
            <p class="text-xs text-slate-500">Informasi dasar perorangan sesuai KTP.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                <input type="text" name="nama" value="{{ old('nama') }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('nama') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('nama')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">NIK (16 Digit) *</label>
                <input type="text" name="nik" value="{{ old('nik') }}" inputmode="numeric" maxlength="16" minlength="16" pattern="[0-9]{16}" title="Harus 16 digit angka" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('nik') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('nik')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('jenis_kelamin') border-rose-500 focus:ring-rose-500/20 @enderror">
                    <option value="">-- Pilih --</option>
                    <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Pria / Laki-laki</option>
                    <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Wanita / Perempuan</option>
                </select>
                @error('jenis_kelamin')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pekerjaan</label>
                <input type="text" name="pekerjaan" value="{{ old('pekerjaan') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('pekerjaan') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('pekerjaan')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('tempat_lahir') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('tempat_lahir')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('tanggal_lahir') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('tanggal_lahir')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Handphone / WA</label>
                <input type="text" name="handphone" value="{{ old('handphone') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('handphone') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('handphone')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('email') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('email')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Rumah (Sesuai KTP)</label>
                <textarea name="alamat" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('alamat') border-rose-500 focus:ring-rose-500/20 @enderror">{{ old('alamat') }}</textarea>
                @error('alamat')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="border-b border-slate-100 pb-4 pt-2">
            <h2 class="text-base font-bold text-slate-900">2. Identitas Muzakki & UPZ</h2>
            <p class="text-xs text-slate-500">Penetapan UPZ dan nomor registrasi zakat.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Pengumpul Zakat (UPZ)</label>
                <select name="lembaga_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('lembaga_id') border-rose-500 focus:ring-rose-500/20 @enderror">
                    <option value="">-- Tanpa UPZ (Muzakki Langsung) --</option>
                    @foreach($upzs as $u)
                        <option value="{{ $u->id }}" {{ old('lembaga_id') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                    @endforeach
                </select>
                @error('lembaga_id')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">NPWZ (Nomor Pokok Wajib Zakat)</label>
                <input type="text" name="npwz" value="{{ old('npwz') }}" placeholder="Contoh: 317720010003429" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('npwz') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('npwz')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">NPWP</label>
                <input type="text" name="npwp" value="{{ old('npwp') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('npwp') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('npwp')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">NIP (Pegawai/ASN)</label>
                <input type="text" name="nip" value="{{ old('nip') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('nip') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('nip')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Zakat Rutin</label>
                <input type="text" name="jenis_zakat" value="{{ old('jenis_zakat', 'zakat profesi') }}" placeholder="zakat profesi / zakat maal" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('jenis_zakat') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('jenis_zakat')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Registrasi</label>
                <input type="date" name="tanggal_registrasi" value="{{ old('tanggal_registrasi', date('Y-m-d')) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('tanggal_registrasi') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('tanggal_registrasi')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Korespondensi</label>
                <input type="text" name="alamat_korespondensi" value="{{ old('alamat_korespondensi') }}" placeholder="rumah / kantor / alamat khusus" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('alamat_korespondensi') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error('alamat_korespondensi')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Kantor / Instansi</label>
                <textarea name="alamat_kantor" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('alamat_kantor') border-rose-500 focus:ring-rose-500/20 @enderror">{{ old('alamat_kantor') }}</textarea>
                @error('alamat_kantor')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                <textarea name="keterangan" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 @error('keterangan') border-rose-500 focus:ring-rose-500/20 @enderror">{{ old('keterangan') }}</textarea>
                @error('keterangan')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('muzakki.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-all">Batal</a>
            <button type="submit" class="px-6 py-2.5 bg-[#118243] hover:bg-[#0d6835] text-white font-semibold rounded-xl text-sm shadow-sm transition-all">Simpan Muzakki</button>
        </div>
    </form>
</div>
@endsection
