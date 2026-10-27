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

    isPenerimaanModalOpen: false,
    penerimaan_dana_id: '{{ $selectedDanaId ?? '' }}',
    penerimaan_tanggal: '{{ date('Y-m-d') }}',
    penerimaan_nominal: '',
    penerimaan_uraian: '',
    penerimaan_sumber: '',

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
    },

    openPenerimaan(danaId = null) {
        this.penerimaan_dana_id = danaId || '{{ $selectedDanaId ?? '' }}';
        this.penerimaan_tanggal = '{{ date('Y-m-d') }}';
        this.penerimaan_nominal = '';
        this.penerimaan_uraian = '';
        this.penerimaan_sumber = '';
        this.isPenerimaanModalOpen = true;
    }
}">
    <!-- Widgets Top Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Saldo Akhir -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-emerald-50 opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-sm font-semibold text-slate-500 mb-1">{{ $selectedDanaId ? 'Saldo Dana Terpilih' : 'Total Saldo Keseluruhan' }}</p>
                    <h3 class="text-2xl font-black text-slate-800">Rp {{ number_format($widgets['saldoAkhir'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-slate-400 relative z-10">
                <span>Berjalan secara {{ $selectedDanaId ? 'spesifik' : 'akumulatif' }}</span>
            </div>
        </div>

        <!-- Total Pemasukan -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-indigo-50 opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-sm font-semibold text-slate-500 mb-1">Total Transaksi Masuk</p>
                    <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($widgets['totalMasuk'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-emerald-500 relative z-10">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                <span>Dana Masuk (Penerimaan)</span>
            </div>
        </div>

        <!-- Total Pengeluaran -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-rose-50 opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-sm font-semibold text-slate-500 mb-1">Total Transaksi Keluar</p>
                    <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($widgets['totalKeluar'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-rose-500 relative z-10">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                <span>Dana Keluar (Penyaluran)</span>
            </div>
        </div>

        <!-- Statistik Kategori -->
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/5 opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-sm font-semibold text-slate-400 mb-1">Manajemen Tipe Dana</p>
                    <h3 class="text-2xl font-black text-white">{{ $widgets['totalDanaAktif'] }} <span class="text-sm font-medium text-slate-400">Aktif</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-700/50 text-white flex items-center justify-center border border-slate-600/50 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-slate-300 relative z-10">
                <span>Dari total {{ $widgets['totalDana'] }} tipe dana terdaftar</span>
            </div>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Danas -->
        <div class="col-span-1 lg:col-span-4 xl:col-span-4 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-[calc(100vh-8rem)] border-t-[4px] border-t-emerald-500">
            <!-- Header -->
            <div class="p-5 text-center">
                <h2 class="text-xl font-bold text-gray-800">Daftar Dana</h2>
            </div>
            
            <!-- Table Header -->
            <div class="grid grid-cols-12 gap-2 px-5 py-3 bg-gray-50/50 text-xs font-bold text-gray-400 uppercase tracking-wider">
                <div class="col-span-3">KODE</div>
                <div class="col-span-9">DANA</div>
            </div>
            
            <!-- List -->
            <div class="overflow-y-auto flex-1">
                @forelse($danas as $dana)
                <div onclick="window.location.href='{{ route('master.danas', ['dana_id' => $dana->id]) }}'" class="group cursor-pointer grid grid-cols-12 gap-2 px-5 py-4 border-b border-gray-100 transition-colors {{ $selectedDanaId == $dana->id ? 'bg-[#c3d8cd] hover:bg-[#b3ccbe]' : 'hover:bg-gray-50/80' }}">
                    <div class="col-span-3 flex items-start pt-1 text-gray-500 font-medium text-sm">
                        {{ $dana->kode }}
                    </div>
                    <div class="col-span-9 flex justify-between items-start">
                        <div>
                            <div class="font-bold text-gray-800 text-base leading-tight">{{ $dana->nama }}</div>
                            <div class="text-xs mt-1 flex items-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded-full font-medium text-[11px] bg-slate-100 text-slate-700">
                                    {{ $dana->kategori }}
                                </span>
                                @if($dana->via_upz)
                                    <span class="px-2 py-0.5 rounded-full font-medium text-[10px] bg-blue-50 text-blue-700 border border-blue-100">
                                        Via UPZ
                                    </span>
                                @endif
                            </div>
                        </div>
                        <!-- Action buttons -->
                        <div class="hidden group-hover:flex space-x-2 bg-white/50 px-2 py-1 rounded-md" onclick="event.stopPropagation()">
                            <button type="button" @click="openPenerimaan({{ $dana->id }})" class="text-emerald-600 hover:text-emerald-800 transition-colors" title="Catat Penerimaan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                            <button type="button" @click="openEditDana({{ $dana->toJson() }})" class="text-indigo-500 hover:text-indigo-700 transition-colors" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <form method="POST" action="{{ route('master.danas.destroy', $dana->id) }}" data-confirm="Yakin ingin menghapus jenis dana ini?" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-5 text-center text-sm text-gray-500">Belum ada data dana.</div>
                @endforelse
            </div>
            
            <!-- Add Button -->
            <div class="p-4 flex justify-center mt-auto border-t border-gray-50">
                <button type="button" @click="openCreateDana()" class="bg-[#118243]/10 text-[#118243] w-full font-semibold text-sm hover:bg-[#118243]/20 py-2.5 rounded-lg flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Dana Baru
                </button>
            </div>
        </div>

        <!-- Right Column: Dana Transaksi -->
        <div class="col-span-1 lg:col-span-8 xl:col-span-8 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-[calc(100vh-8rem)] border-t-[4px] border-t-indigo-500">
            <!-- Header -->
            <div class="p-5 flex flex-col sm:flex-row justify-between items-center border-b border-gray-50 gap-4">
                <div>
                    <h3 class="font-bold text-gray-800 text-xl">Transaksi Dana</h3>
                    @if($selectedDanaId)
                        @php $selectedDana = $danas->firstWhere('id', $selectedDanaId); @endphp
                        <p class="text-[14px] text-gray-500 mt-1">Menampilkan transaksi untuk Dana: <strong class="text-indigo-600">{{ $selectedDana->nama ?? '' }}</strong></p>
                    @else
                        <p class="text-[14px] text-gray-500 mt-1">Menampilkan semua transaksi</p>
                    @endif
                </div>
                <div class="flex items-center gap-2.5">
                    <button type="button" @click="openPenerimaan()" class="px-4 py-2 bg-[#118243] text-white rounded-lg text-sm font-semibold hover:bg-[#0e6b36] transition-colors flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Catat Penerimaan Dana
                    </button>
                    @if($selectedDanaId)
                    <a href="{{ route('master.danas') }}" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-semibold hover:bg-indigo-100 transition-colors flex items-center shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Tampilkan Semua
                    </a>
                    @endif
                </div>
            </div>
            
            <!-- Table Header -->
            <div class="px-6 py-3 bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider grid grid-cols-12 gap-2 border-b border-gray-100">
                <div class="col-span-2">TANGGAL</div>
                <div class="col-span-2">DANA</div>
                <div class="col-span-1">JENIS</div>
                <div class="col-span-2 text-right">NOMINAL</div>
                <div class="col-span-3 pl-4">URAIAN</div>
                <div class="col-span-2">REFERENSI</div>
            </div>
            
            <!-- List -->
            <div class="overflow-y-auto flex-1 bg-white">
                @forelse($transaksis as $transaksi)
                <div class="grid grid-cols-12 gap-2 px-6 py-4 border-b border-gray-50 items-center hover:bg-gray-50/50 transition-colors group">
                    <div class="col-span-2 text-sm text-gray-600 font-medium">
                        {{ $transaksi->tanggal ? $transaksi->tanggal->format('d/m/Y') : '-' }}
                    </div>
                    <div class="col-span-2 text-sm font-semibold text-gray-800">
                        {{ $transaksi->dana ? $transaksi->dana->nama : '-' }}
                    </div>
                    <div class="col-span-1">
                        <span class="px-2 py-1 rounded-md text-[11px] font-bold {{ $transaksi->jenis == 'Masuk' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $transaksi->jenis }}
                        </span>
                    </div>
                    <div class="col-span-2 text-right text-sm font-bold {{ $transaksi->jenis == 'Masuk' ? 'text-emerald-600' : 'text-rose-600' }}">
                        Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                    </div>
                    <div class="col-span-3 pl-4 text-sm text-gray-600 line-clamp-2" title="{{ $transaksi->uraian }}">
                        {{ $transaksi->uraian ?: '-' }}
                    </div>
                    <div class="col-span-2 text-xs text-gray-400">
                        @if($transaksi->referensi)
                            <span>{{ class_basename($transaksi->referensi_type) }} #{{ $transaksi->referensi_id }}</span>
                        @else
                            <span>Penerimaan Langsung</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-400 italic">Belum ada catatan mutasi transaksi pada dana ini.</div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($transaksis->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $transaksis->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Form Dana (Create / Edit) -->
    <div x-show="isDanaModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-slate-800" x-text="isDanaEditing ? 'Edit Jenis Dana' : 'Tambah Jenis Dana Baru'"></h3>
                <button type="button" @click="isDanaModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
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

    <!-- Modal Catat Penerimaan Dana -->
    <div x-show="isPenerimaanModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-slate-800">Catat Penerimaan Kas (Pemasukan)</h3>
                <button type="button" @click="isPenerimaanModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('master.danas.penerimaan') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Pilih Akun Dana <span class="text-red-500">*</span></label>
                    <select name="penerimaan_dana_id" x-model="penerimaan_dana_id" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                        <option value="">-- Pilih Jenis Dana --</option>
                        @foreach($danas as $d)
                            <option value="{{ $d->id }}">{{ $d->kode }} - {{ $d->nama }} ({{ $d->kategori }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Tanggal Penerimaan <span class="text-red-500">*</span></label>
                        <input type="date" name="penerimaan_tanggal" x-model="penerimaan_tanggal" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Nominal Penerimaan (Rp) <span class="text-red-500">*</span></label>
                        <input type="text" inputmode="numeric" x-number name="penerimaan_nominal" x-model="penerimaan_nominal" required placeholder="Contoh: 5.000.000" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-bold text-emerald-600 shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Sumber Penerimaan (Muzakki / Donatur)</label>
                    <input type="text" name="penerimaan_sumber" x-model="penerimaan_sumber" placeholder="Contoh: H. Ahmad Muzakki / Bank Mandiri" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Uraian / Keterangan Transaksi</label>
                    <textarea name="penerimaan_uraian" x-model="penerimaan_uraian" rows="2" placeholder="Catatan perolehan dana..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="isPenerimaanModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm">Catat Penerimaan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
