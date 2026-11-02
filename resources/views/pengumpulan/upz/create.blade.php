@extends('layouts.app')

@php
    $daftarBulan = $daftarBulan ?? [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $currentBulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ][(int)date('n')];
    $selectedBulan = old('bulan');
    $selectedTahun = old('tahun');
    if (!$selectedBulan && old('periode')) {
        $parts = explode(' ', old('periode'));
        $selectedBulan = $parts[0] ?? null;
        $selectedTahun = $parts[1] ?? null;
    }
    $selectedBulan = $selectedBulan ?: $currentBulanIndo;
    $selectedTahun = $selectedTahun ?: date('Y');
    $daftarTahun = $daftarTahun ?? range(min((int)date('Y') - 2, 2024), max((int)date('Y') + 4, 2030));
@endphp

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="setoranForm()">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('pengumpulan.upz.index') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-700">Setoran UPZ</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="text-xs font-bold text-emerald-700">Form Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Input Setoran UPZ Baru</h1>
            <p class="text-sm text-slate-500">Form setoran dana ZIS yang dihimpun UPZ beserta rincian muzakki.</p>
        </div>
        <div class="flex items-center gap-2">
            <template x-if="selectedLembagaId">
                <a :href="'{{ route('pengumpulan.upz.template') }}/' + selectedLembagaId" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm transition-all flex items-center gap-1.5" title="Download template Excel terisi muzakki UPZ ini">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Template Excel UPZ
                </a>
            </template>
            <a href="{{ route('pengumpulan.upz.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition-all">
                Kembali
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('pengumpulan.upz.store') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        @csrf

        <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">1. Data Induk Setoran</h2>
                <p class="text-xs text-slate-500">Pilih UPZ dan tentukan tanggal setoran serta periode.</p>
            </div>
            <div x-show="loadingMuzakki" class="text-xs text-emerald-700 font-medium flex items-center gap-1.5">
                <svg class="animate-spin h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                Memuat muzakki aktif UPZ...
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="sm:col-span-2 lg:col-span-1">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Pengumpul Zakat (UPZ) *</label>
                <select name="lembaga_id" required x-model="selectedLembagaId" @change="onUpzChange($event.target.value)" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    <option value="">-- Pilih UPZ --</option>
                    @foreach($upzs as $u)
                        <option value="{{ $u->id }}" {{ old('lembaga_id') == $u->id ? 'selected' : '' }}>{{ $u->nama }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Pilih UPZ untuk auto-load data muzakki.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Setor *</label>
                <input type="date" name="tanggal_setor" value="{{ old('tanggal_setor', date('Y-m-d')) }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Bulan Periode *</label>
                <select name="bulan" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    @foreach($daftarBulan as $b)
                        <option value="{{ $b }}" {{ $selectedBulan === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Periode *</label>
                <select name="tahun" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    @foreach($daftarTahun as $t)
                        <option value="{{ $t }}" {{ (string)$selectedTahun === (string)$t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-4">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan / Keterangan</label>
                <input type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="Keterangan transfer / bukti bank..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>
        </div>

        <div class="border-b border-slate-100 pb-4 pt-2 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">2. Rincian Muzakki & Munfiq</h2>
                <p class="text-xs text-slate-500">Daftar muzakki aktif UPZ termuat otomatis. Muzakki dengan nominal 0 tetap dicatat sebagai laporan nihil.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="addRow()" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold rounded-lg text-xs flex items-center gap-1 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Baris
                </button>
            </div>
        </div>

        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 uppercase font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-3 py-2.5 text-center">No</th>
                        <th class="px-3 py-2.5 min-w-[180px]">Nama Muzakki *</th>
                        <th class="px-3 py-2.5 min-w-[120px]">NPWZ</th>
                        <th class="px-3 py-2.5 min-w-[110px]">Zakat (Rp)</th>
                        <th class="px-3 py-2.5 min-w-[110px]">Infak (Rp)</th>
                        <th class="px-3 py-2.5 min-w-[100px]">Fitrah (Rp)</th>
                        <th class="px-3 py-2.5 min-w-[100px]">Titipan (Rp)</th>
                        <th class="px-3 py-2.5 min-w-[130px]">Keterangan</th>
                        <th class="px-2 py-2.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(item, idx) in items" :key="idx">
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-3 py-2 text-center font-mono text-slate-400" x-text="idx + 1"></td>
                            <td class="px-3 py-2">
                                <input type="hidden" :name="'items[' + idx + '][muzakki_id]'" :value="item.muzakki_id || ''">
                                <input type="text" :name="'items[' + idx + '][nama]'" x-model="item.nama" required placeholder="Nama lengkap" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" :name="'items[' + idx + '][npwz]'" x-model="item.npwz" placeholder="NPWZ..." class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs font-mono">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" :name="'items[' + idx + '][nominal_zakat]'" x-model.number="item.nominal_zakat" min="0" placeholder="0" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs text-right font-mono">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" :name="'items[' + idx + '][nominal_infak]'" x-model.number="item.nominal_infak" min="0" placeholder="0" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs text-right font-mono">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" :name="'items[' + idx + '][nominal_fitrah]'" x-model.number="item.nominal_fitrah" min="0" placeholder="0" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs text-right font-mono">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" :name="'items[' + idx + '][nominal_titipan]'" x-model.number="item.nominal_titipan" min="0" placeholder="0" class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs text-right font-mono">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" :name="'items[' + idx + '][keterangan]'" x-model="item.keterangan" placeholder="Keterangan..." class="w-full px-2 py-1.5 border border-slate-200 rounded-lg text-xs">
                            </td>
                            <td class="px-2 py-2 text-center">
                                <button type="button" @click="removeRow(idx)" class="text-red-500 hover:text-red-700 p-1" title="Hapus baris" :disabled="items.length <= 1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-100 text-slate-800">
                    <tr>
                        <td colspan="3" class="px-3 py-2 text-right">TOTAL SETORAN:</td>
                        <td class="px-3 py-2 text-right font-mono text-emerald-700" x-text="formatRupiah(totalZakat())"></td>
                        <td class="px-3 py-2 text-right font-mono text-emerald-700" x-text="formatRupiah(totalInfak())"></td>
                        <td class="px-3 py-2 text-right font-mono text-emerald-700" x-text="formatRupiah(totalFitrah())"></td>
                        <td class="px-3 py-2 text-right font-mono text-emerald-700" x-text="formatRupiah(totalTitipan())"></td>
                        <td colspan="2" class="px-3 py-2 text-right font-mono text-emerald-800 font-extrabold" x-text="formatRupiah(grandTotal())"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('pengumpulan.upz.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-all">Batal</a>
            <button type="submit" class="px-6 py-2.5 bg-[#118243] hover:bg-[#0d6835] text-white font-semibold rounded-xl text-sm shadow-sm transition-all">Simpan Setoran UPZ</button>
        </div>
    </form>
</div>

<script>
function setoranForm() {
    return {
        selectedLembagaId: '{{ old('lembaga_id', '') }}',
        loadingMuzakki: false,
        items: [
            { muzakki_id: '', nama: '', npwz: '', nominal_zakat: 0, nominal_infak: 0, nominal_fitrah: 0, nominal_titipan: 0, keterangan: '' }
        ],
        async onUpzChange(lembagaId) {
            if (!lembagaId) return;
            this.loadingMuzakki = true;
            try {
                const response = await fetch("{{ url('pengumpulan/upz/muzakki-by-upz') }}/" + lembagaId, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (Array.isArray(data) && data.length > 0) {
                    this.items = data.map(m => ({
                        muzakki_id: m.id,
                        nama: m.nama,
                        npwz: m.npwz || '',
                        nominal_zakat: 0,
                        nominal_infak: 0,
                        nominal_fitrah: 0,
                        nominal_titipan: 0,
                        keterangan: ''
                    }));
                } else {
                    this.items = [
                        { muzakki_id: '', nama: '', npwz: '', nominal_zakat: 0, nominal_infak: 0, nominal_fitrah: 0, nominal_titipan: 0, keterangan: '' }
                    ];
                }
            } catch (err) {
                console.error('Error auto-loading muzakki:', err);
            } finally {
                this.loadingMuzakki = false;
            }
        },
        addRow() {
            this.items.push({ muzakki_id: '', nama: '', npwz: '', nominal_zakat: 0, nominal_infak: 0, nominal_fitrah: 0, nominal_titipan: 0, keterangan: '' });
        },
        removeRow(idx) {
            if (this.items.length > 1) {
                this.items.splice(idx, 1);
            }
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
        grandTotal() {
            return this.totalZakat() + this.totalInfak() + this.totalFitrah() + this.totalTitipan();
        },
        formatRupiah(val) {
            return 'Rp ' + (val || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endsection
