@php
    $mode = $mode ?? 'client'; // 'client' or 'server'
    $programs = $programs ?? collect();
    $subprograms = $subprograms ?? collect();
    $asnafs = $asnafs ?? collect();
    $provinsis = $provinsis ?? \App\Models\Wilayah::provinsis()->orderBy('nama')->get();
    $pengajuan = $pengajuan ?? null;
    $isLocked = $isLocked ?? ($hasAnyDisposisi ?? false);
    $showSyncPemohon = $showSyncPemohon ?? true;
    $stepNumber = $stepNumber ?? ($mode === 'client' ? '4' : null);
    $title = $title ?? ($mode === 'client' ? 'Program BAZNAS & Penerima Manfaat' : 'Daftar Penerima Manfaat & Program BAZNAS');
@endphp

@if($mode === 'client')
    <!-- ======================================================== -->
    <!-- CLIENT MODE (WIZARD / IN-MEMORY ALPINE LIST)            -->
    <!-- ======================================================== -->
    <div x-data="{
        clientModalOpen: false,
        clientModalIndex: -1,
        clientModalData: {
            program_id: '',
            subprogram_id: '',
            asnaf_id: '',
            pekerjaan: '',
            telepon: '',
            keterangan: '',
            nama: '',
            nikOrKetua: '',
            alamat: '',
            jenis: 'perorangan',
            jumlah_jiwa: 1,
            jumlah_kk: 1
        },
        openClientEditModal(index) {
            this.clientModalIndex = index;
            const pm = (this.penerimaManfaat || penerimaManfaat)[index];
            if (!pm) return;
            this.clientModalData = {
                program_id: pm.program_id ? String(pm.program_id) : '',
                subprogram_id: pm.subprogram_id ? String(pm.subprogram_id) : '',
                asnaf_id: pm.asnaf_id ? String(pm.asnaf_id) : '',
                pekerjaan: pm.pekerjaan || '',
                telepon: pm.telepon || '',
                keterangan: pm.keterangan || '',
                nama: pm.summary?.nama || pm.nama || '',
                nikOrKetua: pm.summary?.nikOrKetua || pm.nik || '',
                alamat: pm.summary?.alamat || pm.alamat || '',
                jenis: pm.jenis || 'perorangan',
                jumlah_jiwa: pm.jumlah_jiwa || 1,
                jumlah_kk: pm.jumlah_kk || 1
            };
            this.clientModalOpen = true;
        },
        onClientModalProgramChange() {
            this.clientModalData.subprogram_id = '';
        },
        saveClientModal() {
            if (this.clientModalIndex >= 0 && (this.penerimaManfaat || penerimaManfaat)[this.clientModalIndex]) {
                const pm = (this.penerimaManfaat || penerimaManfaat)[this.clientModalIndex];
                pm.program_id = this.clientModalData.program_id;
                pm.subprogram_id = this.clientModalData.subprogram_id;
                pm.asnaf_id = this.clientModalData.asnaf_id;
                pm.pekerjaan = this.clientModalData.pekerjaan;
                pm.telepon = this.clientModalData.telepon;
                pm.keterangan = this.clientModalData.keterangan;
            }
            this.clientModalOpen = false;
        },
        openFullBiodataEdit() {
            const idx = this.clientModalIndex;
            const pm = (this.penerimaManfaat || penerimaManfaat)[idx];
            this.clientModalOpen = false;
            if (pm && typeof openEditModal === 'function') {
                openEditModal(pm, idx);
            }
        }
    }" class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs space-y-4">
        <!-- Container Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <h3 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
                @if(!empty($stepNumber))
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-[#118243] flex items-center justify-center text-xs font-bold">{{ $stepNumber }}</span>
                @endif
                {{ $title }}
            </h3>

            <div class="flex items-center gap-3">
                <!-- Toggle Smart Sync (Khusus Perorangan) -->
                @if($showSyncPemohon)
                <div x-show="tipe_pemohon === 'perorangan'" class="flex items-center gap-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="is_pemohon_penerima_langsung" @change="onTogglePenerimaLangsung()" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#118243]"></div>
                        <span class="ml-2 text-xs font-bold text-slate-700">Pemohon adalah Penerima Langsung</span>
                    </label>
                </div>
                @endif

                <!-- Tombol Tambah Penerima Manfaat -->
                <button type="button" @click="showAddPenerimaSelector = true" class="bg-[#118243]/10 text-[#118243] hover:bg-[#118243]/20 font-bold px-3 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Penerima Manfaat
                </button>
            </div>
        </div>

        <!-- Notifikasi Info Sinkronisasi Otomatis -->
        @if($showSyncPemohon)
        <div x-show="tipe_pemohon === 'perorangan' && is_pemohon_penerima_langsung" class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Nama & NIK penerima manfaat otomatis tersinkronkan dengan identitas pemohon di atas.</span>
        </div>
        @endif

        <!-- Card List (Unified Beneficiary Cards) -->
        <div class="space-y-3.5">
            <template x-for="(pm, index) in penerimaManfaat" :key="pm.id">
                <div class="p-4 sm:p-5 rounded-2xl border border-slate-200/90 bg-white hover:border-slate-300 transition-all space-y-3.5 shadow-2xs">
                    <!-- Row 1: Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0" x-text="'#' + (index + 1)"></span>
                            <span class="font-bold text-sm sm:text-base text-slate-800" x-text="pm.summary?.nama || pm.nama || ('Penerima Manfaat #' + (index + 1))"></span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold"
                                  :class="pm.jenis === 'perorangan' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-blue-50 text-blue-700 border border-blue-200/60'"
                                  x-text="pm.jenis === 'perorangan' ? 'Perorangan' : 'Lembaga'">
                            </span>
                            @if($showSyncPemohon)
                            <template x-if="is_pemohon_penerima_langsung && index === 0 && tipe_pemohon === 'perorangan'">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">Pemohon</span>
                            </template>
                            @endif
                        </div>

                        <div class="flex items-center gap-1.5 self-end sm:self-auto">
                            <button type="button"
                                    @click="openClientEditModal(index)"
                                    class="px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </button>
                            @if($showSyncPemohon)
                            <template x-if="!is_pemohon_penerima_langsung || index > 0 || tipe_pemohon === 'lembaga'">
                                <button type="button"
                                        @click="removePenerima(index)"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                        title="Hapus Penerima">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </template>
                            @else
                            <button type="button"
                                    @click="removePenerima(index)"
                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                    title="Hapus Penerima">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- Row 2: Biodata Grid -->
                    <div class="bg-slate-50/70 rounded-xl p-3 sm:p-3.5 border border-slate-200/70 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-400 mb-0.5 uppercase text-[10px] tracking-wider" x-text="pm.jenis === 'perorangan' ? 'NIK' : 'Ketua'"></label>
                            <div class="font-medium text-slate-700 font-mono text-xs" x-text="pm.summary?.nikOrKetua || pm.nik || '-'"></div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-400 mb-0.5 uppercase text-[10px] tracking-wider">Golongan Asnaf</label>
                            <div class="font-medium text-xs">
                                <template x-if="pm.asnaf_id">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-medium bg-slate-100 text-slate-700" x-text="asnafs.find(a => String(a.id) === String(pm.asnaf_id))?.nama || '-'"></span>
                                </template>
                                <template x-if="!pm.asnaf_id">
                                    <span class="text-amber-600 italic">Belum ditentukan</span>
                                </template>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-400 mb-0.5 uppercase text-[10px] tracking-wider">No. Telepon / WA</label>
                            <div class="font-medium text-slate-700 font-mono text-xs" x-text="pm.telepon || pm.summary?.telepon || '-'"></div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-400 mb-0.5 uppercase text-[10px] tracking-wider">Jiwa / KK</label>
                            <div class="font-medium text-slate-700 text-xs" x-text="pm.jumlah_jiwa || '-' + ' Jiwa / ' + (pm.jumlah_kk || '-') + ' KK'"></div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-400 mb-0.5 uppercase text-[10px] tracking-wider" x-text="pm.jenis === 'perorangan' ? 'Pekerjaan' : 'Bidang'"></label>
                            <div class="font-medium text-slate-700 text-xs" x-text="pm.pekerjaan || '-'"></div>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-4 pt-1.5 border-t border-slate-200/50">
                            <label class="block font-semibold text-slate-400 mb-0.5 uppercase text-[10px] tracking-wider">Alamat Domisili</label>
                            <div class="text-slate-600 text-xs line-clamp-2" x-text="pm.summary?.alamat || pm.alamat || '-'"></div>
                        </div>
                    </div>

                    <!-- Row 3: Program & Catatan Bantuan Bar -->
                    <div class="flex items-center justify-between gap-3 flex-wrap pt-0.5 text-xs">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Program:</span>
                            <template x-if="pm.program_id">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 border border-emerald-200/80 text-xs font-semibold text-emerald-800">
                                    <svg class="w-3.5 h-3.5 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    <span x-text="programs.find(p => String(p.id) === String(pm.program_id))?.nama || 'Program BAZNAS'"></span>
                                    <template x-if="pm.subprogram_id">
                                        <span class="inline-flex items-center gap-1">
                                            <span class="text-emerald-400 font-normal">/</span>
                                            <span class="text-emerald-700 font-normal" x-text="(all_subprograms?.find(sp => String(sp.id) === String(pm.subprogram_id))?.nama) || (typeof getSubprograms === 'function' ? getSubprograms(pm.program_id)?.find(sp => String(sp.id) === String(pm.subprogram_id))?.nama : '') || ''"></span>
                                        </span>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!pm.program_id">
                                <button type="button"
                                        @click="openClientEditModal(index)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-50 border border-dashed border-amber-300 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Tentukan Program BAZNAS</span>
                                    <span class="text-[10px] text-red-500 font-bold">*Wajib</span>
                                </button>
                            </template>
                            <template x-if="pm.keterangan">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-slate-300">•</span>
                                    <span class="text-xs text-slate-500 italic truncate max-w-xs sm:max-w-md" :title="pm.keterangan" x-text="'&ldquo;' + pm.keterangan + '&rdquo;'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Hidden inputs for backend form submission -->
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][id]'" :value="pm.id">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][jenis]'" :value="pm.jenis">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][person_id]'" :value="pm.person_id || ''">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][lembaga_id]'" :value="pm.lembaga_id || ''">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][nama]'" :value="pm.summary?.nama || pm.nama || ''">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][nik]'" :value="pm.nik || ''">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][program_id]'" :value="pm.program_id || ''">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][subprogram_id]'" :value="pm.subprogram_id || ''">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][asnaf_id]'" :value="pm.asnaf_id || ''">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][pekerjaan]'" :value="pm.pekerjaan || ''">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][telepon]'" :value="pm.telepon || ''">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][alamat]'" :value="pm.summary?.alamat || pm.alamat || ''">
                    <input type="hidden" :name="'penerima_manfaat[' + index + '][keterangan]'" :value="pm.keterangan || ''">

                    <!-- Fallback hidden inputs for legacy mustahiks payload -->
                    <input type="hidden" :name="'mustahiks[' + index + '][id]'" :value="pm.id">
                    <input type="hidden" :name="'mustahiks[' + index + '][jenis]'" :value="pm.jenis">
                    <input type="hidden" :name="'mustahiks[' + index + '][person_id]'" :value="pm.person_id || ''">
                    <input type="hidden" :name="'mustahiks[' + index + '][lembaga_id]'" :value="pm.lembaga_id || ''">
                    <input type="hidden" :name="'mustahiks[' + index + '][nama]'" :value="pm.summary?.nama || pm.nama || ''">
                    <input type="hidden" :name="'mustahiks[' + index + '][nik]'" :value="pm.nik || ''">
                    <input type="hidden" :name="'mustahiks[' + index + '][program_id]'" :value="pm.program_id || ''">
                    <input type="hidden" :name="'mustahiks[' + index + '][subprogram_id]'" :value="pm.subprogram_id || ''">
                    <input type="hidden" :name="'mustahiks[' + index + '][asnaf_id]'" :value="pm.asnaf_id || ''">
                    <input type="hidden" :name="'mustahiks[' + index + '][pekerjaan]'" :value="pm.pekerjaan || ''">
                    <input type="hidden" :name="'mustahiks[' + index + '][telepon]'" :value="pm.telepon || ''">
                    <input type="hidden" :name="'mustahiks[' + index + '][alamat]'" :value="pm.summary?.alamat || pm.alamat || ''">
                    <input type="hidden" :name="'mustahiks[' + index + '][keterangan]'" :value="pm.keterangan || ''">
                    <input type="hidden" :name="'mustahiks[' + index + '][jumlah_jiwa]'" :value="pm.jumlah_jiwa || 1">
                    <input type="hidden" :name="'mustahiks[' + index + '][jumlah_kk]'" :value="pm.jumlah_kk || 1">
                </div>
            </template>

            <!-- Empty State -->
            <div x-show="penerimaManfaat.length === 0" class="p-8 text-center border-2 border-dashed border-slate-200/80 rounded-2xl bg-slate-50/50">
                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">Belum ada penerima manfaat</p>
                <p class="text-xs text-slate-400 mt-1">Klik "Tambah Penerima Manfaat" atau aktifkan toggle di atas</p>
            </div>
        </div>

        <!-- Modal Edit Program & Data Singkat (Client Mode) -->
        <div x-show="clientModalOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div x-show="clientModalOpen" @click="clientModalOpen = false" class="fixed inset-0" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200 text-xs sm:text-sm z-10">
                <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="font-bold text-base text-slate-800">Alokasi Program & Asnaf Penerima</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tentukan program BAZNAS dan klasifikasi mustahik</p>
                    </div>
                    <button type="button" @click="clientModalOpen = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Ringkasan Singkat Identitas Penerima -->
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                              :class="clientModalData.jenis === 'perorangan' ? 'bg-emerald-100 text-[#118243]' : 'bg-blue-100 text-blue-700'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="clientModalData.jenis === 'perorangan' ? 'bg-[#118243]' : 'bg-blue-600'"></span>
                            <span x-text="clientModalData.jenis === 'perorangan' ? 'Penerima Perorangan' : 'Penerima Lembaga'"></span>
                        </span>
                    </div>
                    <div>
                        <div class="font-bold text-slate-800 text-sm" x-text="clientModalData.nama || 'Nama Penerima'"></div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5" x-text="clientModalData.nikOrKetua"></div>
                        <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5" x-text="clientModalData.alamat"></div>
                    </div>
                </div>

                <div class="space-y-3.5">
                    <!-- Program & Subprogram -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Program BAZNAS <span class="text-red-500">*</span></label>
                            <select x-model="clientModalData.program_id" @change="onClientModalProgramChange()" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-2xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="">-- Pilih Program --</option>
                                @foreach($programs as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Sub Program</label>
                            <select x-model="clientModalData.subprogram_id" :disabled="!clientModalData.program_id" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-2xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all disabled:bg-slate-100 disabled:text-slate-400 disabled:shadow-none">
                                <option value="" x-text="clientModalData.program_id ? '-- Pilih Sub Program --' : '-- Pilih Program Dulu --'"></option>
                                <template x-for="sp in (typeof getSubprograms === 'function' ? getSubprograms(clientModalData.program_id) : [])" :key="sp.id">
                                    <option :value="sp.id" x-text="sp.nama"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Asnaf -->
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">
                            Golongan Asnaf
                            <span x-show="clientModalData.jenis === 'perorangan'" class="text-red-500">*</span>
                            <span x-show="clientModalData.jenis === 'lembaga'" class="text-slate-400 font-normal">(Opsional)</span>
                        </label>
                        <select x-model="clientModalData.asnaf_id" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-2xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                            <option value="">-- Pilih Asnaf --</option>
                            @foreach($asnafs as $asn)
                                <option value="{{ $asn->id }}">{{ $asn->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Pekerjaan & Telepon -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div x-show="clientModalData.jenis === 'perorangan'">
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Pekerjaan</label>
                            <input type="text"
                                   x-model="clientModalData.pekerjaan"
                                   placeholder="Pekerjaan saat ini..."
                                   class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-2xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>
                        <div :class="clientModalData.jenis === 'perorangan' ? '' : 'sm:col-span-2'">
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">No. Telepon / WA</label>
                            <input type="text"
                                   x-model="clientModalData.telepon"
                                   placeholder="08xxxxxxxxxx"
                                   class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-2xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>
                    </div>

                    <!-- Keterangan Khusus -->
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Catatan / Keterangan Bantuan</label>
                        <input type="text"
                               x-model="clientModalData.keterangan"
                               placeholder="Catatan peruntukan bantuan..."
                               class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-2xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="clientModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-semibold text-xs rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="button" @click="saveClientModal()" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-semibold text-xs rounded-xl shadow-2xs transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- ======================================================== -->
    <!-- SERVER MODE (SHOW / DETAIL VIEW)                        -->
    <!-- ======================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 space-y-4">
        <!-- Container Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
                <span class="text-xs font-semibold tracking-wider text-slate-400 uppercase block mb-0.5">
                    RINCIAN PENERIMA MANFAAT
                </span>
                <h3 class="text-sm sm:text-base font-semibold text-slate-800">{{ $title }}</h3>
            </div>
            @if(!$isLocked)
            <button type="button" @click="showAddPenerimaSelector = true" class="bg-[#118243]/10 text-[#118243] hover:bg-[#118243]/20 font-bold px-3 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition-colors self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Penerima Manfaat
            </button>
            @endif
        </div>

        <!-- Card List (Unified Beneficiary Cards) -->
        <div class="space-y-3.5">
            @forelse($pengajuan->pengajuanMustahiks as $pm)
            @php
                $pmHasDisposisi = $pm->disposisi !== null || ($pm->nominal > 0 && $pm->sumber_dana_id !== null) || $isLocked;
                $isLembaga = $pm->mustahik?->lembaga_id || (!$pm->mustahik?->person_id && $pm->mustahik?->jenis_mustahik === 'lembaga');
                $nama = $pm->mustahik?->person?->nama ?? $pm->mustahik?->lembaga?->nama ?? $pm->nama ?? '-';
                $nikOrKetua = $isLembaga 
                    ? ($pm->mustahik?->lembaga?->ketua_nama ? 'Ketua: ' . $pm->mustahik->lembaga->ketua_nama : ($pm->nik ?? ($pm->mustahik?->lembaga?->no_registrasi ? 'Reg: ' . $pm->mustahik->lembaga->no_registrasi : '-')))
                    : ($pm->mustahik?->person?->nik ?? $pm->nik ?? '-');
                $asnafNama = $pm->asnaf?->nama ?? 'Fakir / Miskin';
                $telepon = $pm->mustahik?->person?->telepon ?? $pm->mustahik?->lembaga?->telepon ?? $pm->telepon ?? '-';
                $pekerjaan = $isLembaga
                    ? ($pm->mustahik?->lembaga?->bidang_kegiatan ?? '-')
                    : ($pm->mustahik?->person?->pekerjaan ?? $pm->pekerjaan ?? '-');
                $alamat = $pm->mustahik?->person?->alamat ?? $pm->mustahik?->lembaga?->alamat ?? $pm->alamat ?? '-';
            @endphp
            <div class="p-4 sm:p-5 rounded-2xl border border-slate-200/90 bg-white hover:border-slate-300 transition-all space-y-3.5 shadow-2xs">
                <!-- Row 1: Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center shrink-0">#{{ $loop->iteration }}</span>
                        <span class="font-bold text-sm sm:text-base text-slate-800">{{ $nama }}</span>
                        @if($isLembaga)
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">Lembaga</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Perorangan</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2.5 self-end sm:self-auto">
                        @if($pm->nominal > 0)
                            <div class="text-right">
                                <div class="font-bold text-emerald-700 text-xs sm:text-sm">Rp {{ number_format($pm->nominal, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $pm->sumberDana?->nama ?? 'Kas Dana' }}</div>
                            </div>
                        @elseif($pm->bentuk_bantuan === 'Barang')
                            <div class="text-right">
                                <div class="font-bold text-emerald-700 text-xs sm:text-sm">{{ $pm->nama_barang }}</div>
                                <div class="text-[10px] text-slate-400">{{ $pm->jumlah_barang }} {{ $pm->satuan_barang }}</div>
                            </div>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-500">
                                Menunggu Disposisi
                            </span>
                        @endif

                        @if(!$pmHasDisposisi)
                            <div class="flex items-center gap-1 pl-2 border-l border-slate-100">
                                <button type="button" @click="openEditMustahik({{ $pm->id }})" class="px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5 shadow-2xs" title="Edit Data Penerima">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('pengajuan.mustahik.destroy', ['pengajuanId' => $pengajuan->id, 'mustahikId' => $pm->id]) }}" data-confirm="Hapus mustahik dari pengajuan?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Penerima">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-500">
                                Terkunci
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Row 2: Biodata Grid -->
                <div class="bg-slate-50/70 rounded-xl p-3 sm:p-3.5 border border-slate-200/70 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-400 mb-0.5 uppercase text-[10px] tracking-wider">{{ $isLembaga ? 'Ketua / No. Reg' : 'NIK' }}</label>
                        <div class="font-medium text-slate-700 font-mono text-xs">{{ $nikOrKetua }}</div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-400 mb-0.5 uppercase text-[10px] tracking-wider">Golongan Asnaf</label>
                        <div class="font-medium text-xs">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md font-medium bg-slate-100 text-slate-700">{{ $asnafNama }}</span>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-400 mb-0.5 uppercase text-[10px] tracking-wider">No. Telepon / WA</label>
                        <div class="font-medium text-slate-700 font-mono text-xs">{{ $telepon }}</div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-400 mb-0.5 uppercase text-[10px] tracking-wider">{{ $isLembaga ? 'Bidang Kegiatan' : 'Pekerjaan' }}</label>
                        <div class="font-medium text-slate-700 text-xs">{{ $pekerjaan }}</div>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-4 pt-1.5 border-t border-slate-200/50">
                        <label class="block font-semibold text-slate-400 mb-0.5 uppercase text-[10px] tracking-wider">Alamat Domisili</label>
                        <div class="text-slate-600 text-xs line-clamp-2">{{ $alamat }}</div>
                    </div>
                </div>

                <!-- Row 3: Program & Catatan Bantuan Bar -->
                <div class="flex items-center justify-between gap-3 flex-wrap pt-0.5 text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Program:</span>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 border border-emerald-200/80 text-xs font-semibold text-emerald-800">
                            <svg class="w-3.5 h-3.5 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            <span>{{ $pm->program?->nama ?? 'Program BAZNAS' }}</span>
                            @if($pm->subprogram?->nama)
                                <span class="inline-flex items-center gap-1">
                                    <span class="text-emerald-400 font-normal">/</span>
                                    <span class="text-emerald-700 font-normal">{{ $pm->subprogram->nama }}</span>
                                </span>
                            @endif
                        </div>
                        @if(!empty($pm->keterangan))
                            <span class="text-slate-300">•</span>
                            <span class="text-xs text-slate-500 italic truncate max-w-xs sm:max-w-md" title="{{ $pm->keterangan }}">&ldquo;{{ $pm->keterangan }}&rdquo;</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center border-2 border-dashed border-slate-200/80 rounded-2xl bg-slate-50/50">
                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700">Belum ada penerima manfaat</p>
                <p class="text-xs text-slate-400 mt-1">Belum ada mustahik yang ditambahkan pada pengajuan ini</p>
            </div>
            @endforelse
        </div>
    </div>
@endif

<!-- ======================================================== -->
<!-- MODALS SECTION (SHARED & MODE-SPECIFIC)                  -->
<!-- ======================================================== -->

@if($mode === 'client' || !$isLocked)
    <!-- Type Selector Modal: Pilih Perorangan / Lembaga -->
    <div x-show="showAddPenerimaSelector" x-cloak style="display: none;" x-transition class="fixed inset-0 z-50 overflow-y-auto">
        <div x-show="showAddPenerimaSelector" x-cloak style="display: none;" @click="closeAddPenerimaSelector" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
        <div class="relative z-50 max-w-md w-full mx-auto my-8 p-4">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">Tambah Penerima Manfaat</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih jenis entitas penerima bantuan</p>
                    </div>
                    <button type="button" @click="closeAddPenerimaSelector" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-5 space-y-3">
                    <button type="button"
                            @click="openPersonModal(); closeAddPenerimaSelector()"
                            class="w-full p-4 text-left rounded-xl border border-slate-200 hover:border-[#118243] hover:bg-emerald-50/40 transition-colors flex items-center gap-3.5 group">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm group-hover:text-[#118243] transition-colors">Perorangan</div>
                            <div class="text-xs text-slate-500">Cari data warga terdaftar atau input baru</div>
                        </div>
                    </button>
                    <button type="button"
                            @click="openLembagaModal(); closeAddPenerimaSelector()"
                            class="w-full p-4 text-left rounded-xl border border-slate-200 hover:border-[#118243] hover:bg-emerald-50/40 transition-colors flex items-center gap-3.5 group">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm group-hover:text-[#118243] transition-colors">Lembaga</div>
                            <div class="text-xs text-slate-500">Cari data lembaga terdaftar + pengurus</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cari-Tambah Modals -->
    @include('pengajuan.umum.components.cari-tambah-person', [
        'show' => false,
        'onClose' => 'closePersonModal()',
    ])
    @include('pengajuan.umum.components.cari-tambah-lembaga', [
        'show' => false,
        'onClose' => 'closeLembagaModal()',
    ])
@endif

@if($mode === 'client')
    <!-- Edit Modal Biodata Lengkap (Client Mode) -->
    @include('pengajuan.umum.components.edit-penerima-modal', [
        'show' => false,
        'penerima' => [],
        'index' => 0,
        'programs' => $programs,
        'subprograms' => $subprograms,
        'provinsis' => $provinsis,
        'onClose' => 'closeEditModal()',
    ])

    <!-- Event Listeners for Modal Custom Events (Client Mode) -->
    <div @person-selected.window="selectPersonFromModal($event.detail)" style="display: none;"></div>
    <div @lembaga-selected.window="selectLembagaFromModal($event.detail)" style="display: none;"></div>
    <div @penerima-updated.window="saveEdit($event.detail)" style="display: none;"></div>
@elseif(!$isLocked)
    <!-- Event Listeners for Modal Custom Events (Server Mode) -->
    <div @person-selected.window="onPersonSelected($event.detail)" style="display: none;"></div>
    <div @lembaga-selected.window="onLembagaSelected($event.detail)" style="display: none;"></div>

    <!-- MODAL TAMBAH MUSTAHIK (SERVER MODE) -->
    <div x-show="showMustahikModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-xl w-full p-6 space-y-4 shadow-2xl border border-slate-200 text-xs sm:text-sm">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-base text-slate-800">Lengkapi Data Penerima Manfaat</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tentukan program dan klasifikasi bantuan</p>
                </div>
                <button type="button" @click="showMustahikModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('pengajuan.mustahik.store', $pengajuan->id) }}" class="space-y-4">
                @csrf
                <!-- Hidden Identitas Terpilih -->
                <input type="hidden" name="nama" :value="modal_nama">
                <input type="hidden" name="person_id" :value="selected_person_id || modal_person_id">
                <input type="hidden" name="lembaga_id" :value="selected_lembaga_id || modal_lembaga_id">
                <input type="hidden" name="nik" :value="modal_nik">

                <!-- Ringkasan Entitas Terpilih -->
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                              :class="(modal_jenis || modal_entity_type) === 'perorangan' ? 'bg-emerald-100 text-[#118243]' : 'bg-blue-100 text-blue-700'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="(modal_jenis || modal_entity_type) === 'perorangan' ? 'bg-[#118243]' : 'bg-blue-600'"></span>
                            <span x-text="(modal_jenis || modal_entity_type) === 'perorangan' ? 'Penerima Perorangan' : 'Penerima Lembaga'"></span>
                        </span>
                        <button type="button" @click="showMustahikModal = false; showAddPenerimaSelector = true" class="text-xs font-semibold text-[#118243] hover:text-[#0d6835] hover:underline flex items-center gap-1 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Ganti Pilihan
                        </button>
                    </div>

                    <div>
                        <div class="font-bold text-slate-800 text-sm sm:text-base" x-text="modal_nama || 'Nama Penerima'"></div>
                        
                        <!-- Detail Perorangan -->
                        <template x-if="(modal_jenis || modal_entity_type) === 'perorangan' && modal_entity_summary">
                            <div class="mt-1 space-y-0.5 text-xs text-slate-600">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-slate-400">NIK:</span>
                                    <span class="font-mono font-medium text-slate-700" x-text="modal_nik || modal_entity_summary.nik || '-'"></span>
                                    <template x-if="modal_entity_summary.telepon && modal_entity_summary.telepon !== '-'">
                                        <span class="text-slate-400">• Telp: <span class="text-slate-700 font-medium" x-text="modal_entity_summary.telepon"></span></span>
                                    </template>
                                </div>
                                <template x-if="modal_alamat || (modal_entity_summary.alamat && modal_entity_summary.alamat !== '-')">
                                    <div class="text-[11px] text-slate-500 line-clamp-1" x-text="'Alamat: ' + (modal_alamat || modal_entity_summary.alamat)"></div>
                                </template>
                            </div>
                        </template>

                        <!-- Detail Lembaga -->
                        <template x-if="(modal_jenis || modal_entity_type) === 'lembaga' && modal_entity_summary">
                            <div class="mt-1 space-y-0.5 text-xs text-slate-600">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-slate-400">Ketua:</span>
                                    <span class="font-medium text-slate-700" x-text="modal_entity_summary.ketua_nama || '-'"></span>
                                    <template x-if="modal_entity_summary.ketua_nik && modal_entity_summary.ketua_nik !== '-'">
                                        <span class="text-slate-400">(NIK: <span class="font-mono" x-text="modal_entity_summary.ketua_nik"></span>)</span>
                                    </template>
                                </div>
                                <template x-if="modal_alamat || (modal_entity_summary.alamat && modal_entity_summary.alamat !== '-')">
                                    <div class="text-[11px] text-slate-500 line-clamp-1" x-text="'Alamat: ' + (modal_alamat || modal_entity_summary.alamat)"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Program BAZNAS <span class="text-red-500">*</span></label>
                        <select name="program_id" x-model="modal_program_id" @change="onModalProgramChange()" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                            <option value="">-- Pilih Program --</option>
                            <template x-for="p in programs" :key="p.id">
                                <option :value="p.id" x-text="p.nama"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Sub Program</label>
                        <select name="subprogram_id" x-model="modal_subprogram_id" :disabled="!modal_program_id" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all disabled:bg-slate-100 disabled:text-slate-400 disabled:shadow-none">
                            <option value="" x-text="modal_program_id ? '-- Pilih Sub Program --' : '-- Pilih Program Dulu --'"></option>
                            <template x-for="sp in getSubprograms(modal_program_id)" :key="sp.id">
                                <option :value="sp.id" x-text="sp.nama"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">
                        Golongan Asnaf
                        <span x-show="(modal_jenis || modal_entity_type) === 'perorangan'" class="text-red-500">*</span>
                        <span x-show="(modal_jenis || modal_entity_type) === 'lembaga'" class="text-slate-400 text-xs font-normal">(Opsional untuk Lembaga)</span>
                    </label>
                    <select name="asnaf_id" x-model="modal_asnaf_id" :required="(modal_jenis || modal_entity_type) === 'perorangan'" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                        <option value="">-- Pilih Asnaf --</option>
                        @foreach($asnafs as $as)
                            <option value="{{ $as->id }}">{{ $as->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Keterangan Bantuan <span class="text-slate-400 text-xs font-normal">(Opsional)</span></label>
                    <input type="text" name="keterangan" x-model="modal_keterangan" placeholder="Keterangan atau peruntukan bantuan..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showMustahikModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-medium rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl shadow-2xs transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Simpan Penerima Manfaat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT MUSTAHIK (SERVER MODE) -->
    <div x-show="showEditMustahikModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div x-show="showEditMustahikModal" @click="showEditMustahikModal = false" class="fixed inset-0" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
        <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200 text-xs sm:text-sm z-10">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-base text-slate-800">Edit Data Penerima Manfaat</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Alokasi Program &amp; Asnaf Penerima Manfaat</p>
                </div>
                <button type="button" @click="showEditMustahikModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Ringkasan Singkat Identitas Penerima (Read-Only) -->
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/80 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                          :class="(edit_jenis === 'Lembaga' || edit_jenis === 'lembaga') ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-[#118243]'">
                        <span class="w-1.5 h-1.5 rounded-full" :class="(edit_jenis === 'Lembaga' || edit_jenis === 'lembaga') ? 'bg-blue-600' : 'bg-[#118243]'"></span>
                        <span x-text="(edit_jenis === 'Lembaga' || edit_jenis === 'lembaga') ? 'Penerima Lembaga' : 'Penerima Individu'"></span>
                    </span>
                </div>
                <div>
                    <div class="font-bold text-slate-800 text-sm" x-text="edit_nama || 'Nama Penerima'"></div>
                    <div class="text-xs text-slate-500 font-mono mt-0.5" x-text="'NIK/Identitas: ' + edit_nik_or_ketua"></div>
                    <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5" x-text="edit_alamat"></div>
                </div>
            </div>

            <form method="POST" :action="'{{ url('pengajuan') }}/' + {{ $pengajuan->id }} + '/mustahik/' + (edit_id || edit_mustahik_id)" class="space-y-3.5">
                @csrf
                @method('PUT')

                <!-- Hidden inputs agar lolos validasi controller -->
                <input type="hidden" name="nama" :value="edit_nama">
                <input type="hidden" name="nik" :value="edit_nik">

                <!-- Program & Subprogram -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Program BAZNAS <span class="text-red-500">*</span></label>
                        <select name="program_id" x-model="edit_program_id" @change="onEditProgramChange()" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-2xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                            <option value="">-- Pilih Program --</option>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Sub Program</label>
                        <select name="sub_program_id" x-model="edit_sub_program_id" :disabled="!edit_program_id" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-2xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all disabled:bg-slate-100 disabled:text-slate-400 disabled:shadow-none">
                            <option value="" x-text="edit_program_id ? '-- Pilih Sub Program --' : '-- Pilih Program Dulu --'"></option>
                            <template x-for="sp in (typeof getSubprograms === 'function' ? getSubprograms(edit_program_id) : [])" :key="sp.id">
                                <option :value="sp.id" x-text="sp.nama" :selected="String(sp.id) === String(edit_sub_program_id)"></option>
                            </template>
                        </select>
                        <input type="hidden" name="subprogram_id" :value="edit_sub_program_id">
                    </div>
                </div>

                <!-- Golongan Asnaf -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">
                        Golongan Asnaf
                        <span x-show="edit_jenis !== 'Lembaga' && edit_jenis !== 'lembaga'" class="text-red-500">*</span>
                        <span x-show="edit_jenis === 'Lembaga' || edit_jenis === 'lembaga'" class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <select name="asnaf" x-model="edit_asnaf" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-2xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        <option value="">-- Pilih Asnaf --</option>
                        @foreach($asnafs as $asn)
                            <option value="{{ $asn->id }}">{{ $asn->nama }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="asnaf_id" :value="edit_asnaf">
                </div>

                <!-- Keterangan Khusus -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Catatan / Keterangan Bantuan <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="keterangan" x-model="edit_keterangan" placeholder="Catatan peruntukan bantuan..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-2xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditMustahikModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-semibold text-xs rounded-xl hover:bg-slate-50 transition-colors">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-semibold text-xs rounded-xl shadow-2xs transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
