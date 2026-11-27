@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    isDanaModalOpen: false,
    isDanaEditing: false,
    dana_id: null,
    kode: '',
    nama: '',
    kategori: 'Zakat',
    via_upz: false,
    urutan: 0,
    keterangan: '',
    is_active: true,

    openCreateDana() {
        this.dana_id = null;
        this.kode = '';
        this.nama = '';
        this.kategori = 'Zakat';
        this.via_upz = false;
        this.urutan = 0;
        this.keterangan = '';
        this.is_active = true;
        this.isDanaEditing = false;
        this.isDanaModalOpen = true;
    },

    openEditDana(d) {
        this.dana_id = d.id;
        this.kode = d.kode;
        this.nama = d.nama;
        this.kategori = d.kategori;
        this.via_upz = !!d.via_upz;
        this.urutan = d.urutan;
        this.keterangan = d.keterangan || '';
        this.is_active = !!d.is_active;
        this.isDanaEditing = true;
        this.isDanaModalOpen = true;
    }
}">
    <!-- Header Banner -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <x-heroicon-o-archive-box class="w-7 h-7 text-[#118243]" />
                <span>Master Jenis Dana</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Kelola daftar akun, kode, dan klasifikasi jenis dana ZIS BAZNAS.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" @click="openCreateDana()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#118243] hover:bg-[#0e6b36] text-white text-sm font-semibold shadow-sm transition-all">
                <x-heroicon-o-plus class="w-4 h-4" />
                <span>Tambah Jenis Dana</span>
            </button>
        </div>
    </div>

    <!-- Master Info Cards (Pure Master Metrics: Total, Aktif, Via UPZ) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Total Jenis Dana</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $widgets['totalDana'] ?? 0 }}</h3>
                <span class="text-xs text-slate-400">Terdaftar di sistem</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <x-heroicon-o-folder class="w-6 h-6" />
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Dana Aktif</p>
                <h3 class="text-2xl font-bold text-emerald-600">{{ $widgets['totalDanaAktif'] ?? 0 }}</h3>
                <span class="text-xs text-emerald-600 font-medium">Siap digunakan transaksi</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <x-heroicon-o-check-circle class="w-6 h-6" />
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Dihimpun via UPZ</p>
                <h3 class="text-2xl font-bold text-blue-600">{{ $widgets['totalViaUpz'] ?? 0 }}</h3>
                <span class="text-xs text-blue-600 font-medium">Masuk lembar setor UPZ</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <x-heroicon-o-building-office-2 class="w-6 h-6" />
            </div>
        </div>
    </div>

    <!-- Master Dana Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <!-- Search & Filter Bar -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-slate-50/50">
            <h2 class="text-base font-bold text-slate-800">Daftar Jenis Dana BAZNAS</h2>
            <form method="GET" action="{{ route('master.danas') }}" class="w-full sm:w-72 relative">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari kode atau nama dana..." class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="px-5 py-3.5 w-16 text-center">Urutan</th>
                        <th class="px-5 py-3.5 w-24">Kode</th>
                        <th class="px-5 py-3.5">Nama Jenis Dana</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5 text-center">Via UPZ</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5">Keterangan</th>
                        <th class="px-5 py-3.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($danas as $dana)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-3.5 text-center font-semibold text-slate-400">{{ $dana->urutan ?? 0 }}</td>
                        <td class="px-5 py-3.5 font-bold text-slate-700 font-mono">{{ $dana->kode }}</td>
                        <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $dana->nama }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold text-[11px] bg-slate-100 text-slate-700">
                                {{ $dana->kategori }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($dana->via_upz)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold text-[11px] bg-blue-50 text-blue-700 border border-blue-200">
                                    Ya
                                </span>
                            @else
                                <span class="text-slate-400 font-medium">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($dana->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-semibold text-[11px] bg-slate-100 text-slate-500">
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 max-w-xs truncate">{{ $dana->keterangan ?: '-' }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" @click="openEditDana({{ $dana->toJson() }})" class="p-1.5 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </button>
                                <form method="POST" action="{{ route('master.danas.destroy', $dana->id) }}" data-confirm="Yakin ingin menghapus jenis dana ini?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-8 text-center text-slate-400 italic">Belum ada data jenis dana terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Dana (Create / Edit) -->
    <div x-show="isDanaModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" @keydown.escape.window="isDanaModalOpen = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-200" @click.outside="isDanaModalOpen = false">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-slate-800" x-text="isDanaEditing ? 'Edit Jenis Dana' : 'Tambah Jenis Dana Baru'"></h3>
                <button type="button" @click="isDanaModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('master.danas.store') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="dana_id" x-model="dana_id">

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Kode Akun (COA) <span class="text-red-500">*</span></label>
                    <input type="text" name="kode" x-model="kode" required placeholder="Contoh: 2.1" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Nama Akun Dana <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" x-model="nama" required placeholder="Contoh: Zakat Maal Perorangan" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Kategori Dana <span class="text-red-500">*</span></label>
                        <select name="kategori" x-model="kategori" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                            <option value="Zakat">Zakat</option>
                            <option value="Infak/Sedekah">Infak/Sedekah</option>
                            <option value="CSR">CSR</option>
                            <option value="DSKL">DSKL</option>
                            <option value="Dana Titipan">Dana Titipan</option>
                            <option value="Non Halal">Non Halal</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Urutan Tampil</label>
                        <input type="number" name="urutan" x-model="urutan" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>
                </div>

                <div class="flex items-center gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-700">
                        <input type="checkbox" name="via_upz" value="1" x-model="via_upz" class="rounded text-[#118243] focus:ring-[#118243]">
                        <span>Dihimpun via UPZ</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-700">
                        <input type="checkbox" name="is_active" value="1" x-model="is_active" class="rounded text-[#118243] focus:ring-[#118243]">
                        <span>Status Aktif</span>
                    </label>
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Keterangan Tambahan</label>
                    <textarea name="keterangan" x-model="keterangan" rows="2" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="isDanaModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0e6c37] text-white font-bold rounded-xl shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
