@props([
    'penerima',
    'index',
    'programs',
    'subprograms',
    'showEdit' => true,
])

@php
    $program = $penerima['program_id'] ? collect($programs)->firstWhere('id', $penerima['program_id']) : null;
    $subprogram = $penerima['subprogram_id'] ? collect($subprograms)->firstWhere('id', $penerima['subprogram_id']) : null;
@endphp

<div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3 relative transition-all" x-data="{ expanded: false }">
    <!-- Header Row -->
    <div class="flex justify-between items-start">
        <div class="flex items-center gap-2.5">
            <span class="w-2 h-2 rounded-full bg-[#118243]"></span>
            <span class="font-bold text-xs text-slate-700 uppercase">
                Penerima Manfaat #{{ $index + 1 }}
                <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-semibold
                    {{ $penerima['jenis'] === 'perorangan' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                    {{ $penerima['jenis'] === 'perorangan' ? 'Perorangan' : 'Lembaga' }}
                </span>
            </span>
        </div>
        <div class="flex items-center gap-1.5">
            @if($showEdit)
                <button type="button"
                        @click="$dispatch('edit-penerima', { penerima: penerima, index: {{ $index }} })"
                        class="px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </button>
            @endif
            <button type="button"
                    @click="$dispatch('remove-penerima', { index: {{ $index }} })"
                    class="text-red-500 hover:text-red-700 text-xs font-bold inline-flex items-center gap-1 p-1.5 rounded-lg hover:bg-red-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Summary Fields (Read-Only) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
        <div>
            <label class="block font-semibold text-slate-500 mb-0.5 uppercase text-[10px]">Nama</label>
            <div class="font-bold text-slate-800" x-text="penerima.summary.nama || '-'"></div>
        </div>

        <div>
            <label class="block font-semibold text-slate-500 mb-0.5 uppercase text-[10px]">
                {{ $penerima['jenis'] === 'perorangan' ? 'NIK' : 'Ketua' }}
            </label>
            <div class="font-medium text-slate-700 font-mono text-[11px]" x-text="penerima.summary.nikOrKetua || '-'"></div>
        </div>

        <div class="sm:col-span-2 lg:col-span-3">
            <label class="block font-semibold text-slate-500 mb-0.5 uppercase text-[10px]">Alamat</label>
            <div class="text-slate-600 text-[11px] line-clamp-2" x-text="penerima.summary.alamat || '-'"></div>
        </div>

        <div>
            <label class="block font-semibold text-slate-500 mb-0.5 uppercase text-[10px]">Program BAZNAS</label>
            <div class="font-medium text-slate-700" x-text="penerima.program_id ? ('{{ $program ? $program->nama : '-' }}') : 'Belum dipilih'"></div>
        </div>

        <div>
            <label class="block font-semibold text-slate-500 mb-0.5 uppercase text-[10px]">Sub Program</label>
            <div class="font-medium text-slate-700" x-text="penerima.subprogram_id ? ('{{ $subprogram ? $subprogram->nama : '-' }}') : 'Belum dipilih'"></div>
        </div>

        <div>
            <label class="block font-semibold text-slate-500 mb-0.5 uppercase text-[10px]">Keterangan</label>
            <div class="text-slate-600 text-[11px]" x-text="penerima.keterangan || '-'"></div>
        </div>
    </div>

    <!-- Hidden inputs for form submission -->
    <input type="hidden" :name="'penerima_manfaat[' + {{ $index }} + '][id]'" :value="penerima.id">
    <input type="hidden" :name="'penerima_manfaat[' + {{ $index }} + '][jenis]'" :value="penerima.jenis">
    <input type="hidden" :name="'penerima_manfaat[' + {{ $index }} + '][person_id]'" :value="penerima.person_id">
    <input type="hidden" :name="'penerima_manfaat[' + {{ $index }} + '][lembaga_id]'" :value="penerima.lembaga_id">
    <input type="hidden" :name="'penerima_manfaat[' + {{ $index }} + '][program_id]'" :value="penerima.program_id">
    <input type="hidden" :name="'penerima_manfaat[' + {{ $index }} + '][subprogram_id]'" :value="penerima.subprogram_id">
    <input type="hidden" :name="'penerima_manfaat[' + {{ $index }} + '][keterangan]'" :value="penerima.keterangan">
</div>