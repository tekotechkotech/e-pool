<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Laporan LPJ & BAST Mustahik' }}</title>
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
            .lpj-doc-card {
                box-shadow: none !important;
                border: 1.5px solid #000 !important;
                margin: 0 !important;
                page-break-after: always;
            }
            .lpj-doc-card:last-child {
                page-break-after: avoid;
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
            <span class="text-xs text-slate-500 hidden sm:inline">
                Berita Acara & Lembar LPJ Mustahik (Total: {{ count($items) }} Lembar)
            </span>
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

    @foreach($items as $docIndex => $data)
    <!-- KERTAS BERITA ACARA & LPJ MUSTAHIK -->
    <div class="lpj-doc-card max-w-4xl mx-auto bg-white border border-slate-300 rounded-lg p-6 sm:p-8 text-black mb-8 shadow-lg relative font-sans {{ $docIndex > 0 ? 'mt-8' : '' }}">
        
        <!-- HEADER / KOP RESMI BAZNAS -->
        <div class="flex items-start justify-between pb-3 border-b-2 border-black">
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
            <div class="flex items-center gap-2.5 shrink-0">
                <img src="{{ asset('images/praktis.webp') }}" alt="Praktis BAZNAS" class="h-16 w-auto object-contain shrink-0" onerror="this.style.display='none'">
                <img src="{{ asset('images/amtivo.webp') }}" alt="Amtivo ISO 9001" class="h-16 w-auto object-contain shrink-0" onerror="this.style.display='none'">
            </div>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="text-center py-2 border-b border-black">
            <h1 class="text-sm sm:text-base font-black tracking-wide uppercase font-serif-official">
                BERITA ACARA SERAH TERIMA & LAPORAN PERTANGGUNGJAWABAN (LPJ)
            </h1>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-800">
                PENERIMA MANFAAT BANTUAN DANA ZIS
            </p>
        </div>

        <!-- NOMOR & TANGGAL -->
        <div class="flex justify-between items-baseline text-xs sm:text-sm font-semibold my-2.5">
            <div class="flex items-center">
                <span>No. BAST LPJ :</span>
                <span class="ml-2 font-mono font-bold">{{ $data['nomor_bast'] }}</span>
            </div>
            <div class="flex items-center">
                <span>Pengajuan No :</span>
                <span class="ml-2 font-mono font-bold">{{ $pengajuan->nomor_pengajuan ?: '-' }}</span>
            </div>
        </div>

        <!-- KALIMAT PEMBUKA -->
        <div class="text-xs sm:text-sm text-slate-800 leading-relaxed text-justify mb-3">
            Pada hari ini, <strong class="text-black">{{ $data['tanggal_formatted'] }}</strong>, telah dilaksanakan serah terima bantuan dana Zakat, Infak, dan Sedekah (ZIS) oleh Badan Amil Zakat Nasional (BAZNAS) Kabupaten Cilacap kepada Penerima Manfaat (Mustahik) dengan rincian keterangan sebagai berikut:
        </div>

        <!-- TABEL RINCIAN MUSTAHIK & BANTUAN -->
        <div class="border border-black text-xs sm:text-sm mb-3">
            <table class="w-full border-collapse">
                <tbody>
                    <tr class="border-b border-black">
                        <td class="w-48 font-bold px-3 py-1.5 bg-slate-50 border-r border-black">Nama Penerima Manfaat</td>
                        <td class="px-3 py-1.5 font-bold uppercase text-slate-900">{{ $data['nama'] }}</td>
                    </tr>
                    <tr class="border-b border-black">
                        <td class="font-bold px-3 py-1.5 bg-slate-50 border-r border-black">NIK / No. Identitas</td>
                        <td class="px-3 py-1.5 font-mono">{{ $data['nik'] }}</td>
                    </tr>
                    <tr class="border-b border-black">
                        <td class="font-bold px-3 py-1.5 bg-slate-50 border-r border-black">Nomor Telepon / Kontak</td>
                        <td class="px-3 py-1.5">{{ $data['telepon'] }}</td>
                    </tr>
                    <tr class="border-b border-black">
                        <td class="font-bold px-3 py-1.5 bg-slate-50 border-r border-black">Alamat Lengkap</td>
                        <td class="px-3 py-1.5">{{ $data['alamat'] }}</td>
                    </tr>
                    <tr class="border-b border-black">
                        <td class="font-bold px-3 py-1.5 bg-slate-50 border-r border-black">Golongan Asnaf</td>
                        <td class="px-3 py-1.5">{{ $data['asnaf_nama'] }}</td>
                    </tr>
                    <tr class="border-b border-black">
                        <td class="font-bold px-3 py-1.5 bg-slate-50 border-r border-black">Program BAZNAS</td>
                        <td class="px-3 py-1.5 font-medium">
                            {{ $data['program_nama'] }}
                            @if($data['subprogram_nama'])
                                <span class="text-slate-600">({{ $data['subprogram_nama'] }})</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="border-b border-black">
                        <td class="font-bold px-3 py-1.5 bg-slate-50 border-r border-black">Akun Sumber Dana</td>
                        <td class="px-3 py-1.5">{{ $data['sumber_dana_nama'] }}</td>
                    </tr>
                    <tr class="border-b border-black">
                        <td class="font-bold px-3 py-1.5 bg-slate-50 border-r border-black">Metode Penyaluran</td>
                        <td class="px-3 py-1.5 font-medium">{{ $data['metode_penyaluran'] }}</td>
                    </tr>
                    <tr class="border-b border-black bg-emerald-50/50">
                        <td class="font-bold px-3 py-2 bg-slate-100 border-r border-black text-slate-900">Nominal Bantuan Diterima</td>
                        <td class="px-3 py-2">
                            <span class="text-base font-bold text-slate-900">Rp {{ number_format($data['nominal'], 0, ',', '.') }}</span>
                            <span class="block text-xs italic text-slate-600 font-serif-official capitalize mt-0.5">
                                ( {{ $data['terbilang'] }} rupiah )
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold px-3 py-1.5 bg-slate-50 border-r border-black align-top">Peruntukan / Keterangan</td>
                        <td class="px-3 py-1.5 leading-relaxed">{{ $data['uraian'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PERNYATAAN PERTANGGUNGJAWABAN MUSTAHIK -->
        <div class="border border-black p-3 bg-slate-50/80 rounded-sm text-xs sm:text-[13px] leading-relaxed mb-4">
            <h4 class="font-bold uppercase tracking-tight text-slate-900 mb-1">
                Pernyataan Pertanggungjawaban Penerima Bantuan:
            </h4>
            <p class="text-justify text-slate-800">
                Dengan ini saya menyatakan telah menerima bantuan dana ZIS tersebut di atas secara utuh tanpa ada potongan dalam bentuk apapun dari pihak BAZNAS Kabupaten Cilacap. Saya berkomitmen dan bertanggung jawab penuh untuk mempergunakan bantuan ini sesuai dengan peruntukan yang diajukan dan ketentuan syariat Islam. Apabila di kemudian hari terbukti terjadi penyalahgunaan bantuan ini, saya bersedia mempertanggungjawabkannya sesuai ketentuan yang berlaku.
            </p>
        </div>

        <!-- LAMPIRAN FOTO / DOKUMENTASI MUSTAHIK JIKA SUDAH ADA -->
        @if($data['lampiran_lpj']->count() > 0 || $data['bukti_salur'])
            <div class="no-print my-3 p-3 border border-dashed border-slate-300 rounded-xl bg-slate-50">
                <span class="text-xs font-semibold text-slate-600 block mb-2">Berkas Dokumentasi Terlampir:</span>
                <div class="flex flex-wrap gap-2">
                    @if($data['bukti_salur'])
                        <a href="{{ asset('storage/' . $data['bukti_salur']->file_path) }}" target="_blank" class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xs font-medium text-emerald-700 hover:underline">
                            Bukti Transfer / Kas
                        </a>
                    @endif
                    @foreach($data['lampiran_lpj'] as $lamp)
                        <a href="{{ asset('storage/' . $lamp->file_path) }}" target="_blank" class="px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xs font-medium text-blue-700 hover:underline">
                            {{ $lamp->name ?: 'Foto Dokumentasi / Tanda Terima' }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- TANGGAL DAN TANDA TANGAN -->
        <div class="pt-2 space-y-2">
            <div class="text-right pr-4 font-medium text-xs sm:text-sm">
                Cilacap, {{ $data['tanggal_formatted'] }}
            </div>

            <div class="grid grid-cols-2 gap-8 text-center pt-1 font-serif-official">
                <!-- Pihak BAZNAS -->
                <div class="p-2">
                    <p class="text-xs font-medium text-slate-700">Yang Menyerahkan,</p>
                    <p class="font-bold text-xs sm:text-sm text-slate-900 mt-0.5 uppercase">
                        BAZNAS KABUPATEN CILACAP
                    </p>
                    
                    <div class="h-16 flex items-center justify-center">
                        <!-- Ruang tanda tangan & cap -->
                    </div>

                    <p class="font-bold text-xs sm:text-sm text-slate-900 underline uppercase tracking-tight">
                        {{ $data['pejabat_staf_nama'] ?? 'DZULFIKRI HIDAYAT' }}
                    </p>
                    <p class="text-[11px] text-slate-600 font-sans mt-0.5">
                        {{ $data['pejabat_staf_jabatan'] ?? 'Staf WK II' }} ({{ $data['pejabat_staf_bidang'] ?? 'Bidang Distribusi & Pendayagunaan' }})
                    </p>
                </div>

                <!-- Penerima Manfaat -->
                <div class="p-2">
                    <p class="text-xs font-medium text-slate-700">Yang Menerima (Mustahik),</p>
                    <p class="font-bold text-xs sm:text-sm text-slate-900 mt-0.5 uppercase">
                        PENERIMA MANFAAT
                    </p>
                    
                    <div class="h-16 flex items-center justify-center">
                        <!-- Ruang tanda tangan mustahik -->
                    </div>

                    <p class="font-bold text-xs sm:text-sm text-slate-900 underline uppercase tracking-tight">
                        {{ $data['nama'] }}
                    </p>
                    <p class="text-[11px] text-slate-600 font-sans mt-0.5">
                        Mustahik / Penerima Bantuan
                    </p>
                </div>
            </div>
        </div>

    </div>
    @endforeach

</body>
</html>
