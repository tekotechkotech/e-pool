<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Disposisi - {{ $nomorAgenda }}</title>
    <!-- Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Tinos:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
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
            .disposisi-card {
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
            <span class="text-xs text-slate-500 hidden sm:inline">Lembar Disposisi Surat BAZNAS</span>
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

    <!-- KERTAS LEMBAR DISPOSISI -->
    <div class="disposisi-card max-w-4xl mx-auto bg-white border-2 border-black rounded-sm text-black shadow-lg relative font-serif-official">
        
        <!-- 1. HEADER / KOP SURAT -->
        <div class="flex items-start justify-between p-4 sm:p-5 border-b border-black">
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

        <!-- 2. JUDUL LEMBAR DISPOSISI -->
        <div class="text-center py-1.5 border-b border-black">
            <h1 class="text-base sm:text-lg font-black tracking-wider uppercase font-serif-official">
                LEMBAR DISPOSISI
            </h1>
        </div>

        <!-- 3. METADATA FORM TABLE -->
        <div class="text-xs sm:text-sm border-b border-black">
            
            <!-- Baris 1: Surat Dari -->
            <div class="flex items-center px-4 py-2 border-b border-black">
                <div class="w-32 sm:w-36 font-semibold shrink-0">Surat dari</div>
                <div class="w-4 text-center shrink-0">:</div>
                <div class="flex-1 font-bold text-center sm:text-center uppercase text-slate-950 px-2 truncate">
                    {{ $suratDari }}
                </div>
            </div>

            <!-- Baris 2: No Surat & No Agenda -->
            <div class="grid grid-cols-1 sm:grid-cols-12 border-b border-black">
                <div class="sm:col-span-7 flex items-center px-4 py-2 sm:border-r border-black">
                    <div class="w-32 sm:w-36 font-semibold shrink-0">No. Surat</div>
                    <div class="w-4 text-center shrink-0">:</div>
                    <div class="flex-1 font-bold text-center px-2 truncate font-mono text-xs sm:text-sm">
                        {{ $nomorSurat }}
                    </div>
                </div>
                <div class="sm:col-span-5 flex items-center px-4 py-2">
                    <div class="w-28 sm:w-28 font-semibold shrink-0">No. Agenda</div>
                    <div class="w-4 text-center shrink-0">:</div>
                    <!-- TODO: Kolom No. Agenda saat ini dikosongkan (masih dipertanyakan sumber nomor agendanya) -->
                    <div class="flex-1 font-bold text-center px-2 font-mono text-sm sm:text-base">
                        &nbsp;
                    </div>
                </div>
            </div>

            <!-- Baris 3: Tgl Surat & Diterima Tgl -->
            <div class="grid grid-cols-1 sm:grid-cols-12 border-b border-black">
                <div class="sm:col-span-7 flex items-center px-4 py-2 sm:border-r border-black">
                    <div class="w-32 sm:w-36 font-semibold shrink-0">Tgl. Surat</div>
                    <div class="w-4 text-center shrink-0">:</div>
                    <div class="flex-1 font-bold text-center px-2">
                        {{ $tglSurat }}
                    </div>
                </div>
                <div class="sm:col-span-5 flex items-center px-4 py-2">
                    <div class="w-28 sm:w-28 font-semibold shrink-0">Diterima Tgl.</div>
                    <div class="w-4 text-center shrink-0">:</div>
                    <div class="flex-1 font-bold text-center px-2">
                        {{ $tglDiterima }}
                    </div>
                </div>
            </div>

            <!-- Baris 4: Perihal -->
            <div class="flex items-center px-4 py-2">
                <div class="w-32 sm:w-36 font-semibold shrink-0">Perihal</div>
                <div class="w-4 text-center shrink-0">:</div>
                <div class="flex-1 font-bold text-center px-2 text-slate-950 leading-snug">
                    {{ $perihal }}
                </div>
            </div>
        </div>

        <!-- 4. DITERUSKAN KEPADA & ISI DISPOSISI (2 KOLOM) -->
        <div class="grid grid-cols-1 sm:grid-cols-12 border-b-2 border-black min-h-[320px]">
            
            <!-- Kolom Kiri: Diteruskan Kepada -->
            <div class="sm:col-span-6 p-4 sm:border-r-2 border-black flex flex-col justify-between">
                <div>
                    <div class="flex items-baseline gap-1 text-xs sm:text-sm font-semibold">
                        <span>Diteruskan Kepada</span>
                        <span>:</span>
                        @if($diteruskanKepada)
                            <span class="font-bold text-slate-900 ml-1">{{ $diteruskanKepada }}</span>
                        @endif
                    </div>
                </div>

                <!-- Ruang kosong luas untuk tanda tangan / paraf / stempel / catatan manual pimpinan -->
                <div class="my-auto py-12 text-center">
                    <!-- Area Tanda Tangan / Paraf Pimpinan -->
                </div>

                <div></div>
            </div>

            <!-- Kolom Kanan: Isi Disposisi (Checkboxes) -->
            <div class="sm:col-span-6 p-4">
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 mb-3">
                    Isi Disposisi
                </h4>

                <div class="space-y-2.5 text-xs sm:text-sm">
                    @foreach($checklistItems as $item)
                        @php
                            $isChecked = in_array($item, $checkedItems);
                        @endphp
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-5 h-5 border-2 border-black font-bold text-xs bg-white shrink-0">
                                {{ $isChecked ? '✓' : '' }}
                            </span>
                            <span class="font-medium text-slate-950">{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 5. CATATAN -->
        <div class="p-4 min-h-[140px] text-xs sm:text-sm">
            <p class="font-bold text-slate-900 mb-1">Catatan :</p>
            <div class="font-medium text-slate-950 whitespace-pre-line leading-relaxed pl-2">
                {{ $catatan ?: '' }}
            </div>
        </div>

    </div>

</body>
</html>
