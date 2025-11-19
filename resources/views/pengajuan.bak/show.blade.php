@extends('layouts.app')

@section('content')
@php
    $provinsis = \App\Models\Wilayah::provinsis()->orderBy('nama')->get();
    $isLembaga = in_array($pengajuan->jenis_permohonan, ['Lembaga', 'UPZ/Lembaga']) || $pengajuan->lembaga_id !== null;
    $applicantName = $isLembaga 
        ? ($pengajuan->lembaga?->nama ?? $pengajuan->upz?->nama ?? $pengajuan->lembaga_nama_snapshot ?? 'Nama Lembaga')
        : ($pengajuan->mustahik?->person?->nama ?? $pengajuan->mustahik_nama_snapshot ?? 'Nama Pemohon');
    
    $totalNominalDisetujui = (int) $pengajuan->pengajuanMustahiks->sum('nominal');
    $isDisetujui = $pengajuan->persetujuan && $pengajuan->persetujuan->status === 'disetujui';
    $hasLpj = $pengajuan->lpjBa !== null;
    $totalMustahikCount = $pengajuan->pengajuanMustahiks->count();
    $totalNominalDisalurkan = (int) $pengajuan->pengajuanMustahiks->whereNotNull('tanggal_penyaluran')->sum('nominal');
    if ($totalNominalDisalurkan == 0 && $pengajuan->penyaluran && $pengajuan->penyaluran->tanggal_penyaluran) {
        $totalNominalDisalurkan = (int) ($pengajuan->penyaluran->nominal_penyaluran ?: $totalNominalDisetujui);
    }
    $sisaBelumDisalurkan = max(0, $totalNominalDisetujui - $totalNominalDisalurkan);
    $disalurkanMustahikCount = $pengajuan->pengajuanMustahiks->whereNotNull('tanggal_penyaluran')->count();
    $belumSalurMustahikCount = $pengajuan->pengajuanMustahiks->whereNull('tanggal_penyaluran')->where('nominal', '>', 0)->count();

    $eligibleMustahikSalur = $pengajuan->pengajuanMustahiks->filter(fn($pm) => ($pm->nominal ?? 0) > 0);
    $isAllDisalurkan = $eligibleMustahikSalur->isNotEmpty() && $eligibleMustahikSalur->every(fn($pm) => !is_null($pm->tanggal_penyaluran));
    $hasAnySalur = $disalurkanMustahikCount > 0 || ($pengajuan->penyaluran && $pengajuan->penyaluran->tanggal_penyaluran);
    $isSalur = $isAllDisalurkan || ($pengajuan->penyaluran && ($pengajuan->penyaluran->is_salur ?? true) && $pengajuan->penyaluran->tanggal_penyaluran);

    $totalLpjMustahik = $pengajuan->pengajuanMustahiks->filter(function($pm) {
        return $pm->lampirans->where('category', 'lpj_mustahik')->isNotEmpty();
    })->count();

    // Survey dianggap selesai (centang hijau) jika semua mustahik telah diisi kesimpulan surveynya (hasil_akhir terisi)
    $hasCompletedSurvey = $totalMustahikCount > 0 && $pengajuan->pengajuanMustahiks->every(function($pm) {
        return $pm->survey && !empty($pm->survey->hasil_akhir);
    });

    // Disposisi dianggap selesai (lengkap) jika ada mustahik dan seluruh mustahik telah memiliki disposisi / nominal & sumber dana
    $hasAllDisposisi = $totalMustahikCount > 0 && $pengajuan->pengajuanMustahiks->every(function($pm) {
        return $pm->disposisi !== null || ($pm->nominal > 0 && $pm->sumber_dana_id !== null);
    });
    $isDisposisiComplete = $isDisetujui || $hasAllDisposisi;

    // Cek apakah sudah ada disposisi (pada mustahik atau persetujuan difinalisasi)
    $hasAnyDisposisi = $pengajuan->pengajuanMustahiks->contains(function($pm) {
        return $pm->disposisi !== null || ($pm->nominal > 0 && $pm->sumber_dana_id !== null);
    }) || $isDisetujui || $pengajuan->persetujuan !== null;

    // Status penyelesaian per tahapan untuk indikator centang
    $isStep1Done = $totalMustahikCount > 0;
    $isStep2Done = $hasCompletedSurvey;
    $isStep3Done = $isDisposisiComplete;
    $isStep4Done = $isSalur;
    $isStep5Done = $hasLpj;
@endphp

<div class="w-full space-y-6" x-data="pengajuanDetailApp()">
    
    <!-- ======================================================== -->
    <!-- 1. TOP HEADER BOX (SESUAI FORMAT GAMBAR REFERENSI)       -->
    <!-- ======================================================== -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="space-y-1">
            <div class="text-xs font-semibold text-slate-500">
                Tgl Pengajuan: {{ $pengajuan->tanggal_surat ? $pengajuan->tanggal_surat->locale('id')->isoFormat('D MMM Y') : ($pengajuan->created_at ? $pengajuan->created_at->locale('id')->isoFormat('D MMM Y') : '-') }}
            </div>
            
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-800 font-mono">
                {{ $pengajuan->nomor_pengajuan }}
            </h1>

            <div class="text-sm sm:text-base font-semibold text-slate-600">
                {{ $pengajuan->judul_surat }}
            </div>

            <div class="flex items-center gap-1.5 text-xs sm:text-sm text-slate-500 pt-0.5">
                @if($isLembaga)
                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>
                @else
                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                @endif
                <span>Pemohon:</span>
                <strong class="font-semibold text-slate-800">{{ $applicantName }}</strong>
            </div>
        </div>

        <!-- Quick Top Action Buttons -->
        <div class="flex flex-wrap items-center gap-2 shrink-0 self-start sm:self-auto">
            <a href="{{ $pengajuan->is_upz ? route('pengajuan.upz.index') : route('pengajuan.umum.index') }}" 
               class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-medium rounded-xl text-xs shadow-2xs flex items-center gap-1.5 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                Kembali
            </a>

            @if($isDisposisiComplete)
                <a href="{{ route('pengajuan.cetak-disposisi', $pengajuan->id) }}" target="_blank" 
                   class="px-3.5 py-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-medium rounded-xl text-xs shadow-2xs flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" /></svg>
                    Cetak Disposisi
                </a>

                <a href="{{ route('pengajuan.cetak-form-penyaluran', $pengajuan->id) }}" target="_blank" 
                   class="px-3.5 py-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-medium rounded-xl text-xs shadow-2xs flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    Form Penyaluran
                </a>
            @endif

            @if(!$isDisetujui && !$pengajuan->ditolak_at)
                <a href="{{ route('pengajuan.edit', $pengajuan->id) }}" 
                   class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium rounded-xl text-xs shadow-2xs flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                    Edit Pengajuan
                </a>

                <a href="{{ route('pengajuan.edit-pemohon', $pengajuan->id) }}" 
                   class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium rounded-xl text-xs shadow-2xs flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    Edit Pemohon
                </a>
            @endif
        </div>
    </div>

    @php
        $showRouteName = $pengajuan->is_upz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';

        // Tentukan langkah selanjutnya yang perlu dilakukan berdasarkan data yang belum lengkap
        $warningTitle = null;
        $warningDesc = null;
        $warningBtnText = null;
        $warningBtnRoute = null;
        $warningType = 'warning'; // 'warning' (amber), 'info' (blue), 'danger' (rose), 'success' (emerald)

        if ($pengajuan->ditolak_at) {
            $warningType = 'danger';
            $warningTitle = 'Permohonan Bantuan Ditolak';
            $warningDesc = 'Pengajuan bantuan ini telah ditolak pimpinan pada ' . ($pengajuan->ditolak_at ? $pengajuan->ditolak_at->locale('id')->isoFormat('D MMMM Y') : 'sistem') . '.';
        } elseif (!$isStep1Done) {
            $warningType = 'warning';
            $warningTitle = 'Langkah Selanjutnya: Tambahkan Data Penerima Manfaat';
            $warningDesc = 'Belum ada data penerima manfaat terdaftar. Silakan tambahkan minimal 1 penerima manfaat untuk melanjutkan ke tahapan survey.';
            $warningBtnText = 'Tambah Penerima Manfaat';
            $warningBtnRoute = route($showRouteName, ['pengajuan' => $pengajuan->id, 'tab' => 'detail']);
        } elseif (!$isStep2Done) {
            $warningType = 'warning';
            $warningTitle = 'Langkah Selanjutnya: Lengkapi Hasil Survey Kelayakan';
            $warningDesc = 'Data mustahik telah terdaftar, namun hasil asesmen penilaian Had Kifayah atau observasi lapangan belum diisi.';
            $warningBtnText = 'Isi Form Survey';
            $warningBtnRoute = route($showRouteName, ['pengajuan' => $pengajuan->id, 'tab' => 'survey']);
        } elseif (!$isStep3Done) {
            $warningType = 'warning';
            $warningTitle = 'Langkah Selanjutnya: Menunggu Penetapan Disposisi Pimpinan';
            $warningDesc = 'Survey kelayakan telah selesai. Silakan tetapkan nominal yang disetujui pimpinan dan tentukan akun rekening sumber dana (18 COA).';
            $warningBtnText = 'Proses Disposisi';
            $warningBtnRoute = route($showRouteName, ['pengajuan' => $pengajuan->id, 'tab' => 'persetujuan']);
        } elseif (!$isStep4Done) {
            $warningType = 'info';
            $warningTitle = 'Langkah Selanjutnya: Realisasikan Kas Keluar / Penyaluran Dana';
            $warningDesc = 'Nominal bantuan telah disetujui pimpinan (Total: Rp ' . number_format($totalNominalDisetujui, 0, ',', '.') . '). Silakan lakukan pencairan dana dan catat bukti penyaluran.';
            $warningBtnText = 'Catat Penyaluran';
            $warningBtnRoute = route($showRouteName, ['pengajuan' => $pengajuan->id, 'tab' => 'penyaluran']);
        } elseif (!$isStep5Done) {
            $warningType = 'warning';
            $warningTitle = 'Langkah Selanjutnya: Unggah LPJ & Berita Acara (BAST)';
            $warningDesc = 'Dana bantuan telah berhasil disalurkan. Segera lengkapi Berita Acara Serah Terima (BAST), foto serah terima, dan laporan pertanggungjawaban.';
            $warningBtnText = 'Unggah Berkas LPJ';
            $warningBtnRoute = route($showRouteName, ['pengajuan' => $pengajuan->id, 'tab' => 'lpj']);
        } else {
            $warningType = 'success';
            $warningTitle = 'Semua Tahapan Lengkap & Selesai';
            $warningDesc = 'Seluruh rangkaian berkas, survey lapangan, persetujuan pimpinan, penyaluran kas, hingga dokumen LPJ & BAST telah lengkap dan terdokumentasi dengan baik.';
        }
    @endphp

    <!-- ======================================================== -->
    <!-- ACTION GUIDANCE & WARNING CARD (TAHAPAN SELANJUTNYA)     -->
    <!-- ======================================================== -->
    @if($warningType === 'danger')
        <div class="bg-rose-50/90 border border-rose-200/90 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-semibold text-rose-900">{{ $warningTitle }}</h4>
                    <p class="text-xs text-rose-700/90 mt-0.5 leading-relaxed">{{ $warningDesc }}</p>
                </div>
            </div>
        </div>
    @elseif($warningType === 'warning')
        <div class="bg-amber-50/90 border border-amber-200/90 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-semibold text-amber-900">{{ $warningTitle }}</h4>
                    <p class="text-xs text-amber-800/90 mt-0.5 leading-relaxed">{{ $warningDesc }}</p>
                </div>
            </div>
            @if($warningBtnRoute && $warningBtnText)
                <a href="{{ $warningBtnRoute }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-xl text-xs shadow-2xs inline-flex items-center justify-center gap-1.5 shrink-0 self-start sm:self-auto transition-all">
                    <span>{{ $warningBtnText }}</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            @endif
        </div>
    @elseif($warningType === 'info')
        <div class="bg-blue-50/90 border border-blue-200/90 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-semibold text-blue-900">{{ $warningTitle }}</h4>
                    <p class="text-xs text-blue-800/90 mt-0.5 leading-relaxed">{{ $warningDesc }}</p>
                </div>
            </div>
            @if($warningBtnRoute && $warningBtnText)
                <a href="{{ $warningBtnRoute }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs shadow-2xs inline-flex items-center justify-center gap-1.5 shrink-0 self-start sm:self-auto transition-all">
                    <span>{{ $warningBtnText }}</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            @endif
        </div>
    @elseif($warningType === 'success')
        <div class="bg-emerald-50/90 border border-emerald-200/90 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-semibold text-emerald-900">{{ $warningTitle }}</h4>
                    <p class="text-xs text-emerald-800/90 mt-0.5 leading-relaxed">{{ $warningDesc }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- 2. WORKFLOW TAB BAR (DENGAN ROUTE MASING-MASING TAB)     -->
    <!-- ======================================================== -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs px-6 pt-3">
        <nav id="workflowTabsNav" class="flex space-x-8 overflow-x-auto text-xs sm:text-sm font-medium scroll-smooth" aria-label="Tabs">
            <a href="{{ route($showRouteName, ['pengajuan' => $pengajuan->id, 'tab' => 'detail']) }}" 
               id="tab-item-detail"
               class="inline-flex items-center gap-1.5 whitespace-nowrap pb-3.5 border-b-2 transition-colors {{ $activeTab === 'detail' ? 'border-[#118243] text-[#118243] font-semibold is-active-tab' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                @if($isStep1Done)
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                @else
                    <span>1.</span>
                @endif
                <span>Detail, Berkas & Penerima Manfaat</span>
            </a>

            <a href="{{ route($showRouteName, ['pengajuan' => $pengajuan->id, 'tab' => 'survey']) }}" 
               id="tab-item-survey"
               class="inline-flex items-center gap-1.5 whitespace-nowrap pb-3.5 border-b-2 transition-colors {{ $activeTab === 'survey' ? 'border-[#118243] text-[#118243] font-semibold is-active-tab' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                @if($isStep2Done)
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                @else
                    <span>2.</span>
                @endif
                <span>Survey Kelayakan</span>
            </a>

            <a href="{{ route($showRouteName, ['pengajuan' => $pengajuan->id, 'tab' => 'persetujuan']) }}" 
               id="tab-item-persetujuan"
               class="inline-flex items-center gap-1.5 whitespace-nowrap pb-3.5 border-b-2 transition-colors {{ $activeTab === 'persetujuan' ? 'border-[#118243] text-[#118243] font-semibold is-active-tab' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                @if($isStep3Done)
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                @else
                    <span>3.</span>
                @endif
                <span>Persetujuan Pimpinan & Disposisi</span>
            </a>

            <a href="{{ route($showRouteName, ['pengajuan' => $pengajuan->id, 'tab' => 'penyaluran']) }}" 
               id="tab-item-penyaluran"
               class="inline-flex items-center gap-1.5 whitespace-nowrap pb-3.5 border-b-2 transition-colors {{ $activeTab === 'penyaluran' ? 'border-[#118243] text-[#118243] font-semibold is-active-tab' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                @if($isStep4Done)
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                @else
                    <span>4.</span>
                @endif
                <span>Penyaluran Bantuan</span>
            </a>

            <a href="{{ route($showRouteName, ['pengajuan' => $pengajuan->id, 'tab' => 'lpj']) }}" 
               id="tab-item-lpj"
               class="inline-flex items-center gap-1.5 whitespace-nowrap pb-3.5 border-b-2 transition-colors {{ $activeTab === 'lpj' ? 'border-[#118243] text-[#118243] font-semibold is-active-tab' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                @if($isStep5Done)
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                @else
                    <span>5.</span>
                @endif
                <span>Laporan Pertanggungjawaban (LPJ)</span>
            </a>
        </nav>
    </div>

    <!-- ======================================================== -->
    <!-- 3. TAB 1 CONTENT: DETAIL, BERKAS & MUSTAHIK              -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'detail'" class="space-y-6">
        
        <!-- 2-COLUMN GRID (PERSIS GAMBAR REFERENSI) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            
            <!-- KOLOM KIRI: NOMOR, TGL INPUT, PEMOHON & TARGET PENERIMA -->
            <div class="space-y-6">
                
                <!-- CARD A: NOMOR PENGAJUAN & TGL INPUT SISTEM -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs divide-y divide-slate-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs sm:text-sm font-medium text-slate-500">
                            Nomor Pengajuan
                        </span>
                        <span class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-1.5 text-xs sm:text-sm font-mono font-semibold text-slate-800">
                            {{ $pengajuan->nomor_pengajuan }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between pt-3">
                        <span class="text-xs sm:text-sm font-medium text-slate-500 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                            Tgl Input Sistem
                        </span>
                        
                        <span class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-1.5 text-xs sm:text-sm font-mono font-semibold text-slate-800">
                            {{ $pengajuan->created_at ? $pengajuan->created_at->locale('id')->isoFormat('dddd, D MMMM Y') : '-' }}
                        </span>
                    </div>
                </div>

                <!-- CARD B: DATA PEMOHON -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $isLembaga ? 'DATA LEMBAGA / PEMOHON UTAMA' : 'DATA PEMOHON' }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $isLembaga ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                            {{ $pengajuan->is_upz ? 'Pengajuan UPZ' : ($isLembaga ? 'Lembaga / Organisasi' : 'Perorangan (Individu)') }}
                        </span>
                    </div>

                    @if($isLembaga)
                        @php
                            $lembaga = $pengajuan->lembaga ?? $pengajuan->upz;
                            $pic = $pengajuan->picLembaga ?? $pengajuan->picUpz;
                            $picPerson = $pic?->person;
                            $phoneLembaga = $picPerson?->telepon ?: ($lembaga?->telepon ?: $pengajuan->lembaga_kontak_snapshot);
                        @endphp
                        <div>
                            <h2 class="text-base sm:text-lg font-semibold text-slate-900">
                                {{ $lembaga?->nama ?? $pengajuan->lembaga_nama_snapshot ?? 'Nama Lembaga' }}
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-xs sm:text-sm">
                            <div>
                                <span class="text-slate-400 text-xs block mb-1">Jenis Lembaga</span>
                                <span class="font-medium text-slate-800">{{ $lembaga?->jenis ?? 'Lembaga / Organisasi' }}</span>
                            </div>

                            <div>
                                <span class="text-slate-400 text-xs block mb-1">PIC / Penanggung Jawab</span>
                                <span class="font-medium text-slate-800">{{ $picPerson?->nama ?? $pengajuan->pic_nama_snapshot ?? ($lembaga?->ketua_nama ?? '-') }}</span>
                            </div>

                            <div>
                                <span class="text-slate-400 text-xs block mb-1">Telepon / HP</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-slate-800">{{ $phoneLembaga ?: '-' }}</span>
                                    @if($phoneLembaga)
                                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $phoneLembaga) }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 text-xs font-semibold inline-flex items-center gap-0.5">
                                            <span>(WhatsApp</span>
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                            <span>)</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <span class="text-slate-400 text-xs block mb-1">Jumlah Anggota</span>
                                <span class="font-medium text-slate-800">{{ $lembaga?->jumlah_anggota ? $lembaga->jumlah_anggota . ' Orang' : '-' }}</span>
                            </div>

                            <div class="sm:col-span-2">
                                <span class="text-slate-400 text-xs block mb-1">Alamat Lengkap</span>
                                <span class="font-medium text-slate-800 leading-relaxed">{{ $lembaga?->alamat_lengkap ?? $pengajuan->lembaga_alamat_snapshot ?? '-' }}</span>
                            </div>
                        </div>
                    @else
                        @php
                            $person = $pengajuan->mustahik?->person;
                            $phone = $person?->telepon ?: ($person?->handphone ?: $pengajuan->mustahik_kontak_snapshot);
                            $ttl = [];
                            if ($person?->tempat_lahir) $ttl[] = $person->tempat_lahir;
                            if ($person?->tanggal_lahir) {
                                $ttl[] = $person->tanggal_lahir->format('d/m/Y');
                            }
                            $ttl_str = count($ttl) > 0 ? implode(', ', $ttl) : '-';
                        @endphp
                        <div>
                            <h2 class="text-base sm:text-lg font-semibold text-slate-900">
                                {{ $person?->nama ?? $pengajuan->mustahik_nama_snapshot ?? 'Nama Pemohon' }}
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-xs sm:text-sm">
                            <div>
                                <span class="text-slate-400 text-xs block mb-1">NIK</span>
                                <span class="font-medium text-slate-800 font-mono tracking-wide">{{ $person?->nik ?? $pengajuan->mustahik_nik_snapshot ?? '-' }}</span>
                            </div>

                            <div>
                                <span class="text-slate-400 text-xs block mb-1">Telepon / HP</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-slate-800">{{ $phone ?: '-' }}</span>
                                    @if($phone)
                                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $phone) }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 text-xs font-semibold inline-flex items-center gap-0.5">
                                            <span>(WhatsApp</span>
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                            <span>)</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <span class="text-slate-400 text-xs block mb-1">Pekerjaan</span>
                                <span class="font-medium text-slate-800">{{ $person?->mustahik?->pekerjaan ?: '-' }}</span>
                            </div>

                            <div>
                                <span class="text-slate-400 text-xs block mb-1">Tempat, Tgl Lahir</span>
                                <span class="font-medium text-slate-800">{{ $ttl_str }}</span>
                            </div>

                            <div class="sm:col-span-2">
                                <span class="text-slate-400 text-xs block mb-1">Alamat Lengkap</span>
                                <span class="font-medium text-slate-800 leading-relaxed">{{ $person?->alamat_lengkap ?? $pengajuan->mustahik_alamat_snapshot ?? '-' }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- CARD C: TARGET PENERIMA MANFAAT -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold tracking-wider text-slate-400 uppercase flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            TARGET PENERIMA MANFAAT
                        </span>
                        <div class="mt-1 flex items-baseline gap-2">
                            <span class="text-2xl sm:text-3xl font-bold text-slate-800">{{ $totalMustahikCount }}</span>
                            <span class="text-xl sm:text-2xl font-bold text-[#118243]">Penerima</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Total penerima manfaat terdaftar dalam permohonan bantuan ini.
                        </p>
                    </div>

                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-[#118243] flex items-center justify-center shrink-0 border border-emerald-100">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: INFORMASI SURAT & DOKUMEN -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                <span class="text-xs font-semibold tracking-wider text-slate-400 uppercase block">
                    INFORMASI SURAT & DOKUMEN PERMOHONAN
                </span>

                <div>
                    <span class="text-slate-400 text-xs block mb-1">Perihal / Judul Surat</span>
                    <h3 class="text-sm sm:text-base font-semibold text-slate-800">
                        {{ $pengajuan->judul_surat }}
                    </h3>
                </div>

                @if($pengajuan->uraian_permohonan)
                    <div>
                        <span class="text-slate-400 text-xs block mb-1.5">Uraian / Deskripsi Permohonan</span>
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-slate-700 text-xs sm:text-sm leading-relaxed">
                            {{ $pengajuan->uraian_permohonan }}
                        </div>
                    </div>
                @endif

                <!-- List rows bergaris halus -->
                <div class="border-t border-slate-100 divide-y divide-slate-100 text-xs sm:text-sm">
                    
                    <!-- Dokumen Pengajuan Row -->
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-start gap-2">
                        <span class="text-slate-500 font-medium sm:w-44 shrink-0">Dokumen Pengajuan</span>
                        <div class="flex-1 space-y-2.5">
                            @if($pengajuan->file_surat)
                                <div class="flex items-center justify-between text-emerald-700 bg-emerald-50/40 p-2.5 rounded-xl border border-emerald-100">
                                    <a href="{{ Storage::url($pengajuan->file_surat) }}" target="_blank" class="flex items-center gap-1.5 font-medium hover:underline">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                        <span>Surat Permohonan Utama</span>
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                    </a>
                                    <form method="POST" action="{{ route('pengajuan.surat.destroy', $pengajuan->id) }}" data-confirm="Hapus file surat permohonan utama?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 bg-white text-rose-600 border border-rose-200 hover:bg-rose-50 rounded-lg text-xs font-medium inline-flex items-center gap-1 transition-colors">
                                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            Hapus Dokumen
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="flex items-center gap-1.5 text-slate-400">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                    <span>Tidak ada dokumen utama</span>
                                </div>
                            @endif

                            @foreach($pengajuan->lampirans as $lamp)
                                <div class="flex items-center justify-between text-emerald-700 bg-emerald-50/40 p-2.5 rounded-xl border border-emerald-100">
                                    <a href="{{ Storage::url($lamp->file_path ?? $lamp->path_file) }}" target="_blank" class="flex items-center gap-1.5 font-medium hover:underline">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                        <span>{{ $lamp->name ?? $lamp->nama_file }} ({{ strtolower($lamp->category ?? $lamp->kategori ?? 'lampiran') }})</span>
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                    </a>
                                    <form method="POST" action="{{ route('pengajuan.lampiran.destroy', ['id' => $pengajuan->id, 'lampiranId' => $lamp->id]) }}" data-confirm="Hapus lampiran dokumen ini?" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 bg-white text-rose-600 border border-rose-200 hover:bg-rose-50 rounded-lg text-xs font-medium inline-flex items-center gap-1 transition-colors">
                                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            Hapus Dokumen
                                        </button>
                                    </form>
                                </div>
                            @endforeach

                            <div class="pt-1">
                                <button type="button" @click="showLampiranModal = true" class="text-xs text-[#118243] hover:text-[#0d6835] font-semibold inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    Unggah Dokumen Tambahan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tgl & Nomor Surat Row -->
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-baseline gap-2">
                        <span class="text-slate-500 font-medium sm:w-44 shrink-0">Tgl & Nomor Surat</span>
                        <div>
                            <div class="font-semibold text-slate-800">
                                {{ $pengajuan->tanggal_surat ? $pengajuan->tanggal_surat->locale('id')->isoFormat('dddd, D MMMM Y') : '-' }}
                            </div>
                            <div class="text-xs font-mono text-slate-400 mt-0.5">
                                {{ $pengajuan->nomor_surat ?: '-' }}
                            </div>
                        </div>
                    </div>

                    <!-- Tgl Diterima Surat Row -->
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-baseline gap-2">
                        <span class="text-slate-500 font-medium sm:w-44 shrink-0">Tgl Diterima Surat</span>
                        <span class="font-semibold text-slate-800">
                            {{ $pengajuan->tanggal_diterima ? $pengajuan->tanggal_diterima->locale('id')->isoFormat('dddd, D MMMM Y') : '-' }}
                        </span>
                    </div>

                    <!-- Tgl Pelaksanaan Row -->
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-baseline gap-2">
                        <span class="text-slate-500 font-medium sm:w-44 shrink-0">Tgl Pelaksanaan</span>
                        <span class="font-semibold text-slate-800">
                            {{ $pengajuan->tanggal_pelaksanaan ? $pengajuan->tanggal_pelaksanaan->locale('id')->isoFormat('dddd, D MMMM Y') : '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- DAFTAR MUSTAHIK & PROGRAM BAZNAS TABLE -->
        @include('pengajuan.components.penerima-manfaat-manager', [
            'mode' => 'server',
            'pengajuan' => $pengajuan,
            'programs' => $programs,
            'subprograms' => $subprograms,
            'asnafs' => $asnafs,
            'isLocked' => $hasAnyDisposisi,
        ])
    </div>

    <!-- ======================================================== -->
    <!-- 4. TAB 2: SURVEY KELAYAKAN (MASTER-DETAIL LAYOUT)        -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'survey'" class="space-y-4 sm:space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 sm:p-6 lg:p-7">
            <div class="pb-4 sm:pb-5 border-b border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold text-slate-800">Hasil Survey & Peninjauan Lapangan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Survey Kelayakan dikelola per penerima manfaat (Perorangan: Had Kifayah & Asesmen, Lembaga: Status Layak Dibantu & Catatan).</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-semibold self-start sm:self-auto shrink-0">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Tahap 2 dari 5
                    </span>
                </div>
            </div>

            @if($pengajuan->pengajuanMustahiks->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    <p class="text-sm font-medium text-slate-600">Belum ada mustahik pada pengajuan ini.</p>
                    <p class="text-xs text-slate-400 mt-1">Silakan tambahkan data mustahik terlebih dahulu pada tab Detail & Berkas.</p>
                </div>
            @else
                <!-- MASTER-DETAIL 2 COLUMN GRID -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 items-start pt-4 sm:pt-6">
                    
                    <!-- LEFT COLUMN: DAFTAR MUSTAHIK (MASTER) -->
                    <div class="lg:col-span-4 space-y-3">
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#118243]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                                <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider">Daftar Penerima Manfaat</span>
                            </div>
                            <span class="px-2 py-0.5 text-[11px] font-medium bg-slate-100 text-slate-600 rounded-full">
                                {{ $pengajuan->pengajuanMustahiks->count() }} Penerima Manfaat
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 px-1">Pilih penerima manfaat untuk mengelola surveynya:</p>

                        <!-- Scrollable list of cards -->
                        <div class="space-y-3 max-h-[720px] overflow-y-auto pr-1">
                            @foreach($pengajuan->pengajuanMustahiks as $pm)
                                @php
                                    $pmSurvey = $pm->survey;
                                    $hasHk = $pmSurvey && $pmSurvey->hadKifayah;
                                    $hasAsesmen = $pmSurvey && $pmSurvey->asesmen;
                                    $pmStatus = $pmSurvey?->hasil_akhir ? strtolower($pmSurvey->hasil_akhir) : null;
                                @endphp
                                <div @click="selectedSurveyMustahikId = {{ $pm->id }}"
                                     :class="selectedSurveyMustahikId === {{ $pm->id }} ? 'border-[#118243] ring-2 ring-[#118243]/20 bg-emerald-50/20' : 'border-slate-200/80 bg-white hover:border-slate-300 hover:bg-slate-50/60'"
                                     class="p-4 rounded-2xl border transition-all cursor-pointer relative text-left">
                                    
                                    <!-- Top Row: Badge Type & Status -->
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase bg-slate-100 text-slate-600 rounded-md">
                                            <svg class="w-3 h-3 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                            {{ $pengajuan->is_upz ? 'LEMBAGA' : 'PERORANGAN' }}
                                        </span>

                                        @if($pmStatus === 'layak')
                                            <span class="px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase bg-emerald-100 text-emerald-800 rounded-md">
                                                LAYAK
                                            </span>
                                        @elseif($pmStatus === 'tidak layak')
                                            <span class="px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase bg-rose-100 text-rose-800 rounded-md">
                                                TIDAK LAYAK
                                            </span>
                                        @elseif($pmStatus === 'dipertimbangkan')
                                            <span class="px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase bg-amber-100 text-amber-800 rounded-md">
                                                DIPERTIMBANGKAN
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-semibold tracking-wider uppercase bg-slate-100 text-slate-500 rounded-md">
                                                BELUM
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Middle: Name, NIK, Program -->
                                    <h4 class="text-xs sm:text-sm font-semibold text-slate-800 truncate">
                                        {{ $pm->mustahik?->person?->nama ?? $pm->nama ?? '-' }}
                                    </h4>
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                        NIK: {{ $pm->mustahik?->person?->nik ?? $pm->nik ?? '-' }}
                                    </div>
                                    <div class="text-xs text-slate-600 mt-1 truncate">
                                        Prog: <span class="font-medium text-slate-700">{{ $pm->program?->nama ?? '-' }}</span>
                                    </div>

                                    <!-- Bottom Badges: HK & Asesmen status -->
                                    <div class="flex items-center gap-2 mt-3 pt-2.5 border-t border-slate-100 text-[10px]">
                                        <span class="px-2 py-0.5 rounded {{ $hasHk ? 'bg-emerald-50 text-emerald-700 font-medium border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                            HK: {{ $hasHk ? 'Selesai' : 'Belum' }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded {{ $hasAsesmen ? 'bg-emerald-50 text-emerald-700 font-medium border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                            Asesmen: {{ $hasAsesmen ? 'Selesai' : 'Belum Selesai' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: DETAIL PANEL MUSTAHIK TERPILIH (DETAIL) -->
                    <div class="lg:col-span-8 space-y-6">
                        @foreach($pengajuan->pengajuanMustahiks as $pm)
                        
                            @php
                                $pmSurvey = $pm->survey;
                                $pmHk = $pmSurvey?->hadKifayah;
                                $pmAs = $pmSurvey?->asesmen;
                                $pmStatus = $pmSurvey?->hasil_akhir ? strtolower($pmSurvey->hasil_akhir) : null;
                                $surveyToken = $pmSurvey?->token;
                                $surveyPublicUrl = $surveyToken ? route('public.survey', ['token' => $surveyToken]) : '#';
                                
                                // Gabungkan foto asesmen + foto lampiran survey
                                $surveyPhotos = [];
                                if ($pmAs) {
                                    $photoFields = ['foto_tempat_tinggal', 'foto_atap', 'foto_lantai', 'foto_dinding', 'foto_dapur', 'foto_kamar_mandi', 'foto_usaha'];
                                    foreach ($photoFields as $f) {
                                        if (!empty($pmAs->$f)) {
                                            $surveyPhotos[] = [
                                                'url' => asset('storage/' . $pmAs->$f),
                                                'label' => ucwords(str_replace(['foto_', '_'], ['', ' '], $f)),
                                            ];
                                        }
                                    }
                                }
                                if ($pmSurvey && $pmSurvey->lampirans) {
                                    foreach ($pmSurvey->lampirans as $lamp) {
                                        $surveyPhotos[] = [
                                            'url' => asset('storage/' . $lamp->file_path),
                                            'label' => $lamp->name ?: 'Dokumentasi Lapangan',
                                        ];
                                    }
                                }
                            @endphp

                            <div x-show="selectedSurveyMustahikId === {{ $pm->id }}" class="space-y-6">
                                
                                <!-- MUSTAHIK HEADER CARD -->
                                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-xs">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="text-sm sm:text-base font-semibold text-slate-800">{{ $pm->mustahik?->person?->nama ?? $pm->nama ?? '-' }}</h3>
                                                <span class="px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider bg-emerald-100 text-emerald-800 rounded-md">
                                                    {{ $pengajuan->is_upz ? 'MUSTAHIK LEMBAGA' : 'MUSTAHIK PERORANGAN' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                NIK: <span class="font-mono">{{ $pm->mustahik?->person?->nik ?? $pm->nik ?? '-' }}</span> | Program: <span class="font-medium text-slate-700">{{ $pm->program?->nama ?? '-' }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto">
                                        @if($pmStatus)
                                            <span class="px-3 py-1 text-xs font-semibold uppercase tracking-wider bg-emerald-100 text-emerald-800 rounded-xl">
                                                STATUS SURVEY: SELESAI
                                            </span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-semibold uppercase tracking-wider bg-amber-100 text-amber-800 rounded-xl">
                                                STATUS SURVEY: BELUM DIISI
                                            </span>
                                        @endif

                                        @if($surveyToken)
                                            <a href="{{ $surveyPublicUrl }}" target="_blank" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-xl transition-colors inline-flex items-center gap-1.5" title="Buka form survey lapangan online">
                                                <span>Form Lapangan</span>
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- PUBLIC LINK SURVEY BAR (DARK THEME SESUAI GAMBAR) -->
                                @if($surveyToken)
                                    @php
                                        $pmPerson = $pm->mustahik?->person;
                                        $pmNama = $pmPerson?->nama ?? $pm->nama ?? 'Penerima Manfaat';
                                        $pmNik = $pmPerson?->nik ?? $pm->nik ?? '-';
                                        $pmHp = $pmPerson?->telepon ?: ($pmPerson?->handphone ?: '-');
                                        $pmAlamat = $pmPerson?->alamat_lengkap ?? '-';
                                        $pmProgram = $pm->program?->nama ?? '-';
                                        $pmSubprogram = $pm->subprogram?->nama ?? '-';

                                        $waMessage = "*TUGAS SURVEY LAPANGAN BAZNAS*\n"
                                            . "Nomor Pengajuan: {$pengajuan->nomor_pengajuan}\n"
                                            . "Judul: {$pengajuan->judul_surat}\n\n"
                                            . "*Data Mustahik yang Disurvey:*\n"
                                            . "• Nama: {$pmNama}\n"
                                            . "• NIK: {$pmNik}\n"
                                            . "• No. HP/WA: {$pmHp}\n"
                                            . "• Alamat: {$pmAlamat}\n"
                                            . "• Program: {$pmProgram} (" . $pmSubprogram . ")\n\n"
                                            . "Silakan buka tautan berikut untuk mengisi form survey Had Kifayah & survey asesmen :\n"
                                            . "{$surveyPublicUrl}\n\n"
                                            . "Terima kasih.";
                                    @endphp

                                    <div x-data="{ 
                                            copiedLink: false, 
                                            copiedWa: false,
                                            copyText(text, type) {
                                                navigator.clipboard.writeText(text).then(() => {
                                                    if (type === 'link') {
                                                        this.copiedLink = true;
                                                        setTimeout(() => this.copiedLink = false, 2500);
                                                    } else {
                                                        this.copiedWa = true;
                                                        setTimeout(() => this.copiedWa = false, 2500);
                                                    }
                                                });
                                            }
                                         }" 
                                         class="bg-[#0b1329] border border-slate-800/90 rounded-2xl p-4 sm:p-5 lg:p-6 shadow-md text-white space-y-3.5 sm:space-y-4">
                                        
                                        <!-- Top Badge & Device Info -->
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-semibold bg-emerald-950/80 border border-emerald-700/60 text-emerald-400">
                                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                                </svg>
                                                <span>Public Link Survey ({{ $pengajuan->is_upz ? 'Lembaga' : 'Perorangan' }})</span>
                                            </span>
                                            <span class="text-[10px] sm:text-xs text-slate-400 font-mono hidden sm:inline-block">
                                                Siap Dibagikan
                                            </span>
                                        </div>

                                        <!-- URL Box & Salin Link Button (Responsive Flex/Grid) -->
                                        <div class="flex flex-col sm:flex-row items-stretch gap-2 sm:gap-3">
                                            <div class="flex-1 min-w-0 bg-[#050b18] border border-slate-800 rounded-xl px-3.5 py-2.5 sm:px-4 text-xs sm:text-sm font-mono text-emerald-400 overflow-x-auto whitespace-nowrap scrollbar-thin select-all flex items-center shadow-inner">
                                                {{ $surveyPublicUrl }}
                                            </div>

                                            <button type="button" 
                                                    @click="copyText('{{ $surveyPublicUrl }}', 'link')"
                                                    class="w-full sm:w-auto px-4 py-2.5 bg-[#118243] hover:bg-[#0d6835] active:scale-95 text-white font-semibold rounded-xl text-xs sm:text-sm shadow-xs flex items-center justify-center gap-2 transition-all shrink-0">
                                                <template x-if="!copiedLink">
                                                    <span class="flex items-center gap-1.5">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                                                        <span>Salin Link</span>
                                                    </span>
                                                </template>
                                                <template x-if="copiedLink">
                                                    <span class="flex items-center gap-1.5 text-emerald-200">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                        <span>Link Tersalin!</span>
                                                    </span>
                                                </template>
                                            </button>
                                        </div>

                                        <!-- Bottom Helper & Salin Pesan WA Button -->
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 sm:gap-3 pt-2 sm:pt-2.5 border-t border-slate-800/90">
                                            <p class="text-[11px] sm:text-xs text-slate-400 leading-relaxed">
                                                Kirim tautan survey ini ke WhatsApp Surveyor untuk pengisian langsung.
                                            </p>

                                            <button type="button" 
                                                    @click="copyText(@js($waMessage), 'wa')"
                                                    class="w-full sm:w-auto px-3.5 py-2 sm:py-1.5 bg-slate-800/95 hover:bg-slate-700 active:scale-95 text-slate-200 hover:text-white font-medium rounded-xl text-xs shadow-xs flex items-center justify-center gap-1.5 transition-all shrink-0">
                                                <template x-if="!copiedWa">
                                                    <span class="flex items-center gap-1.5">
                                                        <svg class="w-4 h-4 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.54 1.771.82 2.796.82 3.18 0 5.767-2.586 5.768-5.766 0-3.18-2.587-5.766-5.768-5.766zm9.969 5.766c0 5.518-4.482 10-10 10-1.761 0-3.411-.458-4.85-1.258l-5.15 1.35 1.374-5.019c-.892-1.492-1.374-3.21-1.374-5.073 0-5.517 4.482-10 10-10 5.518 0 10 4.483 10 10z"/>
                                                        </svg>
                                                        <span>Salin Pesan WA</span>
                                                    </span>
                                                </template>
                                                <template x-if="copiedWa">
                                                    <span class="flex items-center gap-1.5 text-emerald-300 font-semibold">
                                                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                        <span>Pesan WA Tersalin!</span>
                                                    </span>
                                                </template>
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                <!-- 2 SECTION CARDS: HAD KIFAYAH & ASESMEN -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    
                                    <!-- CARD 1: HAD KIFAYAH -->
                                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">1</span>
                                                <h4 class="text-xs sm:text-sm font-semibold text-slate-800">Hasil Survey Had Kifayah</h4>
                                            </div>
                                            @if($pmHk)
                                                <span class="px-2 py-0.5 text-[10px] font-semibold uppercase bg-emerald-100 text-emerald-800 rounded-md">TERISI</span>
                                            @else
                                                <span class="px-2 py-0.5 text-[10px] font-semibold uppercase bg-amber-100 text-amber-800 rounded-md">BELUM TERISI</span>
                                            @endif
                                        </div>

                                        @if($pmHk)
                                            <div class="space-y-2.5 text-xs text-slate-700">
                                                <div class="flex justify-between py-1 border-b border-slate-100">
                                                    <span class="text-slate-400">Status Kelayakan HK:</span>
                                                    <span class="font-semibold text-emerald-700 uppercase">{{ $pmHk->status_kelayakan ?: '-' }}</span>
                                                </div>
                                                <div class="flex justify-between py-1 border-b border-slate-100">
                                                    <span class="text-slate-400">Kategori Prioritas:</span>
                                                    <span class="font-medium text-slate-800">{{ $pmHk->kategori_prioritas ?: '-' }}</span>
                                                </div>
                                                <div class="flex justify-between py-1 border-b border-slate-100">
                                                    <span class="text-slate-400">Pendapatan Total:</span>
                                                    <span class="font-medium text-slate-800">{{ $pmHk->pendapatan_txt ?: 'Rp ' . number_format((int)$pmHk->pendapatan_keluarga, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="flex justify-between py-1 border-b border-slate-100">
                                                    <span class="text-slate-400">Pengeluaran Kebutuhan:</span>
                                                    <span class="font-medium text-slate-800">{{ $pmHk->pengeluaran_txt ?: '-' }}</span>
                                                </div>
                                                <div class="flex justify-between py-1">
                                                    <span class="text-slate-400">Selisih Kebutuhan:</span>
                                                    <span class="font-medium text-slate-800">{{ $pmHk->selisih_txt ?: '-' }}</span>
                                                </div>

                                                @if($pmHk->link_had_kifayah)
                                                    <div class="pt-2">
                                                        <a href="{{ $pmHk->link_had_kifayah }}" target="_blank" class="text-xs text-[#118243] hover:underline font-medium inline-flex items-center gap-1">
                                                            <span>Lihat Dokumen Hasil Had Kifayah (Puskas)</span>
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="py-6 text-center text-slate-400 text-xs">
                                                <p>Surveyor belum melakukan kalkulasi Had Kifayah.</p>
                                                @if($surveyToken)
                                                    <a href="{{ $surveyPublicUrl }}" target="_blank" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-medium rounded-xl text-xs transition-colors">
                                                        <span>Kalkulasi Had Kifayah</span>
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <!-- CARD 2: ASESMEN LAPANGAN -->
                                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                                            <div class="flex items-center gap-2">
                                                <span class="w-6 h-6 rounded-lg bg-slate-800 text-white font-bold text-xs flex items-center justify-center">2</span>
                                                <h4 class="text-xs sm:text-sm font-semibold text-slate-800">Hasil Survey Asesmen Lapangan</h4>
                                            </div>
                                            @if($pmAs)
                                                <span class="px-2 py-0.5 text-[10px] font-semibold uppercase bg-emerald-100 text-emerald-800 rounded-md">TERISI</span>
                                            @else
                                                <span class="px-2 py-0.5 text-[10px] font-semibold uppercase bg-amber-100 text-amber-800 rounded-md">BELUM SELESAI</span>
                                            @endif
                                        </div>

                                        @if($pmAs)
                                            <div class="space-y-2.5 text-xs text-slate-700">
                                                <div class="flex justify-between py-1 border-b border-slate-100">
                                                    <span class="text-slate-400">Hasil Observasi Lapangan:</span>
                                                    <span class="font-extrabold uppercase {{ str_contains(strtolower($pmAs->hasil_survei ?? ''), 'tidak') ? 'text-rose-600' : 'text-emerald-700' }}">
                                                        {{ $pmAs->hasil_survei ?: ($pmStatus ?: 'LAYAK DIBANTU') }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between py-1 border-b border-slate-100 items-center">
                                                    <span class="text-slate-400">Skor Penilaian (1-5):</span>
                                                    <div class="flex items-center gap-1 font-bold text-slate-800">
                                                        @if($pmAs->skor_penilaian)
                                                            <span class="text-amber-500 font-extrabold inline-flex items-center gap-1">
                                                                <svg class="w-4 h-4 fill-amber-400 text-amber-500 shrink-0" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                                </svg>
                                                                <span>{{ $pmAs->skor_penilaian }}/5</span>
                                                            </span>
                                                        @else
                                                            <span class="text-slate-400 font-normal">-</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex justify-between py-1 border-b border-slate-100">
                                                    <span class="text-slate-400">Status Rumah & Tanah:</span>
                                                    <span class="font-medium text-slate-800">
                                                        Rumah: {{ $pmAs->status_rumah ?: '-' }} | Tanah: {{ $pmAs->status_tanah ?: '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between py-1 border-b border-slate-100">
                                                    <span class="text-slate-400">Kondisi Bangunan:</span>
                                                    <span class="font-medium text-slate-800">
                                                        {{ $pmAs->kondisi_tempat_tinggal ?: 'Permanen' }} (Atap: {{ $pmAs->kondisi_atap ?: '-' }}, Lantai: {{ $pmAs->kondisi_lantai ?: '-' }})
                                                    </span>
                                                </div>
                                                <div class="flex justify-between py-1 border-b border-slate-100">
                                                    <span class="text-slate-400">Utilitas (Air & Listrik):</span>
                                                    <span class="font-medium text-slate-800">
                                                        Air: {{ $pmAs->sumber_air_bersih ?: '-' }} | Listrik: {{ $pmAs->daya_listrik ?: '-' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between py-1 border-b border-slate-100">
                                                    <span class="text-slate-400">Status Usaha & BPJS:</span>
                                                    <span class="font-medium text-slate-800">
                                                        Usaha: {{ $pmAs->punya_usaha ?: '-' }} | BPJS: {{ $pmAs->bpjs_kesehatan ?: '-' }}{{ $pmAs->bpjs_kesehatan === 'Punya' && $pmAs->status_bpjs_kesehatan ? ' (' . $pmAs->status_bpjs_kesehatan . ')' : '' }}
                                                    </span>
                                                </div>
                                                <div class="flex justify-between py-1">
                                                    <span class="text-slate-400">Riwayat Penyakit / Kesehatan:</span>
                                                    <span class="font-medium text-slate-800 truncate max-w-[190px]" title="{{ $pmAs->riwayat_penyakit }}">
                                                        {{ $pmAs->riwayat_penyakit ?: 'Tidak ada riwayat' }}
                                                    </span>
                                                </div>

                                                @if($surveyToken)
                                                    <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                                                        <span class="text-[11px] text-slate-400">Surveyor: <strong class="text-slate-700">{{ $pmAs->nama_surveyor ?: ($pmSurvey?->surveyor ?: '-') }}</strong></span>
                                                        <a href="{{ $surveyPublicUrl }}" target="_blank" class="text-xs text-[#118243] hover:underline font-semibold inline-flex items-center gap-1">
                                                            <span>Buka Form Asesmen</span>
                                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="py-6 text-center text-slate-400 text-xs space-y-2">
                                                <p>Surveyor belum menginput data Form Asesmen Lapangan.</p>
                                                @if($surveyToken)
                                                    <a href="{{ $surveyPublicUrl }}" target="_blank" class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-medium rounded-xl text-xs transition-colors shadow-xs">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                        <span>Input Form Asesmen Lapangan</span>
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- CARD 3: KESIMPULAN KELAYAKAN BANTUAN (FORM / UPDATE) -->
                                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 space-y-5">
                                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-4">
                                        <h4 class="text-xs sm:text-sm font-semibold text-slate-800">
                                            Kesimpulan Kelayakan Bantuan (Perorangan: {{ $pm->mustahik?->person?->nama ?? $pm->nama ?? '-' }})
                                        </h4>
                                        @if($pmStatus === 'layak')
                                            <span class="px-2.5 py-0.5 text-xs font-semibold uppercase bg-emerald-100 text-emerald-800 rounded-lg">STATUS: LAYAK</span>
                                        @elseif($pmStatus === 'tidak layak')
                                            <span class="px-2.5 py-0.5 text-xs font-semibold uppercase bg-rose-100 text-rose-800 rounded-lg">STATUS: TIDAK LAYAK</span>
                                        @elseif($pmStatus === 'dipertimbangkan')
                                            <span class="px-2.5 py-0.5 text-xs font-semibold uppercase bg-amber-100 text-amber-800 rounded-lg">STATUS: DIPERTIMBANGKAN</span>
                                        @else
                                            <span class="px-2.5 py-0.5 text-xs font-semibold uppercase bg-slate-100 text-slate-600 rounded-lg">STATUS: BELUM SURVEY</span>
                                        @endif
                                    </div>

                                     <form method="POST" action="{{ route('pengajuan.mustahik.survey.store', ['pengajuanId' => $pengajuan->id, 'mustahikId' => $pm->id]) }}" enctype="multipart/form-data" class="space-y-4">
                                        @csrf
                                        
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Peninjauan / Survey <span class="text-red-500">*</span></label>
                                                <input type="date" name="tanggal_survey" value="{{ $pmSurvey?->tanggal_survey ? $pmSurvey->tanggal_survey->format('Y-m-d') : '' }}" required class="w-full px-3 py-2 border border-slate-300 bg-white rounded-xl text-xs sm:text-sm shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-slate-600 mb-1">Nama Surveyor / Verifikator <span class="text-red-500">*</span></label>
                                                <input type="text" name="petugas_survey" value="{{ $pmSurvey?->surveyor }}" placeholder="Nama Lengkap Surveyor" required class="w-full px-3 py-2 border border-slate-300 bg-white rounded-xl text-xs sm:text-sm shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-slate-600 mb-1">No. HP / WhatsApp Surveyor <span class="text-red-500">*</span></label>
                                                <input type="text" name="nohp_surveyor" value="{{ $pmSurvey?->nohp_surveyor }}" placeholder="08xxxxxxxxxx" required class="w-full px-3 py-2 border border-slate-300 bg-white rounded-xl text-xs sm:text-sm shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-slate-600 mb-1">Status Layak Dibantu</label>
                                                <select name="status_kelayakan" class="w-full px-3 py-2 border border-slate-300 bg-white rounded-xl text-xs sm:text-sm shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                                                    <option value="layak" {{ ($pmStatus === 'layak' || !$pmStatus) ? 'selected' : '' }}>Layak Dibantu</option>
                                                    <option value="dipertimbangkan" {{ $pmStatus === 'dipertimbangkan' ? 'selected' : '' }}>Dipertimbangkan</option>
                                                    <option value="tidak layak" {{ $pmStatus === 'tidak layak' ? 'selected' : '' }}>Tidak Layak Dibantu</option>
                                                </select>
                                            </div>

                                            <div class="sm:col-span-2">
                                                <x-file-dropzone 
                                                    name="foto_survey" 
                                                    accept="image/*" 
                                                    capture="environment"
                                                    icon="camera"
                                                    :compact="true"
                                                    label="Unggah Tambahan Foto Survey (JPG / PNG)"
                                                    help-text="Tarik & lepas foto survey atau klik untuk ambil kamera / buka galeri"
                                                />
                                            </div>

                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-medium text-slate-600 mb-1">Keterangan / Catatan Kelayakan</label>
                                                <textarea name="catatan" rows="3" placeholder="Tuliskan catatan kelayakan berdasarkan hasil peninjauan lapangan..." class="w-full px-3 py-2 border border-slate-300 bg-white rounded-xl text-xs sm:text-sm shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">{{ $pmSurvey?->catatan ?: '' }}</textarea>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between gap-3 pt-2">
                                            <div class="text-xs text-slate-400">
                                                @if($surveyToken)
                                                    Link Online: <a href="{{ $surveyPublicUrl }}" target="_blank" class="text-emerald-700 font-mono hover:underline text-[11px]">{{ route('public.survey', ['token' => substr($surveyToken, 0, 10) . '...']) }}</a>
                                                @endif
                                            </div>
                                            <button type="submit" class="px-4 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl text-xs shadow-2xs inline-flex items-center gap-1.5 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                <span>Simpan Kesimpulan Survey</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- CARD 4: LAMPIRAN / DOKUMENTASI SURVEY -->
                                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 space-y-4">
                                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                            <h4 class="text-xs sm:text-sm font-semibold text-slate-800">
                                                Lampiran / Dokumentasi Survey (a.n {{ $pm->mustahik?->person?->nama ?? $pm->nama ?? '-' }})
                                            </h4>
                                        </div>
                                        <span class="text-xs text-slate-400">{{ count($surveyPhotos) }} Foto</span>
                                    </div>

                                    @if(count($surveyPhotos) > 0)
                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                            @foreach($surveyPhotos as $photo)
                                                <a href="{{ $photo['url'] }}" target="_blank" class="group relative rounded-xl overflow-hidden border border-slate-200 aspect-video bg-slate-100 block">
                                                    <img src="{{ $photo['url'] }}" alt="{{ $photo['label'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent flex items-end p-2">
                                                        <span class="text-[11px] font-medium text-white truncate">{{ $photo['label'] }}</span>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="py-8 text-center text-slate-400 text-xs italic">
                                            Belum ada lampiran survey untuk mustahik ini.
                                        </div>
                                    @endif
                                </div>

                            </div>
                        @endforeach
                    </div>

                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 5. TAB 3: PERSETUJUAN PIMPINAN & DISPOSISI               -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'persetujuan'" class="space-y-6">
        
        <!-- HEADER KARTU & QUICK PRINT ACTIONS -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-full border-2 border-sky-500 text-sky-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>
                <h2 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight">
                    Disposisi & Persetujuan Pimpinan (Per Mustahik)
                </h2>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <!-- Item Selection Button -->
                <button type="button" 
                        onclick="openItemSelectionModal('{{ route('api.pengajuan.available-items', $pengajuan->id) }}')"
                        class="px-4 py-2 bg-white border border-[#118243] hover:bg-[#118243] text-[#118243] hover:text-white font-medium rounded-xl text-xs shadow-xs inline-flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75v.75A.75.75 0 0 1 3 20.25h18a.75.75 0 0 1 .75-.75v-.75m-18 0a.75.75 0 0 1 .75-.75H3m0 0v-3.375c0-.621.504-1.125 1.125-1.125h13.5c.621 0 1.125.504 1.125 1.125V18.75m0 0a.75.75 0 0 0 .75-.75V15m-4.5 0h4.5m-4.5 0a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 .75.75m0 0h4.5m0 0a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-.75.75h-4.5a.75.75 0 0 1-.75-.75v-4.5Z" />
                    </svg>
                    Pilih Barang
                </button>

                <a href="{{ route('pengajuan.cetak-form-penyaluran', $pengajuan->id) }}" target="_blank" 
                   class="px-4 py-2 bg-[#0c5931] hover:bg-[#094626] text-white font-medium rounded-xl text-xs shadow-xs inline-flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    Form Pengajuan - Penyaluran ZIS
                </a>

                <a href="{{ route('pengajuan.cetak-disposisi', $pengajuan->id) }}" target="_blank" 
                   class="px-4 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl text-xs shadow-xs inline-flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                    </svg>
                    Cetak Lembar Disposisi
                </a>
            </div>
        </div>

        <!-- TABEL DISPOSISI PER MUSTAHIK -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 font-bold text-slate-700 text-[11px] uppercase tracking-wider border-b border-slate-200">
                            <th class="px-6 py-4 w-[28%]">MUSTAHIK / PEMOHON</th>
                            <th class="px-6 py-4 w-[26%]">RINGKASAN SURVEY</th>
                            <th class="px-6 py-4 w-[28%]">DISPOSISI PIMPINAN</th>
                            <th class="px-6 py-4 w-[18%] text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pengajuan->pengajuanMustahiks as $pm)
                            @php
                                $isPmPerorangan = ($pm->mustahik?->person_id || !$pm->mustahik?->lembaga_id);
                                $pmNama = $isPmPerorangan 
                                    ? ($pm->mustahik?->person?->nama ?? $pm->nama ?? '-')
                                    : ($pm->mustahik?->lembaga?->nama ?? $pm->nama ?? '-');
                                
                                $pmNik = $pm->mustahik?->person?->nik ?? $pm->nik ?? '-';
                                if (str_starts_with($pmNik, 'TMP')) {
                                    $pmNik = '-';
                                }

                                $pmJkRaw = $pm->mustahik?->person?->jenis_kelamin;
                                $pmJk = match(strtoupper(substr($pmJkRaw ?? '', 0, 1))) {
                                    'L' => 'Laki-laki',
                                    'P' => 'Perempuan',
                                    default => '-'
                                };

                                $programText = $pm->program?->nama ?? '-';
                                if ($pm->subprogram?->nama) {
                                    $programText .= ' (' . $pm->subprogram->nama . ')';
                                }

                                // Survey data
                                $survey = $pm->survey;
                                $hasilAkhirRaw = strtolower($survey?->hasil_akhir ?? '');
                                $isLayak = str_contains($hasilAkhirRaw, 'layak') && !str_contains($hasilAkhirRaw, 'tidak');
                                $isTidakLayak = str_contains($hasilAkhirRaw, 'tidak');
                                $isDipertimbangkan = str_contains($hasilAkhirRaw, 'pertimbang');

                                $surveyNotes = $survey?->catatan 
                                    ?: ($isLayak ? 'Layak dibantu berdasarkan hasil survey.' 
                                        : ($isTidakLayak ? 'Tidak layak dibantu berdasarkan hasil survey.' 
                                            : ($isDipertimbangkan ? 'Perlu pertimbangan lebih lanjut.' : 'Belum dilakukan survey.')));

                                // Disposisi data
                                $disposisi = $pm->disposisi;
                                $hasDisposisi = $disposisi !== null || (($pm->nominal > 0 || $pm->bentuk_bantuan === 'Barang') && $pm->sumber_dana_id !== null);
                                $instruksi = $disposisi?->instruksi ?? ($hasDisposisi ? 'Proses Langsung' : null);
                                $instruksiLower = strtolower($instruksi ?? '');

                                $catatanDisposisi = $disposisi?->catatan ?: 'Disposisi pimpinan untuk realisasi bantuan.';
                                $pimpinanNama = $disposisi?->pimpinan?->name ?? 'Pimpinan';
                            @endphp
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <!-- KOLOM 1: MUSTAHIK / PEMOHON -->
                                <td class="px-6 py-5 align-top">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wide uppercase bg-blue-100 text-blue-700">
                                                {{ $isPmPerorangan ? 'PERORANGAN' : 'LEMBAGA' }}
                                            </span>
                                            @if($pm->asnaf)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                                    {{ $pm->asnaf->nama }}
                                                </span>
                                            @endif
                                        </div>

                                        <h4 class="text-sm sm:text-base font-bold text-slate-900 pt-0.5">
                                            {{ $pmNama }}
                                        </h4>

                                        <div class="text-xs text-slate-500 font-mono">
                                            NIK: {{ $pmNik }}
                                        </div>

                                        @if($isPmPerorangan && $pmJk !== '-')
                                            <div class="text-xs text-slate-500">
                                                JK: {{ $pmJk }}
                                            </div>
                                        @endif

                                        <div class="text-xs font-semibold text-emerald-700 pt-0.5">
                                            Prog: {{ $programText }}
                                        </div>
                                    </div>
                                </td>

                                <!-- KOLOM 2: RINGKASAN SURVEY -->
                                <td class="px-6 py-5 align-top">
                                    <div class="space-y-2">
                                        @if($isLayak)
                                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                                LAYAK
                                            </span>
                                        @elseif($isTidakLayak)
                                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-100 text-rose-800">
                                                TIDAK LAYAK
                                            </span>
                                        @elseif($isDipertimbangkan)
                                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-800">
                                                DIPERTIMBANGKAN
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium uppercase tracking-wider bg-slate-100 text-slate-500">
                                                BELUM SURVEY
                                            </span>
                                        @endif

                                        <p class="text-xs italic text-slate-600 leading-relaxed">
                                            &ldquo;{{ $surveyNotes }}&rdquo;
                                        </p>
                                    </div>
                                </td>

                                <!-- KOLOM 3: DISPOSISI PIMPINAN -->
                                <td class="px-6 py-5 align-top">
                                    @if($hasDisposisi)
                                        <div class="space-y-1.5">
                                            <div>
                                                @if(str_contains($instruksiLower, 'proses') || str_contains($instruksiLower, 'langsung'))
                                                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-[#118243] text-white">
                                                        {{ $instruksi }}
                                                    </span>
                                                @elseif(str_contains($instruksiLower, 'tolak'))
                                                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-rose-600 text-white">
                                                        {{ $instruksi }}
                                                    </span>
                                                @elseif(str_contains($instruksiLower, 'survey'))
                                                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-blue-600 text-white">
                                                        {{ $instruksi }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold bg-amber-500 text-white">
                                                        {{ $instruksi }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($pm->bentuk_bantuan === 'Barang')
                                                <div class="space-y-0.5 pt-0.5">
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                        <svg class="w-3 h-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                                        </svg>
                                                        BARANG / NATURA
                                                    </span>
                                                    <div class="text-xs font-bold text-slate-900">
                                                        {{ $pm->jumlah_barang }} {{ $pm->satuan_barang }}{{ $pm->nama_barang ? ' (' . $pm->nama_barang . ')' : '' }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-xs font-bold text-emerald-700 pt-0.5">
                                                    Nominal: Rp {{ number_format($pm->nominal ?? 0, 0, ',', '.') }}
                                                </div>
                                            @endif

                                            <div class="text-xs text-slate-700">
                                                Sumber Dana: <span class="font-bold text-slate-900">{{ $pm->sumberDana?->nama ?? '-' }}</span>
                                            </div>

                                            @if($catatanDisposisi)
                                                <div class="text-xs italic text-slate-500 leading-relaxed">
                                                    Catatan: &ldquo;{{ $catatanDisposisi }}&rdquo;
                                                </div>
                                            @endif

                                            <div class="text-[11px] text-slate-400">
                                                Oleh: {{ $pimpinanNama }}
                                            </div>
                                        </div>
                                    @else
                                        <div class="space-y-2">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-500">
                                                Belum Didisposisi
                                            </span>
                                            <p class="text-xs italic text-slate-400 leading-relaxed">
                                                Belum ada penetapan nominal dan sumber dana dari pimpinan.
                                            </p>
                                        </div>
                                    @endif
                                </td>

                                <!-- KOLOM 4: AKSI -->
                                <td class="px-6 py-5 align-top text-center">
                                    <div class="flex items-center justify-center gap-2 flex-wrap">
                                        @if($hasDisposisi)
                                            <button type="button" 
                                                    @click="openDisposisi({{ $pm->id }})" 
                                                    class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg text-xs shadow-2xs inline-flex items-center gap-1.5 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                                Edit Disposisi
                                            </button>

                                            <form method="POST" action="{{ route('pengajuan.mustahik.disposisi.destroy', ['pengajuanId' => $pengajuan->id, 'mustahikId' => $pm->id]) }}" data-confirm="Apakah Anda yakin ingin membatalkan disposisi untuk mustahik {{ addslashes($pmNama) }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="px-3 py-1.5 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 font-medium rounded-lg text-xs shadow-2xs inline-flex items-center gap-1.5 transition-all">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                    Batal
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" 
                                                    @click="openDisposisi({{ $pm->id }})" 
                                                    class="px-3.5 py-1.5 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-lg text-xs shadow-2xs inline-flex items-center gap-1.5 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                                Tetapkan Disposisi
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-400 italic">
                                    Belum ada mustahik pada pengajuan bantuan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- FOOTER SUMMARY & FINALISASI -->
            <div class="p-5 bg-slate-50/80 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="space-y-0.5 text-center sm:text-left">
                    <span class="text-xs text-slate-500 block">Total Bantuan yang Disetujui Pimpinan:</span>
                    <span class="font-bold text-lg sm:text-xl text-emerald-700">Rp {{ number_format($totalNominalDisetujui, 0, ',', '.') }}</span>
                </div>
                
                <div class="flex items-center gap-3">
                    @if($isDisetujui)
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-100 text-emerald-800 rounded-xl text-xs font-bold">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Persetujuan Pimpinan Telah Difinalisasi
                        </span>
                    @else
                        <form method="POST" action="{{ route($pengajuan->jenis_permohonan === 'Lembaga' ? 'pengajuan.upz.persetujuan.store' : 'pengajuan.umum.persetujuan.store', $pengajuan->id) }}">
                            @csrf
                            <input type="hidden" name="status" value="disetujui">
                            <button type="submit" class="px-5 py-2.5 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl text-xs shadow-2xs transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                Finalisasi Persetujuan Pimpinan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 6. TAB 4: PENYALURAN BANTUAN                             -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'penyaluran'" class="space-y-6">
        <!-- Top Bar: Judul & Aksi Cepat -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-emerald-50 text-[#118243]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v1.5a.75.75 0 0 0 .75.75H3m0 0h1.5A.75.75 0 0 0 5.25 7.5v-1.5m0 0A.75.75 0 0 0 4.5 5.25H3m18 0v.75A.75.75 0 0 1 20.25 6H19.5m0 0v1.5a.75.75 0 0 0 .75.75H20.25m0 0h1.5a.75.75 0 0 0 .75-.75v-1.5m0 0a.75.75 0 0 0-.75-.75H19.5M3 18.75a60.07 60.07 0 0 1 15.797-2.101c.727-.198 1.453.342 1.453 1.096v.75m-18 0H2.25" />
                        </svg>
                    </span>
                    <span>Realisasi Penyaluran Bantuan (Per Penerima Manfaat)</span>
                </h3>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('pengajuan.cetak-form-penyaluran', $pengajuan->id) }}" target="_blank" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 font-medium rounded-xl text-xs border border-slate-200/90 shadow-2xs transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span>Form Penyaluran</span>
                </a>

                @if($hasAnySalur)
                    <a href="{{ route('pengajuan.bukti-pengeluaran.batch', $pengajuan->id) }}" target="_blank" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 font-medium rounded-xl text-xs border border-slate-200/90 shadow-2xs transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.146-1.185-3.159a2.25 2.25 0 0 1 .425-2.585L7.42 6.625a2.25 2.25 0 0 1 2.585-.425c1.013.513 2.083.945 3.159 1.185m-6.444 6.444c1.076.24 2.146.672 3.159 1.185a2.25 2.25 0 0 0 2.585-.425l1.46-1.46a2.25 2.25 0 0 0 .425-2.585c-.513-1.013-.945-2.083-1.185-3.159m-6.444 6.444 6.444-6.444" />
                        </svg>
                        <span>Cetak Semua BKK</span>
                    </a>
                @endif

                @if($belumSalurMustahikCount > 0)
                    <button type="button" @click="openSalurSemua()" class="px-4 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl text-xs sm:text-sm shadow-xs transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>Salurkan Semua Sekaligus</span>
                    </button>
                @else
                    <span class="px-3.5 py-2 bg-emerald-50 text-emerald-700 font-semibold rounded-xl text-xs border border-emerald-200 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Seluruh Bantuan Tersalurkan</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- 3 Kartu Ringkasan (Stat Cards) Sesuai Referensi Gambar -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
            <!-- Card 1: TOTAL DISETUJUI -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase block mb-1">TOTAL DISETUJUI</span>
                    <h4 class="text-xl sm:text-2xl font-bold text-slate-800">Rp {{ number_format($totalNominalDisetujui, 0, ',', '.') }}</h4>
                    <span class="text-xs text-slate-400 mt-1 block">{{ $pengajuan->pengajuanMustahiks->count() }} Mustahik Disetujui</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                </div>
            </div>

            <!-- Card 2: TOTAL TERSALURKAN (Border Hijau Sesuai Gambar) -->
            <div class="bg-white rounded-2xl border-2 border-emerald-400/80 p-5 sm:p-6 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold tracking-wider text-emerald-600 uppercase block mb-1">TOTAL TERSALURKAN</span>
                    <h4 class="text-xl sm:text-2xl font-bold text-slate-800">Rp {{ number_format($totalNominalDisalurkan, 0, ',', '.') }}</h4>
                    <span class="text-xs text-emerald-600 font-medium mt-1 block">{{ $disalurkanMustahikCount }} Penerima Selesai</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#118243] text-white flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>
            </div>

            <!-- Card 3: SISA BELUM DISALURKAN -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold tracking-wider text-slate-400 uppercase block mb-1">SISA BELUM DISALURKAN</span>
                    <h4 class="text-xl sm:text-2xl font-bold text-amber-600">Rp {{ number_format($sisaBelumDisalurkan, 0, ',', '.') }}</h4>
                    <span class="text-xs text-slate-400 mt-1 block">{{ $belumSalurMustahikCount > 0 ? $belumSalurMustahikCount . ' Belum Disalurkan' : 'Selesai' }}</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Tabel Per Mustahik Sesuai Format Referensi Gambar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 font-semibold text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200/80">
                            <th class="px-6 py-4">MUSTAHIK / PEMOHON</th>
                            <th class="px-6 py-4">DISPOSISI PIMPINAN</th>
                            <th class="px-6 py-4">REALISASI PENYALURAN</th>
                            <th class="px-6 py-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pengajuan->pengajuanMustahiks as $index => $pm)
                            @php
                                $m = $pm->mustahik;
                                $isLembagaMustahik = ($m?->jenis_mustahik === 'lembaga' || $m?->lembaga_id);
                                $namaPenerima = $isLembagaMustahik 
                                    ? ($m?->lembaga?->nama ?? $pengajuan->lembaga_nama_snapshot ?? $pm->nama ?? 'Lembaga') 
                                    : ($m?->person?->nama ?? $pengajuan->mustahik_nama_snapshot ?? $pm->nama ?? 'Penerima Manfaat');
                                $nikPenerima = $m?->person?->nik && !str_starts_with($m->person->nik, 'TMP') ? $m->person->nik : null;
                                $programName = $pm->program?->nama ?? '-';
                                if ($pm->subprogram) {
                                    $programName .= ' (' . $pm->subprogram->nama . ')';
                                }
                                $isDisalurkan = !is_null($pm->tanggal_penyaluran);
                                $instruksiDisposisi = $pm->disposisi?->instruksi ?? 'Proses Langsung';
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- Kolom 1: MUSTAHIK / PEMOHON -->
                                <td class="px-6 py-4">
                                    <div class="flex items-start gap-3.5">
                                        <div class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 border border-emerald-100">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-bold text-slate-800 text-sm">{{ $namaPenerima }}</span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                                    {{ $isLembagaMustahik ? 'Lembaga' : 'Perorangan' }}
                                                </span>
                                            </div>
                                            @if($nikPenerima)
                                                <div class="text-xs text-slate-400 font-mono">
                                                    NIK: {{ $nikPenerima }}
                                                </div>
                                            @endif
                                            <div class="inline-flex items-center gap-1.5 text-xs text-slate-600 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#118243]"></span>
                                                <span>{{ $programName }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kolom 2: DISPOSISI PIMPINAN -->
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div>
                                            <span class="px-2.5 py-0.5 rounded text-[11px] font-semibold bg-[#118243] text-white inline-block shadow-2xs">
                                                {{ $instruksiDisposisi }}
                                            </span>
                                        </div>
                                        @if($pm->bentuk_bantuan === 'Barang')
                                            <div class="space-y-0.5">
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                    BARANG
                                                </span>
                                                <div class="font-bold text-slate-900 text-sm">
                                                    {{ $pm->jumlah_barang }} {{ $pm->satuan_barang }}{{ $pm->nama_barang ? ' (' . $pm->nama_barang . ')' : '' }}
                                                </div>
                                                @if($pm->pengadaanBarang)
                                                    <div class="text-[11px] text-amber-900 font-medium">
                                                        BA: <span class="font-semibold">{{ $pm->pengadaanBarang->nomor_ba }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="font-bold text-slate-900 text-sm">
                                                Rp {{ number_format($pm->nominal ?? 0, 0, ',', '.') }}
                                            </div>
                                        @endif
                                        <div class="text-xs text-slate-500">
                                            Sumber: <span class="font-medium text-slate-700">{{ $pm->sumberDana?->nama ?? 'Belum Dipilih' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kolom 3: REALISASI PENYALURAN -->
                                <td class="px-6 py-4">
                                    @if($isDisalurkan)
                                        <div class="space-y-1.5">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Tersalurkan
                                            </span>
                                            <div class="text-xs text-slate-600">
                                                {{ $pm->tanggal_penyaluran->locale('id')->isoFormat('D MMMM Y') }}
                                                <span class="text-slate-400">•</span>
                                                <span class="font-medium text-slate-700">{{ $pm->metode_penyaluran ?? 'Tunai' }}</span>
                                            </div>
                                            @if($pm->bentuk_bantuan === 'Barang' && $pm->pengadaanBarang)
                                                <div class="text-[11px] text-emerald-800 font-medium bg-emerald-50/80 px-2 py-1 rounded-lg border border-emerald-200/60 inline-flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                                    </svg>
                                                    <span>BA: <strong>{{ $pm->pengadaanBarang->nomor_ba }}</strong> ({{ $pm->pengadaanBarang->barang?->nama }})</span>
                                                </div>
                                            @endif
                                            @if($pm->buktiPenyaluran)
                                                <div>
                                                    <a href="{{ asset('storage/' . $pm->buktiPenyaluran->file_path) }}" target="_blank" class="text-xs text-[#118243] hover:text-[#0d6835] font-medium underline inline-flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                                        </svg>
                                                        Lihat Bukti Penyaluran
                                                    </a>
                                                </div>
                                            @endif
                                            @if($pm->catatan_penyaluran)
                                                <div class="text-[11px] text-slate-400 italic line-clamp-1">
                                                    "{{ $pm->catatan_penyaluran }}"
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Menunggu Penyaluran
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Kolom 4: AKSI -->
                                <td class="px-6 py-4 text-right">
                                    @if(!$isDisalurkan)
                                        <button type="button" @click="openSalurMustahik({{ $pm->id }})" class="px-4 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl text-xs shadow-xs transition-all inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v1.5a.75.75 0 0 0 .75.75H3m0 0h1.5A.75.75 0 0 0 5.25 7.5v-1.5m0 0A.75.75 0 0 0 4.5 5.25H3m18 0v.75A.75.75 0 0 1 20.25 6H19.5m0 0v1.5a.75.75 0 0 0 .75.75H20.25m0 0h1.5a.75.75 0 0 0 .75-.75v-1.5m0 0a.75.75 0 0 0-.75-.75H19.5M3 18.75a60.07 60.07 0 0 1 15.797-2.101c.727-.198 1.453.342 1.453 1.096v.75m-18 0H2.25" />
                                            </svg>
                                            Salurkan Bantuan
                                        </button>
                                    @else
                                        <div class="flex items-center justify-end gap-2 flex-wrap">
                                            <a href="{{ route('pengajuan.bukti-pengeluaran.single', $pm->id) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl text-xs transition-all inline-flex items-center gap-1 border border-slate-200/80 shadow-2xs">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.146-1.185-3.159a2.25 2.25 0 0 1 .425-2.585L7.42 6.625a2.25 2.25 0 0 1 2.585-.425c1.013.513 2.083.945 3.159 1.185m-6.444 6.444c1.076.24 2.146.672 3.159 1.185a2.25 2.25 0 0 0 2.585-.425l1.46-1.46a2.25 2.25 0 0 0 .425-2.585c-.513-1.013-.945-2.083-1.185-3.159m-6.444 6.444 6.444-6.444" />
                                                </svg>
                                                <span>Cetak BKK</span>
                                            </a>
                                            <button type="button" @click="openBatalSalur({{ $pm->id }})" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-medium rounded-xl text-xs transition-all inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                </svg>
                                                <span>Batal</span>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-400 text-xs">
                                    Belum ada data mustahik untuk pengajuan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 7. TAB 5: LAPORAN PERTANGGUNGJAWABAN (LPJ)               -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'lpj'" class="space-y-6">
        
        <!-- A. HEADER & ACTION BAR (CETAK REKAP LPJ, EXCEL, BAST BATCH) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs">
                        5
                    </span>
                    <span class="text-xs font-semibold tracking-wider text-slate-400 uppercase">
                        Tahap Akhir Pelaporan & Pertanggungjawaban
                    </span>
                </div>
                <h3 class="text-lg sm:text-xl font-bold text-slate-800">Laporan Penyaluran & BAST LPJ Per Penerima Manfaat</h3>
                <p class="text-xs text-slate-500">
                    Dokumentasi Berita Acara Serah Terima (BAST), monitoring laporan per penerima manfaat, dan rekapitulasi pertanggungjawaban dana ZIS.
                </p>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0 self-start lg:self-auto">
                <a href="{{ route('pengajuan.cetak-lpj', $pengajuan->id) }}" target="_blank" 
                   class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-xl text-xs shadow-2xs inline-flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                    </svg>
                    <span>Cetak Rekap LPJ ZIS</span>
                </a>

                <a href="{{ route('pengajuan.export-lpj-excel', $pengajuan->id) }}" 
                   class="px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-medium rounded-xl text-xs shadow-2xs inline-flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span>Export Excel (.xlsx)</span>
                </a>

                @if($pengajuan->pengajuanMustahiks->isNotEmpty())
                    <a href="{{ route('pengajuan.lpj.mustahik-batch', $pengajuan->id) }}" target="_blank" 
                       class="px-3.5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl text-xs shadow-2xs inline-flex items-center gap-1.5 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                        <span>Cetak BAST Semua Penerima Manfaat</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- B. METRIC SUMMARY CARDS -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
                <span class="text-xs text-slate-500 font-medium block">Total Penerima Manfaat</span>
                <span class="text-lg sm:text-xl font-bold text-slate-800 mt-1 block">
                    {{ $totalMustahikCount }} <span class="text-xs font-normal text-slate-400">Penerima</span>
                </span>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
                <span class="text-xs text-slate-500 font-medium block">Total Dana Disalurkan</span>
                <span class="text-lg sm:text-xl font-bold text-emerald-700 mt-1 block">
                    Rp {{ number_format($totalNominalDisalurkan, 0, ',', '.') }}
                </span>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
                <span class="text-xs text-slate-500 font-medium block">Realisasi LPJ</span>
                <span class="text-lg sm:text-xl font-bold text-blue-700 mt-1 block">
                    Rp {{ number_format($pengajuan->lpjBa?->nominal_realisasi ?? $totalNominalDisalurkan, 0, ',', '.') }}
                </span>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4">
                <span class="text-xs text-slate-500 font-medium block">Kelengkapan Berkas LPJ</span>
                <div class="flex items-baseline gap-1.5 mt-1">
                    <span class="text-lg sm:text-xl font-bold {{ $totalLpjMustahik === $totalMustahikCount && $totalMustahikCount > 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $totalLpjMustahik }} / {{ $totalMustahikCount }}
                    </span>
                    <span class="text-xs text-slate-400">Penerima</span>
                </div>
            </div>
        </div>

        <!-- C. TABEL LAPORAN REALISASI & LPJ PER MUSTAHIK -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h4 class="text-base font-bold text-slate-800">Daftar Laporan Realisasi & BAST Per Penerima Manfaat</h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Rincian dana yang disalurkan, berkas bukti LPJ, dan lembar Berita Acara Serah Terima per penerima manfaat.
                    </p>
                </div>
                <span class="px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-600 rounded-xl self-start sm:self-auto">
                    {{ $totalMustahikCount }} Data Penerima
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-50/80 font-bold text-slate-700 text-[11px] uppercase tracking-wider border-b border-slate-200">
                            <th class="px-5 py-3.5 w-10 text-center">NO</th>
                            <th class="px-5 py-3.5 w-[24%]">MUSTAHIK / PENERIMA</th>
                            <th class="px-5 py-3.5 w-[20%]">PROGRAM & ASNAF</th>
                            <th class="px-5 py-3.5 w-[18%]">SUMBER DANA & REALISASI</th>
                            <th class="px-5 py-3.5 w-[22%]">STATUS & BERKAS LPJ</th>
                            <th class="px-5 py-3.5 w-[16%] text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pengajuan->pengajuanMustahiks as $idx => $pm)
                            @php
                                $isPmPerorangan = ($pm->mustahik?->person_id || !$pm->mustahik?->lembaga_id);
                                $pmNama = $isPmPerorangan 
                                    ? ($pm->mustahik?->person?->nama ?? $pm->nama ?? '-')
                                    : ($pm->mustahik?->lembaga?->nama ?? $pm->nama ?? '-');

                                $pmNik = $pm->mustahik?->person?->nik ?? $pm->nik ?? '-';
                                if (str_starts_with($pmNik, 'TMP')) {
                                    $pmNik = '-';
                                }

                                $pmAlamat = $isPmPerorangan
                                    ? ($pm->mustahik?->person?->alamat ?? $pengajuan->mustahik_alamat_snapshot ?? '-')
                                    : ($pm->mustahik?->lembaga?->alamat ?? $pengajuan->lembaga_alamat_snapshot ?? '-');

                                $pmTelepon = $isPmPerorangan
                                    ? ($pm->mustahik?->person?->telepon ?? '-')
                                    : ($pm->mustahik?->lembaga?->telepon ?? '-');

                                $lpjFiles = $pm->lampirans->where('category', 'lpj_mustahik');
                                $hasIndividualLpj = $lpjFiles->isNotEmpty();
                                $hasBuktiSalur = $pm->buktiPenyaluran !== null;
                                $isDisalurkan = !is_null($pm->tanggal_penyaluran);
                            @endphp
                            <tr class="hover:bg-slate-50/40 transition-colors">
                                <!-- NO -->
                                <td class="px-5 py-4 text-center font-medium text-slate-500 align-top">
                                    {{ $idx + 1 }}
                                </td>

                                <!-- MUSTAHIK / PENERIMA -->
                                <td class="px-5 py-4 align-top">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider {{ $isPmPerorangan ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                                {{ $isPmPerorangan ? 'Perorangan' : 'Lembaga' }}
                                            </span>
                                            <span class="font-bold text-slate-800 text-xs sm:text-sm">
                                                {{ $pmNama }}
                                            </span>
                                        </div>

                                        <div class="text-[11px] font-mono text-slate-500">
                                            NIK: {{ $pmNik }}
                                        </div>

                                        @if($pmAlamat && $pmAlamat !== '-')
                                            <div class="text-[11px] text-slate-500 truncate max-w-xs" title="{{ $pmAlamat }}">
                                                {{ $pmAlamat }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- PROGRAM & ASNAF -->
                                <td class="px-5 py-4 align-top">
                                    <div class="space-y-1">
                                        <div class="font-semibold text-slate-800 text-xs sm:text-sm">
                                            {{ $pm->program?->nama ?? '-' }}
                                        </div>
                                        @if($pm->subprogram?->nama)
                                            <div class="text-xs text-slate-500">
                                                {{ $pm->subprogram->nama }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700">
                                                {{ $pm->asnaf?->nama ?? 'Fakir / Miskin' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- SUMBER DANA & REALISASI -->
                                <td class="px-5 py-4 align-top">
                                    <div class="space-y-1">
                                        <div class="font-bold text-emerald-700 text-xs sm:text-sm">
                                            Rp {{ number_format($pm->nominal ?? 0, 0, ',', '.') }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $pm->sumberDana?->nama ?? 'Kas BAZNAS' }}
                                        </div>
                                        <div class="text-[11px] text-slate-400">
                                            @if($isDisalurkan)
                                                <span>Salur: {{ $pm->tanggal_penyaluran->format('d/m/Y') }}</span>
                                                <span class="text-slate-300">•</span>
                                                <span>{{ $pm->metode_penyaluran ?? 'Tunai' }}</span>
                                            @else
                                                <span class="italic text-amber-600">Belum disalurkan</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- STATUS & BERKAS LPJ -->
                                <td class="px-5 py-4 align-top">
                                    <div class="space-y-2">
                                        @if($hasIndividualLpj)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                                LPJ & BAST Lengkap
                                            </span>
                                        @elseif($hasBuktiSalur)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-blue-100 text-blue-800">
                                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                                Kwitansi / Bukti Salur
                                            </span>
                                        @elseif($isDisalurkan)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-amber-100 text-amber-800">
                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                                </svg>
                                                Tersalurkan (Menunggu Berkas)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-500">
                                                Menunggu Penyaluran
                                            </span>
                                        @endif

                                        <!-- DAFTAR FILE TERLAMPIR PER MUSTAHIK -->
                                        <div class="space-y-1 pt-1">
                                            @if($hasBuktiSalur)
                                                <div class="flex items-center gap-1.5 text-[11px]">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                                                    </svg>
                                                    <a href="{{ asset('storage/' . $pm->buktiPenyaluran->file_path) }}" target="_blank" class="text-slate-700 hover:text-[#118243] hover:underline truncate max-w-[170px]" title="Bukti Transfer / Kwitansi">
                                                        Bukti Transfer / Kwitansi
                                                    </a>
                                                </div>
                                            @endif

                                            @foreach($lpjFiles as $lamp)
                                                <div class="flex items-center justify-between gap-1 text-[11px] bg-slate-50 px-2 py-1 rounded border border-slate-100">
                                                    <a href="{{ asset('storage/' . $lamp->file_path) }}" target="_blank" class="text-blue-700 hover:underline truncate max-w-[130px]" title="{{ $lamp->name }}">
                                                        {{ $lamp->name ?: 'Berkas BAST LPJ' }}
                                                    </a>
                                                    <form method="POST" action="{{ route('pengajuan.mustahik.destroy-lpj', ['pengajuan' => $pengajuan->id, 'mustahik' => $pm->id, 'lampiran' => $lamp->id]) }}" data-confirm="Hapus berkas LPJ ini?" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-rose-500 hover:text-rose-700 shrink-0 p-0.5" title="Hapus berkas">
                                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>

                                <!-- AKSI PER MUSTAHIK -->
                                <td class="px-5 py-4 align-top text-center">
                                    <div class="flex flex-col gap-1.5 items-center justify-center">
                                        <a href="{{ route('pengajuan.mustahik.cetak-lpj', ['pengajuan' => $pengajuan->id, 'mustahik' => $pm->id]) }}" target="_blank" 
                                           class="w-full px-2.5 py-1.5 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-lg text-xs shadow-2xs inline-flex items-center justify-center gap-1 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                            </svg>
                                            <span>Cetak BAST</span>
                                        </a>

                                        <button type="button" 
                                                @click="openUploadLpj({{ $pm->id }}, '{{ addslashes($pmNama) }}')" 
                                                class="w-full px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium rounded-lg text-xs border border-blue-200 inline-flex items-center justify-center gap-1 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                            </svg>
                                            <span>Unggah LPJ</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400 italic">
                                    Belum ada data penerima manfaat (mustahik) pada pengajuan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- D. DOKUMENTASI INDUK LPJ & BERITA ACARA GABUNGAN -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-7 space-y-6">
            <div>
                <span class="text-xs font-semibold tracking-wider text-slate-400 uppercase block mb-1">
                    BERITA ACARA KOLEKTIF & PENGESAHAN LPJ
                </span>
                <h3 class="text-base sm:text-lg font-semibold text-slate-800">
                    Pengesahan Laporan & Berkas LPJ Induk BAZNAS
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Gunakan bagian ini untuk menetapkan tanggal resmi penyelesaian LPJ, mencatat pengembalian sisa kas (jika ada), serta mengunggah berkas BAST kolektif dan foto serah terima bersama.
                </p>
            </div>

            <!-- JIKA SUDAH ADA BERKAS INDUK YANG DIUNGGAH -->
            @if($pengajuan->lpjBa && ($pengajuan->lpjBa->lampirans->isNotEmpty() || $pengajuan->lpjBa->file_lpj || $pengajuan->lpjBa->file_ba))
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                        Dokumen LPJ Induk yang Telah Tersimpan:
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @if($pengajuan->lpjBa->file_lpj)
                            <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-slate-200">
                                <div class="flex items-center gap-2 truncate">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                    <span class="text-xs font-medium text-slate-700 truncate">Berkas BAST / LPJ Utama</span>
                                </div>
                                <a href="{{ asset('storage/' . $pengajuan->lpjBa->file_lpj) }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium shrink-0">Lihat</a>
                            </div>
                        @endif

                        @foreach($pengajuan->lpjBa->lampirans as $lampLpj)
                            <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-slate-200">
                                <div class="flex items-center gap-2 truncate">
                                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" /></svg>
                                    <span class="text-xs font-medium text-slate-700 truncate">{{ $lampLpj->name ?: 'Lampiran LPJ' }}</span>
                                </div>
                                <a href="{{ asset('storage/' . $lampLpj->file_path) }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium shrink-0">Lihat</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- FORM SIMPAN / PERBARUI LPJ INDUK -->
            <form method="POST" action="{{ route('pengajuan.lpj.store', $pengajuan->id) }}" enctype="multipart/form-data" class="space-y-4 text-xs sm:text-sm">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal LPJ Resmi <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_lpj" value="{{ old('tanggal_lpj', $pengajuan->lpjBa?->tanggal_lpj ? $pengajuan->lpjBa->tanggal_lpj->format('Y-m-d') : date('Y-m-d')) }}" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Nominal Realisasi Kas (Rp)</label>
                        <input type="text" inputmode="numeric" x-number name="nominal_realisasi" value="{{ old('nominal_realisasi') !== null ? (is_numeric(str_replace('.', '', old('nominal_realisasi'))) ? number_format((float)str_replace('.', '', old('nominal_realisasi')), 0, ',', '.') : old('nominal_realisasi')) : number_format((float)($pengajuan->lpjBa?->nominal_realisasi ?? $totalNominalDisalurkan), 0, ',', '.') }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-semibold text-emerald-700 shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Sisa Dana Kas (Rp)</label>
                        <input type="text" inputmode="numeric" x-number name="nominal_sisa" value="{{ old('nominal_sisa') !== null ? (is_numeric(str_replace('.', '', old('nominal_sisa'))) ? number_format((float)str_replace('.', '', old('nominal_sisa')), 0, ',', '.') : old('nominal_sisa')) : number_format((float)($pengajuan->lpjBa?->nominal_sisa ?? 0), 0, ',', '.') }}" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs text-slate-700 shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="sisa_dikembalikan" value="1" {{ old('sisa_dikembalikan', $pengajuan->lpjBa?->sisa_dikembalikan) ? 'checked' : '' }} class="rounded text-[#118243] focus:ring-[#118243]">
                            <span class="text-xs text-slate-700 font-medium">Sisa dana telah dikembalikan ke kas BAZNAS</span>
                        </label>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block font-medium text-slate-700 mb-1">Catatan / Uraian Hasil Pelaporan</label>
                        <textarea name="catatan" rows="2" placeholder="Catatan hasil penyerahan bantuan atau kendala di lapangan..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">{{ old('catatan', $pengajuan->lpjBa?->catatan ?? '') }}</textarea>
                    </div>

                    <div class="sm:col-span-1 lg:col-span-2">
                        <x-file-dropzone 
                            name="file_lpj" 
                            accept=".pdf,.jpg,.jpeg,.png"
                            :compact="true"
                            label="Upload Dokumen BAST Kolektif / LPJ Induk (PDF / JPG)"
                            help-text="Tarik & lepas berkas dokumen BAST atau klik untuk pilih (Maks 10 MB)"
                        />
                    </div>

                    <div>
                        <x-file-dropzone 
                            name="file_dokumentasi" 
                            accept="image/*,.pdf"
                            icon="camera"
                            :compact="true"
                            label="Upload Foto Dokumentasi Serah Terima Bersama"
                            help-text="Tarik & lepas foto penyerahan atau klik untuk pilih (Maks 10 MB)"
                        />
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="px-5 py-2.5 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl text-xs shadow-2xs transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        <span>Simpan Laporan Pertanggungjawaban (LPJ)</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 8. MODALS SECTION                                        -->
    <!-- ======================================================== -->

    <!-- MODAL 2: DISPOSISI & PERSETUJUAN NOMINAL (SESUAI REFERENSI GAMBAR 1) -->
    <div x-show="showDisposisiModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-xl w-full overflow-hidden shadow-2xl border border-slate-200 text-xs sm:text-sm">
            
            <!-- HEADER MODAL GELAP (DARK SLATE THEME PERSIS GAMBAR 1) -->
            <div class="bg-[#1e2536] p-5 sm:p-6 text-white relative">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1.5">
                        <!-- Badges Header -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-bold tracking-wider uppercase bg-[#064e3b]/90 border border-[#059669]/60 text-[#34d399]" 
                                  x-text="disposisi_is_perorangan ? 'DISPOSISI PERORANGAN' : 'DISPOSISI LEMBAGA'">
                                DISPOSISI PERORANGAN
                            </span>
                            <template x-if="disposisi_asnaf_nama">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-medium bg-slate-700/80 text-slate-300 border border-slate-600/50" 
                                      x-text="disposisi_asnaf_nama">
                                </span>
                            </template>
                        </div>

                        <!-- Nama Mustahik -->
                        <h3 class="text-base sm:text-lg font-bold text-white tracking-tight pt-0.5" x-text="disposisi_mustahik_nama"></h3>
                        
                        <!-- Subtitle Program -->
                        <p class="text-xs text-slate-400 font-medium" x-text="disposisi_program_nama"></p>
                    </div>

                    <!-- Tombol Silang (Close) -->
                    <button type="button" @click="showDisposisiModal = false" class="w-8 h-8 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- FORM BODY -->
            <form id="formDisposisiMustahik" method="POST" :action="'{{ url('pengajuan') }}/' + {{ $pengajuan->id }} + '/mustahik/' + disposisi_mustahik_id + '/disposisi'" class="p-5 sm:p-6 space-y-4">
                @csrf
                
                <!-- 1. Instruksi Disposisi -->
                <div>
                    <label class="block font-semibold text-slate-800 mb-1.5">
                        Instruksi Disposisi <span class="text-rose-500">*</span>
                    </label>
                    <select name="instruksi" x-model="disposisi_instruksi" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs sm:text-sm font-medium shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all">
                        <option value="Proses Langsung">Proses Penyaluran (Disetujui)</option>
                        <option value="Perlu Survey">Perlu Survey Lapangan</option>
                        <option value="Revisi">Revisi Berkas</option>
                        <option value="Tolak">Tolak Permohonan</option>
                    </select>
                </div>

                <!-- 2. Diteruskan Kepada -->
                <div>
                    <label class="block font-semibold text-slate-800 mb-1.5">
                        Diteruskan Kepada
                    </label>
                    <select name="diteruskan_kepada" x-model="disposisi_diteruskan_kepada" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs sm:text-sm font-medium shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all">
                        <option value="Wakil Ketua II (Pendistribusian & Pendayagunaan)">Wakil Ketua II (Pendistribusian & Pendayagunaan)</option>
                        <option value="Wakil Ketua I (Pengumpulan)">Wakil Ketua I (Pengumpulan)</option>
                        <option value="Wakil Ketua III (Perencanaan, Keuangan & Pelaporan)">Wakil Ketua III (Perencanaan, Keuangan & Pelaporan)</option>
                        <option value="Wakil Ketua IV (Administrasi, SDM & Umum)">Wakil Ketua IV (Administrasi, SDM & Umum)</option>
                        <option value="Ketua BAZNAS">Ketua BAZNAS</option>
                        <option value="Pelaksana Pendistribusian & Pendayagunaan">Pelaksana Pendistribusian & Pendayagunaan</option>
                    </select>
                </div>

                <!-- 3. Isi Disposisi (Checklist Cetak) -->
                <div>
                    <label class="block font-semibold text-slate-800 mb-1.5">
                        Isi Disposisi (Checklist Cetak)
                    </label>
                    <div class="bg-slate-50/90 border border-slate-200/90 rounded-2xl p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                            <label class="flex items-center gap-2.5 cursor-pointer text-slate-700 hover:text-slate-900 select-none">
                                <input type="checkbox" name="isi_disposisi[]" value="Untuk diselesaikan" x-model="disposisi_isi" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                <span class="font-medium">Untuk diselesaikan</span>
                            </label>
                            <label class="flex items-center gap-2.5 cursor-pointer text-slate-700 hover:text-slate-900 select-none">
                                <input type="checkbox" name="isi_disposisi[]" value="Untuk dikoordinasikan" x-model="disposisi_isi" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                <span class="font-medium">Untuk dikoordinasikan</span>
                            </label>
                            <label class="flex items-center gap-2.5 cursor-pointer text-slate-700 hover:text-slate-900 select-none">
                                <input type="checkbox" name="isi_disposisi[]" value="Untuk ditindaklanjuti" x-model="disposisi_isi" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                <span class="font-medium">Untuk ditindaklanjuti</span>
                            </label>
                            <label class="flex items-center gap-2.5 cursor-pointer text-slate-700 hover:text-slate-900 select-none">
                                <input type="checkbox" name="isi_disposisi[]" value="Untuk diketahui" x-model="disposisi_isi" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                <span class="font-medium">Untuk diketahui</span>
                            </label>
                            <label class="flex items-center gap-2.5 cursor-pointer text-slate-700 hover:text-slate-900 select-none">
                                <input type="checkbox" name="isi_disposisi[]" value="Catat" x-model="disposisi_isi" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                <span class="font-medium">Catat</span>
                            </label>
                            <label class="flex items-center gap-2.5 cursor-pointer text-slate-700 hover:text-slate-900 select-none">
                                <input type="checkbox" name="isi_disposisi[]" value="Wakili" x-model="disposisi_isi" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                <span class="font-medium">Wakili</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 4. Bentuk Bantuan (Uang / Barang) -->
                <div>
                    <label class="block font-semibold text-slate-800 mb-1.5">
                        Bentuk Bantuan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2.5 p-1 bg-slate-100/90 rounded-xl border border-slate-200">
                        <label :class="disposisi_bentuk_bantuan === 'Uang' ? 'bg-white text-blue-700 shadow-xs border-blue-300 font-bold' : 'text-slate-600 hover:text-slate-900 border-transparent font-medium'"
                               class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg border cursor-pointer transition-all text-xs sm:text-sm select-none">
                            <input type="radio" name="bentuk_bantuan" value="Uang" x-model="disposisi_bentuk_bantuan" class="sr-only">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v10.5m0-10.5h1.5A.75.75 0 0 1 4.5 6.75V19.5m0-12.75v12.75M21.75 19.5a.75.75 0 0 1-.75.75H4.5m17.25-.75V6.75a.75.75 0 0 0-.75-.75H19.5m0 0a60.07 60.07 0 0 0-15.797-2.101C2.976 3.701 2.25 4.241 2.25 4.995V6m17.25 0h1.5a.75.75 0 0 1 .75.75v12.75" />
                            </svg>
                            <span>Uang Tunai</span>
                        </label>
                        <label :class="disposisi_bentuk_bantuan === 'Barang' ? 'bg-white text-amber-700 shadow-xs border-amber-300 font-bold' : 'text-slate-600 hover:text-slate-900 border-transparent font-medium'"
                               class="flex items-center justify-center gap-2 py-2 px-3 rounded-lg border cursor-pointer transition-all text-xs sm:text-sm select-none">
                            <input type="radio" name="bentuk_bantuan" value="Barang" x-model="disposisi_bentuk_bantuan" class="sr-only">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                            <span>Barang (Natura)</span>
                        </label>
                    </div>
                </div>

                <!-- 5A. Nominal Bantuan (Rp) - HANYA MUNCUL JIKA BENTUK UANG -->
                <div x-show="disposisi_bentuk_bantuan === 'Uang'" x-transition>
                    <label class="block font-semibold text-slate-800 mb-1.5">
                        Nominal Bantuan (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-sm font-bold text-slate-500">Rp</span>
                        <input type="text" inputmode="numeric" x-number name="nominal_disetujui" x-model="disposisi_nominal" :required="disposisi_bentuk_bantuan === 'Uang'" placeholder="700.000" class="w-full pl-11 rounded-xl border border-slate-300 bg-white p-2.5 text-base font-bold text-[#059669] shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all">
                    </div>
                </div>

                <!-- 5B. Detail Barang - HANYA MUNCUL JIKA BENTUK BARANG -->
                <div x-show="disposisi_bentuk_bantuan === 'Barang'" x-transition class="space-y-3.5 p-4 bg-amber-50/70 rounded-2xl border border-amber-200">
                    <div>
                        <label class="block font-semibold text-slate-800 mb-1 text-xs sm:text-sm">
                            Nama / Jenis Barang
                        </label>
                        <input type="text" name="nama_barang" x-model="disposisi_nama_barang" placeholder="Contoh: Paket Sembako, Beras, Kursi Roda, dll" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs sm:text-sm font-medium shadow-xs focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition-all">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-800 mb-1 text-xs sm:text-sm">
                                Berapa Banyak (Qty) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="jumlah_barang" x-model="disposisi_jumlah_barang" :required="disposisi_bentuk_bantuan === 'Barang'" min="1" placeholder="1" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs sm:text-sm font-bold text-amber-900 shadow-xs focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition-all">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-800 mb-1 text-xs sm:text-sm">
                                Satuan Barang <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="satuan_barang" x-model="disposisi_satuan_barang" list="satuanBarangOptions" :required="disposisi_bentuk_bantuan === 'Barang'" placeholder="Pilih/Ketik satuan" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs sm:text-sm font-medium shadow-xs focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 focus:outline-none transition-all">
                            <datalist id="satuanBarangOptions">
                                <option value="Paket">
                                <option value="Unit">
                                <option value="Buah">
                                <option value="Dus">
                                <option value="Kg">
                                <option value="Karung">
                                <option value="Set">
                                <option value="Pcs">
                                <option value="Liter">
                            </datalist>
                        </div>
                    </div>
                </div>

                <!-- 5. Sumber Dana -->
                <div>
                    <label class="block font-semibold text-slate-800 mb-1.5">
                        Sumber Dana <span class="text-rose-500">*</span>
                    </label>
                    <select name="sumber_dana_id" x-model="disposisi_sumber_dana_id" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs sm:text-sm font-medium shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition-all">
                        <option value="">-- Pilih Rekening Sumber Dana --</option>
                        @foreach($danas as $d)
                            <option value="{{ $d->id }}">{{ $d->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 6. Catatan Disposisi (Opsional) -->
                <div>
                    <label class="block font-semibold text-slate-800 mb-1.5">
                        Catatan Disposisi (Opsional)
                    </label>
                    <textarea name="catatan" x-model="disposisi_catatan" rows="3" placeholder="Disposisi pimpinan untuk realisasi bantuan." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs sm:text-sm shadow-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none leading-relaxed transition-all"></textarea>
                </div>

                <!-- FOOTER ACTIONS PERSIS GAMBAR 1 -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-100 gap-3">
                    <div>
                        <template x-if="disposisi_has_existing">
                            <button type="button" 
                                    @click="batalDisposisiFromModal()"
                                    class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 font-medium rounded-xl text-xs sm:text-sm shadow-2xs inline-flex items-center gap-1.5 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                <span>Batal Disposisi</span>
                            </button>
                        </template>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="showDisposisiModal = false" class="px-5 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium rounded-xl text-xs sm:text-sm transition-all">
                            Tutup
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-[#1d63ed] hover:bg-[#1554d1] text-white font-semibold rounded-xl text-xs sm:text-sm shadow-sm inline-flex items-center gap-1.5 transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span>Simpan Disposisi</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: UNGGAH LAMPIRAN DOKUMEN TAMBAHAN -->
    <div x-show="showLampiranModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200 text-xs sm:text-sm">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <h3 class="font-semibold text-base text-slate-800">Unggah Lampiran Berkas</h3>
                <button type="button" @click="showLampiranModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('pengajuan.lampiran.store', $pengajuan->id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Nama / Judul Dokumen <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Contoh: KTP Pemohon / Surat Keterangan RS" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Kategori Dokumen <span class="text-red-500">*</span></label>
                    <select name="kategori" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                        <option value="Identitas">Identitas (KTP/KK)</option>
                        <option value="SKTM">Surat Keterangan Tidak Mampu (SKTM)</option>
                        <option value="Medis">Surat Keterangan Medis / RS</option>
                        <option value="Proposal">Proposal / RAB</option>
                        <option value="Lainnya">Dokumen Lainnya</option>
                    </select>
                </div>

                <div>
                    <x-file-dropzone 
                        name="file" 
                        accept=".pdf,.jpg,.jpeg,.png"
                        :required="true"
                        :compact="true"
                        label="Pilih File (PDF / JPG / PNG)"
                        help-text="Tarik & lepas berkas dokumen atau klik untuk pilih (Maks 10 MB)"
                    />
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showLampiranModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-medium rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl shadow-2xs">Unggah Dokumen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 5: UNGGAH BERKAS LPJ / BAST MUSTAHIK -->
    <div x-show="showUploadLpjModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200 text-xs sm:text-sm">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-semibold text-base text-slate-800">Unggah Berkas LPJ / BAST Penerima Manfaat</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Penerima: <strong class="text-slate-800 font-semibold" x-text="lpj_upload_mustahik_nama"></strong>
                    </p>
                </div>
                <button type="button" @click="showUploadLpjModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" :action="'{{ url('pengajuan') }}/' + {{ $pengajuan->id }} + '/mustahik/' + lpj_upload_mustahik_id + '/upload-lpj'" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-medium text-slate-700 mb-1">Keterangan Dokumen</label>
                    <input type="text" name="keterangan" placeholder="Contoh: BAST Ditandatangani / Foto Serah Terima" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                </div>

                <div>
                    <x-file-dropzone 
                        name="file_lpj" 
                        accept=".pdf,.jpg,.jpeg,.png"
                        :required="true"
                        :compact="true"
                        label="Pilih Berkas BAST / Foto LPJ (PDF / JPG / PNG)"
                        help-text="Tarik & lepas berkas atau klik untuk pilih (Maks 10 MB)"
                    />
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showUploadLpjModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-medium rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl shadow-2xs">Simpan Berkas LPJ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 6: SALURKAN BANTUAN PER MUSTAHIK -->
    <div x-show="showSalurMustahikModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-200 text-xs sm:text-sm">
            <div class="flex justify-between items-start border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-base text-slate-800">Realisasi Penyaluran Bantuan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Pencairan dana kas keluar untuk mustahik terpilih</p>
                </div>
                <button type="button" @click="showSalurMustahikModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Preview Data Mustahik -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1.5">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-400">Penerima Manfaat:</span>
                    <strong class="text-slate-800 font-bold" x-text="salur_mustahik_nama"></strong>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-400">Program:</span>
                    <span class="text-slate-700 font-medium" x-text="salur_program_nama"></span>
                </div>
            </div>

            <form method="POST" :action="'{{ url('pengajuan') }}/' + {{ $pengajuan->id }} + '/mustahik/' + salur_mustahik_id + '/penyaluran'" enctype="multipart/form-data" class="space-y-3.5">
                @csrf

                <!-- Detail Bantuan: Nominal / BA Pengadaan Barang -->
                <div class="space-y-3">
                    <!-- JIKA BANTUAN BERUPA BARANG -->
                    <div x-show="salur_bentuk_bantuan === 'Barang'" class="p-3.5 bg-amber-50/70 rounded-xl border border-amber-200/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-amber-200 text-amber-900 text-[10px] font-extrabold uppercase tracking-wide">BANTUAN BARANG / NATURA</span>
                                <span class="text-xs font-semibold text-amber-900" x-text="salur_disposisi_barang_info ? 'Disposisi: ' + salur_disposisi_barang_info : ''"></span>
                            </div>
                            <input type="hidden" name="nominal" value="0">
                        </div>

                        <!-- Pilih BA Pengadaan Barang -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-semibold text-slate-800 text-xs">
                                    Pilih BA Pengadaan Barang <span class="text-red-500">*</span>
                                </label>
                                <a href="{{ route('master.barangs') }}" target="_blank" class="text-[10px] text-[#118243] hover:underline font-semibold inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    Input Pengadaan Baru
                                </a>
                            </div>
                            <select name="pengadaan_barang_id" x-model="salur_pengadaan_barang_id" @change="onSelectBaPengadaan($event)" :required="salur_bentuk_bantuan === 'Barang'" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                                <option value="">-- Pilih BA Pengadaan Barang --</option>
                                @foreach($pengadaanBarangs as $pb)
                                    <option value="{{ $pb->id }}" 
                                            data-barang-nama="{{ $pb->barang?->nama }}" 
                                            data-barang-satuan="{{ $pb->barang?->satuan }}" 
                                            data-barang-stok="{{ $pb->barang?->stok ?? 0 }}"
                                            data-jumlah-ba="{{ $pb->jumlah }}"
                                            data-nomor-ba="{{ $pb->nomor_ba }}">
                                        {{ $pb->nomor_ba }} — {{ $pb->barang?->nama ?? 'Barang' }} (Stok Gudang: {{ number_format($pb->barang?->stok ?? 0, 0, ',', '.') }} {{ $pb->barang?->satuan }}) • {{ $pb->tanggal_ba?->format('d/m/Y') }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="mt-1 flex items-center justify-between text-[10px]">
                                <span class="text-slate-500">
                                    <template x-if="salur_stok_tersedia !== null">
                                        <span>Stok master saat ini: <strong class="text-emerald-700 font-bold" x-text="salur_stok_tersedia + ' ' + (salur_satuan_barang || '')"></strong></span>
                                    </template>
                                    <template x-if="salur_stok_tersedia === null">
                                        <span>Pilih Berita Acara (BA) pengadaan dari menu Barang sebagai dasar pengeluaran</span>
                                    </template>
                                </span>
                                @if($pengadaanBarangs->isEmpty())
                                    <span class="text-rose-600 font-bold">Belum ada data BA pengadaan barang di sistem</span>
                                @endif
                            </div>
                        </div>

                        <!-- Kuantitas & Satuan Barang -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-800 text-xs mb-1">
                                    Berapa Barangnya (Kuantitas) <span class="text-red-500">*</span>
                                </label>
                                <div class="flex rounded-xl shadow-xs">
                                    <input type="number" name="jumlah_barang" x-model="salur_jumlah_barang" :required="salur_bentuk_bantuan === 'Barang'" min="1" placeholder="Contoh: 1" class="w-full rounded-l-xl border border-slate-300 bg-white p-2.5 text-xs font-bold text-amber-900 focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                                    <span class="inline-flex items-center px-3 rounded-r-xl border border-l-0 border-slate-300 bg-slate-100 text-xs font-semibold text-slate-600" x-text="salur_satuan_barang || 'Satuan'"></span>
                                </div>
                                <template x-if="salur_stok_tersedia !== null && Number(salur_jumlah_barang) > Number(salur_stok_tersedia)">
                                    <p class="text-[10px] text-rose-600 font-semibold mt-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                                        <span>Jumlah melebihi stok yang tersedia (<span x-text="salur_stok_tersedia"></span>)!</span>
                                    </p>
                                </template>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-800 text-xs mb-1">
                                    Satuan Barang
                                </label>
                                <input type="text" name="satuan_barang" x-model="salur_satuan_barang" placeholder="Paket / Pcs / Dus / Kg" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs text-slate-700 shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- JIKA BANTUAN BERUPA UANG -->
                    <div class="p-3.5 bg-emerald-50/50 rounded-xl border border-emerald-200/80 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div x-show="salur_bentuk_bantuan === 'Uang'">
                                <label class="block font-semibold text-slate-800 mb-1">
                                    Nominal Penyaluran (Rp) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">Rp</span>
                                    <input type="text" inputmode="numeric" x-number name="nominal" x-model="salur_nominal" :required="salur_bentuk_bantuan === 'Uang'" placeholder="Nominal bantuan" class="w-full pl-9 rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-bold text-[#118243] shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1">
                                    Rp <span class="font-semibold text-slate-700" x-text="formatNumber(salur_nominal) || '0'"></span>
                                </p>
                            </div>

                            <div :class="salur_bentuk_bantuan === 'Barang' ? 'sm:col-span-2' : ''">
                                <label class="block font-semibold text-slate-800 mb-1">
                                    Sumber Dana <span class="text-red-500" x-show="salur_bentuk_bantuan === 'Uang'">*</span>
                                    <span class="text-[10px] text-slate-400 font-normal" x-show="salur_bentuk_bantuan === 'Barang'">(Opsional / Referensi rekening pengadaan)</span>
                                </label>
                                <select name="sumber_dana_id" x-model="salur_sumber_dana_id" :required="salur_bentuk_bantuan === 'Uang'" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                                    <option value="">-- Pilih Sumber Dana --</option>
                                    @foreach($danas as $d)
                                        <option value="{{ $d->id }}">{{ $d->kode ? $d->kode . ' - ' : '' }}{{ $d->nama }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-slate-500 mt-1" x-show="salur_bentuk_bantuan === 'Uang'">
                                    Rekening kas keluar BAZNAS
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Penyaluran <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_penyaluran" x-model="salur_tgl" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Metode Penyaluran <span class="text-red-500">*</span></label>
                        <select name="metode_penyaluran" x-model="salur_metode" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                            <option value="Tunai">Tunai / Kas Langsung</option>
                            <option value="Transfer">Transfer Bank</option>
                            <option value="Barang">Barang / Natura</option>
                        </select>
                    </div>
                </div>

                <!-- Kolom info transfer opsional -->
                <div x-show="salur_metode === 'Transfer'" class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 bg-slate-50/70 rounded-xl border border-slate-200/60">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Nama Bank</label>
                        <input type="text" name="nama_bank" x-model="salur_nama_bank" placeholder="Contoh: BSI" class="w-full rounded-lg border border-slate-300 bg-white p-2 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">No. Rekening</label>
                        <input type="text" name="no_rekening" x-model="salur_no_rek" placeholder="Nomor rekening" class="w-full rounded-lg border border-slate-300 bg-white p-2 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-1">Atas Nama</label>
                        <input type="text" name="atas_nama" x-model="salur_atas_nama" placeholder="Nama pemilik" class="w-full rounded-lg border border-slate-300 bg-white p-2 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>
                </div>

                <div>
                    <x-file-dropzone 
                        name="bukti_penyaluran" 
                        accept=".pdf,.jpg,.jpeg,.png"
                        :compact="true"
                        label="Bukti Kwitansi / Struk Transfer (PDF / JPG / PNG)"
                        help-text="Tarik & lepas berkas bukti atau klik untuk pilih (Maks 10 MB)"
                    />
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Catatan Penyaluran</label>
                    <textarea name="catatan" x-model="salur_catatan" rows="2" placeholder="Catatan proses serah terima bantuan..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showSalurMustahikModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-medium rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl shadow-2xs">Eksekusi Penyaluran</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 7: SALURKAN SEMUA SEKALIGUS (BATCH) -->
    <div x-show="showSalurSemuaModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-200 text-xs sm:text-sm">
            <div class="flex justify-between items-start border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-base text-slate-800">Salurkan Semua Sekaligus</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Penyaluran serentak untuk seluruh mustahik yang belum dicairkan</p>
                </div>
                <button type="button" @click="showSalurSemuaModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Rekap Penyaluran Serentak -->
            <div class="p-4 bg-emerald-50/60 rounded-xl border border-emerald-200/80 space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-emerald-800 font-medium">Penerima manfaat yang akan disalurkan:</span>
                    <span class="font-bold text-emerald-900">{{ $belumSalurMustahikCount }} Orang</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-emerald-200/60">
                    <span class="text-xs font-semibold text-emerald-900">Total Kas Keluar yang Dicairkan:</span>
                    <span class="text-base font-bold text-emerald-800">Rp {{ number_format($sisaBelumDisalurkan, 0, ',', '.') }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('pengajuan.penyaluran.store', $pengajuan->id) }}" enctype="multipart/form-data" class="space-y-3.5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Tanggal Penyaluran <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_penyaluran" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block font-medium text-slate-700 mb-1">Metode Penyaluran <span class="text-red-500">*</span></label>
                        <select name="metode_penyaluran" required class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                            <option value="Tunai">Tunai / Kas Langsung</option>
                            <option value="Transfer">Transfer Bank</option>
                            <option value="Barang">Barang / Natura</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Sumber Dana <span class="text-slate-400 font-normal">(Opsional - Tetapkan seragam untuk semua mustahik)</span></label>
                    <select name="sumber_dana_id" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs font-medium shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all">
                        <option value="">-- Gunakan Sumber Dana Disposisi Masing-Masing --</option>
                        @foreach($danas as $d)
                            <option value="{{ $d->id }}">{{ $d->kode ? $d->kode . ' - ' : '' }}{{ $d->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-file-dropzone 
                        name="bukti_penyaluran" 
                        accept=".pdf,.jpg,.jpeg,.png"
                        :compact="true"
                        label="Bukti Serah Terima / Kwitansi Kolektif (PDF / JPG / PNG)"
                        help-text="Tarik & lepas berkas bukti kolektif atau klik untuk pilih (Maks 10 MB)"
                    />
                </div>

                <div>
                    <label class="block font-medium text-slate-700 mb-1">Catatan Serah Terima Serentak</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan proses penyaluran serentak..." class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs shadow-xs focus:ring-2 focus:ring-[#118243]/20 focus:border-[#118243] focus:outline-none transition-all"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showSalurSemuaModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-medium rounded-xl hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white font-medium rounded-xl shadow-2xs">Salurkan Seluruhnya Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 8: KONFIRMASI BATALKAN PENYALURAN -->
    <div x-show="showBatalSalurModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200 text-xs sm:text-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-base text-slate-800">Batalkan Penyaluran?</h3>
                    <p class="text-xs text-slate-500">Konfirmasi pembatalan pencairan dana</p>
                </div>
            </div>

            <p class="text-slate-600 text-xs leading-relaxed">
                Anda yakin ingin membatalkan penyaluran untuk mustahik <strong class="text-slate-900 font-bold" x-text="batal_salur_mustahik_nama"></strong>?
                Status akan dikembalikan menjadi <em>Menunggu Penyaluran</em> dan catatan kas keluar di Buku Kas akan otomatis dibatalkan.
            </p>

            <form method="POST" :action="'{{ url('pengajuan') }}/' + {{ $pengajuan->id }} + '/mustahik/' + batal_salur_mustahik_id + '/penyaluran'">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showBatalSalurModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 font-medium rounded-xl hover:bg-slate-50">Tutup</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-medium rounded-xl shadow-2xs">Ya, Batalkan Penyaluran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function pengajuanDetailApp() {
    return {
        activeTab: '{{ $activeTab ?? 'detail' }}',
        selectedSurveyMustahikId: {{ (int) request('mustahik') ?: ($pengajuan->pengajuanMustahiks->first()?->id ?? 'null') }},
        showMustahikModal: false,
        showEditMustahikModal: false,
        showAddPenerimaSelector: false,
        modal_jenis: 'perorangan',
        modal_entity_type: 'perorangan',
        selected_person_id: '',
        modal_person_id: '',
        selected_lembaga_id: '',
        modal_lembaga_id: '',
        modal_nama: '',
        modal_nik: '',
        modal_alamat: '',
        modal_asnaf_id: '',
        modal_keterangan: '',
        modal_entity_summary: null,
        showDisposisiModal: false,
        showLampiranModal: false,
        showUploadLpjModal: false,
        lpj_upload_mustahik_id: null,
        lpj_upload_mustahik_nama: '',

        // State Penyaluran Per Mustahik & Batch
        showSalurMustahikModal: false,
        showSalurSemuaModal: false,
        showBatalSalurModal: false,
        salur_mustahik_id: null,
        salur_mustahik_nama: '',
        salur_mustahik_nik: '',
        salur_program_nama: '',
        salur_bentuk_bantuan: 'Uang',
        salur_nama_barang: '',
        salur_jumlah_barang: '',
        salur_satuan_barang: '',
        salur_disposisi_barang_info: '',
        salur_pengadaan_barang_id: '',
        salur_stok_tersedia: null,
        pengadaan_barangs: @json($pengadaanBarangs),
        salur_nominal: 0,
        salur_nominal_formatted: '0',
        salur_sumber_dana_id: '',
        salur_sumber_dana_nama: '',
        salur_tgl: '{{ date('Y-m-d') }}',
        salur_metode: 'Tunai',
        salur_nama_bank: '',
        salur_no_rek: '',
        salur_atas_nama: '',
        salur_catatan: '',
        batal_salur_mustahik_id: null,
        batal_salur_mustahik_nama: '',

        openSalurMustahik(mustahikId) {
            const pm = this.mustahiks.find(m => Number(m.id) === Number(mustahikId));
            if (!pm) return;
            this.salur_mustahik_id = pm.id;
            this.salur_mustahik_nama = (pm.mustahik && pm.mustahik.person) ? (pm.mustahik.person.nama || '') : ((pm.mustahik && pm.mustahik.lembaga) ? (pm.mustahik.lembaga.nama || '') : (pm.nama || 'Penerima Manfaat'));
            const nik = (pm.mustahik && pm.mustahik.person) ? (pm.mustahik.person.nik || '') : (pm.nik || '');
            this.salur_mustahik_nik = (nik && !nik.startsWith('TMP')) ? nik : '-';
            this.salur_program_nama = pm.program ? (pm.program.nama + (pm.subprogram ? ' (' + pm.subprogram.nama + ')' : '')) : '-';
            this.salur_bentuk_bantuan = pm.bentuk_bantuan || 'Uang';
            this.salur_nama_barang = pm.nama_barang || '';
            this.salur_jumlah_barang = pm.jumlah_barang || '';
            this.salur_satuan_barang = pm.satuan_barang || '';
            this.salur_disposisi_barang_info = (pm.jumlah_barang || '1') + ' ' + (pm.satuan_barang || 'Paket') + (pm.nama_barang ? ' (' + pm.nama_barang + ')' : '');
            this.salur_nominal = (pm.bentuk_bantuan === 'Barang') ? '' : (pm.nominal ? formatNumber(pm.nominal) : '');
            this.salur_nominal_formatted = formatNumber(this.salur_nominal) || '0';
            this.salur_sumber_dana_id = pm.sumber_dana_id ? String(pm.sumber_dana_id) : '';
            this.salur_sumber_dana_nama = (pm.sumber_dana?.kode ? (pm.sumber_dana.kode + ' - ' + (pm.sumber_dana.nama || '')) : (pm.sumberDana?.kode ? (pm.sumberDana.kode + ' - ' + (pm.sumberDana.nama || '')) : (pm.sumber_dana_id ? 'Dana #' + pm.sumber_dana_id : 'Belum Ditentukan')));
            this.salur_tgl = '{{ date('Y-m-d') }}';
            this.salur_metode = (pm.bentuk_bantuan === 'Barang') ? 'Barang' : 'Tunai';
            this.salur_nama_bank = '';
            this.salur_no_rek = '';
            this.salur_atas_nama = '';
            this.salur_catatan = '';

            // Handle BA Pengadaan initialization
            this.salur_pengadaan_barang_id = pm.pengadaan_barang_id ? String(pm.pengadaan_barang_id) : '';
            this.salur_stok_tersedia = null;

            if (this.salur_bentuk_bantuan === 'Barang') {
                if (this.salur_pengadaan_barang_id) {
                    const pb = this.pengadaan_barangs.find(p => String(p.id) === String(this.salur_pengadaan_barang_id));
                    if (pb && pb.barang) {
                        this.salur_stok_tersedia = pb.barang.stok;
                        if (!this.salur_satuan_barang) this.salur_satuan_barang = pb.barang.satuan || '';
                        if (!this.salur_nama_barang) this.salur_nama_barang = pb.barang.nama || '';
                    }
                } else if (this.pengadaan_barangs && this.pengadaan_barangs.length > 0) {
                    // Try to auto-match by name if available
                    const matchedPb = this.pengadaan_barangs.find(p => p.barang && pm.nama_barang && p.barang.nama.toLowerCase().includes(pm.nama_barang.toLowerCase()));
                    if (matchedPb) {
                        this.salur_pengadaan_barang_id = String(matchedPb.id);
                        this.salur_stok_tersedia = matchedPb.barang?.stok ?? null;
                        if (!this.salur_satuan_barang && matchedPb.barang?.satuan) this.salur_satuan_barang = matchedPb.barang.satuan;
                    }
                }
            }

            this.showSalurMustahikModal = true;
        },

        onSelectBaPengadaan(event) {
            const selectEl = event.target;
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            if (!selectedOpt || !selectedOpt.value) {
                this.salur_stok_tersedia = null;
                return;
            }
            const stok = selectedOpt.getAttribute('data-barang-stok');
            const satuan = selectedOpt.getAttribute('data-barang-satuan');
            const nama = selectedOpt.getAttribute('data-barang-nama');

            this.salur_stok_tersedia = (stok !== null && stok !== '') ? Number(stok) : null;
            if (satuan) {
                this.salur_satuan_barang = satuan;
            }
            if (nama) {
                this.salur_nama_barang = nama;
            }
        },

        openSalurSemua() {
            this.salur_tgl = '{{ date('Y-m-d') }}';
            this.salur_metode = 'Tunai';
            this.salur_catatan = '';
            this.showSalurSemuaModal = true;
        },

        openBatalSalur(mustahikId) {
            const pm = this.mustahiks.find(m => Number(m.id) === Number(mustahikId));
            if (!pm) return;
            this.batal_salur_mustahik_id = pm.id;
            this.batal_salur_mustahik_nama = (pm.mustahik && pm.mustahik.person) ? (pm.mustahik.person.nama || '') : ((pm.mustahik && pm.mustahik.lembaga) ? (pm.mustahik.lembaga.nama || '') : (pm.nama || 'Penerima Manfaat'));
            this.showBatalSalurModal = true;
        },

        openUploadLpj(pmId, nama) {
            this.lpj_upload_mustahik_id = pmId;
            this.lpj_upload_mustahik_nama = nama;
            this.showUploadLpjModal = true;
        },

        init() {
            this.scrollActiveTabIntoView();
        },

        scrollActiveTabIntoView() {
            this.$nextTick(() => {
                setTimeout(() => {
                    const activeEl = document.querySelector('.is-active-tab') || document.getElementById('tab-item-' + this.activeTab);
                    if (activeEl) {
                        activeEl.scrollIntoView({
                            behavior: 'smooth',
                            inline: 'center',
                            block: 'nearest'
                        });
                    }
                }, 60);
            });
        },

        // Edit Mustahik state
        edit_id: null,
        edit_mustahik_id: null,
        edit_nama: '',
        edit_nik: '',
        edit_jenis: 'Individu',
        edit_nik_or_ketua: '-',
        edit_alamat: '-',
        edit_program_id: '',
        edit_sub_program_id: '',
        edit_subprogram_id: '',
        edit_asnaf: '',
        edit_asnaf_id: '',
        edit_keterangan: '',

        // Disposisi state
        disposisi_mustahik_id: null,
        disposisi_mustahik_nama: '',
        disposisi_asnaf_nama: '',
        disposisi_program_nama: '',
        disposisi_is_perorangan: true,
        disposisi_has_existing: false,
        disposisi_bentuk_bantuan: 'Uang',
        disposisi_nama_barang: '',
        disposisi_jumlah_barang: '',
        disposisi_satuan_barang: '',
        disposisi_nominal: '',
        disposisi_sumber_dana_id: '',
        disposisi_instruksi: 'Proses Langsung',
        disposisi_diteruskan_kepada: 'Wakil Ketua II (Pendistribusian & Pendayagunaan)',
        disposisi_isi: ['Untuk diselesaikan'],
        disposisi_catatan: '',

        // Penyaluran state
        penyaluran_tgl: '{{ date('Y-m-d') }}',
        penyaluran_metode: 'Transfer Bank',
        penyaluran_no_rek: '',
        penyaluran_bank: '',
        penyaluran_atas_nama: '',

        mustahiks: @json($pengajuan->pengajuanMustahiks),
        programs: @json($programs),
        all_subprograms: @json($subprograms),
        modal_program_id: '{{ $pengajuan->pengajuanMustahiks->first()?->program_id ?? '' }}',
        modal_subprogram_id: '{{ $pengajuan->pengajuanMustahiks->first()?->subprogram_id ?? '' }}',

        openEditMustahik(item) {
            let target = item;
            if (typeof item !== 'object' || item === null) {
                const pm = this.mustahiks.find(m => Number(m.id) === Number(item));
                if (!pm) return;
                const isLembaga = Boolean(pm.lembaga_id || pm.mustahik?.lembaga_id || (!pm.mustahik?.person_id && pm.mustahik?.jenis_mustahik === 'lembaga'));
                const personNama = pm.mustahik?.person?.nama || '';
                const lembagaNama = pm.mustahik?.lembaga?.nama || '';
                const nama = personNama || lembagaNama || pm.nama || '';
                const personNik = pm.mustahik?.person?.nik || '';
                const cleanNik = (personNik && !personNik.startsWith('TMP')) ? personNik : (pm.nik && !pm.nik.startsWith('TMP') ? pm.nik : '');
                const nikOrKetua = isLembaga
                    ? (pm.mustahik?.lembaga?.ketua_nama ? 'Ketua: ' + pm.mustahik.lembaga.ketua_nama : (lembagaNama || cleanNik || '-'))
                    : (cleanNik || '-');
                const alamat = pm.mustahik?.person?.alamat || pm.mustahik?.lembaga?.alamat || pm.alamat || '-';

                target = {
                    id: pm.id,
                    nama: nama,
                    nik: cleanNik,
                    jenis_permohonan: isLembaga ? 'Lembaga' : 'Individu',
                    lembaga_id: pm.lembaga_id || pm.mustahik?.lembaga_id || null,
                    lembaga_nama: lembagaNama || null,
                    nik_or_ketua: nikOrKetua,
                    alamat: alamat,
                    program_id: pm.program_id,
                    sub_program_id: pm.subprogram_id ?? pm.sub_program_id,
                    asnaf: pm.asnaf_id ?? pm.asnaf,
                    keterangan: pm.keterangan || '',
                };
            }

            this.edit_id = target.id;
            this.edit_mustahik_id = target.id;
            this.edit_nama = target.nama || '';
            this.edit_nik = target.nik || '';
            this.edit_jenis = target.jenis_permohonan || (target.lembaga_id ? 'Lembaga' : 'Individu');
            this.edit_nik_or_ketua = target.nik_or_ketua || target.nik || target.lembaga_nama || '-';
            this.edit_alamat = target.alamat || '-';
            this.edit_program_id = target.program_id ? String(target.program_id) : '';
            this.edit_sub_program_id = (target.sub_program_id !== undefined && target.sub_program_id !== null) ? String(target.sub_program_id) : (target.subprogram_id ? String(target.subprogram_id) : '');
            this.edit_subprogram_id = this.edit_sub_program_id;
            this.edit_asnaf = (target.asnaf !== undefined && target.asnaf !== null) ? String(target.asnaf) : (target.asnaf_id ? String(target.asnaf_id) : '');
            this.edit_asnaf_id = this.edit_asnaf;
            this.edit_keterangan = target.keterangan || '';

            this.$nextTick(() => {
                this.edit_sub_program_id = (target.sub_program_id !== undefined && target.sub_program_id !== null) ? String(target.sub_program_id) : (target.subprogram_id ? String(target.subprogram_id) : '');
                this.edit_subprogram_id = this.edit_sub_program_id;
            });
            this.showEditMustahikModal = true;
        },

        onEditProgramChange() {
            this.edit_sub_program_id = '';
            this.edit_subprogram_id = '';
        },

        openDisposisi(mustahikId) {
            const pm = this.mustahiks.find(m => Number(m.id) === Number(mustahikId));
            if (!pm) return;
            this.disposisi_mustahik_id = pm.id;
            this.disposisi_mustahik_nama = (pm.mustahik && pm.mustahik.person) ? (pm.mustahik.person.nama || '') : ((pm.mustahik && pm.mustahik.lembaga) ? (pm.mustahik.lembaga.nama || '') : (pm.nama || 'Penerima Manfaat'));
            this.disposisi_is_perorangan = !pm.mustahik || pm.mustahik.person_id !== null || !pm.mustahik.lembaga_id;
            this.disposisi_asnaf_nama = (pm.asnaf && pm.asnaf.nama) ? pm.asnaf.nama : '';
            
            let prog = (pm.program && pm.program.nama) ? pm.program.nama : '';
            if (pm.subprogram && pm.subprogram.nama) {
                prog += ' - ' + pm.subprogram.nama;
            }
            this.disposisi_program_nama = prog || 'Program BAZNAS';
            
            this.disposisi_has_existing = (pm.disposisi !== null) || ((pm.nominal > 0 || pm.bentuk_bantuan === 'Barang') && pm.sumber_dana_id !== null);
            this.disposisi_bentuk_bantuan = pm.bentuk_bantuan || 'Uang';
            this.disposisi_nama_barang = pm.nama_barang || '';
            this.disposisi_jumlah_barang = pm.jumlah_barang || '';
            this.disposisi_satuan_barang = pm.satuan_barang || '';
            this.disposisi_nominal = (pm.bentuk_bantuan === 'Barang') ? '' : (pm.nominal ? formatNumber(pm.nominal) : '');
            this.disposisi_sumber_dana_id = pm.sumber_dana_id ? String(pm.sumber_dana_id) : '';
            this.disposisi_instruksi = (pm.disposisi && pm.disposisi.instruksi) ? pm.disposisi.instruksi : 'Proses Langsung';
            this.disposisi_diteruskan_kepada = (pm.disposisi && pm.disposisi.diteruskan_kepada) ? pm.disposisi.diteruskan_kepada : 'Wakil Ketua II (Pendistribusian & Pendayagunaan)';
            
            let savedIsi = [];
            if (pm.disposisi && pm.disposisi.isi_disposisi) {
                savedIsi = Array.isArray(pm.disposisi.isi_disposisi) ? pm.disposisi.isi_disposisi : (typeof pm.disposisi.isi_disposisi === 'string' ? JSON.parse(pm.disposisi.isi_disposisi) : []);
            }
            this.disposisi_isi = (savedIsi && savedIsi.length > 0) ? savedIsi : ['Untuk diselesaikan'];

            this.disposisi_catatan = (pm.disposisi && pm.disposisi.catatan) ? pm.disposisi.catatan : (pm.catatan_disposisi || 'Disposisi pimpinan untuk realisasi bantuan.');
            this.showDisposisiModal = true;
        },

        async batalDisposisiFromModal() {
            if (!this.disposisi_mustahik_id) return;
            const confirmed = await window.$confirm('Apakah Anda yakin ingin membatalkan disposisi untuk mustahik ini?');
            if (confirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('pengajuan') }}/' + {{ $pengajuan->id }} + '/mustahik/' + this.disposisi_mustahik_id + '/disposisi';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        },

        getSubprograms(programId) {
            if (!programId) return [];
            return this.all_subprograms.filter(sp => String(sp.program_id) === String(programId));
        },

        onModalProgramChange() {
            this.modal_subprogram_id = '';
        },

        closeAddPenerimaSelector() {
            this.showAddPenerimaSelector = false;
        },

        openPersonModal() {
            window.dispatchEvent(new CustomEvent('open-person-modal'));
        },

        closePersonModal() {
            window.dispatchEvent(new CustomEvent('close-person-modal'));
        },

        openLembagaModal() {
            window.dispatchEvent(new CustomEvent('open-lembaga-modal'));
        },

        closeLembagaModal() {
            window.dispatchEvent(new CustomEvent('close-lembaga-modal'));
        },

        onPersonSelected(detail) {
            this.modal_jenis = 'perorangan';
            this.modal_entity_type = 'perorangan';
            this.selected_person_id = detail.id || '';
            this.modal_person_id = detail.id || '';
            this.selected_lembaga_id = '';
            this.modal_lembaga_id = '';
            this.modal_nama = detail.nama || '';
            this.modal_nik = detail.nik || '';
            this.modal_alamat = detail.alamat || (detail.summary && detail.summary.alamat) || '';
            this.modal_asnaf_id = '';
            this.modal_keterangan = '';
            this.modal_entity_summary = {
                nama: detail.nama || '',
                nik: detail.nik || '-',
                telepon: detail.telepon || '-',
                pekerjaan: detail.pekerjaan || '-',
                alamat: detail.alamat || (detail.summary && detail.summary.alamat) || '-'
            };
            this.closePersonModal();
            this.showMustahikModal = true;
        },

        // Item Selection Modal Functions
        openItemSelectionModal(apiUrl) {
            this.itemSelectionApiUrl = apiUrl;
            this.itemSelectionSearch = '';
            this.itemSelectionKategori = '';
            this.selectedItems = [];
            this.fetchAvailableItems();
            this.showItemSelectionModal = true;
        },

        closeItemSelectionModal() {
            this.showItemSelectionModal = false;
        },

        async fetchAvailableItems() {
            if (!this.itemSelectionApiUrl) return;

            try {
                const url = new URL(this.itemSelectionApiUrl);
                url.searchParams.set('search', this.itemSelectionSearch);
                if (this.itemSelectionKategori) url.searchParams.set('kategori', this.itemSelectionKategori);

                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) throw new Error('Failed to fetch items');

                const data = await response.json();
                this.availableItems = data.data || [];
                this.totalItems = data.count || 0;
            } catch (error) {
                console.error('Error fetching items:', error);
                this.availableItems = [];
                this.totalItems = 0;
            }
        },

        onItemSelected(item) {
            const existingItem = this.selectedItems.find(selected => selected.id === item.id);
            if (existingItem) {
                this.selectedItems = this.selectedItems.filter(selected => selected.id !== item.id);
            } else {
                this.selectedItems = [...this.selectedItems, item];
            }
        },

        removeSelectedItem(itemId) {
            this.selectedItems = this.selectedItems.filter(item => item.id !== itemId);
        },

        confirmItemSelection() {
            // Emit event to parent component with selected items
            window.dispatchEvent(new CustomEvent('items-selected', {
                detail: {
                    items: this.selectedItems,
                    pengajuanId: '{{ $pengajuan->id }}'
                }
            }));
            this.closeItemSelectionModal();
        },

        onModalSearchChange() {
            this.debouncedFetchItems();
        },

        debouncedFetchItems() {
            clearTimeout(this.itemSearchTimeout);
            this.itemSearchTimeout = setTimeout(() => {
                this.fetchAvailableItems();
            }, 300);
        },

        onLembagaSelected(detail) {
            this.modal_jenis = 'lembaga';
            this.modal_entity_type = 'lembaga';
            this.selected_person_id = '';
            this.modal_person_id = '';
            this.selected_lembaga_id = detail.id || '';
            this.modal_lembaga_id = detail.id || '';
            this.modal_nama = detail.nama || '';
            this.modal_nik = '';
            this.modal_alamat = detail.alamat || (detail.summary && detail.summary.alamat) || '';
            this.modal_asnaf_id = '';
            this.modal_keterangan = '';
            this.modal_entity_summary = {
                nama: detail.nama || '',
                jenis: detail.jenis || 'Lembaga',
                ketua_nama: detail.ketua_nama || '-',
                ketua_nik: detail.ketua_nik || '-',
                no_registrasi: detail.no_registrasi || '-',
                telepon: detail.telepon || '-',
                alamat: detail.alamat || (detail.summary && detail.summary.alamat) || '-'
            };
            this.closeLembagaModal();
            this.showMustahikModal = true;
        }
    };
}

    // Show item selection modal
    function openItemSelectionModal(apiUrl) {
        if (window.pengajuanApp) {
            window.pengajuanApp.openItemSelectionModal(apiUrl);
        }
    }

    // Handle items selected from modal
    window.addEventListener('items-selected', function(event) {
        const { items, pengajuanId } = event.detail;

        // Add selected items to pengajuan
        if (items.length > 0) {
            // You can customize this based on your specific requirements
            // For now, we'll just show a success message
            alert(`Selected ${items.length} item(s) for approval:\n            ${items.map(item => item.nama).join(', ')}`);

            // You might want to update the form with selected items
            // or send them to the server
        }
    });
</script>

<!-- Item Selection Modal -->
<div x-cloak x-show="showItemSelectionModal" 
     @click.away="closeItemSelectionModal()"
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Pilih Barang untuk Pengajuan</h3>
                <button type="button" 
                        onclick="closeItemSelectionModal()"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="bg-white px-6 py-4">
                <!-- Search and Filter -->
                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari Barang</label>
                        <input type="text" 
                               x-model="itemSelectionSearch"
                               @input="onModalSearchChange()"
                               placeholder="Cari berdasarkan nama, kode, atau satuan..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#118243] focus:border-transparent" />
                    </div>
                    <div class="sm:w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select x-model="itemSelectionKategori" 
                                @change="fetchAvailableItems()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#118243] focus:border-transparent">
                            <option value="">Semua Kategori</option>
                            <option value="Sembako">Sembako</option>
                            <option value="ATK">ATK</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <!-- Selected Items Summary -->
                <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-blue-900">
                            Terpilih: <span x-text="selectedItems.length"></span> barang
                        </span>
                        <button type="button" 
                                x-show="selectedItems.length > 0"
                                onclick="confirmItemSelection()"
                                class="px-3 py-1 bg-[#118243] text-white text-xs rounded-lg hover:bg-[#0d6835] transition-colors">
                            Konfirmasi Pilihan
                        </button>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="item in selectedItems" :key="item.id">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <span x-text="item.nama"></span>
                                <button type="button" 
                                        @click="removeSelectedItem(item.id)"
                                        class="ml-1 text-blue-600 hover:text-blue-800">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Items List -->
                <div class="max-h-96 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <template x-for="item in availableItems" :key="item.id">
                            <div 
                                 @click="onItemSelected(item)"
                                 :class="selectedItems.find(sel => sel.id === item.id) ? 'border-[#118243] bg-green-50' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'"
                                 class="p-4 border rounded-lg cursor-pointer transition-all duration-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 text-sm" x-text="item.display_text || (item.kode + ' - ' + item.nama + ' (' + item.satuan + ') - Stok: ' + item.stok)"></h4>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <span class="inline-block px-2 py-0.5 bg-gray-100 rounded text-gray-600" x-text="item.kategori"></span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs text-gray-400">Stok</span>
                                        <p class="font-semibold text-gray-900" x-text="item.stok"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="availableItems.length === 0" class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V3.993a.993.993 0 0 0-.993-.993H3.993A1 1 01 0 0 0 3 3.993V13m0 7.007V21.993a.993.993 0 0 0 .993.993H20.993A1 1 0 0 1 21 20.993V13M6 15l2.748-4.538m4.252 0L18 8.462m-6.252 5.538L9 9.5m6.252 0L15 9.5m0 7V8.5M9 8.5V15" />
                        </svg>
                        <p class="text-gray-500">Tidak ada barang tersedia</p>
                        <p class="text-sm text-gray-400 mt-1">Silakan tambahkan barang di menu "Barang"</p>
                        <a href="{{ route('master.barangs') }}" target="_blank" 
                           class="inline-block mt-3 px-4 py-2 bg-[#118243] text-white text-sm rounded-lg hover:bg-[#0d6835] transition-colors">
                            Kelola Barang
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeItemSelectionModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
