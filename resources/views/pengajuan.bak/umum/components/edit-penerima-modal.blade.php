<div x-data="editPenerimaModalController()"
     x-ref="editModal"
     @open-edit-modal.window="show($event.detail.penerima, $event.detail.index)"
     @close-edit-modal.window="close()"
     x-show="isOpen"
     x-cloak
     style="display: none;"
     x-transition
     class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div x-show="isOpen" @click="onClose" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <!-- Modal -->
    <div class="relative z-50 max-w-3xl w-full mx-auto my-4 sm:my-8 p-4 sm:p-6">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sticky top-0 bg-white z-10">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                          :class="(penerima && penerima.jenis === 'perorangan') ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700'">
                        <template x-if="penerima.jenis === 'perorangan'">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </template>
                        <template x-if="penerima.jenis === 'lembaga'">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </template>
                    </span>
                    <span x-text="penerima.jenis === 'perorangan' ? 'Edit Data Perorangan' : 'Edit Data Lembaga'"></span>
                </h3>
                <button type="button" @click="onClose" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-5 space-y-4 max-h-[80vh] overflow-y-auto">
                <!-- EDIT PERORANGAN - Mirror Identitas Pemohon Perorangan -->
                <div x-show="penerima && penerima.jenis === 'perorangan'">
                    <div x-data="editPeroranganController()">
                        <!-- Basic Info -->
                        <div class="space-y-3">
                            <h4 class="font-bold text-slate-700 uppercase text-xs flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">1</span>
                                Data Identitas
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">NIK (16 Digit) <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        x-model="form.nik"
                                        inputmode="numeric"
                                        maxlength="16"
                                        placeholder="3301..."
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"
                                        :class="form.nik.length === 16 ? 'border-emerald-500 bg-emerald-50/20' : ''">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Nomor KK</label>
                                    <input
                                        type="text"
                                        x-model="form.nomor_kk"
                                        inputmode="numeric"
                                        maxlength="16"
                                        placeholder="3301..."
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        x-model="form.nama"
                                        placeholder="Nama lengkap sesuai KTP"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Jenis Kelamin</label>
                                    <select x-model="form.jenis_kelamin" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">No. WhatsApp / HP</label>
                                    <input
                                        type="text"
                                        x-model="form.telepon"
                                        inputmode="numeric"
                                        placeholder="08123456789"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Pekerjaan</label>
                                    <input
                                        type="text"
                                        x-model="form.pekerjaan"
                                        placeholder="Contoh: Petani / Pedagang / Buruh"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Tempat Lahir</label>
                                    <input
                                        type="text"
                                        x-model="form.tempat_lahir"
                                        placeholder="Tempat lahir"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Tanggal Lahir</label>
                                    <input
                                        type="date"
                                        x-model="form.tanggal_lahir"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Email</label>
                                    <input
                                        type="email"
                                        x-model="form.email"
                                        placeholder="email@contoh.com"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Alamat / Wilayah -->
                        <div class="space-y-3 pt-3 border-t border-slate-100" x-data="wilayahSelector()">
                            <h4 class="font-bold text-slate-700 uppercase text-xs flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold">2</span>
                                Alamat Domisili
                            </h4>
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
                                    @click="onClose"
                                    class="px-3 py-2 text-sm font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                Batal
                            </button>
                            <button type="button"
                                    @click="submitEdit()"
                                    :disabled="isSubmitting"
                                    class="px-4 py-2 text-sm font-bold text-white bg-[#118243] hover:bg-[#0d6835] rounded-lg shadow-xs inline-flex items-center gap-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="isSubmitting">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    Menyimpan...
                                </template>
                                <template x-if="!isSubmitting">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Simpan Perubahan
                                </template>
                            </button>
                        </div>

                        <script>
                        function editPeroranganController() {
                            return {
                                form: {
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
                                isSubmitting: false,
                                personId: '',

                                // Wilayah
                                provinsis: @json($provinsis ?? []),
                                selectedProvinsi: '33',
                                selectedKabupaten: '3301',
                                selectedKecamatan: '',
                                selectedKelurahan: '',
                                kodePos: '',
                                alamat: '',
                                kabupatens: [],
                                kecamatans: [],
                                kelurahans: [],
                                loading: { kabupaten: false, kecamatan: false, kelurahan: false },

                                init() {
                                    this.loadData();
                                    window.addEventListener('edit-modal-data-loaded', (e) => this.loadData(e.detail.penerima, e.detail.index));
                                },

                                loadData(penerima, index) {
                                    const p = penerima || (typeof this.penerima !== 'undefined' ? this.penerima : null);
                                    this.currentIndex = index !== undefined ? index : 0;
                                    if (!p) return;

                                    this.personId = p.person_id || '';
                                    this.form = {
                                        nik: p.editForm?.nik || p.summary?.nikOrKetua || p.nik || '',
                                        nomor_kk: p.editForm?.nomor_kk || '',
                                        nama: p.editForm?.nama || p.summary?.nama || p.nama || '',
                                        jenis_kelamin: p.editForm?.jenis_kelamin || 'L',
                                        telepon: p.editForm?.telepon || p.telepon || '',
                                        pekerjaan: p.editForm?.pekerjaan || p.pekerjaan || '',
                                        tempat_lahir: p.editForm?.tempat_lahir || '',
                                        tanggal_lahir: p.editForm?.tanggal_lahir || '',
                                        email: p.editForm?.email || '',
                                    };
                                    this.selectedProvinsi = p.editForm?.id_provinsi || '33';
                                    this.selectedKabupaten = p.editForm?.id_kabupaten || '3301';
                                    this.selectedKecamatan = p.editForm?.id_kecamatan || '';
                                    this.selectedKelurahan = p.editForm?.id_kelurahan || '';
                                    this.kodePos = p.editForm?.kode_pos || '';
                                    this.alamat = p.editForm?.alamat || p.summary?.alamat || p.alamat || '';

                                    if (this.selectedProvinsi) this.onProvinsiChange();
                                    if (this.selectedKabupaten) this.onKabupatenChange();
                                    if (this.selectedKecamatan) this.onKecamatanChange();
                                },

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
                                    } finally { this.loading.kabupaten = false; }
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
                                    } finally { this.loading.kecamatan = false; }
                                },

                                async onKecamatanChange() {
                                    this.kelurahans = [];
                                    this.selectedKelurahan = '';
                                    if (!this.selectedKecamatan) return;
                                    this.loading.kelurahan = true;
                                    try {
                                        const res = await fetch(`/api/wilayah/kelurahan?kecamatan_id=${this.selectedKecamatan}`);
                                        this.kelurahans = await res.json();
                                    } finally { this.loading.kelurahan = false; }
                                },

                                async onKelurahanChange() {
                                    if (this.selectedKelurahan) {
                                        try {
                                            const res = await fetch(`/api/wilayah/kode-pos?kelurahan_id=${this.selectedKelurahan}`);
                                            const data = await res.json();
                                            this.kodePos = data.kode_pos || '';
                                        } catch (e) { this.kodePos = ''; }
                                    }
                                },

                                async submitEdit() {
                                    if (!this.form.nik || this.form.nik.length !== 16) { window.$alert('NIK harus 16 digit'); return; }
                                    if (!this.form.nama.trim()) { window.$alert('Nama wajib diisi'); return; }

                                    this.isSubmitting = true;
                                    try {
                                        const payload = {
                                            nik: this.form.nik,
                                            nomor_kk: this.form.nomor_kk,
                                            nama: this.form.nama,
                                            jenis_kelamin: this.form.jenis_kelamin,
                                            telepon: this.form.telepon,
                                            pekerjaan: this.form.pekerjaan,
                                            tempat_lahir: this.form.tempat_lahir,
                                            tanggal_lahir: this.form.tanggal_lahir || null,
                                            email: this.form.email,
                                            alamat: this.alamat,
                                            id_provinsi: this.selectedProvinsi,
                                            id_kabupaten: this.selectedKabupaten,
                                            id_kecamatan: this.selectedKecamatan,
                                            id_kelurahan: this.selectedKelurahan,
                                            kode_pos: this.kodePos,
                                        };

                                        const res = await fetch(`/pengajuan/umum/person/${this.personId}`, {
                                            method: 'PUT',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                            },
                                            body: JSON.stringify(payload),
                                        });

                                        if (!res.ok) {
                                            const err = await res.json();
                                            throw new Error(err.message || 'Gagal mengupdate data pemohon');
                                        }

                                        const person = await res.json();
                                        this.$dispatch('penerima-updated', {
                                            index: this.currentIndex !== undefined ? this.currentIndex : @js($index),
                                            person: person,
                                            summary: {
                                                nama: person.nama,
                                                nikOrKetua: person.nik,
                                                alamat: person.alamat_lengkap || person.alamat
                                            }
                                        });
                                        this.onClose();
                                    } catch (e) {
                                        window.$alert('Error: ' + e.message);
                                    } finally {
                                        this.isSubmitting = false;
                                    }
                                },
                            };
                        }
                        </script>
                    </div>
                </div>

                <!-- EDIT LEMBAGA - Mirror Identitas Pemohon Lembaga + Pengurus -->
                <div x-show="penerima && penerima.jenis === 'lembaga'">
                    <div x-data="editLembagaController()">
                        <!-- Lembaga Basic Info -->
                        <div class="space-y-3">
                            <h4 class="font-bold text-slate-700 uppercase text-xs flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold">1</span>
                                Identitas Lembaga
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Nama Lembaga <span class="text-red-500">*</span></label>
                                    <input
                                        type="text"
                                        x-model="form.nama"
                                        placeholder="Nama lengkap lembaga"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Jenis Lembaga <span class="text-red-500">*</span></label>
                                    <select x-model="form.jenis" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
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
                                        x-model="form.telepon"
                                        inputmode="numeric"
                                        placeholder="08123456789"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Email</label>
                                    <input
                                        type="email"
                                        x-model="form.email"
                                        placeholder="email@contoh.com"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Alamat Lembaga</label>
                                    <textarea
                                        x-model="form.alamat"
                                        rows="2"
                                        placeholder="Jl, No, RT/RW, Dusun..."
                                        class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Wilayah -->
                        <div class="space-y-3 pt-3 border-t border-slate-100" x-data="wilayahSelector()">
                            <h4 class="font-bold text-slate-700 uppercase text-xs flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold">2</span>
                                Wilayah
                            </h4>
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
                            </div>
                        </div>

                        <!-- Pengurus Section -->
                        <div class="space-y-3 pt-3 border-t border-slate-100">
                            <h4 class="font-bold text-slate-700 uppercase text-xs flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold">3</span>
                                <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Pengurus <span class="text-red-500 text-xs">(Minimal 1 Ketua Wajib)</span>
                            </h4>

                            <div class="space-y-2">
                                <template x-for="(p, pi) in pengurus" :key="pi">
                                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2" x-data="pengurusEditRowController({ index: pi, isKetua: pi === 0 })">
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

                                <button type="button"
                                        @click="addPengurus()"
                                        class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Tambah Pengurus
                                </button>
                            </div>

                            <div class="p-2 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-900 flex items-start gap-2">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>Validasi: Minimal 1 pengurus (Ketua wajib). Setiap pengurus harus memiliki NIK dan Nama.</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center justify-end gap-2 pt-4 border-t border-slate-100">
                            <button type="button"
                                    @click="onClose"
                                    class="px-3 py-2 text-sm font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                Batal
                            </button>
                            <button type="button"
                                    @click="submitEdit()"
                                    :disabled="isSubmitting || !validatePengurus()"
                                    class="px-4 py-2 text-sm font-bold text-white bg-[#118243] hover:bg-[#0d6835] rounded-lg shadow-xs inline-flex items-center gap-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="isSubmitting">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    Menyimpan...
                                </template>
                                <template x-if="!isSubmitting">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Simpan Perubahan
                                </template>
                            </button>
                        </div>

                        <script>
                        function editLembagaController() {
                            return {
                                form: {
                                    nama: '',
                                    jenis: 'Masjid',
                                    telepon: '',
                                    email: '',
                                    alamat: '',
                                },
                                isSubmitting: false,
                                lembagaId: '',

                                // Wilayah
                                provinsis: @json($provinsis ?? []),
                                selectedProvinsi: '33',
                                selectedKabupaten: '3301',
                                selectedKecamatan: '',
                                selectedKelurahan: '',
                                kodePos: '',
                                kabupatens: [],
                                kecamatans: [],
                                kelurahans: [],
                                loading: { kabupaten: false, kecamatan: false, kelurahan: false },

                                // Pengurus
                                pengurus: [{ nik: '', nama: '', jabatan: 'Ketua' }],

                                init() {
                                    this.loadData();
                                    window.addEventListener('edit-modal-data-loaded', (e) => this.loadData(e.detail.penerima, e.detail.index));
                                },

                                loadData(penerima, index) {
                                    const p = penerima || (typeof this.penerima !== 'undefined' ? this.penerima : null);
                                    this.currentIndex = index !== undefined ? index : 0;
                                    if (!p) return;

                                    this.lembagaId = p.lembaga_id || '';
                                    this.form = {
                                        nama: p.editForm?.nama || p.summary?.nama || p.nama || '',
                                        jenis: p.editForm?.jenis || 'Masjid',
                                        telepon: p.editForm?.telepon || p.telepon || '',
                                        email: p.editForm?.email || '',
                                        alamat: p.editForm?.alamat || p.summary?.alamat || p.alamat || '',
                                    };
                                    this.selectedProvinsi = p.editForm?.id_provinsi || '33';
                                    this.selectedKabupaten = p.editForm?.id_kabupaten || '3301';
                                    this.selectedKecamatan = p.editForm?.id_kecamatan || '';
                                    this.selectedKelurahan = p.editForm?.id_kelurahan || '';
                                    this.kodePos = p.editForm?.kode_pos || '';
                                    this.pengurus = p.editForm?.pengurus || [{ nik: p.summary?.nikOrKetua || '', nama: p.summary?.nikOrKetua || '', jabatan: 'Ketua' }];

                                    if (this.selectedProvinsi) this.onProvinsiChange();
                                    if (this.selectedKabupaten) this.onKabupatenChange();
                                    if (this.selectedKecamatan) this.onKecamatanChange();
                                    
                                    // Ensure at least 1 ketua
                                    if (this.pengurus.length === 0) {
                                        this.pengurus = [{ nik: '', nama: '', jabatan: 'Ketua' }];
                                    }
                                    if (!this.pengurus.some(p => p.jabatan === 'Ketua')) {
                                        this.pengurus[0].jabatan = 'Ketua';
                                    }
                                },

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
                                    } finally { this.loading.kabupaten = false; }
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
                                    } finally { this.loading.kecamatan = false; }
                                },

                                async onKecamatanChange() {
                                    this.kelurahans = [];
                                    this.selectedKelurahan = '';
                                    if (!this.selectedKecamatan) return;
                                    this.loading.kelurahan = true;
                                    try {
                                        const res = await fetch(`/api/wilayah/kelurahan?kecamatan_id=${this.selectedKecamatan}`);
                                        this.kelurahans = await res.json();
                                    } finally { this.loading.kelurahan = false; }
                                },

                                async onKelurahanChange() {
                                    if (this.selectedKelurahan) {
                                        try {
                                            const res = await fetch(`/api/wilayah/kode-pos?kelurahan_id=${this.selectedKelurahan}`);
                                            const data = await res.json();
                                            this.kodePos = data.kode_pos || '';
                                        } catch (e) { this.kodePos = ''; }
                                    }
                                },

                                addPengurus() {
                                    const jabatanOptions = ['Sekretaris', 'Bendahara', 'Anggota'];
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
                                    for (const p of this.pengurus) {
                                        if (!p.nik || p.nik.length !== 16) return false;
                                        if (!p.nama.trim()) return false;
                                    }
                                    const ketuaCount = this.pengurus.filter(p => p.jabatan === 'Ketua').length;
                                    return ketuaCount === 1;
                                },

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

                                async submitEdit() {
                                    if (!this.form.nama.trim()) { window.$alert('Nama lembaga wajib diisi'); return; }
                                    if (!this.validatePengurus()) { window.$alert('Validasi pengurus gagal: minimal 1 Ketua wajib, semua pengurus harus punya NIK 16 digit dan Nama'); return; }

                                    this.isSubmitting = true;
                                    try {
                                        const payload = {
                                            nama: this.form.nama,
                                            jenis: this.form.jenis,
                                            telepon: this.form.telepon,
                                            email: this.form.email,
                                            alamat: this.form.alamat,
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

                                        const res = await fetch(`/pengajuan/umum/lembaga/${this.lembagaId}`, {
                                            method: 'PUT',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                            },
                                            body: JSON.stringify(payload),
                                        });

                                        if (!res.ok) {
                                            const err = await res.json();
                                            throw new Error(err.message || 'Gagal mengupdate lembaga');
                                        }

                                        const lembaga = await res.json();
                                        this.$dispatch('penerima-updated', {
                                            index: this.currentIndex !== undefined ? this.currentIndex : @js($index),
                                            lembaga: lembaga,
                                            summary: {
                                                nama: lembaga.nama,
                                                nikOrKetua: lembaga.ketua_nama || '-',
                                                alamat: lembaga.alamat
                                            }
                                        });
                                        this.onClose();
                                    } catch (e) {
                                        window.$alert('Error: ' + e.message);
                                    } finally {
                                        this.isSubmitting = false;
                                    }
                                },
                            };
                        }

                        function pengurusEditRowController({ index, isKetua }) {
                            return {
                                index,
                                isKetua,
                                form: { nik: '', nama: '', jabatan: isKetua ? 'Ketua' : 'Anggota' },
                                isSearchingNik: false,
                                nikNotFound: false,

                                init() {
                                    if (typeof this.pengurus !== 'undefined' && this.pengurus[this.index]) {
                                        const p = this.pengurus[this.index];
                                        this.form = { nik: p.nik || '', nama: p.nama || '', jabatan: p.jabatan || (isKetua ? 'Ketua' : 'Anggota') };
                                    }
                                },
                            };
                        }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editPenerimaModalController() {
    return {
        isOpen: {{ $show ? 'true' : 'false' }},
        penerima: @json($penerima ?? []),
        index: {{ $index ?? 0 }},
        programs: @json($programs ?? []),
        subprograms: @json($subprograms ?? []),
        provinsis: @json($provinsis ?? []),

        get isPerorangan() {
            return this.penerima && this.penerima.jenis === 'perorangan';
        },

        show(penerima, index) {
            this.penerima = penerima || {};
            this.index = index !== undefined ? index : 0;
            this.isOpen = true;
            // Dispatch event for inner controllers to load data
            window.dispatchEvent(new CustomEvent('edit-modal-data-loaded', { detail: { penerima: this.penerima, index: this.index } }));
        },

        close() {
            this.isOpen = false;
            this.penerima = {};
            this.index = 0;
        },

        onClose() {
            this.close();
        },
    };
}
</script>