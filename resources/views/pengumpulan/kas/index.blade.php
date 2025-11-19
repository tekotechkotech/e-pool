@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700">Pengumpulan</span>
                <h1 class="text-2xl font-bold text-slate-800">Buku Kas Masuk ZIS</h1>
            </div>
            <p class="text-sm text-slate-500">Histori dan rekonsiliasi seluruh penerimaan kas masuk dari setoran ZIS UPZ dan muzakki langsung.</p>
        </div>
        
        <div class="bg-emerald-50 border border-emerald-200 px-5 py-3 rounded-2xl flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold">
                Rp
            </div>
            <div>
                <p class="text-xs font-medium text-emerald-800 uppercase tracking-wider">Total Kas Masuk Terfilter</p>
                <p class="text-xl font-black text-emerald-900 font-mono">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('pengumpulan.kas.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari uraian / kode dana..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm">
            </div>

            <div>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-sm">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl text-sm transition-all">Filter</button>
                <a href="{{ route('pengumpulan.kas.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm transition-all">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Kode / Pos Dana</th>
                        <th class="px-6 py-4">Uraian Penerimaan</th>
                        <th class="px-6 py-4">Sumber Referensi</th>
                        <th class="px-6 py-4 text-right">Nominal Masuk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksis as $t)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono font-medium text-slate-700">
                                {{ optional($t->tanggal)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 font-mono text-xs font-bold text-slate-700">{{ $t->dana->kode ?? '-' }}</span>
                                <div class="text-xs font-semibold text-slate-800 mt-1">{{ $t->dana->nama ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-800 font-medium max-w-md">
                                {{ $t->uraian }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if($t->referensi)
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium">
                                        Setoran: {{ $t->referensi->nomor_setoran ?? 'ID #' . $t->referensi_id }}
                                    </span>
                                @else
                                    <span class="text-slate-400">Manual / Langsung</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-mono font-bold text-emerald-700 text-sm">
                                Rp {{ number_format($t->nominal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada data transaksi kas masuk untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transaksis->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $transaksis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
