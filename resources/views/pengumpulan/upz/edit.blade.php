@extends('layouts.app')

@php
    $daftarBulan = $daftarBulan ?? [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $daftarTahun = $daftarTahun ?? range(min((int)date('Y') - 2, 2024), max((int)date('Y') + 4, 2030));

    // Parse bulan & tahun dari $setoran->periode jika ada (e.g. 'November 2026')
    $periodeParts = explode(' ', $setoran->periode ?? '');
    $curBulan = old('bulan', $periodeParts[0] ?? date('F'));
    $curTahun = old('tahun', $periodeParts[1] ?? date('Y'));

    $existingItems = old('items');
    if (!$existingItems) {
        $existingItems = $setoran->items->map(function ($it) {
            return [
                'muzakki_id' => $it->muzakki_id ?? '',
                'nama' => $it->nama ?? '',
                'nik' => $it->muzakki?->person?->nik ?? '',
                'npwz' => $it->npwz ?? '',
                'nominal_zakat' => (int)$it->nominal_zakat,
                'nominal_infak' => (int)$it->nominal_infak,
                'nominal_fitrah' => (int)$it->nominal_fitrah,
                'nominal_titipan' => (int)$it->nominal_titipan,
                'keterangan' => $it->keterangan ?? '',
                'periode' => $it->periode ?? '',
            ];
        })->toArray();
    }

    if (empty($existingItems)) {
        $existingItems = [
            [
                'muzakki_id' => '',
                'nama' => '',
                'nik' => '',
                'npwz' => '',
                'nominal_zakat' => 0,
                'nominal_infak' => 0,
                'nominal_fitrah' => 0,
                'nominal_titipan' => 0,
                'keterangan' => '',
                'periode' => '',
            ]
        ];
    }
    $initialItems = array_values($existingItems);
@endphp

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="editSetoranWorkspace()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('pengumpulan.upz.index') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors">Setoran UPZ</a>
                <span class="text-slate-400 text-xs">/</span>
                <a href="{{ route('pengumpulan.upz.show', $setoran->id) }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-700 transition-colors">{{ $setoran->nomor_setoran }}</a>
                <span class="text-slate-400 text-xs">/</span>
                <span class="px-2 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800">Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Edit Setoran UPZ - {{ $setoran->nomor_setoran }}</h1>
            <p class="text-xs sm:text-sm text-slate-500">Perbarui rincian muzakki, nominal setoran ZIS, dan data administrasi setoran.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('pengumpulan.upz.show', $setoran->id) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                Kembali
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-xs text-red-700 shadow-xs">
            <div class="font-bold mb-1 flex items-center gap-1.5">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-600" />
                Terdapat kendala validasi data:
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('pengumpulan.upz.update', $setoran->id) }}" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
        @csrf
        @method('PUT')

        <!-- Data Induk -->
        <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-black flex items-center justify-center">1</span>
                Data Induk Setoran
            </h2>
            <span class="text-xs text-slate-400">Status saat ini: <strong class="text-emerald-700 font-semibold">{{ $setoran->status }}</strong></span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">UPZ Pengirim</label>
                <select name="lembaga_id" class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2.5 bg-slate-50 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                    @foreach($upzs as $u)
                        <option value="{{ $u->id }}" {{ old('lembaga_id', $setoran->lembaga_id) == $u->id ? 'selected' : '' }}>
                            {{ $u->nama }} ({{ $u->kode_simba ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Setor <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_setor" value="{{ old('tanggal_setor', optional($setoran->tanggal_setor)->format('Y-m-d')) }}" required class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600" />
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Bulan</label>
                    <select name="bulan" class="w-full text-xs border border-slate-200 rounded-xl px-2.5 py-2.5 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                        @foreach($daftarBulan as $b)
                            <option value="{{ $b }}" {{ $curBulan == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun</label>
                    <select name="tahun" class="w-full text-xs border border-slate-200 rounded-xl px-2.5 py-2.5 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600">
                        @foreach($daftarTahun as $t)
                            <option value="{{ $t }}" {{ $curTahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Berita Acara</label>
                <input type="text" name="keterangan" value="{{ old('keterangan', $setoran->keterangan) }}" placeholder="Catatan pengumpulan setoran..." class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600" />
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Verifikasi</label>
                <input type="text" name="catatan_verifikasi" value="{{ old('catatan_verifikasi', $setoran->catatan_verifikasi) }}" placeholder="Catatan verifikator BAZNAS..." class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2.5 bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600" />
            </div>
        </div>

        <!-- Bukti Setor -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Ganti Bukti Setor / Transfer (Opsional)</label>
            <input type="file" name="bukti_setor" accept=".jpg,.jpeg,.png,.webp,.pdf" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-xl cursor-pointer bg-slate-50 focus:outline-none" />
            @if($setoran->bukti_setor)
                <p class="text-[11px] text-slate-400 mt-1">Berkas saat ini: <a href="{{ asset('storage/' . $setoran->bukti_setor) }}" target="_blank" class="text-emerald-600 underline">{{ basename($setoran->bukti_setor) }}</a></p>
            @endif
        </div>

        <!-- Rincian Muzakki -->
        <div class="border-b border-slate-100 pb-4 pt-2 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-black flex items-center justify-center">2</span>
                Rincian Muzakki & Setoran
            </h2>
            <button type="button" @click="addRow()" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5">
                <x-heroicon-o-plus class="w-3.5 h-3.5" />
                Tambah Baris
            </button>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-2xl">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 text-[11px] uppercase font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-3 py-2.5 w-10">No</th>
                        <th class="px-3 py-2.5 min-w-[200px]">Nama Muzakki</th>
                        <th class="px-3 py-2.5 min-w-[120px]">NPWZ</th>
                        <th class="px-3 py-2.5 min-w-[110px] text-right">Zakat (Rp)</th>
                        <th class="px-3 py-2.5 min-w-[110px] text-right">Infak (Rp)</th>
                        <th class="px-3 py-2.5 min-w-[100px] text-right">Fitrah (Rp)</th>
                        <th class="px-3 py-2.5 min-w-[100px] text-right">Titipan (Rp)</th>
                        <th class="px-3 py-2.5 min-w-[120px] text-right">Subtotal</th>
                        <th class="px-3 py-2.5 w-12 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="(item, idx) in items" :key="idx">
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-3 py-2 font-mono text-slate-400 text-center" x-text="idx + 1"></td>
                            <td class="px-3 py-2">
                                <input type="hidden" :name="`items[${idx}][muzakki_id]`" :value="item.muzakki_id" />
                                <input type="text" :name="`items[${idx}][nama]`" x-model="item.nama" required placeholder="Nama Muzakki..." class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500" />
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" :name="`items[${idx}][npwz]`" x-model="item.npwz" placeholder="NPWZ..." class="w-full text-xs font-mono border border-slate-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500" />
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" min="0" :name="`items[${idx}][nominal_zakat]`" x-model.number="item.nominal_zakat" class="w-full text-xs font-mono text-right border border-slate-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500" />
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" min="0" :name="`items[${idx}][nominal_infak]`" x-model.number="item.nominal_infak" class="w-full text-xs font-mono text-right border border-slate-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500" />
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" min="0" :name="`items[${idx}][nominal_fitrah]`" x-model.number="item.nominal_fitrah" class="w-full text-xs font-mono text-right border border-slate-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500" />
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" min="0" :name="`items[${idx}][nominal_titipan]`" x-model.number="item.nominal_titipan" class="w-full text-xs font-mono text-right border border-slate-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500" />
                            </td>
                            <td class="px-3 py-2 text-right font-mono font-bold text-emerald-700" x-text="formatRupiah(rowTotal(item))"></td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" @click="removeRow(idx)" :disabled="items.length <= 1" class="text-rose-500 hover:text-rose-700 disabled:opacity-30 disabled:cursor-not-allowed">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t border-slate-200 text-xs">
                    <tr>
                        <td colspan="3" class="px-3 py-3 text-right">TOTAL:</td>
                        <td class="px-3 py-3 text-right font-mono" x-text="formatRupiah(totalZakat())"></td>
                        <td class="px-3 py-3 text-right font-mono" x-text="formatRupiah(totalInfak())"></td>
                        <td class="px-3 py-3 text-right font-mono" x-text="formatRupiah(totalFitrah())"></td>
                        <td class="px-3 py-3 text-right font-mono" x-text="formatRupiah(totalTitipan())"></td>
                        <td class="px-3 py-3 text-right font-mono font-black text-emerald-700" x-text="formatRupiah(grandTotal())"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('pengumpulan.upz.show', $setoran->id) }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 bg-[#118243] hover:bg-[#0d6835] text-white rounded-xl text-xs font-semibold transition-all shadow-sm flex items-center gap-2">
                <x-heroicon-o-check class="w-4 h-4" />
                Simpan Perubahan Setoran
            </button>
        </div>
    </form>
</div>

<script>
function editSetoranWorkspace() {
    return {
        items: @json($initialItems),
        addRow() {
            this.items.push({
                muzakki_id: '',
                nama: '',
                nik: '',
                npwz: '',
                nominal_zakat: 0,
                nominal_infak: 0,
                nominal_fitrah: 0,
                nominal_titipan: 0,
                keterangan: '',
                periode: ''
            });
        },
        removeRow(idx) {
            if (this.items.length > 1) {
                this.items.splice(idx, 1);
            }
        },
        rowTotal(item) {
            return (Number(item.nominal_zakat) || 0) +
                   (Number(item.nominal_infak) || 0) +
                   (Number(item.nominal_fitrah) || 0) +
                   (Number(item.nominal_titipan) || 0);
        },
        totalZakat() {
            return this.items.reduce((s, i) => s + (Number(i.nominal_zakat) || 0), 0);
        },
        totalInfak() {
            return this.items.reduce((s, i) => s + (Number(i.nominal_infak) || 0), 0);
        },
        totalFitrah() {
            return this.items.reduce((s, i) => s + (Number(i.nominal_fitrah) || 0), 0);
        },
        totalTitipan() {
            return this.items.reduce((s, i) => s + (Number(i.nominal_titipan) || 0), 0);
        },
        grandTotal() {
            return this.totalZakat() + this.totalInfak() + this.totalFitrah() + this.totalTitipan();
        },
        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    };
}
</script>
@endsection
