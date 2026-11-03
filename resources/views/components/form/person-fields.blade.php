@props([
    'prefix' => '',
    'person' => null,
    'nik' => null,
    'labelNik' => 'NIK Pemohon (16 Digit)',
    'nama' => null,
    'labelNama' => 'Nama Lengkap Pemohon',
    'nomorKk' => null,
    'pekerjaan' => null,
    'telepon' => null,
    'labelTelepon' => 'No. Kontak / WA',
    'jenisKelamin' => null,
    'showJenisKelamin' => false,
    'showNomorKk' => true,
    'showPekerjaan' => true,
    'showTtl' => false,
    'tempatLahir' => null,
    'tanggalLahir' => null,
    'showEmail' => false,
    'email' => null,
    'required' => true,
    'gridClass' => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4',
    'alpineModel' => null,
])

@php
    $resolvedNik = old($prefix . 'nik', $nik ?? $person?->nik);
    $resolvedNama = old($prefix . 'nama', $nama ?? $person?->nama);
    $resolvedNomorKk = old($prefix . 'nomor_kk', $nomorKk ?? $person?->nomor_kk);
    $resolvedPekerjaan = old($prefix . 'pekerjaan', $pekerjaan ?? $person?->pekerjaan);
    $resolvedTelepon = old($prefix . 'telepon', $telepon ?? ($person?->telepon ?? $person?->handphone));
    $resolvedJenisKelamin = old($prefix . 'jenis_kelamin', $jenisKelamin ?? ($person?->jenis_kelamin ?? $person?->gender));
    $resolvedTempatLahir = old($prefix . 'tempat_lahir', $tempatLahir ?? $person?->tempat_lahir);
    $rawTgl = old($prefix . 'tanggal_lahir', $tanggalLahir ?? $person?->tanggal_lahir);
    $resolvedTanggalLahir = $rawTgl instanceof \DateTimeInterface
        ? $rawTgl->format('Y-m-d')
        : ($rawTgl ? substr((string)$rawTgl, 0, 10) : null);
    $resolvedEmail = old($prefix . 'email', $email ?? $person?->email);

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
    <div>
        <label class="block font-bold text-slate-700 uppercase mb-1">
            {{ $labelNik }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <input type="text" name="{{ $prefix }}nik" value="{{ $resolvedNik }}" @if($m = $getModel('nik')) x-model="{{ $m }}" @endif {{ $required ? 'required' : '' }} inputmode="numeric" maxlength="16" minlength="16" pattern="[0-9]{16}" title="Harus 16 digit angka" placeholder="3301..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'nik') border-rose-500 focus:ring-rose-500/20 @enderror">
        @error($prefix . 'nik')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block font-bold text-slate-700 uppercase mb-1">
            {{ $labelNama }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <input type="text" name="{{ $prefix }}nama" value="{{ $resolvedNama }}" @if($m = $getModel('nama')) x-model="{{ $m }}" @endif {{ $required ? 'required' : '' }} placeholder="Nama lengkap sesuai KTP" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'nama') border-rose-500 focus:ring-rose-500/20 @enderror">
        @error($prefix . 'nama')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
    </div>

    @if($showNomorKk)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Nomor KK</label>
            <input type="text" name="{{ $prefix }}nomor_kk" value="{{ $resolvedNomorKk }}" @if($m = $getModel('nomor_kk')) x-model="{{ $m }}" @endif inputmode="numeric" maxlength="16" minlength="16" pattern="[0-9]{16}" title="Harus 16 digit angka" placeholder="3301..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'nomor_kk') border-rose-500 focus:ring-rose-500/20 @enderror">
            @error($prefix . 'nomor_kk')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>
    @endif

    @if($showJenisKelamin)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin</label>
            <select name="{{ $prefix }}jenis_kelamin" @if($m = $getModel('jenis_kelamin')) x-model="{{ $m }}" @endif class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'jenis_kelamin') border-rose-500 focus:ring-rose-500/20 @enderror">
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L" {{ $resolvedJenisKelamin === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ $resolvedJenisKelamin === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
            @error($prefix . 'jenis_kelamin')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>
    @endif

    @if($showTtl)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Tempat Lahir</label>
            <input type="text" name="{{ $prefix }}tempat_lahir" value="{{ $resolvedTempatLahir }}" @if($m = $getModel('tempat_lahir')) x-model="{{ $m }}" @endif placeholder="Kota / Kabupaten" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'tempat_lahir') border-rose-500 focus:ring-rose-500/20 @enderror">
            @error($prefix . 'tempat_lahir')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Lahir</label>
            <input type="date" name="{{ $prefix }}tanggal_lahir" value="{{ $resolvedTanggalLahir }}" @if($m = $getModel('tanggal_lahir')) x-model="{{ $m }}" @endif class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'tanggal_lahir') border-rose-500 focus:ring-rose-500/20 @enderror">
            @error($prefix . 'tanggal_lahir')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>
    @endif

    @if($showPekerjaan)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Pekerjaan</label>
            <input type="text" name="{{ $prefix }}pekerjaan" value="{{ $resolvedPekerjaan }}" @if($m = $getModel('pekerjaan')) x-model="{{ $m }}" @endif placeholder="Contoh: Buruh / Pedagang / Petani" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'pekerjaan') border-rose-500 focus:ring-rose-500/20 @enderror">
            @error($prefix . 'pekerjaan')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>
    @endif

    <div>
        <label class="block font-bold text-slate-700 uppercase mb-1">{{ $labelTelepon }}</label>
        <input type="text" name="{{ $prefix }}telepon" value="{{ $resolvedTelepon }}" @if($m = $getModel('telepon')) x-model="{{ $m }}" @endif placeholder="08123456789" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'telepon') border-rose-500 focus:ring-rose-500/20 @enderror">
        @error($prefix . 'telepon')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
    </div>

    @if($showEmail)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Email</label>
            <input type="email" name="{{ $prefix }}email" value="{{ $resolvedEmail }}" @if($m = $getModel('email')) x-model="{{ $m }}" @endif placeholder="nama@domain.com" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all @error($prefix . 'email') border-rose-500 focus:ring-rose-500/20 @enderror">
            @error($prefix . 'email')<p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p>@enderror
        </div>
    @endif

    {{ $slot }}
</div>
