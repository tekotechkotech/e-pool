@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    isBarangModalOpen: false,
    isBarangEditing: false,
    barang_id: null,
    kode: '',
    nama: '',
    kategori: 'Sembako',
    satuan: 'Paket',
    keterangan: '',
    is_active: true,

    // Modal Pengadaan (BA & Masuk)
    isPengadaanModalOpen: false,
    pengadaan_nomor_ba: '',
    pengadaan_tanggal_ba: '{{ date('Y-m-d') }}',
    pengadaan_pengajuan_id: '',
    pengadaan_barang_id: '{{ $selectedBarangId ?? '' }}',
    is_barang_baru: false,
    nama_barang_baru: '',
    satuan_barang_baru: 'Paket',
    kategori_barang_baru: 'Sembako',
    pengadaan_jumlah: 1,
    pengadaan_harga_satuan: '0',
    pengadaan_harga_total: '0',
    pengadaan_penyedia: '',
    pengadaan_sumber_dana_id: '',
    pengadaan_potong_kas: false,
    pengadaan_keterangan: '',

    // Modal Pengeluaran Barang
    isKeluarModalOpen: false,
    keluar_barang_id: '{{ $selectedBarangId ?? '' }}',
    keluar_tanggal: '{{ date('Y-m-d') }}',
    keluar_jumlah: 1,
    keluar_dokumen: '',
    keluar_uraian: '',

    calculateTotal() {
        let qty = parseInt(String(this.pengadaan_jumlah).replace(/\D/g, ''), 10) || 0;
        let price = parseInt(String(this.pengadaan_harga_satuan).replace(/\D/g, ''), 10) || 0;
        let total = qty * price;
        this.pengadaan_harga_total = total ? formatNumber(total) : '0';
    },

    openCreateBarang() {
        this.barang_id = null;
        this.kode = 'BRG-' + Math.floor(1000 + Math.random() * 9000);
        this.nama = '';
        this.kategori = 'Sembako';
        this.satuan = 'Paket';
        this.keterangan = '';
        this.is_active = true;
        this.isBarangEditing = false;
        this.isBarangModalOpen = true;
    },

    openEditBarang(b) {
        this.barang_id = b.id;
        this.kode = b.kode;
        this.nama = b.nama;
        this.kategori = b.kategori;
        this.satuan = b.satuan;
        this.keterangan = b.keterangan || '';
        this.is_active = !!b.is_active;
        this.isBarangEditing = true;
        this.isBarangModalOpen = true;
    },

    openPengadaan(barangId = null) {
        this.pengadaan_barang_id = barangId || '{{ $selectedBarangId ?? '' }}';
        this.pengadaan_nomor_ba = 'BA-LOG/' + new Date().getFullYear() + '/' + Math.floor(100 + Math.random() * 900);
        this.pengadaan_tanggal_ba = '{{ date('Y-m-d') }}';
        this.pengadaan_pengajuan_id = '';
        this.is_barang_baru = false;
        this.nama_barang_baru = '';
        this.satuan_barang_baru = 'Paket';
        this.kategori_barang_baru = 'Sembako';
        this.pengadaan_jumlah = 1;
        this.pengadaan_harga_satuan = '0';
        this.pengadaan_harga_total = '0';
        this.pengadaan_penyedia = '';
        this.pengadaan_sumber_dana_id = '';
        this.pengadaan_potong_kas = false;
        this.pengadaan_keterangan = '';
        this.isPengadaanModalOpen = true;
    },

    openMutasiKeluar(barangId = null) {
        this.keluar_barang_id = barangId || '{{ $selectedBarangId ?? '' }}';
        this.keluar_tanggal = '{{ date('Y-m-d') }}';
        this.keluar_jumlah = 1;
        this.keluar_dokumen = '';
        this.keluar_uraian = '';
        this.isKeluarModalOpen = true;
    },

    onPengajuanChange(event) {
        const select = event.target;
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.dataset.namaBarang) {
            const nama = selectedOption.dataset.namaBarang;
            const qty = selectedOption.dataset.qtyBarang;
            const satuan = selectedOption.dataset.satuanBarang;

            // Cek apakah barang ada di opsi barang
            let found = false;
            const barangSelect = document.getElementById('selectBarangPengadaan');
            if (barangSelect) {
                for (let i = 0; i < barangSelect.options.length; i++) {
                    if (barangSelect.options[i].text.toLowerCase().includes(nama.toLowerCase())) {
                        this.pengadaan_barang_id = barangSelect.options[i].value;
                        found = true;
                        break;
                    }
                }
            }

            if (!found) {
                this.is_barang_baru = true;
                this.nama_barang_baru = nama;
                if (satuan) this.satuan_barang_baru = satuan;
            }

            if (qty && parseInt(qty) > 0) {
                this.pengadaan_jumlah = parseInt(qty);
            }
            this.calculateTotal();
        }
    }
}">

    <!-- Notification Success/Error -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-emerald-800 text-sm">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Terjadi Kesalahan:</span>
            </div>
            <ul class="list-disc list-inside text-xs pl-2">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top Metric Widgets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- 1. Sisa Stok Aktif -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-emerald-50 opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-sm font-semibold text-slate-500 mb-1">Total Sisa Stok Tersedia</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ number_format($widgets['totalStokAktif'], 0, ',', '.') }} <span class="text-sm font-medium text-slate-400">Unit/Pkt</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-[#118243] flex items-center justify-center shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-emerald-600 relative z-10">
                <span>Siap disalurkan untuk permohonan mustahik</span>
            </div>
        </div>

        <!-- 2. Total Pengadaan / Stok Masuk -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-indigo-50 opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-sm font-semibold text-slate-500 mb-1">Total Pengadaan (Masuk)</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ number_format($widgets['totalMasuk'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-indigo-500 relative z-10">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                <span>Akumulasi barang yang dibeli (BA)</span>
            </div>
        </div>

        <!-- 3. Total Penyaluran / Stok Keluar -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-rose-50 opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-sm font-semibold text-slate-500 mb-1">Total Disalurkan (Keluar)</p>
                    <h3 class="text-2xl font-black text-slate-800">{{ number_format($widgets['totalKeluar'], 0, ',', '.') }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-rose-500 relative z-10">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                <span>Barang tersalurkan ke mustahik</span>
            </div>
        </div>

        <!-- 4. Total Jenis Barang Terdaftar -->
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/5 opacity-50 group-hover:scale-110 transition-transform duration-500"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-sm font-semibold text-slate-400 mb-1">Jenis Barang Terdaftar</p>
                    <h3 class="text-2xl font-black text-white">{{ $widgets['totalBarang'] }} <span class="text-sm font-medium text-slate-400">Item</span></h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-slate-700/50 text-white flex items-center justify-center border border-slate-600/50 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs font-medium text-slate-300 relative z-10">
                <span>Katalog logistik & natura BAZNAS</span>
            </div>
        </div>
    </div>

    <!-- Main Content Area: 2 Columns (Master Barang & Transaksi Mutasi) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Master Barang & Stok -->
        <div class="col-span-1 lg:col-span-4 xl:col-span-4 bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-[calc(100vh-8rem)] border-t-[4px] border-t-emerald-500">
            <!-- Header -->
            <div class="p-5 flex items-center justify-between border-b border-slate-50">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Daftar Barang</h2>
                    <p class="text-xs text-slate-400">Klik item untuk filter riwayat mutasi</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                    {{ $barangs->count() }} Item
                </span>
            </div>

            <!-- Table Subheader -->
            <div class="grid grid-cols-12 gap-2 px-5 py-2.5 bg-slate-50/70 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <div class="col-span-3">KODE</div>
                <div class="col-span-6">BARANG & KATEGORI</div>
                <div class="col-span-3 text-right">SISA STOK</div>
            </div>

            <!-- Item List -->
            <div class="overflow-y-auto flex-1 divide-y divide-slate-100">
                @forelse($barangs as $barang)
                    <div onclick="window.location.href='{{ route('master.barangs', ['barang_id' => $barang->id]) }}'" 
                         class="group cursor-pointer grid grid-cols-12 gap-2 px-5 py-3.5 transition-colors items-center {{ $selectedBarangId == $barang->id ? 'bg-[#c3d8cd] hover:bg-[#b3ccbe]' : 'hover:bg-slate-50' }}">
                        <div class="col-span-3 text-xs font-mono font-bold text-slate-500">
                            {{ $barang->kode }}
                        </div>
                        <div class="col-span-6">
                            <div class="font-bold text-slate-800 text-sm leading-snug">{{ $barang->nama }}</div>
                            <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium">
                                    {{ $barang->kategori }}
                                </span>
                                <span>({{ $barang->satuan }})</span>
                            </div>
                        </div>
                        <div class="col-span-3 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black {{ $barang->stok > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                {{ number_format($barang->stok, 0, ',', '.') }}
                            </span>
                            
                            <!-- Action buttons on hover -->
                            <div class="hidden group-hover:flex items-center justify-end space-x-1.5 mt-1" onclick="event.stopPropagation()">
                                <button type="button" @click="openPengadaan({{ $barang->id }})" class="p-1 text-emerald-600 hover:bg-white rounded transition-colors" title="Catat Pengadaan (Masuk)">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                                <button type="button" @click="openEditBarang({{ $barang->toJson() }})" class="p-1 text-indigo-600 hover:bg-white rounded transition-colors" title="Edit Master Barang">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('master.barangs.destroy', $barang->id) }}" data-confirm="Yakin ingin menghapus item barang ini?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-rose-500 hover:bg-white rounded transition-colors" title="Hapus Barang">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-slate-400">
                        Belum ada item barang terdaftar. Klik tombol di bawah untuk menambahkan.
                    </div>
                @endforelse
            </div>

            <!-- Footer Add Master Barang Button -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <button type="button" @click="openCreateBarang()" class="w-full py-2.5 px-4 bg-[#118243]/10 hover:bg-[#118243]/20 text-[#118243] font-bold text-xs sm:text-sm rounded-xl transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Item Barang Baru
                </button>
            </div>
        </div>

        <!-- Right Column: Mutasi Barang Masuk & Keluar -->
        <div class="col-span-1 lg:col-span-8 xl:col-span-8 bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-[calc(100vh-8rem)] border-t-[4px] border-t-indigo-500">
            <!-- Header -->
            <div class="p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-100 gap-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-lg">Tabel Mutasi Barang (Masuk & Keluar)</h3>
                    @if($selectedBarangId)
                        @php $selBarang = $barangs->firstWhere('id', $selectedBarangId); @endphp
                        <p class="text-xs text-slate-500 mt-0.5">
                            Menampilkan transaksi untuk barang: <strong class="text-indigo-600">{{ $selBarang->nama ?? '' }}</strong> (Sisa Stok: {{ $selBarang->stok ?? 0 }} {{ $selBarang->satuan ?? '' }})
                        </p>
                    @else
                        <p class="text-xs text-slate-500 mt-0.5">Menampilkan mutasi seluruh barang</p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Tombol Pengadaan Masuk -->
                    <button type="button" @click="openPengadaan()" class="px-3.5 py-2 bg-[#118243] hover:bg-[#0e6b36] text-white rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>+ Catat Pengadaan (BA)</span>
                    </button>

                    <!-- Tombol Pengeluaran Keluar -->
                    <button type="button" @click="openMutasiKeluar()" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        <span>- Catat Stok Keluar</span>
                    </button>

                    @if($selectedBarangId || $selectedJenis)
                        <a href="{{ route('master.barangs') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Reset Filter</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Filter Pills (Semua, Masuk, Keluar) -->
            <div class="px-6 py-2.5 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span class="text-slate-400 font-medium">Filter Jenis:</span>
                    <a href="{{ route('master.barangs', array_merge(request()->query(), ['jenis' => null])) }}" class="px-2.5 py-1 rounded-lg font-bold {{ empty($selectedJenis) ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
                        Semua
                    </a>
                    <a href="{{ route('master.barangs', array_merge(request()->query(), ['jenis' => 'Masuk'])) }}" class="px-2.5 py-1 rounded-lg font-bold {{ $selectedJenis === 'Masuk' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
                        Masuk (Pengadaan)
                    </a>
                    <a href="{{ route('master.barangs', array_merge(request()->query(), ['jenis' => 'Keluar'])) }}" class="px-2.5 py-1 rounded-lg font-bold {{ $selectedJenis === 'Keluar' ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}">
                        Keluar (Penyaluran)
                    </a>
                </div>

                <span class="text-slate-400">{{ $transaksis->total() }} riwayat transaksi</span>
            </div>

            <!-- Table Header -->
            <div class="px-6 py-3 bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider grid grid-cols-12 gap-2 border-b border-slate-100">
                <div class="col-span-2">TANGGAL</div>
                <div class="col-span-3">BARANG</div>
                <div class="col-span-1 text-center">JENIS</div>
                <div class="col-span-2 text-right">JUMLAH (QTY)</div>
                <div class="col-span-3 pl-2">NO. DOKUMEN / URAIAN</div>
                <div class="col-span-1 text-center">AKSI</div>
            </div>

            <!-- Table Body -->
            <div class="overflow-y-auto flex-1 divide-y divide-slate-100 bg-white">
                @forelse($transaksis as $t)
                    <div class="grid grid-cols-12 gap-2 px-6 py-3.5 items-center hover:bg-slate-50/70 transition-colors text-xs">
                        <!-- Tanggal -->
                        <div class="col-span-2 font-medium text-slate-600">
                            {{ $t->tanggal ? $t->tanggal->format('d/m/Y') : '-' }}
                        </div>

                        <!-- Barang -->
                        <div class="col-span-3">
                            <div class="font-bold text-slate-900 leading-snug">{{ $t->barang->nama ?? '-' }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $t->barang->kode ?? '' }}</div>
                        </div>

                        <!-- Jenis Badge -->
                        <div class="col-span-1 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $t->jenis === 'Masuk' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $t->jenis }}
                            </span>
                        </div>

                        <!-- Jumlah Qty & Harga -->
                        <div class="col-span-2 text-right">
                            <div class="font-bold {{ $t->jenis === 'Masuk' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $t->jenis === 'Masuk' ? '+' : '-' }}{{ number_format($t->jumlah, 0, ',', '.') }} {{ $t->barang->satuan ?? 'Unit' }}
                            </div>
                            @if($t->harga_total)
                                <div class="text-[10px] text-slate-500">
                                    Rp {{ number_format($t->harga_total, 0, ',', '.') }}
                                    @if($t->harga_satuan)
                                        <span class="text-slate-400">(@ {{ number_format($t->harga_satuan, 0, ',', '.') }})</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Dokumen & Uraian -->
                        <div class="col-span-3 pl-2 space-y-0.5">
                            @if($t->nomor_dokumen)
                                <div class="font-bold text-slate-800 flex items-center gap-1.5 flex-wrap">
                                    <span>Doc: {{ $t->nomor_dokumen }}</span>
                                    
                                    <!-- Jika referensi PengadaanBarang dan ada file BA -->
                                    @if($t->referensi && isset($t->referensi->file_ba) && $t->referensi->file_ba)
                                        <a href="{{ asset('storage/' . $t->referensi->file_ba) }}" target="_blank" class="text-[10px] text-indigo-600 hover:text-indigo-800 underline inline-flex items-center gap-0.5">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Lihat BA
                                        </a>
                                    @endif
                                </div>
                            @endif

                            <p class="text-slate-600 text-[11px] line-clamp-2" title="{{ $t->uraian }}">
                                {{ $t->uraian ?: '-' }}
                            </p>

                            <!-- Link Pengajuan Terkait -->
                            @if($t->referensi && isset($t->referensi->pengajuan_id) && $t->referensi->pengajuan_id && \Illuminate\Support\Facades\Route::has('pengajuan.show'))
                                <div class="pt-0.5">
                                    <a href="{{ route('pengajuan.show', $t->referensi->pengajuan_id) }}" class="inline-flex items-center gap-1 text-[10px] font-bold text-[#118243] hover:underline">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Lihat Pengajuan Terkait
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Aksi Hapus / Batalkan -->
                        <div class="col-span-1 text-center">
                            <form method="POST" action="{{ route('master.barangs.transaksi.destroy', $t->id) }}" data-confirm="Apakah Anda yakin ingin membatalkan transaksi mutasi ini? Stok barang akan disesuaikan kembali.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-300 hover:text-rose-600 transition-colors p-1" title="Batalkan Transaksi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-slate-400 italic">
                        Belum ada catatan transaksi mutasi barang pada filter ini.
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($transaksis->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $transaksis->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 1: TAMBAH / EDIT MASTER BARANG                     -->
    <!-- ======================================================== -->
    <div x-show="isBarangModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200" @click.outside="isBarangModalOpen = false">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-slate-800" x-text="isBarangEditing ? 'Edit Master Barang' : 'Tambah Master Barang Baru'"></h3>
                <button type="button" @click="isBarangModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('master.barangs.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <input type="hidden" name="barang_id" x-model="barang_id">

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Kode Barang <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode" x-model="kode" required placeholder="Contoh: BRG-001" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-mono uppercase shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" x-model="nama" required placeholder="Contoh: Beras Premium 5kg, Paket Sembako Ramadhan" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Kategori <span class="text-rose-500">*</span></label>
                        <select name="kategori" x-model="kategori" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                            <option value="Sembako">Sembako / Pangan</option>
                            <option value="Kesehatan">Kesehatan & Medis</option>
                            <option value="Pendidikan">Pendidikan & Sekolah</option>
                            <option value="Peralatan Usaha">Modal & Alat Usaha</option>
                            <option value="Logistik Bencana">Logistik Bencana</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Satuan <span class="text-rose-500">*</span></label>
                        <input type="text" name="satuan" x-model="satuan" required list="listSatuanBarang" placeholder="Paket, Unit, Dus, dll" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                        <datalist id="listSatuanBarang">
                            <option value="Paket">
                            <option value="Unit">
                            <option value="Buah">
                            <option value="Dus">
                            <option value="Kg">
                            <option value="Karung">
                            <option value="Set">
                            <option value="Pcs">
                            <option value="Liter">
                        </datalist>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Keterangan / Spesifikasi</label>
                    <textarea name="keterangan" x-model="keterangan" rows="2" placeholder="Catatan spesifikasi atau detail barang..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="barang_is_active" value="1" x-model="is_active" class="w-4 h-4 rounded text-[#118243] focus:ring-[#118243] border-slate-300">
                    <label for="barang_is_active" class="text-slate-700 font-medium">Status Barang Aktif</label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="isBarangModalOpen = false" class="px-4 py-2 text-slate-600 font-semibold hover:bg-slate-100 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl transition-colors shadow-xs">
                        Simpan Barang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 2: CATAT PENGADAAN BARANG (BA & STOK MASUK)         -->
    <!-- ======================================================== -->
    <div x-show="isPengadaanModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-4 shadow-2xl border border-slate-200 my-8" @click.outside="isPengadaanModalOpen = false">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-base text-slate-800">Catat Pengadaan Barang (BA & Stok Masuk)</h3>
                    <p class="text-xs text-slate-400">Pencatatan pembelian barang inventaris/natura berdasarkan Berita Acara (BA)</p>
                </div>
                <button type="button" @click="isPengadaanModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('master.barangs.pengadaan.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf

                <!-- BAGIAN 1: DATA BERITA ACARA (BA) PENGADAAN -->
                <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/80 space-y-3">
                    <span class="font-bold text-slate-700 uppercase tracking-wider block text-[11px]">1. Data Berita Acara (BA) Pengadaan</span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Nomor BA Pengadaan <span class="text-rose-500">*</span></label>
                            <input type="text" name="nomor_ba" x-model="pengadaan_nomor_ba" required placeholder="Contoh: 042/BA-PENGADAAN/IX/2026" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-mono font-bold shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Tanggal BA <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_ba" x-model="pengadaan_tanggal_ba" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Penyedia / Toko / Vendor</label>
                            <input type="text" name="penyedia" x-model="pengadaan_penyedia" placeholder="Nama rekanan/supplier..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Upload Berkas BA (PDF / Gambar)</label>
                            <input type="file" name="file_ba" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-[#118243] hover:file:bg-emerald-100">
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 2: HUBUNGKAN KE PENGAJUAN (DISPOSISI BARANG) -->
                <div class="bg-amber-50/60 p-3.5 rounded-xl border border-amber-200/80 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-amber-900 uppercase tracking-wider block text-[11px]">2. Hubungkan ke Pengajuan Disposisi Barang (Opsional)</span>
                        <span class="text-[10px] text-amber-700 font-medium">Bisa dikosongkan jika pengadaan stok umum</span>
                    </div>

                    <select name="pengajuan_id" x-model="pengadaan_pengajuan_id" @change="onPengajuanChange($event)" class="w-full rounded-xl border border-amber-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none">
                        <option value="">-- Tidak Terhubung / Pengadaan Stok Umum Gudang --</option>
                        @foreach($pengajuansWithBarang as $peng)
                            @php
                                $pmBarang = $peng->pengajuanMustahiks->firstWhere('bentuk_bantuan', 'Barang');
                                $namaPemohon = $peng->mustahik?->person?->nama ?? $peng->lembaga?->nama ?? $peng->mustahik_nama_snapshot ?? 'Pemohon';
                            @endphp
                            <option value="{{ $peng->id }}" 
                                    data-nama-barang="{{ $pmBarang?->nama_barang ?? '' }}" 
                                    data-qty-barang="{{ $pmBarang?->jumlah_barang ?? '' }}" 
                                    data-satuan-barang="{{ $pmBarang?->satuan_barang ?? '' }}">
                                #{{ $peng->nomor_pengajuan }} - {{ $namaPemohon }} (Kebutuhan: {{ $pmBarang?->jumlah_barang ?? '' }} {{ $pmBarang?->satuan_barang ?? '' }} {{ $pmBarang?->nama_barang ?? 'Barang' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- BAGIAN 3: RINCIAN BARANG, QTY, & HARGA -->
                <div class="space-y-3">
                    <span class="font-bold text-slate-700 uppercase tracking-wider block text-[11px]">3. Rincian Barang & Pembelian</span>

                    <!-- Toggle Barang Baru / Pilih Existing -->
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="mode_barang" :value="false" x-model="is_barang_baru" class="text-[#118243] focus:ring-[#118243]">
                            <span>Pilih dari Master Barang</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="mode_barang" :value="true" x-model="is_barang_baru" class="text-[#118243] focus:ring-[#118243]">
                            <span>+ Input Nama Barang Baru</span>
                        </label>
                    </div>

                    <!-- Jika Pilih dari Master Barang -->
                    <div x-show="!is_barang_baru">
                        <label class="block font-bold text-slate-600 uppercase mb-1">Item Barang <span class="text-rose-500">*</span></label>
                        <select id="selectBarangPengadaan" name="barang_id" x-model="pengadaan_barang_id" :required="!is_barang_baru" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($barangs as $b)
                                <option value="{{ $b->id }}">{{ $b->nama }} ({{ $b->satuan }}) - Stok: {{ $b->stok }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jika Input Barang Baru -->
                    <div x-show="is_barang_baru" class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-600 uppercase mb-1">Nama Barang Baru <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_barang_baru" x-model="nama_barang_baru" :required="is_barang_baru" placeholder="Contoh: Beras Rojolele 5kg" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Satuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="satuan_barang_baru" x-model="satuan_barang_baru" placeholder="Paket, Kg, dll" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                        </div>
                    </div>

                    <!-- Kuantitas, Harga Satuan, Harga Total -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Berapa Banyak (Qty) <span class="text-rose-500">*</span></label>
                            <input type="number" name="jumlah" x-model="pengadaan_jumlah" @input="calculateTotal()" min="1" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-bold text-slate-800 shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Harga Satuan (Rp) <span class="text-rose-500">*</span></label>
                            <input type="text" inputmode="numeric" x-number name="harga_satuan" x-model="pengadaan_harga_satuan" @input="calculateTotal()" required placeholder="0" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-bold text-[#118243] shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Harga Total (Rp) <span class="text-rose-500">*</span></label>
                            <input type="text" inputmode="numeric" x-number name="harga_total" x-model="pengadaan_harga_total" required class="w-full rounded-xl border border-emerald-300 bg-emerald-50/50 p-2.5 text-xs font-black text-emerald-800 shadow-xs focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 4: PEMBAYARAN & REKENING DANA (OPSIONAL POTONG KAS) -->
                <div class="bg-indigo-50/60 p-3.5 rounded-xl border border-indigo-200/80 space-y-3">
                    <span class="font-bold text-indigo-900 uppercase tracking-wider block text-[11px]">4. Sumber Rekening Dana Pembelian</span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Pilih Rekening Dana</label>
                            <select name="sumber_dana_id" x-model="pengadaan_sumber_dana_id" class="w-full rounded-xl border border-indigo-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none">
                                <option value="">-- Tanpa Potong Kas / Non Kas --</option>
                                @foreach($danas as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama }} ({{ $d->kode }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-4">
                            <label class="flex items-center gap-2 cursor-pointer select-none text-slate-700">
                                <input type="checkbox" name="potong_kas" value="1" x-model="pengadaan_potong_kas" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                <span class="font-bold text-xs text-indigo-900">Otomatis catat pengeluaran di menu Dana & Transaksi</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Catatan / Keterangan Tambahan</label>
                    <textarea name="keterangan" x-model="pengadaan_keterangan" rows="2" placeholder="Catatan pengadaan..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="isPengadaanModalOpen = false" class="px-4 py-2 text-slate-600 font-semibold hover:bg-slate-100 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl transition-colors shadow-xs flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Pengadaan & Tambah Stok</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- MODAL 3: CATAT PENGELUARAN BARANG (MANUAL)                -->
    <!-- ======================================================== -->
    <div x-show="isKeluarModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200" @click.outside="isKeluarModalOpen = false">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-base text-slate-800">Catat Pengeluaran Barang</h3>
                    <p class="text-xs text-slate-400">Pengurangan stok barang untuk penyaluran mandiri / penyesuaian</p>
                </div>
                <button type="button" @click="isKeluarModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('master.barangs.mutasi-keluar.store') }}" class="space-y-3.5 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Item Barang <span class="text-rose-500">*</span></label>
                    <select name="keluar_barang_id" x-model="keluar_barang_id" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:outline-none">
                        <option value="">-- Pilih Barang yang Dikeluarkan --</option>
                        @foreach($barangs as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }} (Sisa Stok: {{ $b->stok }} {{ $b->satuan }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Tanggal Keluar <span class="text-rose-500">*</span></label>
                        <input type="date" name="keluar_tanggal" x-model="keluar_tanggal" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Banyak Dikeluarkan (Qty) <span class="text-rose-500">*</span></label>
                        <input type="number" name="keluar_jumlah" x-model="keluar_jumlah" min="1" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-bold text-rose-700 shadow-xs focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Nomor Dokumen / BAST / Nota (Opsional)</label>
                    <input type="text" name="keluar_dokumen" x-model="keluar_dokumen" placeholder="Contoh: BAST-001/LOG/2026" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Keperluan / Uraian Pengeluaran <span class="text-rose-500">*</span></label>
                    <textarea name="keluar_uraian" x-model="keluar_uraian" rows="2" required placeholder="Contoh: Penyaluran bantuan tanggap darurat bencana atau rusak..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" @click="isKeluarModalOpen = false" class="px-4 py-2 text-slate-600 font-semibold hover:bg-slate-100 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition-colors shadow-xs flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        <span>Simpan & Kurangi Stok</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
