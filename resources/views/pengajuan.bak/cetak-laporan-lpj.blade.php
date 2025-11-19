<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penyaluran Dana ZIS (LPJ) - {{ $pengajuan->nomor_pengajuan }}</title>
    <!-- Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tinos:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 12mm;
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
            .lpj-card {
                box-shadow: none !important;
                border: 1.5px solid #000 !important;
                margin: 0 !important;
                page-break-inside: avoid;
            }
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .font-serif-official {
            font-family: 'Tinos', 'Times New Roman', serif;
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <!-- Action Bar (Sticky Top) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-md border border-slate-200 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Tab LPJ
            </a>
            <span class="text-xs text-slate-500 hidden sm:inline">Laporan Penyaluran Dana ZIS (LPJ)</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('pengajuan.export-lpj-excel', $pengajuan) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export Excel
            </a>
            <button onclick="window.print()" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl transition shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- KERTAS LAPORAN LPJ (FORMAT SESUAI TEMPLATE EXCEL) -->
    <div class="lpj-card max-w-4xl mx-auto bg-white border border-slate-300 rounded-lg p-6 sm:p-8 text-black mb-8 shadow-lg relative font-sans">
        
        <!-- HEADER / KOP -->
        <div class="flex items-center justify-between pb-3 border-b-2 border-black">
            <!-- Left: Logo Garuda & BAZNAS Cilacap -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.webp') }}" alt="BAZNAS Logo" class="h-16 w-auto object-contain shrink-0" >
                
            </div>

            <!-- Middle: Judul Utama Laporan -->
            <div class="text-center px-2 flex-1">
                <h1 class="text-sm sm:text-base font-black uppercase tracking-tight text-slate-900 leading-tight">
                    LAPORAN PENYALURAN DANA ZIS BAZNAS KABUPATEN CILACAP
                </h1>
                <p class="text-xs sm:text-sm font-bold uppercase tracking-wide text-slate-700 mt-0.5">
                    BERDASARKAN UANG MUKA {{ $bulanTahunUangMuka }}
                </p>
            </div>

            <!-- Right: Logo Praktis & Amtivo ISO 9001 -->
            <div class="flex items-center gap-2 shrink-0">
                <img src="{{ asset('images/praktis.webp') }}" alt="Praktis BAZNAS" class="h-12 w-auto object-contain shrink-0" onerror="this.style.display='none'">
                <img src="{{ asset('images/amtivo.webp') }}" alt="Amtivo ISO 9001" class="h-12 w-auto object-contain shrink-0" onerror="this.style.display='none'">
            </div>
        </div>

        <div class="mt-4 space-y-4 text-xs sm:text-sm text-black">
            
            <!-- 1. SUMBER DANA -->
            <div>
                <h3 class="font-bold text-slate-900 mb-1">1. SUMBER DANA</h3>
                <div class="pl-4 space-y-1">
                    <div class="flex items-center">
                        <span class="w-28 font-medium">a. Zakat</span>
                        <span class="mr-2">:</span>
                        <span class="font-semibold">{{ $totalSumberZakat > 0 ? 'Rp ' . number_format($totalSumberZakat, 0, ',', '.') : 'Rp -' }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-28 font-medium">b. Infak</span>
                        <span class="mr-2">:</span>
                        <span class="font-semibold">{{ $totalSumberInfak > 0 ? 'Rp ' . number_format($totalSumberInfak, 0, ',', '.') : 'Rp -' }}</span>
                    </div>
                    <div class="flex items-center pt-1 border-t border-slate-300 w-64">
                        <span class="w-28 font-bold">Total</span>
                        <span class="mr-2 font-bold">:</span>
                        <span class="font-bold text-slate-900">{{ $totalSumberKeseluruhan > 0 ? 'Rp ' . number_format($totalSumberKeseluruhan, 0, ',', '.') : 'Rp -' }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. PENYALURAN DANA -->
            <div class="space-y-3">
                <h3 class="font-bold text-slate-900">2. PENYALURAN DANA</h3>
                
                <!-- a. Zakat -->
                <div class="space-y-1">
                    <h4 class="font-bold pl-4">a. Zakat :</h4>
                    <table class="w-full border-collapse border border-black text-xs">
                        <thead>
                            <tr class="bg-slate-100 font-bold">
                                <th class="border border-black px-2 py-1.5 text-center w-10">NO</th>
                                <th class="border border-black px-3 py-1.5 text-center w-2/5">MUSTAHIQ/KEGIATAN</th>
                                <th class="border border-black px-3 py-1.5 text-center">KETERANGAN</th>
                                <th class="border border-black px-3 py-1.5 text-center w-36">NOMINAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($zakatItems as $idx => $item)
                            <tr>
                                <td class="border border-black px-2 py-1 text-center font-medium">{{ $idx + 1 }}</td>
                                <td class="border border-black px-3 py-1 font-medium">{{ $item['nama'] }}</td>
                                <td class="border border-black px-3 py-1">{{ $item['keterangan'] }}</td>
                                <td class="border border-black px-3 py-1 text-right font-medium">
                                    {{ $item['nominal'] > 0 ? 'Rp ' . number_format($item['nominal'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="border border-black px-2 py-1 text-center">-</td>
                                <td class="border border-black px-3 py-1 text-center italic text-slate-500" colspan="2">Tidak ada penyaluran pos Zakat</td>
                                <td class="border border-black px-3 py-1 text-right">Rp -</td>
                            </tr>
                            @endforelse
                            <tr class="bg-slate-50 font-bold">
                                <td colspan="3" class="border border-black px-3 py-1.5 text-center font-bold">TOTAL</td>
                                <td class="border border-black px-3 py-1.5 text-right font-bold">
                                    {{ $totalSalurZakat > 0 ? 'Rp ' . number_format($totalSalurZakat, 0, ',', '.') : 'Rp -' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- b. Infak -->
                <div class="space-y-1">
                    <h4 class="font-bold pl-4">b. Infak :</h4>
                    <table class="w-full border-collapse border border-black text-xs">
                        <thead>
                            <tr class="bg-slate-100 font-bold">
                                <th class="border border-black px-2 py-1.5 text-center w-10">NO</th>
                                <th class="border border-black px-3 py-1.5 text-center w-2/5">MUSTAHIQ</th>
                                <th class="border border-black px-3 py-1.5 text-center">KETERANGAN</th>
                                <th class="border border-black px-3 py-1.5 text-center w-36">NOMINAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($infakItems as $idx => $item)
                            <tr>
                                <td class="border border-black px-2 py-1 text-center font-medium">{{ $idx + 1 }}</td>
                                <td class="border border-black px-3 py-1 font-medium">{{ $item['nama'] }}</td>
                                <td class="border border-black px-3 py-1">{{ $item['keterangan'] }}</td>
                                <td class="border border-black px-3 py-1 text-right font-medium">
                                    {{ $item['nominal'] > 0 ? 'Rp ' . number_format($item['nominal'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="border border-black px-2 py-1 text-center">-</td>
                                <td class="border border-black px-3 py-1 text-center italic text-slate-500" colspan="2">Tidak ada penyaluran pos Infak</td>
                                <td class="border border-black px-3 py-1 text-right">Rp -</td>
                            </tr>
                            @endforelse
                            <tr class="bg-slate-50 font-bold">
                                <td colspan="3" class="border border-black px-3 py-1.5 text-center font-bold">TOTAL</td>
                                <td class="border border-black px-3 py-1.5 text-right font-bold">
                                    {{ $totalSalurInfak > 0 ? 'Rp ' . number_format($totalSalurInfak, 0, ',', '.') : 'Rp -' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- TOTAL PENYALURAN (ZAKAT + INFAK) -->
                <div class="border-2 border-black bg-slate-100 flex items-center justify-between px-4 py-2 font-bold text-xs sm:text-sm">
                    <span class="tracking-wider uppercase">TOTAL PENYALURAN (ZAKAT & INFAK)</span>
                    <span>{{ $totalPenyaluranKeseluruhan > 0 ? 'Rp ' . number_format($totalPenyaluranKeseluruhan, 0, ',', '.') : 'Rp -' }}</span>
                </div>
            </div>

            <!-- 3. SISA DANA -->
            <div>
                <h3 class="font-bold text-slate-900 mb-1">3. SISA DANA</h3>
                <div class="pl-4 space-y-1">
                    <div class="flex items-center">
                        <span class="w-28 font-medium">a. Zakat</span>
                        <span class="mr-2">:</span>
                        <span class="font-semibold">{{ $sisaZakat > 0 ? 'Rp ' . number_format($sisaZakat, 0, ',', '.') : 'Rp -' }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-28 font-medium">b. Infak</span>
                        <span class="mr-2">:</span>
                        <span class="font-semibold">{{ $sisaInfak > 0 ? 'Rp ' . number_format($sisaInfak, 0, ',', '.') : 'Rp -' }}</span>
                    </div>
                </div>
            </div>

            <!-- 4. RINCIAN DANA -->
            <div>
                <h3 class="font-bold text-slate-900 mb-1">4. RINCIAN DANA :</h3>
                <div class="pl-4 space-y-1">
                    <div class="flex items-center">
                        <span class="w-28 font-medium">1</span>
                        <span class="mr-2">:</span>
                        <span class="font-semibold">{{ $sisaZakat > 0 ? 'Rp ' . number_format($sisaZakat, 0, ',', '.') : 'Rp -' }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-28 font-medium">2</span>
                        <span class="mr-2">:</span>
                        <span class="font-semibold">{{ $sisaInfak > 0 ? 'Rp ' . number_format($sisaInfak, 0, ',', '.') : 'Rp -' }}</span>
                    </div>
                    <div class="flex items-center pt-1 border-t border-slate-300 w-64">
                        <span class="w-28 font-bold">PENGEMBALIAN</span>
                        <span class="mr-2 font-bold">:</span>
                        <span class="font-bold text-slate-900">{{ $nominalSisaLpj > 0 ? 'Rp ' . number_format($nominalSisaLpj, 0, ',', '.') : 'Rp -' }}</span>
                    </div>
                </div>
            </div>

            <!-- TANGGAL DAN TANDA TANGAN -->
            <div class="pt-6 space-y-3">
                <div class="text-right pr-12 font-medium">
                    Cilacap, {{ $tanggalLpj->translatedFormat('d F Y') }}
                </div>

                <div class="grid grid-cols-2 gap-8 text-center pt-2">
                    <!-- Disusun Oleh -->
                    <div class="border border-slate-300 rounded-xl p-4 bg-white shadow-xs">
                        <p class="text-xs text-slate-600">Disusun oleh</p>
                        <p class="font-bold text-xs sm:text-sm text-slate-900 mt-1 uppercase">{{ $pejabat['staf_jabatan'] ?? 'STAF WK II' }}</p>
                        
                        <div class="h-16 flex items-center justify-center">
                            <!-- Tempat tanda tangan -->
                        </div>

                        <p class="font-bold text-xs sm:text-sm text-slate-900 underline uppercase tracking-tight">
                            {{ $pejabat['staf_nama'] ?? 'DZULFIKRI HIDAYAT' }}
                        </p>
                        <p class="text-[11px] text-slate-600 mt-0.5">( {{ $pejabat['staf_bidang'] ?? 'Bidang Distribusi & Pendayagunaan' }} )</p>
                    </div>

                    <!-- Mengetahui -->
                    <div class="border border-slate-300 rounded-xl p-4 bg-white shadow-xs">
                        <p class="text-xs text-slate-600">Mengetahui</p>
                        <p class="font-bold text-xs sm:text-sm text-slate-900 mt-1 uppercase">{{ $pejabat['waka2_jabatan'] ?? 'WAKIL KETUA II' }}</p>
                        
                        <div class="h-16 flex items-center justify-center">
                            <!-- Tempat tanda tangan -->
                        </div>

                        <p class="font-bold text-xs sm:text-sm text-slate-900 underline uppercase tracking-tight">
                            {{ $pejabat['waka2_nama'] ?? 'AKHMAD KHOLIL, SH' }}
                        </p>
                        <p class="text-[11px] text-slate-600 mt-0.5">( {{ $pejabat['waka2_bidang'] ?? 'Bidang Distribusi & Pendayagunaan' }} )</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
