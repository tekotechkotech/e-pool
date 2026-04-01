@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    isFormOpen: {{ $errors->any() ? 'true' : 'false' }},
    isEditing: {{ old('lembaga_id') ? 'true' : 'false' }},
    lembaga_id: {{ json_encode(old('lembaga_id')) }},
    nama: {{ json_encode((string)old('nama', '')) }},
    ketua_nama: {{ json_encode((string)old('ketua_nama', '')) }},
    ketua_nik: {{ json_encode((string)old('ketua_nik', '')) }},
    jenis: {{ json_encode((string)old('jenis', 'Masjid')) }},
    telepon: {{ json_encode((string)old('telepon', '')) }},
    handphone: {{ json_encode((string)old('handphone', '')) }},
    email: {{ json_encode((string)old('email', '')) }},
    is_active: {{ json_encode(old('is_active', '1') == '1') }},
    wilayah: wilayahSelector({
        alamat: {{ json_encode((string)old('alamat', '')) }},
        id_provinsi: {{ json_encode((string)old('id_provinsi', '')) }},
        id_kabupaten: {{ json_encode((string)old('id_kabupaten', '')) }},
        id_kecamatan: {{ json_encode((string)old('id_kecamatan', '')) }},
        id_kelurahan: {{ json_encode((string)old('id_kelurahan', '')) }},
        kode_pos: {{ json_encode((string)old('kode_pos', '')) }}
    }),

    async init() {
        await this.wilayah.init();
    },

    openCreate() {
        this.lembaga_id = null;
        this.nama = '';
        this.ketua_nama = '';
        this.ketua_nik = '';
        this.jenis = 'Masjid';
        this.telepon = '';
        this.handphone = '';
        this.email = '';
        this.is_active = true;
        this.wilayah.setValues({
            alamat: '',
            id_provinsi: '',
            id_kabupaten: '',
            id_kecamatan: '',
            id_kelurahan: '',
            kode_pos: ''
        });
        this.isEditing = false;
        this.isFormOpen = true;
    },

    openEdit(l) {
        this.lembaga_id = l.id;
        this.nama = l.nama;
        this.ketua_nama = l.ketua_nama || '';
        this.ketua_nik = l.ketua_nik || '';
        this.jenis = l.jenis || 'Masjid';
        this.telepon = l.telepon || '';
        this.handphone = l.handphone || l.telepon || '';
        this.email = l.email || '';
        this.is_active = !!l.is_active;
        this.wilayah.setValues({
            alamat: l.alamat || '',
            id_provinsi: l.id_provinsi || '',
            id_kabupaten: l.id_kabupaten || '',
            id_kecamatan: l.id_kecamatan || '',
            id_kelurahan: l.id_kelurahan || '',
            kode_pos: l.kode_pos || ''
        });
        this.isEditing = true;
        this.isFormOpen = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Data Lembaga & Yayasan</h2>
            <p class="mt-1 text-sm text-gray-500">Kelola data lembaga pemohon bantuan (masjid, musholla, pesantren, yayasan non-UPZ).</p>
        </div>
        <button type="button" @click="openCreate()" class="bg-[#118243] hover:bg-[#0d6835] text-white font-bold py-2.5 px-4 rounded-xl shadow-sm flex items-center gap-2 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Lembaga Baru
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 sm:rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-4 bg-slate-50/50">
            <form method="GET" action="{{ route('manajemen.lembaga') }}" class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] sm:text-sm shadow-xs transition-all" placeholder="Cari Nama Lembaga, Ketua, atau Jenis...">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Lembaga</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Lembaga</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ketua / Kontak</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($lembagas as $lembaga)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">{{ $lembaga->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $lembaga->penguruses_count }} Anggota Pengurus</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 rounded-md">
                                {{ $lembaga->jenis }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-semibold">{{ $lembaga->ketua_nama ?: '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $lembaga->handphone ?: $lembaga->telepon ?: '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($lembaga->is_active)
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-green-100 text-green-700 rounded-full">Aktif</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-700 rounded-full">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('manajemen.lembaga.detail', $lembaga->id) }}" class="p-2 text-[#118243] bg-[#eaf5ef] hover:bg-[#118243] hover:text-white rounded-lg transition-all" title="Detail Lembaga">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <button type="button" @click="openEdit({{ $lembaga->toJson() }})" class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all" title="Edit Lembaga">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                 <form method="POST" action="{{ route('manajemen.lembaga.destroy', $lembaga->id) }}" data-confirm="Yakin ingin menghapus data lembaga ini?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all" title="Hapus Lembaga">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada data lembaga terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($lembagas->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $lembagas->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Form Create / Edit Lembaga -->
    <div x-show="isFormOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-slate-200 my-8">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-gray-900" x-text="isEditing ? 'Edit Data Lembaga' : 'Tambah Lembaga Baru'"></h3>
                <button type="button" @click="isFormOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('manajemen.lembaga.store') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="lembaga_id" x-model="lembaga_id">

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

                <x-form.lembaga-fields
                    alpine-model="true"
                    label-nama="Nama Lembaga"
                    placeholder-nama="Contoh: Yayasan Darussalam Cilacap"
                    :full-width-nama="true"
                    :show-jenis="true"
                    :jenis-options="[
                        'Masjid' => 'Masjid',
                        'Musholla' => 'Musholla',
                        'Pesantren' => 'Pesantren',
                        'Madrasah / Sekolah' => 'Madrasah / Sekolah',
                        'Panti Asuhan' => 'Panti Asuhan',
                        'Yayasan Sosial' => 'Yayasan Sosial',
                        'Organisasi Kemasyarakatan' => 'Organisasi Kemasyarakatan',
                    ]"
                    :show-ketua="true"
                    :show-ketua-nik="false"
                    label-ketua="Nama Ketua / Pimpinan"
                    name-telepon="handphone"
                    :show-email="true"
                    grid-class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                />

                <!-- Alamat & Wilayah -->
                <x-form.wilayah-select
                    :standalone="false"
                    alpine-scope="wilayah"
                    title="Informasi Alamat & Lokasi Wilayah"
                    grid-class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                />

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" x-model="is_active" class="rounded text-[#118243]">
                        <span>Status Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="isFormOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
