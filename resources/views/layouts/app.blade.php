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
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>

            <!-- Sidebar Nav -->
            <nav class="flex-1 px-4 py-5 space-y-1 overflow-y-auto">
                <!-- 1. UTAMA -->
                <div class="pt-2 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">UTAMA</p>
                </div>

                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-home class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Dashboard</span>
                </a>

                <!-- 2. PENGUMPULAN ZIS -->
                <div class="pt-5 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">PENGUMPULAN ZIS</p>
                </div>

                <a href="{{ route('pengumpulan.upz.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('pengumpulan.upz.*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-document-text class="w-5 h-5 {{ request()->routeIs('pengumpulan.upz.*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Setoran ZIS UPZ</span>
                </a>

                <a href="{{ route('pengumpulan.kas.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('pengumpulan.kas.*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-banknotes class="w-5 h-5 {{ request()->routeIs('pengumpulan.kas.*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Buku Kas Masuk ZIS</span>
                </a>

                <!-- 3. DATA MUZAKI & MUNFIQ -->
                <div class="pt-5 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">DATA MUZAKI & MUNFIQ</p>
                </div>

                <a href="{{ route('muzakki.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ (request()->routeIs('muzakki.*') && !request()->routeIs('muzakki.import*')) ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-users class="w-5 h-5 {{ (request()->routeIs('muzakki.*') && !request()->routeIs('muzakki.import*')) ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Data Muzakki</span>
                </a>

                <a href="{{ route('muzakki.import') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('muzakki.import*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-arrow-up-tray class="w-5 h-5 {{ request()->routeIs('muzakki.import*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Import Data Muzakki</span>
                </a>

                <!-- 4. INTEGRASI SIMBA PUSAT -->
                <!-- <div class="pt-5 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">INTEGRASI SIMBA PUSAT</p>
                </div>

                <a href="{{ route('simba.muzakki') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('simba.muzakki*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-arrow-down-tray class="w-5 h-5 {{ request()->routeIs('simba.muzakki*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Export Muzaki SIMBA</span>
                </a>

                <a href="{{ route('simba.kas-masuk') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('simba.kas-masuk*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-document-arrow-down class="w-5 h-5 {{ request()->routeIs('simba.kas-masuk*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Export Kas Masuk SIMBA</span>
                </a> -->

                <!-- 5. PELAPORAN -->
                <div class="pt-5 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">PELAPORAN</p>
                </div>

                <!-- <a href="{{ route('laporan.lampiran-setoran') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('laporan.lampiran-setoran*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 {{ request()->routeIs('laporan.lampiran-setoran*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Lampiran Setoran ZIS</span>
                </a> -->

                <a href="{{ route('laporan.buku-kas') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('laporan.buku-kas*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-book-open class="w-5 h-5 {{ request()->routeIs('laporan.buku-kas*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Buku Kas & Mutasi</span>
                </a>

                <a href="{{ route('laporan.realisasi-dana') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('laporan.realisasi-dana*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-chart-bar class="w-5 h-5 {{ request()->routeIs('laporan.realisasi-dana*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Realisasi Jenis Dana</span>
                </a>

                <!-- 6. MASTER DATA -->
                <div class="pt-5 pb-1.5 px-3">
                    <p class="text-[11px] font-bold tracking-wider text-slate-400 uppercase">MASTER DATA</p>
                </div>

                <a href="{{ route('manajemen.upz') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('manajemen.upz*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-building-office-2 class="w-5 h-5 {{ request()->routeIs('manajemen.upz*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Data UPZ</span>
                </a>

                <a href="{{ route('master.danas') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('master.danas*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-banknotes class="w-5 h-5 {{ request()->routeIs('master.danas*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
                    <span class="text-[14px]">Master Jenis Dana</span>
                </a>

                <a href="{{ route('manajemen.pengguna') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('manajemen.pengguna*') ? 'bg-[#118243] text-white font-semibold shadow-[0_4px_12px_rgba(17,130,67,0.25)]' : 'text-slate-600 hover:bg-[#eaf5ef] hover:text-[#118243] font-medium' }} group">
                    <x-heroicon-o-user-group class="w-5 h-5 {{ request()->routeIs('manajemen.pengguna*') ? 'text-white' : 'text-slate-400 group-hover:text-[#118243]' }} transition-colors shrink-0" />
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
                            <x-heroicon-o-bars-3 class="w-6 h-6" />
                        </button>
                        <h1 class="text-xl font-bold text-slate-800 hidden sm:block">
                            {{ $title ?? 'Dashboard E-POOL' }}
                        </h1>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="hidden md:flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-100">
                            <x-heroicon-o-calendar-days class="w-4 h-4 text-[#118243]" />
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
                                <x-heroicon-o-chevron-down class="w-4 h-4 text-slate-400 transition-transform duration-200" x-bind:class="userMenuOpen ? 'rotate-180' : ''" />
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="userMenuOpen" x-transition.opacity.duration.200ms style="display: none;" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2 z-50">
                                <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-[#118243] transition-colors">
                                    <x-heroicon-o-user class="w-4 h-4" />
                                    Edit Profil
                                </a>
                                @if(auth()->user()->person?->penguruses->count() > 1)
                                    <a href="{{ route('select-jabatan') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                        <x-heroicon-o-arrows-right-left class="w-4 h-4" />
                                        Ganti Posisi
                                    </a>
                                @endif
                                <div class="border-t border-slate-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
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
                                <x-heroicon-o-check class="w-5 h-5" />
                            </div>
                            <div class="text-sm font-semibold text-emerald-900">{{ session('success') }}</div>
                        </div>
                        <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-800 p-1 rounded-lg">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-200 flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-red-500 text-white flex items-center justify-center shrink-0">
                                <x-heroicon-o-x-circle class="w-5 h-5" />
                            </div>
                            <div class="text-sm font-semibold text-red-900">{{ session('error') }}</div>
                        </div>
                        <button @click="show = false" type="button" class="text-red-500 hover:text-red-800 p-1 rounded-lg">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 rounded-xl bg-amber-50 p-4 border border-amber-200 flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0">
                                <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                            </div>
                            <div class="text-sm font-semibold text-amber-900">{{ session('warning') }}</div>
                        </div>
                        <button @click="show = false" type="button" class="text-amber-500 hover:text-amber-800 p-1 rounded-lg">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-6 rounded-xl bg-blue-50 p-4 border border-blue-200 flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500 text-white flex items-center justify-center shrink-0">
                                <x-heroicon-o-information-circle class="w-5 h-5" />
                            </div>
                            <div class="text-sm font-semibold text-blue-900">{{ session('status') }}</div>
                        </div>
                        <button @click="show = false" type="button" class="text-blue-500 hover:text-blue-800 p-1 rounded-lg">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
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
