@extends('layouts.guest')

@section('content')
<div class="md:w-[calc(100%-8rem)] max-w-md lg:max-w-[1400px] lg:max-h-[900px] mx-auto bg-transparent lg:bg-white lg:rounded-[40px] lg:shadow-[0_20px_40px_rgba(0,0,0,0.1)] flex flex-col lg:flex-row lg:overflow-hidden">
    <!-- Left Panel: Branding & Slogan -->
    <div class="hidden lg:flex w-1/2 bg-white border-r border-slate-100 p-16 relative overflow-hidden flex-col justify-center">
        <div class="absolute -left-20 top-1/5 w-40 h-40 bg-[#eaf5ef] rounded-full opacity-80 z-10"></div>
        <div class="absolute -right-24 -bottom-24 w-[350px] h-[350px] bg-[#eaf5ef] rounded-full opacity-80 z-10"></div>

        <!-- Top Right Dot Grid -->
        <svg class="absolute w-[100px] h-[100px] z-10 opacity-50 top-12 right-12" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="10" cy="10" r="2.5" fill="#cbd5e1" opacity="0.9" />
            <circle cx="28" cy="10" r="2.5" fill="#cbd5e1" opacity="0.8" />
            <circle cx="46" cy="10" r="2.5" fill="#cbd5e1" opacity="0.7" />
            <circle cx="64" cy="10" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="82" cy="10" r="2.5" fill="#cbd5e1" opacity="0.5" />
            <circle cx="100" cy="10" r="2.5" fill="#cbd5e1" opacity="0.4" />
            <circle cx="10" cy="28" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="28" cy="28" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="46" cy="28" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="64" cy="28" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="82" cy="28" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="100" cy="28" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="10" cy="46" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="28" cy="46" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="46" cy="46" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="64" cy="46" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="82" cy="46" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="100" cy="46" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="10" cy="64" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="28" cy="64" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="46" cy="64" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="64" cy="64" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="82" cy="64" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="100" cy="64" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="10" cy="82" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="28" cy="82" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="46" cy="82" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="64" cy="82" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="82" cy="82" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="100" cy="82" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="10" cy="100" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="28" cy="100" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="46" cy="100" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="64" cy="100" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="82" cy="100" r="2.5" fill="#cbd5e1" opacity="0.6" />
            <circle cx="100" cy="100" r="2.5" fill="#cbd5e1" opacity="0.6" />
        </svg>

        <div class="relative z-20 pl-4">
            <div class="flex items-center gap-5 mb-8">
                <div class="w-20 h-20 bg-white rounded-2xl shadow-[0_8px_24px_rgba(0,0,0,0.06)] border border-slate-50 flex items-center justify-center shrink-0">
                    <img class="max-w-[50px] max-h-[50px] object-contain" src="{{ asset('images/logo.webp') }}" alt="Logo BAZNAS" />
                </div>
                <div class="flex flex-col">
                    <h2 class="text-[26px] font-bold text-[#118243] leading-tight tracking-tight">E-POOL</h2>
                    <span class="text-[15px] text-slate-500 font-medium mt-1">Pengumpulan & Penghimpunan ZIS</span>
                </div>
            </div>

            <div class="w-10 h-1 bg-[#118243] rounded-full mb-8"></div>

            <div class="text-slate-600 text-base leading-relaxed mb-10">
                <p class="font-semibold text-slate-900 mb-1.5">Efektif, Akuntabel, dan Transparan</p>
                <p>E-POOL Sistem Pengumpulan ZIS BAZNAS</p>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-[#118243] tracking-wide uppercase">BAZNAS CILACAP</span>
               </div>
            </div>

    </div>

    <!-- Right Panel: Login Form -->
    <div class="w-full lg:w-1/2 bg-transparent lg:bg-[#fafdfb] p-0 lg:p-10 flex items-center justify-center relative">
        <div class="bg-white rounded-[24px] shadow-[0_10px_30px_rgba(0,0,0,0.04)] border border-slate-200 lg:border-slate-100 lg:shadow-[0_10px_40px_rgba(0,0,0,0.06)] w-full max-w-[480px] lg:max-w-[440px] p-8 sm:p-12 text-center relative z-20" x-data="{ email: '{{ old('email') }}', password: '' }">
            <img class="h-[70px] mx-auto mb-6 object-contain" src="{{ asset('images/logo.webp') }}" alt="BAZNAS Kabupaten Cilacap" />

            <h3 class="text-[22px] font-bold text-slate-800 mb-2">Selamat datang di E-POOL</h3>
            <p class="text-[14px] font-medium text-slate-500 leading-relaxed">Pengumpulan & Penghimpunan ZIS</p>

            <div class="w-8 h-1 bg-[#118243] rounded-full mx-auto mt-5 mb-8"></div>

            <p class="text-[14px] text-slate-500 mb-5 text-left">Masuk ke akun anda</p>

            @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm text-left mb-6" role="alert">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm text-left mb-6">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="text-left">
                @csrf
                <!-- Email Field -->
                <div class="mb-6">
                    <label for="email" class="block text-[14px] font-semibold text-slate-700 mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="email" name="email" id="email" x-model="email" value="{{ old('email') }}" required autofocus placeholder="Masukkan Email" class="w-full pl-11 pr-4 py-3.5 text-[15px] text-slate-800 bg-white border border-slate-300 shadow-xs rounded-xl focus:outline-none focus:border-[#118243] focus:ring-4 focus:ring-[#118243]/10 transition-all duration-200 @error('email') border-red-500 focus:border-red-500 focus:ring-red-500/10 @enderror" />
                    </div>
                </div>

                <!-- Password Field -->
                <div class="mb-8" x-data="{ show: false }">
                    <label for="password" class="block text-[14px] font-semibold text-slate-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input :type="show ? 'text' : 'password'" name="password" id="password" x-model="password" required placeholder="Masukkan Password" class="w-full pl-11 pr-12 py-3.5 text-[15px] text-slate-800 bg-white border border-slate-300 shadow-xs rounded-xl focus:outline-none focus:border-[#118243] focus:ring-4 focus:ring-[#118243]/10 transition-all duration-200" />

                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none transition-colors">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display: none">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#118243] hover:bg-[#0d6835] active:translate-y-0 text-white font-semibold py-3.5 px-4 rounded-xl shadow-[0_4px_12px_rgba(17,130,67,0.25)] hover:-translate-y-px transition-all duration-200 flex justify-center items-center h-[50px] mb-6">
                    Masuk
                </button>
            </form>

            @if(app()->environment('local') && config('app.debug'))
           <div class="mb-6 pt-4 border-t border-dashed border-slate-200 text-left">
                <div class="flex items-center justify-between mb-2.5">
                    <span class="text-[11px] font-bold tracking-wider text-amber-600 uppercase flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Auto-Fill Dev (Seeder)
                    </span>
                    <span class="text-[11px] text-slate-400 font-mono">pwd: password</span>
                </div>
                <div class="grid grid-cols-3 gap-1.5">
                    <button type="button" @click="email = 'admin@baznas.id'; password = 'password'" onclick="window.fillLogin && window.fillLogin('admin@baznas.id', 'password')" class="px-2 py-2 text-xs font-medium text-slate-700 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 border border-slate-200 rounded-lg transition-colors flex items-center justify-center gap-1.5 truncate cursor-pointer" title="Admin BAZNAS (admin@baznas.id)">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span>Admin</span>
                    </button>
                    <button type="button" @click="email = 'staff@baznas.id'; password = 'password'" onclick="window.fillLogin && window.fillLogin('staff@baznas.id', 'password')" class="px-2 py-2 text-xs font-medium text-slate-700 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 border border-slate-200 rounded-lg transition-colors flex items-center justify-center gap-1.5 truncate cursor-pointer" title="Staff Penyaluran (staff@baznas.id)">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Staff</span>
                    </button>
                    <button type="button" @click="email = 'pimpinan@baznas.id'; password = 'password'" onclick="window.fillLogin && window.fillLogin('pimpinan@baznas.id', 'password')" class="px-2 py-2 text-xs font-medium text-slate-700 bg-slate-50 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 border border-slate-200 rounded-lg transition-colors flex items-center justify-center gap-1.5 truncate cursor-pointer" title="Pimpinan BAZNAS (pimpinan@baznas.id)">
                        <svg class="w-3.5 h-3.5 shrink-0 text-amber-500 fill-amber-400" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        <span>Pimpinan</span>
                    </button>
                </div>
            </div>
            @endif

            <p class="text-[14px] text-slate-500">
                Butuh bantuan?
                <a href="#" class="text-[#118243] font-semibold hover:text-[#0d6835] hover:underline transition-colors">Hubungi bantuan teknis</a>
            </p>
        </div>
    </div>
</div>
@endsection
