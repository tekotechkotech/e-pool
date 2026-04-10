@extends('layouts.app')

@section('content')
<div>
    <!-- Welcome Banner -->
    <div class="bg-white rounded-[24px] p-8 mb-8 shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-slate-100 relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-64 bg-[#eaf5ef] rounded-full opacity-50 blur-3xl translate-x-1/3 -translate-y-1/3 pointer-events-none"></div>
        
        <div class="relative z-10">
            <h1 class="text-2xl lg:text-3xl font-bold text-slate-800 mb-2 inline-flex items-center gap-2 flex-wrap">
                <span>Selamat datang kembali, <span class="text-[#118243]">{{ $user->person->nama ?? explode('@', $user->email)[0] }}</span>!</span>
                <x-heroicon-o-sparkles class="w-7 h-7 text-[#118243]" />
            </h1>
            <p class="text-slate-500 text-[15px] max-w-2xl">Pantau dan kelola pendistribusian & pendayagunaan dana ZIS dengan mudah, transparan, dan akuntabel.</p>
            
            @if(count($roles) > 0)
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach($roles as $role)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-[#eaf5ef] text-[#118243] border border-[#118243]/20 shadow-sm">
                        <x-heroicon-o-shield-check class="w-4 h-4 mr-1.5" />
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
                    <x-heroicon-o-users class="w-7 h-7" />
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
                    <x-heroicon-o-building-office-2 class="w-7 h-7" />
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
                    <x-heroicon-o-banknotes class="w-7 h-7" />
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
                    <x-heroicon-o-document-text class="w-7 h-7" />
                </div>
                <div>
                    <p class="text-[13px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Setoran UPZ</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ number_format($stats['totalSetoranUpz'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
        
        <!-- Nominal Setoran UPZ -->
        <div class="bg-white rounded-[20px] p-6 shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 hover:-translate-y-1 transition-transform duration-300 relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-14 h-14 bg-emerald-50 text-[#118243] rounded-2xl flex items-center justify-center shrink-0 border border-emerald-100/50">
                    <x-heroicon-o-currency-dollar class="w-7 h-7" />
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
                    <x-heroicon-o-bolt class="w-4 h-4" />
                </div>
            </div>
            <div class="p-8">
                <div class="space-y-4">
                    <a href="{{ route('pengumpulan.upz.create') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-[#118243] hover:bg-[#eaf5ef] transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-[#eaf5ef] text-[#118243] flex items-center justify-center transition-colors shadow-sm">
                            <x-heroicon-o-plus class="w-5 h-5" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-[#118243] transition-colors">Input Setoran UPZ</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Input data transaksi setor UPZ ke dalam sistem</p>
                        </div>
                        <x-heroicon-o-chevron-right class="w-5 h-5 text-slate-400 group-hover:text-[#118243] group-hover:translate-x-1 transition-all" />
                    </a>

                    <a href="{{ route('muzakki.create') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-blue-600 hover:bg-blue-50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center transition-colors shadow-sm">
                            <x-heroicon-o-user-plus class="w-5 h-5" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-blue-600 transition-colors">Tambah Muzakki</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Buat data muzakki baru dalam sistem E-POOL</p>
                        </div>
                        <x-heroicon-o-chevron-right class="w-5 h-5 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all" />
                    </a>

                    <a href="{{ route('simba.kas-masuk') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-green-600 hover:bg-green-50 transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center transition-colors shadow-sm">
                            <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-green-600 transition-colors">Export SIMBA</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Download laporan transaksi kas masuk format SIMBA</p>
                        </div>
                        <x-heroicon-o-chevron-right class="w-5 h-5 text-slate-400 group-hover:text-green-600 group-hover:translate-x-1 transition-all" />
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[24px] shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 flex justify-between items-center bg-[#eaf5ef]/50">
                <h3 class="text-lg font-bold text-slate-800">Laporan & Evaluasi</h3>
                <div class="w-8 h-8 rounded-full bg-[#118243]/10 text-[#118243] flex items-center justify-center">
                    <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                </div>
            </div>
            <div class="p-8">
                <div class="space-y-4">
                    <a href="{{ route('laporan.realisasi-dana') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-[#118243] hover:bg-[#eaf5ef] transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center transition-colors shadow-sm">
                            <x-heroicon-o-document-chart-bar class="w-5 h-5" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-[#118243] transition-colors">Realisasi Jenis Dana</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Laporan 18 bagan akun standar BAZNAS</p>
                        </div>
                        <x-heroicon-o-chevron-right class="w-5 h-5 text-slate-400 group-hover:text-[#118243] group-hover:translate-x-1 transition-all" />
                    </a>

                    <a href="{{ route('laporan.program-asnaf') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-[#118243] hover:bg-[#eaf5ef] transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center transition-colors shadow-sm">
                            <x-heroicon-o-chart-bar class="w-5 h-5" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-[#118243] transition-colors">Matriks Program & Asnaf</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Penyaluran silang menurut program & 8 asnaf</p>
                        </div>
                        <x-heroicon-o-chevron-right class="w-5 h-5 text-slate-400 group-hover:text-[#118243] group-hover:translate-x-1 transition-all" />
                    </a>

                    <a href="{{ route('laporan.buku-kas') }}" class="flex items-center gap-4 p-4 rounded-xl border border-slate-200 hover:border-[#118243] hover:bg-[#eaf5ef] transition-all group">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center transition-colors shadow-sm">
                            <x-heroicon-o-book-open class="w-5 h-5" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-slate-800 group-hover:text-[#118243] transition-colors">Buku Kas & Mutasi</h4>
                            <p class="text-sm text-slate-500 mt-0.5">Rekening koran dan saldo kas berjalan</p>
                        </div>
                        <x-heroicon-o-chevron-right class="w-5 h-5 text-slate-400 group-hover:text-[#118243] group-hover:translate-x-1 transition-all" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
