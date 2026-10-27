@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header (Hidden on print) -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 print:hidden">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                <span class="p-2 rounded-xl {{ $mode === 'masuk' ? 'bg-emerald-100 text-emerald-700' : ($mode === 'keluar' ? 'bg-rose-100 text-rose-700' : 'bg-indigo-100 text-indigo-700') }}">
                    @if($mode === 'masuk')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    @elseif($mode === 'keluar')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    @endif
                </span>
                @if($mode === 'masuk')
                    Laporan Penerimaan / Penghimpunan Dana
                @elseif($mode === 'keluar')
                    Laporan Penyaluran / Pendistribusian Dana
                @else
                    Laporan Realisasi Jenis Dana
                @endif
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                @if($mode === 'masuk')
                    Rekapitulasi total dana masuk dari muzakki, donatur, korporasi, dan UPZ
                @elseif($mode === 'keluar')
                    Rekapitulasi total dana keluar untuk program bantuan dan pendayagunaan
                @else
                    Rekapitulasi penerimaan, penyaluran, dan saldo dana berdasarkan format standar 18 akun BAZNAS
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('laporan.realisasi-dana.export', request()->query()) }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Ekspor Excel
            </a>
            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Laporan
            </button>
            <a href="{{ route('master.danas') }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                Kelola Dana
            </a>
        </div>
    </div>

    <!-- 3 Segmented Pills: Switch Jenis Laporan -->
    <div class="bg-slate-200/80 p-1.5 rounded-2xl flex flex-wrap sm:flex-nowrap gap-1.5 print:hidden shadow-inner">
        <a href="{{ route('laporan.realisasi-dana', array_merge(request()->query(), ['mode' => 'semua'])) }}" class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all {{ $mode === 'semua' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <svg class="w-4 h-4 {{ $mode === 'semua' ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Semua (Masuk, Keluar & Saldo)</span>
        </a>

        <a href="{{ route('laporan.realisasi-dana', array_merge(request()->query(), ['mode' => 'masuk'])) }}" class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all {{ $mode === 'masuk' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <svg class="w-4 h-4 {{ $mode === 'masuk' ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            <span>Hanya Penerimaan (Masuk)</span>
        </a>

        <a href="{{ route('laporan.realisasi-dana', array_merge(request()->query(), ['mode' => 'keluar'])) }}" class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all {{ $mode === 'keluar' ? 'bg-white text-rose-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <svg class="w-4 h-4 {{ $mode === 'keluar' ? 'text-rose-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span>Hanya Penyaluran (Keluar)</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm space-y-4 print:hidden">
        <form method="GET" action="{{ route('laporan.realisasi-dana') }}" class="flex flex-wrap items-center justify-between gap-4">
            <input type="hidden" name="mode" value="{{ $mode }}">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase mr-1">Periode:</span>
                <a href="{{ route('laporan.realisasi-dana', array_merge(request()->query(), ['periode_tipe' => 'all'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodeTipe === 'all' ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Waktu
                </a>
                <a href="{{ route('laporan.realisasi-dana', array_merge(request()->query(), ['periode_tipe' => 'this_month'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodeTipe === 'this_month' ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Bulan Ini
                </a>
                <a href="{{ route('laporan.realisasi-dana', array_merge(request()->query(), ['periode_tipe' => 'this_year'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $periodeTipe === 'this_year' ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Tahun Ini
                </a>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-400 uppercase">Sumber:</span>
                <select name="filter_sumber" onchange="this.form.submit()" class="bg-white border border-slate-300 shadow-xs text-slate-700 text-xs font-semibold rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-400 transition-all">
                    <option value="all" {{ $filterSumber === 'all' ? 'selected' : '' }}>Semua Sumber</option>
                    <option value="langsung" {{ $filterSumber === 'langsung' ? 'selected' : '' }}>Hanya Langsung</option>
                    <option value="upz" {{ $filterSumber === 'upz' ? 'selected' : '' }}>Hanya Via UPZ</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Widgets Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 print:hidden">
        @if($mode === 'semua' || $mode === 'masuk')
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-emerald-50 opacity-60"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Penerimaan (Masuk)</p>
            <h3 class="text-2xl font-black text-emerald-600">Rp {{ number_format($grandTotalMasuk, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-400 mt-2">Akumulasi penghimpunan dana</p>
        </div>
        @endif

        @if($mode === 'semua' || $mode === 'keluar')
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-rose-50 opacity-60"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Penyaluran (Keluar)</p>
            <h3 class="text-2xl font-black text-rose-600">Rp {{ number_format($grandTotalKeluar, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-400 mt-2">Realisasi pendistribusian program</p>
        </div>
        @endif

        @if($mode === 'semua')
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-indigo-50 opacity-60"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Saldo Akhir Bersih</p>
            <h3 class="text-2xl font-black text-indigo-600">Rp {{ number_format($grandTotalSaldo, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-slate-400 mt-2">Sisa saldo dana berjalan</p>
        </div>
        @endif
    </div>

    <!-- Table of 18 Accounts -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden print:border-none print:shadow-none">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 uppercase text-[11px] tracking-wider">
                        <th class="py-3 px-4 w-20 text-center border-r border-slate-200">KODE</th>
                        <th class="py-3 px-6 border-r border-slate-200">URAIAN AKUN DANA</th>
                        @if($mode === 'semua' || $mode === 'masuk')
                            <th class="py-3 px-4 text-right border-r border-slate-200 w-44">PENERIMAAN (MASUK)</th>
                        @endif
                        @if($mode === 'semua' || $mode === 'keluar')
                            <th class="py-3 px-4 text-right border-r border-slate-200 w-44">PENYALURAN (KELUAR)</th>
                        @endif
                        @if($mode === 'semua')
                            <th class="py-3 px-4 text-right w-44">SALDO AKHIR</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($groupedData as $catKey => $group)
                        <!-- Subheader Kategori -->
                        <tr class="bg-slate-50 font-bold text-slate-800">
                            <td class="py-2.5 px-4 text-center border-r border-slate-200">#</td>
                            <td class="py-2.5 px-6 border-r border-slate-200 text-indigo-900 uppercase font-black tracking-wider text-xs">
                                {{ $group['label'] }}
                            </td>
                            @if($mode === 'semua' || $mode === 'masuk')
                                <td class="py-2.5 px-4 text-right border-r border-slate-200 text-emerald-700 font-bold">
                                    Rp {{ number_format($group['total_masuk'], 0, ',', '.') }}
                                </td>
                            @endif
                            @if($mode === 'semua' || $mode === 'keluar')
                                <td class="py-2.5 px-4 text-right border-r border-slate-200 text-rose-700 font-bold">
                                    Rp {{ number_format($group['total_keluar'], 0, ',', '.') }}
                                </td>
                            @endif
                            @if($mode === 'semua')
                                <td class="py-2.5 px-4 text-right text-slate-900 font-black">
                                    Rp {{ number_format($group['total_saldo'], 0, ',', '.') }}
                                </td>
                            @endif
                        </tr>

                        <!-- Item Akun -->
                        @foreach($group['items'] as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-2 px-4 text-center text-slate-500 font-mono font-bold border-r border-slate-100">
                                {{ $item['kode'] }}
                            </td>
                            <td class="py-2 px-6 border-r border-slate-100 text-slate-700 font-medium">
                                <span>{{ $item['nama'] }}</span>
                                @if($item['via_upz'])
                                    <span class="ml-2 text-[10px] bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded border border-blue-200 font-semibold">UPZ</span>
                                @endif
                            </td>
                            @if($mode === 'semua' || $mode === 'masuk')
                                <td class="py-2 px-4 text-right text-emerald-600 font-semibold border-r border-slate-100">
                                    Rp {{ number_format($item['masuk'], 0, ',', '.') }}
                                </td>
                            @endif
                            @if($mode === 'semua' || $mode === 'keluar')
                                <td class="py-2 px-4 text-right text-rose-600 font-semibold border-r border-slate-100">
                                    Rp {{ number_format($item['keluar'], 0, ',', '.') }}
                                </td>
                            @endif
                            @if($mode === 'semua')
                                <td class="py-2 px-4 text-right text-slate-800 font-bold">
                                    Rp {{ number_format($item['saldo'], 0, ',', '.') }}
                                </td>
                            @endif
                        </tr>
                        @endforeach
                    @endforeach

                    <!-- Grand Total Row -->
                    <tr class="bg-slate-900 text-white font-black text-xs uppercase tracking-wide border-t-2 border-slate-900">
                        <td colspan="2" class="py-3.5 px-6 text-right tracking-wider border-r border-slate-800">
                            GRAND TOTAL REALISASI DANA
                        </td>
                        @if($mode === 'semua' || $mode === 'masuk')
                            <td class="py-3.5 px-4 text-right text-emerald-400 border-r border-slate-800">
                                Rp {{ number_format($grandTotalMasuk, 0, ',', '.') }}
                            </td>
                        @endif
                        @if($mode === 'semua' || $mode === 'keluar')
                            <td class="py-3.5 px-4 text-right text-rose-300 border-r border-slate-800">
                                Rp {{ number_format($grandTotalKeluar, 0, ',', '.') }}
                            </td>
                        @endif
                        @if($mode === 'semua')
                            <td class="py-3.5 px-4 text-right text-white text-sm bg-slate-950">
                                Rp {{ number_format($grandTotalSaldo, 0, ',', '.') }}
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
