@props([
    'penerima',
    'index',
    'programs',
    'subprograms',
])

@php
    $programOptions = $programs;
    $subprogramOptions = $subprograms;
@endphp

<div class="p-4 rounded-2xl border border-slate-200 bg-white/80 space-y-3 relative transition-all hidden lg:block"
     x-show="penerima['{{ $index }}'].showDetail"
     x-transition
     x-cloak>

    <!-- Program & Sub Program Selection -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
        <!-- Program BAZNAS -->
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Program BAZNAS <span class="text-red-500">*</span></label>
            <select
                :name="'penerima_manfaat[' + {{ $index }} + '][program_id]'"
                x-model="penerima[{{ $index }}].program_id"
                @change="$dispatch('penerima-program-changed', { index: {{ $index }} })"
                required
                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-bold text-slate-800 shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
                <option value="">-- Pilih Program --</option>
                @foreach($programOptions as $p)
                    <option value="{{ $p->id }}" x-text="{{ $p->nama }}"></option>
                @endforeach
            </select>
        </div>

        <!-- Sub Program (Cascading) -->
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Sub Program <span class="text-red-500">*</span></label>
            <select
                :name="'penerima_manfaat[' + {{ $index }} + '][subprogram_id]'"
                x-model="penerima[{{ $index }}].subprogram_id"
                :disabled="!penerima[{{ $index }}].program_id"
                required
                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all disabled:bg-slate-100 disabled:text-slate-400 disabled:shadow-none">
                <option value="" x-text="penerima[{{ $index }}].program_id ? '-- Pilih Sub Program --' : '-- Pilih Program Dulu --'"></option>
                <template x-for="sp in $store.pengajuanUmumForm().getSubprograms(penerima[{{ $index }}].program_id)" :key="sp.id">
                    <option :value="sp.id" x-text="sp.nama"></option>
                </template>
            </select>
        </div>

        <!-- Keterangan Khusus -->
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Keterangan Khusus</label>
            <input
                type="text"
                :name="'penerima_manfaat[' + {{ $index }} + '][keterangan]'"
                x-model="penerima[{{ $index }}].keterangan"
                placeholder="Catatan bantuan..."
                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
        </div>
    </div>

    <!-- Additional fields for Perorangan: Pekerjaan, Telepon, Alamat -->
    <div x-show="penerima[{{ $index }}].jenis === 'perorangan'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-2 border-t border-slate-100">
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Pekerjaan</label>
            <input
                type="text"
                :name="'penerima_manfaat[' + {{ $index }} + '][pekerjaan]'"
                x-model="penerima[{{ $index }}].pekerjaan"
                placeholder="Pekerjaan..."
                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">No. WhatsApp / HP</label>
            <input
                type="text"
                :name="'penerima_manfaat[' + {{ $index }} + '][telepon]'"
                x-model="penerima[{{ $index }}].telepon"
                inputmode="numeric"
                placeholder="08123456789"
                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
        </div>

        <div class="sm:col-span-2">
            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Alamat Domisili</label>
            <textarea
                :name="'penerima_manfaat[' + {{ $index }} + '][alamat]'"
                x-model="penerima[{{ $index }}].alamat"
                rows="2"
                placeholder="Jl, No, RT/RW, Dusun..."
                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"></textarea>
        </div>
    </div>

    <!-- Additional fields for Lembaga: Kontak, Alamat -->
    <div x-show="penerima[{{ $index }}].jenis === 'lembaga'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-2 border-t border-slate-100">
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">No. Kontak / WA</label>
            <input
                type="text"
                :name="'penerima_manfaat[' + {{ $index }} + '][telepon]'"
                x-model="penerima[{{ $index }}].telepon"
                inputmode="numeric"
                placeholder="08123456789"
                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Email</label>
            <input
                type="email"
                :name="'penerima_manfaat[' + {{ $index }} + '][email]'"
                x-model="penerima[{{ $index }}].email"
                placeholder="email@contoh.com"
                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all">
        </div>

        <div class="sm:col-span-2">
            <label class="block font-bold text-slate-700 uppercase mb-1 text-[11px]">Alamat Lembaga</label>
            <textarea
                :name="'penerima_manfaat[' + {{ $index }} + '][alamat]'"
                x-model="penerima[{{ $index }}].alamat"
                rows="2"
                placeholder="Jl, No, RT/RW, Dusun..."
                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:ring-2 focus:ring-[#118243]/20 focus:outline-none transition-all"></textarea>
        </div>
    </div>
</div>