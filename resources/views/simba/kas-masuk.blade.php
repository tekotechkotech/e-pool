@extends('layouts.app')

@php
    $daftarBulan = $daftarBulan ?? [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $daftarTahun = $daftarTahun ?? range(min((int)date('Y') - 2, 2024), max((int)date('Y') + 4, 2030));
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-sky-100 text-sky-700">Simba Export</span>
                <h1 class="text-2xl font-bold text-slate-800">Export Simba: Kas Masuk ZIS</h1>
            </div>
            <p class="text-sm text-slate-500">Preview & Unduh file CSV format resmi migrasi kas masuk Simba BAZNAS.</p>
        </div>
        
        <div>
            <a href="{{ request()->fullUrlWithQuery(['download' => 'csv']) }}" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-sky-700 transition-all">
                <svg class="mr-2 -ml-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download CSV Simba
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('simba.kas-masuk') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Filter UPZ</label>
                <select name="lembaga_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                    <option value="">-- Semua UPZ --</option>
                    @foreach($upzs as $u)
                        <option value="{{ $u->id }}" {{ $lembagaId == $u->id ? 'selected' : '' }}>{{ $u->nama }} (Kode: {{ $u->kode_simba ?? '-' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Bulan</label>
                <select name="bulan" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                    <option value="">-- Semua Bulan --</option>
                    @foreach($daftarBulan as $b)
                        <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tahun</label>
                <select name="tahun" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
                    <option value="">-- Semua Tahun --</option>
                    @foreach($daftarTahun as $t)
                        <option value="{{ $t }}" {{ (string)$tahun === (string)$t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tgl Setor Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl text-xs transition-all">Filter</button>
                <a href="{{ route('simba.kas-masuk') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs transition-all">Reset</a>
            </div>
        </form>
    </div>

    <!-- Preview Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Preview Format: No, tgl_transaksi, NPWZ, nama, zakat, zakat fitrah, infak, titipan, Keterangan, Periode</span>
            <span class="text-xs text-slate-500 font-mono">Total: {{ $items->total() }} baris</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                <thead class="bg-slate-100/70 text-slate-700 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">tgl_transaksi</th>
                        <th class="px-4 py-3">NPWZ</th>
                        <th class="px-4 py-3">nama</th>
                        <th class="px-4 py-3 text-right">zakat</th>
                        <th class="px-4 py-3 text-right">zakat fitrah</th>
                        <th class="px-4 py-3 text-right">infak</th>
                        <th class="px-4 py-3 text-right">titipan</th>
                        <th class="px-4 py-3">Keterangan</th>
                        <th class="px-4 py-3">Periode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($items as $idx => $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5 text-center text-slate-400">{{ $items->firstItem() + $idx }}</td>
                            <td class="px-4 py-2.5">{{ optional($item->setoranUpz->tanggal_setor)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2.5 text-slate-500">{{ $item->npwz ?: ($item->muzakki?->npwz ?? '') }}</td>
                            <td class="px-4 py-2.5 font-bold font-sans text-slate-900">{{ $item->nama ?: ($item->muzakki?->person?->nama ?? '') }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ $item->nominal_zakat > 0 ? number_format($item->nominal_zakat, 0, ',', '') : '' }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ $item->nominal_fitrah > 0 ? number_format($item->nominal_fitrah, 0, ',', '') : '' }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ $item->nominal_infak > 0 ? number_format($item->nominal_infak, 0, ',', '') : '' }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-slate-800">{{ $item->nominal_titipan > 0 ? number_format($item->nominal_titipan, 0, ',', '') : '' }}</td>
                            <td class="px-4 py-2.5 font-sans">{{ $item->keterangan ?? '' }}</td>
                            <td class="px-4 py-2.5 font-sans text-slate-500">{{ $item->periode ?? ($item->setoranUpz?->periode ?? '') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-400 font-sans">
                                Tidak ada data kas masuk untuk preview.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
