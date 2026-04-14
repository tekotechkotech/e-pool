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
            <a href="{{ route('manajemen.upz') }}" class="p-2 bg-white text-gray-600 hover:text-[#118243] hover:bg-[#eaf5ef] rounded-xl border border-slate-200 shadow-sm transition-all inline-flex items-center justify-center">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $upz->nama }}</h2>
                    @if($upz->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                            Nonaktif
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500">Detail informasi Unit Pengumpul Zakat (UPZ) dan data terkait.</p>
            </div>
        </div>

        <button type="button" @click="isPengurusModalOpen = true" class="bg-[#118243] hover:bg-[#0d6835] text-white font-bold py-2.5 px-4 rounded-xl shadow-sm flex items-center gap-2 text-sm transition-colors">
            <x-heroicon-o-plus class="w-4 h-4" />
            Tambah Pengurus UPZ
        </button>
    </div>

    <!-- Info Cards Section -->
    <div class="bg-white shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 sm:rounded-2xl p-6">
        <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
            <x-heroicon-o-information-circle class="w-5 h-5 text-[#118243]" />
            Informasi Umum UPZ
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-xs">
            <div class="space-y-1">
                <p class="font-medium text-gray-500 uppercase tracking-wider">Nama UPZ</p>
                <p class="font-semibold text-gray-900 text-sm">{{ $upz->nama }}</p>
            </div>
            <div class="space-y-1">
                <p class="font-medium text-gray-500 uppercase tracking-wider">Jenis UPZ</p>
                <p class="font-semibold text-gray-900">{{ $upz->jenis }}</p>
            </div>
            <div class="space-y-1">
                <p class="font-medium text-gray-500 uppercase tracking-wider">Ketua</p>
                <p class="font-semibold text-gray-900">{{ $upz->ketua_nama ?: '-' }}</p>
            </div>
            <div class="space-y-1">
                <p class="font-medium text-gray-500 uppercase tracking-wider">Kontak / WA</p>
                <p class="font-semibold text-gray-900">{{ $upz->handphone ?: $upz->telepon ?: '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Tabs Container -->
    @php
        $pengajuans = (class_exists(\App\Models\PengajuanBantuan::class) && method_exists($upz, 'pengajuanBantuans'))
            ? ($upz->pengajuanBantuans ?? collect())
            : collect();
    @endphp
    <div class="bg-white shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 sm:rounded-2xl overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px px-6 space-x-8">
                <button type="button" @click="activeTab = 'pengurus'" :class="activeTab === 'pengurus' ? 'border-[#118243] text-[#118243]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="py-4 px-1 border-b-2 font-bold text-sm transition-colors">
                    Daftar Pengurus ({{ $upz->penguruses->count() }})
                </button>
                <!-- <button type="button" @click="activeTab = 'pengajuan'" :class="activeTab === 'pengajuan' ? 'border-[#118243] text-[#118243]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="py-4 px-1 border-b-2 font-bold text-sm transition-colors">
                    Riwayat Pengajuan Rekomendasi ({{ $pengajuans->count() }})
                </button> -->
                <button type="button" @click="activeTab = 'mustahik'" :class="activeTab === 'mustahik' ? 'border-[#118243] text-[#118243]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="py-4 px-1 border-b-2 font-bold text-sm transition-colors">
                    Mustahik Binaan ({{ $upz->mustahiks->count() }})
                </button>
                <button type="button" @click="activeTab = 'muzakki'" :class="activeTab === 'muzakki' ? 'border-[#118243] text-[#118243]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="py-4 px-1 border-b-2 font-bold text-sm transition-colors">
                    Muzakki Terdaftar ({{ $muzakkis->total() }})
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
                            @forelse($upz->penguruses as $pengurus)
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
                                    <form method="POST" action="{{ route('manajemen.upz.pengurus.destroy', ['upzId' => $upz->id, 'pengurusId' => $pengurus->id]) }}" data-confirm="Yakin ingin menghapus pengurus ini?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-xs">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada pengurus ditambahkan pada UPZ ini.</td>
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
                                    @if(\Illuminate\Support\Facades\Route::has('pengajuan.upz.show'))
                                        <a href="{{ route('pengajuan.upz.show', $pengajuan->id) }}" class="text-[#118243] hover:underline font-bold">Detail &rarr;</a>
                                    @else
                                        <span class="text-gray-400 font-semibold">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada pengajuan rekomendasi dari UPZ ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mustahik Tab Content -->
            <div x-show="activeTab === 'mustahik'">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">NIK</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Nama Mustahik</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Pekerjaan</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Kontak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($upz->mustahiks as $m)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-900">{{ $m->person?->nik ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">{{ $m->person?->nama ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $m->pekerjaan ?: '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $m->person?->telepon ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada mustahik binaan terdaftar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Muzakki Tab Content -->
            <div x-show="activeTab === 'muzakki'" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <form method="GET" action="{{ route('manajemen.upz.detail', $upz->id) }}" class="flex items-center gap-2 max-w-sm w-full">
                        <input type="hidden" name="tab" value="muzakki">
                        <div class="relative w-full">
                            <input type="text" name="muzakki_search" value="{{ $muzakkiSearch ?? '' }}" placeholder="Cari Muzakki (NIK, Nama, NPWZ)..." class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] transition-all">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                        </div>
                        <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">Cari</button>
                        @if(!empty($muzakkiSearch))
                            <a href="{{ route('manajemen.upz.detail', ['id' => $upz->id, 'tab' => 'muzakki']) }}" class="px-3 py-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl text-xs font-semibold">Reset</a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">NPWZ / NRM</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Nama Muzakki</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">NIK</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Pekerjaan</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-500 uppercase">Kontak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($muzakkis as $mz)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 whitespace-nowrap font-mono font-semibold text-slate-900">{{ $mz->npwz ?: $mz->nrm ?: '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">{{ $mz->person?->nama ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-600">{{ $mz->person?->nik ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $mz->pekerjaan ?: '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $mz->person?->handphone ?: $mz->person?->telepon ?: '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada muzakki terdaftar pada UPZ ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($muzakkis->hasPages())
                <div class="pt-3">
                    {{ $muzakkis->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Form Tambah Pengurus UPZ -->
    <div x-show="isPengurusModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-gray-900">Tambah Pengurus UPZ</h3>
                <button type="button" @click="isPengurusModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('manajemen.upz.pengurus.store', $upz->id) }}" class="space-y-4 text-xs">
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
                    <label class="block font-bold text-gray-700 uppercase mb-1">Jabatan UPZ <span class="text-red-500">*</span></label>
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
