@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header (Hidden on print) -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 print:hidden">
        <div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                <span class="p-2 rounded-xl {{ $jenisTransaksi === 'Masuk' ? 'bg-emerald-100 text-emerald-700' : ($jenisTransaksi === 'Keluar' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                    @if($jenisTransaksi === 'Masuk')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    @elseif($jenisTransaksi === 'Keluar')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    @endif
                </span>
                @if($jenisTransaksi === 'Masuk')
                    Buku Kas Penerimaan Dana
                @elseif($jenisTransaksi === 'Keluar')
                    Buku Kas Penyaluran Dana
                @else
                    Buku Kas & Rekening Koran Dana
                @endif
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                @if($jenisTransaksi === 'Masuk')
                    Buku register mutasi kas masuk / penghimpunan dana
                @elseif($jenisTransaksi === 'Keluar')
                    Buku register mutasi kas keluar / pendistribusian dana
                @else
                    Riwayat mutasi debet/kredit dan saldo berjalan per akun jenis dana
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Buku Kas
            </button>
        </div>
    </div>

    <!-- 3 Segmented Pills: Switch Jenis Buku Kas (Hidden on print) -->
    <div class="bg-slate-200/80 p-1.5 rounded-2xl flex flex-wrap sm:flex-nowrap gap-1.5 print:hidden shadow-inner">
        <a href="{{ route('laporan.buku-kas', array_merge(request()->query(), ['jenis_transaksi' => 'all'])) }}" class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all {{ $jenisTransaksi === 'all' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <svg class="w-4 h-4 {{ $jenisTransaksi === 'all' ? 'text-amber-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <span>Semua Transaksi (Masuk & Keluar)</span>
        </a>

        <a href="{{ route('laporan.buku-kas', array_merge(request()->query(), ['jenis_transaksi' => 'Masuk'])) }}" class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all {{ $jenisTransaksi === 'Masuk' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <svg class="w-4 h-4 {{ $jenisTransaksi === 'Masuk' ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
            <span>Hanya Penerimaan (Masuk)</span>
        </a>

        <a href="{{ route('laporan.buku-kas', array_merge(request()->query(), ['jenis_transaksi' => 'Keluar'])) }}" class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all {{ $jenisTransaksi === 'Keluar' ? 'bg-white text-rose-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
            <svg class="w-4 h-4 {{ $jenisTransaksi === 'Keluar' ? 'text-rose-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span>Hanya Penyaluran (Keluar)</span>
        </a>
    </div>

    <!-- Filter Bar (Hidden on print) -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm space-y-4 print:hidden">
        <form method="GET" action="{{ route('laporan.buku-kas') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="hidden" name="jenis_transaksi" value="{{ $jenisTransaksi }}">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1.5">Pilih Akun Dana</label>
                <select name="dana_id" onchange="this.form.submit()" class="w-full bg-white border border-slate-300 shadow-xs text-slate-700 text-xs font-semibold rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                    <option value="">-- Semua Jenis Dana --</option>
                    @foreach($danas as $d)
                        <option value="{{ $d->id }}" {{ $selectedDanaId == $d->id ? 'selected' : '' }}>{{ $d->kode }} - {{ $d->nama }} ({{ $d->kategori }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1.5">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" onchange="this.form.submit()" class="w-full border border-slate-300 bg-white shadow-xs rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1.5">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" onchange="this.form.submit()" class="w-full border border-slate-300 bg-white shadow-xs rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1.5">Cari Uraian / Catatan</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik kata kunci & Enter..." class="w-full border border-slate-300 bg-white shadow-xs rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
            </div>
        </form>
    </div>

    <!-- Summary Widgets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 print:hidden">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase">Saldo Awal Periode</span>
            <h4 class="text-xl font-black text-slate-800 mt-1">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</h4>
            <p class="text-[11px] text-slate-400 mt-1">Per sebelum {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-emerald-500 uppercase">+ Total Penerimaan</span>
            <h4 class="text-xl font-black text-emerald-600 mt-1">Rp {{ number_format($totalMasukPeriode, 0, ',', '.') }}</h4>
            <p class="text-[11px] text-slate-400 mt-1">Penerimaan kas periode ini</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <span class="text-xs font-bold text-rose-500 uppercase">- Total Penyaluran</span>
            <h4 class="text-xl font-black text-rose-600 mt-1">Rp {{ number_format($totalKeluarPeriode, 0, ',', '.') }}</h4>
            <p class="text-[11px] text-slate-400 mt-1">Penyaluran dana periode ini</p>
        </div>

        <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-5 rounded-2xl shadow-sm text-white">
            <span class="text-xs font-bold text-slate-300 uppercase">= Saldo Akhir Berjalan</span>
            <h4 class="text-xl font-black text-emerald-400 mt-1">Rp {{ number_format($saldoAkhirPeriode, 0, ',', '.') }}</h4>
            <p class="text-[11px] text-slate-400 mt-1">Posisi kas saat ini</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden print:border-none print:shadow-none">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 uppercase text-[11px] tracking-wider">
                        <th class="py-3 px-4 w-28 text-center border-r border-slate-200">TANGGAL</th>
                        <th class="py-3 px-4 w-40 border-r border-slate-200">AKUN DANA</th>
                        <th class="py-3 px-6 border-r border-slate-200">URAIAN TRANSAKSI</th>
                        @if($jenisTransaksi === 'all' || $jenisTransaksi === 'Masuk')
                            <th class="py-3 px-4 text-right border-r border-slate-200 w-40">PENERIMAAN (D)</th>
                        @endif
                        @if($jenisTransaksi === 'all' || $jenisTransaksi === 'Keluar')
                            <th class="py-3 px-4 text-right border-r border-slate-200 w-40">PENYALURAN (K)</th>
                        @endif
                        <th class="py-3 px-4 text-right w-44">SALDO BERJALAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="bg-slate-50 font-bold text-slate-700">
                        <td class="py-2.5 px-4 text-center border-r border-slate-100">-</td>
                        <td class="py-2.5 px-4 border-r border-slate-100">-</td>
                        <td class="py-2.5 px-6 border-r border-slate-100 font-bold italic">
                            SALDO AWAL PERIODE (SEBELUM {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }})
                        </td>
                        @if($jenisTransaksi === 'all' || $jenisTransaksi === 'Masuk')
                            <td class="py-2.5 px-4 text-right border-r border-slate-100">-</td>
                        @endif
                        @if($jenisTransaksi === 'all' || $jenisTransaksi === 'Keluar')
                            <td class="py-2.5 px-4 text-right border-r border-slate-100">-</td>
                        @endif
                        <td class="py-2.5 px-4 text-right font-black text-slate-900">
                            Rp {{ number_format($saldoAwal, 0, ',', '.') }}
                        </td>
                    </tr>

                    @forelse($allPeriodTransaksis as $t)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-4 text-center text-slate-600 border-r border-slate-100">
                                {{ $t->tanggal ? $t->tanggal->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-2.5 px-4 font-semibold text-slate-800 border-r border-slate-100">
                                <span class="font-bold text-indigo-600">{{ $t->dana?->kode }}</span> - {{ $t->dana?->nama }}
                            </td>
                            <td class="py-2.5 px-6 text-slate-700 border-r border-slate-100">
                                <div>{{ $t->uraian ?: '-' }}</div>
                                @if($t->referensi)
                                    <span class="text-[10px] text-slate-400">Ref: {{ class_basename($t->referensi_type) }} #{{ $t->referensi_id }}</span>
                                @endif
                            </td>
                            @if($jenisTransaksi === 'all' || $jenisTransaksi === 'Masuk')
                                <td class="py-2.5 px-4 text-right font-semibold text-emerald-600 border-r border-slate-100">
                                    {{ $t->jenis === 'Masuk' ? 'Rp ' . number_format($t->nominal, 0, ',', '.') : '-' }}
                                </td>
                            @endif
                            @if($jenisTransaksi === 'all' || $jenisTransaksi === 'Keluar')
                                <td class="py-2.5 px-4 text-right font-semibold text-rose-600 border-r border-slate-100">
                                    {{ $t->jenis === 'Keluar' ? 'Rp ' . number_format($t->nominal, 0, ',', '.') : '-' }}
                                </td>
                            @endif
                            <td class="py-2.5 px-4 text-right font-bold text-slate-800">
                                Rp {{ number_format($runningBalances[$t->id] ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        @php $emptyColspan = ($jenisTransaksi === 'all') ? 6 : 5; @endphp
                        <tr>
                            <td colspan="{{ $emptyColspan }}" class="py-8 text-center text-slate-400 italic">
                                Tidak ada transaksi pada periode ini
                            </td>
                        </tr>
                    @endforelse

                    <!-- TOTAL REKAPITULASI ROW -->
                    <tr class="bg-slate-900 text-white font-black text-xs uppercase tracking-wide border-t-2 border-slate-900">
                        <td colspan="3" class="py-3.5 px-6 text-right tracking-wider border-r border-slate-800">
                            TOTAL MUTASI & SALDO AKHIR
                        </td>
                        @if($jenisTransaksi === 'all' || $jenisTransaksi === 'Masuk')
                            <td class="py-3.5 px-4 text-right text-emerald-400 border-r border-slate-800">
                                Rp {{ number_format($totalMasukPeriode, 0, ',', '.') }}
                            </td>
                        @endif
                        @if($jenisTransaksi === 'all' || $jenisTransaksi === 'Keluar')
                            <td class="py-3.5 px-4 text-right text-rose-300 border-r border-slate-800">
                                Rp {{ number_format($totalKeluarPeriode, 0, ',', '.') }}
                            </td>
                        @endif
                        <td class="py-3.5 px-4 text-right text-white text-sm bg-slate-950">
                            Rp {{ number_format($saldoAkhirPeriode, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
