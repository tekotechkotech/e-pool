@extends('layouts.app')

@php
    $daftarBulan = $daftarBulan ?? [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $currentBulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ][(int)date('n')];
    $selectedBulan = old('bulan');
    $selectedTahun = old('tahun');
    if (!$selectedBulan && old('periode')) {
        $parts = explode(' ', old('periode'));
        $selectedBulan = $parts[0] ?? null;
        $selectedTahun = $parts[1] ?? null;
    }
    $selectedBulan = $selectedBulan ?: $currentBulanIndo;
    $selectedTahun = $selectedTahun ?: date('Y');
    $daftarTahun = $daftarTahun ?? range(min((int)date('Y') - 2, 2024), max((int)date('Y') + 4, 2030));

    $isUpzUser = $isUpzUser ?? false;
    $lockedUpz = $lockedUpz ?? null;
    $initialLembagaId = old('lembaga_id', $lockedUpz ? $lockedUpz->id : '');
    $initialUpz = null;
    if ($lockedUpz) {
        $initialUpz = [
            'id' => $lockedUpz->id,
            'nama' => $lockedUpz->nama,
            'kode_simba' => $lockedUpz->kode_simba ?? '',
        ];
    } elseif ($initialLembagaId && isset($upzs)) {
        $found = $upzs->firstWhere('id', (int)$initialLembagaId);
        if ($found) {
            $initialUpz = [
                'id' => $found->id,
                'nama' => $found->nama,
                'kode_simba' => $found->kode_simba ?? '',
            ];
        }
    }
    $initialItems = old('items', []);
    $defaultItems = [
        [
            'muzakki_id' => '',
            'nama' => '',
            'nik' => '',
            'npwz' => '',
            'nominal_zakat' => 0,
            'nominal_infak' => 0,
            'nominal_fitrah' => 0,
            'nominal_titipan' => 0,
            'keterangan' => '',
            'status' => 'belum_terdaftar'
        ]
    ];
@endphp

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="setoranWorkspace()">
    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('pengumpulan.upz.index') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors">Setoran UPZ</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">Unified Workspace</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Input Setoran UPZ Baru</h1>
            <p class="text-xs sm:text-sm text-slate-500">Pencarian instan UPZ, drag-and-drop bukti setor & spreadsheet, serta pengisian massal dalam 1 ruang kerja.</p>
        </div>
        <div class="flex items-center gap-2">
            <template x-if="selectedLembagaId">
                <a :href="'{{ route('pengumpulan.upz.template') }}/' + selectedLembagaId" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-xs transition-all flex items-center gap-1.5" title="Download template Excel terisi muzakki UPZ ini">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-emerald-600" />
                    Template Excel UPZ
                </a>
            </template>
            <a href="{{ route('pengumpulan.upz.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                Kembali
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-700 shadow-xs">
            <div class="font-bold mb-1 flex items-center gap-1.5">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-600" />
                Terdapat kendala validasi data:
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Utama Setoran Workspace -->
    <form method="POST" action="{{ route('pengumpulan.upz.store') }}" enctype="multipart/form-data" @submit="submitForm($event)" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        @csrf

        <!-- 1. DATA INDUK SETORAN -->
        <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">1. Data Induk Setoran</h2>
                <p class="text-xs text-slate-500">Pilih UPZ dan tentukan tanggal setoran serta periode buku kas.</p>
            </div>
            <div x-show="loadingMuzakki" class="text-xs text-emerald-700 font-medium flex items-center gap-1.5" style="display: none;">
                <svg class="animate-spin h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                Memuat muzakki aktif UPZ...
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Pencarian Asynchronous UPZ / Locked UPZ Mandiri -->
            <div class="sm:col-span-2 lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Pengumpul Zakat (UPZ) *</label>
                
                @if($isUpzUser && $lockedUpz)
                    <!-- UPZ User Mandiri State (Terkunci ke UPZ Akun Sendiri) -->
                    <div class="space-y-2">
                        <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-[#118243] text-white flex items-center justify-center font-bold text-sm shrink-0">
                                    <x-heroicon-o-building-office-2 class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs sm:text-sm font-bold text-emerald-950">{{ $lockedUpz->nama }}</span>
                                        @if($lockedUpz->kode_simba)
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-mono font-medium">SIMBA: {{ $lockedUpz->kode_simba }}</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-emerald-700 flex items-center gap-2 mt-0.5">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-200/80 text-emerald-900">
                                            Input Mandiri UPZ: {{ $lockedUpz->nama }}
                                        </span>
                                        <span>•</span>
                                        <span x-text="items.length + ' baris rincian muzakki'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-2.5 bg-emerald-50/50 rounded-xl border border-emerald-200/60 text-[11px] text-emerald-900 flex items-start gap-2">
                            <x-heroicon-o-information-circle class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                            <span>Setoran yang diinput oleh Pengurus UPZ akan disimpan dengan status <strong>"Menunggu Verifikasi"</strong> oleh Admin BAZNAS sebelum dibukukan ke Buku Kas.</span>
                        </div>
                    </div>
                    <input type="hidden" name="lembaga_id" value="{{ $lockedUpz->id }}">
                @else
                    <!-- Admin BAZNAS: Pencarian Asynchronous UPZ -->
                    <!-- State Belum Memilih UPZ: Input Pencarian Asynchronous -->
                    <div x-show="!selectedLembagaId" class="relative">
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="upzSearchQuery" 
                                @input.debounce.300ms="searchUpz()" 
                                @focus="if(upzSearchQuery.replace(/\s+/g, '').length >= 4) searchDropdownOpen = true"
                                @click.outside="searchDropdownOpen = false"
                                placeholder="Ketik min. 4 huruf nama UPZ (misal: 'Dinas', 'Masjid', 'Kecamatan')..." 
                                class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 placeholder:text-slate-400">
                            
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                            </div>

                            <div x-show="isSearchingUpz" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none" style="display: none;">
                                <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            </div>
                        </div>

                        <!-- Dropdown List Hasil Pencarian Asynchronous -->
                        <div x-show="searchDropdownOpen" class="absolute z-30 left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100" style="display: none;" x-cloak>
                            <template x-if="upzSearchResults.length > 0">
                                <div>
                                    <template x-for="u in upzSearchResults" :key="u.id">
                                        <button type="button" @click="selectUpz(u)" class="w-full px-4 py-2.5 text-left hover:bg-emerald-50 flex items-center justify-between transition-colors group">
                                            <div>
                                                <div class="font-semibold text-slate-800 text-xs group-hover:text-emerald-800" x-text="u.nama"></div>
                                                <div class="text-[11px] text-slate-400 font-mono" x-show="u.kode_simba" x-text="'Kode SIMBA: ' + u.kode_simba"></div>
                                            </div>
                                            <span class="text-xs text-emerald-600 font-medium group-hover:translate-x-0.5 transition-transform flex items-center gap-1">
                                                Pilih
                                                <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                                            </span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                            <template x-if="upzSearchResults.length === 0 && !isSearchingUpz && upzSearchQuery.replace(/\s+/g, '').length >= 4">
                                <div class="px-4 py-3 text-xs text-slate-500 text-center">
                                    Tidak ada UPZ yang cocok dengan kata kunci "<span class="font-semibold text-slate-700" x-text="upzSearchQuery"></span>".
                                </div>
                            </template>
                        </div>

                        <div class="mt-1 text-[11px] text-slate-400 flex items-center justify-between">
                            <span>Pencarian aktif otomatis (min. 4 karakter tanpa spasi).</span>
                            <span x-show="upzSearchQuery.replace(/\s+/g, '').length > 0" x-text="'Panjang: ' + upzSearchQuery.replace(/\s+/g, '').length + '/4'"></span>
                        </div>
                    </div>

                    <!-- State Sudah Memilih UPZ: Kartu UPZ Terpilih -->
                    <div x-show="selectedLembagaId && selectedUpz" style="display: none;">
                        <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-[#118243] text-white flex items-center justify-center font-bold text-sm shrink-0">
                                    <x-heroicon-o-building-office-2 class="w-5 h-5" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs sm:text-sm font-bold text-emerald-950" x-text="selectedUpz ? selectedUpz.nama : ''"></span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-mono font-medium" x-show="selectedUpz && selectedUpz.kode_simba" x-text="'SIMBA: ' + (selectedUpz ? selectedUpz.kode_simba : '')"></span>
                                    </div>
                                    <div class="text-[11px] text-emerald-700 flex items-center gap-2 mt-0.5">
                                        <span>UPZ Terpilih</span>
                                        <span>•</span>
                                        <span x-text="items.length + ' baris rincian muzakki'"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="changeUpz()" class="px-2.5 py-1.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 rounded-lg text-xs font-semibold shadow-xs transition-all flex items-center gap-1">
                                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5 text-slate-500" />
                                    Ganti UPZ
                                </button>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="lembaga_id" :value="selectedLembagaId" required>
                @endif
            </div>

            <!-- Tanggal Setor -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Setor *</label>
                <input type="date" name="tanggal_setor" x-model="tanggalSetor" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>

            <!-- Bulan & Tahun Periode -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Bulan *</label>
                    <select name="bulan" x-model="selectedBulan" required class="w-full px-2.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        @foreach($daftarBulan as $b)
                            <option value="{{ $b }}" {{ $selectedBulan === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun *</label>
                    <select name="tahun" x-model="selectedTahun" required class="w-full px-2.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        @foreach($daftarTahun as $t)
                            <option value="{{ $t }}" {{ (string)$selectedTahun === (string)$t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <input type="hidden" name="periode" :value="selectedBulan + ' ' + selectedTahun">

            <!-- Keterangan -->
            <div class="sm:col-span-2 lg:col-span-4">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Keterangan</label>
                <input type="text" name="keterangan" x-model="keterangan" placeholder="Keterangan transfer bank, nomor nota dinas, atau informasi tambahan..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>
        </div>

        <!-- 2. WORKSPACE CENTER: DRAG & DROP BUKTI TRANSFER & INLINE SPREADSHEET PARSER -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 pt-2">
            <!-- Dropzone Bukti Transfer -->
            <div class="bg-slate-50/70 border border-slate-200 rounded-2xl p-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold text-slate-800">Bukti Transfer / Nota Setoran</span>
                        </div>
                        <span class="text-[11px] text-slate-400">JPG, PNG, WEBP, PDF (Maks. 5MB)</span>
                    </div>

                    <!-- Input File Hidden -->
                    <input 
                        type="file" 
                        name="bukti_setor" 
                        x-ref="buktiInput" 
                        accept=".jpg,.jpeg,.png,.webp,.pdf" 
                        @change="handleFileBukti($event)" 
                        class="hidden"
                    >

                    <!-- Drop Area Bukti Transfer -->
                    <div 
                        x-show="!buktiFile"
                        @click="$refs.buktiInput.click()"
                        @dragover.prevent="isDraggingBukti = true"
                        @dragleave.prevent="isDraggingBukti = false"
                        @drop.prevent="handleDropBukti($event)"
                        class="border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-all flex flex-col items-center justify-center min-h-[110px]"
                        :class="isDraggingBukti ? 'border-emerald-500 bg-emerald-50/50 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-emerald-400 bg-white'"
                    >
                        <x-heroicon-o-photo class="w-7 h-7 text-slate-400 mb-1.5" />
                        <div class="text-xs font-semibold text-slate-700">
                            Tarik file bukti ke sini, atau <span class="text-emerald-700 underline">pilih file</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Struk transfer bank, kwitansi, atau bukti kas</div>
                    </div>

                    <!-- File Terpilih & Live Preview -->
                    <div x-show="buktiFile" style="display: none;" class="p-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Image Thumbnail Preview -->
                            <template x-if="buktiFileType === 'image' && buktiPreviewUrl">
                                <img :src="buktiPreviewUrl" class="w-12 h-12 object-cover rounded-lg border border-slate-200 shrink-0">
                            </template>
                            <!-- PDF Icon Preview -->
                            <template x-if="buktiFileType === 'pdf'">
                                <div class="w-12 h-12 rounded-lg bg-rose-50 border border-rose-200 text-rose-600 flex flex-col items-center justify-center shrink-0 font-bold text-[10px]">
                                    <x-heroicon-o-document class="w-5 h-5 mb-0.5" />
                                    PDF
                                </div>
                            </template>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-800 truncate" x-text="buktiFileName"></div>
                                <div class="text-[11px] text-slate-400" x-text="buktiFileSizeFormatted"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="$refs.buktiInput.click()" class="px-2.5 py-1 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg text-xs font-semibold transition-colors">
                                Ganti
                            </button>
                            <button type="button" @click="removeBukti()" class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Hapus File">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dropzone Inline Excel Parser -->
            <div class="bg-slate-50/70 border border-slate-200 rounded-2xl p-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span class="text-xs font-bold text-slate-800">Inline Spreadsheet Parser (Otomatisasi Tabel)</span>
                        </div>
                        <span class="text-[11px] text-slate-400">.xlsx, .xls, .csv</span>
                    </div>

                    <!-- Input File Hidden Excel -->
                    <input 
                        type="file" 
                        x-ref="excelInput" 
                        accept=".xlsx,.xls,.csv" 
                        @change="handleFileExcel($event)" 
                        class="hidden"
                    >

                    <!-- Drop Area Excel Parser -->
                    <div 
                        @click="$refs.excelInput.click()"
                        @dragover.prevent="isDraggingExcel = true"
                        @dragleave.prevent="isDraggingExcel = false"
                        @drop.prevent="handleDropExcel($event)"
                        class="border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-all flex flex-col items-center justify-center min-h-[110px]"
                        :class="isDraggingExcel ? 'border-emerald-500 bg-emerald-50/50 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-emerald-400 bg-white'"
                    >
                        <div x-show="!isParsingExcel" class="flex flex-col items-center">
                            <x-heroicon-o-document-chart-bar class="w-7 h-7 text-emerald-600 mb-1.5" />
                            <div class="text-xs font-semibold text-slate-700">
                                Tarik spreadsheet (.xlsx, .csv) ke sini untuk isi tabel in-place
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Memetakan muzakki & nominal langsung tanpa reload halaman</div>
                        </div>

                        <div x-show="isParsingExcel" style="display: none;" class="flex flex-col items-center gap-1.5">
                            <svg class="animate-spin h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <div class="text-xs font-bold text-emerald-800">Membaca dan memetakan baris spreadsheet...</div>
                        </div>
                    </div>

                    <!-- Notifikasi Sukses / Error Parser -->
                    <div x-show="excelSuccessMessage" style="display: none;" class="mt-2 p-2 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-800 flex items-center justify-between">
                        <span x-text="excelSuccessMessage"></span>
                        <button type="button" @click="excelSuccessMessage = ''" class="text-emerald-600 hover:text-emerald-800 text-[11px] font-bold">×</button>
                    </div>
                    <div x-show="excelErrorMessage" style="display: none;" class="mt-2 p-2 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700 flex items-center justify-between">
                        <span x-text="excelErrorMessage"></span>
                        <button type="button" @click="excelErrorMessage = ''" class="text-red-500 hover:text-red-700 text-[11px] font-bold">×</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. SMART BULK ACTIONS BAR & FOCUS MODE -->
        <div class="border-t border-slate-100 pt-5">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pb-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900">3. Rincian Muzakki & Munfiq</h2>
                    <p class="text-xs text-slate-500">Gunakan aksi massal atau ubah nominal per baris secara real-time.</p>
                </div>

                <!-- Action Toolbar -->
                <div class="flex items-center flex-wrap gap-2">
                    <!-- Quick Nominal Apply -->
                    <div class="flex items-center gap-1.5 bg-slate-50 p-1 rounded-xl border border-slate-200">
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                            <input 
                                type="number" 
                                x-model.number="bulkNominal" 
                                min="0" 
                                placeholder="Nominal Zakat..." 
                                class="pl-8 pr-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs w-32 sm:w-36 font-mono focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                            >
                        </div>
                        <button 
                            type="button" 
                            @click="applyBulkNominal()" 
                            class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-lg text-xs shadow-xs transition-all"
                            title="Terapkan nominal zakat ini ke semua baris"
                        >
                            Terapkan Semua
                        </button>
                    </div>

                    <!-- Tandai Semua Nihil (Rp 0) -->
                    <button 
                        type="button" 
                        @click="markAllNihil()" 
                        class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold rounded-xl text-xs shadow-xs transition-all"
                    >
                        Tandai Semua Nihil (Rp 0)
                    </button>

                    <!-- Toggle Focus Mode (Musim Ramadhan: Fitrah & Titipan) -->
                    <button 
                        type="button" 
                        @click="showFitrahTitipan = !showFitrahTitipan" 
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all flex items-center gap-1.5"
                        :class="showFitrahTitipan ? 'bg-amber-50 text-amber-900 border-amber-300' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    >
                        <x-heroicon-o-sun class="w-3.5 h-3.5" />
                        <span x-text="showFitrahTitipan ? 'Mode Ramadhan Aktif (Fitrah & Titipan)' : '+ Fitrah & Titipan (Ramadhan)'"></span>
                    </button>

                    <!-- Tambah Baris Manual -->
                    <button 
                        type="button" 
                        @click="addRow()" 
                        class="px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 font-semibold rounded-xl text-xs flex items-center gap-1 transition-all"
                    >
                        <x-heroicon-o-plus class="w-3.5 h-3.5" />
                        Tambah Baris
                    </button>
                </div>
            </div>

            <!-- TABEL RINCIAN MUZAKKI -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl shadow-xs">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase font-semibold border-b border-slate-200 text-[11px]">
                        <tr>
                            <th class="px-3 py-2.5 text-center w-12">No</th>
                            <th class="px-3 py-2.5 min-w-[200px]">Nama Muzakki *</th>
                            <th class="px-3 py-2.5 min-w-[120px]">NPWZ</th>
                            <th class="px-3 py-2.5 min-w-[110px] text-right">Zakat (Rp)</th>
                            <th class="px-3 py-2.5 min-w-[110px] text-right">Infak (Rp)</th>
                            <template x-if="showFitrahTitipan">
                                <th class="px-3 py-2.5 min-w-[100px] text-right bg-amber-50/50 text-amber-900">Fitrah (Rp)</th>
                            </template>
                            <template x-if="showFitrahTitipan">
                                <th class="px-3 py-2.5 min-w-[100px] text-right bg-amber-50/50 text-amber-900">Titipan (Rp)</th>
                            </template>
                            <th class="px-3 py-2.5 min-w-[120px] text-right">Total (Rp)</th>
                            <th class="px-3 py-2.5 text-center min-w-[120px]">Status</th>
                            <th class="px-2 py-2.5 text-center min-w-[80px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="(item, idx) in items" :key="idx">
                            <tr class="hover:bg-slate-50/70 transition-colors" :class="item.status === 'belum_terdaftar' ? 'bg-amber-50/20' : (item.status === 'nihil' ? 'bg-slate-50/30' : '')">
                                <td class="px-3 py-2 text-center font-mono text-slate-400" x-text="idx + 1"></td>
                                
                                <!-- Nama & Muzakki ID -->
                                <td class="px-3 py-2">
                                    <input type="hidden" :name="'items[' + idx + '][muzakki_id]'" :value="item.muzakki_id || ''">
                                    <input type="hidden" :name="'items[' + idx + '][nik]'" :value="item.nik || ''">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            :name="'items[' + idx + '][nama]'" 
                                            x-model="item.nama" 
                                            required 
                                            placeholder="Nama lengkap muzakki..." 
                                            class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 font-medium text-slate-800"
                                        >
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-400">
                                        <span x-show="item.muzakki_id" class="text-emerald-700 font-mono" x-text="'ID: #' + item.muzakki_id"></span>
                                        <span x-show="item.nik" class="font-mono" x-text="'NIK: ' + item.nik"></span>
                                    </div>
                                </td>

                                <!-- NPWZ -->
                                <td class="px-3 py-2">
                                    <input 
                                        type="text" 
                                        :name="'items[' + idx + '][npwz]'" 
                                        x-model="item.npwz" 
                                        placeholder="NPWZ (opsional)..." 
                                        class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs font-mono focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                    >
                                </td>

                                <!-- Zakat -->
                                <td class="px-3 py-2">
                                    <input 
                                        type="number" 
                                        :name="'items[' + idx + '][nominal_zakat]'" 
                                        x-model.number="item.nominal_zakat" 
                                        @input="updateItemStatus(idx)" 
                                        min="0" 
                                        placeholder="0" 
                                        class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-right font-mono focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                    >
                                </td>

                                <!-- Infak -->
                                <td class="px-3 py-2">
                                    <input 
                                        type="number" 
                                        :name="'items[' + idx + '][nominal_infak]'" 
                                        x-model.number="item.nominal_infak" 
                                        @input="updateItemStatus(idx)" 
                                        min="0" 
                                        placeholder="0" 
                                        class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-right font-mono focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                    >
                                </td>

                                <!-- Fitrah (Conditional Musim Ramadhan) -->
                                <template x-if="showFitrahTitipan">
                                    <td class="px-3 py-2 bg-amber-50/20">
                                        <input 
                                            type="number" 
                                            :name="'items[' + idx + '][nominal_fitrah]'" 
                                            x-model.number="item.nominal_fitrah" 
                                            @input="updateItemStatus(idx)" 
                                            min="0" 
                                            placeholder="0" 
                                            class="w-full px-2.5 py-1.5 border border-amber-200 rounded-lg text-xs text-right font-mono focus:ring-1 focus:ring-amber-500 focus:border-amber-500"
                                        >
                                    </td>
                                </template>

                                <!-- Titipan (Conditional Musim Ramadhan) -->
                                <template x-if="showFitrahTitipan">
                                    <td class="px-3 py-2 bg-amber-50/20">
                                        <input 
                                            type="number" 
                                            :name="'items[' + idx + '][nominal_titipan]'" 
                                            x-model.number="item.nominal_titipan" 
                                            @input="updateItemStatus(idx)" 
                                            min="0" 
                                            placeholder="0" 
                                            class="w-full px-2.5 py-1.5 border border-amber-200 rounded-lg text-xs text-right font-mono focus:ring-1 focus:ring-amber-500 focus:border-amber-500"
                                        >
                                    </td>
                                </template>

                                <!-- Subtotal Realtime -->
                                <td class="px-3 py-2 text-right font-mono font-bold text-emerald-800" x-text="formatRupiah(rowTotal(item))"></td>

                                <!-- Status Badge -->
                                <td class="px-3 py-2 text-center">
                                    <template x-if="item.status === 'terdaftar'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                            🟢 Terdaftar
                                        </span>
                                    </template>
                                    <template x-if="item.status === 'nihil'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                            ⚪ Nihil
                                        </span>
                                    </template>
                                    <template x-if="item.status === 'belum_terdaftar'">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">
                                            🟡 Belum Terdaftar
                                        </span>
                                    </template>
                                </td>

                                <!-- Aksi Baris -->
                                <td class="px-2 py-2 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <template x-if="item.status === 'belum_terdaftar'">
                                            <button 
                                                type="button" 
                                                @click="openQuickRegister(idx)" 
                                                class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-semibold rounded-md text-[11px] transition-colors"
                                                title="Registrasi Cepat Muzakki Ini"
                                            >
                                                + Reg
                                            </button>
                                        </template>

                                        <button 
                                            type="button" 
                                            @click="removeRow(idx)" 
                                            class="text-rose-500 hover:text-rose-700 p-1 hover:bg-rose-50 rounded-lg transition-colors disabled:opacity-25 disabled:cursor-not-allowed" 
                                            title="Hapus baris" 
                                            :disabled="items.length <= 1"
                                        >
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <!-- Summary Realtime Footer -->
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200 text-slate-800 text-xs">
                        <tr>
                            <td colspan="3" class="px-3 py-2.5 text-right font-bold text-slate-700">TOTAL SETORAN:</td>
                            <td class="px-3 py-2.5 text-right font-mono text-emerald-700" x-text="formatRupiah(totalZakat())"></td>
                            <td class="px-3 py-2.5 text-right font-mono text-emerald-700" x-text="formatRupiah(totalInfak())"></td>
                            <template x-if="showFitrahTitipan">
                                <td class="px-3 py-2.5 text-right font-mono text-emerald-700 bg-amber-50/50" x-text="formatRupiah(totalFitrah())"></td>
                            </template>
                            <template x-if="showFitrahTitipan">
                                <td class="px-3 py-2.5 text-right font-mono text-emerald-700 bg-amber-50/50" x-text="formatRupiah(totalTitipan())"></td>
                            </template>
                            <td class="px-3 py-2.5 text-right font-mono text-emerald-800 font-extrabold" x-text="formatRupiah(grandTotal())"></td>
                            <td colspan="2" class="px-3 py-2.5 text-center text-slate-400 font-normal text-[11px]" x-text="items.length + ' muzakki'"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 4. VERIFIKASI LANGSUNG (KHUSUS ADMIN BAZNAS) -->
        @if(!$isUpzUser)
            <div class="bg-emerald-50/40 border border-emerald-200/80 rounded-2xl p-4 sm:p-5 space-y-3">
                <div class="flex items-start gap-3">
                    <div class="pt-0.5">
                        <input 
                            type="checkbox" 
                            id="is_verified" 
                            name="is_verified" 
                            value="1" 
                            x-model="isVerified" 
                            class="w-4 h-4 rounded text-[#118243] focus:ring-[#118243] border-slate-300"
                        >
                    </div>
                    <div class="flex-1 space-y-1">
                        <label for="is_verified" class="text-xs sm:text-sm font-bold text-slate-800 cursor-pointer flex items-center gap-2">
                            <span>Tandai Data Sesuai & Langsung Terverifikasi</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Admin BAZNAS</span>
                        </label>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Jika dicentang, data setoran langsung berstatus <strong>Terverifikasi</strong> dan dibukukan ke Buku Kas (DanaTransaksi). Jika tidak dicentang, setoran berstatus <strong>Menunggu Verifikasi</strong>.
                        </p>
                        <div x-show="isVerified" x-transition class="pt-2">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Catatan Verifikasi (Opsional)</label>
                            <textarea 
                                name="catatan_verifikasi" 
                                x-model="catatanVerifikasi" 
                                rows="2" 
                                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 placeholder:text-slate-400 bg-white" 
                                placeholder="Contoh: Telah dicek rekening koran BSI / Bank Jateng sesuai mutasi..."
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- 5. BOTTOM ACTION & EKSEKUSI 1X SIMPAN -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100">
            <div class="text-xs text-slate-500">
                @if($isUpzUser)
                    Setoran yang diinput oleh Pengurus UPZ akan disimpan dengan status <strong>Menunggu Verifikasi</strong> oleh Admin BAZNAS.
                @else
                    Data setoran akan langsung tercatat dan mensinkronkan transaksi masuk pada buku kas BAZNAS jika terverifikasi.
                @endif
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <a href="{{ route('pengumpulan.upz.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs sm:text-sm transition-all text-center">
                    Batal
                </a>
                <button 
                    type="submit" 
                    :disabled="submittingForm" 
                    class="px-6 py-2.5 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl text-xs sm:text-sm shadow-md shadow-emerald-700/20 hover:shadow-lg transition-all flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <x-heroicon-o-check x-show="!submittingForm" class="w-4 h-4" />
                    <svg x-show="submittingForm" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span x-text="submittingForm ? 'Menyimpan...' : 'Simpan Setoran & Arsip Bukti'"></span>
                </button>
            </div>
        </div>
    </form>

    <!-- Modal Registrasi Cepat Muzakki (Alpine.js Modal In-Place, No Native Alert/Confirm) -->
    <div x-show="quickModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="closeQuickRegister()"></div>

            <!-- Modal Box -->
            <div class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Registrasi Cepat Muzakki</h3>
                        <p class="text-xs text-slate-500">Daftarkan muzakki baru ke data master UPZ & kaitkan otomatis ke baris ini.</p>
                    </div>
                    <button type="button" @click="closeQuickRegister()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div x-show="quickError" style="display: none;" class="mt-3 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs" x-text="quickError"></div>

                <div class="mt-4 space-y-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" x-model="quickForm.nama" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Nama sesuai identitas KTP">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">NIK (16 Digit) *</label>
                            <input type="text" x-model="quickForm.nik" maxlength="16" class="w-full px-3 py-2 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="3301xxxxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                            <select x-model="quickForm.jenis_kelamin" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <option value="">-- Pilih --</option>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">NPWZ (Opsional)</label>
                            <input type="text" x-model="quickForm.npwz" class="w-full px-3 py-2 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="NPWZ jika ada">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">No. Handphone</label>
                            <input type="text" x-model="quickForm.handphone" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Alamat</label>
                        <textarea x-model="quickForm.alamat" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Alamat domisili muzakki..."></textarea>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="closeQuickRegister()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition-all">
                        Batal
                    </button>
                    <button type="button" @click="saveQuickRegister()" :disabled="savingQuick" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white rounded-xl font-semibold transition-all disabled:opacity-50 flex items-center gap-1.5">
                        <span x-show="!savingQuick">Simpan & Kaitkan</span>
                        <span x-show="savingQuick">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- In-Page Toast Notification (Replaces JS Native Alert) -->
    <div 
        x-show="notification.show" 
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-6 right-6 z-50 max-w-sm w-full bg-white rounded-2xl shadow-xl border p-4 flex items-start gap-3"
        :class="{
            'border-emerald-200': notification.type === 'success',
            'border-red-200': notification.type === 'error',
            'border-blue-200': notification.type === 'info',
            'border-amber-200': notification.type === 'warning'
        }"
        style="display: none;"
        x-cloak
    >
        <div class="shrink-0 mt-0.5">
            <template x-if="notification.type === 'success'">
                <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">✓</div>
            </template>
            <template x-if="notification.type === 'error'">
                <div class="w-7 h-7 rounded-full bg-red-100 text-red-700 flex items-center justify-center font-bold">!</div>
            </template>
            <template x-if="notification.type === 'info' || notification.type === 'warning'">
                <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold">i</div>
            </template>
        </div>
        <div class="flex-1 text-xs">
            <div class="font-bold text-slate-800" x-text="notification.type === 'error' ? 'Peringatan' : 'Pemberitahuan'"></div>
            <div class="text-slate-600 mt-0.5 leading-relaxed" x-text="notification.message"></div>
        </div>
        <button type="button" @click="notification.show = false" class="text-slate-400 hover:text-slate-600 p-0.5">
            <x-heroicon-o-x-mark class="w-4 h-4" />
        </button>
    </div>
</div>

<script>
function setoranWorkspace() {
    return {
        isUpzUser: {{ $isUpzUser ? 'true' : 'false' }},
        isVerified: {{ old('is_verified', '1') ? 'true' : 'false' }},
        catatanVerifikasi: '{{ old('catatan_verifikasi', '') }}',

        // UPZ Search & Selection
        upzSearchQuery: '',
        isSearchingUpz: false,
        upzSearchResults: [],
        searchDropdownOpen: false,
        selectedLembagaId: '{{ $initialLembagaId }}',
        selectedUpz: @json($initialUpz),
        loadingMuzakki: false,

        // Periode & Induk
        tanggalSetor: '{{ old('tanggal_setor', date('Y-m-d')) }}',
        selectedBulan: '{{ $selectedBulan }}',
        selectedTahun: '{{ $selectedTahun }}',
        keterangan: '{{ old('keterangan', '') }}',

        // Bukti Transfer
        buktiFile: null,
        buktiPreviewUrl: null,
        buktiFileType: null,
        buktiFileName: '',
        buktiFileSizeFormatted: '',
        isDraggingBukti: false,

        // Inline Excel Parser
        isDraggingExcel: false,
        isParsingExcel: false,
        excelSuccessMessage: '',
        excelErrorMessage: '',

        // Bulk Actions & Focus Mode
        bulkNominal: '',
        showFitrahTitipan: false,
        submittingForm: false,

        // Items Data
        items: @json(!empty($initialItems) ? array_values($initialItems) : $defaultItems),

        // Quick Register Modal State
        quickModalOpen: false,
        quickRegisterRowIndex: null,
        savingQuick: false,
        quickError: '',
        quickForm: {
            nama: '',
            nik: '',
            jenis_kelamin: '',
            npwz: '',
            handphone: '',
            alamat: ''
        },

        // In-page Toast Notification
        notification: {
            show: false,
            type: 'info',
            message: ''
        },

        init() {
            // Jika ada initial lembaga tapi belum ada muzakki termuat
            if (this.selectedLembagaId && (!this.items || this.items.length === 0 || (this.items.length === 1 && !this.items[0].nama))) {
                this.loadMuzakki(this.selectedLembagaId);
            }
        },

        // Helper Toast Notification (Zero Native Alerts)
        showToast(type, message) {
            this.notification = {
                show: true,
                type: type,
                message: message
            };
            setTimeout(() => {
                if (this.notification.message === message) {
                    this.notification.show = false;
                }
            }, 5000);
        },

        // Asynchronous UPZ Search
        async searchUpz() {
            const cleanQuery = this.upzSearchQuery.replace(/\s+/g, '');
            if (cleanQuery.length < 4) {
                this.upzSearchResults = [];
                this.searchDropdownOpen = false;
                return;
            }

            this.isSearchingUpz = true;
            this.searchDropdownOpen = true;

            try {
                const res = await fetch("{{ route('pengumpulan.upz.search-upz') }}?q=" + encodeURIComponent(this.upzSearchQuery), {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.upzSearchResults = Array.isArray(data) ? data : [];
                } else {
                    this.upzSearchResults = [];
                }
            } catch (e) {
                console.error('Error searching UPZ:', e);
                this.upzSearchResults = [];
            } finally {
                this.isSearchingUpz = false;
            }
        },

        selectUpz(upz) {
            this.selectedLembagaId = upz.id;
            this.selectedUpz = upz;
            this.searchDropdownOpen = false;
            this.upzSearchQuery = '';
            this.upzSearchResults = [];
            this.loadMuzakki(upz.id);
        },

        changeUpz() {
            this.selectedLembagaId = '';
            this.selectedUpz = null;
            this.upzSearchQuery = '';
            this.upzSearchResults = [];
            this.searchDropdownOpen = false;
        },

        // Auto-load Muzakki Master Data UPZ
        async loadMuzakki(lembagaId) {
            if (!lembagaId) return;
            this.loadingMuzakki = true;
            try {
                const response = await fetch("{{ url('pengumpulan/upz/muzakki-by-upz') }}/" + lembagaId, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (Array.isArray(data) && data.length > 0) {
                    this.items = data.map(m => ({
                        muzakki_id: m.id,
                        nama: m.nama,
                        nik: m.nik || '',
                        npwz: m.npwz || '',
                        nominal_zakat: 0,
                        nominal_infak: 0,
                        nominal_fitrah: 0,
                        nominal_titipan: 0,
                        keterangan: '',
                        status: 'nihil'
                    }));
                    this.showToast('info', `${data.length} muzakki aktif UPZ berhasil dimuat.`);
                } else {
                    this.items = [
                        { muzakki_id: '', nama: '', nik: '', npwz: '', nominal_zakat: 0, nominal_infak: 0, nominal_fitrah: 0, nominal_titipan: 0, keterangan: '', status: 'belum_terdaftar' }
                    ];
                }
            } catch (err) {
                console.error('Error auto-loading muzakki:', err);
                this.showToast('error', 'Gagal memuat data master muzakki UPZ.');
            } finally {
                this.loadingMuzakki = false;
            }
        },

        // Bukti Transfer Drag & Drop Handlers
        handleDropBukti(e) {
            this.isDraggingBukti = false;
            const files = e.dataTransfer ? e.dataTransfer.files : null;
            if (files && files.length > 0) {
                this.processBuktiFile(files[0]);
            }
        },

        handleFileBukti(e) {
            const files = e.target ? e.target.files : null;
            if (files && files.length > 0) {
                this.processBuktiFile(files[0]);
            }
        },

        processBuktiFile(file) {
            if (!file) return;

            // Maksimal 5 MB
            if (file.size > 5 * 1024 * 1024) {
                this.showToast('error', 'Ukuran file bukti transfer melebihi batas maksimal 5 MB.');
                return;
            }

            const ext = file.name.split('.').pop().toLowerCase();
            const validExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            if (!validExts.includes(ext)) {
                this.showToast('error', 'Format file tidak didukung. Harap pilih gambar (JPG, PNG, WEBP) atau PDF.');
                return;
            }

            this.buktiFile = file;
            this.buktiFileName = file.name;
            this.buktiFileSizeFormatted = (file.size / (1024 * 1024)).toFixed(2) + ' MB';

            if (['jpg', 'jpeg', 'png', 'webp'].includes(ext) || file.type.startsWith('image/')) {
                this.buktiFileType = 'image';
                this.buktiPreviewUrl = URL.createObjectURL(file);
            } else {
                this.buktiFileType = 'pdf';
                this.buktiPreviewUrl = null;
            }

            // Sinkronkan ke input file sebenarnya untuk submission form
            if (this.$refs.buktiInput && typeof DataTransfer !== 'undefined') {
                const dt = new DataTransfer();
                dt.items.add(file);
                this.$refs.buktiInput.files = dt.files;
            }
            this.showToast('success', `Bukti transfer "${file.name}" siap diunggah.`);
        },

        removeBukti() {
            this.buktiFile = null;
            this.buktiFileName = '';
            this.buktiFileSizeFormatted = '';
            this.buktiFileType = null;
            if (this.buktiPreviewUrl) {
                URL.revokeObjectURL(this.buktiPreviewUrl);
                this.buktiPreviewUrl = null;
            }
            if (this.$refs.buktiInput) {
                this.$refs.buktiInput.value = '';
            }
        },

        // Inline Spreadsheet Drag & Drop Parser Handlers
        handleDropExcel(e) {
            this.isDraggingExcel = false;
            const files = e.dataTransfer ? e.dataTransfer.files : null;
            if (files && files.length > 0) {
                this.processExcelFile(files[0]);
            }
        },

        handleFileExcel(e) {
            const files = e.target ? e.target.files : null;
            if (files && files.length > 0) {
                this.processExcelFile(files[0]);
            }
        },

        async processExcelFile(file) {
            if (!file) return;

            const ext = file.name.split('.').pop().toLowerCase();
            if (!['xlsx', 'xls', 'csv'].includes(ext)) {
                this.showToast('error', 'File harus berformat spreadsheet (.xlsx, .xls, .csv).');
                return;
            }

            this.isParsingExcel = true;
            this.excelErrorMessage = '';
            this.excelSuccessMessage = '';

            const formData = new FormData();
            formData.append('file', file);
            if (this.selectedLembagaId) {
                formData.append('lembaga_id', this.selectedLembagaId);
            }

            try {
                const response = await fetch("{{ route('pengumpulan.upz.parse-inline-excel') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    let hasRamadhan = false;
                    this.items = data.items.map(it => {
                        const z = Number(it.nominal_zakat) || 0;
                        const i = Number(it.nominal_infak) || 0;
                        const f = Number(it.nominal_fitrah) || 0;
                        const t = Number(it.nominal_titipan) || 0;
                        if (f > 0 || t > 0) hasRamadhan = true;

                        let st = it.status;
                        if (!st) {
                            if (!it.muzakki_id) st = 'belum_terdaftar';
                            else st = (z + i + f + t) > 0 ? 'terdaftar' : 'nihil';
                        }

                        return {
                            muzakki_id: it.muzakki_id || '',
                            nama: it.nama || '',
                            nik: it.nik || '',
                            npwz: it.npwz || '',
                            nominal_zakat: z,
                            nominal_infak: i,
                            nominal_fitrah: f,
                            nominal_titipan: t,
                            keterangan: it.keterangan || '',
                            status: st
                        };
                    });

                    if (hasRamadhan) {
                        this.showFitrahTitipan = true;
                    }

                    this.excelSuccessMessage = `${data.total_rows} baris data berhasil dimuat ke dalam tabel!`;
                    this.showToast('success', `${data.total_rows} baris data spreadsheet berhasil dimuat ke tabel.`);
                } else {
                    const msg = data.message || 'Gagal memproses spreadsheet.';
                    this.excelErrorMessage = msg;
                    this.showToast('error', msg);
                }
            } catch (err) {
                console.error('Error parsing excel:', err);
                this.excelErrorMessage = 'Terjadi kesalahan sistem saat memproses file spreadsheet.';
                this.showToast('error', this.excelErrorMessage);
            } finally {
                this.isParsingExcel = false;
                if (this.$refs.excelInput) {
                    this.$refs.excelInput.value = '';
                }
            }
        },

        // Smart Bulk Actions
        applyBulkNominal() {
            const val = Math.max(0, parseInt(this.bulkNominal) || 0);
            if (val <= 0) {
                this.showToast('warning', 'Masukkan angka nominal zakat yang valid (> Rp 0).');
                return;
            }
            this.items.forEach((item, idx) => {
                item.nominal_zakat = val;
                this.updateItemStatus(idx);
            });
            this.bulkNominal = '';
            this.showToast('info', `Nominal zakat ${this.formatRupiah(val)} berhasil diterapkan ke seluruh baris.`);
        },

        markAllNihil() {
            this.items.forEach((item, idx) => {
                item.nominal_zakat = 0;
                item.nominal_infak = 0;
                item.nominal_fitrah = 0;
                item.nominal_titipan = 0;
                this.updateItemStatus(idx);
            });
            this.showToast('info', 'Seluruh baris telah ditandai nihil (Rp 0).');
        },

        // Table Rows Management
        addRow() {
            this.items.push({
                muzakki_id: '',
                nama: '',
                nik: '',
                npwz: '',
                nominal_zakat: 0,
                nominal_infak: 0,
                nominal_fitrah: 0,
                nominal_titipan: 0,
                keterangan: '',
                status: 'belum_terdaftar'
            });
        },

        removeRow(idx) {
            if (this.items.length > 1) {
                this.items.splice(idx, 1);
            }
        },

        rowTotal(item) {
            return (Number(item.nominal_zakat) || 0) +
                   (Number(item.nominal_infak) || 0) +
                   (Number(item.nominal_fitrah) || 0) +
                   (Number(item.nominal_titipan) || 0);
        },

        updateItemStatus(idx) {
            const item = this.items[idx];
            if (!item) return;
            const total = this.rowTotal(item);
            if (!item.muzakki_id) {
                item.status = 'belum_terdaftar';
            } else {
                item.status = total > 0 ? 'terdaftar' : 'nihil';
            }
        },

        // Totals Realtime Footer
        totalZakat() {
            return this.items.reduce((sum, i) => sum + (Number(i.nominal_zakat) || 0), 0);
        },

        totalInfak() {
            return this.items.reduce((sum, i) => sum + (Number(i.nominal_infak) || 0), 0);
        },

        totalFitrah() {
            return this.items.reduce((sum, i) => sum + (Number(i.nominal_fitrah) || 0), 0);
        },

        totalTitipan() {
            return this.items.reduce((sum, i) => sum + (Number(i.nominal_titipan) || 0), 0);
        },

        grandTotal() {
            return this.totalZakat() + this.totalInfak() + this.totalFitrah() + this.totalTitipan();
        },

        formatRupiah(val) {
            return 'Rp ' + (val || 0).toLocaleString('id-ID');
        },

        // Modal Registrasi Cepat Muzakki
        openQuickRegister(idx) {
            const item = this.items[idx];
            if (!item) return;

            if (!this.selectedLembagaId) {
                this.showToast('warning', 'Pilih Unit Pengumpul Zakat (UPZ) terlebih dahulu sebelum meregistrasi muzakki.');
                return;
            }

            this.quickRegisterRowIndex = idx;
            this.quickForm = {
                nama: item.nama && item.nama !== '-' ? item.nama : '',
                nik: item.nik || '',
                jenis_kelamin: '',
                npwz: item.npwz || '',
                handphone: '',
                alamat: ''
            };
            this.quickError = '';
            this.quickModalOpen = true;
        },

        closeQuickRegister() {
            this.quickModalOpen = false;
            this.quickRegisterRowIndex = null;
            this.quickError = '';
        },

        async saveQuickRegister() {
            if (!this.quickForm.nama || !this.quickForm.nama.trim()) {
                this.quickError = 'Nama lengkap muzakki wajib diisi.';
                return;
            }
            if (!this.quickForm.nik || !this.quickForm.nik.trim()) {
                this.quickError = 'NIK 16 digit wajib diisi.';
                return;
            }
            if (!this.selectedLembagaId) {
                this.quickError = 'Unit Pengumpul Zakat (UPZ) belum dipilih.';
                return;
            }

            this.savingQuick = true;
            this.quickError = '';

            try {
                const response = await fetch("{{ route('pengumpulan.upz.quick-store-muzakki') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nama: this.quickForm.nama.trim(),
                        nik: this.quickForm.nik.trim(),
                        lembaga_id: this.selectedLembagaId,
                        jenis_kelamin: this.quickForm.jenis_kelamin || null,
                        npwz: this.quickForm.npwz ? this.quickForm.npwz.trim() : null,
                        handphone: this.quickForm.handphone ? this.quickForm.handphone.trim() : null,
                        alamat: this.quickForm.alamat ? this.quickForm.alamat.trim() : null
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const rowIdx = this.quickRegisterRowIndex;
                    if (rowIdx !== null && this.items[rowIdx]) {
                        this.items[rowIdx].muzakki_id = data.muzakki.id;
                        this.items[rowIdx].nama = data.muzakki.nama;
                        this.items[rowIdx].nik = data.muzakki.nik;
                        this.items[rowIdx].npwz = data.muzakki.npwz || '';
                        this.updateItemStatus(rowIdx);
                    }
                    this.showToast('success', `Muzakki "${data.muzakki.nama}" berhasil didaftarkan dan dikaitkan!`);
                    this.closeQuickRegister();
                } else {
                    let errMsg = data.message || 'Gagal menyimpan data muzakki.';
                    if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey][0]) {
                            errMsg = data.errors[firstKey][0];
                        }
                    }
                    this.quickError = errMsg;
                }
            } catch (err) {
                console.error('Error saving quick register:', err);
                this.quickError = 'Terjadi kesalahan sistem saat mendaftarkan muzakki.';
            } finally {
                this.savingQuick = false;
            }
        },

        // Form Submit Validation
        submitForm(e) {
            if (!this.selectedLembagaId) {
                e.preventDefault();
                this.showToast('error', 'Harap pilih Unit Pengumpul Zakat (UPZ) terlebih dahulu.');
                return;
            }
            if (!this.items || this.items.length === 0) {
                e.preventDefault();
                this.showToast('error', 'Minimal harus ada 1 baris rincian muzakki.');
                return;
            }
            const hasEmptyName = this.items.some(i => !i.nama || !i.nama.trim());
            if (hasEmptyName) {
                e.preventDefault();
                this.showToast('error', 'Nama muzakki pada setiap baris wajib diisi.');
                return;
            }
            this.submittingForm = true;
        }
    }
}
</script>
@endsection