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
    $resolvedNik = $nik ?? $person?->nik;
    $resolvedNama = $nama ?? $person?->nama;
    $resolvedNomorKk = $nomorKk ?? $person?->nomor_kk;
    $resolvedPekerjaan = $pekerjaan ?? $person?->pekerjaan;
    $resolvedTelepon = $telepon ?? ($person?->telepon ?? $person?->handphone);
    $resolvedJenisKelamin = $jenisKelamin ?? ($person?->jenis_kelamin ?? $person?->gender);
    $resolvedTempatLahir = $tempatLahir ?? $person?->tempat_lahir;
    $rawTgl = $tanggalLahir ?? $person?->tanggal_lahir;
    $resolvedTanggalLahir = $rawTgl instanceof \DateTimeInterface
        ? $rawTgl->format('Y-m-d')
        : ($rawTgl ? substr((string)$rawTgl, 0, 10) : null);
    $resolvedEmail = $email ?? $person?->email;

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
        <input type="text" name="{{ $prefix }}nik" value="{{ $resolvedNik }}" @if($m = $getModel('nik')) x-model="{{ $m }}" @endif {{ $required ? 'required' : '' }} placeholder="3301..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
    </div>

    <div>
        <label class="block font-bold text-slate-700 uppercase mb-1">
            {{ $labelNama }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <input type="text" name="{{ $prefix }}nama" value="{{ $resolvedNama }}" @if($m = $getModel('nama')) x-model="{{ $m }}" @endif {{ $required ? 'required' : '' }} placeholder="Nama lengkap sesuai KTP" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
    </div>

    @if($showNomorKk)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Nomor KK</label>
            <input type="text" name="{{ $prefix }}nomor_kk" value="{{ $resolvedNomorKk }}" @if($m = $getModel('nomor_kk')) x-model="{{ $m }}" @endif placeholder="3301..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
        </div>
    @endif

    @if($showJenisKelamin)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Jenis Kelamin</label>
            <select name="{{ $prefix }}jenis_kelamin" @if($m = $getModel('jenis_kelamin')) x-model="{{ $m }}" @endif class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="L" {{ $resolvedJenisKelamin === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ $resolvedJenisKelamin === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>
    @endif

    @if($showTtl)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Tempat Lahir</label>
            <input type="text" name="{{ $prefix }}tempat_lahir" value="{{ $resolvedTempatLahir }}" @if($m = $getModel('tempat_lahir')) x-model="{{ $m }}" @endif placeholder="Kota / Kabupaten" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
        </div>

        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Lahir</label>
            <input type="date" name="{{ $prefix }}tanggal_lahir" value="{{ $resolvedTanggalLahir }}" @if($m = $getModel('tanggal_lahir')) x-model="{{ $m }}" @endif class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
        </div>
    @endif

    @if($showPekerjaan)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Pekerjaan</label>
            <input type="text" name="{{ $prefix }}pekerjaan" value="{{ $resolvedPekerjaan }}" @if($m = $getModel('pekerjaan')) x-model="{{ $m }}" @endif placeholder="Contoh: Buruh / Pedagang / Petani" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
        </div>
    @endif

    <div>
        <label class="block font-bold text-slate-700 uppercase mb-1">{{ $labelTelepon }}</label>
        <input type="text" name="{{ $prefix }}telepon" value="{{ $resolvedTelepon }}" @if($m = $getModel('telepon')) x-model="{{ $m }}" @endif placeholder="08123456789" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
    </div>

    @if($showEmail)
        <div>
            <label class="block font-bold text-slate-700 uppercase mb-1">Email</label>
            <input type="email" name="{{ $prefix }}email" value="{{ $resolvedEmail }}" @if($m = $getModel('email')) x-model="{{ $m }}" @endif placeholder="nama@domain.com" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:border-[#118243] focus:outline-none focus:ring-2 focus:ring-[#118243]/20 transition-all">
        </div>
    @endif

    {{ $slot }}
</div>
