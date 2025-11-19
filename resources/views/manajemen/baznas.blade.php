@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    isModalOpen: false,
    isInfoModalOpen: false,
    isEditing: false,
    pengurus_id: null,
    person_id: '',
    pengurus_jabatan_id: '',
    no_sk: '',
    tanggal_mulai: '',
    tanggal_selesai: '',
    is_active: true,

    openCreatePengurus() {
        this.pengurus_id = null;
        this.person_id = '';
        this.pengurus_jabatan_id = '';
        this.no_sk = '';
        this.tanggal_mulai = '{{ date('Y-m-d') }}';
        this.tanggal_selesai = '';
        this.is_active = true;
        this.isEditing = false;
        this.isModalOpen = true;
    },

    openEditPengurus(p) {
        this.pengurus_id = p.id;
        this.person_id = p.person_id;
        this.pengurus_jabatan_id = p.pengurus_jabatan_id;
        this.no_sk = p.no_sk || '';
        this.tanggal_mulai = p.tanggal_mulai ? p.tanggal_mulai.substring(0, 10) : '';
        this.tanggal_selesai = p.tanggal_selesai ? p.tanggal_selesai.substring(0, 10) : '';
        this.is_active = !!p.is_active;
        this.isEditing = true;
        this.isModalOpen = true;
    }
}">
    <!-- Profile & Pengurus BAZNAS Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Manajemen BAZNAS</h2>
            <p class="mt-1 text-sm text-gray-500">Kelola profil lembaga BAZNAS dan susunan pimpinan/staf.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" @click="isInfoModalOpen = true" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-2 text-sm transition-colors">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Informasi BAZNAS
            </button>
            <button type="button" @click="openCreatePengurus()" class="bg-[#118243] hover:bg-[#0d6835] text-white font-bold py-2.5 px-4 rounded-xl shadow-sm flex items-center gap-2 text-sm transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Pengurus BAZNAS
            </button>
        </div>
    </div>

    <!-- Info BAZNAS Card -->
    <div class="bg-white shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 sm:rounded-2xl p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
            <div>
                <span class="text-slate-400 uppercase font-bold block mb-1">Nama Organisasi</span>
                <p class="text-sm font-bold text-slate-800">{{ $info['nama_baznas'] ?? 'BADAN AMIL ZAKAT NASIONAL' }}</p>
                <p class="text-slate-500 mt-1">
                    {{ $info['alamat'] ?? '-' }}
                    @php
                        $loc = array_filter([$info['desa_kelurahan'] ?? null, $info['kecamatan'] ?? null, $info['kabupaten'] ?? null, $info['provinsi'] ?? null, $info['kode_pos'] ?? null]);
                    @endphp
                    @if(count($loc))
                        <br><span class="text-[11px] text-slate-400 font-medium">{{ implode(', ', $loc) }}</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-bold block mb-1">Kontak Resmi</span>
                <p class="text-slate-700 font-semibold">Email: {{ $info['email'] ?? '-' }}</p>
                <p class="text-slate-700 font-semibold mt-0.5">Telp: {{ $info['telepon'] ?? '-' }}</p>
                <p class="text-slate-700 font-semibold mt-0.5">WA: {{ $info['whatsapp'] ?? '-' }}</p>
            </div>
            <div>
                <span class="text-slate-400 uppercase font-bold block mb-1">Visi & Misi Singkat</span>
                <p class="text-slate-600 line-clamp-3 italic">{{ $info['visi'] ?? 'Menjadi lembaga utama menyejahterakan umat.' }}</p>
            </div>
        </div>
    </div>

    <!-- Table Pengurus BAZNAS -->
    <div class="bg-white shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 sm:rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-4 bg-slate-50/50">
            <form method="GET" action="{{ route('manajemen.baznas') }}" class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] sm:text-sm shadow-xs transition-all" placeholder="Cari Nama Pengurus BAZNAS...">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Pengurus</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jabatan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor SK</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Masa Tugas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($penguruses as $p)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">{{ $p->person?->nama ?? '-' }}</div>
                            <div class="text-xs text-gray-500">NIK: {{ $p->person?->nik ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2.5 py-1 text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg">
                                {{ $p->pengurusJabatan?->nama ?? 'Pengurus' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600">
                            {{ $p->no_sk ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                            {{ $p->tanggal_mulai ? $p->tanggal_mulai->format('d/m/Y') : '-' }} s/d {{ $p->tanggal_selesai ? $p->tanggal_selesai->format('d/m/Y') : 'Sekarang' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($p->is_active)
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-green-100 text-green-700 rounded-full">Aktif</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-600 rounded-full">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="openEditPengurus({{ $p->toJson() }})" class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                 <form method="POST" action="{{ route('manajemen.baznas.pengurus.destroy', $p->id) }}" data-confirm="Yakin ingin menghapus pengurus ini?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada pengurus BAZNAS terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($penguruses->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $penguruses->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Form Pengurus BAZNAS -->
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-gray-900" x-text="isEditing ? 'Edit Pengurus BAZNAS' : 'Tambah Pengurus BAZNAS'"></h3>
                <button type="button" @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('manajemen.baznas.pengurus.store') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="pengurus_id" x-model="pengurus_id">

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Pilih Person / Individu <span class="text-red-500">*</span></label>
                    <select name="person_id" x-model="person_id" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        <option value="">-- Pilih Individu --</option>
                        @foreach($persons as $person)
                            <option value="{{ $person->id }}">{{ $person->nik }} - {{ $person->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Jabatan BAZNAS <span class="text-red-500">*</span></label>
                    <select name="pengurus_jabatan_id" x-model="pengurus_jabatan_id" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatans as $jabatan)
                            <option value="{{ $jabatan->id }}">{{ $jabatan->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 uppercase mb-1">Nomor SK Pengangkatan</label>
                    <input type="text" name="no_sk" x-model="no_sk" placeholder="Contoh: SK/001/BAZNAS/2026" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" x-model="tanggal_mulai" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" x-model="tanggal_selesai" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" x-model="is_active" class="rounded text-[#118243]">
                        <span>Status Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="isModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form Info BAZNAS -->
    <div x-show="isInfoModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-gray-900">Edit Profil Informasi BAZNAS</h3>
                <button type="button" @click="isInfoModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('manajemen.baznas.info') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-gray-700 uppercase mb-1">Nama Organisasi BAZNAS</label>
                        <input type="text" name="nama_baznas" value="{{ $info['nama_baznas'] ?? 'BADAN AMIL ZAKAT NASIONAL' }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Email Resmi</label>
                        <input type="email" name="email" value="{{ $info['email'] ?? '' }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">No. Telepon</label>
                        <input type="text" name="telepon" value="{{ $info['telepon'] ?? '' }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">WhatsApp</label>
                        <input type="text" name="whatsapp" value="{{ $info['whatsapp'] ?? '' }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Website</label>
                        <input type="text" name="website" value="{{ $info['website'] ?? '' }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>
                    <!-- Alamat & Wilayah BAZNAS -->
                    <div class="sm:col-span-2">
                        <x-form.wilayah-select
                            :provinsis="$provinsis ?? []"
                            :provinsi="$info['id_provinsi'] ?? ''"
                            :kabupaten="$info['id_kabupaten'] ?? ''"
                            :kecamatan="$info['id_kecamatan'] ?? ''"
                            :kelurahan="$info['id_kelurahan'] ?? ''"
                            :kode-pos="$info['kode_pos'] ?? ''"
                            :alamat="$info['alamat'] ?? ''"
                            title="Lokasi & Alamat Kantor BAZNAS"
                            grid-class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-gray-700 uppercase mb-1">Visi</label>
                        <textarea name="visi" rows="2" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">{{ $info['visi'] ?? '' }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-gray-700 uppercase mb-1">Misi</label>
                        <textarea name="misi" rows="2" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">{{ $info['misi'] ?? '' }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="isInfoModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl shadow-sm">Simpan Informasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
