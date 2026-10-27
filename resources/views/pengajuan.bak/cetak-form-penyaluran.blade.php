<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan - Penyaluran ZIS - {{ $nomorForm }}</title>
    <!-- Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tinos:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 15mm;
        }
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .form-card {
                box-shadow: none !important;
                border: 2px solid #000 !important;
                margin: 0 !important;
                page-break-inside: avoid;
            }
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #000;
        }
        .font-serif-official {
            font-family: 'Tinos', 'Times New Roman', serif;
        }
        .border-black-main {
            border-color: #000 !important;
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <!-- Action Bar (Sticky Top - Disembunyikan saat Print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-md border border-slate-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Pengajuan
            </a>
            <span class="text-xs text-slate-500 hidden sm:inline">Form Pengajuan - Penyaluran ZIS</span>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2 bg-[#118243] hover:bg-[#0d6835] text-white text-sm font-bold rounded-xl transition shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- KERTAS FORM PENGAJUAN - PENYALURAN ZIS -->
    <div class="form-card max-w-4xl mx-auto bg-white border-2 border-black rounded-xs text-black shadow-lg relative font-sans text-xs sm:text-sm">
        
        <!-- 1. HEADER 3 KOLOM -->
        <div class="grid grid-cols-12 border-b-2 border-black">
            <!-- Left: Logo BAZNAS Cilacap -->
            <div class="col-span-2 sm:col-span-2 p-2 sm:p-3 border-r border-black flex flex-col items-center justify-center text-center">
                <img src="{{ asset('images/logo.webp') }}" alt="BAZNAS Logo" class="h-20 sm:h-12 w-auto object-contain shrink-0" onerror="this.style.display='none'">
            </div>

            <!-- Middle: Judul Utama Form -->
            <div class="col-span-7 sm:col-span-7 p-3 sm:p-4 border-r border-black flex items-center justify-center text-center">
                <h1 class="text-sm sm:text-base md:text-lg font-black tracking-wide uppercase font-sans">
                    FORM PENGAJUAN - PENYALURAN ZIS
                </h1>
            </div>

            <!-- Right: Nomor Form -->
            <div class="col-span-3 sm:col-span-3 p-2 sm:p-3 flex flex-col justify-start text-left">
                <span class="text-[11px] sm:text-xs font-bold text-slate-800">NO.</span>
                <span class="text-[10px] sm:text-xs font-semibold font-mono break-all text-slate-950 mt-0.5">
                    {{ $nomorForm }}
                </span>
            </div>
        </div>

        <!-- 2. PARAGRAF PERMOHONAN UANG MUKA -->
        <div class="p-4 sm:p-5 border-b border-black space-y-3 leading-relaxed">
            <p class="text-xs sm:text-sm text-slate-950">
                Sehubungan dengan akan segera dilaksanakan kegiatan dari <span class="font-bold uppercase">BIDANG PENDISTRIBUSIAN</span> kami mengajukan uang muka kegiatan sebesar &nbsp;&nbsp;&nbsp;
                <span class="font-bold text-slate-950 text-sm sm:text-base">Rp &nbsp;&nbsp;&nbsp;&nbsp; {{ number_format($totalNominal, 0, ',', '.') }}</span><br>
                <span class="italic text-slate-900 text-xs sm:text-sm">( {{ $terbilang }} )</span>
            </p>

            <p class="text-xs sm:text-sm text-slate-950 pt-1">
                Dengan ini menyatakan dengan sebenarnya bahwa uang sejumlah tersebut digunakan untuk keperluan sebagai berikut :
            </p>
        </div>

        <!-- 3. TABEL ALOKASI & URAIAN KEGIATAN -->
        <div class="border-b-2 border-black">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-black text-center font-bold">
                        <th class="border-r border-black py-2 px-2 w-12 sm:w-14">NO</th>
                        <th class="border-r border-black py-2 px-3 text-center">ALOKASI <br> DANA</th>
                        <th class="border-r border-black py-2 px-3">URAIAN</th>
                        <th class="border-r border-black py-2 px-2 w-28 sm:w-32 text-center">TANGGAL</th>
                        <th class="py-2 px-3 w-36 sm:w-44 text-center">JUMLAH (Rp)</th>
                    </tr>
                    <!-- Sub-row numbering 1 2 3 4 5 -->
                    <tr class="border-b border-black text-center italic text-[11px] sm:text-xs">
                        <td class="border-r border-black py-0.5 px-2">1</td>
                        <td class="border-r border-black py-0.5 px-3">2</td>
                        <td class="border-r border-black py-0.5 px-3">3</td>
                        <td class="border-r border-black py-0.5 px-2">4</td>
                        <td class="py-0.5 px-3">5</td>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr class="border-b border-black">
                        <td class="border-r border-black py-2 px-2 text-center font-medium align-top">
                            {{ $item['no'] }}
                        </td>
                        <td class="border-r border-black py-2 px-3 font-semibold align-top text-center sm:text-left">
                            {{ $item['alokasi_dana'] }}
                        </td>
                        <td class="border-r border-black py-2 px-3 font-medium align-top leading-snug">
                            {{ $item['uraian'] }}
                        </td>
                        <td class="border-r border-black py-2 px-2 text-center font-medium align-top whitespace-nowrap">
                            {{ $item['tanggal'] }}
                        </td>
                        <td class="py-2 px-3 align-top font-semibold text-right">
                            <div class="flex justify-between items-center w-full">
                                <span>Rp</span>
                                <span>{{ number_format($item['nominal'], 0, ',', '.') }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="border-b border-black">
                        <td class="border-r border-black py-3 px-2 text-center">-</td>
                        <td class="border-r border-black py-3 px-3 text-center">-</td>
                        <td class="border-r border-black py-3 px-3 text-center italic text-slate-500">Tidak ada data rincian alokasi dana</td>
                        <td class="border-r border-black py-3 px-2 text-center">-</td>
                        <td class="py-3 px-3 text-right">Rp 0</td>
                    </tr>
                    @endforelse

                    <!-- TOTAL ROW -->
                    <tr class="font-bold text-slate-950">
                        <td colspan="4" class="border-r border-black py-2.5 px-4 text-center font-bold tracking-wider uppercase">
                            TOTAL
                        </td>
                        <td class="py-2.5 px-3 text-right font-extrabold">
                            <div class="flex justify-between items-center w-full">
                                <span>Rp</span>
                                <span>{{ number_format($totalNominal, 0, ',', '.') }}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 4. AREA TANDA TANGAN 4 KOLOM -->
        <div class="p-4 sm:p-5 border-b-2 border-black">
            <div class="mb-2 text-xs sm:text-sm font-medium">
                Tanggal, {{ $tanggalFormatted }}
            </div>

            <div class="grid grid-cols-4 gap-2 sm:gap-4 text-left pt-1">
                <!-- Kolom 1: Disusun oleh -->
                <div class="flex flex-col justify-between min-h-[130px] sm:min-h-[145px]">
                    <div>
                        <p class="text-xs text-slate-900 leading-tight">Disusun oleh</p>
                        <p class="text-xs font-bold text-slate-950 leading-tight">{{ $pejabat['staf_jabatan'] ?? 'Staf Waka II' }}</p>
                    </div>
                    <div class="pt-12 sm:pt-16">
                        <p class="font-bold text-xs sm:text-sm text-slate-950 border-b border-transparent leading-snug">
                            {{ $pejabat['staf_nama'] ?? 'Dzulfikri Hidayat' }}
                        </p>
                    </div>
                </div>

                <!-- Kolom 2: Diajukan oleh -->
                <div class="flex flex-col justify-between min-h-[130px] sm:min-h-[145px]">
                    <div>
                        <p class="text-xs text-slate-900 leading-tight">Diajukan oleh</p>
                        <p class="text-xs font-bold text-slate-950 leading-tight">{{ $pejabat['pelaksana_jabatan'] ?? 'Pelaksana Waka II' }}</p>
                    </div>
                    <div class="pt-12 sm:pt-16">
                        <p class="font-bold text-xs sm:text-sm text-slate-950 border-b border-transparent leading-snug">
                            {{ $pejabat['pelaksana_nama'] ?? 'Cili Wibowo' }}
                        </p>
                    </div>
                </div>

                <!-- Kolom 3: Mengetahui -->
                <div class="flex flex-col justify-between min-h-[130px] sm:min-h-[145px]">
                    <div>
                        <p class="text-xs text-slate-900 leading-tight">Mengetahui</p>
                        <p class="text-xs font-bold text-slate-950 leading-tight">{{ $pejabat['waka2_jabatan'] ?? 'Wakil Ketua II' }}</p>
                    </div>
                    <div class="pt-12 sm:pt-16">
                        <p class="font-bold text-xs sm:text-sm text-slate-950 border-b border-transparent leading-snug">
                            {{ $pejabat['waka2_nama'] ?? 'Akhmad Kholil, SH' }}
                        </p>
                    </div>
                </div>

                <!-- Kolom 4: Disetujui oleh -->
                <div class="flex flex-col justify-between min-h-[130px] sm:min-h-[145px]">
                    <div>
                        <p class="text-xs text-slate-900 leading-tight">Disetujui oleh,</p>
                        <p class="text-xs font-bold text-slate-950 leading-tight">{{ $pejabat['waka3_jabatan'] ?? 'Wakil Ketua III' }}</p>
                    </div>
                    <div class="pt-12 sm:pt-16">
                        <p class="font-bold text-xs sm:text-sm text-slate-950 border-b border-transparent leading-snug">
                            {{ $pejabat['waka3_nama'] ?? 'Khakimatusodiqoh, S.Pd' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. CATATAN FOOTER -->
        <div class="p-3 sm:p-4 text-[11px] sm:text-xs text-slate-900 leading-relaxed">
            <p>
                <span class="font-bold">Catatan :</span> Penyelesaian Uang Muka Kegiatan ini sesuai Hasil Rapat Pleno diselesaikan dalam waktu 1 (satu) hari dan selambat - lambatnya 5 hari kerja.
            </p>
        </div>

    </div>

</body>
</html>
