@extends('layouts.app')

@php
    $daftarBulan = $daftarBulan ?? [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $daftarTahun = $daftarTahun ?? range(min((int)date('Y') - 2, 2024), max((int)date('Y') + 4, 2030));
    $currentBulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ][(int)date('n')];
@endphp

@section('content')
<div class="space-y-6" x-data="{ importModalOpen: false, modalUpzId: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700">Pengumpulan</span>
                <h1 class="text-2xl font-bold text-slate-800">Setoran ZIS UPZ</h1>
            </div>
            <p class="text-sm text-slate-500">Pencatatan dan arsip bukti setoran ZIS dari Unit Pengumpul Zakat ke BAZNAS.</p>
        </div>
        
        <div class="flex items-center gap-2.5 flex-wrap">
            <a :href="'{{ route('pengumpulan.upz.template') }}' + (modalUpzId ? '/' + modalUpzId : '')" class="inline-flex items-center justify-center rounded-xl bg-white border border-slate-200 px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all" title="Unduh template Excel">
                <svg class="mr-1.5 h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Template Excel
            </a>

            <button type="button" @click="importModalOpen = true" class="inline-flex items-center justify-center rounded-xl bg-white border border-slate-200 px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                <svg class="mr-1.5 h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Import Excel
            </button>

            <a href="{{ route('pengumpulan.upz.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#118243] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#0d6835] transition-all">
                <svg class="mr-2 -ml-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Input Setoran UPZ
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('pengumpulan.upz.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="No. Setoran / Ket..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div>
                <select name="lembaga_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm">
                    <option value="">-- Semua UPZ --</option>
                    @foreach($upzs as $u)
                        <option value="{{ $u->id }}" {{ $lembagaId == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="bulan" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm">
                    <option value="">-- Semua Bulan --</option>
                    @foreach($daftarBulan as $b)
                        <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="tahun" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm">
                    <option value="">-- Semua Tahun --</option>
                    @foreach($daftarTahun as $t)
                        <option value="{{ $t }}" {{ (string)$tahun === (string)$t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl text-sm transition-all">Filter</button>
                <a href="{{ route('pengumpulan.upz.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm transition-all">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">UPZ</th>
                        <th class="px-6 py-4">Tgl / Periode</th>
                        <th class="px-6 py-4 text-right">Zakat</th>
                        <th class="px-6 py-4 text-right">Infak</th>
                        <th class="px-6 py-4 text-right">Total Setoran</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($setorans as $s)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $s->upz->nama ?? '-' }}</div>
                                <div class="text-xs text-slate-400">Kode: {{ $s->upz->kode_simba ?? '-' }}</div>
                                <div class="text-[11px] font-mono text-emerald-700 mt-0.5">
                                    <a href="{{ route('pengumpulan.upz.show', $s->id) }}" class="hover:underline">{{ $s->nomor_setoran }}</a>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div class="font-medium text-slate-700">{{ optional($s->tanggal_setor)->format('d M Y') }}</div>
                                <div class="text-slate-400">{{ $s->periode }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-right font-mono">
                                Rp {{ number_format($s->total_zakat, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-xs text-right font-mono">
                                Rp {{ number_format($s->total_infak, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-xs text-right font-mono font-bold text-emerald-700">
                                Rp {{ number_format($s->total_jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    {{ $s->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('pengumpulan.upz.show', $s->id) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Detail">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('pengumpulan.upz.cetak', $s->id) }}" target="_blank" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Cetak Lampiran Setoran">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                    <form action="{{ route('pengumpulan.upz.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus setoran ini beserta sinkronisasi transaksi buku kas terkait?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                Belum ada data setoran UPZ.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($setorans->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $setorans->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Import Setoran Excel (Alpine.js Modal, Strictly No JS Native Alert) -->
    <div x-show="importModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="importModalOpen = false"></div>

            <!-- Modal Body -->
            <div class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Import Setoran ZIS UPZ</h3>
                        <p class="text-xs text-slate-500">Unggah file spreadsheet (XLSX, XLS, CSV) hasil rekap UPZ.</p>
                    </div>
                    <button type="button" @click="importModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('pengumpulan.upz.import-preview') }}" enctype="multipart/form-data" class="mt-4 space-y-4 text-xs">
                    @csrf

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-semibold text-slate-700">Unit Pengumpul Zakat (UPZ) *</label>
                            <a :href="'{{ route('pengumpulan.upz.template') }}' + (modalUpzId ? '/' + modalUpzId : '')" class="text-emerald-700 hover:underline flex items-center gap-1 font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Unduh Template UPZ
                            </a>
                        </div>
                        <select name="lembaga_id" required x-model="modalUpzId" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <option value="">-- Pilih UPZ Tujuan --</option>
                            @foreach($upzs as $u)
                                <option value="{{ $u->id }}">{{ $u->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal Setor *</label>
                            <input type="date" name="tanggal_setor" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Bulan Periode *</label>
                            <select name="bulan" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                @foreach($daftarBulan as $b)
                                    <option value="{{ $b }}" {{ $b === $currentBulanIndo ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tahun Periode *</label>
                            <select name="tahun" required class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                @foreach($daftarTahun as $t)
                                    <option value="{{ $t }}" {{ (string)$t === date('Y') ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">File Spreadsheet (.xlsx, .xls, .csv) *</label>
                        <input type="file" name="file" required accept=".xlsx,.xls,.csv" class="w-full px-3 py-2 border border-slate-200 rounded-xl file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Gunakan template resmi agar baris terpetakan otomatis.</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Catatan / Keterangan</label>
                        <input type="text" name="keterangan" placeholder="Keterangan setoran atau nomor referensi bank..." class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="importModalOpen = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition-all">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white rounded-xl font-semibold transition-all flex items-center gap-1.5">
                            <span>Lanjut ke Verifikasi (Preview)</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
