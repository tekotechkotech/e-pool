<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lampiran Setoran ZIS UPZ - {{ $setoran->nomor_setoran }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 20px;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-weight: bold;
        }
        .meta-table td {
            padding: 2px 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .footer-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .notes {
            margin-top: 20px;
            font-size: 10px;
            page-break-inside: avoid;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 6px 14px; background: #118243; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Cetak Lampiran</button>
        <button onclick="window.close()" style="padding: 6px 14px; background: #666; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 5px;">Tutup</button>
    </div>

    <div class="header">
        LAPORAN SETORAN ZIS (Zakat, Infaq dan Shodaqoh)
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 80px;">UPZ</td>
            <td style="width: 10px;">:</td>
            <td>{{ $setoran->upz->nama ?? '........................' }}</td>
            <td style="width: 120px; text-align: right;">No. Setoran :</td>
            <td style="width: 150px;">{{ $setoran->nomor_setoran }}</td>
        </tr>
        <tr>
            <td>BULAN / PERIODE</td>
            <td>:</td>
            <td>{{ strtoupper($setoran->periode) }}</td>
            <td style="text-align: right;">Tgl Setor :</td>
            <td>{{ optional($setoran->tanggal_setor)->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No.</th>
                <th rowspan="2" style="width: 70px;">Tanggal<br>Setor</th>
                <th rowspan="2" style="width: 110px;">Nomor Pokok Wajib Zakat<br>(NPWZ)</th>
                <th rowspan="2">NAMA</th>
                <th colspan="2">BESARAN SETORAN</th>
                <th rowspan="2" style="width: 90px;">JUMLAH</th>
                <th rowspan="2" style="width: 130px;">KETERANGAN</th>
                <th rowspan="2" style="width: 85px;">PERIODE</th>
            </tr>
            <tr>
                <th style="width: 85px;">ZAKAT</th>
                <th style="width: 85px;">INFAQ/SHODAQOH</th>
            </tr>
        </thead>
        <tbody>
            @forelse($setoran->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-mono">{{ optional($setoran->tanggal_setor)->format('d/m/Y') }}</td>
                    <td class="text-center font-mono">{{ $item->npwz ?: '-' }}</td>
                    <td class="text-left">{{ strtoupper($item->nama) }}</td>
                    <td class="text-right font-mono">{{ $item->nominal_zakat > 0 ? 'Rp ' . number_format($item->nominal_zakat, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-mono">{{ $item->nominal_infak > 0 ? 'Rp ' . number_format($item->nominal_infak, 0, ',', '.') : '-' }}</td>
                    <td class="text-right font-mono" style="font-weight: bold;">{{ $item->jumlah > 0 ? 'Rp ' . number_format($item->jumlah, 0, ',', '.') : 'Rp 0 (Nihil)' }}</td>
                    <td class="text-left">{{ $item->keterangan ?: ($item->jumlah == 0 ? 'Nihil / Tidak Menyetor' : 'Penerimaan ZIS') }}</td>
                    <td class="text-center">{{ $item->periode ?: $setoran->periode }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data setoran.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="4" class="text-center">JUMLAH TOTAL</td>
                <td class="text-right font-mono">Rp {{ number_format($setoran->total_zakat, 0, ',', '.') }}</td>
                <td class="text-right font-mono">Rp {{ number_format($setoran->total_infak, 0, ',', '.') }}</td>
                <td class="text-right font-mono">Rp {{ number_format($setoran->total_jumlah, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <table class="footer-table">
        <tr>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                Mengetahui,<br>
                <strong>BAZNAS KABUPATEN CILACAP</strong>
                <div style="height: 60px;"></div>
                <strong>( .................................................... )</strong><br>
                NIP/Tanda Tangan
            </td>
            <td style="width: 50%; text-align: center; vertical-align: top;">
                Cilacap, {{ optional($setoran->tanggal_setor)->format('d F Y') }}<br>
                <strong>KETUA UNIT PENGUMPUL ZAKAT (UPZ)</strong>
                <div style="height: 60px;"></div>
                <strong>( {{ $setoran->upz->ketua_nama ?: '....................................................' }} )</strong><br>
                NIP. {{ $setoran->upz->ketua_nik ?: '....................................' }}
            </td>
        </tr>
    </table>

    <div class="notes">
        <strong>Catatan :</strong><br>
        1. NPWZ yang membuat BAZNAS.<br>
        2. Bagi yang belum mempunyai NPWZ, agar di setrip (-), karena selama ini belum memberikan data yang lengkap.<br>
        3. Bagi yang sudah mempunyai NPWZ, agar kolom NPWZ diisi.<br>
        4. Kolom keterangan di isi ketika MUZAKI/MUNFIK ada yang update (masuk/keluar).
    </div>
</body>
</html>
