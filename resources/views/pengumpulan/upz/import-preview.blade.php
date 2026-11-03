@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="importPreviewData()">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('pengumpulan.upz.index') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-700">Setoran UPZ</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700">Staging Verification</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Verifikasi Import Setoran UPZ</h1>
            <p class="text-sm text-slate-500">Pratinjau data setoran ZIS dari spreadsheet sebelum disimpan ke database dan buku kas.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('pengumpulan.upz.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition-all">
                Batalkan
            </a>
            <button type="button" @click="submitConfirm()" :disabled="submitting" class="inline-flex items-center justify-center rounded-xl bg-[#118243] px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#0d6835] transition-all disabled:opacity-50">
                <span x-show="!submitting" class="flex items-center gap-1.5">
                    <x-heroicon-o-check class="w-4 h-4" />
                    Konfirmasi & Simpan Setoran
                </span>
                <span x-show="submitting" class="flex items-center gap-1.5">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    Menyimpan...
                </span>
            </button>
        </div>
    </div>

    <!-- Data Induk Setoran Banner -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
        <div>
            <div class="text-xs text-slate-400 font-medium">Unit Pengumpul Zakat (UPZ)</div>
            <div class="text-base font-bold text-slate-800 mt-0.5">{{ $upz->nama }}</div>
            <div class="text-xs text-slate-500 font-mono">Kode SIMBA: {{ $upz->kode_simba ?? '-' }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400 font-medium">Tanggal Setoran</div>
            <div class="text-base font-bold text-slate-800 mt-0.5">{{ \Carbon\Carbon::parse($tanggalSetor)->format('d F Y') }}</div>
            <div class="text-xs text-slate-500">Periode: {{ $periode }}</div>
        </div>
        <div class="sm:col-span-2">
            <div class="text-xs text-slate-400 font-medium">Catatan / Keterangan</div>
            <div class="text-slate-700 mt-0.5">{{ $keterangan ?: 'Tidak ada keterangan tambahan.' }}</div>
        </div>
    </div>

    <!-- KPI & Status Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
            <div class="text-xs text-slate-400 font-medium">Total Baris</div>
            <div class="text-xl font-bold text-slate-800 mt-1" x-text="items.length"></div>
            <div class="text-[11px] text-slate-400 mt-0.5">Semua record valid</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
            <div class="text-xs text-emerald-600 font-medium">Terdaftar (> Rp 0)</div>
            <div class="text-xl font-bold text-emerald-700 mt-1" x-text="countTerdaftar()"></div>
            <div class="text-[11px] text-emerald-600/80 mt-0.5">Muzakki aktif & menyetor</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
            <div class="text-xs text-amber-600 font-medium">Muzakki Nihil (Rp 0)</div>
            <div class="text-xl font-bold text-amber-700 mt-1" x-text="countNihil()"></div>
            <div class="text-[11px] text-slate-400 mt-0.5">Audit tracking tersimpan</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
            <div class="text-xs text-rose-600 font-medium">Belum Terdaftar</div>
            <div class="text-xl font-bold text-rose-700 mt-1" x-text="countBelumTerdaftar()"></div>
            <div class="text-[11px] text-rose-500 mt-0.5">Dapat registrasi cepat</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm col-span-2 lg:col-span-1">
            <div class="text-xs text-slate-400 font-medium">Total Nominal Setor</div>
            <div class="text-xl font-extrabold text-emerald-800 font-mono mt-1" x-text="formatRupiah(totalNominal())"></div>
            <div class="text-[11px] text-slate-400 mt-0.5">Zakat + Infak + Fitrah + Titipan</div>
        </div>
    </div>

    <!-- Alert Belum Terdaftar jika ada -->
    <div x-show="countBelumTerdaftar() > 0" class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3 text-sm text-amber-800">
        <x-heroicon-o-information-circle class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
        <div class="flex-1">
            <span class="font-bold">Perhatian:</span> Terdapat <span class="font-bold underline" x-text="countBelumTerdaftar()"></span> muzakki yang belum terdaftar di sistem. Anda dapat langsung mengklik tombol <span class="font-semibold bg-amber-100 px-1.5 py-0.5 rounded text-amber-900">Registrasi Cepat</span> pada tabel di bawah untuk mendaftarkannya sekarang, atau tetap menyimpannya sebagai catatan nama lepas.
        </div>
    </div>

    <!-- Tabel Data Preview -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-3 py-3 text-center">No</th>
                        <th class="px-3 py-3 min-w-[160px]">Nama Muzakki</th>
                        <th class="px-3 py-3 min-w-[120px]">NIK</th>
                        <th class="px-3 py-3 min-w-[100px]">NPWZ</th>
                        <th class="px-3 py-3 text-right min-w-[100px]">Zakat (Rp)</th>
                        <th class="px-3 py-3 text-right min-w-[100px]">Infak (Rp)</th>
                        <th class="px-3 py-3 text-right min-w-[90px]">Fitrah (Rp)</th>
                        <th class="px-3 py-3 text-right min-w-[90px]">Titipan (Rp)</th>
                        <th class="px-3 py-3 text-right min-w-[110px]">Total (Rp)</th>
                        <th class="px-3 py-3 text-center min-w-[110px]">Status</th>
                        <th class="px-3 py-3 min-w-[120px]">Keterangan</th>
                        <th class="px-3 py-3 text-center min-w-[100px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(item, idx) in items" :key="idx">
                        <tr class="hover:bg-slate-50/60 transition-colors" :class="item.status === 'belum_terdaftar' ? 'bg-rose-50/20' : (item.status === 'nihil' ? 'bg-slate-50/30' : '')">
                            <td class="px-3 py-2.5 text-center font-mono text-slate-400" x-text="idx + 1"></td>
                            <td class="px-3 py-2.5">
                                <div class="font-semibold text-slate-800" x-text="item.nama || '-'"></div>
                                <div class="text-[10px] text-slate-400" x-show="item.muzakki_id" x-text="'ID: #' + item.muzakki_id"></div>
                            </td>
                            <td class="px-3 py-2.5 font-mono text-slate-600" x-text="item.nik || '-'"></td>
                            <td class="px-3 py-2.5 font-mono text-slate-600" x-text="item.npwz || '-'"></td>
                            <td class="px-3 py-2.5 text-right font-mono" x-text="formatRupiah(item.nominal_zakat)"></td>
                            <td class="px-3 py-2.5 text-right font-mono" x-text="formatRupiah(item.nominal_infak)"></td>
                            <td class="px-3 py-2.5 text-right font-mono" x-text="formatRupiah(item.nominal_fitrah)"></td>
                            <td class="px-3 py-2.5 text-right font-mono" x-text="formatRupiah(item.nominal_titipan)"></td>
                            <td class="px-3 py-2.5 text-right font-mono font-bold text-emerald-800" x-text="formatRupiah(item.total)"></td>
                            <td class="px-3 py-2.5 text-center">
                                <template x-if="item.status === 'terdaftar'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                        Terdaftar
                                    </span>
                                </template>
                                <template x-if="item.status === 'nihil'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">
                                        Nihil (Rp 0)
                                    </span>
                                </template>
                                <template x-if="item.status === 'belum_terdaftar'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                        Belum Terdaftar
                                    </span>
                                </template>
                            </td>
                            <td class="px-3 py-2.5 text-slate-500" x-text="item.keterangan || '-'"></td>
                            <td class="px-3 py-2.5 text-center">
                                <template x-if="item.status === 'belum_terdaftar'">
                                    <button type="button" @click="openQuickRegister(idx)" class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-semibold rounded-lg text-[11px] transition-all">
                                        + Registrasi
                                    </button>
                                </template>
                                <template x-if="item.status !== 'belum_terdaftar'">
                                    <span class="text-slate-300">-</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-100 text-slate-800">
                    <tr>
                        <td colspan="4" class="px-3 py-3 text-right">TOTAL:</td>
                        <td class="px-3 py-3 text-right font-mono text-emerald-700" x-text="formatRupiah(totalZakat())"></td>
                        <td class="px-3 py-3 text-right font-mono text-emerald-700" x-text="formatRupiah(totalInfak())"></td>
                        <td class="px-3 py-3 text-right font-mono text-emerald-700" x-text="formatRupiah(totalFitrah())"></td>
                        <td class="px-3 py-3 text-right font-mono text-emerald-700" x-text="formatRupiah(totalTitipan())"></td>
                        <td class="px-3 py-3 text-right font-mono font-extrabold text-emerald-800" x-text="formatRupiah(totalNominal())"></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Hidden Form for Final Submission -->
    <form id="confirmForm" method="POST" action="{{ route('pengumpulan.upz.import-confirm') }}">
        @csrf
        <input type="hidden" name="staging_data" :value="JSON.stringify(getStagingPayload())">
    </form>

    <!-- Modal Registrasi Cepat Muzakki (Alpine.js Modal, No JS Native Dialog) -->
    <div x-show="quickModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeQuickRegister()"></div>

            <!-- Modal Card -->
            <div class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Registrasi Cepat Muzakki</h3>
                        <p class="text-xs text-slate-500">Daftarkan person dan muzakki baru langsung ke UPZ terkait.</p>
                    </div>
                    <button type="button" @click="closeQuickRegister()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div x-show="quickError" class="mt-3 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs" x-text="quickError"></div>

                <div class="mt-4 space-y-3.5 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" x-model="quickForm.nama" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Nama sesuai KTP">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">NIK (16 Digit) *</label>
                            <input type="text" x-model="quickForm.nik" maxlength="16" class="w-full px-3 py-2 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="3301xxxxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                            <select x-model="quickForm.jenis_kelamin" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <option value="">-- Pilih --</option>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">NPWZ (Opsional)</label>
                            <input type="text" x-model="quickForm.npwz" class="w-full px-3 py-2 border border-slate-200 rounded-xl font-mono focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="NPWZ jika ada">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">No. Handphone</label>
                            <input type="text" x-model="quickForm.handphone" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Alamat</label>
                        <textarea x-model="quickForm.alamat" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" placeholder="Alamat domisili..."></textarea>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="closeQuickRegister()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition-all">Batal</button>
                    <button type="button" @click="saveQuickRegister()" :disabled="savingQuick" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white rounded-xl font-semibold transition-all disabled:opacity-50 flex items-center gap-1.5">
                        <span x-show="!savingQuick">Simpan & Kaitkan</span>
                        <span x-show="savingQuick">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function importPreviewData() {
    return {
        items: @json($items),
        lembagaId: {{ (int)$upz->id }},
        tanggalSetor: @json($tanggalSetor),
        bulan: @json($bulan),
        tahun: @json($tahun),
        periode: @json($periode),
        keterangan: @json($keterangan),
        submitting: false,

        // Quick register modal state
        quickModalOpen: false,
        quickTargetIdx: null,
        savingQuick: false,
        quickError: '',
        quickForm: {
            nama: '',
            nik: '',
            jenis_kelamin: '',
            npwz: '',
            handphone: '',
            alamat: '',
        },

        countTerdaftar() {
            return this.items.filter(i => i.status === 'terdaftar').length;
        },
        countNihil() {
            return this.items.filter(i => i.status === 'nihil').length;
        },
        countBelumTerdaftar() {
            return this.items.filter(i => i.status === 'belum_terdaftar').length;
        },
        totalZakat() {
            return this.items.reduce((sum, i) => sum + (Number(i.nominal_zakat) || 0), 0);
        },
        totalInfak() {
            return this.items.reduce((sum, i) => sum + (Number(i.nominal_infak) || 0), 0);
        },
        totalFitrah() {
            return this.items.reduce((sum, i) => sum + (Number(i.nominal_fitrah) || 0), 0);
        },
        totalTitipan() {
            return this.items.reduce((sum, i) => sum + (Number(i.nominal_titipan) || 0), 0);
        },
        totalNominal() {
            return this.totalZakat() + this.totalInfak() + this.totalFitrah() + this.totalTitipan();
        },
        formatRupiah(val) {
            return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID');
        },

        openQuickRegister(idx) {
            this.quickTargetIdx = idx;
            const target = this.items[idx];
            this.quickError = '';
            this.quickForm = {
                nama: target.nama || '',
                nik: target.nik || '',
                jenis_kelamin: '',
                npwz: target.npwz || '',
                handphone: '',
                alamat: '',
            };
            this.quickModalOpen = true;
        },
        closeQuickRegister() {
            this.quickModalOpen = false;
            this.quickTargetIdx = null;
            this.quickError = '';
        },
        async saveQuickRegister() {
            if (!this.quickForm.nama.trim()) {
                this.quickError = 'Nama lengkap wajib diisi.';
                return;
            }
            if (!this.quickForm.nik.trim()) {
                this.quickError = 'NIK 16 digit wajib diisi.';
                return;
            }

            this.savingQuick = true;
            this.quickError = '';

            try {
                const response = await fetch("{{ route('pengumpulan.upz.quick-store-muzakki') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        nama: this.quickForm.nama,
                        nik: this.quickForm.nik,
                        lembaga_id: this.lembagaId,
                        jenis_kelamin: this.quickForm.jenis_kelamin || null,
                        npwz: this.quickForm.npwz || null,
                        handphone: this.quickForm.handphone || null,
                        alamat: this.quickForm.alamat || null,
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        const errs = Object.values(data.errors).flat();
                        this.quickError = errs.join(' ');
                    } else {
                        this.quickError = data.message || 'Gagal menyimpan registrasi muzakki.';
                    }
                    this.savingQuick = false;
                    return;
                }

                if (data.success && data.muzakki) {
                    const target = this.items[this.quickTargetIdx];
                    target.muzakki_id = data.muzakki.id;
                    target.nama = data.muzakki.nama;
                    target.nik = data.muzakki.nik;
                    target.npwz = data.muzakki.npwz;
                    target.status = target.total > 0 ? 'terdaftar' : 'nihil';

                    this.closeQuickRegister();
                }
            } catch (err) {
                this.quickError = 'Terjadi kesalahan jaringan atau server: ' + err.message;
            } finally {
                this.savingQuick = false;
            }
        },

        getStagingPayload() {
            return {
                lembaga_id: this.lembagaId,
                tanggal_setor: this.tanggalSetor,
                bulan: this.bulan,
                tahun: this.tahun,
                periode: this.periode,
                keterangan: this.keterangan,
                items: this.items,
            };
        },

        submitConfirm() {
            this.submitting = true;
            document.getElementById('confirmForm').submit();
        }
    }
}
</script>
@endsection
