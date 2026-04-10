@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header (Hidden on print) -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 print:hidden">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                <span class="p-2 rounded-xl bg-indigo-100 text-indigo-700">
                    <x-heroicon-o-chart-bar class="w-6 h-6" />
                </span>
                Matriks Penyaluran Program vs Asnaf
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Laporan silang (cross-tabulation) realisasi penyaluran dana menurut 5 Program Utama dan 8 Golongan Asnaf
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center gap-2">
                <x-heroicon-o-printer class="w-4 h-4" />
                Cetak Matriks
            </button>
        </div>
    </div>

    <!-- Filter Bar (Hidden on print) -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm space-y-4 print:hidden">
        <form method="GET" action="{{ route('laporan.program-asnaf') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase mr-1">Periode:</span>
                <a href="{{ route('laporan.program-asnaf', array_merge(request()->query(), ['periode_tipe' => 'all'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodeTipe === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Waktu
                </a>
                <a href="{{ route('laporan.program-asnaf', array_merge(request()->query(), ['periode_tipe' => 'this_month'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodeTipe === 'this_month' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Bulan Ini
                </a>
                <a href="{{ route('laporan.program-asnaf', array_merge(request()->query(), ['periode_tipe' => 'this_year'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodeTipe === 'this_year' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Tahun Ini
                </a>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-400 uppercase">Tampilan:</span>
                    <select name="tampilan" onchange="this.form.submit()" class="bg-white border border-slate-300 shadow-xs text-slate-700 text-xs font-semibold rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        <option value="nominal" {{ $tampilan === 'nominal' ? 'selected' : '' }}>Nominal Penyaluran (Rp)</option>
                        <option value="mustahik_count" {{ $tampilan === 'mustahik_count' ? 'selected' : '' }}>Jumlah Mustahik (Orang/Lembaga)</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-400 uppercase">Sumber Dana:</span>
                    <select name="dana_id" onchange="this.form.submit()" class="bg-white border border-slate-300 shadow-xs text-slate-700 text-xs font-semibold rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                        <option value="">Semua Jenis Dana</option>
                        @foreach($danas as $d)
                            <option value="{{ $d->id }}" {{ $filterDanaId == $d->id ? 'selected' : '' }}>{{ $d->kode }} - {{ $d->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Widgets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 print:hidden">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-indigo-500 uppercase">Total Dana Tersalurkan</span>
            <h4 class="text-2xl font-black text-indigo-700 mt-1">Rp {{ number_format($grandTotalNominal, 0, ',', '.') }}</h4>
            <p class="text-[11px] text-slate-400 mt-1">Akumulasi penyaluran seluruh program</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-emerald-500 uppercase">Total Mustahik Terlayani</span>
            <h4 class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($grandTotalCount) }} Mustahik</h4>
            <p class="text-[11px] text-slate-400 mt-1">Individu dan lembaga penerima manfaat</p>
        </div>
    </div>

    <!-- Matrix Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden print:border-none print:shadow-none">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-800 text-white font-bold uppercase text-[11px] tracking-wider">
                        <th class="py-3.5 px-4 border-r border-slate-700 min-w-[200px]" rowspan="2">PROGRAM BAZNAS</th>
                        <th class="py-2.5 px-4 text-center border-b border-slate-700" colspan="{{ count($asnafs) }}">KATEGORI ASNAF</th>
                        <th class="py-3.5 px-4 text-right border-l border-slate-700 min-w-[140px]" rowspan="2">TOTAL PROGRAM</th>
                    </tr>
                    <tr class="bg-slate-700 text-white font-semibold text-[10px] uppercase">
                        @foreach($asnafs as $asnaf)
                            <th class="py-2 px-3 text-center border-r border-slate-600 min-w-[110px]">{{ $asnaf->nama }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($matrix as $row)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-800 border-r border-slate-100 bg-slate-50/50">
                                {{ $row['program']->nama }}
                            </td>
                            @foreach($asnafs as $asnaf)
                                @php
                                    $val = $row['cells'][$asnaf->id];
                                @endphp
                                <td class="py-3 px-3 text-center border-r border-slate-100 font-medium {{ $val['nominal'] > 0 ? 'text-indigo-600 font-bold' : 'text-slate-400' }}">
                                    @if($tampilan === 'nominal')
                                        {{ $val['nominal'] > 0 ? 'Rp ' . number_format($val['nominal'], 0, ',', '.') : '-' }}
                                    @else
                                        {{ $val['count'] > 0 ? $val['count'] . ' Mustahik' : '-' }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="py-3 px-4 text-right font-black text-indigo-700 bg-indigo-50/30 border-l border-slate-100">
                                @if($tampilan === 'nominal')
                                    Rp {{ number_format($row['total_nominal'], 0, ',', '.') }}
                                @else
                                    {{ number_format($row['total_count']) }} Mustahik
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($asnafs) + 2 }}" class="py-6 text-center text-slate-400 italic">
                                Belum ada data program
                            </td>
                        </tr>
                    @endforelse

                    <!-- Rekap Asnaf Total Row -->
                    <tr class="bg-slate-900 text-white font-black text-xs uppercase tracking-wide border-t-2 border-slate-900">
                        <td class="py-3.5 px-4 border-r border-slate-800">
                            TOTAL ASNAF
                        </td>
                        @foreach($asnafs as $asnaf)
                            <td class="py-3.5 px-3 text-center border-r border-slate-800 text-amber-300">
                                @if($tampilan === 'nominal')
                                    Rp {{ number_format($totalPerAsnafNominal[$asnaf->id] ?? 0, 0, ',', '.') }}
                                @else
                                    {{ number_format($totalPerAsnafCount[$asnaf->id] ?? 0) }}
                                @endif
                            </td>
                        @endforeach
                        <td class="py-3.5 px-4 text-right bg-slate-950 text-white font-black">
                            @if($tampilan === 'nominal')
                                Rp {{ number_format($grandTotalNominal, 0, ',', '.') }}
                            @else
                                {{ number_format($grandTotalCount) }} Mustahik
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
