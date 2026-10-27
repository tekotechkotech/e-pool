@extends('layouts.survey')

@section('content')
<div class="min-h-screen bg-[#F4F8F5] font-sans text-[#0F172A] pb-24" x-data="{
    activeModal: '{{ session('active_modal', '') }}',
    hk_step: 1,
    as_step: 1,
    final_hasil: '{{ $survey->hasil_akhir ?? 'layak' }}',
    final_catatan: '{{ $survey->catatan ?? '' }}'
}">
    <!-- TOP STICKY HEADER (MOBILE-FIRST) -->
    <header class="sticky top-0 z-40 bg-[#118243] text-white shadow-md border-b border-emerald-800">
        <div class="max-w-4xl mx-auto px-4 py-3.5 sm:py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white/15 flex items-center justify-center text-white shadow-inner shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold tracking-tight text-white leading-tight">e-Pool BAZNAS</h1>
                    <p class="text-[11px] sm:text-xs text-emerald-100 font-medium">Formulir Verifikasi Lapangan Mustahik</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 bg-[#0d6935] border border-emerald-400/40 rounded-lg text-[10px] sm:text-xs font-bold text-emerald-100 uppercase tracking-wider">
                    ID #{{ $survey->pengajuanMustahik?->pengajuan_id ?? '-' }}
                </span>
            </div>
        </div>
    </header>

    <!-- STICKY PANDUAN CEPAT 4 LANGKAH INTERAKTIF (MOBILE-FIRST) -->
    @php
        $isSurveyorLengkap = !empty(trim($survey->surveyor ?? '')) && !empty(trim($survey->nohp_surveyor ?? '')) && !empty($survey->tanggal_survey);
        $isHkDone = !empty($survey->hadKifayah);
        $isAsesmenDone = !empty($survey->asesmen);
        $isFinalDone = !empty($survey->hasil_akhir);
        $completedSteps = ($isSurveyorLengkap ? 1 : 0) + ($isHkDone ? 1 : 0) + ($isAsesmenDone ? 1 : 0) + ($isFinalDone ? 1 : 0);
    @endphp

    <!-- STICKY SUB-BAR (DI BAWAH HEADER) -->
    <div class="sticky top-[57px] sm:top-[65px] z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs">
        <div class="max-w-4xl mx-auto px-2.5 sm:px-6 py-2">
            <div class="flex items-center justify-between mb-1.5 px-1">
                <span class="text-[10px] sm:text-[11px] font-bold text-slate-600 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                    <span>Alur Survey</span>
                    <span class="text-slate-400">•</span>
                    <span class="text-slate-500 font-normal">Ketuk langkah untuk langsung menuju form</span>
                </span>
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $completedSteps === 4 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    <span>{{ $completedSteps }}/4 Selesai</span>
                    @if($isFinalDone)
                        <svg class="w-3 h-3 text-emerald-800 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </span>
            </div>

            <!-- 4 TILES GRID INTERAKTIF DENGAN SCROLL OTOMATIS -->
            <div class="grid grid-cols-4 gap-1.5 sm:gap-2 text-center text-xs select-none">
                <!-- Step 1: Petugas -->
                <a href="#section-petugas" 
                   class="group p-1.5 sm:p-2 rounded-xl border transition-all flex flex-col items-center justify-center active:scale-95 {{ $isSurveyorLengkap ? 'bg-emerald-50/80 border-emerald-300 text-emerald-900 hover:bg-emerald-100' : 'bg-amber-50/70 border-amber-300 text-slate-800 hover:bg-amber-100 shadow-xs' }}">
                    <div class="w-5 h-5 rounded-full {{ $isSurveyorLengkap ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white ring-2 ring-amber-200' }} flex items-center justify-center text-[10px] font-black mb-0.5">
                        @if($isSurveyorLengkap)
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @else
                            1
                        @endif
                    </div>
                    <p class="font-bold text-[10px] sm:text-xs leading-tight truncate w-full">Petugas</p>
                    <span class="text-[8px] sm:text-[10px] {{ $isSurveyorLengkap ? 'text-emerald-700 font-semibold' : 'text-amber-700 font-bold' }} truncate w-full">
                        {{ $isSurveyorLengkap ? 'Selesai' : 'Wajib Diisi' }}
                    </span>
                </a>

                <!-- Step 2: Had Kifayah -->
                <a href="#section-hadkifayah" 
                   class="group p-1.5 sm:p-2 rounded-xl border transition-all flex flex-col items-center justify-center active:scale-95 {{ $isHkDone ? 'bg-emerald-50/80 border-emerald-300 text-emerald-900 hover:bg-emerald-100' : ($isSurveyorLengkap ? 'bg-white border-slate-300 text-slate-800 hover:border-amber-400 hover:bg-slate-50' : 'bg-slate-100/70 border-slate-200 text-slate-400 opacity-80') }}">
                    <div class="w-5 h-5 rounded-full {{ $isHkDone ? 'bg-emerald-600 text-white' : ($isSurveyorLengkap ? 'bg-amber-500 text-white' : 'bg-slate-300 text-white') }} flex items-center justify-center text-[10px] font-black mb-0.5">
                        @if($isHkDone)
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @else
                            2
                        @endif
                    </div>
                    <p class="font-bold text-[10px] sm:text-xs leading-tight truncate w-full">Had Kifayah</p>
                    <span class="text-[8px] sm:text-[10px] {{ $isHkDone ? 'text-emerald-700 font-semibold' : ($isSurveyorLengkap ? 'text-amber-700' : 'text-slate-400') }} truncate w-full">
                        {{ $isHkDone ? 'Selesai' : ($isSurveyorLengkap ? 'Siap Diisi' : 'Terkunci') }}
                    </span>
                </a>

                <!-- Step 3: Asesmen -->
                <a href="#section-asesmen" 
                   class="group p-1.5 sm:p-2 rounded-xl border transition-all flex flex-col items-center justify-center active:scale-95 {{ $isAsesmenDone ? 'bg-emerald-50/80 border-emerald-300 text-emerald-900 hover:bg-emerald-100' : ($isSurveyorLengkap ? 'bg-white border-slate-300 text-slate-800 hover:border-amber-400 hover:bg-slate-50' : 'bg-slate-100/70 border-slate-200 text-slate-400 opacity-80') }}">
                    <div class="w-5 h-5 rounded-full {{ $isAsesmenDone ? 'bg-emerald-600 text-white' : ($isSurveyorLengkap ? 'bg-amber-500 text-white' : 'bg-slate-300 text-white') }} flex items-center justify-center text-[10px] font-black mb-0.5">
                        @if($isAsesmenDone)
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @else
                            3
                        @endif
                    </div>
                    <p class="font-bold text-[10px] sm:text-xs leading-tight truncate w-full">Asesmen</p>
                    <span class="text-[8px] sm:text-[10px] {{ $isAsesmenDone ? 'text-emerald-700 font-semibold' : ($isSurveyorLengkap ? 'text-amber-700 font-medium' : 'text-slate-400') }} truncate w-full">
                        {{ $isAsesmenDone ? 'Selesai' : ($isSurveyorLengkap ? 'Siap Diisi' : 'Terkunci') }}
                    </span>
                </a>

                <!-- Step 4: Finalisasi -->
                <a href="#section-final" 
                   class="group p-1.5 sm:p-2 rounded-xl border transition-all flex flex-col items-center justify-center active:scale-95 {{ $isFinalDone ? 'bg-emerald-50/80 border-emerald-300 text-emerald-900 hover:bg-emerald-100' : ($isHkDone && $isAsesmenDone ? 'bg-white border-amber-300 text-slate-800 hover:border-amber-400 hover:bg-slate-50 shadow-xs' : 'bg-slate-100/70 border-slate-200 text-slate-400 opacity-80') }}">
                    <div class="w-5 h-5 rounded-full {{ $isFinalDone ? 'bg-emerald-600 text-white' : ($isHkDone && $isAsesmenDone ? 'bg-amber-500 text-white' : 'bg-slate-300 text-white') }} flex items-center justify-center text-[10px] font-black mb-0.5">
                        @if($isFinalDone)
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @else
                            4
                        @endif
                    </div>
                    <p class="font-bold text-[10px] sm:text-xs leading-tight truncate w-full">Finalisasi</p>
                    <span class="text-[8px] sm:text-[10px] {{ $isFinalDone ? 'text-emerald-700 font-semibold' : ($isHkDone && $isAsesmenDone ? 'text-amber-700 font-bold' : 'text-slate-400') }} truncate w-full">
                        {{ $isFinalDone ? 'Selesai' : ($isHkDone && $isAsesmenDone ? 'Siap Diisi' : 'Terkunci') }}
                    </span>
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN CONTAINER -->
    <main class="max-w-4xl mx-auto px-3.5 sm:px-6 py-5 sm:py-8 space-y-5">

        @if($isFinalDone)
            <!-- STATUS SUMMARY BANNER (TETAP TAMPILKAN FORM, DENGAN STATUS SELESAI & BISA DIEDIT SELAMA BELUM ADA DISPOSISI) -->
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl shadow-xs border-2 border-emerald-300 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm sm:text-base font-bold text-slate-800">Survey Kelayakan Telah Selesai</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase {{ str_contains(strtolower($survey->hasil_akhir ?? ''), 'tidak') ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                Rekomendasi: {{ $survey->hasil_akhir ?? 'Layak' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 mt-0.5">
                            @if($canEditSurvey ?? true)
                                Seluruh tahapan telah dicentang selesai. Formulir tetap dapat diperbarui/diedit selama disposisi pimpinan belum ditetapkan.
                            @else
                                Survei telah terkunci permanen karena penetapan disposisi / persetujuan pimpinan telah diproses.
                            @endif
                        </p>
                    </div>
                </div>
                @if($survey->hadKifayah?->link_had_kifayah)
                    <a href="{{ $survey->hadKifayah->link_had_kifayah }}" target="_blank" class="self-start sm:self-auto px-4 py-2.5 bg-white border border-emerald-300 hover:bg-emerald-50 text-[#118243] font-bold text-xs rounded-xl shadow-2xs transition flex items-center gap-1.5 shrink-0">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Dokumen Puskas</span>
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                @endif
            </div>
        @endif

            <!-- NOTIFIKASI SUCCESS / STATUS -->
            @if(session('success') || session('success_asesmen') || session('success_hk'))
                <div class="p-3.5 sm:p-4 bg-emerald-50 border-2 border-emerald-300 text-[#118243] rounded-2xl text-xs font-bold flex items-center justify-between shadow-xs">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') ?: (session('success_asesmen') ?: session('success_hk')) }}</span>
                    </span>
                    @if(session('link_had_kifayah'))
                        <a href="{{ session('link_had_kifayah') }}" target="_blank" class="px-3 py-1.5 bg-[#118243] text-white text-[11px] font-bold rounded-xl hover:bg-[#0e6c37] inline-flex items-center gap-1">
                            <span>Dokumen Puskas</span>
                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    @endif
                </div>
            @endif

            <!-- SECTION 1: INFORMASI PETUGAS SURVEYOR -->
            <section id="section-petugas" class="bg-white rounded-2xl shadow-sm border-2 {{ $isSurveyorLengkap ? 'border-emerald-200' : 'border-amber-300 ring-2 ring-amber-100' }} p-4 sm:p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full {{ $isSurveyorLengkap ? 'bg-emerald-600' : 'bg-amber-500' }} text-white font-black text-xs flex items-center justify-center shrink-0">
                                @if($isSurveyorLengkap)
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    1
                                @endif
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-[#0F172A]">
                                Langkah 1: Data Petugas Surveyor
                            </h3>
                        </div>
                        <p class="text-[11px] sm:text-xs text-[#64748B] mt-1 pl-8">
                            {{ $isSurveyorLengkap ? 'Data petugas sudah tersimpan. Dapat diubah jika diperlukan.' : 'Harap lengkapi nama, WhatsApp, dan tanggal survei agar modul Had Kifayah & Asesmen terbuka.' }}
                        </p>
                    </div>
                    @if($isSurveyorLengkap)
                        <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Lengkap</span>
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('public.survey.surveyor', $token) }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#0F172A] uppercase mb-1.5">
                                Nama Petugas Surveyor <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="surveyor_nama" value="{{ old('surveyor_nama', $survey->surveyor) }}" required 
                                   class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:ring-4 focus:ring-emerald-100 bg-white shadow-xs text-xs font-medium p-3 transition" 
                                   placeholder="Contoh: Budi Santoso">
                            @error('surveyor_nama') <span class="text-[11px] text-red-500 font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#0F172A] uppercase mb-1.5">
                                No. HP / WhatsApp Surveyor <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" name="surveyor_nohp" value="{{ old('surveyor_nohp', $survey->nohp_surveyor) }}" required 
                                   class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:ring-4 focus:ring-emerald-100 bg-white shadow-xs text-xs font-medium p-3 transition" 
                                   placeholder="08xxxxxxxxxx">
                            @error('surveyor_nohp') <span class="text-[11px] text-red-500 font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2 md:col-span-1">
                            <label class="block text-xs font-bold text-[#0F172A] uppercase mb-1.5">
                                Tanggal Pelaksanaan Survey <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="surveyor_tanggal" value="{{ old('surveyor_tanggal', $survey->tanggal_survey ? $survey->tanggal_survey->format('Y-m-d') : now()->format('Y-m-d')) }}" required 
                                   class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:ring-4 focus:ring-emerald-100 bg-white shadow-xs text-xs font-medium p-3 transition">
                            @error('surveyor_tanggal') <span class="text-[11px] text-red-500 font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-4 mt-3 border-t border-slate-100 gap-3">
                        <span class="text-[11px] text-[#64748B] inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            <span>Nama & No. HP akan otomatis disinkronkan ke dokumen hasil survei.</span>
                        </span>
                        <button type="submit" class="w-full sm:w-auto px-5 py-3 bg-[#118243] hover:bg-[#0e6c37] active:scale-[0.98] text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan Data Surveyor</span>
                        </button>
                    </div>
                </form>
            </section>

            <!-- SECTION 2: TARGET PENERIMA MANFAAT & PROGRAM BAZNAS (COLLAPSIBLE / RINGKAS) -->
            <section class="bg-white rounded-2xl shadow-sm border-2 border-slate-200 p-4 sm:p-6 space-y-4" x-data="{ openDetail: true }">
                <div class="flex items-center justify-between cursor-pointer select-none" @click="openDetail = !openDetail">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-[#118243] flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-[#0F172A]">
                                Data Pengajuan & Mustahik yang Disurvei
                            </h3>
                            <p class="text-[11px] text-slate-500">Ketuk untuk menampilkan/menyembunyikan detail pemohon</p>
                        </div>
                    </div>
                    <button type="button" class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 transition" :class="openDetail ? 'rotate-180' : ''">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>

                @php
                    $pm = $survey->pengajuanMustahik;
                    $pengajuan = $pm?->pengajuan;
                    $mustahikPerson = $pm?->mustahik?->person ?? $pengajuan?->mustahik?->person;
                    $alamatLengkap = $mustahikPerson?->alamat_lengkap ?: ($mustahikPerson?->alamat ?: ($pengajuan?->mustahik_alamat_snapshot ?: '-'));
                    $telepon = $mustahikPerson?->telepon ?: ($mustahikPerson?->handphone ?: ($pengajuan?->mustahik_kontak_snapshot ?: '-'));
                @endphp

                <div x-show="openDetail" x-transition class="space-y-4 pt-1">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
                        <!-- KARTU 1: IDENTITAS PEMOHON -->
                        <div class="bg-[#F4F8F5] p-3.5 sm:p-4 rounded-xl border border-emerald-100 flex flex-col justify-between space-y-2">
                            <div>
                                <span class="text-[10px] font-bold text-[#64748B] uppercase tracking-wider block mb-1">Target Mustahik</span>
                                <p class="font-bold text-[#0F172A] text-sm leading-snug">{{ $mustahikPerson?->nama ?? ($pengajuan?->mustahik_nama_snapshot ?? '-') }}</p>
                                <p class="text-[11px] text-slate-500 mt-1">
                                    <span class="font-semibold text-slate-600">NIK:</span> {{ $mustahikPerson?->nik ?? ($pengajuan?->mustahik_nik_snapshot ?? '-') }}
                                </p>
                                <p class="text-[11px] text-slate-500">
                                    <span class="font-semibold text-slate-600">No. HP/WA:</span> {{ $telepon }}
                                </p>
                            </div>
                            <div class="pt-2 border-t border-emerald-200/60 text-[11px] text-slate-600">
                                <span class="font-semibold text-slate-700 block">Alamat Faktual:</span>
                                <span class="line-clamp-2 leading-relaxed">{{ $alamatLengkap }}</span>
                            </div>
                        </div>

                        <!-- KARTU 2: PROGRAM BANTUAN -->
                        <div class="bg-[#F4F8F5] p-3.5 sm:p-4 rounded-xl border border-emerald-100 flex flex-col justify-between space-y-2">
                            <div>
                                <span class="text-[10px] font-bold text-[#64748B] uppercase tracking-wider block mb-1">Program & Asnaf</span>
                                <p class="font-bold text-[#0F172A] text-sm leading-snug">{{ $pm?->program?->nama ?? 'Program BAZNAS' }}</p>
                                <p class="text-[11px] text-[#118243] font-semibold mt-1">
                                    {{ $pm?->subprogram?->nama ?? 'Bantuan Langsung' }}
                                </p>
                            </div>
                            <div class="pt-2 border-t border-emerald-200/60 text-[11px] text-slate-600 space-y-0.5">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Kategori Asnaf:</span>
                                    <span class="font-semibold text-slate-700">{{ $pm?->asnaf?->nama ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Jenis Permohonan:</span>
                                    <span class="font-semibold text-slate-700 uppercase">{{ $pengajuan?->jenis_permohonan ?? 'Umum' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- KARTU 3: DETAIL PENGAJUAN -->
                        <div class="bg-[#F4F8F5] p-3.5 sm:p-4 rounded-xl border border-emerald-100 flex flex-col justify-between space-y-2">
                            <div>
                                <span class="text-[10px] font-bold text-[#64748B] uppercase tracking-wider block mb-1">Data Surat & Pengajuan</span>
                                <p class="font-bold text-[#0F172A] text-xs leading-snug font-mono text-emerald-800">
                                    {{ $pengajuan?->nomor_pengajuan ?? ('#' . ($pm?->pengajuan_id ?? '-')) }}
                                </p>
                                <p class="text-[11px] text-slate-700 font-medium mt-1 line-clamp-2" title="{{ $pengajuan?->judul_surat }}">
                                    {{ $pengajuan?->judul_surat ?? '-' }}
                                </p>
                            </div>
                            <div class="pt-2 border-t border-emerald-200/60 text-[11px] text-slate-600 space-y-0.5">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Tgl Masuk:</span>
                                    <span class="font-semibold text-slate-700">{{ $pengajuan?->tanggal_diterima ? $pengajuan->tanggal_diterima->translatedFormat('d M Y') : '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Jumlah Orang:</span>
                                    <span class="font-semibold text-slate-700">{{ $pengajuan?->jumlah_orang ?? 1 }} Orang ({{ $pengajuan?->jumlah_kk ?? 1 }} KK)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($pengajuan?->uraian_permohonan))
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 text-xs">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Maksud / Uraian Permohonan:</span>
                            <p class="text-slate-700 leading-relaxed italic text-[11px]">
                                "{{ $pengajuan->uraian_permohonan }}"
                            </p>
                        </div>
                    @endif
                </div>
            </section>

            @php
                $isSurveyorLengkap = !empty(trim($survey->surveyor ?? '')) && !empty(trim($survey->nohp_surveyor ?? '')) && !empty($survey->tanggal_survey);
            @endphp

            @if(!$isSurveyorLengkap)
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl text-xs flex items-start gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                    <div>
                        <p class="font-bold text-sm">Lengkapi Data Surveyor Terlebih Dahulu</p>
                        <p class="text-amber-700 mt-0.5 leading-relaxed">
                            Form <strong>Survey Had Kifayah</strong> dan <strong>Survey Asesmen</strong> terkunci dan belum dapat diisi. Silakan lengkapi dan simpan <strong>Nama Petugas Surveyor</strong>, <strong>No. HP/WhatsApp</strong>, dan <strong>Tanggal Pelaksanaan Survey</strong> pada Bagian 1 di atas.
                        </p>
                    </div>
                </div>
            @endif

            <!-- SECTION 2 & 3: DUA MODUL SURVEI (HAD KIFAYAH & ASESMEN) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-[#0F172A] flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full {{ $isHkDone && $isAsesmenDone ? 'bg-emerald-600' : 'bg-slate-400' }} text-white font-black text-xs flex items-center justify-center shrink-0">
                                @if($isHkDone && $isAsesmenDone)
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                @endif
                            </span>
                            <span>Instrumen Survei Lapangan (Langkah 2 & 3)</span>
                        </h3>
                        <p class="text-[11px] sm:text-xs text-[#64748B] pl-8">
                            Kedua modul ini wajib diisi sesuai kondisi faktual mustahik sebelum mengisi kesimpulan akhir.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <!-- MODUL HAD KIFAYAH (STEP 2) -->
                    <div id="section-hadkifayah" class="bg-white rounded-2xl shadow-sm border-2 {{ $isHkDone ? 'border-emerald-300 ring-2 ring-emerald-50' : ($isSurveyorLengkap ? 'border-amber-300 ring-2 ring-amber-50' : 'border-slate-200 opacity-80') }} p-4 sm:p-6 flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-[10px] font-bold px-2.5 py-1 {{ $isHkDone ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }} rounded-md uppercase tracking-wider">
                                    Langkah 2 • Standar Puskas
                                </span>
                                @if(!$isSurveyorLengkap)
                                    <span class="text-xs font-semibold text-slate-400 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                        Terkunci
                                    </span>
                                @elseif($isHkDone)
                                    <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Sudah Diisi</span>
                                    </span>
                                @else
                                    <span class="text-xs font-bold text-amber-600 flex items-center gap-1">
                                        ● Belum Diisi
                                    </span>
                                @endif
                            </div>
                            <h4 class="text-base sm:text-lg font-bold text-[#0F172A] leading-snug">2. Survey Had Kifayah Puskas BAZNAS</h4>
                            <p class="text-xs text-[#64748B] mt-1 leading-relaxed">
                                Standar batas kecukupan pemenuhan kebutuhan dasar mustahik (pendapatan, tanggungan, hunian, bansos).
                            </p>
                            @if($survey->hadKifayah)
                                <div class="mt-3.5 p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 text-xs space-y-1.5">
                                    <div class="flex justify-between items-center text-slate-700">
                                        <span class="font-medium text-slate-500">Hasil Kelayakan:</span>
                                        <span class="font-extrabold px-2 py-0.5 rounded-md text-[11px] uppercase {{ str_contains(strtolower($survey->hadKifayah->status_kelayakan ?? ''), 'tidak') ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $survey->hadKifayah->status_kelayakan ?? '-' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-slate-700">
                                        <span class="font-medium text-slate-500">Prioritas:</span>
                                        <span class="font-bold text-slate-800">{{ $survey->hadKifayah->kategori_prioritas ?? '-' }}</span>
                                    </div>
                                    @if($survey->hadKifayah->link_had_kifayah)
                                        <div class="pt-1.5 border-t border-emerald-200/60 mt-1 flex justify-end">
                                            <a href="{{ $survey->hadKifayah->link_had_kifayah }}" target="_blank" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 hover:underline inline-flex items-center gap-1">
                                                <span>Buka Lembar Dokumen Puskas</span>
                                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        @if($isSurveyorLengkap)
                            <button type="button" @click="activeModal = 'had_kifayah'; hk_step = 1" 
                                    class="w-full py-3.5 px-4 {{ $isHkDone ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-amber-500 hover:bg-amber-600 ring-4 ring-amber-100 animate-pulse' }} text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-md flex items-center justify-center gap-2 active:scale-[0.98]">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>{{ $isHkDone ? 'Edit Survey Had Kifayah' : 'Mulai Isi Had Kifayah (5 Menit)' }}</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        @else
                            <button type="button" disabled class="w-full py-3.5 px-4 bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed text-xs font-bold rounded-xl flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                <span>Isi & Simpan Data Petugas Dulu</span>
                            </button>
                        @endif
                    </div>

                    <!-- MODUL ASESMEN LAPANGAN (STEP 3) -->
                    <div id="section-asesmen" class="bg-white rounded-2xl shadow-sm border-2 {{ $isAsesmenDone ? 'border-emerald-300 ring-2 ring-emerald-50' : ($isSurveyorLengkap ? 'border-amber-300 ring-2 ring-amber-50' : 'border-slate-200 opacity-80') }} p-4 sm:p-6 flex flex-col justify-between space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-[10px] font-bold px-2.5 py-1 {{ $isAsesmenDone ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }} rounded-md uppercase tracking-wider">
                                    Langkah 3 • Faktual Lapangan
                                </span>
                                @if(!$isSurveyorLengkap)
                                    <span class="text-xs font-semibold text-slate-400 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                        Terkunci
                                    </span>
                                @elseif($isAsesmenDone)
                                    <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>Sudah Diisi</span>
                                    </span>
                                @else
                                    <span class="text-xs font-bold text-amber-600 flex items-center gap-1">
                                        ● Belum Selesai
                                    </span>
                                @endif
                            </div>
                            <h4 class="text-base sm:text-lg font-bold text-[#0F172A] leading-snug">3. Survey Asesmen Kondisi Faktual</h4>
                            <p class="text-xs text-[#64748B] mt-1 leading-relaxed">
                                Pemeriksaan kondisi fisik rumah, skor penilaian surveyor, dan upload dokumentasi foto lokasi.
                            </p>
                            @if($survey->asesmen)
                                <div class="mt-3.5 p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 text-xs space-y-1.5">
                                    <div class="flex justify-between items-center text-slate-700">
                                        <span class="font-medium text-slate-500">Rekomendasi:</span>
                                        <span class="font-extrabold px-2 py-0.5 rounded-md text-[11px] uppercase {{ str_contains(strtolower($survey->asesmen->hasil_survei ?? ''), 'tidak') ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $survey->asesmen->hasil_survei ?? '-' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-slate-700">
                                        <span class="font-medium text-slate-500">Skor Lapangan:</span>
                                        <span class="font-bold text-slate-800">{{ $survey->asesmen->skor_penilaian ?? '-' }} dari 5</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($isSurveyorLengkap)
                            <button type="button" @click="activeModal = 'asesmen'; as_step = 1" 
                                    class="w-full py-3.5 px-4 {{ $isAsesmenDone ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-amber-500 hover:bg-amber-600 ring-4 ring-amber-100 animate-pulse' }} text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-md flex items-center justify-center gap-2 active:scale-[0.98]">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>{{ $isAsesmenDone ? 'Edit Survey Asesmen' : 'Mulai Survey Asesmen Lapangan' }}</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        @else
                            <button type="button" disabled class="w-full py-3.5 px-4 bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed text-xs font-bold rounded-xl flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                <span>Isi & Simpan Data Petugas Dulu</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- SECTION 4: FORM FINAL KELAYAKAN (STEP 4) -->
            <section id="section-final" class="bg-white rounded-2xl shadow-sm border-2 {{ $isFinalDone ? 'border-emerald-300' : ($isHkDone && $isAsesmenDone ? 'border-amber-300 ring-2 ring-amber-50' : 'border-slate-200') }} p-4 sm:p-6 space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full {{ $isFinalDone ? 'bg-emerald-600' : ($isHkDone && $isAsesmenDone ? 'bg-amber-500' : 'bg-slate-400') }} text-white font-black text-xs flex items-center justify-center shrink-0">
                                @if($isFinalDone)
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    4
                                @endif
                            </span>
                            <h3 class="text-sm sm:text-base font-bold text-[#0F172A]">
                                Langkah 4: Kesimpulan & Keputusan Final
                            </h3>
                        </div>
                        <p class="text-[11px] sm:text-xs text-[#64748B] mt-1 pl-8">
                            Tetapkan kesimpulan surveyor untuk diajukan ke persetujuan pimpinan BAZNAS.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('public.survey.submit', $token) }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#0F172A] uppercase mb-1.5">
                                Keputusan Hasil Survei Lapangan <span class="text-red-500">*</span>
                            </label>
                            <select name="final_hasil" x-model="final_hasil" required 
                                    class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:ring-4 focus:ring-emerald-100 bg-white shadow-xs text-xs sm:text-sm font-bold p-3.5 transition">
                                <option value="layak">LAYAK (Direkomendasikan Menerima Bantuan)</option>
                                <option value="tidak layak">TIDAK LAYAK (Tidak Memenuhi Kriteria)</option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1">Pilih rekomendasi Anda setelah meninjau Had Kifayah & kondisi fisik.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#0F172A] uppercase mb-1.5">
                                Catatan / Pertimbangan Tambahan
                            </label>
                            <textarea name="final_catatan" x-model="final_catatan" rows="3" 
                                      class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:ring-4 focus:ring-emerald-100 bg-white shadow-xs text-xs font-medium p-3 transition" 
                                      placeholder="Tuliskan catatan khusus kondisi keluarga, urgensi bantuan, atau alasan keputusan..."></textarea>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-4 mt-3 border-t border-slate-100 gap-3">
                        <span class="text-[11px] text-[#64748B] inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-500 shrink-0 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Setelah difinalkan, laporan tersimpan dan status survei ditandai selesai.</span>
                        </span>
                        <button type="submit" class="w-full sm:w-auto px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-md flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            <span>Simpan & Finalkan Hasil Survei</span>
                        </button>
                    </div>
                </form>
            </section>
    </main>

    <!-- MODAL HAD KIFAYAH (WIZARD 5 TAHAP SESUAI STANDAR PUSKAS BAZNAS - OPSI B) -->
    @php
        $hk = $survey->hadKifayah;
        $defaultOpz = $hk?->opz ?? 'BAZNAS KABUPATEN CILACAP';
        $defaultPetugas = $hk?->nama_petugas ?? ($survey->surveyor ?? 'Surveyor BAZNAS');
        $defaultMustahik = $hk?->nama_mustahik ?? ($mustahikPerson?->nama ?? '');
        $defaultAlamat = $hk?->alamat_mustahik ?? ($alamatLengkap !== '-' ? $alamatLengkap : '');
        $defaultProvinsi = $hk?->propinsi ?? '33|JAWA TENGAH';
        $defaultPendapatanKk = ($hk?->pendapatan_keluarga !== null && $hk?->pendapatan_keluarga !== '') ? number_format((float) preg_replace('/\D/', '', (string) $hk->pendapatan_keluarga), 0, ',', '.') : '';
        $defaultPendapatanAnggota = ($hk?->pendapatan_anggotakel !== null && $hk?->pendapatan_anggotakel !== '') ? number_format((float) preg_replace('/\D/', '', (string) $hk->pendapatan_anggotakel), 0, ',', '.') : '';

        $hkConfig = [
            'f_opz' => old('opz', $defaultOpz),
            'f_petugas' => old('nama_petugas', $defaultPetugas),
            'f_mustahik' => old('nama_mustahik', $defaultMustahik),
            'f_provinsi' => old('propinsi', $defaultProvinsi),
            'f_alamat' => old('alamat_mustahik', $defaultAlamat),
            'f_pendapatan_kk' => old('pendapatan_keluarga', $defaultPendapatanKk),
            'f_pendapatan_anggota' => old('pendapatan_anggotakel', $defaultPendapatanAnggota),
            'f_peng_kk' => old('pengeluaran_kk', $hk?->pengeluaran_kk ?? ''),
            'f_peng_ibu' => old('pengeluaran_ibu', $hk?->pengeluaran_ibu ?? ''),
            'f_peng_anak_dewasa_kerja' => old('pengeluaran_anakDewasaBekerja', $hk?->pengeluaran_anakDewasaBekerja ?? ''),
            'f_peng_anak_dewasa_tdk_kerja' => old('pengeluaran_anakDewasaTidakBekerja', $hk?->pengeluaran_anakDewasaTidakBekerja ?? ''),
            'f_peng_sma' => old('pengeluaran_sma', $hk?->pengeluaran_sma ?? ''),
            'f_peng_smp' => old('pengeluaran_smp', $hk?->pengeluaran_smp ?? ''),
            'f_peng_sd' => old('pengeluaran_sd', $hk?->pengeluaran_sd ?? ''),
            'f_peng_bayi' => old('pengeluaran_bayi', $hk?->pengeluaran_bayi ?? ''),
            'f_peng_disabilitas' => old('pengeluaran_disabilitas', $hk?->pengeluaran_disabilitas ?? ''),
            'f_peng_ibu_menyusui' => old('pengeluaran_ibuMenyusui', $hk?->pengeluaran_ibuMenyusui ?? ''),
            'f_peng_ibu_hamil' => old('pengeluaran_ibuHamil', $hk?->pengeluaran_ibuHamil ?? ''),
            'f_peng_janda' => old('pengeluaran_janda', $hk?->pengeluaran_janda ?? ''),
            'f_peng_pt' => old('pengeluaran_pt', $hk?->pengeluaran_pt ?? ''),
            'f_tempat_rumah' => old('tempat_rumah', $hk?->tempat_rumah ?? ''),
            'f_tempat_listrik' => old('tempat_listrik', $hk?->tempat_listrik ?? ''),
            'f_pemerintah_bansos' => old('pemerintah_bansos', $hk?->pemerintah_bansos ?? ''),
            'f_pemerintah_bpjs' => old('pemerintah_bpjs', $hk?->pemerintah_bpjs ?? ''),
            'f_asset_motor' => old('asset_motor', $hk?->asset_motor ?? ''),
            'f_asset_tabungan' => old('asset_tabungan', $hk?->asset_tabungan ?? ''),
            'f_beban_hutang' => old('beban_hutang', $hk?->beban_hutang ?? ''),
        ];
    @endphp

    <!-- MODAL HAD KIFAYAH (MOBILE-OPTIMIZED WITH STEPPER & COMPREHENSIVE VALIDATION) -->
    <div x-show="activeModal === 'had_kifayah'" x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         x-data="hadKifayahModalForm(@js($hkConfig))">
        <div x-show="activeModal === 'had_kifayah'"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="bg-white rounded-t-3xl sm:rounded-3xl max-w-4xl w-full shadow-2xl border-2 border-slate-300 overflow-hidden flex flex-col h-[94vh] sm:h-[90vh] max-h-[96vh] relative">
            
            <!-- HEADER MODAL -->
            <div class="bg-gradient-to-r from-[#118243] via-[#0d6b37] to-[#0a522a] text-white px-4 sm:px-6 py-3.5 sm:py-4 flex items-center justify-between shadow-md shrink-0 border-b border-emerald-700">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-white/15 flex items-center justify-center text-white shadow-inner shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-white leading-tight tracking-tight">Formulir Had Kifayah BAZNAS</h3>
                        <p class="text-[11px] sm:text-xs text-emerald-100 font-medium mt-0.5">
                            Tahap <span class="font-bold text-white underline underline-offset-2" x-text="hk_step"></span> dari 5 — Standar Puskas BAZNAS
                        </p>
                    </div>
                </div>
                <button type="button" @click="activeModal = ''" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/30 text-white flex items-center justify-center transition shadow-xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- STEPPER PROGRESS TABS (SCROLLABLE DENGAN TOMBOL BESAR & JELAS DI MOBILE) -->
            <div x-ref="tabsContainer" class="bg-slate-100/95 border-b-2 border-slate-200 px-3 sm:px-6 py-2.5 shrink-0 overflow-x-auto no-scrollbar scroll-smooth">
                <div class="flex items-center gap-1.5 sm:gap-2 min-w-max text-xs font-bold">
                    <!-- TAB 1 -->
                    <button type="button" x-ref="tab_1" @click="hk_step = 1" 
                            :class="tabHasError(1) ? 'bg-rose-50 text-rose-700 border-2 border-rose-500 ring-2 ring-rose-200' : (hk_step === 1 ? 'bg-[#118243] text-white shadow-md ring-2 ring-emerald-600/30' : (isStep1Valid() ? 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300' : 'bg-white text-slate-600 border-2 border-slate-300 hover:border-slate-400'))"
                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-xs active:scale-95">
                        <template x-if="tabHasError(1)">
                            <span class="w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-black">!</span>
                        </template>
                        <template x-if="!tabHasError(1) && isStep1Valid()">
                            <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center">
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </template>
                        <template x-if="!tabHasError(1) && !isStep1Valid()">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]" :class="hk_step === 1 ? 'bg-white text-[#118243]' : 'bg-slate-200 text-slate-700'">1</span>
                        </template>
                        <span>1. Identitas</span>
                    </button>

                    <span class="text-slate-400 font-bold">›</span>

                    <!-- TAB 2 -->
                    <button type="button" x-ref="tab_2" @click="hk_step = 2" 
                            :class="tabHasError(2) ? 'bg-rose-50 text-rose-700 border-2 border-rose-500 ring-2 ring-rose-200' : (hk_step === 2 ? 'bg-[#118243] text-white shadow-md ring-2 ring-emerald-600/30' : (isStep2Valid() ? 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300' : 'bg-white text-slate-600 border-2 border-slate-300 hover:border-slate-400'))"
                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-xs active:scale-95">
                        <template x-if="tabHasError(2)">
                            <span class="w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-black">!</span>
                        </template>
                        <template x-if="!tabHasError(2) && isStep2Valid()">
                            <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center">
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </template>
                        <template x-if="!tabHasError(2) && !isStep2Valid()">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]" :class="hk_step === 2 ? 'bg-white text-[#118243]' : 'bg-slate-200 text-slate-700'">2</span>
                        </template>
                        <span>2. Pendapatan & Keluarga</span>
                    </button>

                    <span class="text-slate-400 font-bold">›</span>

                    <!-- TAB 3 -->
                    <button type="button" x-ref="tab_3" @click="hk_step = 3" 
                            :class="tabHasError(3) ? 'bg-rose-50 text-rose-700 border-2 border-rose-500 ring-2 ring-rose-200' : (hk_step === 3 ? 'bg-[#118243] text-white shadow-md ring-2 ring-emerald-600/30' : (isStep3Valid() ? 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300' : 'bg-white text-slate-600 border-2 border-slate-300 hover:border-slate-400'))"
                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-xs active:scale-95">
                        <template x-if="tabHasError(3)">
                            <span class="w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-black">!</span>
                        </template>
                        <template x-if="!tabHasError(3) && isStep3Valid()">
                            <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center">
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </template>
                        <template x-if="!tabHasError(3) && !isStep3Valid()">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]" :class="hk_step === 3 ? 'bg-white text-[#118243]' : 'bg-slate-200 text-slate-700'">3</span>
                        </template>
                        <span>3. Tambahan</span>
                    </button>

                    <span class="text-slate-400 font-bold">›</span>

                    <!-- TAB 4 -->
                    <button type="button" x-ref="tab_4" @click="hk_step = 4" 
                            :class="tabHasError(4) ? 'bg-rose-50 text-rose-700 border-2 border-rose-500 ring-2 ring-rose-200' : (hk_step === 4 ? 'bg-[#118243] text-white shadow-md ring-2 ring-emerald-600/30' : (isStep4Valid() ? 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300' : 'bg-white text-slate-600 border-2 border-slate-300 hover:border-slate-400'))"
                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-xs active:scale-95">
                        <template x-if="tabHasError(4)">
                            <span class="w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-black">!</span>
                        </template>
                        <template x-if="!tabHasError(4) && isStep4Valid()">
                            <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center">
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </template>
                        <template x-if="!tabHasError(4) && !isStep4Valid()">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]" :class="hk_step === 4 ? 'bg-white text-[#118243]' : 'bg-slate-200 text-slate-700'">4</span>
                        </template>
                        <span>4. Hunian & Bansos</span>
                    </button>

                    <span class="text-slate-400 font-bold">›</span>

                    <!-- TAB 5 -->
                    <button type="button" x-ref="tab_5" @click="hk_step = 5" 
                            :class="tabHasError(5) ? 'bg-rose-50 text-rose-700 border-2 border-rose-500 ring-2 ring-rose-200' : (hk_step === 5 ? 'bg-[#118243] text-white shadow-md ring-2 ring-emerald-600/30' : (isStep5Valid() ? 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300' : 'bg-white text-slate-600 border-2 border-slate-300 hover:border-slate-400'))"
                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-xs active:scale-95">
                        <template x-if="tabHasError(5)">
                            <span class="w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-black">!</span>
                        </template>
                        <template x-if="!tabHasError(5) && isStep5Valid()">
                            <span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center">
                                <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                        </template>
                        <template x-if="!tabHasError(5) && !isStep5Valid()">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]" :class="hk_step === 5 ? 'bg-white text-[#118243]' : 'bg-slate-200 text-slate-700'">5</span>
                        </template>
                        <span>5. Asset & Hutang</span>
                    </button>
                </div>
            </div>

            <!-- FORM START -->
            <form method="POST" action="{{ route('public.survey.hadkifayah', $token) }}" 
                  novalidate
                  @submit="validateAndSubmit($event)"
                  class="flex flex-col flex-1 min-h-0 overflow-hidden">
                @csrf

                <!-- SCROLLABLE BODY FORM -->
                <div x-ref="formBody" class="p-5 sm:p-7 overflow-y-auto space-y-6 flex-1 text-xs bg-[#f8faf9] scroll-smooth">

                    <!-- ========================================== -->
                    <!-- TAHAP 1: IDENTITAS & MUSTAHIK              -->
                    <!-- ========================================== -->
                    <div x-show="hk_step === 1" class="space-y-6">
                        <!-- SECTION 1: INFORMASI OPZ & PETUGAS -->
                        <div class="bg-white border-2 border-emerald-200/90 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-3 border-b-2 border-emerald-100 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">1. Informasi OPZ & Petugas</h4>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Nama OPZ <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="opz" x-model="f_opz" required 
                                           :class="!f_opz && validationErrors.some(e => e.field === 'Nama OPZ') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-3 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="Nama Organisasi Pengelola Zakat">
                                    <p class="text-[10px] text-slate-500 mt-1">Organisasi Pengelola Zakat pelaksana survey</p>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Nama Petugas <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nama_petugas" x-model="f_petugas" required 
                                           :class="!f_petugas && validationErrors.some(e => e.field === 'Nama Petugas') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-3 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="Nama lengkap surveyor lapangan">
                                    <p class="text-[10px] text-slate-500 mt-1">Sesuai data surveyor terverifikasi</p>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: INFORMASI MUSTAHIK -->
                        <div class="bg-white border-2 border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-slate-50 px-5 py-3 border-b-2 border-slate-200 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">2. Informasi Mustahik</h4>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Nama Mustahik <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="nama_mustahik" x-model="f_mustahik" required 
                                           :class="!f_mustahik && validationErrors.some(e => e.field === 'Nama Mustahik') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-3 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="Nama lengkap kepala keluarga / mustahik">
                                    <p class="text-[10px] text-slate-500 mt-1">Nama lengkap sesuai KTP / KK</p>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Provinsi <span class="text-red-500">*</span>
                                    </label>
                                    <select name="propinsi" x-model="f_provinsi" required 
                                            :class="!f_provinsi && validationErrors.some(e => e.field === 'Provinsi') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                            class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                        <option value="" disabled>-- Pilih Provinsi Standar Had Kifayah --</option>
                                        <option value="33|JAWA TENGAH">Jawa Tengah</option>
                                        <option value="31|DKI JAKARTA">DKI Jakarta</option>
                                        <option value="32|JAWA BARAT">Jawa Barat</option>
                                        <option value="34|DI YOGYAKARTA">DI Yogyakarta</option>
                                        <option value="35|JAWA TIMUR">Jawa Timur</option>
                                        <option value="36|BANTEN">Banten</option>
                                        <option value="11|ACEH">Aceh</option>
                                        <option value="12|SUMATERA UTARA">Sumatera Utara</option>
                                        <option value="13|SUMATERA BARAT">Sumatera Barat</option>
                                        <option value="14|RIAU">Riau</option>
                                        <option value="15|JAMBI">Jambi</option>
                                        <option value="16|SUMATERA SELATAN">Sumatera Selatan</option>
                                        <option value="17|BENGKULU">Bengkulu</option>
                                        <option value="18|LAMPUNG">Lampung</option>
                                        <option value="19|KEPULAUAN BANGKA BELITUNG">Kepulauan Bangka Belitung</option>
                                        <option value="21|KEPULAUAN RIAU">Kepulauan Riau</option>
                                        <option value="51|BALI">Bali</option>
                                        <option value="52|NUSA TENGGARA BARAT">Nusa Tenggara Barat</option>
                                        <option value="53|NUSA TENGGARA TIMUR">Nusa Tenggara Timur</option>
                                        <option value="61|KALIMANTAN BARAT">Kalimantan Barat</option>
                                        <option value="62|KALIMANTAN TENGAH">Kalimantan Tengah</option>
                                        <option value="63|KALIMANTAN SELATAN">Kalimantan Selatan</option>
                                        <option value="64|KALIMANTAN TIMUR">Kalimantan Timur</option>
                                        <option value="65|KALIMANTAN UTARA">Kalimantan Utara</option>
                                        <option value="71|SULAWESI UTARA">Sulawesi Utara</option>
                                        <option value="72|SULAWESI TENGAH">Sulawesi Tengah</option>
                                        <option value="73|SULAWESI SELATAN">Sulawesi Selatan</option>
                                        <option value="74|SULAWESI TENGGARA">Sulawesi Tenggara</option>
                                        <option value="75|GORONTALO">Gorontalo</option>
                                        <option value="76|SULAWESI BARAT">Sulawesi Barat</option>
                                        <option value="81|MALUKU">Maluku</option>
                                        <option value="82|MALUKU UTARA">Maluku Utara</option>
                                        <option value="91|PAPUA BARAT">Papua Barat</option>
                                        <option value="94|PAPUA">Papua</option>
                                    </select>
                                    <p class="text-[10px] text-slate-500 mt-1">Standar UMR / Garis Kemiskinan Had Kifayah per Provinsi</p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Alamat Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="alamat_mustahik" x-model="f_alamat" rows="2" required 
                                              :class="!f_alamat && validationErrors.some(e => e.field === 'Alamat Lengkap') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                              class="w-full rounded-xl border-2 bg-white p-3 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                              placeholder="Alamat domisili/faktual mustahik"></textarea>
                                    <p class="text-[10px] text-slate-500 mt-1">Alamat rumah tempat tinggal saat ini disurvey</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAHAP 2: PENDAPATAN & PENGELUARAN          -->
                    <!-- ========================================== -->
                    <div x-show="hk_step === 2" class="space-y-6">
                        <!-- BANNER CATATAN PENGISIAN -->
                        <div class="p-3 bg-amber-50 border-2 border-amber-300 rounded-xl flex items-center gap-2.5 text-xs text-amber-900 font-semibold shadow-xs">
                            <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span><strong>Catatan:</strong> Semua kolom pada tahap ini <u>wajib diisi</u>. Jika tidak ada / tidak memiliki nilai, <strong>isi dengan angka 0</strong>.</span>
                        </div>

                        <!-- SECTION 3: SUMBER PENDAPATAN -->
                        <div class="bg-white border-2 border-emerald-200/90 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-3 border-b-2 border-emerald-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                    <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">3. Sumber Pendapatan</h4>
                                </div>
                                <span class="text-[10px] text-emerald-800 font-bold bg-emerald-100/70 px-2 py-0.5 rounded">Wajib diisi (isi 0 jika tidak ada)</span>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Pendapatan Kepala Keluarga Per Bulan (Rp) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" inputmode="numeric" x-number name="pendapatan_keluarga" x-model="f_pendapatan_kk" 
                                           :class="(f_pendapatan_kk === null || f_pendapatan_kk === '') && validationErrors.some(e => e.field === 'Pendapatan Kepala Keluarga') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-3 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                    <p class="text-[10px] text-slate-500 mt-1">Gaji/upah/hasil usaha kepala keluarga (Isi 0 jika tidak ada)</p>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Pendapatan Anggota Lain Per Bulan (Rp) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" inputmode="numeric" x-number name="pendapatan_anggotakel" x-model="f_pendapatan_anggota" 
                                           :class="(f_pendapatan_anggota === null || f_pendapatan_anggota === '') && validationErrors.some(e => e.field === 'Pendapatan Anggota Lain') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-3 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                    <p class="text-[10px] text-slate-500 mt-1">Total kontribusi istri/anak/anggota keluarga lain (Isi 0 jika tidak ada)</p>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: PENGELUARAN KELUARGA PER BULAN -->
                        <div class="bg-white border-2 border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-slate-50 px-5 py-3 border-b-2 border-slate-200 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                    <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">4. Pengeluaran Keluarga Per Bulan (Jumlah Jiwa)</h4>
                                </div>
                                <span class="text-[10px] text-slate-600 font-bold bg-slate-200/80 px-2 py-0.5 rounded">Wajib diisi (isi 0 jika tidak ada)</span>
                            </div>
                            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Kepala Rumah Tangga <span class="text-slate-400 font-normal">(Min: 0, Max: 1)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_kk" min="0" max="1" x-model="f_peng_kk" 
                                           :class="(f_peng_kk === null || f_peng_kk === '') && validationErrors.some(e => e.field === 'Kepala Rumah Tangga') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Ibu Rumah Tangga <span class="text-slate-400 font-normal">(Min: 0, Max: 4)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_ibu" min="0" max="4" x-model="f_peng_ibu" 
                                           :class="(f_peng_ibu === null || f_peng_ibu === '') && validationErrors.some(e => e.field === 'Ibu Rumah Tangga') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Anak Dewasa (Bekerja) <span class="text-slate-400 font-normal">(Min: 0, Max: 20)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_anakDewasaBekerja" min="0" max="20" x-model="f_peng_anak_dewasa_kerja" 
                                           :class="(f_peng_anak_dewasa_kerja === null || f_peng_anak_dewasa_kerja === '') && validationErrors.some(e => e.field === 'Anak Dewasa (Bekerja)') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Anak Dewasa (Tdk Bekerja) <span class="text-slate-400 font-normal">(Min: 0, Max: 20)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_anakDewasaTidakBekerja" min="0" max="20" x-model="f_peng_anak_dewasa_tdk_kerja" 
                                           :class="(f_peng_anak_dewasa_tdk_kerja === null || f_peng_anak_dewasa_tdk_kerja === '') && validationErrors.some(e => e.field === 'Anak Dewasa (Tdk Bekerja)') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Anak SMA/Setingkat <span class="text-slate-400 font-normal">(Min: 0, Max: 20)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_sma" min="0" max="20" x-model="f_peng_sma" 
                                           :class="(f_peng_sma === null || f_peng_sma === '') && validationErrors.some(e => e.field === 'Anak SMA/Setingkat') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Anak SMP/Setingkat <span class="text-slate-400 font-normal">(Min: 0, Max: 20)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_smp" min="0" max="20" x-model="f_peng_smp" 
                                           :class="(f_peng_smp === null || f_peng_smp === '') && validationErrors.some(e => e.field === 'Anak SMP/Setingkat') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Anak SD/Setingkat <span class="text-slate-400 font-normal">(Min: 0, Max: 20)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_sd" min="0" max="20" x-model="f_peng_sd" 
                                           :class="(f_peng_sd === null || f_peng_sd === '') && validationErrors.some(e => e.field === 'Anak SD/Setingkat') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Anak/Bayi Belum Sekolah <span class="text-slate-400 font-normal">(Min: 0, Max: 20)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_bayi" min="0" max="20" x-model="f_peng_bayi" 
                                           :class="(f_peng_bayi === null || f_peng_bayi === '') && validationErrors.some(e => e.field === 'Anak/Bayi Belum Sekolah') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAHAP 3: TAMBAHAN & PENDIDIKAN             -->
                    <!-- ========================================== -->
                    <div x-show="hk_step === 3" class="space-y-6">
                        <!-- BANNER CATATAN PENGISIAN -->
                        <div class="p-3 bg-amber-50 border-2 border-amber-300 rounded-xl flex items-center gap-2.5 text-xs text-amber-900 font-semibold shadow-xs">
                            <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span><strong>Catatan:</strong> Semua kolom pada tahap ini <u>wajib diisi</u>. Jika tidak ada / tidak memiliki tanggungan, <strong>isi dengan angka 0</strong>.</span>
                        </div>

                        <!-- SECTION 5: PENGELUARAN TAMBAHAN PER BULAN -->
                        <div class="bg-white border-2 border-emerald-200/90 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-3 border-b-2 border-emerald-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                    <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">5. Pengeluaran Tambahan Per Bulan</h4>
                                </div>
                                <span class="text-[10px] text-emerald-800 font-bold bg-emerald-100/70 px-2 py-0.5 rounded">Wajib diisi (isi 0 jika tidak ada)</span>
                            </div>
                            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Penyandang Disabilitas <span class="text-slate-400 font-normal">(Min: 0, Max: 10)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_disabilitas" min="0" max="10" x-model="f_peng_disabilitas" 
                                           :class="(f_peng_disabilitas === null || f_peng_disabilitas === '') && validationErrors.some(e => e.field === 'Penyandang Disabilitas') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                    <p class="text-[10px] text-slate-500 mt-1">Anggota keluarga disabilitas</p>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Ibu Menyusui <span class="text-slate-400 font-normal">(Min: 0, Max: 4)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_ibuMenyusui" min="0" max="4" x-model="f_peng_ibu_menyusui" 
                                           :class="(f_peng_ibu_menyusui === null || f_peng_ibu_menyusui === '') && validationErrors.some(e => e.field === 'Ibu Menyusui') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                    <p class="text-[10px] text-slate-500 mt-1">Ibu dengan anak menyusui</p>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Ibu Hamil <span class="text-slate-400 font-normal">(Min: 0, Max: 4)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_ibuHamil" min="0" max="4" x-model="f_peng_ibu_hamil" 
                                           :class="(f_peng_ibu_hamil === null || f_peng_ibu_hamil === '') && validationErrors.some(e => e.field === 'Ibu Hamil') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                    <p class="text-[10px] text-slate-500 mt-1">Ibu sedang mengandung</p>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">
                                        Status Janda (Tdk Bekerja) <span class="text-slate-400 font-normal">(Min: 0, Max: 4)</span> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pengeluaran_janda" min="0" max="4" x-model="f_peng_janda" 
                                           :class="(f_peng_janda === null || f_peng_janda === '') && validationErrors.some(e => e.field === 'Status Janda (Tdk Bekerja)') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                           class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                           placeholder="0">
                                    <p class="text-[10px] text-slate-500 mt-1">Janda tidak berpenghasilan</p>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 6: PENDIDIKAN -->
                        <div class="bg-white border-2 border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-slate-50 px-5 py-3 border-b-2 border-slate-200 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">6. Pendidikan</h4>
                            </div>
                            <div class="p-5 max-w-md">
                                <label class="block font-bold text-slate-700 mb-1">
                                    Biaya Perguruan Tinggi <span class="text-slate-400 font-normal">(Min: 0, Max: 10)</span> <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="pengeluaran_pt" min="0" max="10" x-model="f_peng_pt" 
                                       :class="(f_peng_pt === null || f_peng_pt === '') && validationErrors.some(e => e.field === 'Biaya Perguruan Tinggi') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                       class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" 
                                       placeholder="0">
                                <p class="text-[10px] text-slate-500 mt-1">Jumlah anak/anggota keluarga yang menempuh jenjang Kuliah/PT</p>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAHAP 4: TEMPAT TINGGAL & BANTUAN          -->
                    <!-- ========================================== -->
                    <div x-show="hk_step === 4" class="space-y-6">
                        <!-- SECTION 7: STATUS TEMPAT TINGGAL -->
                        <div class="bg-white border-2 border-emerald-200/90 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-3 border-b-2 border-emerald-100 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">7. Status Tempat Tinggal</h4>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Memiliki Rumah / Kontrak <span class="text-red-500">*</span>
                                    </label>
                                    <select name="tempat_rumah" x-model="f_tempat_rumah" required 
                                            :class="!f_tempat_rumah && validationErrors.some(e => e.field === 'Status Kepemilikan Rumah / Kontrak') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                            class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                        <option value="" disabled>-- Pilih Status Rumah --</option>
                                        <option value="5">Memiliki Tempat Tinggal Sendiri</option>
                                        <option value="1">Sewa / Mengontrak</option>
                                        <option value="2">Menumpang (Keluarga / Kerabat)</option>
                                        <option value="3">Bukan Rumah Sendiri (Lainnya)</option>
                                    </select>
                                    <p class="text-[10px] text-slate-500 mt-1">Kepemilikan hunian tempat tinggal saat ini</p>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Penerangan Listrik <span class="text-red-500">*</span>
                                    </label>
                                    <select name="tempat_listrik" x-model="f_tempat_listrik" required 
                                            :class="!f_tempat_listrik && validationErrors.some(e => e.field === 'Penerangan Listrik') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                            class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                        <option value="" disabled>-- Pilih Daya Listrik --</option>
                                        <option value="1">Non Listrik / Menumpang Tetangga</option>
                                        <option value="2">450 Watt</option>
                                        <option value="3">900 Watt</option>
                                        <option value="4">> 900 Watt (1300 Watt ke atas)</option>
                                    </select>
                                    <p class="text-[10px] text-slate-500 mt-1">Daya langganan PLN di tempat tinggal</p>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 8: MENERIMA BANTUAN PEMERINTAH -->
                        <div class="bg-white border-2 border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-slate-50 px-5 py-3 border-b-2 border-slate-200 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">8. Menerima Bantuan Pemerintah</h4>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Bantuan Sosial (Bansos) <span class="text-red-500">*</span>
                                    </label>
                                    <select name="pemerintah_bansos" x-model="f_pemerintah_bansos" required 
                                            :class="!f_pemerintah_bansos && validationErrors.some(e => e.field === 'Bantuan Sosial (Bansos)') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                            class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                        <option value="" disabled>-- Pilih Status Bansos --</option>
                                        <option value="1">Tidak Menerima Bansos</option>
                                        <option value="2">Menerima Bansos (PKH / BPNT / BST)</option>
                                    </select>
                                    <p class="text-[10px] text-slate-500 mt-1">Program bantuan reguler Kemensos/Pemerintah</p>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Jaminan Kesehatan (BPJS/KIS/PBI) <span class="text-red-500">*</span>
                                    </label>
                                    <select name="pemerintah_bpjs" x-model="f_pemerintah_bpjs" required 
                                            :class="!f_pemerintah_bpjs && validationErrors.some(e => e.field === 'Jaminan Kesehatan (BPJS/KIS/PBI)') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                            class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                        <option value="" disabled>-- Pilih Kepesertaan BPJS --</option>
                                        <option value="5">Penerima BPJS/KIS/PBI (Ditanggung Pemerintah)</option>
                                        <option value="2">BPJS Mandiri (Bayar Sendiri)</option>
                                        <option value="1">Bukan Penerima / Tidak Punya BPJS</option>
                                    </select>
                                    <p class="text-[10px] text-slate-500 mt-1">Jenis kepesertaan jaminan kesehatan nasional</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAHAP 5: ASSET & HUTANG                    -->
                    <!-- ========================================== -->
                    <div x-show="hk_step === 5" class="space-y-6">
                        <!-- SECTION 9: KEPEMILIKAN ASSET -->
                        <div class="bg-white border-2 border-emerald-200/90 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-3 border-b-2 border-emerald-100 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">9. Kepemilikan Asset</h4>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Memiliki Kendaraan Bermotor <span class="text-red-500">*</span>
                                    </label>
                                    <select name="asset_motor" x-model="f_asset_motor" required 
                                            :class="!f_asset_motor && validationErrors.some(e => e.field === 'Memiliki Kendaraan Bermotor') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                            class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                        <option value="" disabled>-- Pilih Kepemilikan Kendaraan --</option>
                                        <option value="1">Tidak Memiliki Kendaraan Bermotor</option>
                                        <option value="2">Memiliki Kendaraan Bermotor (Roda 2 / Roda 4)</option>
                                    </select>
                                    <p class="text-[10px] text-slate-500 mt-1">Kepemilikan sepeda motor atau mobil pribadi</p>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                        Memiliki Tabungan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="asset_tabungan" x-model="f_asset_tabungan" required 
                                            :class="!f_asset_tabungan && validationErrors.some(e => e.field === 'Memiliki Tabungan') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                            class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                        <option value="" disabled>-- Pilih Kepemilikan Tabungan --</option>
                                        <option value="1">Tidak Memiliki Tabungan</option>
                                        <option value="2">Memiliki Tabungan / Simpanan Uang</option>
                                    </select>
                                    <p class="text-[10px] text-slate-500 mt-1">Simpanan di bank, koperasi, atau uang tunai</p>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 10: BEBAN KEUANGAN -->
                        <div class="bg-white border-2 border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-slate-50 px-5 py-3 border-b-2 border-slate-200 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">10. Beban Keuangan</h4>
                            </div>
                            <div class="p-5 max-w-md">
                                <label class="block font-bold text-slate-700 uppercase mb-1.5">
                                    Memiliki Hutang <span class="text-red-500">*</span>
                                </label>
                                <select name="beban_hutang" x-model="f_beban_hutang" required 
                                        :class="!f_beban_hutang && validationErrors.some(e => e.field === 'Beban Hutang') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'"
                                        class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                    <option value="" disabled>-- Pilih Beban Hutang --</option>
                                    <option value="1">Tidak Memiliki Hutang</option>
                                    <option value="2">Memiliki Hutang Kebutuhan Pokok Mendesak</option>
                                </select>
                                <p class="text-[10px] text-slate-500 mt-1">Tanggungan hutang untuk bertahan hidup / pengobatan</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- FOOTER NAVIGATION BAR (MOBILE-FRIENDLY & THUMB ACCESSIBLE) -->
                <div class="bg-slate-100 border-t-2 border-slate-200 p-3 sm:px-6 sm:py-3.5 flex flex-col gap-2 shrink-0 shadow-inner">
                    <!-- PEMBERITAHUAN VALIDASI DI ATAS TOMBOL SUBMIT -->
                    <template x-if="hasAttemptedSubmit && validationErrors.length > 0">
                        <div class="p-2.5 sm:p-3 bg-rose-50 border-2 border-rose-300 rounded-xl flex items-center justify-between gap-3 text-xs animate-shake">
                            <div class="flex items-center gap-2 text-rose-800 font-semibold min-w-0">
                                <span class="w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0">!</span>
                                <span class="truncate">
                                    Ada <span class="font-black text-rose-900" x-text="validationErrors.length"></span> isian yang belum lengkap pada tab:
                                    <span class="font-bold underline" x-text="[...new Set(validationErrors.map(e => e.step))].map(s => 'Tahap ' + s).join(', ')"></span>
                                </span>
                            </div>
                            <button type="button" @click="showValidationModal = true" 
                                    class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] rounded-lg shrink-0 transition shadow-xs">
                                Lihat Rincian
                            </button>
                        </div>
                    </template>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                        <div class="flex items-center gap-2 order-1 sm:order-1">
                            <button type="button" x-show="hk_step > 1" @click="hk_step--" 
                                    class="w-auto px-4 py-3 sm:py-2.5 border-2 border-slate-300 bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 font-bold text-xs rounded-xl shadow-xs transition text-center">
                                <span>←</span> <span>Kembali</span>
                            </button>
                            <button type="button" x-show="hk_step === 1" @click="activeModal = ''" 
                                    class="w-auto sm:flex-none px-4 py-3 sm:py-2.5 border-2 border-slate-300 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs rounded-xl transition text-center">
                                Tutup
                            </button>
                            
                            <button type="button" x-show="hk_step < 5" @click="hk_step++" 
                                    class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-[#118243] hover:bg-[#0e6c37] active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                                <span>Berikutnya</span>
                                <span>→</span>
                            </button>
                        </div>

                        <div class="flex items-center gap-2 order-2 sm:order-2">

                            <button type="submit" 
                                    class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-gradient-to-r from-emerald-600 to-[#118243] hover:from-emerald-700 hover:to-[#0e6c37] active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span>Hitung & Simpan Had Kifayah</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- MODAL ALERT VALIDASI KOMPREHENSIF (OPSI B) -->
            <div x-show="showValidationModal" x-cloak 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div x-show="showValidationModal"
                     x-transition:enter="transition ease-out duration-250 transform"
                     x-transition:enter-start="opacity-0 scale-90"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-90"
                     class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border-2 border-rose-300 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-slate-800">Ada Form yang Belum Diisi!</h4>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Mohon lengkapi isian berikut sebelum menghitung Had Kifayah:
                            </p>
                        </div>
                    </div>

                    <!-- LIST ERROR PER TAHAP -->
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-1 text-xs">
                        <template x-for="(err, idx) in validationErrors" :key="idx">
                            <div class="p-2.5 bg-rose-50/80 border border-rose-200 rounded-xl flex items-center justify-between gap-2">
                                <div>
                                    <span class="font-bold text-rose-900" x-text="err.tabName"></span>
                                    <p class="text-rose-700 text-[11px]" x-text="'Kolom: ' + err.field"></p>
                                </div>
                                <button type="button" @click="goToStep(err.step)" 
                                        class="px-2.5 py-1 bg-white hover:bg-rose-100 text-rose-700 border border-rose-300 rounded-lg text-[10px] font-bold shrink-0 transition">
                                    Buka Tab →
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="showValidationModal = false" 
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL ASESMEN (5-STEP MOBILE-FIRST WIZARD - SEMUA INPUT DEFAULT KOSONG)    -->
    <!-- ========================================================================= -->
    @php
        $as = $survey->asesmen;
        $pm = $survey->pengajuanMustahik;
        $pengajuan = $pm?->pengajuan;
        $mustahikPerson = $pm?->mustahik?->person ?? $pengajuan?->mustahik?->person;
        $alamatLengkapDb = $mustahikPerson?->alamat_lengkap ?: ($mustahikPerson?->alamat ?: ($pengajuan?->mustahik_alamat_snapshot ?: ''));
        $teleponDb = $mustahikPerson?->telepon ?: ($mustahikPerson?->handphone ?: ($pengajuan?->mustahik_kontak_snapshot ?: ''));

        // Tahap 1: Identitas & Petugas
        $defaultAsSurveyor = $as?->nama_surveyor ?? ($survey->surveyor ?? '');
        $defaultAsProgram = $as?->jenis_program ?? ($pm?->program?->nama ?? '');
        $defaultAsSubProgram = $as?->sub_program ?? ($pm?->subprogram?->nama ?? '');

        $defaultAsMustahik = $as?->nama_mustahik ?? ($mustahikPerson?->nama ?? ($pengajuan?->mustahik_nama_snapshot ?? ''));
        $defaultAsNkk = $as?->nkk ?? ($mustahikPerson?->nomor_kk ?? '');
        $defaultAsNik = $as?->nik ?? ($mustahikPerson?->nik ?? ($pengajuan?->mustahik_nik_snapshot ?? ''));
        $defaultAsTempatLahir = $as?->tempat_lahir ?? ($mustahikPerson?->tempat_lahir ?? '');
        $defaultAsTanggalLahir = $as?->tanggal_lahir ? $as->tanggal_lahir->format('Y-m-d') : ($mustahikPerson?->tanggal_lahir ? \Carbon\Carbon::parse($mustahikPerson->tanggal_lahir)->format('Y-m-d') : '');
        $defaultAsJk = $as?->jenis_kelamin ?? ($mustahikPerson?->jenis_kelamin ?? '');
        $defaultAsAlamat = $as?->alamat_lengkap ?? ($alamatLengkapDb !== '-' ? $alamatLengkapDb : '');
        $defaultAsIdProvinsi = $as?->id_provinsi ?? ($mustahikPerson?->id_provinsi ?? '');
        $defaultAsIdKabupaten = $as?->id_kabupaten ?? ($mustahikPerson?->id_kabupaten ?? '');
        $defaultAsIdKecamatan = $as?->id_kecamatan ?? ($mustahikPerson?->id_kecamatan ?? '');
        $defaultAsIdKelurahan = $as?->id_kelurahan ?? ($mustahikPerson?->id_kelurahan ?? '');
        $defaultAsKodePos = $as?->kode_pos ?? ($mustahikPerson?->kode_pos ?? '');
        $defaultAsDesa = $as?->desa ?? ($mustahikPerson?->kelurahan?->nama ?? ($mustahikPerson?->desa ?? ''));
        $defaultAsKecamatan = $as?->kecamatan ?? ($mustahikPerson?->kecamatan?->nama ?? ($mustahikPerson?->kecamatan ?? ''));
        $defaultAsNohp = $as?->nohp ?? ($teleponDb !== '-' ? $teleponDb : '');
        $defaultAsBersedia = $as?->bersedia_disurvei ?? 'YA';

        // Tahap 2: Ekonomi & Tanggungan
        $defaultAsAgama = $as?->agama ?? '';
        $defaultAsTanggungan = $as?->jumlah_tanggungan !== null ? $as->jumlah_tanggungan : ($pengajuan?->jumlah_orang ? max(0, $pengajuan->jumlah_orang - 1) : '');
        $defaultAsSepeda = $as?->jumlah_sepeda ?? '';
        $defaultAsMotor = $as?->jumlah_sepeda_motor ?? '';
        $defaultAsMobil = $as?->jumlah_mobil ?? '';
        $defaultAsPenghasilan = ($as?->total_penghasilan_perbulan !== null && $as?->total_penghasilan_perbulan !== '') ? number_format((float) preg_replace('/\D/', '', (string) $as->total_penghasilan_perbulan), 0, ',', '.') : '';

        // Tahap 3: Kondisi Rumah & Aset
        $defaultAsStatusTanah = $as?->status_tanah ?? '';
        $defaultAsStatusRumah = $as?->status_rumah ?? '';
        $defaultAsBuktiDokumen = $as?->bukti_dokumen ?? '';
        $defaultAsKondisiRumah = $as?->kondisi_tempat_tinggal ?? '';
        $defaultAsKondisiAtap = $as?->kondisi_atap ?? '';
        $defaultAsKondisiLantai = $as?->kondisi_lantai ?? '';
        $defaultAsKondisiDinding = $as?->kondisi_dinding ?? '';
        $defaultAsKondisiDapur = $as?->kondisi_dapur ?? '';
        $defaultAsKondisiKamarMandi = $as?->kondisi_kamar_mandi ?? '';
        $defaultAsSumberAir = $as?->sumber_air_bersih ?? '';
        $defaultAsDayaListrik = $as?->daya_listrik ?? '';

        // Tahap 4: Usaha & Kesehatan
        $defaultAsPunyaUsaha = $as?->punya_usaha ?? '';
        $defaultAsLamaUsaha = $as?->lama_usaha ?? '';
        $defaultAsHasilPermohonan = $as?->hasil_akhir_permohonan ?? '';
        $defaultAsBpjs = $as?->bpjs_kesehatan ?? '';
        $defaultAsStatusBpjs = $as?->status_bpjs_kesehatan ?? '';
        $defaultAsRiwayatPenyakit = $as?->riwayat_penyakit ?? '';

        // Tahap 5: Penilaian
        $defaultAsSkor = $as?->skor_penilaian ? (int) $as->skor_penilaian : 0;
        $defaultAsHasilSurvei = $as?->hasil_survei ?? '';

        $asConfig = [
            'as_nama_surveyor' => old('as_nama_surveyor', $defaultAsSurveyor),
            'as_jenis_program' => old('as_jenis_program', $defaultAsProgram),
            'as_sub_program' => old('as_sub_program', $defaultAsSubProgram),
            'as_nama_mustahik' => old('as_nama_mustahik', $defaultAsMustahik),
            'as_nik' => old('as_nik', $defaultAsNik),
            'as_nkk' => old('as_nkk', $defaultAsNkk),
            'as_tempat_lahir' => old('as_tempat_lahir', $defaultAsTempatLahir),
            'as_tanggal_lahir' => old('as_tanggal_lahir', $defaultAsTanggalLahir),
            'as_jenis_kelamin' => old('as_jenis_kelamin', $defaultAsJk),
            'as_nohp' => old('as_nohp', $defaultAsNohp),
            'as_bersedia_disurvei' => old('as_bersedia_disurvei', $defaultAsBersedia),
            'as_alamat_lengkap' => old('as_alamat_lengkap', $defaultAsAlamat),
            'as_desa' => old('as_desa', $defaultAsDesa),
            'as_kecamatan' => old('as_kecamatan', $defaultAsKecamatan),
            'wilayah' => [
                'provinsis' => $provinsis ?? [],
                'provinsi' => old('as_id_provinsi', $defaultAsIdProvinsi),
                'kabupaten' => old('as_id_kabupaten', $defaultAsIdKabupaten),
                'kecamatan' => old('as_id_kecamatan', $defaultAsIdKecamatan),
                'kelurahan' => old('as_id_kelurahan', $defaultAsIdKelurahan),
                'kode_pos' => old('as_kode_pos', $defaultAsKodePos),
                'alamat' => old('as_alamat_lengkap', $defaultAsAlamat),
            ],
            'as_agama' => old('as_agama', $defaultAsAgama),
            'as_jumlah_tanggungan' => old('as_jumlah_tanggungan', $defaultAsTanggungan),
            'as_total_penghasilan_perbulan' => old('as_total_penghasilan_perbulan', $defaultAsPenghasilan),
            'as_jumlah_sepeda' => old('as_jumlah_sepeda', $defaultAsSepeda),
            'as_jumlah_sepeda_motor' => old('as_jumlah_sepeda_motor', $defaultAsMotor),
            'as_jumlah_mobil' => old('as_jumlah_mobil', $defaultAsMobil),
            'as_status_tanah' => old('as_status_tanah', $defaultAsStatusTanah),
            'as_status_rumah' => old('as_status_rumah', $defaultAsStatusRumah),
            'as_bukti_dokumen' => old('as_bukti_dokumen', $defaultAsBuktiDokumen),
            'as_kondisi_tempat_tinggal' => old('as_kondisi_tempat_tinggal', $defaultAsKondisiRumah),
            'as_kondisi_atap' => old('as_kondisi_atap', $defaultAsKondisiAtap),
            'as_kondisi_lantai' => old('as_kondisi_lantai', $defaultAsKondisiLantai),
            'as_kondisi_dinding' => old('as_kondisi_dinding', $defaultAsKondisiDinding),
            'as_kondisi_dapur' => old('as_kondisi_dapur', $defaultAsKondisiDapur),
            'as_kondisi_kamar_mandi' => old('as_kondisi_kamar_mandi', $defaultAsKondisiKamarMandi),
            'as_sumber_air_bersih' => old('as_sumber_air_bersih', $defaultAsSumberAir),
            'as_daya_listrik' => old('as_daya_listrik', $defaultAsDayaListrik),
            'as_punya_usaha' => old('as_punya_usaha', $defaultAsPunyaUsaha),
            'as_lama_usaha' => old('as_lama_usaha', $defaultAsLamaUsaha),
            'as_hasil_akhir_permohonan' => old('as_hasil_akhir_permohonan', $defaultAsHasilPermohonan),
            'as_bpjs_kesehatan' => old('as_bpjs_kesehatan', $defaultAsBpjs),
            'as_status_bpjs_kesehatan' => old('as_status_bpjs_kesehatan', $defaultAsStatusBpjs),
            'as_riwayat_penyakit' => old('as_riwayat_penyakit', $defaultAsRiwayatPenyakit),
            'as_skor_penilaian' => (int) old('as_skor_penilaian', $defaultAsSkor),
            'as_hasil_survei' => old('as_hasil_survei', $defaultAsHasilSurvei),
        ];
    @endphp

    <div x-show="activeModal === 'asesmen'" x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         x-data="asesmenModalForm(@js($asConfig))">
        <div x-show="activeModal === 'asesmen'"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="bg-white rounded-t-3xl sm:rounded-3xl max-w-4xl w-full shadow-2xl border-2 border-slate-300 overflow-hidden flex flex-col h-[94vh] sm:h-[90vh] max-h-[96vh] relative">

            <!-- HEADER MODAL -->
            <div class="bg-gradient-to-r from-[#118243] via-[#0d6b37] to-[#0a522a] text-white px-4 sm:px-6 py-3.5 sm:py-4 flex items-center justify-between shadow-md shrink-0 border-b border-emerald-700">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-white/15 flex items-center justify-center text-white shadow-inner shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-white leading-tight tracking-tight">Survey Asesmen Lapangan</h3>
                        <p class="text-[11px] sm:text-xs text-emerald-100 font-medium mt-0.5">
                            Tahap <span class="font-bold text-white underline underline-offset-2" x-text="as_step"></span> dari 5 — Isian Manual Surveyor Lapangan
                        </p>
                    </div>
                </div>
                <button type="button" @click="activeModal = ''" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/30 text-white flex items-center justify-center transition shadow-xs">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- STEPPER PROGRESS TABS -->
            <div class="bg-slate-100/95 border-b-2 border-slate-200 px-3 sm:px-6 py-2.5 shrink-0 overflow-x-auto no-scrollbar scroll-smooth">
                <div class="flex items-center gap-1.5 sm:gap-2 min-w-max text-xs font-bold">
                    <button type="button" x-ref="as_tab_1" @click="as_step = 1" 
                            :class="tabHasError(1) ? 'bg-rose-50 text-rose-700 border-2 border-rose-500 ring-2 ring-rose-200' : (as_step === 1 ? 'bg-[#118243] text-white shadow-md ring-2 ring-emerald-600/30' : (isStep1Valid() ? 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300' : 'bg-white text-slate-600 border-2 border-slate-300 hover:border-slate-400'))"
                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-xs active:scale-95">
                        <template x-if="tabHasError(1)"><span class="w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-black">!</span></template>
                        <template x-if="!tabHasError(1) && isStep1Valid()"><span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center"><svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></span></template>
                        <template x-if="!tabHasError(1) && !isStep1Valid()"><span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]" :class="as_step === 1 ? 'bg-white text-[#118243]' : 'bg-slate-200 text-slate-700'">1</span></template>
                        <span>1. Identitas</span>
                    </button>
                    <span class="text-slate-400 font-bold">›</span>
                    <button type="button" x-ref="as_tab_2" @click="as_step = 2" 
                            :class="tabHasError(2) ? 'bg-rose-50 text-rose-700 border-2 border-rose-500 ring-2 ring-rose-200' : (as_step === 2 ? 'bg-[#118243] text-white shadow-md ring-2 ring-emerald-600/30' : (isStep2Valid() ? 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300' : 'bg-white text-slate-600 border-2 border-slate-300 hover:border-slate-400'))"
                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-xs active:scale-95">
                        <template x-if="tabHasError(2)"><span class="w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-black">!</span></template>
                        <template x-if="!tabHasError(2) && isStep2Valid()"><span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center"><svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></span></template>
                        <template x-if="!tabHasError(2) && !isStep2Valid()"><span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]" :class="as_step === 2 ? 'bg-white text-[#118243]' : 'bg-slate-200 text-slate-700'">2</span></template>
                        <span>2. Ekonomi</span>
                    </button>
                    <span class="text-slate-400 font-bold">›</span>
                    <button type="button" x-ref="as_tab_3" @click="as_step = 3" 
                            :class="tabHasError(3) ? 'bg-rose-50 text-rose-700 border-2 border-rose-500 ring-2 ring-rose-200' : (as_step === 3 ? 'bg-[#118243] text-white shadow-md ring-2 ring-emerald-600/30' : (isStep3Valid() ? 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300' : 'bg-white text-slate-600 border-2 border-slate-300 hover:border-slate-400'))"
                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-xs active:scale-95">
                        <template x-if="tabHasError(3)"><span class="w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-black">!</span></template>
                        <template x-if="!tabHasError(3) && isStep3Valid()"><span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center"><svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></span></template>
                        <template x-if="!tabHasError(3) && !isStep3Valid()"><span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]" :class="as_step === 3 ? 'bg-white text-[#118243]' : 'bg-slate-200 text-slate-700'">3</span></template>
                        <span>3. Aset & Rumah</span>
                    </button>
                    <span class="text-slate-400 font-bold">›</span>
                    <button type="button" x-ref="as_tab_4" @click="as_step = 4" 
                            :class="tabHasError(4) ? 'bg-rose-50 text-rose-700 border-2 border-rose-500 ring-2 ring-rose-200' : (as_step === 4 ? 'bg-[#118243] text-white shadow-md ring-2 ring-emerald-600/30' : (isStep4Valid() ? 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300' : 'bg-white text-slate-600 border-2 border-slate-300 hover:border-slate-400'))"
                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-xs active:scale-95">
                        <template x-if="tabHasError(4)"><span class="w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-black">!</span></template>
                        <template x-if="!tabHasError(4) && isStep4Valid()"><span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center"><svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></span></template>
                        <template x-if="!tabHasError(4) && !isStep4Valid()"><span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]" :class="as_step === 4 ? 'bg-white text-[#118243]' : 'bg-slate-200 text-slate-700'">4</span></template>
                        <span>4. Usaha & BPJS</span>
                    </button>
                    <span class="text-slate-400 font-bold">›</span>
                    <button type="button" x-ref="as_tab_5" @click="as_step = 5" 
                            :class="tabHasError(5) ? 'bg-rose-50 text-rose-700 border-2 border-rose-500 ring-2 ring-rose-200' : (as_step === 5 ? 'bg-[#118243] text-white shadow-md ring-2 ring-emerald-600/30' : (isStep5Valid() ? 'bg-emerald-50 text-emerald-800 border-2 border-emerald-300' : 'bg-white text-slate-600 border-2 border-slate-300 hover:border-slate-400'))"
                            class="px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl transition-all flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-xs active:scale-95">
                        <template x-if="tabHasError(5)"><span class="w-4 h-4 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-black">!</span></template>
                        <template x-if="!tabHasError(5) && isStep5Valid()"><span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center"><svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></span></template>
                        <template x-if="!tabHasError(5) && !isStep5Valid()"><span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px]" :class="as_step === 5 ? 'bg-white text-[#118243]' : 'bg-slate-200 text-slate-700'">5</span></template>
                        <span>5. Penilaian</span>
                    </button>
                </div>
            </div>

            <!-- FORM BODY -->
            <form method="POST" action="{{ route('public.survey.asesmen', $token) }}" enctype="multipart/form-data" novalidate @submit="validateAndSubmit($event)" class="flex flex-col flex-1 min-h-0">
                @csrf
                <input type="hidden" name="as_nama_surveyor" :value="as_nama_surveyor">
                <input type="hidden" name="as_jenis_program" :value="as_jenis_program">
                <input type="hidden" name="as_sub_program" :value="as_sub_program">

                <div class="h-1.5 w-full bg-slate-200 shrink-0">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-[#118243] transition-all duration-300" :style="'width: ' + (as_step * 20) + '%'"></div>
                </div>

                <div x-ref="asesmenFormBody" class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-6 text-xs">
                    <!-- TAHAP 1: IDENTITAS -->
                    <div x-show="as_step === 1" class="space-y-5">
                        <div class="p-3 bg-emerald-50 border-2 border-emerald-300 rounded-xl flex items-center gap-2.5 text-xs text-emerald-900 font-semibold shadow-xs">
                            <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span><strong>Informasi:</strong> Data yang telah tercatat pada sistem/permohonan otomatis terisi sebagai default. Silakan verifikasi dan lengkapi kolom yang masih kosong sesuai kondisi faktual lapangan.</span>
                        </div>
                        <div class="bg-white border-2 border-emerald-200/90 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-3 border-b-2 border-emerald-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                    <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">Data Identitas Mustahik</h4>
                                </div>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Nama Mustahik <span class="text-red-500">*</span></label>
                                    <input type="text" name="as_nama_mustahik" x-model="as_nama_mustahik" required :class="!as_nama_mustahik && asesmenErrors.some(e => e.field === 'Nama Mustahik') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'" class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition" placeholder="Ketik nama lengkap mustahik / kepala keluarga...">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">NIK (16 Digit) <span class="text-red-500">*</span></label>
                                    <input type="text" name="as_nik" x-model="as_nik" maxlength="16" required :class="!as_nik && asesmenErrors.some(e => e.field.includes('NIK')) ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'" class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition" placeholder="Ketik 16 digit NIK...">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">NKK / No. Kartu Keluarga (16 Digit)</label>
                                    <input type="text" name="as_nkk" x-model="as_nkk" maxlength="16" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" placeholder="Ketik 16 digit NKK...">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                                    <select name="as_jenis_kelamin" x-model="as_jenis_kelamin" required :class="!as_jenis_kelamin && asesmenErrors.some(e => e.field === 'Jenis Kelamin') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'" class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                        <option value="">-- Pilih Jenis Kelamin --</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Bersedia Disurvei? <span class="text-red-500">*</span></label>
                                    <select name="as_bersedia_disurvei" x-model="as_bersedia_disurvei" required :class="!as_bersedia_disurvei && asesmenErrors.some(e => e.field === 'Kesediaan Disurvei') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'" class="w-full rounded-xl border-2 bg-white p-3 text-xs font-bold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                        <option value="">-- Pilih Status Kesediaan --</option>
                                        <option value="YA">YA (Bersedia)</option>
                                        <option value="TIDAK">TIDAK (Menolak)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Tempat Lahir</label>
                                    <input type="text" name="as_tempat_lahir" x-model="as_tempat_lahir" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" placeholder="Ketik kota/kabupaten lahir...">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Tanggal Lahir</label>
                                    <input type="date" name="as_tanggal_lahir" x-model="as_tanggal_lahir" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">No. HP / WhatsApp</label>
                                    <input type="tel" name="as_nohp" x-model="as_nohp" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" placeholder="Ketik no. WhatsApp / HP...">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Agama</label>
                                    <select name="as_agama" x-model="as_agama" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                        <option value="">-- Pilih Agama --</option>
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen">Kristen</option>
                                        <option value="Katolik">Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Buddha">Buddha</option>
                                        <option value="Konghucu">Konghucu</option>
                                    </select>
                                </div>
                                <!-- Standar Alamat & Wilayah -->
                                <div class="md:col-span-2 pt-2 border-t-2 border-emerald-100">
                                    <span class="font-bold text-slate-800 text-xs uppercase tracking-wider block mb-1">Wilayah & Alamat Domisili / Faktual</span>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Provinsi</label>
                                    <select name="as_id_provinsi" x-model="wilayah.selectedProvinsi" @change="wilayah.onProvinsiChange()" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                        <option value="">-- Pilih Provinsi --</option>
                                        <template x-for="p in wilayah.provinsis" :key="p.id_wilayah">
                                            <option :value="p.id_wilayah" x-text="p.nama" :selected="wilayah.selectedProvinsi === p.id_wilayah"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Kabupaten / Kota</label>
                                    <select name="as_id_kabupaten" x-model="wilayah.selectedKabupaten" @change="wilayah.onKabupatenChange()" :disabled="!wilayah.selectedProvinsi || wilayah.loading.kabupaten" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="">-- Pilih Kabupaten/Kota --</option>
                                        <template x-for="k in wilayah.kabupatens" :key="k.id_wilayah">
                                            <option :value="k.id_wilayah" x-text="k.nama" :selected="wilayah.selectedKabupaten === k.id_wilayah"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Kecamatan</label>
                                    <select name="as_id_kecamatan" x-model="wilayah.selectedKecamatan" @change="wilayah.onKecamatanChange()" :disabled="!wilayah.selectedKabupaten || wilayah.loading.kecamatan" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="">-- Pilih Kecamatan --</option>
                                        <template x-for="c in wilayah.kecamatans" :key="c.id_wilayah">
                                            <option :value="c.id_wilayah" x-text="c.nama" :selected="wilayah.selectedKecamatan === c.id_wilayah"></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Desa / Kelurahan</label>
                                    <select name="as_id_kelurahan" x-model="wilayah.selectedKelurahan" @change="wilayah.onKelurahanChange()" :disabled="!wilayah.selectedKecamatan || wilayah.loading.kelurahan" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition disabled:bg-slate-50 disabled:text-slate-400">
                                        <option value="">-- Pilih Desa/Kelurahan --</option>
                                        <template x-for="l in wilayah.kelurahans" :key="l.id_wilayah">
                                            <option :value="l.id_wilayah" x-text="l.nama" :selected="wilayah.selectedKelurahan === l.id_wilayah"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Kode Pos</label>
                                    <input type="text" name="as_kode_pos" x-model="wilayah.kodePos" placeholder="Kode Pos (otomatis terisi saat memilih Desa/Kelurahan)" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Alamat (Jl, No, RT/RW, Dusun) <span class="text-red-500">*</span></label>
                                    <textarea name="as_alamat_lengkap" x-model="as_alamat_lengkap" rows="2" required :class="!as_alamat_lengkap && asesmenErrors.some(e => e.field === 'Alamat Lengkap') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'" class="w-full rounded-xl border-2 bg-white p-3 text-xs font-medium shadow-xs focus:ring-4 focus:ring-emerald-100 transition" placeholder="Contoh: Jl. Diponegoro No. 12, RT 02 / RW 01, Dusun Karanganyar"></textarea>
                                </div>

                                <input type="hidden" name="as_desa" :value="wilayah.kelurahans.find(k => k.id_wilayah == wilayah.selectedKelurahan)?.nama || ''">
                                <input type="hidden" name="as_kecamatan" :value="wilayah.kecamatans.find(c => c.id_wilayah == wilayah.selectedKecamatan)?.nama || ''">
                            </div>
                        </div>
                    </div>
                    <!-- TAHAP 2: EKONOMI -->
                    <div x-show="as_step === 2" class="space-y-5">
                        <div class="p-3 bg-amber-50 border-2 border-amber-300 rounded-xl flex items-center gap-2.5 text-xs text-amber-900 font-semibold shadow-xs">
                            <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-xs shrink-0">2</span>
                            <span>Karakteristik tanggungan keluarga, pendapatan bulanan, dan kepemilikan aset transportasi. (Default kosong)</span>
                        </div>
                        <div class="bg-white border-2 border-emerald-200/90 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-3 border-b-2 border-emerald-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                    <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">Penghasilan & Tanggungan</h4>
                                </div>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Jumlah Tanggungan (Jiwa) <span class="text-red-500">*</span></label>
                                    <input type="number" name="as_jumlah_tanggungan" x-model="as_jumlah_tanggungan" min="0" required :class="(as_jumlah_tanggungan === null || as_jumlah_tanggungan === '') && asesmenErrors.some(e => e.field === 'Jumlah Tanggungan') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'" class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition" placeholder="Ketik jumlah jiwa yang ditanggung...">
                                    <p class="text-[10px] text-slate-500 mt-1">Jumlah anggota keluarga yang dinafkahi/dibiayai</p>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Total Penghasilan Per Bulan (Rp) <span class="text-red-500">*</span></label>
                                    <input type="text" inputmode="numeric" x-number name="as_total_penghasilan_perbulan" x-model="as_total_penghasilan_perbulan" required :class="(as_total_penghasilan_perbulan === null || as_total_penghasilan_perbulan === '') && asesmenErrors.some(e => e.field.includes('Penghasilan')) ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'" class="w-full rounded-xl border-2 bg-white p-3 text-xs font-semibold shadow-xs focus:ring-4 focus:ring-emerald-100 transition" placeholder="Ketik nominal rupiah per bulan...">
                                    <p class="text-[10px] text-slate-500 mt-1">Ketik 0 jika tidak memiliki pemasukan tetap</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white border-2 border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-slate-50 px-5 py-3 border-b-2 border-slate-200 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                    <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">Kepemilikan Aset Kendaraan</h4>
                                </div>
                            </div>
                            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1.5">Sepeda Ontel</label>
                                    <select name="as_jumlah_sepeda" x-model="as_jumlah_sepeda" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 bg-white p-2.5 text-xs font-medium shadow-xs transition">
                                        <option value="">-- Pilih --</option>
                                        <option value="TIDAK ADA">Tidak Ada</option>
                                        <option value="1 UNIT">1 Unit</option>
                                        <option value="LEBIH DARI 1">Lebih dari 1</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1.5">Sepeda Motor</label>
                                    <select name="as_jumlah_sepeda_motor" x-model="as_jumlah_sepeda_motor" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 bg-white p-2.5 text-xs font-medium shadow-xs transition">
                                        <option value="">-- Pilih --</option>
                                        <option value="TIDAK ADA">Tidak Ada</option>
                                        <option value="1 UNIT">1 Unit</option>
                                        <option value="LEBIH DARI 1">Lebih dari 1</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1.5">Mobil</label>
                                    <select name="as_jumlah_mobil" x-model="as_jumlah_mobil" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 bg-white p-2.5 text-xs font-medium shadow-xs transition">
                                        <option value="">-- Pilih --</option>
                                        <option value="TIDAK ADA">Tidak Ada</option>
                                        <option value="1 UNIT">1 Unit</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- TAHAP 3: ASET & RUMAH -->
                    <div x-show="as_step === 3" class="space-y-5">
                        <div class="p-3 bg-emerald-50 border-2 border-emerald-200 rounded-xl flex items-center gap-2.5 text-xs text-emerald-900 font-semibold shadow-xs">
                            <span class="w-5 h-5 rounded-full bg-[#118243] text-white flex items-center justify-center font-bold text-xs shrink-0">3</span>
                            <span>Kondisi fisik tempat tinggal mustahik beserta bukti foto faktual. Mendukung fitur <strong>Drag & Drop</strong> dan upload kamera HP.</span>
                        </div>
                        <div class="bg-white border-2 border-emerald-200/90 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-3 border-b-2 border-emerald-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                    <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">Kondisi Atap & Lantai</h4>
                                </div>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-3 p-4 bg-slate-50/80 rounded-2xl border border-slate-200" x-data="{ isDragging: false, fileName: '', previewUrl: '' }">
                                    <label class="block font-bold text-slate-800 uppercase text-xs">Kondisi Atap <span class="text-red-500">*</span></label>
                                    <select name="as_kondisi_atap" x-model="as_kondisi_atap" required :class="!as_kondisi_atap && asesmenErrors.some(e => e.field === 'Kondisi Atap') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'" class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-semibold shadow-xs transition">
                                        <option value="">-- Pilih Kondisi Atap --</option>
                                        <option value="Genteng">Genteng Biasa</option>
                                        <option value="Seng / Asbes">Seng / Asbes</option>
                                        <option value="Bambu / Rumbia">Bambu / Daun Rumbia / Ijuk</option>
                                        <option value="Bocor Parah / Rusak">Bocor Parah / Rusak Berat</option>
                                    </select>
                                    <div class="pt-1">
                                        <label class="block font-semibold text-slate-700 text-[11px] mb-1.5 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>Foto Kondisi Atap (Drag & Drop / Kamera)</span>
                                        </label>
                                        <div @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="isDragging = false; const dt = $event.dataTransfer; if (dt.files.length) { $refs.fileInputAtap.files = dt.files; fileName = dt.files[0].name; previewUrl = URL.createObjectURL(dt.files[0]); }" :class="isDragging ? 'border-[#118243] bg-emerald-50/80 ring-2 ring-emerald-300' : 'border-dashed border-slate-300 bg-white hover:border-emerald-500 hover:bg-emerald-50/20'" class="relative rounded-xl border-2 p-3 text-center transition flex flex-col items-center justify-center cursor-pointer min-h-[95px]" @click="$refs.fileInputAtap.click()">
                                            <input type="file" name="as_foto_atap" x-ref="fileInputAtap" accept="image/*" capture="environment" class="hidden" @change="if ($event.target.files.length) { fileName = $event.target.files[0].name; previewUrl = URL.createObjectURL($event.target.files[0]); }">
                                            <template x-if="!previewUrl">
                                                <div class="flex flex-col items-center space-y-1">
                                                    <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                    <p class="text-[11px] font-bold text-slate-700">Tarik & Lepas file foto ke sini</p>
                                                    <p class="text-[10px] text-slate-500">atau <span class="text-[#118243] font-semibold underline">klik untuk buka kamera / galeri HP</span></p>
                                                </div>
                                            </template>
                                            <template x-if="previewUrl">
                                                <div class="flex items-center gap-3 w-full text-left">
                                                    <img :src="previewUrl" class="w-12 h-12 object-cover rounded-lg border border-slate-300 shrink-0">
                                                    <div class="min-w-0 flex-1">
                                                        <span class="text-[11px] font-bold text-emerald-800 block truncate" x-text="fileName"></span>
                                                        <span class="text-[10px] text-slate-500">File siap diupload (Klik untuk ganti)</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 shrink-0">
                                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                        <button type="button" @click.stop="fileName = ''; previewUrl = ''; $refs.fileInputAtap.value = '';" class="px-2 py-1 text-[11px] font-bold text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">Hapus</button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                        @if($as?->foto_atap)<p class="text-[10px] text-emerald-700 font-semibold mt-1 flex items-center gap-1"><svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>Foto atap sudah ada di sistem.</span></p>@endif
                                    </div>
                                </div>
                                <div class="space-y-3 p-4 bg-slate-50/80 rounded-2xl border border-slate-200" x-data="{ isDragging: false, fileName: '', previewUrl: '' }">
                                    <label class="block font-bold text-slate-800 uppercase text-xs">Kondisi Lantai <span class="text-red-500">*</span></label>
                                    <select name="as_kondisi_lantai" x-model="as_kondisi_lantai" required :class="!as_kondisi_lantai && asesmenErrors.some(e => e.field === 'Kondisi Lantai') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'" class="w-full rounded-xl border-2 bg-white p-2.5 text-xs font-semibold shadow-xs transition">
                                        <option value="">-- Pilih Kondisi Lantai --</option>
                                        <option value="Keramik">Keramik / Ubin Baik</option>
                                        <option value="Semen / Plesteran">Semen / Plesteran Kasar</option>
                                        <option value="Tanah">Tanah Liat</option>
                                        <option value="Papan Kayu">Papan Kayu / Bambu</option>
                                    </select>
                                    <div class="pt-1">
                                        <label class="block font-semibold text-slate-700 text-[11px] mb-1.5 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>Foto Kondisi Lantai (Drag & Drop / Kamera)</span>
                                        </label>
                                        <div @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="isDragging = false; const dt = $event.dataTransfer; if (dt.files.length) { $refs.fileInputLantai.files = dt.files; fileName = dt.files[0].name; previewUrl = URL.createObjectURL(dt.files[0]); }" :class="isDragging ? 'border-[#118243] bg-emerald-50/80 ring-2 ring-emerald-300' : 'border-dashed border-slate-300 bg-white hover:border-emerald-500 hover:bg-emerald-50/20'" class="relative rounded-xl border-2 p-3 text-center transition flex flex-col items-center justify-center cursor-pointer min-h-[95px]" @click="$refs.fileInputLantai.click()">
                                            <input type="file" name="as_foto_lantai" x-ref="fileInputLantai" accept="image/*" capture="environment" class="hidden" @change="if ($event.target.files.length) { fileName = $event.target.files[0].name; previewUrl = URL.createObjectURL($event.target.files[0]); }">
                                            <template x-if="!previewUrl">
                                                <div class="flex flex-col items-center space-y-1">
                                                    <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                    <p class="text-[11px] font-bold text-slate-700">Tarik & Lepas file foto ke sini</p>
                                                    <p class="text-[10px] text-slate-500">atau <span class="text-[#118243] font-semibold underline">klik untuk buka kamera / galeri HP</span></p>
                                                </div>
                                            </template>
                                            <template x-if="previewUrl">
                                                <div class="flex items-center gap-3 w-full text-left">
                                                    <img :src="previewUrl" class="w-12 h-12 object-cover rounded-lg border border-slate-300 shrink-0">
                                                    <div class="min-w-0 flex-1">
                                                        <span class="text-[11px] font-bold text-emerald-800 block truncate" x-text="fileName"></span>
                                                        <span class="text-[10px] text-slate-500">File siap diupload (Klik untuk ganti)</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 shrink-0">
                                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                        <button type="button" @click.stop="fileName = ''; previewUrl = ''; $refs.fileInputLantai.value = '';" class="px-2 py-1 text-[11px] font-bold text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">Hapus</button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                        @if($as?->foto_lantai)<p class="text-[10px] text-emerald-700 font-semibold mt-1 flex items-center gap-1"><svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>Foto lantai sudah ada di sistem.</span></p>@endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white border-2 border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-slate-50 px-5 py-3 border-b-2 border-slate-200 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                    <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">Spesifikasi Hunian Lainnya</h4>
                                </div>
                            </div>
                            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Status Kepemilikan Rumah</label>
                                    <select name="as_status_rumah" x-model="as_status_rumah" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 bg-white p-2.5 text-xs font-medium shadow-xs transition">
                                        <option value="">-- Pilih Status Rumah --</option>
                                        <option value="Hak Milik">Milik Sendiri</option>
                                        <option value="Menumpang">Menumpang Orang Tua/Kerabat</option>
                                        <option value="Sewa / Kontrak">Sewa / Kontrak</option>
                                        <option value="Bantaran / Liar">Tanah Tak Bertuan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Status Tanah</label>
                                    <select name="as_status_tanah" x-model="as_status_tanah" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 bg-white p-2.5 text-xs font-medium shadow-xs transition">
                                        <option value="">-- Pilih Status Tanah --</option>
                                        <option value="Hak Milik">Hak Milik (SHM/Girik)</option>
                                        <option value="Bukan Hak Milik">Bukan Hak Milik / Sewa</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Kondisi Bangunan</label>
                                    <select name="as_kondisi_tempat_tinggal" x-model="as_kondisi_tempat_tinggal" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 bg-white p-2.5 text-xs font-medium shadow-xs transition">
                                        <option value="">-- Pilih Kondisi Bangunan --</option>
                                        <option value="Permanen">Permanen (Tembok semen)</option>
                                        <option value="Semi Permanen">Semi Permanen</option>
                                        <option value="Non Permanen">Non Permanen / Bilik Kayu</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Kondisi Dinding</label>
                                    <select name="as_kondisi_dinding" x-model="as_kondisi_dinding" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 bg-white p-2.5 text-xs font-medium shadow-xs transition">
                                        <option value="">-- Pilih Kondisi Dinding --</option>
                                        <option value="Permanen">Tembok Plesteran</option>
                                        <option value="Bata Tanpa Plester">Bata Tanpa Plester</option>
                                        <option value="Kayu / Papan">Kayu / Papan</option>
                                        <option value="Anyaman Bambu / Bilik">Anyaman Bambu / Bilik</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Sumber Air Bersih</label>
                                    <select name="as_sumber_air_bersih" x-model="as_sumber_air_bersih" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 bg-white p-2.5 text-xs font-medium shadow-xs transition">
                                        <option value="">-- Pilih Sumber Air --</option>
                                        <option value="PDAM">PDAM / Ledeng</option>
                                        <option value="Sumur Pompa / Gali">Sumur Pompa / Gali</option>
                                        <option value="Mata Air / Sungai">Mata Air / Sungai</option>
                                        <option value="Beli Eceran">Beli Eceran</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Daya Listrik</label>
                                    <select name="as_daya_listrik" x-model="as_daya_listrik" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 bg-white p-2.5 text-xs font-medium shadow-xs transition">
                                        <option value="">-- Pilih Daya Listrik --</option>
                                        <option value="450 W">450 VA (Subsidi)</option>
                                        <option value="900 W">900 VA</option>
                                        <option value="1300 W">1300 VA</option>
                                        <option value="Menumpang">Menumpang Tetangga</option>
                                        <option value="Tidak Ada">Tidak Ada Listrik</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2 md:col-span-3 pt-2" x-data="{ isDragging: false, fileName: '', previewUrl: '' }">
                                    <label class="block font-bold text-slate-700 mb-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Foto Tempat Tinggal / Tampak Depan (Drag & Drop / Kamera)</span>
                                    </label>
                                    <div @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="isDragging = false; const dt = $event.dataTransfer; if (dt.files.length) { $refs.fileInputRumah.files = dt.files; fileName = dt.files[0].name; previewUrl = URL.createObjectURL(dt.files[0]); }" :class="isDragging ? 'border-[#118243] bg-emerald-50/80 ring-2 ring-emerald-300' : 'border-dashed border-slate-300 bg-white hover:border-emerald-500 hover:bg-emerald-50/20'" class="relative rounded-xl border-2 p-3.5 text-center transition flex flex-col items-center justify-center cursor-pointer min-h-[95px]" @click="$refs.fileInputRumah.click()">
                                        <input type="file" name="as_foto_tempat_tinggal" x-ref="fileInputRumah" accept="image/*" capture="environment" class="hidden" @change="if ($event.target.files.length) { fileName = $event.target.files[0].name; previewUrl = URL.createObjectURL($event.target.files[0]); }">
                                        <template x-if="!previewUrl">
                                            <div class="flex flex-col items-center space-y-1">
                                                <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                                <p class="text-[11px] font-bold text-slate-700">Tarik & Lepas foto rumah tampak depan ke sini</p>
                                                <p class="text-[10px] text-slate-500">atau <span class="text-[#118243] font-semibold underline">klik untuk ambil foto kamera / galeri</span></p>
                                            </div>
                                        </template>
                                        <template x-if="previewUrl">
                                            <div class="flex items-center gap-3 w-full text-left">
                                                <img :src="previewUrl" class="w-12 h-12 object-cover rounded-lg border border-slate-300 shrink-0">
                                                <div class="min-w-0 flex-1">
                                                    <span class="text-[11px] font-bold text-emerald-800 block truncate" x-text="fileName"></span>
                                                    <span class="text-[10px] text-slate-500">File siap diupload (Klik untuk ganti foto)</span>
                                                </div>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <button type="button" @click.stop="fileName = ''; previewUrl = ''; $refs.fileInputRumah.value = '';" class="px-2 py-1 text-[11px] font-bold text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">Hapus</button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    @if($as?->foto_tempat_tinggal)<p class="text-[10px] text-emerald-700 font-semibold mt-1 flex items-center gap-1"><svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>Foto tempat tinggal sudah ada di sistem.</span></p>@endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- TAHAP 4: USAHA & BPJS -->
                    <div x-show="as_step === 4" class="space-y-5">
                        <div class="p-3 bg-amber-50 border-2 border-amber-300 rounded-xl flex items-center gap-2.5 text-xs text-amber-900 font-semibold shadow-xs">
                            <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-xs shrink-0">4</span>
                            <span>Informasi kegiatan usaha mustahik, kepemilikan jaminan kesehatan BPJS, dan riwayat kesehatan. (Default kosong)</span>
                        </div>
                        <div class="bg-white border-2 border-emerald-200/90 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-3 border-b-2 border-emerald-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                    <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">Status Usaha Mustahik</h4>
                                </div>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Punya Usaha? <span class="text-red-500">*</span></label>
                                    <select name="as_punya_usaha" x-model="as_punya_usaha" required :class="!as_punya_usaha && asesmenErrors.some(e => e.field === 'Status Usaha') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'" class="w-full rounded-xl border-2 bg-white p-3 text-xs font-bold shadow-xs transition">
                                        <option value="">-- Pilih Status Usaha --</option>
                                        <option value="Ada">Ada Usaha</option>
                                        <option value="Tidak Ada">Tidak Ada Usaha</option>
                                    </select>
                                </div>
                                <div x-show="as_punya_usaha === 'Ada'" x-transition>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Lama Usaha Berjalan</label>
                                    <input type="text" name="as_lama_usaha" x-model="as_lama_usaha" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-medium shadow-xs transition" placeholder="Ketik durasi usaha (cth: 6 Bulan / 2 Tahun)...">
                                </div>
                                <div x-show="as_punya_usaha === 'Ada'" x-transition class="md:col-span-2" x-data="{ isDragging: false, fileName: '', previewUrl: '' }">
                                    <label class="block font-bold text-slate-700 mb-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Foto Tempat / Kegiatan Usaha (Drag & Drop / Kamera)</span>
                                    </label>
                                    <div @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="isDragging = false; const dt = $event.dataTransfer; if (dt.files.length) { $refs.fileInputUsaha.files = dt.files; fileName = dt.files[0].name; previewUrl = URL.createObjectURL(dt.files[0]); }" :class="isDragging ? 'border-[#118243] bg-emerald-50/80 ring-2 ring-emerald-300' : 'border-dashed border-slate-300 bg-white hover:border-emerald-500 hover:bg-emerald-50/20'" class="relative rounded-xl border-2 p-3.5 text-center transition flex flex-col items-center justify-center cursor-pointer min-h-[95px]" @click="$refs.fileInputUsaha.click()">
                                        <input type="file" name="as_foto_usaha" x-ref="fileInputUsaha" accept="image/*" capture="environment" class="hidden" @change="if ($event.target.files.length) { fileName = $event.target.files[0].name; previewUrl = URL.createObjectURL($event.target.files[0]); }">
                                        <template x-if="!previewUrl">
                                            <div class="flex flex-col items-center space-y-1">
                                                <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                <p class="text-[11px] font-bold text-slate-700">Tarik & Lepas foto usaha ke sini</p>
                                                <p class="text-[10px] text-slate-500">atau <span class="text-[#118243] font-semibold underline">klik untuk buka kamera / galeri</span></p>
                                            </div>
                                        </template>
                                        <template x-if="previewUrl">
                                            <div class="flex items-center gap-3 w-full text-left">
                                                <img :src="previewUrl" class="w-12 h-12 object-cover rounded-lg border border-slate-300 shrink-0">
                                                <div class="min-w-0 flex-1">
                                                    <span class="text-[11px] font-bold text-emerald-800 block truncate" x-text="fileName"></span>
                                                    <span class="text-[10px] text-slate-500">File siap diupload (Klik untuk ganti)</span>
                                                </div>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    <button type="button" @click.stop="fileName = ''; previewUrl = ''; $refs.fileInputUsaha.value = '';" class="px-2 py-1 text-[11px] font-bold text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">Hapus</button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    @if($as?->foto_usaha)<p class="text-[10px] text-emerald-700 font-semibold mt-1 flex items-center gap-1"><svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg><span>Foto usaha sudah tersimpan di sistem.</span></p>@endif
                                </div>
                            </div>
                        </div>
                        <div class="bg-white border-2 border-emerald-200/90 rounded-2xl shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-3 border-b-2 border-emerald-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                    <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">Kesehatan & BPJS</h4>
                                </div>
                            </div>
                            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">BPJS Kesehatan <span class="text-red-500">*</span></label>
                                    <select name="as_bpjs_kesehatan" x-model="as_bpjs_kesehatan" required :class="!as_bpjs_kesehatan && asesmenErrors.some(e => e.field === 'BPJS Kesehatan') ? 'border-red-500 ring-2 ring-red-200' : 'border-slate-300 hover:border-emerald-500 focus:border-[#118243]'" class="w-full rounded-xl border-2 bg-white p-3 text-xs font-bold shadow-xs transition">
                                        <option value="">-- Pilih Status BPJS --</option>
                                        <option value="Punya">Punya (KIS / BPJS Mandiri / PBI)</option>
                                        <option value="Tidak Punya">Tidak Punya</option>
                                    </select>
                                </div>
                                <div x-show="as_bpjs_kesehatan === 'Punya'" x-transition>
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Status Keaktifan BPJS</label>
                                    <select name="as_status_bpjs_kesehatan" x-model="as_status_bpjs_kesehatan" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 bg-white p-3 text-xs font-semibold shadow-xs transition">
                                        <option value="">-- Pilih Keaktifan --</option>
                                        <option value="Aktif">Aktif (Dapat digunakan)</option>
                                        <option value="Menunggak">Menunggak / Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block font-bold text-slate-700 uppercase mb-1.5">Riwayat Penyakit Menahun / Berat (Jika Ada)</label>
                                    <textarea name="as_riwayat_penyakit" x-model="as_riwayat_penyakit" rows="2" class="w-full rounded-xl border-2 border-slate-300 hover:border-emerald-500 focus:border-[#118243] bg-white p-3 text-xs font-medium shadow-xs transition" placeholder="Ketik jika ada anggota keluarga yang menderita penyakit menahun / disabilitas..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- TAHAP 5: PENILAIAN -->
                    <div x-show="as_step === 5" class="space-y-5">
                        <div class="p-3 bg-emerald-50 border-2 border-emerald-300 rounded-xl flex items-center gap-2.5 text-xs text-emerald-900 font-semibold shadow-xs">
                            <span class="w-5 h-5 rounded-full bg-[#118243] text-white flex items-center justify-center font-bold text-xs shrink-0">5</span>
                            <span>Penilaian akhir surveyor berdasarkan pengamatan faktual di lapangan. (Default skor belum dipilih)</span>
                        </div>
                        <div class="bg-white border-2 rounded-2xl p-5 shadow-sm space-y-4" :class="as_skor_penilaian < 1 && asesmenErrors.some(e => e.field.includes('Skor')) ? 'border-red-500 ring-2 ring-red-200' : 'border-emerald-200'">
                            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                                <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">Skor Penilaian Lapangan <span class="text-red-500">*</span></h4>
                            </div>
                            <p class="text-xs text-slate-600 font-medium">Silakan ketuk untuk memilih skor rating kelayakan (1 = Rendah, 5 = Sangat Prioritas/Mendesak):</p>
                            <div class="grid grid-cols-5 gap-2 sm:gap-3 py-2">
                                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                    <button type="button" @click="as_skor_penilaian = star" :class="as_skor_penilaian === star ? 'bg-amber-400 border-amber-500 text-slate-900 shadow-md ring-4 ring-amber-100 scale-105' : (as_skor_penilaian >= star ? 'bg-amber-100 border-amber-300 text-amber-800' : 'bg-slate-50 border-slate-200 text-slate-400 hover:bg-amber-50 hover:text-amber-500')" class="py-3 px-1 rounded-2xl border-2 flex flex-col items-center justify-center transition-all duration-200 cursor-pointer active:scale-95">
                                        <svg class="w-6 h-6 sm:w-7 sm:h-7 transition-colors" :class="as_skor_penilaian >= star ? 'text-amber-500 fill-amber-400' : 'text-slate-300 fill-none'" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        <span class="text-xs font-black mt-1" x-text="star"></span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="as_skor_penilaian" :value="as_skor_penilaian">
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                                <template x-if="as_skor_penilaian > 0">
                                    <div>
                                        <span class="text-xs font-bold text-slate-700">Skor Terpilih: </span>
                                        <span class="text-xs font-extrabold text-[#118243]" x-text="as_skor_penilaian + ' Bintang'"></span>
                                        <span class="text-[11px] text-slate-500 block mt-0.5" x-text="as_skor_penilaian === 5 ? 'Kondisi Sangat Mendesak / Prioritas Utama' : (as_skor_penilaian === 4 ? 'Kondisi Layak Mendapat Bantuan' : (as_skor_penilaian === 3 ? 'Cukup Memenuhi Kriteria' : 'Kondisi Kurang Memenuhi Kriteria'))"></span>
                                    </div>
                                </template>
                                <template x-if="!as_skor_penilaian || as_skor_penilaian < 1">
                                    <span class="text-xs font-bold text-slate-400 italic">Belum ada skor yang dipilih (Wajib dipilih oleh surveyor)</span>
                                </template>
                            </div>
                        </div>
                        <div class="bg-white border-2 rounded-2xl p-5 shadow-sm space-y-4" :class="!as_hasil_survei && asesmenErrors.some(e => e.field.includes('Rekomendasi')) ? 'border-red-500 ring-2 ring-red-200' : 'border-emerald-200'">
                            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                                <span class="w-3 h-3 rounded-full bg-[#118243]"></span>
                                <h4 class="font-bold text-[#0F172A] uppercase tracking-wider text-xs">Rekomendasi Hasil Asesmen <span class="text-red-500">*</span></h4>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="p-3.5 rounded-xl border-2 cursor-pointer flex items-center justify-center gap-2.5 transition active:scale-95 text-xs font-bold" :class="as_hasil_survei === 'LAYAK' ? 'bg-emerald-50 border-[#118243] text-emerald-900 ring-4 ring-emerald-100' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                                    <input type="radio" name="as_hasil_survei" value="LAYAK" x-model="as_hasil_survei" class="hidden">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>LAYAK DIBANTU</span>
                                </label>
                                <label class="p-3.5 rounded-xl border-2 cursor-pointer flex items-center justify-center gap-2.5 transition active:scale-95 text-xs font-bold" :class="as_hasil_survei === 'TIDAK LAYAK' ? 'bg-rose-50 border-rose-500 text-rose-900 ring-4 ring-rose-100' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                                    <input type="radio" name="as_hasil_survei" value="TIDAK LAYAK" x-model="as_hasil_survei" class="hidden">
                                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <span>TIDAK LAYAK</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER NAVIGASI -->
                <div class="bg-slate-50/95 border-t-2 border-slate-200 p-3 sm:p-4 shrink-0 shadow-lg space-y-2">
                    <template x-if="hasAttemptedAsesmenSubmit && asesmenErrors.length > 0">
                        <div class="p-2.5 sm:p-3 bg-rose-50 border-2 border-rose-300 rounded-xl flex items-center justify-between gap-3 text-xs animate-shake">
                            <div class="flex items-center gap-2 text-rose-800 font-semibold min-w-0">
                                <span class="w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0">!</span>
                                <span class="truncate">
                                    Ada <span class="font-black text-rose-900" x-text="asesmenErrors.length"></span> isian yang belum lengkap pada 
                                    <span class="font-bold underline" x-text="[...new Set(asesmenErrors.map(e => e.step))].map(s => 'Tahap ' + s).join(', ')"></span>
                                </span>
                            </div>
                            <button type="button" @click="showAsesmenValidationModal = true" 
                                    class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] rounded-lg shrink-0 transition shadow-xs">
                                Lihat Rincian
                            </button>
                        </div>
                    </template>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                        <div class="flex items-center gap-2 order-1 sm:order-1">
                            <button type="button" x-show="as_step > 1" @click="as_step--" class="w-auto px-4 py-3 sm:py-2.5 border-2 border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl shadow-xs transition text-center active:scale-95"><span>← Kembali</span></button>
                            <button type="button" x-show="as_step === 1" @click="activeModal = ''" class="w-auto px-4 py-3 sm:py-2.5 border-2 border-slate-300 bg-white hover:bg-slate-50 text-slate-600 font-bold text-xs rounded-xl transition text-center active:scale-95">Tutup</button>
                            <button type="button" x-show="as_step < 5" @click="as_step++" class="w-full sm:w-auto px-5 py-3 sm:py-2.5 bg-[#118243] hover:bg-[#0e6c37] active:scale-[0.98] text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2"><span>Lanjut Tahap Berikutnya →</span></button>
                        </div>
                        <div class="flex items-center gap-2 order-2 sm:order-2">
                            <button type="submit" class="w-full sm:w-auto px-6 py-3 sm:py-2.5 bg-gradient-to-r from-emerald-600 to-[#118243] hover:from-emerald-700 hover:to-[#0e6c37] active:scale-[0.98] text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Simpan Hasil Asesmen</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- MODAL ALERT VALIDASI KOMPREHENSIF ASESMEN -->
            <div x-show="showAsesmenValidationModal" x-cloak 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div x-show="showAsesmenValidationModal"
                     x-transition:enter="transition ease-out duration-250 transform"
                     x-transition:enter-start="opacity-0 scale-90"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150 transform"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-90"
                     class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border-2 border-rose-300 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-slate-800">Ada Form yang Belum Diisi!</h4>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Mohon lengkapi isian berikut sebelum menyimpan hasil asesmen:
                            </p>
                        </div>
                    </div>

                    <!-- LIST ERROR PER TAHAP -->
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-1 text-xs">
                        <template x-for="(err, idx) in asesmenErrors" :key="idx">
                            <div class="p-2.5 bg-rose-50/80 border border-rose-200 rounded-xl flex items-center justify-between gap-2">
                                <div>
                                    <span class="font-bold text-rose-900" x-text="err.tabName"></span>
                                    <p class="text-rose-700 text-[11px]" x-text="'Kolom: ' + err.field"></p>
                                </div>
                                <button type="button" @click="goToStep(err.step)" 
                                        class="px-2.5 py-1 bg-white hover:bg-rose-100 text-rose-700 border border-rose-300 rounded-lg text-[10px] font-bold shrink-0 transition">
                                    Buka Tab →
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="showAsesmenValidationModal = false" 
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Controller Form Modal Had Kifayah & Asesmen Lapangan
 * Menjaga template Blade tetap bersih dan mencegah DOM text node leakage.
 */
function hadKifayahModalForm(config = {}) {
    return {
        hk_step: 1,
        f_opz: config.f_opz || '',
        f_petugas: config.f_petugas || '',
        f_mustahik: config.f_mustahik || '',
        f_provinsi: config.f_provinsi || '',
        f_alamat: config.f_alamat || '',

        f_pendapatan_kk: config.f_pendapatan_kk ?? '',
        f_pendapatan_anggota: config.f_pendapatan_anggota ?? '',

        f_peng_kk: config.f_peng_kk ?? '',
        f_peng_ibu: config.f_peng_ibu ?? '',
        f_peng_anak_dewasa_kerja: config.f_peng_anak_dewasa_kerja ?? '',
        f_peng_anak_dewasa_tdk_kerja: config.f_peng_anak_dewasa_tdk_kerja ?? '',
        f_peng_sma: config.f_peng_sma ?? '',
        f_peng_smp: config.f_peng_smp ?? '',
        f_peng_sd: config.f_peng_sd ?? '',
        f_peng_bayi: config.f_peng_bayi ?? '',

        f_peng_disabilitas: config.f_peng_disabilitas ?? '',
        f_peng_ibu_menyusui: config.f_peng_ibu_menyusui ?? '',
        f_peng_ibu_hamil: config.f_peng_ibu_hamil ?? '',
        f_peng_janda: config.f_peng_janda ?? '',
        f_peng_pt: config.f_peng_pt ?? '',

        f_tempat_rumah: config.f_tempat_rumah || '',
        f_tempat_listrik: config.f_tempat_listrik || '',
        f_pemerintah_bansos: config.f_pemerintah_bansos || '',
        f_pemerintah_bpjs: config.f_pemerintah_bpjs || '',

        f_asset_motor: config.f_asset_motor || '',
        f_asset_tabungan: config.f_asset_tabungan || '',
        f_beban_hutang: config.f_beban_hutang || '',

        hasAttemptedSubmit: false,
        validationErrors: [],
        showValidationModal: false,

        tabHasError(stepNumber) {
            return this.hasAttemptedSubmit && this.validationErrors.some(e => e.step === stepNumber);
        },

        isStep1Valid() {
            return (this.f_opz || '').toString().trim() !== '' &&
                   (this.f_petugas || '').toString().trim() !== '' &&
                   (this.f_mustahik || '').toString().trim() !== '' &&
                   (this.f_provinsi || '').toString().trim() !== '' &&
                   (this.f_alamat || '').toString().trim() !== '';
        },
        isStep2Valid() {
            return (this.f_pendapatan_kk !== null && this.f_pendapatan_kk !== '') &&
                   (this.f_pendapatan_anggota !== null && this.f_pendapatan_anggota !== '') &&
                   (this.f_peng_kk !== null && this.f_peng_kk !== '') &&
                   (this.f_peng_ibu !== null && this.f_peng_ibu !== '') &&
                   (this.f_peng_anak_dewasa_kerja !== null && this.f_peng_anak_dewasa_kerja !== '') &&
                   (this.f_peng_anak_dewasa_tdk_kerja !== null && this.f_peng_anak_dewasa_tdk_kerja !== '') &&
                   (this.f_peng_sma !== null && this.f_peng_sma !== '') &&
                   (this.f_peng_smp !== null && this.f_peng_smp !== '') &&
                   (this.f_peng_sd !== null && this.f_peng_sd !== '') &&
                   (this.f_peng_bayi !== null && this.f_peng_bayi !== '');
        },
        isStep3Valid() {
            return (this.f_peng_disabilitas !== null && this.f_peng_disabilitas !== '') &&
                   (this.f_peng_ibu_menyusui !== null && this.f_peng_ibu_menyusui !== '') &&
                   (this.f_peng_ibu_hamil !== null && this.f_peng_ibu_hamil !== '') &&
                   (this.f_peng_janda !== null && this.f_peng_janda !== '') &&
                   (this.f_peng_pt !== null && this.f_peng_pt !== '');
        },
        isStep4Valid() {
            return (this.f_tempat_rumah || '').toString().trim() !== '' &&
                   (this.f_tempat_listrik || '').toString().trim() !== '' &&
                   (this.f_pemerintah_bansos || '').toString().trim() !== '' &&
                   (this.f_pemerintah_bpjs || '').toString().trim() !== '';
        },
        isStep5Valid() {
            return (this.f_asset_motor || '').toString().trim() !== '' &&
                   (this.f_asset_tabungan || '').toString().trim() !== '' &&
                   (this.f_beban_hutang || '').toString().trim() !== '';
        },

        validateAndSubmit(e) {
            this.hasAttemptedSubmit = true;
            this.validationErrors = [];

            if (!(this.f_opz || '').toString().trim()) {
                this.validationErrors.push({ step: 1, tabName: '1. Identitas & Mustahik', field: 'Nama OPZ' });
            }
            if (!(this.f_petugas || '').toString().trim()) {
                this.validationErrors.push({ step: 1, tabName: '1. Identitas & Mustahik', field: 'Nama Petugas' });
            }
            if (!(this.f_mustahik || '').toString().trim()) {
                this.validationErrors.push({ step: 1, tabName: '1. Identitas & Mustahik', field: 'Nama Mustahik' });
            }
            if (!(this.f_provinsi || '').toString().trim()) {
                this.validationErrors.push({ step: 1, tabName: '1. Identitas & Mustahik', field: 'Provinsi' });
            }
            if (!(this.f_alamat || '').toString().trim()) {
                this.validationErrors.push({ step: 1, tabName: '1. Identitas & Mustahik', field: 'Alamat Lengkap' });
            }

            if (this.f_pendapatan_kk === null || this.f_pendapatan_kk === '') {
                this.validationErrors.push({ step: 2, tabName: '2. Pendapatan & Keluarga', field: 'Pendapatan Kepala Keluarga' });
            }
            if (this.f_pendapatan_anggota === null || this.f_pendapatan_anggota === '') {
                this.validationErrors.push({ step: 2, tabName: '2. Pendapatan & Keluarga', field: 'Pendapatan Anggota Lain' });
            }
            if (this.f_peng_kk === null || this.f_peng_kk === '') {
                this.validationErrors.push({ step: 2, tabName: '2. Pendapatan & Keluarga', field: 'Kepala Rumah Tangga' });
            }
            if (this.f_peng_ibu === null || this.f_peng_ibu === '') {
                this.validationErrors.push({ step: 2, tabName: '2. Pendapatan & Keluarga', field: 'Ibu Rumah Tangga' });
            }
            if (this.f_peng_anak_dewasa_kerja === null || this.f_peng_anak_dewasa_kerja === '') {
                this.validationErrors.push({ step: 2, tabName: '2. Pendapatan & Keluarga', field: 'Anak Dewasa (Bekerja)' });
            }
            if (this.f_peng_anak_dewasa_tdk_kerja === null || this.f_peng_anak_dewasa_tdk_kerja === '') {
                this.validationErrors.push({ step: 2, tabName: '2. Pendapatan & Keluarga', field: 'Anak Dewasa (Tdk Bekerja)' });
            }
            if (this.f_peng_sma === null || this.f_peng_sma === '') {
                this.validationErrors.push({ step: 2, tabName: '2. Pendapatan & Keluarga', field: 'Anak SMA/Setingkat' });
            }
            if (this.f_peng_smp === null || this.f_peng_smp === '') {
                this.validationErrors.push({ step: 2, tabName: '2. Pendapatan & Keluarga', field: 'Anak SMP/Setingkat' });
            }
            if (this.f_peng_sd === null || this.f_peng_sd === '') {
                this.validationErrors.push({ step: 2, tabName: '2. Pendapatan & Keluarga', field: 'Anak SD/Setingkat' });
            }
            if (this.f_peng_bayi === null || this.f_peng_bayi === '') {
                this.validationErrors.push({ step: 2, tabName: '2. Pendapatan & Keluarga', field: 'Anak/Bayi Belum Sekolah' });
            }

            if (this.f_peng_disabilitas === null || this.f_peng_disabilitas === '') {
                this.validationErrors.push({ step: 3, tabName: '3. Tambahan & Pendidikan', field: 'Penyandang Disabilitas' });
            }
            if (this.f_peng_ibu_menyusui === null || this.f_peng_ibu_menyusui === '') {
                this.validationErrors.push({ step: 3, tabName: '3. Tambahan & Pendidikan', field: 'Ibu Menyusui' });
            }
            if (this.f_peng_ibu_hamil === null || this.f_peng_ibu_hamil === '') {
                this.validationErrors.push({ step: 3, tabName: '3. Tambahan & Pendidikan', field: 'Ibu Hamil' });
            }
            if (this.f_peng_janda === null || this.f_peng_janda === '') {
                this.validationErrors.push({ step: 3, tabName: '3. Tambahan & Pendidikan', field: 'Status Janda (Tdk Bekerja)' });
            }
            if (this.f_peng_pt === null || this.f_peng_pt === '') {
                this.validationErrors.push({ step: 3, tabName: '3. Tambahan & Pendidikan', field: 'Biaya Perguruan Tinggi' });
            }

            if (!(this.f_tempat_rumah || '').toString().trim()) {
                this.validationErrors.push({ step: 4, tabName: '4. Hunian & Bansos', field: 'Status Kepemilikan Rumah / Kontrak' });
            }
            if (!(this.f_tempat_listrik || '').toString().trim()) {
                this.validationErrors.push({ step: 4, tabName: '4. Hunian & Bansos', field: 'Penerangan Listrik' });
            }
            if (!(this.f_pemerintah_bansos || '').toString().trim()) {
                this.validationErrors.push({ step: 4, tabName: '4. Hunian & Bansos', field: 'Bantuan Sosial (Bansos)' });
            }
            if (!(this.f_pemerintah_bpjs || '').toString().trim()) {
                this.validationErrors.push({ step: 4, tabName: '4. Hunian & Bansos', field: 'Jaminan Kesehatan (BPJS/KIS/PBI)' });
            }

            if (!(this.f_asset_motor || '').toString().trim()) {
                this.validationErrors.push({ step: 5, tabName: '5. Asset & Hutang', field: 'Memiliki Kendaraan Bermotor' });
            }
            if (!(this.f_asset_tabungan || '').toString().trim()) {
                this.validationErrors.push({ step: 5, tabName: '5. Asset & Hutang', field: 'Memiliki Tabungan' });
            }
            if (!(this.f_beban_hutang || '').toString().trim()) {
                this.validationErrors.push({ step: 5, tabName: '5. Asset & Hutang', field: 'Memiliki Hutang' });
            }

            if (this.validationErrors.length > 0) {
                e.preventDefault();
                const firstErrorStep = this.validationErrors[0].step;
                this.hk_step = firstErrorStep;
                return false;
            }

            return true;
        },

        goToStep(stepNumber) {
            this.hk_step = stepNumber;
            this.showValidationModal = false;
            this.scrollToTab(stepNumber);
        },

        scrollToTab(stepNumber) {
            this.$nextTick(() => {
                const tabBtn = this.$refs['tab_' + stepNumber];
                if (tabBtn) {
                    tabBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
                if (this.$refs.formBody) {
                    this.$refs.formBody.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        },

        init() {
            this.$watch('hk_step', (value) => {
                this.scrollToTab(value);
            });
        }
    };
}

function asesmenModalForm(config = {}) {
    const selectorFn = window.wilayahSelector || (typeof wilayahSelector === 'function' ? wilayahSelector : null);
    return {
        as_step: 1,
        as_nama_surveyor: config.as_nama_surveyor || '',
        as_jenis_program: config.as_jenis_program || '',
        as_sub_program: config.as_sub_program || '',
        as_nama_mustahik: config.as_nama_mustahik || '',
        as_nik: config.as_nik || '',
        as_nkk: config.as_nkk || '',
        as_tempat_lahir: config.as_tempat_lahir || '',
        as_tanggal_lahir: config.as_tanggal_lahir || '',
        as_jenis_kelamin: config.as_jenis_kelamin || '',
        as_nohp: config.as_nohp || '',
        as_bersedia_disurvei: config.as_bersedia_disurvei || 'YA',
        as_alamat_lengkap: config.as_alamat_lengkap || '',
        as_desa: config.as_desa || '',
        as_kecamatan: config.as_kecamatan || '',
        wilayah: selectorFn ? selectorFn(config.wilayah || {}) : {},

        as_agama: config.as_agama || '',
        as_jumlah_tanggungan: config.as_jumlah_tanggungan ?? '',
        as_total_penghasilan_perbulan: config.as_total_penghasilan_perbulan ?? '',
        as_jumlah_sepeda: config.as_jumlah_sepeda ?? '',
        as_jumlah_sepeda_motor: config.as_jumlah_sepeda_motor ?? '',
        as_jumlah_mobil: config.as_jumlah_mobil ?? '',

        as_status_tanah: config.as_status_tanah || '',
        as_status_rumah: config.as_status_rumah || '',
        as_bukti_dokumen: config.as_bukti_dokumen || '',
        as_kondisi_tempat_tinggal: config.as_kondisi_tempat_tinggal || '',
        as_kondisi_atap: config.as_kondisi_atap || '',
        as_kondisi_lantai: config.as_kondisi_lantai || '',
        as_kondisi_dinding: config.as_kondisi_dinding || '',
        as_kondisi_dapur: config.as_kondisi_dapur || '',
        as_kondisi_kamar_mandi: config.as_kondisi_kamar_mandi || '',
        as_sumber_air_bersih: config.as_sumber_air_bersih || '',
        as_daya_listrik: config.as_daya_listrik || '',

        as_punya_usaha: config.as_punya_usaha || '',
        as_lama_usaha: config.as_lama_usaha || '',
        as_hasil_akhir_permohonan: config.as_hasil_akhir_permohonan || '',
        as_bpjs_kesehatan: config.as_bpjs_kesehatan || '',
        as_status_bpjs_kesehatan: config.as_status_bpjs_kesehatan || '',
        as_riwayat_penyakit: config.as_riwayat_penyakit || '',

        as_skor_penilaian: Number(config.as_skor_penilaian || 0),
        as_hasil_survei: config.as_hasil_survei || '',

        hasAttemptedAsesmenSubmit: false,
        asesmenErrors: [],
        showAsesmenValidationModal: false,

        tabHasError(stepNumber) {
            return this.hasAttemptedAsesmenSubmit && this.asesmenErrors.some(e => e.step === stepNumber);
        },

        isStep1Valid() {
            return (this.as_nama_mustahik || '').toString().trim() !== '' &&
                   (this.as_nik || '').toString().trim() !== '' &&
                   (this.as_jenis_kelamin || '').toString().trim() !== '' &&
                   (this.as_bersedia_disurvei || '').toString().trim() !== '' &&
                   (this.as_alamat_lengkap || '').toString().trim() !== '';
        },

        isStep2Valid() {
            return (this.as_jumlah_tanggungan !== null && this.as_jumlah_tanggungan !== '') &&
                   (this.as_total_penghasilan_perbulan !== null && this.as_total_penghasilan_perbulan !== '');
        },

        isStep3Valid() {
            return (this.as_kondisi_atap || '').toString().trim() !== '' &&
                   (this.as_kondisi_lantai || '').toString().trim() !== '';
        },

        isStep4Valid() {
            return (this.as_punya_usaha || '').toString().trim() !== '' &&
                   (this.as_bpjs_kesehatan || '').toString().trim() !== '';
        },

        isStep5Valid() {
            return this.as_skor_penilaian >= 1 && (this.as_hasil_survei || '').toString().trim() !== '';
        },

        validateAndSubmit(e) {
            this.hasAttemptedAsesmenSubmit = true;
            this.asesmenErrors = [];

            if (!(this.as_nama_mustahik || '').toString().trim()) {
                this.asesmenErrors.push({ step: 1, tabName: '1. Identitas', field: 'Nama Mustahik' });
            }
            if (!(this.as_nik || '').toString().trim()) {
                this.asesmenErrors.push({ step: 1, tabName: '1. Identitas', field: 'NIK (16 Digit)' });
            }
            if (!(this.as_jenis_kelamin || '').toString().trim()) {
                this.asesmenErrors.push({ step: 1, tabName: '1. Identitas', field: 'Jenis Kelamin' });
            }
            if (!(this.as_bersedia_disurvei || '').toString().trim()) {
                this.asesmenErrors.push({ step: 1, tabName: '1. Identitas', field: 'Kesediaan Disurvei' });
            }
            if (!(this.as_alamat_lengkap || '').toString().trim()) {
                this.asesmenErrors.push({ step: 1, tabName: '1. Identitas', field: 'Alamat Lengkap' });
            }

            if (this.as_jumlah_tanggungan === null || this.as_jumlah_tanggungan === '') {
                this.asesmenErrors.push({ step: 2, tabName: '2. Ekonomi', field: 'Jumlah Tanggungan' });
            }
            if (this.as_total_penghasilan_perbulan === null || this.as_total_penghasilan_perbulan === '') {
                this.asesmenErrors.push({ step: 2, tabName: '2. Ekonomi', field: 'Total Penghasilan Perbulan' });
            }

            if (!(this.as_kondisi_atap || '').toString().trim()) {
                this.asesmenErrors.push({ step: 3, tabName: '3. Aset & Rumah', field: 'Kondisi Atap' });
            }
            if (!(this.as_kondisi_lantai || '').toString().trim()) {
                this.asesmenErrors.push({ step: 3, tabName: '3. Aset & Rumah', field: 'Kondisi Lantai' });
            }

            if (!(this.as_punya_usaha || '').toString().trim()) {
                this.asesmenErrors.push({ step: 4, tabName: '4. Usaha & BPJS', field: 'Status Usaha' });
            }
            if (!(this.as_bpjs_kesehatan || '').toString().trim()) {
                this.asesmenErrors.push({ step: 4, tabName: '4. Usaha & BPJS', field: 'BPJS Kesehatan' });
            }

            if (!this.as_skor_penilaian || this.as_skor_penilaian < 1) {
                this.asesmenErrors.push({ step: 5, tabName: '5. Penilaian', field: 'Skor Penilaian Lapangan (Bintang 1-5)' });
            }
            if (!(this.as_hasil_survei || '').toString().trim()) {
                this.asesmenErrors.push({ step: 5, tabName: '5. Penilaian', field: 'Rekomendasi Hasil Survei' });
            }

            if (this.asesmenErrors.length > 0) {
                e.preventDefault();
                const firstErrorStep = this.asesmenErrors[0].step;
                this.as_step = firstErrorStep;
                this.scrollToTab(firstErrorStep);
                return false;
            }
            return true;
        },

        goToStep(stepNumber) {
            this.as_step = stepNumber;
            this.showAsesmenValidationModal = false;
            this.scrollToTab(stepNumber);
        },

        scrollToTab(stepNumber) {
            this.$nextTick(() => {
                const tabBtn = this.$refs['as_tab_' + stepNumber];
                if (tabBtn) tabBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                if (this.$refs.asesmenFormBody) this.$refs.asesmenFormBody.scrollTo({ top: 0, behavior: 'smooth' });
            });
        },

        async init() {
            if (this.wilayah && typeof this.wilayah.init === 'function') {
                await this.wilayah.init();
            }
            this.$watch('as_step', (value) => { this.scrollToTab(value); });
        }
    };
}
</script>
@endsection
