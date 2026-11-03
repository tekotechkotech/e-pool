@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    isOpen: {{ $errors->any() ? 'true' : 'false' }},
    isEditing: {{ old('mustahik_id') ? 'true' : 'false' }},
    isDetailOpen: false,
    mustahik_id: {{ json_encode(old('mustahik_id')) }},
    person_id: {{ json_encode(old('person_id')) }},
    nik: {{ json_encode((string)old('nik', '')) }},
    nomor_kk: {{ json_encode((string)old('nomor_kk', '')) }},
    nama: {{ json_encode((string)old('nama', '')) }},
    jenis_kelamin: {{ json_encode((string)old('jenis_kelamin', 'L')) }},
    tempat_lahir: {{ json_encode((string)old('tempat_lahir', '')) }},
    tanggal_lahir: {{ json_encode((string)old('tanggal_lahir', '')) }},
    telepon: {{ json_encode((string)old('telepon', '')) }},
    pekerjaan: {{ json_encode((string)old('pekerjaan', '')) }},
    nim: {{ json_encode((string)old('nim', '')) }},
    keterangan: {{ json_encode((string)old('keterangan', '')) }},
    is_new_person: {{ json_encode(old('is_new_person', 1)) }},
    detailMustahik: null,
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
        this.mustahik_id = null;
        this.person_id = null;
        this.nik = '';
        this.nomor_kk = '';
        this.nama = '';
        this.jenis_kelamin = 'L';
        this.tempat_lahir = '';
        this.tanggal_lahir = '';
        this.telepon = '';
        this.pekerjaan = '';
        this.nim = '';
        this.keterangan = '';
        this.is_new_person = 1;
        this.wilayah.setValues({
            alamat: '',
            id_provinsi: '',
            id_kabupaten: '',
            id_kecamatan: '',
            id_kelurahan: '',
            kode_pos: ''
        });
        this.isEditing = false;
        this.isOpen = true;
    },

    openEdit(m) {
        this.mustahik_id = m.id;
        this.person_id = m.person_id;
        this.pekerjaan = m.pekerjaan || '';
        this.nim = m.nim || '';
        this.keterangan = m.keterangan || '';
        if (m.person) {
            this.nik = m.person.nik || '';
            this.nomor_kk = m.person.nomor_kk || '';
            this.nama = m.person.nama || '';
            this.jenis_kelamin = m.person.jenis_kelamin || 'L';
            this.tempat_lahir = m.person.tempat_lahir || '';
            this.tanggal_lahir = m.person.tanggal_lahir ? m.person.tanggal_lahir.substring(0, 10) : '';
            this.telepon = m.person.telepon || m.person.handphone || '';
            this.wilayah.setValues({
                alamat: m.person.alamat || '',
                id_provinsi: m.person.id_provinsi || '',
                id_kabupaten: m.person.id_kabupaten || '',
                id_kecamatan: m.person.id_kecamatan || '',
                id_kelurahan: m.person.id_kelurahan || '',
                kode_pos: m.person.kode_pos || ''
            });
        }
        this.isEditing = true;
        this.isOpen = true;
    },

    openDetail(m) {
        this.detailMustahik = m;
        this.isDetailOpen = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Data Mustahik</h2>
            <p class="mt-1 text-sm text-gray-500">Kelola basis data individu penerima manfaat (mustahik).</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('manajemen.mustahik.template') }}" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-2 text-sm transition-colors">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-emerald-600" />
                Download Template
            </a>
            <button type="button" @click="openCreate()" class="bg-[#118243] hover:bg-[#0d6835] text-white font-bold py-2.5 px-4 rounded-xl shadow-sm flex items-center gap-2 text-sm transition-colors">
                <x-heroicon-o-plus class="w-4 h-4" />
                Tambah Mustahik
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 sm:rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-4 bg-slate-50/50">
            <form method="GET" action="{{ route('manajemen.mustahik') }}" class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                <input type="text" name="search" value="{{ $search }}" class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] sm:text-sm shadow-xs transition-all" placeholder="Cari NIK atau Nama Mustahik...">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NIK / No. KK</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Mustahik</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pekerjaan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">UPZ Terdaftar</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($mustahiks as $mustahik)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $mustahik->person?->nik ?? '-' }}</div>
                            <div class="text-xs text-gray-500">KK: {{ $mustahik->person?->nomor_kk ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ $mustahik->person?->nama ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $mustahik->person?->telepon ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $mustahik->pekerjaan ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            @if($mustahik->upzs && $mustahik->upzs->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($mustahik->upzs as $upz)
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-md border border-blue-200 font-semibold">{{ $upz->nama }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400">Individu (Reguler)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="openDetail({{ $mustahik->toJson() }})" class="p-2 text-[#118243] bg-[#eaf5ef] hover:bg-[#118243] hover:text-white rounded-lg transition-all" title="Detail Mustahik">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </button>
                                <button type="button" @click="openEdit({{ $mustahik->toJson() }})" class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all" title="Edit Mustahik">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </button>
                                 <form method="POST" action="{{ route('manajemen.mustahik.destroy', $mustahik->id) }}" data-confirm="Yakin ingin menghapus data mustahik ini?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all" title="Hapus Mustahik">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                            Belum ada data mustahik terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mustahiks->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $mustahiks->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Form Create / Edit Mustahik -->
    <div x-show="isOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-slate-200 my-8">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-lg text-gray-900" x-text="isEditing ? 'Edit Data Mustahik' : 'Tambah Mustahik Baru'"></h3>
                <button type="button" @click="isOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('manajemen.mustahik.store') }}">
                @csrf
                <input type="hidden" name="mustahik_id" x-model="mustahik_id">
                <input type="hidden" name="person_id" x-model="person_id">
                <input type="hidden" name="is_new_person" x-model="is_new_person">

                @if($errors->any())
                    <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700">
                        <p class="font-semibold mb-1">Periksa kembali data yang Anda masukkan:</p>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-4 text-xs">
                    <x-form.person-fields
                        alpine-model="true"
                        :show-nomor-kk="true"
                        :show-pekerjaan="true"
                        :show-ttl="true"
                        label-telepon="No. Kontak / WA"
                        label-nama="Nama Lengkap"
                        grid-class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                    >
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin</label>
                            <select name="jenis_kelamin" x-model="jenis_kelamin" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error('jenis_kelamin') border-rose-500 focus:ring-rose-500/20 @enderror">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">NIM (Nomor Induk Mustahik)</label>
                            <input type="text" name="nim" x-model="nim" placeholder="Kosongkan jika tidak ada NIM" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error('nim') border-rose-500 focus:ring-rose-500/20 @enderror">
                            @error('nim')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
                        </div>
                    </x-form.person-fields>

                    <!-- Alamat & Wilayah -->
                    <x-form.wilayah-select
                        :standalone="false"
                        alpine-scope="wilayah"
                        title="Informasi Alamat & Wilayah"
                        grid-class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                    />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="isOpen = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#118243] text-white rounded-xl text-xs font-semibold hover:bg-[#0d6835]">Simpan Mustahik</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Mustahik -->
    <div x-show="isDetailOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center border-gray-200 pb-3 border-b">
                <h3 class="text-base font-bold text-gray-900">Detail Data Mustahik</h3>
                <button type="button" @click="isDetailOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>
            
            <template x-if="detailMustahik">
                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-xl">
                        <div>
                            <span class="text-slate-400 uppercase font-bold block">NIK</span>
                            <span class="font-bold text-slate-800 text-sm" x-text="detailMustahik.person ? detailMustahik.person.nik : '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 uppercase font-bold block">Nama Lengkap</span>
                            <span class="font-bold text-slate-800 text-sm" x-text="detailMustahik.person ? detailMustahik.person.nama : '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 uppercase font-bold block">Pekerjaan</span>
                            <span class="font-semibold text-slate-700" x-text="detailMustahik.pekerjaan || '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 uppercase font-bold block">Kontak</span>
                            <span class="font-semibold text-slate-700" x-text="detailMustahik.person ? (detailMustahik.person.telepon || detailMustahik.person.handphone || '-') : '-'"></span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-400 uppercase font-bold block">Alamat Lengkap & Wilayah</span>
                            <span class="text-slate-700 font-medium" x-text="detailMustahik.person ? [
                                detailMustahik.person.alamat,
                                detailMustahik.person.kelurahan ? 'Desa/Kel. ' + detailMustahik.person.kelurahan.nama : null,
                                detailMustahik.person.kecamatan ? 'Kec. ' + detailMustahik.person.kecamatan.nama : null,
                                detailMustahik.person.kabupaten ? detailMustahik.person.kabupaten.nama : null,
                                detailMustahik.person.provinsi ? 'Prov. ' + detailMustahik.person.provinsi.nama : null,
                                detailMustahik.person.kode_pos ? detailMustahik.person.kode_pos : null
                            ].filter(Boolean).join(', ') : '-'"></span>
                        </div>
                    </div>
                </div>
            </template>

            <div class="flex justify-end pt-3 border-t border-gray-100">
                <button type="button" @click="isDetailOpen = false" class="px-4 py-2 bg-white border border-gray-300 text-xs font-semibold rounded-xl text-gray-700 hover:bg-gray-50">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
