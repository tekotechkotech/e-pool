<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'E-POOL BAZNAS' }}</title>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- FilePond CSS -->
    <link href="{{ asset('vendor/filepond/filepond.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/filepond/filepond-plugin-image-preview.css') }}" rel="stylesheet" />
</head>
<body class="antialiased bg-[#f4f9f6] text-slate-800 font-sans" x-data="{ sidebarOpen: false }">

    @auth
    
    <div class="flex h-screen overflow-hidden relative">

        <!-- Mobile Sidebar Overlay -->
        <x-modal-backdrop show="sidebarOpen" :z-index="40" @click="sidebarOpen = false" class="lg:hidden" style="display: none;" />

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-100 shadow-[0_20px_40px_rgba(0,0,0,0.04)] transition-transform duration-300 lg:static lg:translate-x-0 lg:z-auto flex flex-col">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 relative overflow-hidden">
                <div class="absolute -left-10 top-0 w-24 h-24 bg-[#008000] rounded-full opacity-5 z-0"></div>
                <div class="flex items-center gap-3 relative z-10">
                    <div class="w-10 h-10 bg-white rounded-xl shadow-[0_4px_12px_rgba(0,0,0,0.06)] border border-slate-50 flex items-center justify-center shrink-0">
                        <img class="max-w-[28px] max-h-[28px] object-contain" src="{{ asset('images/logo.webp') }}" alt="Logo BAZNAS" />
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-[18px] font-bold text-[#118243] leading-tight tracking-tight">E-POOL</h2>
                        <span class="text-[11px] text-slate-500 font-medium">Pengumpulan & Penghimpunan ZIS</span>
                    </div>
                </div>
                <button @click="sidebarOpen = false" type="button" class="lg:hidden relative text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-50 transition-colors" aria-label="Tutup Menu">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Sidebar Nav -->
            <nav class="flex-1 px-4 py-5 space-y-1 overflow-y-auto">
                <!-- 1. UTAMA -->
                <div class="pt-2 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">UTAMA</p>
                </div>

                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-[14px]">Dashboard</span>
                </a>

                <!-- 2. PENGUMPULAN ZIS -->
                <div class="pt-5 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">PENGUMPULAN ZIS</p>
                </div>

                <a href="{{ route('pengumpulan.upz.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('pengumpulan.upz.*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('pengumpulan.upz.*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-[14px]">Setoran ZIS UPZ</span>
                </a>

                <a href="{{ route('pengumpulan.kas.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('pengumpulan.kas.*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('pengumpulan.kas.*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[14px]">Buku Kas Masuk ZIS</span>
                </a>

                <!-- 3. DATA MUZAKI & MUNFIQ -->
                <div class="pt-5 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">DATA MUZAKI & MUNFIQ</p>
                </div>

                <a href="{{ route('muzakki.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ (request()->routeIs('muzakki.*') && !request()->routeIs('muzakki.import*')) ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ (request()->routeIs('muzakki.*') && !request()->routeIs('muzakki.import*')) ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="text-[14px]">Data Muzakki</span>
                </a>

                <a href="{{ route('muzakki.import') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('muzakki.import*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('muzakki.import*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span class="text-[14px]">Import Data Muzakki</span>
                </a>

                <!-- 4. INTEGRASI SIMBA PUSAT -->
                <div class="pt-5 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">INTEGRASI SIMBA PUSAT</p>
                </div>

                <a href="{{ route('simba.muzakki') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('simba.muzakki*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('simba.muzakki*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="text-[14px]">Export Muzaki SIMBA</span>
                </a>

                <a href="{{ route('simba.kas-masuk') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('simba.kas-masuk*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('simba.kas-masuk*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-[14px]">Export Kas Masuk SIMBA</span>
                </a>

                <!-- 5. PELAPORAN -->
                <div class="pt-5 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">PELAPORAN</p>
                </div>

                <!-- <a href="{{ route('laporan.lampiran-setoran') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('laporan.lampiran-setoran*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('laporan.lampiran-setoran*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span class="text-[14px]">Lampiran Setoran ZIS</span>
                </a> -->

                <a href="{{ route('laporan.buku-kas') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('laporan.buku-kas*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('laporan.buku-kas*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="text-[14px]">Buku Kas & Mutasi</span>
                </a>

                <a href="{{ route('laporan.realisasi-dana') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('laporan.realisasi-dana*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('laporan.realisasi-dana*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="text-[14px]">Realisasi Jenis Dana</span>
                </a>

                <!-- 6. MASTER DATA -->
                <div class="pt-5 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">MASTER DATA</p>
                </div>

                <a href="{{ route('manajemen.upz') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('manajemen.upz*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('manajemen.upz*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="text-[14px]">Data UPZ</span>
                </a>

                <a href="{{ route('master.danas') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('master.danas*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('master.danas*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="text-[14px]">Master Jenis Dana</span>
                </a>

                <a href="{{ route('manajemen.pengguna') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('manajemen.pengguna*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <svg class="w-5 h-5 {{ request()->routeIs('manajemen.pengguna*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-[14px]">Manajemen Pengguna</span>
                </a>
            </nav>


        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 min-w-0 bg-[#f4f9f6] relative h-full overflow-hidden">

            <!-- Top Navbar -->
            <header class="bg-white/70 backdrop-blur-md sticky top-0 border-b border-slate-200/60 shadow-sm relative">
                <div class="flex items-center justify-between px-6 lg:px-8 py-4">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = true" type="button" class="lg:hidden text-slate-500 hover:text-[#118243] p-1.5 -ml-1.5 rounded-lg hover:bg-slate-50 transition-colors focus:outline-none" aria-label="Buka Menu">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                        </button>
                        <h1 class="text-xl font-bold text-slate-800 hidden sm:block">
                            {{ $title ?? 'Dashboard E-POOL' }}
                        </h1>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="hidden md:flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-100">
                            <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-sm font-medium text-slate-600">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
                        </div>

                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ userMenuOpen: false }">
                            <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false" class="flex items-center gap-3 px-3 py-2 bg-white rounded-xl border border-slate-100 shadow-sm hover:bg-slate-50 transition-colors">
                                <div class="hidden sm:flex flex-col items-end text-right">
                                    <span class="text-[14px] font-bold text-slate-800 leading-tight">{{ auth()->user()->person->nama ?? explode('@', auth()->user()->email)[0] }}</span>
                                    <span class="text-[12px] text-[#118243] font-medium leading-tight">{{ session('active_jabatan') }}</span>
                                </div>
                                <div class="w-9 h-9 rounded-lg bg-[#118243] text-white flex items-center justify-center font-bold shadow-sm shrink-0 text-sm">
                                    {{ strtoupper(substr(auth()->user()->person->nama ?? auth()->user()->email, 0, 1)) }}
                                </div>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="userMenuOpen" x-transition.opacity.duration.200ms style="display: none;" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2 z-50">
                                <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-[#118243] transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Edit Profil
                                </a>
                                @if(auth()->user()->person?->penguruses->count() > 1)
                                    <a href="{{ route('select-jabatan') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                        Ganti Posisi
                                    </a>
                                @endif
                                <div class="border-t border-slate-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6 lg:p-8 overflow-y-auto z-10 relative">
                @if (session('success'))
                    <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-200 flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="text-sm font-semibold text-emerald-900">{{ session('success') }}</div>
                        </div>
                        <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-800 p-1 rounded-lg">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-200 flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-red-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <div class="text-sm font-semibold text-red-900">{{ session('error') }}</div>
                        </div>
                        <button @click="show = false" type="button" class="text-red-500 hover:text-red-800 p-1 rounded-lg">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 rounded-xl bg-amber-50 p-4 border border-amber-200 flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div class="text-sm font-semibold text-amber-900">{{ session('warning') }}</div>
                        </div>
                        <button @click="show = false" type="button" class="text-amber-500 hover:text-amber-800 p-1 rounded-lg">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-6 rounded-xl bg-blue-50 p-4 border border-blue-200 flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="text-sm font-semibold text-blue-900">{{ session('status') }}</div>
                        </div>
                        <button @click="show = false" type="button" class="text-blue-500 hover:text-blue-800 p-1 rounded-lg">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>
    @else
    <!-- Guest Layout Fallback -->
    <main class="min-h-screen flex flex-col justify-center items-center">
        @yield('content')
        {{ $slot ?? '' }}
    </main>
    @endauth
    
    <!-- Global Confirm / Alert Dialog Modal -->
    <x-confirm-dialog />

    <!-- FilePond JS -->
    <script src="{{ asset('vendor/filepond/filepond-plugin-file-validate-type.js') }}" defer></script>
    <script src="{{ asset('vendor/filepond/filepond-plugin-file-validate-size.js') }}" defer></script>
    <script src="{{ asset('vendor/filepond/filepond-plugin-image-preview.js') }}" defer></script>
    <script src="{{ asset('vendor/filepond/filepond.js') }}" defer></script>
</body>
</html>
