@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700">Pengumpulan</span>
                <h1 class="text-2xl font-bold text-slate-800">Data Muzakki & Munfiq</h1>
            </div>
            <p class="text-sm text-slate-500">Daftar muzakki perorangan binaan Unit Pengumpul Zakat (UPZ) maupun langsung.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('muzakki.template') }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-slate-200 px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Template CSV
            </a>
            <a href="{{ route('muzakki.import') }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-slate-200 px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                <svg class="w-4 h-4 mr-2 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Import CSV UPZ
            </a>
            <a href="{{ route('muzakki.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#118243] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#0d6835] transition-all">
                <svg class="mr-2 -ml-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Muzakki
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('muzakki.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, NIK, NPWZ..." 
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

            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl text-sm transition-all">Filter</button>
                <a href="{{ route('muzakki.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm transition-all">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Muzakki</th>
                        <th class="px-6 py-4">Identitas (NIK/NPWZ)</th>
                        <th class="px-6 py-4">UPZ</th>
                        <th class="px-6 py-4">Pekerjaan / Alamat</th>
                        <th class="px-6 py-4">Jenis Zakat</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($muzakkis as $index => $m)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 text-xs font-mono text-slate-400">
                                {{ $muzakkis->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $m->person->nama ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $m->person->jenis_kelamin == 'L' ? 'Pria' : ($m->person->jenis_kelamin == 'P' ? 'Wanita' : '-') }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs space-y-0.5">
                                <div><span class="text-slate-400">NIK:</span> <span class="font-mono font-medium text-slate-700">{{ $m->person->nik ?? '-' }}</span></div>
                                <div><span class="text-slate-400">NPWZ:</span> <span class="font-mono text-emerald-600">{{ $m->npwz ?? '-' }}</span></div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-medium">
                                    {{ $m->upz->nama ?? 'Langsung' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div class="font-medium text-slate-800">{{ $m->pekerjaan ?? '-' }}</div>
                                <div class="text-slate-400 truncate max-w-xs">{{ $m->alamat_korespondensi ?: ($m->person->alamat ?? '-') }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <span class="font-medium text-slate-700">{{ $m->jenis_zakat ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('muzakki.edit', $m->id) }}" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('muzakki.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus muzakki ini?');" class="inline">
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
                                Tidak ada data muzakki.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($muzakkis->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $muzakkis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
