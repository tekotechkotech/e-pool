<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pengeluaran - {{ $mustahikNama }}</title>
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
            .kwitansi-card {
                box-shadow: none !important;
                border: 1.5px solid #000 !important;
                margin: 0 !important;
                page-break-inside: avoid;
            }
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
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
                Kembali ke Pengajuan
            </a>
            <span class="text-xs text-slate-500 hidden sm:inline"> Bukti Pengeluaran Kas (BKK)</span>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold rounded-xl transition shadow-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    @foreach($items as $docIndex => $data)
    <!-- KERTAS BUKTI PENGELUARAN -->
    <div id="captureArea-{{ $docIndex }}" class="kwitansi-card max-w-4xl mx-auto bg-white border border-slate-300 rounded-lg p-6 sm:p-8 text-black mb-8 shadow-lg relative {{ $docIndex > 0 ? 'mt-8' : '' }}">
        
        <!-- HEADER / KOP -->
        <div class="flex items-start justify-between pb-3">
            <!-- Left: Logo & Info BAZNAS -->
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('images/logo.webp') }}" alt="BAZNAS Logo" class="h-20 w-auto object-contain shrink-0" onerror="this.style.display='none'">
                <div>
                    <h2 class="text-base sm:text-lg font-black tracking-tight uppercase leading-tight font-serif-official">
                        BADAN AMIL ZAKAT NASIONAL
                    </h2>
                    <h3 class="text-sm sm:text-base font-extrabold uppercase leading-tight font-serif-official">
                        {{ $baznasInfo['kabupaten'] ? 'KABUPATEN ' . strtoupper($baznasInfo['kabupaten']) : 'KABUPATEN CILACAP' }}
                    </h3>
                    <p class="text-[11px] sm:text-xs text-slate-800 leading-snug mt-0.5">
                        {{ $baznasInfo['alamat'] ?? 'Jl. Masjid No. 19 Sidanegara Cilacap' }}
                    </p>
                    <p class="text-[10px] sm:text-[11px] text-slate-800 leading-snug">
                        Telp. {{ $baznasInfo['telepon'] ?? '(0282) 539 0181' }}, Email : {{ $baznasInfo['email'] ?? 'baznaskab.cilacap@baznas.go.id' }}
                    </p>
                </div>
            </div>

            <!-- Right: Logo Branding Praktis & Amtivo -->
            <div class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/praktis.webp') }}" alt="Praktis BAZNAS" class="h-20 w-auto object-contain shrink-0" onerror="this.style.display='none'">
                <img src="{{ asset('images/amtivo.webp') }}" alt="Amtivo ISO 9001" class="h-20 w-auto object-contain shrink-0" onerror="this.style.display='none'">
            </div>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="text-center py-1.5  border-b border-t border-black">
            <h1 class="text-base sm:text-lg font-black tracking-wider uppercase font-serif-official decoration-1 ">
                BUKTI PENGELUARAN
            </h1>
        </div>

        <!-- NOMOR & TANGGAL -->
        <div class="flex justify-between items-baseline text-xs sm:text-sm font-semibold my-3">
            <div class="flex items-center">
                <span>No. : &ThickSpace; </span>
                <span class="ml-2 font-mono font-bold"> &ThickSpace; {{ $data['nomor_bkk'] }}</span>
            </div>
            <div class="flex items-center">
                <span>Tanggal : &ThickSpace; </span>
                <span class="ml-2 font-bold"> &ThickSpace; {{ $data['tanggal_penyaluran_formatted'] }}</span>
            </div>
        </div>

        <!-- IDENTITAS PEMBAYARAN -->
        <div class="border-t border-black pt-2 pb-2 text-xs sm:text-sm space-y-1.5 font-serif-official">
            <div class="grid grid-cols-12 gap-1 items-baseline">
                <div class="col-span-3 sm:col-span-2.5 font-bold">Dibayarkan Kepada</div>
                <div class="col-span-1 text-center w-3 font-bold">:</div>
                <div class="col-span-8 sm:col-span-8.5 font-bold text-slate-900 uppercase">
                    {{ $data['mustahik_nama'] }}
                </div>
            </div>

            <div class="grid grid-cols-12 gap-1 items-baseline">
                <div class="col-span-3 sm:col-span-2.5 font-bold">Alamat</div>
                <div class="col-span-1 text-center w-3 font-bold">:</div>
                <div class="col-span-8 sm:col-span-8.5 text-slate-900">
                    {{ $data['mustahik_alamat'] ?: '-' }}
                </div>
            </div>

            <div class="grid grid-cols-12 gap-1 items-center">
                <div class="col-span-3 sm:col-span-2.5 font-bold">Berupa</div>
                <div class="col-span-1 text-center w-3 font-bold">:</div>
                <div class="col-span-8 sm:col-span-8.5 flex items-center gap-6">
                    <label class="inline-flex items-center gap-1.5">
                        <span class="inline-block w-4 h-4 border border-black text-center text-xs font-bold leading-3.5">
                            {{ $data['is_uang'] ? '✓' : '' }}
                        </span>
                        <span class="font-bold">Uang</span>
                    </label>

                    <label class="inline-flex items-center gap-1.5">
                        <span class="inline-block w-4 h-4 border border-black text-center text-xs font-bold leading-3.5">
                            {{ !$data['is_uang'] ? '✓' : '' }}
                        </span>
                        <span class="font-bold">Barang</span>
                    </label>

                    <div class="ml-auto font-bold flex items-center gap-1">
                        <span>Sebanyak :</span>
                        <span class="font-mono text-sm sm:text-base border-b border-black font-extrabold px-1">
                            Rp {{ number_format($data['nominal'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-1 items-baseline">
                <div class="col-span-3 sm:col-span-2.5 font-bold">Terbilang</div>
                <div class="col-span-1 text-center w-3 font-bold">:</div>
                <div class="col-span-8 sm:col-span-8.5 italic font-bold text-slate-900 border-b border-dotted border-slate-400 pb-0.5">
                    {{ $data['terbilang'] }}
                </div>
            </div>
        </div>

        <!-- TABEL RINCIAN KEPERLUAN -->
        <div class="mt-2 font-serif-official">
            <div class="flex items-baseline gap-2 mb-1.5 text-xs sm:text-sm font-bold">
                <span>Untuk Keperluan</span>
                <span>:</span>
                <span class="uppercase tracking-wide font-extrabold text-slate-900">
                    {{ $data['program_nama'] ? 'PROGRAM ' . $data['program_nama'] : 'PROGRAM BAZNAS' }}
                </span>
            </div>

            <table class="w-full border-collapse border border-black text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-black">
                        <th class="border-r border-black w-10 py-1.5 text-center font-bold">NO</th>
                        <th class="border-r border-black py-1.5 px-3 text-left font-bold">RINCIAN KEPERLUAN / PENGELUARAN</th>
                        <th class="py-1.5 px-3 w-40 text-center font-bold">JUMLAH (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Baris 1: Sub Program -->
                    <tr class="border-b border-black">
                        <td class="border-r border-black py-1.5 text-center align-top font-semibold">1</td>
                        <td class="border-r border-black py-1.5 px-3 uppercase font-semibold">
                            {{ $data['subprogram_nama'] ? 'SUB PROGRAM ' . $data['subprogram_nama'] : '-' }}
                        </td>
                        <td class="py-1.5 px-3 text-right font-mono align-top">
                            <span class="float-left">Rp</span>
                            <span>-</span>
                        </td>
                    </tr>

                    <!-- Baris 2: Kegiatan / Keterangan Bantuan -->
                    <tr class="border-b border-black">
                        <td class="border-r border-black py-1.5 text-center align-top font-semibold">2</td>
                        <td class="border-r border-black py-1.5 px-3 uppercase font-bold text-slate-900">
                            {{ $data['uraian'] }}
                        </td>
                        <td class="py-1.5 px-3 text-right font-mono font-bold align-top">
                            <span class="float-left">Rp</span>
                            <span>{{ number_format($data['nominal'], 0, ',', '.') }}</span>
                        </td>
                    </tr>

                    <!-- Baris 3: Asnaf / Sumber Dana -->
                    <tr class="border-b border-black">
                        <td class="border-r border-black py-1.5 text-center align-top font-semibold">3</td>
                        <td class="border-r border-black py-1.5 px-3 uppercase text-slate-800">
                            {{ $data['asnaf_nama'] ? 'ASNAF ' . $data['asnaf_nama'] : ($data['sumber_dana_nama'] ? 'SUMBER: ' . $data['sumber_dana_nama'] : '') }}
                        </td>
                        <td class="py-1.5 px-3 text-right font-mono align-top">
                            <span class="float-left">Rp</span>
                            <span>-</span>
                        </td>
                    </tr>

                    <!-- Baris 4: Kosong / Tambahan -->
                    <tr class="border-b border-black">
                        <td class="border-r border-black py-1.5 text-center align-top font-semibold">4</td>
                        <td class="border-r border-black py-1.5 px-3 uppercase text-slate-700">
                            {{ $data['catatan'] ? 'CATATAN: ' . $data['catatan'] : '' }}
                        </td>
                        <td class="py-1.5 px-3 text-right font-mono align-top">
                            <span class="float-left">Rp</span>
                            <span>-</span>
                        </td>
                    </tr>

                    <!-- Baris Jumlah -->
                    <tr class="font-extrabold bg-slate-50">
                        <td colspan="2" class="border-r border-black py-2 px-4 text-center italic font-bold tracking-wider uppercase">
                            Jumlah
                        </td>
                        <td class="py-2 px-3 text-right font-mono text-sm sm:text-base border-black">
                            <span class="float-left font-bold">Rp</span>
                            <span class="font-black">{{ number_format($data['nominal'], 0, ',', '.') }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- TANDA TANGAN -->
        <div class="mt-8 pt-4 font-serif-official text-xs sm:text-sm">
            <div class="grid grid-cols-3 gap-2 text-center">
                <!-- Kolom 1: Setuju Bayar -->
                <div class="flex flex-col justify-between h-36 px-1">
                    <div>
                        <p class="font-bold">Setuju Bayar</p>
                        <p class="font-bold text-[11px] sm:text-xs text-slate-800">{{ $data['pejabat_setuju_jabatan'] }}</p>
                    </div>

                    <div>
                        <p class="font-bold text-slate-900 border-b border-black inline-block pb-0.5 uppercase">
                            {{ $data['pejabat_setuju_nama'] }}
                        </p>
                        <p class="text-[11px] sm:text-xs text-slate-700 mt-0.5">
                            ( {{ $data['pejabat_setuju_subjabatan'] }} )
                        </p>
                    </div>
                </div>

                <!-- Kolom 2: Bendahara -->
                <div class="flex flex-col justify-between h-36 px-1">
                    <div>
                        <p class="font-bold">Bendahara</p>
                    </div>

                    <div>
                        <p class="font-bold text-slate-900 border-b border-black inline-block pb-0.5 uppercase">
                            {{ $data['bendahara_nama'] }}
                        </p>
                        <p class="text-[11px] sm:text-xs text-slate-700 mt-0.5">
                            ( {{ $data['bendahara_jabatan'] }} )
                        </p>
                    </div>
                </div>

                <!-- Kolom 3: Diterima Oleh -->
                <div class="flex flex-col justify-between h-36 px-1">
                    <div>
                        <p class="font-bold">Diterima oleh</p>
                    </div>

                    <div>
                        <p class="font-bold text-slate-900 border-b border-black inline-block pb-0.5 uppercase">
                            {{ $data['penerima_nama'] }}
                        </p>
                        <p class="text-[11px] sm:text-xs text-slate-700 mt-0.5">
                            ( Penerima / Pemohon )
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @endforeach

</body>
</html>
