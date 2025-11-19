@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-blue-100 text-blue-700">UPZ</span>
                <h1 class="text-2xl font-bold text-slate-800">Pengajuan Bantuan UPZ</h1>
            </div>
            <p class="text-sm text-slate-500">Kelola pengajuan rekomendasi bantuan dan distribusi berbasis Unit Pengumpul Zakat.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('pengajuan.upz.import') }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                <svg class="w-4 h-4 mr-2 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Import Excel Mustahik UPZ
            </a>
            <a href="{{ route('pengajuan.upz.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#118243] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#0d6835] transition-all">
                <svg class="mr-2 -ml-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Pengajuan UPZ
            </a>
        </div>
    </div>

    <!-- Summary Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Total Pengajuan UPZ</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['total_pengajuan'] }}</p>
                <p class="text-[10px] text-slate-400 mt-1">Semua waktu</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Disetujui</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['total_disetujui'] }}</p>
                <p class="text-[10px] text-slate-400 mt-1">
                    {{ $stats['total_pengajuan'] > 0 ? round(($stats['total_disetujui'] / $stats['total_pengajuan']) * 100) : 0 }}% dari total
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Proses / Survey</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['total_proses'] }}</p>
                <p class="text-[10px] text-slate-400 mt-1">
                    {{ $stats['total_pengajuan'] > 0 ? round(($stats['total_proses'] / $stats['total_pengajuan']) * 100) : 0 }}% dari total
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Ditolak</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $stats['total_ditolak'] }}</p>
                <p class="text-[10px] text-slate-400 mt-1">
                    {{ $stats['total_pengajuan'] > 0 ? round(($stats['total_ditolak'] / $stats['total_pengajuan']) * 100) : 0 }}% dari total
                </p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Section -->
    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('pengajuan.upz.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="relative md:col-span-2">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    name="search" 
                    value="{{ $search }}" 
                    type="text" 
                    class="block w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-11 pr-4 text-sm placeholder-slate-400 shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all" 
                    placeholder="Cari nomor surat, nama UPZ, judul & Enter...">
            </div>

            <div>
                <select 
                    name="status" 
                    onchange="this.form.submit()"
                    class="block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-4 text-sm text-slate-600 shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif / Berjalan</option>
                    <option value="ditolak" {{ $status === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            <div>
                <select 
                    name="program_id" 
                    onchange="this.form.submit()"
                    class="block w-full rounded-xl border border-slate-300 bg-white py-2.5 px-4 text-sm text-slate-600 shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
                    <option value="">Semua Program</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ ($programId ?? $programFilter ?? '') == $prog->id ? 'selected' : '' }}>{{ $prog->nama }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Data Table Section -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor & Judul Pengajuan</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">UPZ Pengaju</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Program / Sub Program</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Mustahik Rekomendasi</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Disetujui</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pengajuans as $item)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs">
                                        {{ $loop->iteration + ($pengajuans->currentPage() - 1) * $pengajuans->perPage() }}
                                    </div>
                                    <div>
                                        <a href="{{ route('pengajuan.upz.show', $item) }}" class="text-xs font-bold text-slate-900 hover:text-[#118243] transition-colors line-clamp-1">
                                            {{ $item->nomor_pengajuan }}
                                        </a>
                                        <div class="text-xs text-slate-600 mt-0.5 line-clamp-1" title="{{ $item->judul_surat }}">
                                            {{ $item->judul_surat }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-1">
                                            Tgl Surat: {{ $item->tanggal_surat ? $item->tanggal_surat->format('d/m/Y') : '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold text-slate-800">
                                    {{ $item->upz?->nama ?? $item->lembaga?->nama ?? $item->lembaga_nama_snapshot ?? '-' }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    PIC: {{ $item->picUpz?->person?->nama ?? $item->pic_nama_snapshot ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $progs = $item->pengajuanMustahiks->map(fn($m) => $m->program?->nama)->filter()->unique();
                                    $subprogs = $item->pengajuanMustahiks->map(fn($m) => $m->subprogram?->nama)->filter()->unique();
                                @endphp
                                <div class="text-xs font-bold text-slate-900">
                                    @if($progs->count() > 1)
                                        Multi Program ({{ $progs->count() }})
                                    @else
                                        {{ $progs->first() ?? '-' }}
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    @if($subprogs->count() > 1)
                                        Multi Sub Program ({{ $subprogs->count() }})
                                    @else
                                        {{ $subprogs->first() ?? '-' }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                    {{ $item->pengajuanMustahiks->count() }} Orang
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $totalDisetujui = $item->persetujuan?->nominal_disetujui ?: $item->pengajuanMustahiks->sum('nominal');
                                @endphp
                                <div class="text-xs font-bold {{ $totalDisetujui > 0 ? 'text-[#118243]' : 'text-slate-400' }}">
                                    {{ $totalDisetujui > 0 ? 'Rp ' . number_format($totalDisetujui, 0, ',', '.') : '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $badge = $item->status_tahapan_badge;
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('pengajuan.upz.show', $item) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 text-xs font-bold rounded-lg transition-all">
                                    Detail
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Tidak ada data pengajuan bantuan UPZ yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pengajuans->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $pengajuans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
