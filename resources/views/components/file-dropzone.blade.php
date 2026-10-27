@props([
    'name',
    'id' => null,
    'accept' => null,
    'required' => false,
    'capture' => null,
    'label' => null,
    'helpText' => null,
    'icon' => 'upload',
    'existingFileUrl' => null,
    'existingFileName' => null,
    'compact' => false,
    'maxSizeMb' => 10,
])

@php
    $inputId = $id ?? ($name . '_' . str_replace('.', '_', uniqid('', true)));
    $hasExisting = !empty($existingFileUrl);
@endphp

<div
    x-data="{
        isDragging: false,
        fileName: '',
        fileSize: '',
        fileType: '',
        previewUrl: '',
        errorMessage: '',
        hasFile: false,
        existingUrl: {{ json_encode($existingFileUrl) }},
        existingName: {{ json_encode($existingFileName ?? ($existingFileUrl ? basename($existingFileUrl) : '')) }},
        maxSizeMb: {{ (int) $maxSizeMb }},

        onDragOver(e) {
            e.preventDefault();
            this.isDragging = true;
        },
        onDragLeave(e) {
            e.preventDefault();
            this.isDragging = false;
        },
        onDrop(e) {
            e.preventDefault();
            this.isDragging = false;
            this.errorMessage = '';
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length) {
                this.$refs.input.files = dt.files;
                this.handleFile(dt.files[0]);
                this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },
        onChange(e) {
            this.errorMessage = '';
            if (e.target.files && e.target.files.length) {
                this.handleFile(e.target.files[0]);
            }
        },
        handleFile(file) {
            if (!file) return;
            const maxBytes = this.maxSizeMb * 1024 * 1024;
            if (file.size > maxBytes) {
                this.errorMessage = `Ukuran file melebihi batas maksimum (${this.maxSizeMb} MB).`;
                this.clearFile();
                return;
            }

            this.fileName = file.name;
            const kb = Math.round(file.size / 1024);
            this.fileSize = kb >= 1024 ? (kb / 1024).toFixed(1) + ' MB' : kb + ' KB';
            this.hasFile = true;
            this.errorMessage = '';

            const ext = (file.name.split('.').pop() || '').toLowerCase();
            const isImg = file.type.startsWith('image/') || ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'].includes(ext);
            const isExcel = ['xlsx', 'xls', 'csv'].includes(ext);
            const isPdf = ext === 'pdf';

            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = '';
            }

            if (isImg) {
                this.fileType = 'image';
                this.previewUrl = URL.createObjectURL(file);
            } else if (isExcel) {
                this.fileType = 'excel';
            } else if (isPdf) {
                this.fileType = 'pdf';
            } else {
                this.fileType = 'doc';
            }

            this.$dispatch('file-selected', {
                name: file.name,
                size: file.size,
                sizeFormatted: this.fileSize,
                file: file
            });
        },
        clearFile() {
            this.$refs.input.value = '';
            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = '';
            }
            this.fileName = '';
            this.fileSize = '';
            this.fileType = '';
            this.hasFile = false;
            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
            this.$dispatch('file-cleared');
        },
        triggerBrowse() {
            this.$refs.input.click();
        }
    }"
    class="w-full space-y-1"
    {{ $attributes }}
>
    @if($label)
        <label for="{{ $inputId }}" class="block font-bold text-slate-700 text-xs uppercase mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div
        @dragover.prevent="onDragOver($event)"
        @dragleave.prevent="onDragLeave($event)"
        @drop.prevent="onDrop($event)"
        @click="triggerBrowse()"
        :class="{
            'border-[#118243] bg-emerald-50/80 ring-4 ring-emerald-500/20 scale-[1.008]': isDragging,
            'border-slate-300 bg-slate-50/60 hover:border-emerald-500 hover:bg-emerald-50/20': !isDragging && !hasFile,
            'border-emerald-400 bg-emerald-50/30': hasFile
        }"
        class="relative border-2 border-dashed rounded-2xl cursor-pointer transition-all duration-200 text-center select-none {{ $compact ? 'p-3 sm:p-4' : 'p-5 sm:p-6' }}"
    >
        <!-- Actual Hidden HTML Input for standard form submission -->
        <input
            x-ref="input"
            type="file"
            name="{{ $name }}"
            id="{{ $inputId }}"
            @if($accept) accept="{{ $accept }}" @endif
            @if($capture) capture="{{ $capture }}" @endif
            @if($required) :required="!hasFile && !existingUrl" @endif
            @change="onChange($event)"
            class="sr-only"
        >

        <!-- STATE 1: KOSONG / BELUM PILIH FILE BARU -->
        <div x-show="!hasFile" class="space-y-2">
            <!-- Icon -->
            <div class="flex justify-center items-center">
                @if($icon === 'excel')
                    <div class="w-10 h-10 {{ $compact ? 'w-8 h-8' : '' }} rounded-xl bg-emerald-100 text-[#118243] flex items-center justify-center shadow-2xs">
                        <svg class="{{ $compact ? 'w-4 h-4' : 'w-5 h-5' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                @elseif($icon === 'camera')
                    <div class="w-10 h-10 {{ $compact ? 'w-8 h-8' : '' }} rounded-xl bg-emerald-100 text-[#118243] flex items-center justify-center shadow-2xs">
                        <svg class="{{ $compact ? 'w-4 h-4' : 'w-5 h-5' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                @else
                    <div class="w-10 h-10 {{ $compact ? 'w-8 h-8' : '' }} rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center group-hover:bg-emerald-100 group-hover:text-emerald-700 transition-colors shadow-2xs">
                        <svg class="{{ $compact ? 'w-4 h-4' : 'w-5 h-5' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Primary Label -->
            <div class="{{ $compact ? 'text-xs' : 'text-xs sm:text-sm' }} text-slate-700 font-medium">
                <span class="font-bold text-slate-800">Tarik & lepas file ke sini</span>, atau 
                <span class="text-[#118243] font-bold underline hover:text-[#0d6835]">Pilih Berkas</span>
            </div>

            <!-- Helper Text -->
            <p class="text-[11px] text-slate-400">
                {{ $helpText ?? ($accept ? 'Format didukung: ' . $accept . ' (Maks ' . $maxSizeMb . ' MB)' : 'Maksimal ukuran file ' . $maxSizeMb . ' MB') }}
            </p>

            <!-- Existing file banner (jika ada file saat ini dalam mode edit) -->
            @if($hasExisting)
                <div @click.stop class="mt-3 pt-3 border-t border-slate-200/80 flex items-center justify-between text-left text-xs bg-white/80 p-2.5 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-2 truncate min-w-0">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="text-slate-500 text-[11px] shrink-0">Berkas saat ini:</span>
                        <span class="font-semibold text-slate-700 truncate" x-text="existingName"></span>
                    </div>
                    <a
                        :href="existingUrl"
                        target="_blank"
                        class="text-[#118243] hover:text-[#0d6835] font-bold text-xs inline-flex items-center gap-1 shrink-0 ml-2 hover:underline"
                    >
                        <span>Lihat</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>

        <!-- STATE 2: SUDAH ADA FILE TERPILIH -->
        <div x-show="hasFile" x-cloak class="text-left" @click.stop>
            <div class="flex items-center justify-between gap-3 bg-white p-3 rounded-xl border border-emerald-200 shadow-2xs">
                <!-- Preview Thumbnail / File Icon -->
                <div class="flex items-center gap-3 min-w-0">
                    <template x-if="fileType === 'image'">
                        <img :src="previewUrl" alt="Preview" class="w-12 h-12 object-cover rounded-lg border border-slate-200 shrink-0 shadow-2xs">
                    </template>
                    <template x-if="fileType === 'excel'">
                        <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-800 flex flex-col items-center justify-center font-bold text-[10px] shrink-0 border border-emerald-200 shadow-2xs">
                            <svg class="w-5 h-5 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>XLS</span>
                        </div>
                    </template>
                    <template x-if="fileType === 'pdf'">
                        <div class="w-12 h-12 rounded-lg bg-rose-100 text-rose-800 flex flex-col items-center justify-center font-bold text-[10px] shrink-0 border border-rose-200 shadow-2xs">
                            <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>PDF</span>
                        </div>
                    </template>
                    <template x-if="fileType === 'doc'">
                        <div class="w-12 h-12 rounded-lg bg-slate-100 text-slate-700 flex flex-col items-center justify-center font-bold text-[10px] shrink-0 border border-slate-200 shadow-2xs">
                            <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>DOC</span>
                        </div>
                    </template>

                    <!-- File Details -->
                    <div class="min-w-0 flex-1">
                        <span class="font-bold text-slate-800 text-xs sm:text-sm truncate block" x-text="fileName"></span>
                        <div class="text-[11px] text-emerald-700 font-medium flex items-center gap-1.5 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <span x-text="fileSize"></span>
                            <span class="text-slate-300">•</span>
                            <span class="text-slate-500">Siap diunggah</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-1 shrink-0 ml-2">
                    <button
                        type="button"
                        @click="triggerBrowse()"
                        class="px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-emerald-700 hover:bg-slate-100 rounded-lg transition-colors"
                    >
                        Ganti
                    </button>
                    <button
                        type="button"
                        @click="clearFile()"
                        class="px-2.5 py-1.5 text-xs font-bold text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error message alert -->
    <div x-show="errorMessage" x-cloak class="p-2.5 bg-red-50 border border-red-200 rounded-xl flex items-center gap-2 text-xs text-red-700 font-medium">
        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span x-text="errorMessage"></span>
    </div>
</div>
