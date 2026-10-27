@extends('layouts.app')

@section('content')
<div>
    <!-- Welcome Banner -->
    <div class="bg-white rounded-[24px] p-8 mb-8 shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-slate-100 relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-64 bg-[#eaf5ef] rounded-full opacity-50 blur-3xl translate-x-1/3 -translate-y-1/3 pointer-events-none"></div>
        
        <div class="relative z-10">
            <h1 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-2 inline-flex items-center gap-2 flex-wrap">
                <span>Selamat datang kembali, <span class="text-[#118243]">{{ $user->person->nama ?? explode('@', $user->email)[0] }}</span>!</span>
                <svg class="w-7 h-7 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
            </h1>
            <p class="text-slate-500 text-[15px] max-w-2xl">Pantau dan kelola pendistribusian & pendayagunaan dana ZIS dengan mudah, transparan, dan akuntabel.</p>
            
            @if(count($roles) > 0)
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach($roles as $role)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-[#eaf5ef] text-[#118243] border border-[#118243]/20 shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        {{ $role }}
                    </span>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5 mb-8">
        <!-- Total Muzakki -->
        <div class="bg-white rounded-[20px] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-transform duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-[#eaf5ef] rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-[#eaf5ef] text-[#118243] rounded-2xl flex items-center justify-center shrink-0 border border-[#118243]/10">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M7 10H3m4 4H3m4 4h2m4-8h2M6 8a2 2 0 11-4 0v4a2 2 0 11-4 0v4a2 2 0 102 0v-4a2 2 0 102 0M12 14v6m0 0l4-4m-4 4l-4-4" />
                    </svg>
                </div>
                <div>
                    <p class="text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Muzakki</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ number_format($stats['totalMuzakki'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        
        <!-- Total UPZ -->
        <div class="bg-white rounded-[20px] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-transform duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center shrink-0 border border-blue-100/50">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Total UPZ</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ number_format($stats['totalUpz'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        
        <!-- ZIS Terkumpul -->
        <div class="bg-white rounded-[20px] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-transform duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center shrink-0 border border-green-100/50">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1">ZIS Terkumpul</p>
                    <h3 class="text-2xl font-bold text-slate-800">Rp {{ number_format($stats['totalZisTerkumpul'] ?? 0, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <!-- Setoran UPZ -->
        <div class="bg-white rounded-[20px] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-transform duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center shrink-0 border border-orange-100/50">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M6 14l2-2 4 4 4-4 2 2M6 10l2-2 4 4 4-4 2 2" />
                    </svg>
                </div>
                <div>
                    <p class="text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Setoran UPZ</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ number_format($stats['totalSetoranUpz'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        
        <!-- Nominal Setoran UPZ -->
        <div class="bg-white rounded-[20px] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-transform duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center shrink-0 border border-purple-100/50">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Nominal Setoran UPZ</p>
                    <h3 class="text-2xl font-bold text-slate-800">Rp {{ number_format($stats['nominalSetoranUpz'] ?? 0, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Role-specific content -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <div class="bg-white rounded-[24px] shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">Menu Cepat E-POOL</h3>
                <div class="w-8 h-8 rounded-full bg-[#118243]/10 text-[#118243] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <div class="p-8">
                <div class="space-y-4">
                    <a href="{{ route('pengumpulan.upz.create') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-[#118243] hover:bg-[#eaf5ef] transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-[#eaf5ef] text-[#118243] flex items-center justify-center transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-[#118243] transition-colors">Input Setoran UPZ</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Input data transaksi setor UPZ ke dalam sistem</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-[#118243] group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>

                    <a href="{{ route('muzakki.create') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-blue-600 hover:bg-blue-50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-blue-600 transition-colors">Tambah Muzakki</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Buat data muzakki baru dalam sistem E-POOL</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>

                    <a href="{{ route('simba.kas-masuk') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-green-600 hover:bg-green-50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-green-600 transition-colors">Export SIMBA</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Download laporan transaksi kas masuk format SIMBA</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-green-600 group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[24px] shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-[#eaf5ef]/50">
                <h3 class="text-lg font-bold text-slate-800">Laporan & Evaluasi</h3>
                <div class="w-8 h-8 rounded-full bg-[#118243]/10 text-[#118243] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <div class="p-8">
                <div class="space-y-4">
                    <a href="{{ route('laporan.realisasi-dana') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-[#118243] hover:bg-[#eaf5ef] transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-[#118243] transition-colors">Realisasi Jenis Dana</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Laporan 18 bagan akun standar BAZNAS</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-[#118243] group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>

                    <a href="{{ route('laporan.program-asnaf') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-[#118243] hover:bg-[#eaf5ef] transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-[#118243] transition-colors">Matriks Program & Asnaf</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Penyaluran silang menurut program & 8 asnaf</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-[#118243] group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>

                    <a href="{{ route('laporan.buku-kas') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-[#118243] hover:bg-[#eaf5ef] transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-[#118243] transition-colors">Buku Kas & Mutasi</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Rekening koran dan saldo kas berjalan</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-[#118243] group-hover:translate-x-1 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
