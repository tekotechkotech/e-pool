@props([
    'prefix' => '',
    'provinsis' => [],
    'provinsi' => '',
    'kabupaten' => '',
    'kecamatan' => '',
    'kelurahan' => '',
    'kodePos' => '',
    'alamat' => '',
    'withAlamat' => true,
    'withTitle' => true,
    'title' => 'Alamat & Wilayah',
    'gridClass' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4',
    'standalone' => true,
    'alpineScope' => null,
])

@php
    $s = function (string $prop) use ($alpineScope): string {
        return ($alpineScope ? "{$alpineScope}." : '') . $prop;
    };
@endphp

<div {{ $attributes->merge(['class' => 'space-y-4 text-xs']) }}
    @if($standalone)
    x-data="wilayahSelector({
        provinsis: @json($provinsis ?? []),
        provinsi: {{ json_encode((string)($provinsi ?? '')) }},
        kabupaten: {{ json_encode((string)($kabupaten ?? '')) }},
        kecamatan: {{ json_encode((string)($kecamatan ?? '')) }},
        kelurahan: {{ json_encode((string)($kelurahan ?? '')) }},
        kode_pos: {{ json_encode((string)($kodePos ?? '')) }},
        alamat: {{ json_encode((string)($alamat ?? '')) }}
    })"
    @endif>
    @if($withTitle)
        <div class="pt-2 border-t border-slate-100">
            <span class="font-bold text-slate-800 block mb-2">{{ $title }}</span>
        </div>
    @endif

    <div class="{{ $gridClass }}">
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Provinsi</label>
            <select name="{{ $prefix }}id_provinsi" x-model="{{ $s('selectedProvinsi') }}" @change="{{ $s('onProvinsiChange()') }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'id_provinsi') border-rose-500 focus:ring-rose-500/20 @enderror">
                <option value="">-- Pilih Provinsi --</option>
                <template x-for="p in {{ $s('provinsis') }}" :key="p.id_wilayah">
                    <option :value="p.id_wilayah" x-text="p.nama" :selected="{{ $s('selectedProvinsi') }} === p.id_wilayah"></option>
                </template>
            </select>
            @error($prefix . 'id_provinsi')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Kabupaten/Kota</label>
            <select name="{{ $prefix }}id_kabupaten" x-model="{{ $s('selectedKabupaten') }}" @change="{{ $s('onKabupatenChange()') }}" :disabled="!{{ $s('selectedProvinsi') }} || {{ $s('loading.kabupaten') }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none @error($prefix . 'id_kabupaten') border-rose-500 focus:ring-rose-500/20 @enderror">
                <option value="">-- Pilih Kabupaten/Kota --</option>
                <template x-for="k in {{ $s('kabupatens') }}" :key="k.id_wilayah">
                    <option :value="k.id_wilayah" x-text="k.nama" :selected="{{ $s('selectedKabupaten') }} === k.id_wilayah"></option>
                </template>
            </select>
            @error($prefix . 'id_kabupaten')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Kecamatan</label>
            <select name="{{ $prefix }}id_kecamatan" x-model="{{ $s('selectedKecamatan') }}" @change="{{ $s('onKecamatanChange()') }}" :disabled="!{{ $s('selectedKabupaten') }} || {{ $s('loading.kecamatan') }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none @error($prefix . 'id_kecamatan') border-rose-500 focus:ring-rose-500/20 @enderror">
                <option value="">-- Pilih Kecamatan --</option>
                <template x-for="c in {{ $s('kecamatans') }}" :key="c.id_wilayah">
                    <option :value="c.id_wilayah" x-text="c.nama" :selected="{{ $s('selectedKecamatan') }} === c.id_wilayah"></option>
                </template>
            </select>
            @error($prefix . 'id_kecamatan')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Desa/Kelurahan</label>
            <select name="{{ $prefix }}id_kelurahan" x-model="{{ $s('selectedKelurahan') }}" @change="{{ $s('onKelurahanChange()') }}" :disabled="!{{ $s('selectedKecamatan') }} || {{ $s('loading.kelurahan') }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all disabled:bg-slate-50 disabled:text-slate-400 disabled:shadow-none @error($prefix . 'id_kelurahan') border-rose-500 focus:ring-rose-500/20 @enderror">
                <option value="">-- Pilih Desa/Kelurahan --</option>
                <template x-for="l in {{ $s('kelurahans') }}" :key="l.id_wilayah">
                    <option :value="l.id_wilayah" x-text="l.nama" :selected="{{ $s('selectedKelurahan') }} === l.id_wilayah"></option>
                </template>
            </select>
            @error($prefix . 'id_kelurahan')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>

        <div class="{{ str_contains($gridClass, 'lg:grid-cols-3') ? '' : 'sm:col-span-2' }}">
            <label class="block font-bold text-slate-700 uppercase mb-1">Kode Pos</label>
            <input type="text" name="{{ $prefix }}kode_pos" x-model="{{ $s('kodePos') }}" value="{{ old($prefix . 'kode_pos', $kodePos) }}" placeholder="Kode Pos" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'kode_pos') border-rose-500 focus:ring-rose-500/20 @enderror">
            @error($prefix . 'kode_pos')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>

        @if($withAlamat)
            <div class="{{ str_contains($gridClass, 'lg:grid-cols-3') ? 'sm:col-span-2 lg:col-span-3' : 'sm:col-span-2' }}">
                <label class="block font-bold text-slate-700 uppercase mb-1">Alamat (Jl, No, RT/RW, Dusun)</label>
                <textarea name="{{ $prefix }}alamat" x-model="{{ $s('alamat') }}" rows="2" placeholder="Contoh: Jl. Ahmad Yani No. 45, RT 02 / RW 03, Dusun Karanganyar" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'alamat') border-rose-500 focus:ring-rose-500/20 @enderror">{{ old($prefix . 'alamat', $alamat) }}</textarea>
                @error($prefix . 'alamat')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>
        @endif

        {{ $slot }}
    </div>
</div>
