@extends('layouts.app')

@section('content')
<div class="space-y-6 pb-24 lg:pb-8" x-data="pengajuanUmumForm()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 sm:gap-4">
            <a href="{{ route('pengajuan.umum.index') }}" class="p-2 sm:p-2.5 bg-white text-gray-600 hover:text-[#118243] hover:bg-[#eaf5ef] rounded-xl border border-slate-200 shadow-xs transition-all inline-flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Buat Pengajuan Bantuan Umum</h1>
                <p class="text-xs sm:text-sm text-slate-500">Pendaftaran permohonan bantuan individu atau lembaga non-UPZ.</p>
            </div>
        </div>

        <!-- Tombol Batal Desktop -->
        <a href="{{ route('pengajuan.umum.index') }}" class="hidden sm:inline-flex px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
            Kembali ke Daftar
        </a>
    </div>

    <!-- Main Layout Grid: Form (Kiri) & Live Preview (Kanan Desktop) -->
    <form method="POST" action="{{ route('pengajuan.umum.store') }}" enctype="multipart/form-data" id="pengajuanForm" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        @csrf

        <!-- Hidden Inputs for Form Submission -->
        <input type="hidden" name="jenis_permohonan" :value="tipe_pemohon === 'lembaga' ? 'Lembaga' : 'Individu'">
        <input type="hidden" name="person_id" :value="person_id">
        <input type="hidden" name="lembaga_id" :value="lembaga_id">

        <!-- ==================== KOLOM UTAMA (KIRI) ==================== -->
        <div class="lg:col-span-8 space-y-6">

            <!-- 1. KATEGORI PEMOHON -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-[#118243] flex items-center justify-center text-xs font-bold">1</span>
                        Kategori Pemohon Bantuan
                    </h3>
                    <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md" x-text="tipe_pemohon === 'perorangan' ? 'Individu / Warga' : 'Organisasi / Lembaga'"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <!-- Opsi Perorangan -->
                    <label class="cursor-pointer border-2 rounded-2xl p-4 flex items-start gap-3.5 transition-all relative overflow-hidden" 
                           :class="tipe_pemohon === 'perorangan' ? 'border-[#118243] bg-[#eaf5ef]/40 shadow-xs ring-2 ring-[#118243]/20' : 'border-slate-200 bg-slate-50/40 hover:bg-slate-50 hover:border-slate-300'">
                        <input type="radio" name="tipe_pemohon" value="perorangan" x-model="tipe_pemohon" class="mt-1 text-[#118243] focus:ring-[#118243]">
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Perorangan / Individu
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">Bantuan biaya hidup, kesehatan, pendidikan perorangan, modal usaha individu.</p>
                        </div>
                    </label>

                    <!-- Opsi Lembaga -->
                    <label class="cursor-pointer border-2 rounded-2xl p-4 flex items-start gap-3.5 transition-all relative overflow-hidden" 
                           :class="tipe_pemohon === 'lembaga' ? 'border-[#118243] bg-[#eaf5ef]/40 shadow-xs ring-2 ring-[#118243]/20' : 'border-slate-200 bg-slate-50/40 hover:bg-slate-50 hover:border-slate-300'">
                        <input type="radio" name="tipe_pemohon" value="lembaga" x-model="tipe_pemohon" class="mt-1 text-[#118243] focus:ring-[#118243]">
                        <div class="space-y-0.5">
                            <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Lembaga / Yayasan Non-UPZ
                            </div>
                            <p class="text-xs text-slate-500 leading-relaxed">Pengurus masjid, musholla, ponpes, panti asuhan, sekolah, atau yayasan sosial.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 2. DATA IDENTITAS PEMOHON -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-[#118243] flex items-center justify-center text-xs font-bold">2</span>
                        <span>Identitas Pemohon</span>
                        <span class="text-xs text-slate-400 font-normal" x-text="tipe_pemohon === 'perorangan' ? '(Data Orang)' : '(Data Lembaga)'"></span>
                    </h3>
                </div>

                <!-- ================= SECTION PERORANGAN ================= -->
                <div x-show="tipe_pemohon === 'perorangan'" class="space-y-4">
                    <!-- Unified Smart Search / Autocomplete Box -->
                    <div class="bg-slate-50/90 rounded-2xl p-4 border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Cari Data Orang Terdaftar (People / Mustahik)
                            </label>
                            <span class="text-[11px] text-slate-400">Ketik NIK atau Nama</span>
                        </div>

                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="search_person_query" 
                                @input.debounce.250ms="searchPeople()" 
                                placeholder="Ketik NIK (16 digit) atau Nama orang untuk cek database..." 
                                class="w-full rounded-xl border border-slate-300 bg-white py-2.5 sm:py-3 pl-10 pr-10 text-xs sm:text-sm focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all shadow-xs">
                            
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <div x-show="is_searching_person" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="animate-spin h-4 w-4 text-[#118243]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Dropdown Hasil Pencarian -->
                        <div x-show="search_person_results.length > 0" @click.away="search_person_results = []" 
                             class="max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg divide-y divide-slate-100 z-30 relative mt-1">
                            <template x-for="p in search_person_results" :key="p.id">
                                <div @click="selectPerson(p)" class="p-3 hover:bg-[#eaf5ef] cursor-pointer transition-colors text-xs flex items-center justify-between group">
                                    <div>
                                        <div class="font-bold text-slate-900 group-hover:text-[#118243]" x-text="p.nama"></div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            NIK: <span class="font-semibold text-slate-700" x-text="p.nik || 'Tanpa NIK'"></span>
                                            <span x-show="p.nomor_kk"> • KK: <span x-text="p.nomor_kk"></span></span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5" x-text="p.alamat_lengkap || p.alamat || '-'"></div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#118243] text-white shadow-xs group-hover:bg-[#0d6835]">
                                        Pilih Data
                                    </span>
                                </div>
                            </template>
                        </div>

                        <!-- Notifikasi Interaktif: Data Orang Tidak Ditemukan -->
                        <div x-show="search_person_not_found && search_person_query.trim().length >= 2 && !is_searching_person" 
                             x-transition 
                             class="p-3.5 bg-blue-50/90 border border-blue-200 rounded-xl text-xs text-blue-950 space-y-2">
                            <div class="flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-full bg-blue-200 text-blue-800 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <div class="flex-1 space-y-0.5">
                                    <div class="font-bold text-blue-900">
                                        Data Tidak Ditemukan di Database
                                    </div>
                                    <p class="text-[11px] text-blue-800 leading-relaxed">
                                        Kata kunci <strong class="underline decoration-blue-400 font-semibold" x-text="'&ldquo;' + search_person_query + '&rdquo;'"></strong> belum pernah terdaftar sebelumnya.
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-blue-200/70">
                                <button type="button" 
                                        @click="useSearchAsNewPerson()" 
                                        class="px-3 py-1.5 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-lg text-xs shadow-xs inline-flex items-center gap-1.5 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Lanjutkan Input sebagai Pemohon Baru
                                </button>
                                <button type="button" 
                                        @click="search_person_not_found = false; search_person_query = ''" 
                                        class="px-2.5 py-1 text-xs text-slate-500 hover:text-slate-700 font-semibold bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                    Tutup
                                </button>
                            </div>
                        </div>

                        <!-- Badge Konfirmasi Data Terpilih -->
                        <div x-show="selected_person_name" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-900 animate-fadeIn">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <span class="text-slate-600 text-[11px]">Data Terpilih dari Database:</span>
                                    <div class="font-bold text-emerald-900 text-xs sm:text-sm" x-text="selected_person_name"></div>
                                </div>
                            </div>
                            <button type="button" @click="resetPersonForm()" class="px-2.5 py-1 text-xs font-bold text-red-600 hover:text-red-800 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                                Ganti / Reset
                            </button>
                        </div>

                        <!-- Banner Live Detection jika mengetik NIK / Nama yang sama -->
                        <div x-show="!person_id && detected_person" class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-3 animate-fadeIn">
                            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="flex-1 space-y-1">
                                <div class="font-bold text-amber-900">Terdeteksi Data Terdaftar di Database!</div>
                                <p class="text-[11px] text-amber-800">
                                    Ditemukan data orang: <strong x-text="detected_person ? detected_person.nama : ''"></strong> 
                                    (NIK: <span x-text="detected_person ? (detected_person.nik || '-') : ''"></span>) 
                                    di <span x-text="detected_person ? (detected_person.alamat_lengkap || detected_person.alamat || '-') : ''"></span>.
                                </p>
                                <div class="pt-1">
                                    <button type="button" @click="selectPerson(detected_person)" class="bg-[#118243] hover:bg-[#0d6835] text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow-xs inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Gunakan Data Orang Ini
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                        <!-- Warning Banner saat Edit Mode Data dari Database -->
                        <div x-show="person_id && person_edit_mode" class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-3 animate-fadeIn">
                            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="flex-1 space-y-1">
                                <div class="font-bold text-amber-900">Mode Edit Data Database Aktif</div>
                                <p class="text-[11px] text-amber-800">
                                    Anda sedang mengubah data orang yang sudah terdaftar di database. Perubahan akan mempengaruhi data asli di sistem.
                                </p>
                            </div>
                        </div>

                        <!-- Input Detail Formulir Perorangan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs" :class="person_id && !person_edit_mode ? 'opacity-60 pointer-events-none' : ''">
                        <!-- NIK Input dengan Live Counter -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-bold text-slate-700 uppercase text-[11px]">NIK (16 Digit) <span class="text-red-500">*</span></label>
                                <span class="text-[10px] font-semibold inline-flex items-center gap-1" 
                                      :class="p_nik.length === 16 ? 'text-emerald-600' : 'text-slate-400'">
                                    <template x-if="p_nik.length === 16">
                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                    <span x-text="p_nik.length === 16 ? '16 Digit Pas' : (p_nik.length + '/16 digit')"></span>
                                </span>
                            </div>
                            <input 
                                type="text" 
                                name="p_nik" 
                                x-model="p_nik" 
                                @input="onPersonInput()" 
                                inputmode="numeric" 
                                maxlength="16" 
                                placeholder="3301..." 
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"
                                :class="p_nik.length === 16 ? 'border-emerald-500 bg-emerald-50/20' : ''">
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Nama Lengkap Pemohon <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                name="p_nama" 
                                x-model="p_nama" 
                                @input="onPersonInput()" 
                                placeholder="Nama lengkap sesuai KTP" 
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Nomor KK -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Nomor KK</label>
                            <input 
                                type="text" 
                                name="p_nomor_kk" 
                                x-model="p_nomor_kk" 
                                inputmode="numeric" 
                                maxlength="16" 
                                placeholder="3301..." 
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Jenis Kelamin -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Jenis Kelamin</label>
                            <select name="p_jenis_kelamin" x-model="p_jenis_kelamin" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>

                        <!-- Telepon / WA -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">No. WhatsApp / HP</label>
                            <input 
                                type="text" 
                                name="p_telepon" 
                                x-model="p_telepon" 
                                inputmode="numeric" 
                                placeholder="08123456789" 
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Pekerjaan (Hanya tampil jika Pemohon = Penerima Langsung) -->
                        <div x-show="is_pemohon_penerima_langsung">
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Pekerjaan <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                name="p_pekerjaan" 
                                x-model="p_pekerjaan" 
                                @input="syncPekerjaanToMustahik()" 
                                placeholder="Contoh: Petani / Pedagang / Buruh" 
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Wilayah Berjenjang -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Provinsi</label>
                            <select name="p_id_provinsi" x-model="wilayahPerson.selectedProvinsi" @change="wilayahPerson.onProvinsiChange()" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="">-- Pilih Provinsi --</option>
                                <template x-for="p in wilayahPerson.provinsis" :key="p.id_wilayah">
                                    <option :value="p.id_wilayah" x-text="p.nama" :selected="wilayahPerson.selectedProvinsi === p.id_wilayah"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Kabupaten/Kota</label>
                            <select name="p_id_kabupaten" x-model="wilayahPerson.selectedKabupaten" @change="wilayahPerson.onKabupatenChange()" :disabled="!wilayahPerson.selectedProvinsi || wilayahPerson.loading.kabupaten" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="">-- Pilih Kabupaten --</option>
                                <template x-for="k in wilayahPerson.kabupatens" :key="k.id_wilayah">
                                    <option :value="k.id_wilayah" x-text="k.nama" :selected="wilayahPerson.selectedKabupaten === k.id_wilayah"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Kecamatan</label>
                            <select name="p_id_kecamatan" x-model="wilayahPerson.selectedKecamatan" @change="wilayahPerson.onKecamatanChange()" :disabled="!wilayahPerson.selectedKabupaten || wilayahPerson.loading.kecamatan" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="">-- Pilih Kecamatan --</option>
                                <template x-for="c in wilayahPerson.kecamatans" :key="c.id_wilayah">
                                    <option :value="c.id_wilayah" x-text="c.nama" :selected="wilayahPerson.selectedKecamatan === c.id_wilayah"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Desa/Kelurahan</label>
                            <select name="p_id_kelurahan" x-model="wilayahPerson.selectedKelurahan" @change="wilayahPerson.onKelurahanChange()" :disabled="!wilayahPerson.selectedKecamatan || wilayahPerson.loading.kelurahan" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="">-- Pilih Desa/Kelurahan --</option>
                                <template x-for="l in wilayahPerson.kelurahans" :key="l.id_wilayah">
                                    <option :value="l.id_wilayah" x-text="l.nama" :selected="wilayahPerson.selectedKelurahan === l.id_wilayah"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Kode Pos</label>
                            <input type="text" name="p_kode_pos" x-model="wilayahPerson.kodePos" placeholder="Kode Pos" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Alamat Domisili (Jl, No, RT/RW, Dusun)</label>
                            <textarea name="p_alamat" x-model="wilayahPerson.alamat" rows="2" placeholder="Contoh: Jl. Gatot Subroto No. 45, RT 02 / RW 03, Dusun Karanganyar" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"></textarea>
                        </div>
                    </div>

                    <!-- Edit/Simpan/Batal Buttons (Perorangan) -->
                    <div x-show="tipe_pemohon === 'perorangan' && person_id" class="pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2">
                        <button type="button" @click="togglePersonEditMode()" class="px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            <span x-text="person_edit_mode ? 'Batal' : 'Edit Data'"></span>
                        </button>
                        <button type="button" @click="savePersonEdit()" x-show="person_edit_mode" class="px-3 py-1.5 text-xs font-bold text-white bg-[#118243] hover:bg-[#0d6835] rounded-lg shadow-xs inline-flex items-center gap-1.5 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Simpan
                        </button>
                    </div>
                </div>

                <!-- ================= SECTION LEMBAGA ================= -->
                <div x-show="tipe_pemohon === 'lembaga'" class="space-y-4">
                    <!-- Unified Smart Search Lembaga -->
                    <div class="bg-slate-50/90 rounded-2xl p-4 border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Cari Lembaga Terdaftar (Masjid, Ponpes, Yayasan)
                            </label>
                            <span class="text-[11px] text-slate-400">Ketik Nama Lembaga / Ketua</span>
                        </div>

                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="search_lembaga_query" 
                                @input.debounce.250ms="searchLembaga()" 
                                placeholder="Ketik nama lembaga untuk cek database..." 
                                class="w-full rounded-xl border border-slate-300 bg-white py-2.5 sm:py-3 pl-10 pr-10 text-xs sm:text-sm focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all shadow-xs">
                            
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <div x-show="is_searching_lembaga" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg class="animate-spin h-4 w-4 text-[#118243]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Dropdown Hasil Pencarian Lembaga -->
                        <div x-show="search_lembaga_results.length > 0" @click.away="search_lembaga_results = []" 
                             class="max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg divide-y divide-slate-100 z-30 relative mt-1">
                            <template x-for="l in search_lembaga_results" :key="l.id">
                                <div @click="selectLembaga(l)" class="p-3 hover:bg-[#eaf5ef] cursor-pointer transition-colors text-xs flex items-center justify-between group">
                                    <div>
                                        <div class="font-bold text-slate-900 group-hover:text-[#118243]">
                                            <span x-text="l.nama"></span>
                                            <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold" x-text="l.jenis || '-'"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            Ketua: <span class="font-semibold text-slate-700" x-text="l.ketua_nama || '-'"></span>
                                            <span x-show="l.telepon"> • Kontak: <span x-text="l.telepon"></span></span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5" x-text="l.alamat_lengkap || l.alamat || '-'"></div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#118243] text-white shadow-xs group-hover:bg-[#0d6835]">
                                        Pilih Lembaga
                                    </span>
                                </div>
                            </template>
                        </div>

                        <!-- Notifikasi Interaktif: Lembaga Tidak Ditemukan -->
                        <div x-show="search_lembaga_not_found && search_lembaga_query.trim().length >= 2 && !is_searching_lembaga" 
                             x-transition 
                             class="p-3.5 bg-blue-50/90 border border-blue-200 rounded-xl text-xs text-blue-950 space-y-2">
                            <div class="flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-full bg-blue-200 text-blue-800 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <div class="flex-1 space-y-0.5">
                                     <div class="font-bold text-blue-900">
                                         Lembaga Belum Terdaftar di Database
                                     </div>
                                     <p class="text-[11px] text-blue-800 leading-relaxed">
                                         Lembaga dengan nama <strong class="underline decoration-blue-400 font-semibold" x-text="'&ldquo;' + search_lembaga_query + '&rdquo;'"></strong> belum ada di database.
                                     </p>
                                 </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-blue-200/70">
                                <button type="button" 
                                        @click="useSearchAsNewLembaga()" 
                                        class="px-3 py-1.5 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-lg text-xs shadow-xs inline-flex items-center gap-1.5 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Gunakan sebagai Nama Lembaga Baru
                                </button>
                                <button type="button" 
                                        @click="search_lembaga_not_found = false; search_lembaga_query = ''" 
                                        class="px-2.5 py-1 text-xs text-slate-500 hover:text-slate-700 font-semibold bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                    Tutup
                                </button>
                            </div>
                        </div>

                        <!-- Badge Konfirmasi Lembaga Terpilih -->
                        <div x-show="selected_lembaga_name" class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-900 animate-fadeIn">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <span class="text-slate-600 text-[11px]">Lembaga Terpilih:</span>
                                    <div class="font-bold text-emerald-900 text-xs sm:text-sm" x-text="selected_lembaga_name"></div>
                                </div>
                            </div>
                            <button type="button" @click="resetLembagaForm()" class="px-2.5 py-1 text-xs font-bold text-red-600 hover:text-red-800 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                                Ganti / Reset
                            </button>
                        </div>
                    </div>

                        <!-- Warning Banner saat Edit Mode Data dari Database -->
                        <div x-show="lembaga_id && lembaga_edit_mode" class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-3 animate-fadeIn">
                            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="flex-1 space-y-1">
                                <div class="font-bold text-amber-900">Mode Edit Data Database Aktif</div>
                                <p class="text-[11px] text-amber-800">
                                    Anda sedang mengubah data lembaga yang sudah terdaftar di database. Perubahan akan mempengaruhi data asli di sistem.
                                </p>
                            </div>
                        </div>

                        <!-- Input Detail Formulir Lembaga -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs" :class="lembaga_id && !lembaga_edit_mode ? 'opacity-60 pointer-events-none' : ''">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Nama Lembaga <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                name="l_nama" 
                                x-model="l_nama" 
                                @input="onLembagaInput()" 
                                placeholder="Contoh: Masjid Jami' Al-Ikhlas" 
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Jenis Lembaga</label>
                            <select name="l_jenis" x-model="l_jenis" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="Masjid">Masjid</option>
                                <option value="Musholla">Musholla</option>
                                <option value="Pesantren">Pesantren</option>
                                <option value="Madrasah / Sekolah">Madrasah / Sekolah</option>
                                <option value="Panti Asuhan">Panti Asuhan</option>
                                <option value="Yayasan Sosial">Yayasan Sosial</option>
                                <option value="Organisasi Kemasyarakatan">Organisasi Kemasyarakatan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Nama Ketua / Pimpinan</label>
                            <input 
                                type="text" 
                                name="l_ketua_nama" 
                                x-model="l_ketua_nama" 
                                placeholder="Nama ketua/pengurus penanggung jawab" 
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">No. Kontak / WA</label>
                            <input 
                                type="text" 
                                name="l_telepon" 
                                x-model="l_telepon" 
                                inputmode="numeric" 
                                placeholder="08123456789" 
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Wilayah Lembaga -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Provinsi</label>
                            <select name="l_id_provinsi" x-model="wilayahLembaga.selectedProvinsi" @change="wilayahLembaga.onProvinsiChange()" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="">-- Pilih Provinsi --</option>
                                <template x-for="p in wilayahLembaga.provinsis" :key="p.id_wilayah">
                                    <option :value="p.id_wilayah" x-text="p.nama" :selected="wilayahLembaga.selectedProvinsi === p.id_wilayah"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Kabupaten/Kota</label>
                            <select name="l_id_kabupaten" x-model="wilayahLembaga.selectedKabupaten" @change="wilayahLembaga.onKabupatenChange()" :disabled="!wilayahLembaga.selectedProvinsi || wilayahLembaga.loading.kabupaten" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="">-- Pilih Kabupaten --</option>
                                <template x-for="k in wilayahLembaga.kabupatens" :key="k.id_wilayah">
                                    <option :value="k.id_wilayah" x-text="k.nama" :selected="wilayahLembaga.selectedKabupaten === k.id_wilayah"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Kecamatan</label>
                            <select name="l_id_kecamatan" x-model="wilayahLembaga.selectedKecamatan" @change="wilayahLembaga.onKecamatanChange()" :disabled="!wilayahLembaga.selectedKabupaten || wilayahLembaga.loading.kecamatan" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="">-- Pilih Kecamatan --</option>
                                <template x-for="c in wilayahLembaga.kecamatans" :key="c.id_wilayah">
                                    <option :value="c.id_wilayah" x-text="c.nama" :selected="wilayahLembaga.selectedKecamatan === c.id_wilayah"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Desa/Kelurahan</label>
                            <select name="l_id_kelurahan" x-model="wilayahLembaga.selectedKelurahan" @change="wilayahLembaga.onKelurahanChange()" :disabled="!wilayahLembaga.selectedKecamatan || wilayahLembaga.loading.kelurahan" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="">-- Pilih Desa/Kelurahan --</option>
                                <template x-for="l in wilayahLembaga.kelurahans" :key="l.id_wilayah">
                                    <option :value="l.id_wilayah" x-text="l.nama" :selected="wilayahLembaga.selectedKelurahan === l.id_wilayah"></option>
                                </template>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Kode Pos</label>
                            <input type="text" name="l_kode_pos" x-model="wilayahLembaga.kodePos" placeholder="Kode Pos" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Alamat Lembaga -->
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Alamat Lembaga (Jl, No, RT/RW, Dusun)</label>
                            <textarea name="l_alamat" x-model="wilayahLembaga.alamat" rows="2" placeholder="Contoh: Jl. Ahmad Yani No. 10, RT 02 / RW 04, Dusun Karanganyar" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"></textarea>
                        </div>
                    </div>

                    <!-- Edit/Simpan/Batal Buttons (Lembaga) -->
                    <div x-show="tipe_pemohon === 'lembaga' && lembaga_id" class="pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2">
                        <button type="button" @click="toggleLembagaEditMode()" class="px-3 py-1.5 text-xs font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            <span x-text="lembaga_edit_mode ? 'Batal' : 'Edit Data'"></span>
                        </button>
                        <button type="button" @click="saveLembagaEdit()" x-show="lembaga_edit_mode" class="px-3 py-1.5 text-xs font-bold text-white bg-[#118243] hover:bg-[#0d6835] rounded-lg shadow-xs inline-flex items-center gap-1.5 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Simpan
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. SURAT PERMOHONAN & DOKUMEN -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-[#118243] flex items-center justify-center text-xs font-bold">3</span>
                        Informasi Surat Permohonan
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs">
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Perihal / Judul Surat Permohonan <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            name="judul_surat" 
                            x-model="judul_surat" 
                            required 
                            placeholder="Contoh: Permohonan Bantuan Biaya Berobat / Bantuan Modal Usaha" 
                            class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Nomor Surat Masuk (Jika Ada)</label>
                        <input 
                            type="text" 
                            name="nomor_surat" 
                            x-model="nomor_surat" 
                            placeholder="001/DS/..." 
                            class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Tanggal Surat <span class="text-red-500">*</span></label>
                        <input 
                            type="date" 
                            name="tanggal_surat" 
                            x-model="tanggal_surat" 
                            required 
                            class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Tanggal Pelaksanaan / Agenda</label>
                        <input 
                            type="date" 
                            name="tanggal_pelaksanaan" 
                            x-model="tanggal_pelaksanaan" 
                            class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Opsional (Rencana kegiatan/operasi)</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Tanggal Diterima BAZNAS <span class="text-red-500">*</span></label>
                        <input 
                            type="date" 
                            name="tanggal_diterima" 
                            x-model="tanggal_diterima" 
                            required 
                            class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Uraian Kebutuhan / Ringkasan Permohonan</label>
                        <textarea 
                            name="uraian_permohonan" 
                            x-model="uraian_permohonan" 
                            rows="2" 
                            placeholder="Jelaskan secara singkat kondisi pemohon, kebutuhan bantuan, atau latar belakang permohonan..." 
                            class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"></textarea>
                    </div>

                    <!-- File Upload Card dengan Drag & Drop -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-file-dropzone 
                            name="lampiran_surat" 
                            id="fileInput"
                            accept=".pdf,.jpg,.jpeg,.png"
                            label="Upload Berkas Surat / Proposal (PDF / Foto JPG / PNG)"
                            help-text="Tarik & lepas file ke sini atau pilih berkas/foto langsung dari kamera (Maks 10 MB)"
                            @file-selected="uploaded_filename = $event.detail.name; uploaded_filesize = $event.detail.sizeFormatted"
                            @file-cleared="uploaded_filename = ''; uploaded_filesize = ''"
                        />
                    </div>
                </div>
            </div>

            <!-- 4. PROGRAM & DAFTAR PENERIMA MANFAAT -->
            @include('pengajuan.components.penerima-manfaat-manager', [
                'mode' => 'client',
                'programs' => $programs,
                'subprograms' => $subprograms,
                'asnafs' => $asnafs,
                'provinsis' => $provinsis,
                'showSyncPemohon' => true,
                'stepNumber' => '4',
                'title' => 'Program BAZNAS & Penerima Manfaat',
            ])
        </div>

        <!-- ==================== SIDEBAR RINGKASAN LIVE (DESKTOP) ==================== -->
        <div class="lg:col-span-4 sticky top-6 space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Ringkasan Pengajuan
                    </h4>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Preview
                    </span>
                </div>

                <!-- Kartu Ringkasan Realtime -->
                <div class="space-y-3 text-xs">
                    <!-- Pemohon -->
                    <div class="p-3 bg-slate-50 rounded-xl space-y-1">
                        <div class="text-[11px] text-slate-400 font-semibold uppercase">Pemohon Bantuan</div>
                        <div class="font-bold text-slate-800 text-sm" x-text="tipe_pemohon === 'perorangan' ? (p_nama || 'Belum diisi') : (l_nama || 'Belum diisi')"></div>
                        <div class="text-[11px] text-slate-500" x-show="tipe_pemohon === 'perorangan' && p_nik" x-text="'NIK: ' + p_nik"></div>
                        <div class="text-[11px] text-slate-500" x-show="tipe_pemohon === 'lembaga' && l_jenis" x-text="'Jenis: ' + l_jenis"></div>
                    </div>

                    <!-- Surat Permohonan -->
                    <div class="p-3 bg-slate-50 rounded-xl space-y-1">
                        <div class="text-[11px] text-slate-400 font-semibold uppercase">Perihal Surat</div>
                        <div class="font-semibold text-slate-800 line-clamp-2" x-text="judul_surat || 'Belum mengisi perihal surat'"></div>
                        <div class="text-[11px] text-slate-500 flex items-center justify-between mt-1">
                            <span x-text="'Tgl: ' + tanggal_surat"></span>
                            <span :class="uploaded_filename ? 'text-emerald-600 font-bold' : 'text-slate-400'" class="inline-flex items-center gap-1">
                                <template x-if="uploaded_filename">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                </template>
                                <span x-text="uploaded_filename ? 'Berkas Ada' : 'Tanpa Berkas'"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Program Utama & Total Penerima -->
                    <div class="p-3 bg-slate-50 rounded-xl space-y-1">
                        <div class="text-[11px] text-slate-400 font-semibold uppercase">Program & Mustahik</div>
                        <div class="font-bold text-[#118243]" x-text="getPrimaryProgramSummary()"></div>
                        <div class="text-[11px] text-slate-600 flex items-center justify-between pt-1 border-t border-slate-200/60 mt-1">
                            <span>Total Penerima Manfaat:</span>
                            <span class="font-bold text-slate-800" x-text="penerimaManfaat.length + ' Orang'"></span>
                        </div>
                    </div>
                </div>

                <!-- Checklist Kelengkapan Form -->
                <div class="pt-2 border-t border-slate-100 space-y-1.5 text-[11px]">
                    <div class="flex items-center gap-1.5" :class="(tipe_pemohon === 'perorangan' ? (p_nama && p_nik) : l_nama) ? 'text-emerald-700 font-medium' : 'text-slate-400'">
                        <template x-if="(tipe_pemohon === 'perorangan' ? (p_nama && p_nik) : l_nama)">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="!(tipe_pemohon === 'perorangan' ? (p_nama && p_nik) : l_nama)">
                            <div class="w-3.5 h-3.5 rounded-full border border-slate-300 shrink-0"></div>
                        </template>
                        <span>Identitas Pemohon Lengkap</span>
                    </div>
                    <div class="flex items-center gap-1.5" :class="judul_surat ? 'text-emerald-700 font-medium' : 'text-slate-400'">
                        <template x-if="judul_surat">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="!judul_surat">
                            <div class="w-3.5 h-3.5 rounded-full border border-slate-300 shrink-0"></div>
                        </template>
                        <span>Perihal Surat Terisi</span>
                    </div>
                    <div class="flex items-center gap-1.5" :class="penerimaManfaat[0]?.program_id ? 'text-emerald-700 font-medium' : 'text-slate-400'">
                        <template x-if="penerimaManfaat[0]?.program_id">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="!penerimaManfaat[0]?.program_id">
                            <div class="w-3.5 h-3.5 rounded-full border border-slate-300 shrink-0"></div>
                        </template>
                        <span>Program BAZNAS Dipilih</span>
                    </div>
                </div>

                <!-- Tombol Submit Desktop -->
                <button type="submit" class="w-full py-3.5 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all text-xs flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan & Daftarkan Pengajuan
                </button>
            </div>
        </div>
    </form>

    <!-- ==================== MOBILE FLOATING BOTTOM BAR & SLIDE-UP DRAWER (LG:HIDDEN) ==================== -->
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 p-3 shadow-xl lg:hidden">
        <div class="flex items-center justify-between gap-2 max-w-lg mx-auto">
            <!-- Ringkasan Singkat & Tombol Buka Sheet -->
            <button type="button" @click="showMobileSummary = true" class="flex items-center gap-2 text-left flex-1 min-w-0 pr-2">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-[#118243] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div class="truncate">
                    <div class="font-bold text-xs text-slate-800 truncate" x-text="tipe_pemohon === 'perorangan' ? (p_nama || 'Pemohon Baru') : (l_nama || 'Lembaga Baru')"></div>
                    <div class="text-[10px] text-emerald-700 truncate font-semibold" x-text="getPrimaryProgramSummary()"></div>
                </div>
            </button>

            <!-- Tombol Simpan Mobile -->
            <button type="button" @click="document.getElementById('pengajuanForm').submit()" class="px-5 py-2.5 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl text-xs shadow-sm shrink-0 flex items-center gap-1.5 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Simpan
            </button>
        </div>
    </div>

    <!-- Mobile Slide-Up Bottom Sheet Modal -->
    <div x-show="showMobileSummary" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak 
         style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 lg:hidden">
        <div @click.away="showMobileSummary = false" 
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="translate-y-full sm:translate-y-4 sm:opacity-0"
             x-transition:enter-end="translate-y-0 sm:opacity-100"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="translate-y-0 sm:opacity-100"
             x-transition:leave-end="translate-y-full sm:translate-y-4 sm:opacity-0"
             class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md w-full p-5 space-y-4 shadow-2xl border border-slate-200 text-xs">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#118243]"></span>
                    Pratinjau Ringkasan Pengajuan
                </h3>
                <button type="button" @click="showMobileSummary = false" class="p-1 text-slate-400 hover:text-slate-600 text-xs font-bold flex items-center gap-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Tutup</span>
                </button>
            </div>

            <!-- Konten Ringkasan Mobile -->
            <div class="space-y-3 text-xs max-h-[60vh] overflow-y-auto pr-1">
                <div class="p-3 bg-slate-50 rounded-xl space-y-1">
                    <div class="text-[10px] text-slate-400 font-semibold uppercase">Pemohon Bantuan</div>
                    <div class="font-bold text-slate-800" x-text="tipe_pemohon === 'perorangan' ? (p_nama || 'Belum diisi') : (l_nama || 'Belum diisi')"></div>
                    <div class="text-[11px] text-slate-500" x-show="tipe_pemohon === 'perorangan' && p_nik" x-text="'NIK: ' + p_nik"></div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl space-y-1">
                    <div class="text-[10px] text-slate-400 font-semibold uppercase">Perihal Surat</div>
                    <div class="font-semibold text-slate-800" x-text="judul_surat || 'Belum mengisi perihal surat'"></div>
                    <div class="text-[10px] text-slate-500 flex justify-between pt-1">
                        <span x-text="'Tgl: ' + tanggal_surat"></span>
                        <span :class="uploaded_filename ? 'text-emerald-600 font-bold' : 'text-slate-400'" class="inline-flex items-center gap-1">
                            <template x-if="uploaded_filename">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            </template>
                            <span x-text="uploaded_filename ? 'File Terlampir' : 'Tanpa File'"></span>
                        </span>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl space-y-1">
                    <div class="text-[10px] text-slate-400 font-semibold uppercase">Program & Mustahik</div>
                    <div class="font-bold text-[#118243]" x-text="getPrimaryProgramSummary()"></div>
                    <div class="text-[11px] text-slate-600 flex justify-between pt-1 border-t border-slate-200 mt-1">
                        <span>Total Mustahik:</span>
                        <span class="font-bold text-slate-800" x-text="penerimaManfaat.length + ' Orang'"></span>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="button" @click="showMobileSummary = false; document.getElementById('pengajuanForm').submit()" class="w-full py-3 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl text-xs shadow-md">
                    Konfirmasi & Daftarkan Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function pengajuanUmumForm() {
    return {
        tipe_pemohon: 'perorangan',
        showMobileSummary: false,

        // Surat & Permohonan
        judul_surat: '',
        nomor_surat: '',
        tanggal_surat: '{{ date('Y-m-d') }}',
        tanggal_pelaksanaan: '',
        tanggal_diterima: '{{ date('Y-m-d') }}',
        uraian_permohonan: '',
        uploaded_filename: '',
        uploaded_filesize: '',

        // Smart Switch Sinkronisasi Pemohon = Penerima
        is_pemohon_penerima_langsung: true,

        // Perorangan state
        person_mode: 'baru',
        person_id: '',
        person_edit_mode: false,
        p_nik: '',
        p_nama: '',
        p_nomor_kk: '',
        p_jenis_kelamin: 'L',
        p_telepon: '',
        p_pekerjaan: '',
        p_tempat_lahir: '',
        p_tanggal_lahir: '',
        p_handphone: '',
        p_email: '',
        search_person_query: '',
        search_person_results: [],
        search_person_not_found: false,
        is_searching_person: false,
        selected_person_name: '',
        detected_person: null,
        debounce_timer_person: null,

        // Lembaga state
        lembaga_mode: 'baru',
        lembaga_id: '',
        lembaga_edit_mode: false,
        l_nama: '',
        l_jenis: 'Masjid',
        l_ketua_nama: '',
        l_ketua_nik: '',
        l_telepon: '',
        l_handphone: '',
        l_email: '',
        l_jumlah_anggota: '',
        search_lembaga_query: '',
        search_lembaga_results: [],
        search_lembaga_not_found: false,
        is_searching_lembaga: false,
        selected_lembaga_name: '',
        detected_lembaga: null,
        debounce_timer_lembaga: null,

        // Wilayah dengan default Jawa Tengah (33) & Cilacap (3301)
        wilayahPerson: window.wilayahSelector ? window.wilayahSelector({ provinsis: @json($provinsis ?? []), provinsi: '33', kabupaten: '3301' }) : {},
        wilayahLembaga: window.wilayahSelector ? window.wilayahSelector({ provinsis: @json($provinsis ?? []), provinsi: '33', kabupaten: '3301' }) : {},

        programs: @json($programs),
        all_subprograms: @json($subprograms),

        asnafs: @json($asnafs),

        penerimaManfaat: [],

        get mustahiks() {
            return this.penerimaManfaat;
        },

        async init() {
            if (this.wilayahPerson && typeof this.wilayahPerson.init === 'function') {
                await this.wilayahPerson.init();
            }
            if (this.wilayahLembaga && typeof this.wilayahLembaga.init === 'function') {
                await this.wilayahLembaga.init();
            }
            if (this.is_pemohon_penerima_langsung && this.tipe_pemohon === 'perorangan') {
                this.onTogglePenerimaLangsung();
            }
        },

        // Helper filter Sub Program berdasarkan Program
        getSubprograms(programId) {
            if (!programId) return [];
            return this.all_subprograms.filter(sp => String(sp.program_id) === String(programId));
        },

        onProgramChange(m) {
            m.subprogram_id = '';
        },

        // Ringkasan Teks Program untuk Live Preview
        getPrimaryProgramSummary() {
            const first = this.penerimaManfaat[0];
            if (!first || !first.program_id) return 'Belum memilih program';
            const prog = this.programs.find(p => String(p.id) === String(first.program_id));
            const sub = this.all_subprograms.find(s => String(s.id) === String(first.subprogram_id));
            const progName = prog ? prog.nama : '-';
            const subName = sub ? sub.nama : 'Pilih Sub Program';
            return `${progName} • ${subName}`;
        },

        // File Handler
        onFileChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.uploaded_filename = file.name;
                const sizeKb = Math.round(file.size / 1024);
                this.uploaded_filesize = sizeKb > 1024 ? (sizeKb / 1024).toFixed(1) + ' MB' : sizeKb + ' KB';
            }
        },
        clearFile() {
            this.uploaded_filename = '';
            this.uploaded_filesize = '';
            const input = document.getElementById('fileInput');
            if (input) input.value = '';
        },

        // Toggle Pemohon = Penerima
        onTogglePenerimaLangsung() {
            if (this.is_pemohon_penerima_langsung && this.tipe_pemohon === 'perorangan') {
                const pemohonAlamat = this.getFullAddress({
                    ...this.wilayahPerson.buildFullAddress(),
                    alamat: this.wilayahPerson.alamat || '',
                    kode_pos: this.wilayahPerson.kodePos,
                });
                if (this.penerimaManfaat.length === 0) {
                    this.penerimaManfaat.push({
                        id: 'pemohon_sync',
                        jenis: 'perorangan',
                        person_id: this.person_id || null,
                        nik: this.p_nik || '',
                        nama: this.p_nama || '',
                        pekerjaan: this.p_pekerjaan || '',
                        telepon: this.p_telepon || '',
                        summary: {
                            nama: this.p_nama || '',
                            nikOrKetua: this.p_nik || '',
                            alamat: pemohonAlamat
                        },
                program_id: '',
                subprogram_id: '',
                asnaf_id: '',
                keterangan: ''
                    });
                } else {
                    const first = this.penerimaManfaat[0];
                    if (first.id === 'pemohon_sync' || first.id.startsWith('temp_')) {
                        first.id = 'pemohon_sync';
                        first.jenis = 'perorangan';
                        first.person_id = this.person_id || null;
                        first.nik = this.p_nik || '';
                        first.nama = this.p_nama || '';
                        first.pekerjaan = this.p_pekerjaan || '';
                        first.telepon = this.p_telepon || '';
                        if (!first.summary) first.summary = {};
                        first.summary.nama = this.p_nama || '';
                        first.summary.nikOrKetua = this.p_nik || '';
                        first.summary.alamat = pemohonAlamat;
                    }
                }
            } else if (!this.is_pemohon_penerima_langsung && this.penerimaManfaat.length > 0) {
                if (this.penerimaManfaat[0].id === 'pemohon_sync') {
                    this.penerimaManfaat.shift();
                }
            }
        },

        // Sync pekerjaan from pemohon to penerimaManfaat[0] when toggle is ON
        syncPekerjaanToMustahik() {
            if (this.tipe_pemohon === 'perorangan' && this.is_pemohon_penerima_langsung && this.penerimaManfaat.length > 0) {
                this.penerimaManfaat[0].pekerjaan = this.p_pekerjaan;
            }
        },
        syncPekerjaanToPenerima() {
            this.syncPekerjaanToMustahik();
        },

        // Build full address from wilayah cascade
        getFullAddress(wilayah) {
            if (!wilayah) return '-';
            const parts = [];
            if (wilayah.alamat) parts.push(wilayah.alamat);
            if (wilayah.kelurahan_nama) parts.push('Desa/Kel. ' + wilayah.kelurahan_nama);
            if (wilayah.kecamatan_nama) parts.push('Kec. ' + wilayah.kecamatan_nama);
            if (wilayah.kabupaten_nama) parts.push(wilayah.kabupaten_nama);
            if (wilayah.provinsi_nama) parts.push('Prov. ' + wilayah.provinsi_nama);
            if (wilayah.kode_pos) parts.push(wilayah.kode_pos);
            return parts.length ? parts.join(', ') : (wilayah.alamat || '-');
        },

        // --- New Component Event Handlers ---

        // Add Penerima Selector
        showAddPenerimaSelector: false,
        closeAddPenerimaSelector() { this.showAddPenerimaSelector = false; },

        // Person Modal
        openPersonModal() {
            window.dispatchEvent(new CustomEvent('open-person-modal'));
        },
        closePersonModal() {
            window.dispatchEvent(new CustomEvent('close-person-modal'));
        },
        selectPersonFromModal(detail) {
            this.penerimaManfaat.push({
                id: 'temp_' + Date.now(),
                jenis: 'perorangan',
                person_id: detail.id,
                nik: detail.nik || '',
                nama: detail.nama || '',
                pekerjaan: detail.pekerjaan || '',
                telepon: detail.telepon || '',
                summary: detail.summary || {
                    nama: detail.nama,
                    nikOrKetua: detail.nik,
                    alamat: this.getFullAddress(detail)
                },
                program_id: '',
                subprogram_id: '',
                asnaf_id: '',
                keterangan: ''
            });
            this.closePersonModal();
        },

        // Lembaga Modal
        openLembagaModal() {
            window.dispatchEvent(new CustomEvent('open-lembaga-modal'));
        },
        closeLembagaModal() {
            window.dispatchEvent(new CustomEvent('close-lembaga-modal'));
        },
        selectLembagaFromModal(detail) {
            this.penerimaManfaat.push({
                id: 'temp_' + Date.now(),
                jenis: 'lembaga',
                lembaga_id: detail.id,
                nama: detail.nama || '',
                telepon: detail.telepon || '',
                summary: detail.summary || {
                    nama: detail.nama,
                    nikOrKetua: detail.ketua_nama ? `Ketua: ${detail.ketua_nama}` : (detail.jenis || 'Lembaga'),
                    alamat: detail.alamat || ''
                },
                program_id: '',
                subprogram_id: '',
                asnaf_id: '',
                keterangan: ''
            });
            this.closeLembagaModal();
        },

        // Edit Modal
        editModalIndex: -1,
        openEditModal(penerima, index) {
            this.editModalIndex = index;
            window.dispatchEvent(new CustomEvent('open-edit-modal', {
                detail: { penerima, index }
            }));
        },
        closeEditModal() {
            window.dispatchEvent(new CustomEvent('close-edit-modal'));
            this.editModalIndex = -1;
        },
        saveEdit(detail) {
            if (this.editModalIndex >= 0 && this.penerimaManfaat[this.editModalIndex]) {
                const pm = this.penerimaManfaat[this.editModalIndex];
                pm.summary = detail.summary;
                if (detail.person) {
                    pm.person_id = detail.person.id;
                    pm.nama = detail.person.nama;
                    pm.nik = detail.person.nik;
                    pm.pekerjaan = detail.person.pekerjaan;
                    pm.telepon = detail.person.telepon;
                }
                if (detail.lembaga) {
                    pm.lembaga_id = detail.lembaga.id;
                    pm.nama = detail.lembaga.nama;
                    pm.telepon = detail.lembaga.telepon;
                }
            }
            this.closeEditModal();
        },

        // Remove Penerima
        removePenerima(index) {
            if (this.penerimaManfaat.length > 1 || !this.is_pemohon_penerima_langsung) {
                this.penerimaManfaat.splice(index, 1);
            }
        },

        // Program Change Handler (for detail row)
        onPenerimaProgramChange(index) {
            this.penerimaManfaat[index].subprogram_id = '';
        },

        // Search People
        async searchPeople() {
            const q = (this.search_person_query || '').trim();
            if (q.length < 2) {
                this.search_person_results = [];
                this.search_person_not_found = false;
                return;
            }
            this.is_searching_person = true;
            this.search_person_not_found = false;
            try {
                const res = await fetch(`/api/search/people?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                this.search_person_results = Array.isArray(data) ? data : [];
                this.search_person_not_found = this.search_person_results.length === 0;
            } catch (e) {
                this.search_person_results = [];
                this.search_person_not_found = true;
            } finally {
                this.is_searching_person = false;
            }
        },

        useSearchAsNewPerson() {
            const q = (this.search_person_query || '').trim();
            if (/^\d{6,}$/.test(q)) {
                this.p_nik = q;
            } else {
                this.p_nama = q;
            }
            this.search_person_not_found = false;
            this.search_person_query = '';
            this.onPersonInput();
            const el = document.querySelector('input[name="p_nama"]');
            if (el) el.focus();
        },

        selectPerson(p) {
            this.person_mode = 'existing';
            this.person_id = p.id;
            this.p_nik = p.nik || '';
            this.p_nama = p.nama || '';
            this.p_nomor_kk = p.nomor_kk || '';
            this.p_jenis_kelamin = p.jenis_kelamin || 'L';
            this.p_telepon = p.telepon || '';
            this.p_pekerjaan = p.pekerjaan || '';
            this.selected_person_name = `${p.nama} (${p.nik || 'Tanpa NIK'})`;
            this.search_person_query = '';
            this.search_person_results = [];
            this.search_person_not_found = false;
            this.detected_person = null;

            if (this.wilayahPerson && typeof this.wilayahPerson.setValues === 'function') {
                this.wilayahPerson.setValues({
                    alamat: p.alamat || '',
                    id_provinsi: p.id_provinsi || '33',
                    id_kabupaten: p.id_kabupaten || '3301',
                    id_kecamatan: p.id_kecamatan || '',
                    id_kelurahan: p.id_kelurahan || '',
                    kode_pos: p.kode_pos || ''
                });
            }

            const fullAddress = this.getFullAddress({
                alamat: p.alamat,
                kelurahan_nama: p.kelurahan_nama,
                kecamatan_nama: p.kecamatan_nama,
                kabupaten_nama: p.kabupaten_nama,
                provinsi_nama: p.provinsi_nama,
                kode_pos: p.kode_pos
            });

            if (this.tipe_pemohon === 'perorangan' && this.is_pemohon_penerima_langsung && this.penerimaManfaat.length > 0) {
                this.penerimaManfaat[0].nama = p.nama;
                this.penerimaManfaat[0].nik = p.nik || '';
                this.penerimaManfaat[0].pekerjaan = p.pekerjaan || '';
                this.penerimaManfaat[0].telepon = p.telepon || '';
                if (this.penerimaManfaat[0].summary) {
                    this.penerimaManfaat[0].summary.nama = p.nama;
                    this.penerimaManfaat[0].summary.nikOrKetua = p.nik || '';
                    this.penerimaManfaat[0].summary.alamat = fullAddress;
                }
            }
        },

        onPersonInput() {
            clearTimeout(this.debounce_timer_person);
            const nikVal = (this.p_nik || '').trim();
            const namaVal = (this.p_nama || '').trim();

            if (this.tipe_pemohon === 'perorangan' && this.is_pemohon_penerima_langsung && this.penerimaManfaat.length > 0) {
                this.penerimaManfaat[0].nama = namaVal;
                this.penerimaManfaat[0].nik = nikVal;
                this.penerimaManfaat[0].pekerjaan = this.p_pekerjaan;
                this.penerimaManfaat[0].telepon = this.p_telepon;
                const fullAddress = this.getFullAddress({
                    ...this.wilayahPerson.buildFullAddress(),
                    alamat: this.wilayahPerson.alamat || '',
                    kode_pos: this.wilayahPerson.kodePos,
                });
                if (this.penerimaManfaat[0].summary) {
                    this.penerimaManfaat[0].summary.nama = namaVal;
                    this.penerimaManfaat[0].summary.nikOrKetua = nikVal;
                    this.penerimaManfaat[0].summary.alamat = fullAddress;
                }
            }

            if (nikVal.length < 4 && namaVal.length < 3) {
                this.detected_person = null;
                return;
            }

            this.debounce_timer_person = setTimeout(async () => {
                const query = nikVal.length >= 4 ? nikVal : namaVal;
                try {
                    const res = await fetch(`/api/search/people?q=${encodeURIComponent(query)}`);
                    const data = await res.json();
                    if (data && data.length > 0) {
                        const exactNik = nikVal ? data.find(p => p.nik && p.nik.toLowerCase() === nikVal.toLowerCase()) : null;
                        const matchName = namaVal ? data.find(p => p.nama && p.nama.toLowerCase().includes(namaVal.toLowerCase())) : null;
                        this.detected_person = exactNik || matchName || data[0];
                    } else {
                        this.detected_person = null;
                    }
                } catch (e) {
                    this.detected_person = null;
                }
            }, 300);
        },

        resetPersonForm() {
            this.person_id = '';
            this.p_nik = '';
            this.p_nama = '';
            this.p_nomor_kk = '';
            this.p_jenis_kelamin = 'L';
            this.p_telepon = '';
            this.p_pekerjaan = '';
            this.selected_person_name = '';
            this.search_person_query = '';
            this.search_person_results = [];
            this.search_person_not_found = false;
            this.detected_person = null;
            if (this.wilayahPerson && typeof this.wilayahPerson.setValues === 'function') {
                this.wilayahPerson.setValues({
                    alamat: '',
                    id_provinsi: '33',
                    id_kabupaten: '3301',
                    id_kecamatan: '',
                    id_kelurahan: '',
                    kode_pos: ''
                });
            }
        },

        // Search Lembaga
        async searchLembaga() {
            const q = (this.search_lembaga_query || '').trim();
            if (q.length < 2) {
                this.search_lembaga_results = [];
                this.search_lembaga_not_found = false;
                return;
            }
            this.is_searching_lembaga = true;
            this.search_lembaga_not_found = false;
            try {
                const res = await fetch(`/api/search/lembaga?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                this.search_lembaga_results = Array.isArray(data) ? data : [];
                this.search_lembaga_not_found = this.search_lembaga_results.length === 0;
            } catch (e) {
                this.search_lembaga_results = [];
                this.search_lembaga_not_found = true;
            } finally {
                this.is_searching_lembaga = false;
            }
        },

        useSearchAsNewLembaga() {
            this.l_nama = (this.search_lembaga_query || '').trim();
            this.search_lembaga_not_found = false;
            this.search_lembaga_query = '';
            this.onLembagaInput();
            const el = document.querySelector('input[name="l_nama"]');
            if (el) el.focus();
        },

        selectLembaga(l) {
            this.lembaga_mode = 'existing';
            this.lembaga_id = l.id;
            this.l_nama = l.nama || '';
            this.l_jenis = l.jenis || 'Masjid';
            this.l_ketua_nama = l.ketua_nama || '';
            this.l_telepon = l.telepon || '';
            this.selected_lembaga_name = `${l.nama} (${l.jenis || '-'})`;
            this.search_lembaga_query = '';
            this.search_lembaga_results = [];
            this.search_lembaga_not_found = false;
            this.detected_lembaga = null;

            if (this.wilayahLembaga && typeof this.wilayahLembaga.setValues === 'function') {
                this.wilayahLembaga.setValues({
                    alamat: l.alamat || '',
                    id_provinsi: l.id_provinsi || '33',
                    id_kabupaten: l.id_kabupaten || '3301',
                    id_kecamatan: l.id_kecamatan || '',
                    id_kelurahan: l.id_kelurahan || '',
                    kode_pos: l.kode_pos || ''
                });
            }
        },

        onLembagaInput() {
            clearTimeout(this.debounce_timer_lembaga);
            const namaVal = (this.l_nama || '').trim();

            if (namaVal.length < 3) {
                this.detected_lembaga = null;
                return;
            }

            this.debounce_timer_lembaga = setTimeout(async () => {
                try {
                    const res = await fetch(`/api/search/lembaga?q=${encodeURIComponent(namaVal)}`);
                    const data = await res.json();
                    if (data && data.length > 0) {
                        const match = data.find(l => l.nama && l.nama.toLowerCase().includes(namaVal.toLowerCase())) || data[0];
                        this.detected_lembaga = match;
                    } else {
                        this.detected_lembaga = null;
                    }
                } catch (e) {
                    this.detected_lembaga = null;
                }
            }, 300);
        },

        resetLembagaForm() {
            this.lembaga_id = '';
            this.l_nama = '';
            this.l_jenis = 'Masjid';
            this.l_ketua_nama = '';
            this.l_telepon = '';
            this.selected_lembaga_name = '';
            this.search_lembaga_query = '';
            this.search_lembaga_results = [];
            this.search_lembaga_not_found = false;
            this.detected_lembaga = null;
            if (this.wilayahLembaga && typeof this.wilayahLembaga.setValues === 'function') {
                this.wilayahLembaga.setValues({
                    alamat: '',
                    id_provinsi: '33',
                    id_kabupaten: '3301',
                    id_kecamatan: '',
                    id_kelurahan: '',
                    kode_pos: ''
                });
            }
        },

        // Edit Mode Methods
        togglePersonEditMode() {
            this.person_edit_mode = !this.person_edit_mode;
            if (!this.person_edit_mode) {
                // Cancel edit - reset form to original values from selected person
                this.resetPersonForm();
            }
        },
        savePersonEdit() {
            if (!this.person_id) return;
            
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('nik', this.p_nik);
            formData.append('nama', this.p_nama);
            formData.append('nomor_kk', this.p_nomor_kk);
            formData.append('jenis_kelamin', this.p_jenis_kelamin);
            formData.append('telepon', this.p_telepon);
            formData.append('pekerjaan', this.p_pekerjaan);
            formData.append('alamat', this.wilayahPerson.alamat);
            formData.append('id_provinsi', this.wilayahPerson.selectedProvinsi);
            formData.append('id_kabupaten', this.wilayahPerson.selectedKabupaten);
            formData.append('id_kecamatan', this.wilayahPerson.selectedKecamatan);
            formData.append('id_kelurahan', this.wilayahPerson.selectedKelurahan);
            formData.append('kode_pos', this.wilayahPerson.kodePos);

            fetch(`{{ route('pengajuan.umum.person.update', ['person' => ':id']) }}`.replace(':id', this.person_id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(res => res.json())
            .then(data => {
                if (data.id) {
                    this.selected_person_name = data.nama;
                    // Update snapshot if needed
                    this.person_id = data.id;
                }
                this.person_edit_mode = false;
            })
            .catch(err => {
                console.error('Failed to update person:', err);
                window.$alert('Gagal menyimpan perubahan data pemohon');
                this.person_edit_mode = false;
            });
        },
        toggleLembagaEditMode() {
            this.lembaga_edit_mode = !this.lembaga_edit_mode;
            if (!this.lembaga_edit_mode) {
                // Cancel edit - reset form to original values from selected lembaga
                this.resetLembagaForm();
            }
        },
        saveLembagaEdit() {
            if (!this.lembaga_id) return;
            
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('nama', this.l_nama);
            formData.append('jenis', this.l_jenis);
            formData.append('ketua_nama', this.l_ketua_nama);
            formData.append('telepon', this.l_telepon);
            formData.append('email', this.l_email);
            formData.append('alamat', this.wilayahLembaga.alamat);
            formData.append('id_provinsi', this.wilayahLembaga.selectedProvinsi);
            formData.append('id_kabupaten', this.wilayahLembaga.selectedKabupaten);
            formData.append('id_kecamatan', this.wilayahLembaga.selectedKecamatan);
            formData.append('id_kelurahan', this.wilayahLembaga.selectedKelurahan);
            formData.append('kode_pos', this.wilayahLembaga.kodePos);
            // pengurus array would need special handling if required
            formData.append('pengurus', JSON.stringify(this.pengurus || []));

            fetch(`{{ route('pengajuan.umum.lembaga.update', ['lembaga' => ':id']) }}`.replace(':id', this.lembaga_id), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(res => res.json())
            .then(data => {
                if (data.id) {
                    this.selected_lembaga_name = data.nama;
                    this.lembaga_id = data.id;
                }
                this.lembaga_edit_mode = false;
            })
            .catch(err => {
                console.error('Failed to update lembaga:', err);
                window.$alert('Gagal menyimpan perubahan data lembaga');
                this.lembaga_edit_mode = false;
            });
        },

        addMustahik() {
            this.penerimaManfaat.push({
                id: 'pm_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
                jenis: 'perorangan',
                person_id: null,
                nama: '',
                nik: '',
                pekerjaan: '',
                alamat: '',
                telepon: '',
                program_id: '',
                subprogram_id: '',
                keterangan: '',
                summary: {
                    nama: 'Penerima Baru',
                    nikOrKetua: '-',
                    alamat: '-'
                }
            });
        },
        removeMustahik(index) {
            this.removePenerima(index);
        }
    };
}
</script>
@endsection
