@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <p class="font-semibold">Terjadi kesalahan:</p>
            <ul class="list-disc list-inside mt-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-800">{{ $setoran->nomor_setoran }}</h1>
                @if($setoran->status === 'Terverifikasi')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $setoran->status }}
                    </span>
                @elseif($setoran->status === 'Ditolak')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        {{ $setoran->status }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $setoran->status }}
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-500">Detail data setoran ZIS UPZ {{ $setoran->upz->nama ?? '-' }}.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()?->isBaznasRole() || auth()->user()?->hasBaznasAppointment() || auth()->user()?->isSuperuser())
                <a href="{{ route('pengumpulan.upz.edit', $setoran->id) }}" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-xl text-sm font-semibold transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
            @endif
            <a href="{{ route('pengumpulan.upz.cetak', $setoran->id) }}" target="_blank" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Lampiran Setoran
            </a>
            <a href="{{ route('pengumpulan.upz.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition-all">
                Kembali
            </a>
        </div>
    </div>

    @if(auth()->user()?->isBaznasRole() || auth()->user()?->hasBaznasAppointment() || auth()->user()?->isSuperuser())
        @if($setoran->status === 'Menunggu Verifikasi')
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4" x-data="{ tolakModal: false, verifikasiModal: false }">
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-amber-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Menunggu Verifikasi BAZNAS
                    </h3>
                    <p class="text-xs text-amber-700">Setoran ini belum diverifikasi dan belum dibukukan ke buku kas (DanaTransaksi). Silakan periksa rincian data dan bukti transfer.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="tolakModal = true" class="px-4 py-2 bg-white border border-rose-200 text-rose-700 hover:bg-rose-50 rounded-xl text-xs font-semibold shadow-xs transition-all">
                        Tolak Setoran
                    </button>
                    <button type="button" @click="verifikasiModal = true" class="px-4 py-2 bg-[#118243] hover:bg-[#0d6835] text-white rounded-xl text-xs font-semibold shadow-xs transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Verifikasi Setoran
                    </button>
                </div>

                <!-- Modal Verifikasi -->
                <div x-show="verifikasiModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" @keydown.escape.window="verifikasiModal = false">
                    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200" @click.outside="verifikasiModal = false">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Verifikasi Setoran UPZ
                            </h3>
                            <button type="button" @click="verifikasiModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500">Dengan memverifikasi setoran ini, status akan menjadi <strong>Terverifikasi</strong> dan nominal zakat/infak akan otomatis dibukukan ke buku kas (DanaTransaksi).</p>
                        <form method="POST" action="{{ route('pengumpulan.upz.verifikasi', $setoran->id) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Verifikasi (Opsional)</label>
                                <textarea name="catatan_verifikasi" rows="2" class="w-full text-xs border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243]" placeholder="Catatan kesesuaian data / rekening koran...">{{ $setoran->catatan_verifikasi }}</textarea>
                            </div>
                            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                <button type="button" @click="verifikasiModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors">Batal</button>
                                <button type="submit" class="px-4 py-2 bg-[#118243] hover:bg-[#0d6835] text-white rounded-xl text-xs font-semibold shadow-sm transition-colors">Konfirmasi Verifikasi</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Tolak -->
                <div x-show="tolakModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" @keydown.escape.window="tolakModal = false">
                    <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200" @click.outside="tolakModal = false">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                            <h3 class="text-base font-bold text-rose-700 flex items-center gap-2">
                                <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Tolak Setoran UPZ
                            </h3>
                            <button type="button" @click="tolakModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-slate-500">Wajib memberikan alasan atau catatan penolakan agar UPZ dapat memperbaiki data setoran.</p>
                        <form method="POST" action="{{ route('pengumpulan.upz.tolak', $setoran->id) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                                <textarea name="catatan_verifikasi" required rows="3" class="w-full text-xs border border-rose-300 rounded-xl p-2.5 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500" placeholder="Jelaskan alasan penolakan..."></textarea>
                            </div>
                            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                <button type="button" @click="tolakModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors">Batal</button>
                                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-colors">Tolak Setoran</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if($setoran->verified_at || $setoran->catatan_verifikasi)
        <div class="p-4 rounded-2xl border {{ $setoran->status === 'Ditolak' ? 'bg-rose-50/50 border-rose-200' : 'bg-emerald-50/50 border-emerald-200' }} shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold {{ $setoran->status === 'Ditolak' ? 'text-rose-800' : 'text-emerald-800' }}">
                    {{ $setoran->status === 'Ditolak' ? 'Ditolak oleh:' : 'Diverifikasi oleh:' }}
                </span>
                <span class="font-semibold text-slate-900">{{ $setoran->verifiedBy?->person?->nama ?? $setoran->verifiedBy?->name ?? 'Staf BAZNAS' }}</span>
                @if($setoran->verified_at)
                    <span class="text-slate-400">pada {{ optional($setoran->verified_at)->format('d F Y H:i') }}</span>
                @endif
            </div>
            @if($setoran->catatan_verifikasi)
                <div class="text-slate-700 italic bg-white/80 px-3 py-1.5 rounded-xl border {{ $setoran->status === 'Ditolak' ? 'border-rose-200 text-rose-800' : 'border-emerald-200 text-slate-700' }}">
                    Catatan: "{{ $setoran->catatan_verifikasi }}"
                </div>
            @endif
        </div>
    @endif

    <!-- Info Cards -->
    @php
        $totalMuzakki = $setoran->items->count();
        $setorCount = $setoran->items->where('jumlah', '>', 0)->count();
        $nihilCount = $setoran->items->where('jumlah', '<=', 0)->count();
        $partisipasi = $totalMuzakki > 0 ? round(($setorCount / $totalMuzakki) * 100) : 0;
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-1">
            <p class="text-xs text-slate-400 font-semibold uppercase">Nama UPZ</p>
            <p class="text-base font-bold text-slate-900">{{ $setoran->upz->nama ?? '-' }}</p>
            <p class="text-xs text-slate-400">Kode Simba: {{ $setoran->upz->kode_simba ?? '-' }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-1">
            <p class="text-xs text-slate-400 font-semibold uppercase">Tanggal Setor & Periode</p>
            <p class="text-base font-bold text-slate-900">{{ optional($setoran->tanggal_setor)->format('d F Y') }}</p>
            <p class="text-xs text-slate-400">Periode: {{ $setoran->periode }}</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-1">
            <p class="text-xs text-slate-400 font-semibold uppercase">Tingkat Partisipasi UPZ</p>
            <div class="flex items-center gap-2 mt-0.5">
                <span class="text-xl font-bold text-slate-900">{{ $partisipasi }}%</span>
                <span class="text-xs text-slate-500 font-medium">({{ $setorCount }} dari {{ $totalMuzakki }} setor)</span>
            </div>
            <p class="text-xs text-amber-600 font-medium">{{ $nihilCount }} orang nihil (Rp 0)</p>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm space-y-1">
            <p class="text-xs text-slate-400 font-semibold uppercase">Grand Total Setoran</p>
            <p class="text-xl font-black text-emerald-700">Rp {{ number_format($setoran->total_jumlah, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400">Z: Rp {{ number_format($setoran->total_zakat, 0, ',', '.') }} | I: Rp {{ number_format($setoran->total_infak, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Bukti Transfer Card -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4" x-data="{ uploadModal: false }">
        <div class="flex items-start gap-4">
            <div class="p-3 rounded-xl {{ $setoran->bukti_setor ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-slate-800">Bukti Transfer / Setoran</h3>
                    @if($setoran->bukti_setor)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                            Tersedia
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800">
                            Belum Diunggah
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if($setoran->bukti_setor)
                        File tersimpan: <span class="font-mono text-slate-600">{{ basename($setoran->bukti_setor) }}</span>
                    @else
                        Bukti transfer bank atau tanda terima fisik belum dilampirkan.
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($setoran->bukti_setor)
                <a href="{{ asset('storage/' . $setoran->bukti_setor) }}" target="_blank" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Lihat Bukti
                </a>
                <button type="button" @click="uploadModal = true" class="px-3 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Ganti Bukti
                </button>
            @else
                <button type="button" @click="uploadModal = true" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Unggah Bukti Transfer
                </button>
            @endif
        </div>

        <!-- Modal Upload/Ganti Bukti -->
        <div x-show="uploadModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="uploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="uploadModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="uploadModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                    <form action="{{ route('pengumpulan.upz.upload-bukti', $setoran->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                        @csrf
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <h3 class="text-base font-bold text-slate-800" id="modal-title">
                                {{ $setoran->bukti_setor ? 'Ganti Bukti Transfer' : 'Unggah Bukti Transfer' }}
                            </h3>
                            <button type="button" @click="uploadModal = false" class="text-slate-400 hover:text-slate-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-semibold text-slate-700">Pilih Berkas (JPG, PNG, WEBP, atau PDF, Max 5MB)</label>
                            <input type="file" name="bukti_setor" required accept=".jpg,.jpeg,.png,.webp,.pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-xl cursor-pointer bg-slate-50 focus:outline-none" />
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="uploadModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-[#118243] hover:bg-[#0d6835] text-white rounded-xl text-xs font-semibold transition-all shadow-sm">
                                Simpan Bukti
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-900">Rincian Daftar Muzakki & Setoran</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-xs uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Nama Muzakki</th>
                        <th class="px-5 py-3">NPWZ</th>
                        <th class="px-5 py-3 text-right">Zakat</th>
                        <th class="px-5 py-3 text-right">Infak</th>
                        <th class="px-5 py-3 text-right">Fitrah</th>
                        <th class="px-5 py-3 text-right">Titipan</th>
                        <th class="px-5 py-3 text-right">Jumlah</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($setoran->items as $idx => $item)
                        <tr class="hover:bg-slate-50/50 {{ $item->jumlah <= 0 ? 'bg-slate-50/30' : '' }}">
                            <td class="px-5 py-3 font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $item->nama }}</td>
                            <td class="px-5 py-3 font-mono text-slate-500">{{ $item->npwz ?: '-' }}</td>
                            <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($item->nominal_zakat, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($item->nominal_infak, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($item->nominal_fitrah, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($item->nominal_titipan, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-mono font-bold {{ $item->jumlah > 0 ? 'text-emerald-700' : 'text-slate-400' }}">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($item->jumlah > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Setor
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">
                                        Nihil (Rp 0)
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $item->keterangan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-slate-400">Tidak ada rincian item.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-100 text-xs text-slate-900">
                    <tr>
                        <td colspan="3" class="px-5 py-3 text-right">TOTAL:</td>
                        <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($setoran->total_zakat, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($setoran->total_infak, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($setoran->total_fitrah, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($setoran->total_titipan, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono font-black text-emerald-700">Rp {{ number_format($setoran->total_jumlah, 0, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
