@extends('layouts.guest')

@section('content')
<div class="w-full max-w-md mx-auto bg-transparent">
    <div class="bg-white rounded-[24px] shadow-[0_10px_40px_rgba(0,0,0,0.06)] border border-slate-100 w-full p-8 sm:p-12 text-center">
        <img class="h-[70px] mx-auto mb-6 object-contain" src="{{ asset('images/logo.webp') }}" alt="BAZNAS" />

        <h3 class="text-[22px] font-bold text-slate-800 mb-2">Pilih Jabatan Akses</h3>
        <p class="text-[14px] font-medium text-slate-500 leading-relaxed mb-5">
            Akun Anda terdaftar di beberapa posisi. Silakan pilih salah satu untuk sesi ini.
        </p>

        <div class="w-8 h-1 bg-[#118243] rounded-full mx-auto mt-2 mb-8"></div>

        <form method="POST" action="{{ route('select-jabatan.update') }}" class="text-left">
            @csrf
            <div class="mb-6">
                <label for="pengurus_id" class="block text-[14px] font-semibold text-slate-700 mb-2">Jabatan / Posisi</label>
                <select id="pengurus_id" name="pengurus_id" required
                    class="w-full px-4 py-3.5 text-[15px] text-slate-800 bg-white border border-slate-300 shadow-xs rounded-xl focus:outline-none focus:border-[#118243] focus:ring-4 focus:ring-[#118243]/10 transition-all duration-200">
                    <option value="">-- Pilih Jabatan --</option>
                    @foreach($penguruses as $p)
                        <option value="{{ $p->id }}">{{ $p->pengurusJabatan->nama ?? 'Tidak Ada Jabatan' }}</option>
                    @endforeach
                </select>
                @error('pengurus_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <button type="submit"
                class="w-full bg-[#118243] hover:bg-[#0d6835] text-white font-semibold py-3.5 px-4 rounded-xl shadow-[0_4px_12px_rgba(17,130,67,0.25)] transition-all duration-200 flex justify-center items-center h-[50px]">
                <span>Konfirmasi & Masuk</span>
            </button>
        </form>
    </div>
</div>
@endsection
