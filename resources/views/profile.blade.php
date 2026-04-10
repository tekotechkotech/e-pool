@extends('layouts.app')

@section('content')
<div>
    <div class="mb-8">
        <h1 class="text-2xl lg:text-3xl font-bold text-slate-800">Profil Saya</h1>
        <p class="text-slate-500 text-[15px] mt-2">Kelola informasi pribadi dan pengaturan akun Anda.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Informasi Akun -->
                <div class="bg-white rounded-[20px] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <x-heroicon-o-user class="w-5 h-5 text-[#118243]" />
                        Informasi Akun
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] transition-colors bg-white shadow-xs @error('email') border-rose-500 focus:ring-rose-500/20 @enderror">
                            @error('email')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>
                        
                        <div x-data="{ show: false }">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password Saat Ini</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="current_password" class="w-full pl-4 pr-11 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] transition-colors bg-white shadow-xs @error('current_password') border-rose-500 focus:ring-rose-500/20 @enderror" placeholder="Biarkan kosong jika tidak ingin mengubah">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition-colors">
                                    <x-heroicon-o-eye-slash x-show="!show" class="w-4 h-4" />
                                    <x-heroicon-o-eye x-show="show" x-cloak class="w-4 h-4" style="display: none" />
                                </button>
                            </div>
                            @error('current_password')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>
                        
                        <div class="hidden md:block"></div>

                        <div x-data="{ show: false }">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="password" class="w-full pl-4 pr-11 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] transition-colors bg-white shadow-xs @error('password') border-rose-500 focus:ring-rose-500/20 @enderror">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition-colors">
                                    <x-heroicon-o-eye-slash x-show="!show" class="w-4 h-4" />
                                    <x-heroicon-o-eye x-show="show" x-cloak class="w-4 h-4" style="display: none" />
                                </button>
                            </div>
                            @error('password')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div x-data="{ show: false }">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="password_confirmation" class="w-full pl-4 pr-11 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] transition-colors bg-white shadow-xs @error('password_confirmation') border-rose-500 focus:ring-rose-500/20 @enderror">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition-colors">
                                    <x-heroicon-o-eye-slash x-show="!show" class="w-4 h-4" />
                                    <x-heroicon-o-eye x-show="show" x-cloak class="w-4 h-4" style="display: none" />
                                </button>
                            </div>
                            @error('password_confirmation')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Informasi Pribadi -->
                <div class="bg-white rounded-[20px] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <x-heroicon-o-identification class="w-5 h-5 text-[#118243]" />
                        Informasi Pribadi
                    </h2>
                    
                    <x-form.person-fields
                        :person="$person"
                        :nama="old('nama', $person->nama ?? '')"
                        :nik="old('nik', $person->nik ?? '')"
                        :nomor-kk="old('nomor_kk', $person->nomor_kk ?? '')"
                        :tempat-lahir="old('tempat_lahir', $person->tempat_lahir ?? '')"
                        :tanggal-lahir="old('tanggal_lahir', $person && $person->tanggal_lahir ? $person->tanggal_lahir->format('Y-m-d') : '')"
                        :telepon="old('telepon', $person->telepon ?? '')"
                        :show-nomor-kk="true"
                        :show-pekerjaan="false"
                        :show-ttl="true"
                        label-nama="Nama Lengkap"
                        grid-class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    >
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error('jenis_kelamin') border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ old('jenis_kelamin', $person->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $person->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Handphone</label>
                            <input type="text" name="handphone" value="{{ old('handphone', $person->handphone ?? '') }}" placeholder="08123456789" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error('handphone') border-rose-500 focus:ring-rose-500/20 @enderror">
                            @error('handphone')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>
                    </x-form.person-fields>
                </div>

                <!-- Alamat & Wilayah -->
                <div class="bg-white rounded-[20px] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <x-heroicon-o-map-pin class="w-5 h-5 text-[#118243]" />
                        Alamat & Wilayah
                    </h2>
                    
                    <x-form.wilayah-select
                        :provinsis="$provinsis ?? []"
                        :provinsi="old('id_provinsi', $person->id_provinsi ?? '')"
                        :kabupaten="old('id_kabupaten', $person->id_kabupaten ?? '')"
                        :kecamatan="old('id_kecamatan', $person->id_kecamatan ?? '')"
                        :kelurahan="old('id_kelurahan', $person->id_kelurahan ?? '')"
                        :kode-pos="old('kode_pos', $person->kode_pos ?? '')"
                        :alamat="old('alamat', $person->alamat ?? '')"
                        :with-title="false"
                        grid-class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    />
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-[#118243] hover:bg-[#0e6d38] text-white font-semibold rounded-xl shadow-[0_4px_12px_rgba(17,130,67,0.25)] transition-all flex items-center gap-2">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            @if(count($penguruses) > 0)
                <div class="bg-white rounded-[20px] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <x-heroicon-o-briefcase class="w-5 h-5 text-[#118243]" />
                        Jabatan Pengurus
                    </h2>
                    
                    <div class="space-y-4">
                        @foreach($penguruses as $pengurus)
                            <div class="p-4 rounded-xl border border-slate-100 bg-[#f4f9f6]/50">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-slate-800">{{ $pengurus->pengurusJabatan->nama ?? 'Pengurus' }}</span>
                                    @if($pengurus->is_active)
                                        <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide text-green-700 uppercase bg-green-100 rounded-lg">Aktif</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide text-slate-500 uppercase bg-slate-100 rounded-lg">Nonaktif</span>
                                    @endif
                                </div>
                                <div class="text-sm text-slate-600 mb-1">
                                    <span class="font-medium text-slate-500">Lembaga:</span> {{ $pengurus->upz->nama ?? ($pengurus->jenis_pengurus === 'Baznas' ? 'BAZNAS' : '-') }}
                                </div>
                                <div class="text-sm text-slate-600 mb-1">
                                    <span class="font-medium text-slate-500">No SK:</span> {{ $pengurus->no_sk ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500 mt-2">
                                    {{ $pengurus->tanggal_mulai ? \Carbon\Carbon::parse($pengurus->tanggal_mulai)->translatedFormat('d M Y') : '-' }} s/d {{ $pengurus->tanggal_selesai ? \Carbon\Carbon::parse($pengurus->tanggal_selesai)->translatedFormat('d M Y') : 'Sekarang' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div class="bg-gradient-to-br from-[#118243] to-[#0e6d38] rounded-[20px] p-6 shadow-lg relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-white rounded-full opacity-10 pointer-events-none"></div>
                <div class="relative z-10 text-white">
                    <x-heroicon-o-information-circle class="w-8 h-8 mb-3 opacity-80" />
                    <h3 class="font-bold text-lg mb-2">Pusat Bantuan</h3>
                    <p class="text-sm text-green-50 mb-4 opacity-90">Jika Anda mengalami kendala dalam memperbarui data profil atau data pengurus tidak sesuai, silakan hubungi administrator sistem.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
