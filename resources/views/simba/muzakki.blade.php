@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-sky-100 text-sky-700">Simba Export</span>
                <h1 class="text-2xl font-bold text-slate-800">Export Simba: Data Muzakki</h1>
            </div>
            <p class="text-sm text-slate-500">Preview & Unduh file CSV format resmi Simba BAZNAS (Template Migrasi Muzaki Perorangan).</p>
        </div>
        
        <div>
            <a href="{{ request()->fullUrlWithQuery(['download' => 'csv']) }}" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-sky-700 transition-all">
                <x-heroicon-o-arrow-down-tray class="mr-2 -ml-1 h-5 w-5" />
                Download CSV Simba
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
        <form method="GET" action="{{ route('simba.muzakki') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Filter UPZ</label>
                <select name="lembaga_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs">
                    <option value="">-- Semua UPZ --</option>
                    @foreach($upzs as $u)
                        <option value="{{ $u->id }}" {{ $lembagaId == $u->id ? 'selected' : '' }}>{{ $u->nama }} (Kode: {{ $u->kode_simba ?? '-' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tgl Registrasi Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tgl Registrasi Selesai</label>
                <input type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl text-xs transition-all">Filter</button>
                <a href="{{ route('simba.muzakki') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs transition-all">Reset</a>
            </div>
        </form>
    </div>

    <!-- Preview Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Preview Kolom Export CSV Simba</span>
            <span class="text-xs text-slate-500 font-mono">Total: {{ $muzakkis->total() }} data</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 whitespace-nowrap">
                <thead class="bg-slate-100/70 text-slate-700 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Tanggal Registrasi</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">NPWP</th>
                        <th class="px-4 py-3">NIK</th>
                        <th class="px-4 py-3">NIP</th>
                        <th class="px-4 py-3">Tempat Lahir</th>
                        <th class="px-4 py-3">tanggal lahir</th>
                        <th class="px-4 py-3">jenis kelamin</th>
                        <th class="px-4 py-3">pekerjaan</th>
                        <th class="px-4 py-3">alamat rumah</th>
                        <th class="px-4 py-3">telepon</th>
                        <th class="px-4 py-3">handphone</th>
                        <th class="px-4 py-3">UPZ (Kode)</th>
                        <th class="px-4 py-3">zakat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($muzakkis as $m)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2.5">{{ optional($m->tanggal_registrasi)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2.5 font-bold font-sans text-slate-900">{{ $m->person->nama ?? '-' }}</td>
                            <td class="px-4 py-2.5">{{ $m->npwp ?? '-' }}</td>
                            <td class="px-4 py-2.5">{{ $m->person->nik ?? '-' }}</td>
                            <td class="px-4 py-2.5">{{ $m->nip ?? '-' }}</td>
                            <td class="px-4 py-2.5 font-sans">{{ $m->person->tempat_lahir ?? '-' }}</td>
                            <td class="px-4 py-2.5">{{ optional($m->person->tanggal_lahir)->format('d/m/Y') }}</td>
                            <td class="px-4 py-2.5 font-sans">{{ $m->person->jenis_kelamin == 'L' ? 'Pria' : ($m->person->jenis_kelamin == 'P' ? 'Wanita' : '-') }}</td>
                            <td class="px-4 py-2.5 font-sans">{{ $m->pekerjaan ?? '-' }}</td>
                            <td class="px-4 py-2.5 font-sans max-w-xs truncate">{{ $m->person->alamat ?? '-' }}</td>
                            <td class="px-4 py-2.5">{{ $m->person->telepon ?? '-' }}</td>
                            <td class="px-4 py-2.5">{{ $m->person->handphone ?? '-' }}</td>
                            <td class="px-4 py-2.5 font-bold text-sky-700">{{ $m->upz->kode_simba ?? '-' }}</td>
                            <td class="px-4 py-2.5 font-sans">{{ $m->jenis_zakat ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-4 py-8 text-center text-slate-400 font-sans">
                                Tidak ada data muzakki untuk preview.
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
