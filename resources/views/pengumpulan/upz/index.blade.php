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
                <x-heroicon-o-arrow-down-tray class="mr-1.5 h-4 w-4 text-emerald-600" />
                Template Excel
            </a>

            <button type="button" @click="importModalOpen = true" class="inline-flex items-center justify-center rounded-xl bg-white border border-slate-200 px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                <x-heroicon-o-arrow-up-tray class="mr-1.5 h-4 w-4 text-emerald-600" />
                Import Excel
            </button>

            <a href="{{ route('pengumpulan.upz.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#118243] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#0d6835] transition-all">
                <x-heroicon-o-plus class="mr-2 -ml-1 h-5 w-5" />
                Input Setoran UPZ
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('pengumpulan.upz.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="No. Setoran / Ket..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
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

            <div>
                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="Menunggu Verifikasi" {{ $status == 'Menunggu Verifikasi' ? 'selected' : '' }}>⏳ Menunggu Verifikasi</option>
                    <option value="Terverifikasi" {{ $status == 'Terverifikasi' ? 'selected' : '' }}>✅ Terverifikasi</option>
                    <option value="Ditolak" {{ $status == 'Ditolak' ? 'selected' : '' }}>❌ Ditolak</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl text-sm transition-all flex-1">Filter</button>
                <a href="{{ route('pengumpulan.upz.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm transition-all text-center">Reset</a>
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
                                <div class="flex flex-col items-center gap-1.5">
                                    @if($s->status === 'Terverifikasi')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200/60">
                                            <x-heroicon-o-check class="w-3.5 h-3.5 text-emerald-600" />
                                            Terverifikasi
                                        </span>
                                        @if($s->verified_at)
                                            <div class="text-[10px] text-slate-500 leading-tight">
                                                <span>{{ $s->verifiedBy?->person?->nama ?? $s->verifiedBy?->name ?? 'Staf BAZNAS' }}</span>
                                                <span class="block text-[9px] text-slate-400 font-mono">{{ optional($s->verified_at)->format('d/m/Y H:i') }}</span>
                                            </div>
                                        @endif
                                    @elseif($s->status === 'Ditolak')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200/60">
                                            <x-heroicon-o-x-mark class="w-3.5 h-3.5 text-rose-600" />
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200/60">
                                            <x-heroicon-o-clock class="w-3.5 h-3.5 text-amber-600" />
                                            Menunggu Verifikasi
                                        </span>
                                    @endif

                                    @if($s->bukti_setor)
                                        <a href="{{ asset('storage/' . $s->bukti_setor) }}" target="_blank" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors" title="Lihat Bukti Transfer">
                                            📎 Ada Bukti
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-500">
                                            Tanpa Bukti
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('pengumpulan.upz.show', $s->id) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Detail">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </a>
                                    @if(auth()->user()?->isBaznasRole() || auth()->user()?->hasBaznasAppointment() || auth()->user()?->isSuperuser())
                                        <a href="{{ route('pengumpulan.upz.edit', $s->id) }}" class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Setoran">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </a>
                                    @endif
                                    <a href="{{ route('pengumpulan.upz.cetak', $s->id) }}" target="_blank" class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Cetak Lampiran Setoran">
                                        <x-heroicon-o-printer class="w-4 h-4" />
                                    </a>
                                    <form action="{{ route('pengumpulan.upz.destroy', $s->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="$confirm({ title: 'Hapus Setoran UPZ', message: 'Hapus setoran ini beserta sinkronisasi transaksi buku kas terkait?', confirmText: 'Hapus', isDanger: true }).then(ok => { if (ok) $el.closest('form').submit(); })" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <x-heroicon-o-trash class="w-4 h-4" />
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
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('pengumpulan.upz.import-preview') }}" enctype="multipart/form-data" class="mt-4 space-y-4 text-xs">
                    @csrf

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-semibold text-slate-700">Unit Pengumpul Zakat (UPZ) *</label>
                            <a :href="'{{ route('pengumpulan.upz.template') }}' + (modalUpzId ? '/' + modalUpzId : '')" class="text-emerald-700 hover:underline flex items-center gap-1 font-semibold">
                                <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
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
