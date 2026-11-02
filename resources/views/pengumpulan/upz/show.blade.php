@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800">{{ $setoran->nomor_setoran }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    {{ $setoran->status }}
                </span>
            </div>
            <p class="text-sm text-slate-500">Detail data setoran ZIS UPZ {{ $setoran->upz->nama ?? '-' }}.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pengumpulan.upz.cetak', $setoran->id) }}" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Lampiran Setoran
            </a>
            <a href="{{ route('pengumpulan.upz.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition-all">
                Kembali
            </a>
        </div>
    </div>

    <!-- Info Cards -->
    @php
        $totalMuzakki = $setoran->items->count();
        $setorCount = $setoran->items->where('jumlah', '>', 0)->count();
        $nihilCount = $setoran->items->where('jumlah', '<=', 0)->count();
        $partisipasi = $totalMuzakki > 0 ? round(($setorCount / $totalMuzakki) * 100) : 0;
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-1">
            <p class="text-xs text-slate-400 font-semibold uppercase">Nama UPZ</p>
            <p class="text-base font-bold text-slate-900">{{ $setoran->upz->nama ?? '-' }}</p>
            <p class="text-xs text-slate-400">Kode Simba: {{ $setoran->upz->kode_simba ?? '-' }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-1">
            <p class="text-xs text-slate-400 font-semibold uppercase">Tanggal Setor & Periode</p>
            <p class="text-base font-bold text-slate-900">{{ optional($setoran->tanggal_setor)->format('d F Y') }}</p>
            <p class="text-xs text-slate-400">Periode: {{ $setoran->periode }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-1">
            <p class="text-xs text-slate-400 font-semibold uppercase">Tingkat Partisipasi UPZ</p>
            <div class="flex items-center gap-2 mt-0.5">
                <span class="text-xl font-bold text-slate-900">{{ $partisipasi }}%</span>
                <span class="text-xs text-slate-500 font-medium">({{ $setorCount }} dari {{ $totalMuzakki }} setor)</span>
            </div>
            <p class="text-xs text-amber-600 font-medium">{{ $nihilCount }} orang nihil (Rp 0)</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-1">
            <p class="text-xs text-slate-400 font-semibold uppercase">Grand Total Setoran</p>
            <p class="text-xl font-black text-emerald-700">Rp {{ number_format($setoran->total_jumlah, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400">Z: Rp {{ number_format($setoran->total_zakat, 0, ',', '.') }} | I: Rp {{ number_format($setoran->total_infak, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-900">Rincian Daftar Muzakki & Setoran</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Nama Muzakki</th>
                        <th class="px-5 py-3">NPWZ</th>
                        <th class="px-5 py-3 text-right">Zakat</th>
                        <th class="px-5 py-3 text-right">Infak</th>
                        <th class="px-5 py-3 text-right">Fitrah</th>
                        <th class="px-5 py-3 text-right">Titipan</th>
                        <th class="px-5 py-3 text-right">Jumlah</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($setoran->items as $idx => $item)
                        <tr class="hover:bg-slate-50/50 {{ $item->jumlah <= 0 ? 'bg-slate-50/30' : '' }}">
                            <td class="px-5 py-3 font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $item->nama }}</td>
                            <td class="px-5 py-3 font-mono text-slate-500">{{ $item->npwz ?: '-' }}</td>
                            <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($item->nominal_zakat, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($item->nominal_infak, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($item->nominal_fitrah, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($item->nominal_titipan, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-mono font-bold {{ $item->jumlah > 0 ? 'text-emerald-700' : 'text-slate-400' }}">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($item->jumlah > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Setor
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">
                                        Nihil (Rp 0)
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $item->keterangan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-slate-400">Tidak ada rincian item.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-100 text-xs text-slate-900">
                    <tr>
                        <td colspan="3" class="px-5 py-3 text-right">TOTAL:</td>
                        <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($setoran->total_zakat, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($setoran->total_infak, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($setoran->total_fitrah, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($setoran->total_titipan, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono font-black text-emerald-700">Rp {{ number_format($setoran->total_jumlah, 0, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
