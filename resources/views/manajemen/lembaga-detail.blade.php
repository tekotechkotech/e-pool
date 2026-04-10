@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ $activeTab }}',
    isPengurusModalOpen: {{ $errors->any() ? 'true' : 'false' }},
    person_id: '{{ old('person_id', '') }}',
    pengurus_jabatan_id: '{{ old('pengurus_jabatan_id', '') }}',
    no_sk: '{{ old('no_sk', '') }}',
    tanggal_mulai: '{{ old('tanggal_mulai', date('Y-m-d')) }}',
    tanggal_selesai: '{{ old('tanggal_selesai', '') }}',
    is_active: true
}">
    <!-- Header with Back Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('manajemen.lembaga') }}" class="p-2 bg-white text-gray-600 hover:text-[#118243] hover:bg-[#eaf5ef] rounded-xl border border-slate-200 shadow-sm transition-all inline-flex items-center justify-center">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $lembaga->nama }}</h2>
                    @if($lembaga->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                            Nonaktif
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500">Detail informasi Lembaga dan riwayat pengajuan bantuan.</p>
            </div>
        </div>

        <button type="button" @click="isPengurusModalOpen = true" class="bg-[#118243] hover:bg-[#0d6835] text-white font-bold py-2.5 px-4 rounded-xl shadow-sm flex items-center gap-2 text-sm transition-colors">
            <x-heroicon-o-plus class="w-4 h-4" />
            Tambah Pengurus Lembaga
        </button>
    </div>

    <!-- Info Cards Section -->
    <div class="bg-white shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 sm:rounded-2xl p-6">
        <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
            <x-heroicon-o-information-circle class="w-5 h-5 text-[#118243]" />
            Informasi Lembaga
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-xs">
            <div class="space-y-1">
                <p class="font-medium text-gray-500 uppercase tracking-wider">Nama Lembaga</p>
                <p class="font-semibold text-gray-900 text-sm">{{ $lembaga->nama }}</p>
            </div>
            <div class="space-y-1">
                <p class="font-medium text-gray-500 uppercase tracking-wider">Jenis Lembaga</p>
                <p class="font-semibold text-gray-900">{{ $lembaga->jenis }}</p>
            </div>
            <div class="space-y-1">
                <p class="font-medium text-gray-500 uppercase tracking-wider">Pimpinan / Ketua</p>
                <p class="font-semibold text-gray-900">{{ $lembaga->ketua_nama ?: '-' }}</p>
            </div>
            <div class="space-y-1">
                <p class="font-medium text-gray-500 uppercase tracking-wider">Kontak</p>
                <p class="font-semibold text-gray-900">{{ $lembaga->handphone ?: $lembaga->telepon ?: '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Tabs Container -->
    @php
        $pengajuans = (class_exists(\App\Models\PengajuanBantuan::class) && method_exists($lembaga, 'pengajuanBantuans'))
            ? ($lembaga->pengajuanBantuans ?? collect())
            : collect();
    @endphp
    <div class="bg-white shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 sm:rounded-2xl overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px px-6 space-x-8">
                <button type="button" @click="activeTab = 'pengurus'" :class="activeTab === 'pengurus' ? 'border-[#118243] text-[#118243]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="py-4 px-1 border-b-2 font-bold text-sm transition-colors">
                    Daftar Pengurus ({{ $lembaga->penguruses->count() }})
                </button>
                <button type="button" @click="activeTab = 'pengajuan'" :class="activeTab === 'pengajuan' ? 'border-[#118243] text-[#118243]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="py-4 px-1 border-b-2 font-bold text-sm transition-colors">
                    Riwayat Pengajuan Bantuan ({{ $pengajuans->count() }})
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Pengurus Tab Content -->
            <div x-show="activeTab === 'pengurus'">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Nama Lengkap</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Jabatan</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">No SK</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Masa Berlaku</th>
                                <th class="px-6 py-3 text-center font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($lembaga->penguruses as $pengurus)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900 text-sm">{{ $pengurus->person?->nama ?? '-' }}</div>
                                    <div class="text-gray-500">NIK: {{ $pengurus->person?->nik ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-md font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ $pengurus->pengurusJabatan?->nama ?? 'Pengurus' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-mono">
                                    {{ $pengurus->no_sk ?: '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    {{ $pengurus->tanggal_mulai ? $pengurus->tanggal_mulai->format('d/m/Y') : '-' }} s/d {{ $pengurus->tanggal_selesai ? $pengurus->tanggal_selesai->format('d/m/Y') : 'Sekarang' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($pengurus->is_active)
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-green-100 text-green-700 rounded-full">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-600 rounded-full">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <form method="POST" action="{{ route('manajemen.lembaga.pengurus.destroy', ['lembagaId' => $lembaga->id, 'pengurusId' => $pengurus->id]) }}" data-confirm="Yakin ingin menghapus pengurus ini?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-xs">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada pengurus ditambahkan pada lembaga ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pengajuan Tab Content -->
            <div x-show="activeTab === 'pengajuan'">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">No. Pengajuan</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Program</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Judul / Uraian</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Tgl Diterima</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($pengajuans as $pengajuan)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-[#118243]">
                                    {{ $pengajuan->nomor_pengajuan ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">{{ $pengajuan->program?->nama ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $pengajuan->subprogram?->nama ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ $pengajuan->judul_surat ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                    {{ !empty($pengajuan->tanggal_diterima) ? date('d/m/Y', strtotime($pengajuan->tanggal_diterima)) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    @if(\Illuminate\Support\Facades\Route::has('pengajuan.umum.show'))
                                        <a href="{{ route('pengajuan.umum.show', $pengajuan->id) }}" class="text-[#118243] hover:underline font-bold">Detail &rarr;</a>
                                    @else
                                        <span class="text-gray-400 font-semibold">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada permohonan bantuan dari lembaga ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Tambah Pengurus Lembaga -->
    <div x-show="isPengurusModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-gray-900">Tambah Pengurus Lembaga</h3>
                <button type="button" @click="isPengurusModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('manajemen.lembaga.pengurus.store', $lembaga->id) }}" class="space-y-4 text-xs">
                @csrf

                @if($errors->any())
                    <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700">
                        <p class="font-semibold mb-1">Periksa kembali data yang Anda masukkan:</p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Pilih Individu <span class="text-red-500">*</span></label>
                    <select name="person_id" x-model="person_id" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all @error('person_id') border-rose-500 focus:ring-rose-500/20 @enderror">
                        <option value="">-- Pilih Individu --</option>
                        @foreach($persons as $person)
                            <option value="{{ $person->id }}">{{ $person->nik }} - {{ $person->nama }}</option>
                        @endforeach
                    </select>
                    @error('person_id')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Jabatan Lembaga <span class="text-red-500">*</span></label>
                    <select name="pengurus_jabatan_id" x-model="pengurus_jabatan_id" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all @error('pengurus_jabatan_id') border-rose-500 focus:ring-rose-500/20 @enderror">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}">{{ $jabatan->nama }}</option>
                        @endforeach
                    </select>
                    @error('pengurus_jabatan_id')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Nomor SK</label>
                    <input type="text" name="no_sk" x-model="no_sk" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all @error('no_sk') border-rose-500 focus:ring-rose-500/20 @enderror">
                    @error('no_sk')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" x-model="tanggal_mulai" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all @error('tanggal_mulai') border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('tanggal_mulai')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" x-model="tanggal_selesai" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all @error('tanggal_selesai') border-rose-500 focus:ring-rose-500/20 @enderror">
                        @error('tanggal_selesai')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="isPengurusModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
