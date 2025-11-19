@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="pengajuanUpzCreateForm()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('pengajuan.upz.index') }}" class="p-2 bg-white text-gray-600 hover:text-[#118243] hover:bg-[#eaf5ef] rounded-xl border border-slate-200 shadow-sm transition-all inline-flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Buat Pengajuan Bantuan UPZ</h1>
                <p class="text-sm text-slate-500">Formulir pendaftaran permohonan rekomendasi bantuan dari Unit Pengumpul Zakat.</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('pengajuan.upz.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- 1. Identifikasi UPZ Pengaju -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">1</span>
                Unit Pengumpul Zakat (UPZ) Pengaju
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Pilih UPZ Terdaftar <span class="text-red-500">*</span></label>
                    <select name="upz_id" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                        <option value="">-- Pilih UPZ --</option>
                        @foreach(\App\Models\Upz::orderBy('nama')->get() as $u)
                            <option value="{{ $u->id }}">{{ $u->nama }} ({{ $u->jenis }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nama PIC / Pengurus UPZ Pengaju</label>
                    <input type="text" name="pic_nama" placeholder="Nama ketua/sekretaris UPZ..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>
            </div>
        </div>

        <!-- 2. Surat Permohonan & Detail Pengajuan -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">2</span>
                Informasi Surat Rekomendasi UPZ
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Perihal / Judul Surat Permohonan <span class="text-red-500">*</span></label>
                    <input type="text" name="judul_surat" required placeholder="Contoh: Rekomendasi Bantuan Mustahik Warga Binaan UPZ..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Nomor Surat Masuk</label>
                    <input type="text" name="nomor_surat" placeholder="001/UPZ/..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Surat <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_surat" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Pelaksanaan / Agenda</label>
                    <input type="date" name="tanggal_pelaksanaan" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Opsional (Rencana kegiatan/agenda)</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tanggal Diterima BAZNAS <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_diterima" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block font-bold text-slate-700 uppercase mb-1">Uraian / Ringkasan Rekomendasi</label>
                    <textarea name="uraian_permohonan" rows="3" placeholder="Keterangan rekomendasi bantuan..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all"></textarea>
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <x-file-dropzone 
                        name="file_surat" 
                        accept=".pdf,.jpg,.jpeg,.png"
                        label="File Dokumen Surat Rekomendasi UPZ (PDF / JPG / PNG)"
                        help-text="Tarik & lepas file surat rekomendasi atau klik untuk pilih berkas (Maks 10 MB)"
                    />
                </div>
            </div>
        </div>

        <!-- 3. Daftar Mustahik Rekomendasi UPZ -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">3</span>
                    Daftar Mustahik Penerima Rekomendasi
                </h3>
                <button type="button" @click="addMustahik()" class="bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold px-3 py-1.5 rounded-lg text-xs flex items-center gap-1">
                    + Tambah Mustahik
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(m, index) in mustahiks" :key="index">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-xs text-slate-700 uppercase" x-text="'Mustahik #' + (index + 1)"></span>
                            <button type="button" @click="removeMustahik(index)" x-show="mustahiks.length > 1" class="text-red-500 hover:text-red-700 text-xs font-semibold">
                                Hapus
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nama Mustahik <span class="text-red-500">*</span></label>
                                <input type="text" :name="'mustahiks[' + index + '][nama]'" x-model="m.nama" required placeholder="Nama penerima..." class="w-full rounded-xl border border-slate-300 bg-white p-2 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">NIK (16 Digit)</label>
                                <input type="text" :name="'mustahiks[' + index + '][nik]'" x-model="m.nik" placeholder="3301..." class="w-full rounded-xl border border-slate-300 bg-white p-2 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Pekerjaan</label>
                                <input type="text" :name="'mustahiks[' + index + '][pekerjaan]'" x-model="m.pekerjaan" class="w-full rounded-xl border border-slate-300 bg-white p-2 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Program BAZNAS <span class="text-red-500">*</span></label>
                                <select :name="'mustahiks[' + index + '][program_id]'" x-model="m.program_id" @change="onProgramChange(m)" required class="w-full rounded-xl border border-slate-300 bg-white p-2 text-xs font-semibold shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                                    <option value="">-- Pilih Program --</option>
                                    <template x-for="p in programs" :key="p.id">
                                        <option :value="p.id" x-text="p.nama" :selected="String(m.program_id) === String(p.id)"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Sub Program <span class="text-red-500">*</span></label>
                                <select :name="'mustahiks[' + index + '][subprogram_id]'" x-model="m.subprogram_id" :disabled="!m.program_id" required class="w-full rounded-xl border border-slate-300 bg-white p-2 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all disabled:bg-slate-100 disabled:text-slate-400 disabled:shadow-none">
                                    <option value="" x-text="m.program_id ? '-- Pilih Sub Program --' : '-- Pilih Program Terlebih Dahulu --'"></option>
                                    <template x-for="sp in getSubprograms(m.program_id)" :key="sp.id">
                                        <option :value="sp.id" x-text="sp.nama" :selected="String(m.subprogram_id) === String(sp.id)"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Keterangan / Rekomendasi Bantuan</label>
                                <input type="text" :name="'mustahiks[' + index + '][keterangan]'" x-model="m.keterangan" placeholder="Catatan bantuan..." class="w-full rounded-xl border border-slate-300 bg-white p-2 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('pengajuan.upz.index') }}" class="px-6 py-3 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors text-sm">
                Batal
            </a>
            <button type="submit" class="px-8 py-3 bg-[#118243] hover:bg-[#0d6835] text-white font-bold rounded-xl shadow-md transition-all text-sm flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Simpan & Daftarkan Pengajuan UPZ
            </button>
        </div>
    </form>
</div>

<script>
function pengajuanUpzCreateForm() {
    return {
        programs: @json($programs),
        all_subprograms: @json($subprograms),

        getSubprograms(programId) {
            if (!programId) return [];
            return this.all_subprograms.filter(sp => String(sp.program_id) === String(programId));
        },

        onProgramChange(m) {
            m.subprogram_id = '';
        },

        mustahiks: [
            {
                nama: '',
                nik: '',
                pekerjaan: '',
                alamat: '',
                telepon: '',
                program_id: '',
                subprogram_id: '',
                keterangan: ''
            }
        ],
        addMustahik() {
            this.mustahiks.push({
                nama: '',
                nik: '',
                pekerjaan: '',
                alamat: '',
                telepon: '',
                program_id: '',
                subprogram_id: '',
                keterangan: ''
            });
        },
        removeMustahik(index) {
            if (this.mustahiks.length > 1) {
                this.mustahiks.splice(index, 1);
            }
        }
    };
}
</script>
@endsection
