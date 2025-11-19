<div x-data="lembagaModalController()" x-ref="lembagaModal" x-show="isOpen" @open-lembaga-modal.window="openModal()" @close-lembaga-modal.window="closeModal()" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div x-show="isOpen" @click="closeModal()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <!-- Modal -->
    <div class="relative z-50 max-w-4xl w-full mx-auto my-4 sm:my-8 p-4 sm:p-6">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sticky top-0 bg-white z-10">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </span>
                    Cari / Tambah Penerima Lembaga
                </h3>
                <button type="button" @click="onClose" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-5 space-y-4 max-h-[80vh] overflow-y-auto">
                <!-- Search Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Cari Data Lembaga Terdaftar (Nama)
                        </label>
                    </div>

                    <div class="relative">
                        <input
                            type="text"
                            x-model="lembagaSearchQuery"
                            @input.debounce.250ms="searchLembagaInModal()"
                            placeholder="Ketik Nama Lembaga untuk cek database..."
                            class="w-full rounded-xl border border-slate-300 bg-white py-2.5 sm:py-3 pl-10 pr-10 text-sm focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all shadow-xs">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <div x-show="isSearchingLembagaModal" class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="animate-spin h-4 w-4 text-[#118243]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Search Results Dropdown -->
                    <div x-show="lembagaSearchResults.length > 0" @click.away="lembagaSearchResults = []"
                         class="max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg divide-y divide-slate-100 z-20 relative mt-1">
                        <template x-for="l in lembagaSearchResults" :key="l.id">
                            <div @click="selectLembagaInModal(l)" class="p-3 hover:bg-[#eaf5ef] cursor-pointer transition-colors text-sm flex items-center justify-between group">
                                <div>
                                    <div class="font-bold text-slate-900 group-hover:text-[#118243]" x-text="l.nama"></div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        Jenis: <span class="font-semibold text-slate-700" x-text="l.jenis || '-'"></span>
                                        <span x-show="l.ketua_nama"> • Ketua: <span x-text="l.ketua_nama"></span></span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5" x-text="l.alamat || '-'"></div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-[#118243] text-white shadow-xs group-hover:bg-[#0d6835]">
                                    Pilih
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- Not Found State -->
                    <div x-show="lembagaNotFound && lembagaSearchQuery.trim().length >= 2 && !isSearchingLembagaModal"
                         x-transition
                         class="p-3.5 bg-blue-50/90 border border-blue-200 rounded-xl text-sm text-blue-950 space-y-3">
                        <div class="flex items-start gap-2.5">
                            <div class="w-6 h-6 rounded-full bg-blue-200 text-blue-800 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 space-y-0.5">
                                <div class="font-bold text-blue-900">Data Lembaga Tidak Ditemukan</div>
                                <p class="text-xs text-blue-800 leading-relaxed">
                                    Kata kunci <strong class="underline decoration-blue-400 font-semibold" x-text="'&ldquo;' + lembagaSearchQuery + '&rdquo;'"></strong> belum pernah terdaftar sebelumnya.
                                </p>
                            </div>
                        </div>
                        <button type="button"
                                @click="showLembagaForm = true; lembagaSearchQuery = ''; lembagaNotFound = false"
                                class="w-full px-3 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-lg text-sm shadow-xs inline-flex items-center justify-center gap-2 transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Input Data Lembaga Baru
                        </button>
                    </div>
                </div>

                <!-- Lembaga Form (when not found or manual entry) -->
                <div x-show="showLembagaForm" x-transition class="space-y-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-100 text-[#118243] flex items-center justify-center text-xs font-bold">+</span>
                            Formulir Data Lembaga Baru
                        </h4>
                        <button type="button"
                                @click="showLembagaForm = false; resetLembagaForm()"
                                class="text-xs text-slate-500 hover:text-slate-700 font-semibold">
                            Kembali ke Pencarian
                        </button>
                    </div>

                    <!-- Lembaga Basic Info -->
                    <div class="space-y-3 pt-2 border-t border-slate-100">
                        <h5 class="font-bold text-slate-700 uppercase text-xs">Identitas Lembaga</h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Nama Lembaga <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    x-model="lembagaForm.nama"
                                    placeholder="Nama lengkap lembaga"
                                    class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Jenis Lembaga <span class="text-red-500">*</span></label>
                                <select x-model="lembagaForm.jenis" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                    <option value="Masjid">Masjid</option>
                                    <option value="Musholla">Musholla</option>
                                    <option value="Pondok Pesantren">Pondok Pesantren</option>
                                    <option value="Panti Asuhan">Panti Asuhan</option>
                                    <option value="Sekolah">Sekolah</option>
                                    <option value="Yayasan Sosial">Yayasan Sosial</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">No. Kontak / WA</label>
                                <input
                                    type="text"
                                    x-model="lembagaForm.telepon"
                                    inputmode="numeric"
                                    placeholder="08123456789"
                                    class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Email</label>
                                <input
                                    type="email"
                                    x-model="lembagaForm.email"
                                    placeholder="email@contoh.com"
                                    class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Alamat Lembaga</label>
                                <textarea
                                    x-model="lembagaForm.alamat"
                                    rows="2"
                                    placeholder="Jl, No, RT/RW, Dusun..."
                                    class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Wilayah -->
                    <div class="space-y-3 pt-2 border-t border-slate-100">
                        <h5 class="font-bold text-slate-700 uppercase text-xs">Wilayah</h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm" x-data="wilayahSelector()">
                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Provinsi</label>
                                <select x-model="selectedProvinsi" @change="onProvinsiChange()" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                    <option value="">-- Pilih Provinsi --</option>
                                    <template x-for="p in provinsis" :key="p.id_wilayah">
                                        <option :value="p.id_wilayah" x-text="p.nama"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Kabupaten/Kota</label>
                                <select x-model="selectedKabupaten" @change="onKabupatenChange()" :disabled="!selectedProvinsi || loading.kabupaten" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                    <option value="">-- Pilih Kabupaten --</option>
                                    <template x-for="k in kabupatens" :key="k.id_wilayah">
                                        <option :value="k.id_wilayah" x-text="k.nama"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Kecamatan</label>
                                <select x-model="selectedKecamatan" @change="onKecamatanChange()" :disabled="!selectedKabupaten || loading.kecamatan" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                    <option value="">-- Pilih Kecamatan --</option>
                                    <template x-for="c in kecamatans" :key="c.id_wilayah">
                                        <option :value="c.id_wilayah" x-text="c.nama"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Desa/Kelurahan</label>
                                <select x-model="selectedKelurahan" @change="onKelurahanChange()" :disabled="!selectedKecamatan || loading.kelurahan" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                    <option value="">-- Pilih Desa/Kelurahan --</option>
                                    <template x-for="l in kelurahans" :key="l.id_wilayah">
                                        <option :value="l.id_wilayah" x-text="l.nama"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Kode Pos</label>
                                <input type="text" x-model="kodePos" placeholder="Kode Pos" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Pengurus Section - MINIMAL 1 KETUA WAJIB -->
                    <div class="space-y-3 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <h5 class="font-bold text-slate-700 uppercase text-xs flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Pengurus Lembaga <span class="text-red-500 text-xs">(Minimal 1 Ketua Wajib)</span>
                            </h5>
                            <span class="text-[11px] text-slate-500" x-text="pengurus.length + ' pengurus'"></span>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(p, pi) in pengurus" :key="pi">
                                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2" x-data="pengurusRowController({ index: pi, isKetua: pi === 0 })">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-xs text-slate-700 uppercase flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full" :class="pi === 0 ? 'bg-red-500' : 'bg-[#118243]'"></span>
                                                <span x-text="pi === 0 ? 'KETUA (Wajib)' : 'Pengurus #' + pi"></span>
                                                <span x-show="pi === 0" class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-100 text-red-700">WAJIB</span>
                                        </span>
                                        <button type="button"
                                                @click="removePengurus()"
                                                x-show="pengurus.length > 1"
                                                class="text-red-500 hover:text-red-700 text-xs font-bold inline-flex items-center gap-1 p-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                        <!-- NIK with Cari-Tambah -->
                                        <div class="sm:col-span-2">
                                            <div class="flex items-center justify-between mb-1">
                                                <label class="font-bold text-slate-700 uppercase text-[10px]">NIK <span class="text-red-500">*</span></label>
                                                <span class="text-[10px] font-semibold inline-flex items-center gap-1"
                                                      :class="form.nik.length === 16 ? 'text-emerald-600' : 'text-slate-400'">
                                                    <template x-if="form.nik.length === 16">
                                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    </template>
                                                    <span x-text="form.nik.length === 16 ? '16 Digit Pas' : (form.nik.length + '/16 digit')"></span>
                                                </span>
                                            </div>
                                            <div class="relative">
                                                <input
                                                    type="text"
                                                    x-model="form.nik"
                                                    inputmode="numeric"
                                                    maxlength="16"
                                                    @keydown.enter.prevent="searchPengurusByNik()"
                                                    @blur="searchPengurusByNik()"
                                                    placeholder="Ketik 16 digit NIK lalu Enter/Tab untuk cari-tambah..."
                                                    class="w-full rounded-xl border border-slate-300 bg-white p-2 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all pr-10"
                                                    :class="form.nik.length === 16 ? 'border-emerald-500 bg-emerald-50/20' : ''">
                                                <div x-show="isSearchingNik" class="absolute inset-y-0 right-0 flex items-center pr-2">
                                                    <svg class="animate-spin h-3.5 w-3.5 text-[#118243]" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div x-show="nikNotFound && form.nik.length >= 4 && !isSearchingNik"
                                                 x-transition
                                                 class="p-2 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-900 mt-1">
                                                NIK <strong x-text="form.nik"></strong> tidak ditemukan. Isi nama manual.
                                            </div>
                                        </div>

                                        <!-- Jabatan (locked for Ketua) -->
                                        <div>
                                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[10px]">Jabatan</label>
                                            <select x-model="form.jabatan"
                                                    :disabled="isKetua"
                                                    class="w-full rounded-xl border border-slate-300 bg-white p-2 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all disabled:bg-slate-100 disabled:text-slate-400">
                                                <option value="Ketua">Ketua</option>
                                                <option value="Sekretaris">Sekretaris</option>
                                                <option value="Bendahara">Bendahara</option>
                                                <option value="Anggota">Anggota</option>
                                            </select>
                                        </div>

                                        <!-- Nama -->
                                        <div class="sm:col-span-3">
                                            <label class="block font-bold text-slate-700 uppercase mb-1 text-[10px]">Nama Lengkap <span class="text-red-500">*</span></label>
                                            <input
                                                type="text"
                                                x-model="form.nama"
                                                placeholder="Nama lengkap"
                                                class="w-full rounded-xl border border-slate-300 bg-white p-2 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"
                                                :readonly="isSearchingNik">
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Add Pengurus Button -->
                            <button type="button"
                                    @click="addPengurus()"
                                    class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Pengurus
                            </button>
                        </div>

                        <!-- Validation Note -->
                        <div class="p-2 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-900 flex items-start gap-2">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <strong>Validasi:</strong> Minimal 1 pengurus (Ketua wajib). Setiap pengurus harus memiliki NIK dan Nama. NIK 16 digit otomatis dicek ke database (cari-tambah).
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button"
                                @click="showLembagaForm = false; resetLembagaForm()"
                                class="px-3 py-2 text-sm font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button type="button"
                                @click="submitLembagaForm()"
                                :disabled="isSubmittingLembaga || !validatePengurus()"
                                class="px-4 py-2 text-sm font-bold text-white bg-[#118243] hover:bg-[#0d6835] rounded-lg shadow-xs inline-flex items-center gap-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="isSubmittingLembaga">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Menyimpan...
                            </template>
                            <template x-if="!isSubmittingLembaga">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Simpan & Pilih
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function lembagaModalController() {
    return {
        show: {{ $show ? 'true' : 'false' }},
        lembagaSearchQuery: '',
        lembagaSearchResults: [],
        lembagaNotFound: false,
        isSearchingLembagaModal: false,
        showLembagaForm: false,
        isSubmittingLembaga: false,
        lembagaForm: {
            nama: '',
            jenis: 'Masjid',
            telepon: '',
            email: '',
            alamat: '',
        },

        // Wilayah state
        provinsis: @json($provinsis ?? []),
        selectedProvinsi: '',
        selectedKabupaten: '',
        selectedKecamatan: '',
        selectedKelurahan: '',
        kodePos: '',
        kabupatens: [],
        kecamatans: [],
        kelurahans: [],
        loading: { kabupaten: false, kecamatan: false, kelurahan: false },

        isOpen: false,
        openModal() { this.isOpen = true; },
        closeModal() { this.isOpen = false; this.lembagaSearchQuery = ''; this.lembagaSearchResults = []; this.lembagaNotFound = false; this.showLembagaForm = false; this.resetLembagaForm(); },
        show() { this.openModal(); },
        close() { this.closeModal(); },
        onClose() { this.closeModal(); },

        // Pengurus array - FIRST ONE IS KETUA (LOCKED)
        pengurus: [
            { nik: '', nama: '', jabatan: 'Ketua' }
        ],

        async init() {
            this.selectedProvinsi = '33';
            await this.onProvinsiChange();
            this.selectedKabupaten = '3301';
            await this.onKabupatenChange();
        },

        // Search Lembaga API
        async searchLembagaInModal() {
            const q = (this.lembagaSearchQuery || '').trim();
            if (q.length < 2) {
                this.lembagaSearchResults = [];
                this.lembagaNotFound = false;
                return;
            }
            this.isSearchingLembagaModal = true;
            this.lembagaNotFound = false;
            try {
                const res = await fetch(`/api/search/lembaga?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                this.lembagaSearchResults = Array.isArray(data) ? data : [];
                this.lembagaNotFound = this.lembagaSearchResults.length === 0;
            } catch (e) {
                this.lembagaSearchResults = [];
                this.lembagaNotFound = true;
            } finally {
                this.isSearchingLembagaModal = false;
            }
        },

        selectLembagaInModal(l) {
            const emitLembaga = (item) => {
                this.$dispatch('lembaga-selected', {
                    id: item.id,
                    nama: item.nama,
                    jenis: item.jenis,
                    ketua_nama: item.ketua_nama,
                    ketua_nik: item.ketua_nik,
                    telepon: item.telepon || item.handphone || '',
                    email: item.email || '',
                    alamat: item.alamat || '',
                    id_provinsi: item.id_provinsi || '',
                    id_kabupaten: item.id_kabupaten || '',
                    id_kecamatan: item.id_kecamatan || '',
                    id_kelurahan: item.id_kelurahan || '',
                    kode_pos: item.kode_pos || '',
                    pengurus: item.pengurus || [],
                    summary: {
                        nama: item.nama,
                        nikOrKetua: item.ketua_nama ? `Ketua: ${item.ketua_nama}` : (item.jenis || 'Lembaga'),
                        alamat: item.alamat || ''
                    }
                });
                this.closeModal();
            };

            fetch(`/api/search/lembaga-by-nama?q=${encodeURIComponent(l.nama)}`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(r => r.json())
                .then(data => {
                    const full = Array.isArray(data) ? (data.length > 0 ? data[0] : null) : data;
                    emitLembaga(full || l);
                })
                .catch(() => {
                    emitLembaga(l);
                });
        },

        // Wilayah methods
        async onProvinsiChange() {
            this.kabupatens = [];
            this.kecamatans = [];
            this.kelurahans = [];
            this.selectedKabupaten = '';
            this.selectedKecamatan = '';
            this.selectedKelurahan = '';
            if (!this.selectedProvinsi) return;
            this.loading.kabupaten = true;
            try {
                const res = await fetch(`/api/wilayah/kabupaten?provinsi_id=${this.selectedProvinsi}`);
                this.kabupatens = await res.json();
            } finally {
                this.loading.kabupaten = false;
            }
        },

        async onKabupatenChange() {
            this.kecamatans = [];
            this.kelurahans = [];
            this.selectedKecamatan = '';
            this.selectedKelurahan = '';
            if (!this.selectedKabupaten) return;
            this.loading.kecamatan = true;
            try {
                const res = await fetch(`/api/wilayah/kecamatan?kabupaten_id=${this.selectedKabupaten}`);
                this.kecamatans = await res.json();
            } finally {
                this.loading.kecamatan = false;
            }
        },

        async onKecamatanChange() {
            this.kelurahans = [];
            this.selectedKelurahan = '';
            if (!this.selectedKecamatan) return;
            this.loading.kelurahan = true;
            try {
                const res = await fetch(`/api/wilayah/kelurahan?kecamatan_id=${this.selectedKecamatan}`);
                this.kelurahans = await res.json();
            } finally {
                this.loading.kelurahan = false;
            }
        },

        async onKelurahanChange() {
            if (this.selectedKelurahan) {
                try {
                    const res = await fetch(`/api/wilayah/kode-pos?kelurahan_id=${this.selectedKelurahan}`);
                    const data = await res.json();
                    this.kodePos = data.kode_pos || '';
                } catch (e) {
                    this.kodePos = '';
                }
            }
        },

        // Pengurus Management
        addPengurus() {
            const jabatanOptions = ['Sekretaris', 'Bendahara', 'Anggota'];
            // Find unused jabatan
            const used = this.pengurus.map(p => p.jabatan);
            const available = jabatanOptions.filter(j => !used.includes(j));
            this.pengurus.push({
                nik: '',
                nama: '',
                jabatan: available.length > 0 ? available[0] : 'Anggota'
            });
        },

        removePengurus() {
            if (this.pengurus.length > 1) {
                this.pengurus.splice(this.$data.index, 1);
            }
        },

        validatePengurus() {
            // Check all pengurus have nik and nama
            for (const p of this.pengurus) {
                if (!p.nik || p.nik.length !== 16) return false;
                if (!p.nama.trim()) return false;
            }
            // Check exactly 1 Ketua
            const ketuaCount = this.pengurus.filter(p => p.jabatan === 'Ketua').length;
            if (ketuaCount !== 1) return false;
            return true;
        },

        // Cari-tambah for pengurus NIK
        async searchPengurusByNik() {
            const nik = (this.$data.form?.nik || '').trim();
            if (nik.length < 4) return;
            this.$data.isSearchingNik = true;
            try {
                const res = await fetch(`/api/search/person-by-nik?nik=${encodeURIComponent(nik)}`);
                const data = await res.json();
                if (data && data.nama) {
                    this.$data.form.nama = data.nama;
                    this.$data.nikNotFound = false;
                } else {
                    this.$data.nikNotFound = true;
                    this.$data.form.nama = '';
                }
            } catch (e) {
                this.$data.nikNotFound = true;
            } finally {
                this.$data.isSearchingNik = false;
            }
        },

        // Submit new lembaga + pengurus
        async submitLembagaForm() {
            const form = this.lembagaForm;
            if (!form.nama.trim()) {
                window.$alert('Nama lembaga wajib diisi');
                return;
            }
            if (!this.validatePengurus()) {
                window.$alert('Validasi pengurus gagal: minimal 1 Ketua wajib, semua pengurus harus punya NIK 16 digit dan Nama');
                return;
            }

            this.isSubmittingLembaga = true;
            try {
                const payload = {
                    nama: form.nama,
                    jenis: form.jenis,
                    telepon: form.telepon,
                    email: form.email,
                    alamat: this.$data.alamat || '',
                    id_provinsi: this.selectedProvinsi,
                    id_kabupaten: this.selectedKabupaten,
                    id_kecamatan: this.selectedKecamatan,
                    id_kelurahan: this.selectedKelurahan,
                    kode_pos: this.kodePos,
                    pengurus: this.pengurus.map(p => ({
                        nik: p.nik,
                        nama: p.nama,
                        jabatan: p.jabatan
                    }))
                };

                const res = await fetch('/pengajuan/umum/lembaga', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    const err = await res.json();
                    throw new Error(err.message || 'Gagal menyimpan lembaga');
                }

                const lembaga = await res.json();

                this.$dispatch('lembaga-selected', {
                    id: lembaga.id,
                    nama: lembaga.nama,
                    jenis: lembaga.jenis,
                    ketua_nama: lembaga.ketua_nama,
                    ketua_nik: lembaga.ketua_nik,
                    telepon: lembaga.telepon,
                    email: lembaga.email,
                    alamat: lembaga.alamat,
                    id_provinsi: lembaga.id_provinsi,
                    id_kabupaten: lembaga.id_kabupaten,
                    id_kecamatan: lembaga.id_kecamatan,
                    id_kelurahan: lembaga.id_kelurahan,
                    kode_pos: lembaga.kode_pos,
                    pengurus: lembaga.pengurus || [],
                    summary: {
                        nama: lembaga.nama,
                        nikOrKetua: lembaga.ketua_nama || '-',
                        alamat: lembaga.alamat
                    }
                });

                this.closeModal();
            } catch (e) {
                window.$alert('Error: ' + e.message);
            } finally {
                this.isSubmittingLembaga = false;
            }
        },

        resetLembagaForm() {
            this.lembagaForm = { nama: '', jenis: 'Masjid', telepon: '', email: '', alamat: '' };
            this.pengurus = [{ nik: '', nama: '', jabatan: 'Ketua' }];
            this.alamat = '';
            this.selectedProvinsi = '33';
            this.selectedKabupaten = '3301';
            this.selectedKecamatan = '';
            this.selectedKelurahan = '';
            this.kodePos = '';
        }
    };
}

function pengurusRowController({ index, isKetua }) {
    return {
        index,
        isKetua,
        form: { nik: '', nama: '', jabatan: isKetua ? 'Ketua' : 'Anggota' },
        isSearchingNik: false,
        nikNotFound: false,
    };
}
</script>