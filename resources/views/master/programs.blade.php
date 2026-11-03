@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    isProgramModalOpen: false,
    isProgramEditing: false,
    program_id: null,
    program_kode: '',
    program_nama: '',
    program_sort_order: 0,
    program_is_active: true,

    isSubprogramModalOpen: false,
    isSubprogramEditing: false,
    subprogram_id: null,
    subprogram_nama: '',
    subprogram_jenis: 'Pendistribusian',
    subprogram_sort_order: 0,
    subprogram_is_active: true,

    openCreateProgram() {
        this.program_id = null;
        this.program_kode = '';
        this.program_nama = '';
        this.program_sort_order = 0;
        this.program_is_active = true;
        this.isProgramEditing = false;
        this.isProgramModalOpen = true;
    },

    openEditProgram(p) {
        this.program_id = p.id;
        this.program_kode = p.kode;
        this.program_nama = p.nama;
        this.program_sort_order = p.sort_order;
        this.program_is_active = !!p.is_active;
        this.isProgramEditing = true;
        this.isProgramModalOpen = true;
    },

    openCreateSubprogram() {
        this.subprogram_id = null;
        this.subprogram_nama = '';
        this.subprogram_jenis = 'Pendistribusian';
        this.subprogram_sort_order = 0;
        this.subprogram_is_active = true;
        this.isSubprogramEditing = false;
        this.isSubprogramModalOpen = true;
    },

    openEditSubprogram(s) {
        this.subprogram_id = s.id;
        this.subprogram_nama = s.nama;
        this.subprogram_jenis = s.jenis;
        this.subprogram_sort_order = s.sort_order;
        this.subprogram_is_active = !!s.is_active;
        this.isSubprogramEditing = true;
        this.isSubprogramModalOpen = true;
    }
}">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Programs -->
        <div class="col-span-1 lg:col-span-4 xl:col-span-4 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-[calc(100vh-8rem)] border-t-[4px] border-t-emerald-500">
            <div class="p-5 text-center">
                <h2 class="text-xl font-bold text-gray-800">Daftar Program</h2>
            </div>
            
            <div class="grid grid-cols-12 gap-2 px-5 py-3 bg-gray-50/50 text-xs font-bold text-gray-400 uppercase tracking-wider">
                <div class="col-span-2">NO</div>
                <div class="col-span-10">PROGRAM</div>
            </div>
            
            <div class="overflow-y-auto flex-1">
                @forelse($programs as $index => $program)
                <div onclick="window.location.href='{{ route('master.programs', ['program_id' => $program->id]) }}'" class="group cursor-pointer grid grid-cols-12 gap-2 px-5 py-4 border-b border-gray-100 transition-colors {{ $selectedProgramId == $program->id ? 'bg-[#c3d8cd] hover:bg-[#b3ccbe]' : 'hover:bg-gray-50/80' }}">
                    <div class="col-span-2 flex items-start pt-1 text-gray-500 font-medium">
                        {{ $index + 1 }}
                    </div>
                    <div class="col-span-10 flex justify-between items-start">
                        <div>
                            <div class="font-bold text-gray-800 text-base leading-tight">{{ $program->nama }}</div>
                            <div class="text-sm text-gray-500 mt-1">Sub Program: {{ $program->subprograms_count }}</div>
                        </div>
                        <div class="hidden group-hover:flex space-x-2 bg-white/50 px-2 py-1 rounded-md" onclick="event.stopPropagation()">
                            <button type="button" @click="openEditProgram({{ $program->toJson() }})" class="text-indigo-500 hover:text-indigo-700 transition-colors" title="Edit Program">
                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                            </button>
                            <form method="POST" action="{{ route('master.programs.program.destroy', $program->id) }}" data-confirm="Yakin ingin menghapus program ini?" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 transition-colors" title="Hapus Program">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-5 text-center text-sm text-gray-500">Belum ada data program.</div>
                @endforelse
            </div>
            
            <div class="p-4 flex justify-center mt-auto border-t border-gray-50">
                <button type="button" @click="openCreateProgram()" class="bg-[#118243]/10 text-[#118243] w-full font-semibold text-sm hover:bg-[#118243]/20 py-2.5 rounded-lg flex items-center justify-center transition-colors">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    Tambah Program Baru
                </button>
            </div>
        </div>

        <!-- Middle Column: Subprograms -->
        <div class="col-span-1 lg:col-span-5 xl:col-span-5 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-[calc(100vh-8rem)] border-t-[4px] border-t-green-400">
            @if($selectedProgramId)
                @php $selectedProgram = $programs->firstWhere('id', $selectedProgramId); @endphp
                
                <div class="p-5 text-center">
                    <h3 class="font-bold text-gray-800 text-xl">Daftar Sub Program</h3>
                    <p class="text-[15px] text-gray-500 mt-1">({{ $selectedProgram->nama ?? '' }})</p>
                </div>
                
                <div class="px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-3 border-y border-gray-50">
                    <form method="GET" action="{{ route('master.programs') }}" class="relative w-full sm:w-64">
                        <input type="hidden" name="program_id" value="{{ $selectedProgramId }}">
                        <input type="text" name="search_subprogram" value="{{ $searchSubprogram ?? '' }}" placeholder="Cari..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-300 shadow-xs rounded-xl focus:outline-none focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] text-sm transition-all">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-heroicon-o-magnifying-glass class="h-4 w-4 text-gray-400" />
                        </div>
                    </form>
                    <button type="button" @click="openCreateSubprogram()" class="bg-[#118243] w-full sm:w-auto hover:bg-[#0e6b36] text-white font-medium py-2 px-4 rounded-xl flex items-center justify-center transition-colors shadow-sm text-sm">
                        <x-heroicon-o-plus class="w-4 h-4 mr-1.5" />
                        Tambah Sub
                    </button>
                </div>
                
                <div class="px-6 pb-2 mt-4 flex-1 flex flex-col overflow-hidden">
                    <div class="grid grid-cols-12 gap-2 px-4 py-3 bg-gray-50/80 text-[11px] font-bold text-gray-500 uppercase tracking-wider rounded-t-xl border border-gray-100">
                        <div class="col-span-2 text-center">URUT</div>
                        <div class="col-span-6">NAMA SUB PROGRAM</div>
                        <div class="col-span-2">JENIS</div>
                        <div class="col-span-2 text-right">AKSI</div>
                    </div>
                    
                    <div class="overflow-y-auto bg-white rounded-b-xl border-x border-b border-gray-100 flex-1">
                        @forelse($subprograms as $index => $sub)
                        <div class="grid grid-cols-12 gap-2 px-4 py-3.5 border-b border-gray-50 last:border-b-0 items-center hover:bg-[#eaf5ef]/50 transition-colors group">
                            <div class="col-span-2 text-gray-500 font-medium text-xs text-center">
                                {{ $selectedProgram->sort_order }}.{{ $sub->sort_order }}
                            </div>
                            <div class="col-span-6">
                                <div class="text-gray-800 font-semibold text-xs">{{ $sub->nama }}</div>
                            </div>
                            <div class="col-span-2">
                                <span class="text-[11px] text-gray-500">{{ $sub->jenis }}</span>
                            </div>
                            <div class="col-span-2 flex justify-end space-x-1">
                                <button type="button" @click="openEditSubprogram({{ $sub->toJson() }})" class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center transition-colors" title="Edit">
                                    <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                </button>
                                 <form method="POST" action="{{ route('master.programs.subprogram.destroy', $sub->id) }}" data-confirm="Yakin ingin menghapus subprogram ini?" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors" title="Hapus">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                    </button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="p-8 text-center text-xs text-gray-400">Belum ada data sub-program.</div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center flex-1 text-center p-8">
                    <p class="text-gray-500 font-medium text-sm">Pilih Program Terlebih Dahulu</p>
                </div>
            @endif
        </div>

        <!-- Right Column: Asnaf -->
        <div class="col-span-1 lg:col-span-3 xl:col-span-3 bg-white rounded-xl shadow-sm border border-gray-100 border-t-[4px] border-t-sky-400 flex flex-col h-[calc(100vh-8rem)]">
            <div class="p-5 text-center border-b border-gray-50">
                <h3 class="font-bold text-gray-800 text-lg">Kategori Asnaf</h3>
                <p class="text-[13px] text-gray-500 mt-1">({{ count($asnafs) }} Golongan)</p>
            </div>
            
            <div class="px-5 py-3 bg-gray-50/50 border-b border-gray-100 flex justify-between text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                <div class="w-8 text-left">NO</div>
                <div class="flex-1 px-2">ASNAF</div>
                <div class="w-16 text-right">POSISI</div>
            </div>
            
            <div class="overflow-y-auto flex-1">
                @foreach($asnafs as $index => $asnaf)
                <div class="flex justify-between items-center px-5 py-3 border-b border-gray-50 {{ $index % 2 == 0 ? 'bg-gray-50/30' : 'bg-white' }}">
                    <div class="w-8 text-left text-gray-500 font-medium text-xs">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 px-2 font-bold text-gray-800 text-xs">
                        {{ $asnaf->nama }}
                    </div>
                    <div class="w-16 flex justify-end space-x-1.5">
                        <form method="POST" action="{{ route('master.programs.asnaf.up', $asnaf->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="w-6 h-6 flex items-center justify-center rounded-lg border {{ $loop->first ? 'border-emerald-100 bg-emerald-50/50 text-emerald-200 cursor-not-allowed' : 'border-emerald-500 bg-emerald-500 text-white hover:bg-emerald-600 shadow-sm' }} transition-colors" @if($loop->first) disabled @endif title="Geser ke Atas">
                                <x-heroicon-o-arrow-up class="h-3 w-3 stroke-[3]" />
                            </button>
                        </form>
                        <form method="POST" action="{{ route('master.programs.asnaf.down', $asnaf->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="w-6 h-6 flex items-center justify-center rounded-lg border {{ $loop->last ? 'border-rose-100 bg-rose-50/50 text-rose-200 cursor-not-allowed' : 'border-rose-500 bg-rose-500 text-white hover:bg-rose-600 shadow-sm' }} transition-colors" @if($loop->last) disabled @endif title="Geser ke Bawah">
                                <x-heroicon-o-arrow-down class="h-3 w-3 stroke-[3]" />
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal Program -->
    <div x-show="isProgramModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-slate-800" x-text="isProgramEditing ? 'Edit Program' : 'Tambah Program Baru'"></h3>
                <button type="button" @click="isProgramModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('master.programs.program.store') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="program_id" x-model="program_id">

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Kode Program <span class="text-red-500">*</span></label>
                    <input type="text" name="program_kode" x-model="program_kode" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Nama Program <span class="text-red-500">*</span></label>
                    <input type="text" name="program_nama" x-model="program_nama" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Urutan Ditampilkan</label>
                        <input type="number" name="program_sort_order" x-model="program_sort_order" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>
                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-700">
                            <input type="checkbox" name="program_is_active" value="1" x-model="program_is_active" class="rounded text-[#118243]">
                            <span>Status Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="isProgramModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0e6c37] text-white font-bold rounded-xl shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Subprogram -->
    <div x-show="isSubprogramModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl border border-slate-200">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-bold text-base text-slate-800" x-text="isSubprogramEditing ? 'Edit Sub Program' : 'Tambah Sub Program Baru'"></h3>
                <button type="button" @click="isSubprogramModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Tutup">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('master.programs.subprogram.store') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="program_id" value="{{ $selectedProgramId }}">
                <input type="hidden" name="subprogram_id" x-model="subprogram_id">

                <div>
                    <label class="block font-bold text-slate-600 uppercase mb-1">Nama Sub Program <span class="text-red-500">*</span></label>
                    <input type="text" name="subprogram_nama" x-model="subprogram_nama" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Jenis Program <span class="text-red-500">*</span></label>
                        <select name="subprogram_jenis" x-model="subprogram_jenis" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                            <option value="Pendistribusian">Pendistribusian</option>
                            <option value="Pendayagunaan">Pendayagunaan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-600 uppercase mb-1">Urutan Ditampilkan</label>
                        <input type="number" name="subprogram_sort_order" x-model="subprogram_sort_order" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer font-semibold text-slate-700">
                        <input type="checkbox" name="subprogram_is_active" value="1" x-model="subprogram_is_active" class="rounded text-[#118243]">
                        <span>Status Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="isSubprogramModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0e6c37] text-white font-bold rounded-xl shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
