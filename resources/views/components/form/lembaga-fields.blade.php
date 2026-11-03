@props([
    'prefix' => '',
    'lembaga' => null,
    'nama' => null,
    'labelNama' => 'Nama UPZ / Lembaga',
    'placeholderNama' => 'Nama UPZ atau Lembaga',
    'fullWidthNama' => false,
    'telepon' => null,
    'labelTelepon' => 'No. Kontak / WA',
    'nameTelepon' => 'telepon',
    'showJenis' => false,
    'jenis' => null,
    'jenisOptions' => [],
    'showEmail' => false,
    'email' => null,
    'showKetua' => false,
    'labelKetua' => 'Nama Ketua / Pimpinan',
    'placeholderKetua' => 'Nama Ketua Lembaga',
    'showKetuaNik' => true,
    'ketuaNama' => null,
    'ketuaNik' => null,
    'required' => true,
    'gridClass' => 'grid grid-cols-1 sm:grid-cols-2 gap-4',
    'alpineModel' => null,
])

@php
    $teleponFieldName = $nameTelepon ?: 'telepon';
    $teleponModelKey = $nameTelepon === 'handphone' ? 'handphone' : 'telepon';

    $resolvedNama = old($prefix . 'nama', $nama ?? $lembaga?->nama);
    $resolvedTelepon = old($prefix . $teleponFieldName, $telepon ?? ($lembaga?->telepon ?? $lembaga?->handphone));
    $resolvedJenis = old($prefix . 'jenis', $jenis ?? $lembaga?->jenis);
    $resolvedEmail = old($prefix . 'email', $email ?? $lembaga?->email);
    $resolvedKetuaNama = old($prefix . 'ketua_nama', $ketuaNama ?? ($lembaga?->ketua_nama ?? $lembaga?->nama_ketua));
    $resolvedKetuaNik = old($prefix . 'ketua_nik', $ketuaNik ?? ($lembaga?->ketua_nik ?? $lembaga?->nik_ketua));

    $getModel = function (string $field) use ($alpineModel): ?string {
        if ($alpineModel === null || $alpineModel === false || $alpineModel === '') {
            return null;
        }
        if ($alpineModel === true || $alpineModel === 'true' || $alpineModel === 1 || $alpineModel === '1') {
            return $field;
        }
        return "{$alpineModel}.{$field}";
    };
@endphp

<div {{ $attributes->merge(['class' => $gridClass . ' text-xs']) }}>
    <div class="{{ $fullWidthNama ? 'sm:col-span-2' : '' }}">
        <label class="block font-bold text-slate-700 uppercase mb-1">
            {{ $labelNama }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <input type="text" name="{{ $prefix }}nama" value="{{ $resolvedNama }}" @if($m = $getModel('nama')) x-model="{{ $m }}" @endif {{ $required ? 'required' : '' }} placeholder="{{ $placeholderNama }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'nama') border-rose-500 focus:ring-rose-500/20 @enderror">
        @error($prefix . 'nama')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
    </div>

    @if($showJenis)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Lembaga</label>
            @if(!empty($jenisOptions))
                <select name="{{ $prefix }}jenis" @if($m = $getModel('jenis')) x-model="{{ $m }}" @endif class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'jenis') border-rose-500 focus:ring-rose-500/20 @enderror">
                    @foreach($jenisOptions as $optVal => $optLabel)
                        @php
                            $val = is_numeric($optVal) ? $optLabel : $optVal;
                            $lbl = $optLabel;
                        @endphp
                        <option value="{{ $val }}" {{ $resolvedJenis === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            @else
                <input type="text" name="{{ $prefix }}jenis" value="{{ $resolvedJenis }}" @if($m = $getModel('jenis')) x-model="{{ $m }}" @endif placeholder="Contoh: Masjid / Yayasan / Sekolah" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'jenis') border-rose-500 focus:ring-rose-500/20 @enderror">
            @endif
            @error($prefix . 'jenis')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>
    @endif

    @if($showKetua)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">{{ $labelKetua }}</label>
            <input type="text" name="{{ $prefix }}ketua_nama" value="{{ $resolvedKetuaNama }}" @if($m = $getModel('ketua_nama')) x-model="{{ $m }}" @endif placeholder="{{ $placeholderKetua }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'ketua_nama') border-rose-500 focus:ring-rose-500/20 @enderror">
            @error($prefix . 'ketua_nama')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>

        @if($showKetuaNik)
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">NIK Ketua</label>
                <input type="text" name="{{ $prefix }}ketua_nik" value="{{ $resolvedKetuaNik }}" @if($m = $getModel('ketua_nik')) x-model="{{ $m }}" @endif inputmode="numeric" maxlength="16" minlength="16" pattern="[0-9]{16}" title="Harus 16 digit angka" placeholder="3301..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'ketua_nik') border-rose-500 focus:ring-rose-500/20 @enderror">
                @error($prefix . 'ketua_nik')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
            </div>
        @endif
    @endif

    <div>
        <label class="block font-bold text-slate-700 uppercase mb-1">{{ $labelTelepon }}</label>
        <input type="text" name="{{ $prefix }}{{ $teleponFieldName }}" value="{{ $resolvedTelepon }}" @if($m = $getModel($teleponModelKey)) x-model="{{ $m }}" @endif placeholder="08123456789" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . $teleponFieldName) border-rose-500 focus:ring-rose-500/20 @enderror">
        @error($prefix . $teleponFieldName)<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
    </div>

    @if($showEmail)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Email</label>
            <input type="email" name="{{ $prefix }}email" value="{{ $resolvedEmail }}" @if($m = $getModel('email')) x-model="{{ $m }}" @endif placeholder="lembaga@domain.com" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'email') border-rose-500 focus:ring-rose-500/20 @enderror">
            @error($prefix . 'email')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>
    @endif

    {{ $slot }}
</div>
