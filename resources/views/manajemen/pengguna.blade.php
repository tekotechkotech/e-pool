@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    isOpen: false,
    isDetailOpen: false,
    isEditing: false,
    person_id: null,
    nik: '',
    nomor_kk: '',
    nama: '',
    jenis_kelamin: 'L',
    tempat_lahir: '',
    tanggal_lahir: '',
    telepon: '',
    handphone: '',
    email: '',
    detailPerson: null,
    wilayah: wilayahSelector(),

    async init() {
        await this.wilayah.init();
    },

    openCreate() {
        this.person_id = null;
        this.nik = '';
        this.nomor_kk = '';
        this.nama = '';
        this.jenis_kelamin = 'L';
        this.tempat_lahir = '';
        this.tanggal_lahir = '';
        this.telepon = '';
        this.handphone = '';
        this.email = '';
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

    openEdit(p) {
        this.person_id = p.id;
        this.nik = p.nik || '';
        this.nomor_kk = p.nomor_kk || '';
        this.nama = p.nama || '';
        this.jenis_kelamin = p.jenis_kelamin || 'L';
        this.tempat_lahir = p.tempat_lahir || '';
        this.tanggal_lahir = p.tanggal_lahir ? p.tanggal_lahir.substring(0, 10) : '';
        this.telepon = p.telepon || '';
        this.handphone = p.handphone || '';
        this.email = p.email || '';
        this.wilayah.setValues({
            alamat: p.alamat || '',
            id_provinsi: p.id_provinsi || '',
            id_kabupaten: p.id_kabupaten || '',
            id_kecamatan: p.id_kecamatan || '',
            id_kelurahan: p.id_kelurahan || '',
            kode_pos: p.kode_pos || ''
        });
        this.isEditing = true;
        this.isOpen = true;
    },

    openDetail(p) {
        this.detailPerson = p;
        this.isDetailOpen = true;
    }
}">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Data Person</h2>
            <p class="mt-1 text-sm text-gray-500">Kelola data individu pengguna aplikasi.</p>
        </div>
        <button type="button" @click="openCreate()" class="bg-[#118243] hover:bg-[#0d6835] text-white font-bold py-2.5 px-4 rounded-xl shadow-sm flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Person
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-[0_8px_30px_rgba(0,0,0,0.03)] border border-slate-100 sm:rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center gap-4 bg-slate-50/50">
            <form method="GET" action="{{ route('manajemen.pengguna') }}" class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-xl leading-5 bg-white placeholder-gray-500 shadow-xs focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] sm:text-sm transition-all" placeholder="Cari NIK atau Nama...">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NIK / No. KK</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">L/P</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($persons as $person)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $person->nik ?? '-' }}</div>
                            <div class="text-xs text-gray-500">KK: {{ $person->nomor_kk ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ $person->nama }}</div>
                            <div class="text-xs text-gray-500">{{ $person->tempat_lahir }} {{ $person->tanggal_lahir ? ', '.$person->tanggal_lahir->format('d/m/Y') : '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $person->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $person->handphone ?: $person->telepon ?: '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $person->email ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="openDetail({{ $person->toJson() }})" class="p-2 text-[#118243] bg-[#eaf5ef] hover:bg-[#118243] hover:text-white rounded-lg transition-all duration-200" title="Detail Person">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <button type="button" @click="openEdit({{ $person->toJson() }})" class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-lg transition-all duration-200" title="Edit Person">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                 <form method="POST" action="{{ route('manajemen.pengguna.destroy', $person->id) }}" data-confirm="Yakin ingin menghapus data person ini?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all duration-200" title="Hapus Person">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center">
                            <p class="text-sm text-gray-500">Belum ada data person.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($persons->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $persons->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Create / Edit Person -->
    <div x-show="isOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-slate-200 my-8">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-lg text-gray-900" x-text="isEditing ? 'Edit Data Person' : 'Tambah Person Baru'"></h3>
                <button type="button" @click="isOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('manajemen.pengguna.store') }}">
                @csrf
                <input type="hidden" name="person_id" x-model="person_id">
                <div class="space-y-4 text-xs">
                    <x-form.person-fields
                        alpine-model="true"
                        :show-nomor-kk="true"
                        :show-pekerjaan="false"
                        :show-ttl="true"
                        :show-email="true"
                        label-nama="Nama Lengkap"
                        label-telepon="No. Telepon Rumah"
                        grid-class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                    >
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin</label>
                            <select name="jenis_kelamin" x-model="jenis_kelamin" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">No. Handphone / WA</label>
                            <input type="text" name="handphone" x-model="handphone" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
                        </div>
                    </x-form.person-fields>

                    <!-- Alamat & Wilayah Form -->
                    <x-form.wilayah-select
                        :standalone="false"
                        alpine-scope="wilayah"
                        title="Informasi Alamat & Wilayah"
                        grid-class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                    />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="isOpen = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#118243] text-white rounded-xl text-xs font-semibold hover:bg-[#0d6835]">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail Person -->
    <div x-show="isDetailOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-5 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center border-b border-gray-200 pb-3">
                <h3 class="text-base font-bold text-gray-900">Detail Data Person</h3>
                <button type="button" @click="isDetailOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <template x-if="detailPerson">
                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-xl">
                        <div>
                            <span class="text-slate-400 uppercase font-bold block">NIK</span>
                            <span class="font-bold text-slate-800 text-sm" x-text="detailPerson.nik || '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 uppercase font-bold block">Nomor KK</span>
                            <span class="font-bold text-slate-800 text-sm" x-text="detailPerson.nomor_kk || '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 uppercase font-bold block">Nama Lengkap</span>
                            <span class="font-bold text-slate-800 text-sm" x-text="detailPerson.nama"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 uppercase font-bold block">Kontak / HP</span>
                            <span class="font-semibold text-slate-700" x-text="detailPerson.handphone || detailPerson.telepon || '-'"></span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-slate-400 uppercase font-bold block">Alamat Lengkap & Wilayah</span>
                            <span class="text-slate-700 font-medium" x-text="[
                                detailPerson.alamat,
                                detailPerson.kelurahan ? 'Desa/Kel. ' + detailPerson.kelurahan.nama : null,
                                detailPerson.kecamatan ? 'Kec. ' + detailPerson.kecamatan.nama : null,
                                detailPerson.kabupaten ? detailPerson.kabupaten.nama : null,
                                detailPerson.provinsi ? 'Prov. ' + detailPerson.provinsi.nama : null,
                                detailPerson.kode_pos ? detailPerson.kode_pos : null
                            ].filter(Boolean).join(', ') || '-'"></span>
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
