@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Data Keluarga</h2>
            <p class="mt-1 text-sm text-gray-500">Daftar pengguna yang dikelompokkan berdasarkan Nomor KK.</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 sm:rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-4 bg-slate-50/50">
            <form method="GET" action="{{ route('manajemen.keluarga') }}" class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                <input type="text" name="search" value="{{ $search }}" class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-xl leading-5 bg-white placeholder-gray-500 shadow-xs focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] sm:text-sm transition-all" placeholder="Cari Nomor KK...">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor KK</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Anggota Terdaftar</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($keluargas as $keluarga)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ $keluarga->nomor_kk }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#eaf5ef] text-[#118243]">
                                {{ $keluarga->jumlah_anggota }} Orang
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('manajemen.keluarga', ['kk' => $keluarga->nomor_kk]) }}" class="inline-flex items-center p-2 text-[#118243] bg-[#eaf5ef] hover:bg-[#118243] hover:text-white rounded-lg transition-all" title="Lihat Anggota Keluarga">
                                <x-heroicon-o-eye class="w-4 h-4" />
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada data keluarga tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($keluargas->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $keluargas->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Anggota Keluarga -->
    @if($selectedKk && count($anggotaKeluarga) > 0)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-3xl w-full p-6 space-y-5 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center border-b border-gray-200 pb-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Daftar Anggota Keluarga (KK: {{ $selectedKk }})</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar individu yang terhubung dengan kartu keluarga ini</p>
                </div>
                <a href="{{ route('manajemen.keluarga') }}" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-bold text-gray-500 uppercase">NIK</th>
                            <th class="px-4 py-2.5 text-left font-bold text-gray-500 uppercase">Nama Lengkap</th>
                            <th class="px-4 py-2.5 text-left font-bold text-gray-500 uppercase">L/P</th>
                            <th class="px-4 py-2.5 text-left font-bold text-gray-500 uppercase">Kontak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($anggotaKeluarga as $anggota)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $anggota->nik }}</td>
                            <td class="px-4 py-3 font-bold text-gray-800">{{ $anggota->nama }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $anggota->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $anggota->handphone ?: $anggota->telepon ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end pt-3 border-t border-gray-100">
                <a href="{{ route('manajemen.keluarga') }}" class="px-4 py-2 bg-white border border-gray-300 text-xs font-semibold rounded-xl text-gray-700 hover:bg-gray-50">Tutup</a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
