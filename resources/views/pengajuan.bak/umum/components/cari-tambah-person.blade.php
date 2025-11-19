<div x-data="personModalController()" x-ref="personModal" x-show="isOpen" @open-person-modal.window="openModal()" @close-person-modal.window="closeModal()" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div x-show="isOpen" @click="closeModal()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <!-- Modal -->
    <div class="relative z-50 max-w-2xl w-full mx-auto my-8 sm:my-16 p-4 sm:p-6">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sticky top-0 bg-white z-10">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    Cari / Tambah Penerima Perorangan
                </h3>
                <button type="button" @click="onClose" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
                <!-- Search Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Cari Data Orang Terdaftar (NIK / Nama)
                        </label>
                    </div>

                    <div class="relative">
                        <input
                            type="text"
                            x-model="personSearchQuery"
                            @input.debounce.250ms="searchPeopleInModal()"
                            placeholder="Ketik NIK (16 digit) atau Nama untuk cek database..."
                            class="w-full rounded-xl border border-slate-300 bg-white py-2.5 sm:py-3 pl-10 pr-10 text-sm focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all shadow-xs">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>

                        <div x-show="isSearchingPersonModal" class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="animate-spin h-4 w-4 text-[#118243]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Search Results Dropdown -->
                    <div x-show="personSearchResults.length > 0" @click.away="personSearchResults = []"
                         class="max-h-56 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg divide-y divide-slate-100 z-20 relative mt-1">
                        <template x-for="p in personSearchResults" :key="p.id">
                            <div @click="selectPersonInModal(p)" class="p-3 hover:bg-[#eaf5ef] cursor-pointer transition-colors text-sm flex items-center justify-between group">
                                <div>
                                    <div class="font-bold text-slate-900 group-hover:text-[#118243]" x-text="p.nama"></div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        NIK: <span class="font-semibold text-slate-700" x-text="p.nik || 'Tanpa NIK'"></span>
                                        <span x-show="p.nomor_kk"> • KK: <span x-text="p.nomor_kk"></span></span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5" x-text="p.alamat_lengkap || p.alamat || '-'"></div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-[#118243] text-white shadow-xs group-hover:bg-[#0d6835]">
                                    Pilih
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- Not Found State -->
                    <div x-show="personNotFound && personSearchQuery.trim().length >= 2 && !isSearchingPersonModal"
                         x-transition
                         class="p-3.5 bg-blue-50/90 border border-blue-200 rounded-xl text-sm text-blue-950 space-y-3">
                        <div class="flex items-start gap-2.5">
                            <div class="w-6 h-6 rounded-full bg-blue-200 text-blue-800 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 space-y-0.5">
                                <div class="font-bold text-blue-900">Data Tidak Ditemukan di Database</div>
                                <p class="text-xs text-blue-800 leading-relaxed">
                                    Kata kunci <strong class="underline decoration-blue-400 font-semibold" x-text="'&ldquo;' + personSearchQuery + '&rdquo;'"></strong> belum pernah terdaftar sebelumnya.
                                </p>
                            </div>
                        </div>
                        <button type="button"
                                @click="showPersonForm = true; personSearchQuery = ''; personNotFound = false"
                                class="w-full px-3 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-lg text-sm shadow-xs inline-flex items-center justify-center gap-2 transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Input Data Perorangan Baru
                        </button>
                    </div>
                </div>

                <!-- Person Form (when not found or manual entry) -->
                <div x-show="showPersonForm" x-transition class="space-y-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-100 text-[#118243] flex items-center justify-center text-xs font-bold">+</span>
                            Formulir Data Perorangan Baru
                        </h4>
                        <button type="button"
                                @click="showPersonForm = false; resetPersonForm()"
                                class="text-xs text-slate-500 hover:text-slate-700 font-semibold">
                            Kembali ke Pencarian
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <!-- NIK -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="font-bold text-slate-700 uppercase text-xs">NIK (16 Digit) <span class="text-red-500">*</span></label>
                                <span class="text-[10px] font-semibold inline-flex items-center gap-1"
                                      :class="personForm.nik.length === 16 ? 'text-emerald-600' : 'text-slate-400'">
                                    <template x-if="personForm.nik.length === 16">
                                        <svg class="w-3 h-3 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                    <span x-text="personForm.nik.length === 16 ? '16 Digit Pas' : (personForm.nik.length + '/16 digit')"></span>
                                </span>
                            </div>
                            <input
                                type="text"
                                x-model="personForm.nik"
                                inputmode="numeric"
                                maxlength="16"
                                placeholder="3301..."
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"
                                :class="personForm.nik.length === 16 ? 'border-emerald-500 bg-emerald-50/20' : ''">
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                x-model="personForm.nama"
                                placeholder="Nama lengkap sesuai KTP"
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Nomor KK -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Nomor KK</label>
                            <input
                                type="text"
                                x-model="personForm.nomor_kk"
                                inputmode="numeric"
                                maxlength="16"
                                placeholder="3301..."
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Jenis Kelamin -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Jenis Kelamin</label>
                            <select x-model="personForm.jenis_kelamin" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>

                        <!-- Telepon -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">No. WhatsApp / HP</label>
                            <input
                                type="text"
                                x-model="personForm.telepon"
                                inputmode="numeric"
                                placeholder="08123456789"
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Pekerjaan -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Pekerjaan</label>
                            <input
                                type="text"
                                x-model="personForm.pekerjaan"
                                placeholder="Contoh: Petani / Pedagang / Buruh"
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Tempat Lahir -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Tempat Lahir</label>
                            <input
                                type="text"
                                x-model="personForm.tempat_lahir"
                                placeholder="Tempat lahir"
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Tanggal Lahir -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Tanggal Lahir</label>
                            <input
                                type="date"
                                x-model="personForm.tanggal_lahir"
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Email</label>
                            <input
                                type="email"
                                x-model="personForm.email"
                                placeholder="email@contoh.com"
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                        </div>
                    </div>

                    <!-- Wilayah -->
                    <div class="space-y-3 pt-2 border-t border-slate-100">
                        <h5 class="font-bold text-slate-700 uppercase text-xs">Alamat Domisili</h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
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

                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Alamat Lengkap (Jl, No, RT/RW, Dusun)</label>
                                <textarea x-model="alamat" rows="2" placeholder="Contoh: Jl. Gatot Subroto No. 45, RT 02 / RW 03, Dusun Karanganyar" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center justify-end gap-2 pt-4 border-t border-slate-100">
                        <button type="button"
                                @click="showPersonForm = false; resetPersonForm()"
                                class="px-3 py-2 text-sm font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button type="button"
                                @click="submitPersonForm()"
                                :disabled="isSubmittingPerson"
                                class="px-4 py-2 text-sm font-bold text-white bg-[#118243] hover:bg-[#0d6835] rounded-lg shadow-xs inline-flex items-center gap-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="isSubmittingPerson">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Menyimpan...
                            </template>
                            <template x-if="!isSubmittingPerson">
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
function personModalController() {
    return {
        show: {{ $show ? 'true' : 'false' }},
        personSearchQuery: '',
        personSearchResults: [],
        personNotFound: false,
        isSearchingPersonModal: false,
        showPersonForm: false,
        isSubmittingPerson: false,
        personForm: {
            nik: '',
            nomor_kk: '',
            nama: '',
            jenis_kelamin: 'L',
            telepon: '',
            pekerjaan: '',
            tempat_lahir: '',
            tanggal_lahir: '',
            email: '',
        },

        // Wilayah selector state
        provinsis: @json($provinsis ?? []),
        selectedProvinsi: '',
        selectedKabupaten: '',
        selectedKecamatan: '',
        selectedKelurahan: '',
        kodePos: '',
        alamat: '',
        kabupatens: [],
        kecamatans: [],
        kelurahans: [],
        loading: { kabupaten: false, kecamatan: false, kelurahan: false },

        isOpen: false,
        openModal() { this.isOpen = true; },
        closeModal() { this.isOpen = false; this.personSearchQuery = ''; this.personSearchResults = []; this.personNotFound = false; this.showPersonForm = false; this.resetPersonForm(); },
        show() { this.openModal(); },
        close() { this.closeModal(); },
        onClose() { this.closeModal(); },

        async init() {
            // Initialize wilayah with default Jawa Tengah
            this.selectedProvinsi = '33';
            await this.onProvinsiChange();
            this.selectedKabupaten = '3301';
            await this.onKabupatenChange();
        },

        // Search People API
        async searchPeopleInModal() {
            const q = (this.personSearchQuery || '').trim();
            if (q.length < 2) {
                this.personSearchResults = [];
                this.personNotFound = false;
                return;
            }
            this.isSearchingPersonModal = true;
            this.personNotFound = false;
            try {
                const res = await fetch(`/api/search/people?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                this.personSearchResults = Array.isArray(data) ? data : [];
                this.personNotFound = this.personSearchResults.length === 0;
            } catch (e) {
                this.personSearchResults = [];
                this.personNotFound = true;
            } finally {
                this.isSearchingPersonModal = false;
            }
        },

        selectPersonInModal(p) {
            this.$dispatch('person-selected', {
                id: p.id,
                nik: p.nik,
                nomor_kk: p.nomor_kk,
                nama: p.nama,
                jenis_kelamin: p.jenis_kelamin,
                telepon: p.telepon,
                pekerjaan: p.pekerjaan,
                tempat_lahir: p.tempat_lahir,
                tanggal_lahir: p.tanggal_lahir,
                email: p.email,
                alamat: p.alamat,
                id_provinsi: p.id_provinsi,
                id_kabupaten: p.id_kabupaten,
                id_kecamatan: p.id_kecamatan,
                id_kelurahan: p.id_kelurahan,
                kode_pos: p.kode_pos,
                summary: {
                    nama: p.nama,
                    nikOrKetua: p.nik,
                    alamat: p.alamat_lengkap || p.alamat
                }
            });
            this.closeModal();
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

        // Submit new person
        async submitPersonForm() {
            const form = this.personForm;
            if (!form.nik || form.nik.length !== 16) {
                window.$alert('NIK harus 16 digit');
                return;
            }
            if (!form.nama.trim()) {
                window.$alert('Nama wajib diisi');
                return;
            }

            this.isSubmittingPerson = true;
            try {
                const payload = {
                    nik: form.nik,
                    nomor_kk: form.nomor_kk,
                    nama: form.nama,
                    jenis_kelamin: form.jenis_kelamin,
                    telepon: form.telepon,
                    pekerjaan: form.pekerjaan,
                    tempat_lahir: form.tempat_lahir,
                    tanggal_lahir: form.tanggal_lahir || null,
                    email: form.email,
                    alamat: this.alamat,
                    id_provinsi: this.selectedProvinsi,
                    id_kabupaten: this.selectedKabupaten,
                    id_kecamatan: this.selectedKecamatan,
                    id_kelurahan: this.selectedKelurahan,
                    kode_pos: this.kodePos,
                };

                const res = await fetch('/pengajuan/umum/person', {
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
                    throw new Error(err.message || 'Gagal menyimpan data');
                }

                const person = await res.json();

                this.$dispatch('person-selected', {
                    id: person.id,
                    nik: person.nik,
                    nomor_kk: person.nomor_kk,
                    nama: person.nama,
                    jenis_kelamin: person.jenis_kelamin,
                    telepon: person.telepon,
                    pekerjaan: person.pekerjaan,
                    tempat_lahir: person.tempat_lahir,
                    tanggal_lahir: person.tanggal_lahir,
                    email: person.email,
                    alamat: person.alamat,
                    id_provinsi: person.id_provinsi,
                    id_kabupaten: person.id_kabupaten,
                    id_kecamatan: person.id_kecamatan,
                    id_kelurahan: person.id_kelurahan,
                    kode_pos: person.kode_pos,
                    summary: {
                        nama: person.nama,
                        nikOrKetua: person.nik,
                        alamat: person.alamat_lengkap || person.alamat
                    }
                });

                this.closeModal();
            } catch (e) {
                window.$alert('Error: ' + e.message);
            } finally {
                this.isSubmittingPerson = false;
            }
        },

        resetPersonForm() {
            this.personForm = {
                nik: '', nomor_kk: '', nama: '', jenis_kelamin: 'L',
                telepon: '', pekerjaan: '', tempat_lahir: '', tanggal_lahir: '', email: '',
            };
            this.alamat = '';
            this.selectedProvinsi = '33';
            this.selectedKabupaten = '3301';
            this.selectedKecamatan = '';
            this.selectedKelurahan = '';
            this.kodePos = '';
        }
    };
}
</script>