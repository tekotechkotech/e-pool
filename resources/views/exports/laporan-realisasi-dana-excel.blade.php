@php
    $judulLaporan = 'LAPORAN REALISASI SUMBER DAN JENIS DANA';
    if ($mode === 'masuk') {
        $judulLaporan = 'LAPORAN PENERIMAAN / PENGHIMPUNAN DANA';
    } elseif ($mode === 'keluar') {
        $judulLaporan = 'LAPORAN PENYALURAN / PENDISTRIBUSIAN DANA';
    }
    $colspan = ($mode === 'semua') ? 5 : 3;
@endphp
<table>
    <thead>
        <tr>
            <th colspan="{{ $colspan }}" style="font-weight: bold; font-size: 14pt; text-align: center;">BADAN AMIL ZAKAT NASIONAL (BAZNAS)</th>
        </tr>
        <tr>
            <th colspan="{{ $colspan }}" style="font-weight: bold; font-size: 12pt; text-align: center;">{{ $judulLaporan }}</th>
        </tr>
        <tr>
            <th colspan="{{ $colspan }}" style="text-align: center; font-style: italic;">
                Periode: 
                @if($periodeTipe === 'all')
                    Semua Waktu (s/d {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }})
                @else
                    {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                @endif
            </th>
        </tr>
        <tr></tr>
        <tr style="background-color: #E2E8F0; font-weight: bold;">
            <th style="border: 1px solid #000000; text-align: center; width: 10px;">KODE</th>
            <th style="border: 1px solid #000000; width: 40px;">JENIS DANA</th>
            @if($mode === 'semua' || $mode === 'masuk')
                <th style="border: 1px solid #000000; text-align: right; width: 24px;">PENERIMAAN (MASUK)</th>
            @endif
            @if($mode === 'semua' || $mode === 'keluar')
                <th style="border: 1px solid #000000; text-align: right; width: 24px;">PENYALURAN (KELUAR)</th>
            @endif
            @if($mode === 'semua')
                <th style="border: 1px solid #000000; text-align: right; width: 24px;">SALDO AKHIR</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($groupedData as $group)
            <!-- Header Kategori -->
            <tr style="background-color: #CBD5E1; font-weight: bold;">
                <td colspan="{{ $colspan }}" style="border: 1px solid #000000; font-weight: bold; color: #118243;">
                    {{ $group['label'] }}
                </td>
            </tr>

            <!-- Sub Akun -->
            @foreach($group['items'] as $item)
                <tr>
                    <td style="border: 1px solid #000000; text-align: center;">{{ $item['kode'] }}</td>
                    <td style="border: 1px solid #000000;">
                        {{ $item['nama'] }} {{ $item['via_upz'] ? '(Via UPZ)' : '' }}
                    </td>
                    @if($mode === 'semua' || $mode === 'masuk')
                        <td style="border: 1px solid #000000; text-align: right;">{{ $item['masuk'] }}</td>
                    @endif
                    @if($mode === 'semua' || $mode === 'keluar')
                        <td style="border: 1px solid #000000; text-align: right;">{{ $item['keluar'] }}</td>
                    @endif
                    @if($mode === 'semua')
                        <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $item['saldo'] }}</td>
                    @endif
                </tr>
            @endforeach

            <!-- Subtotal Kategori -->
            <tr style="background-color: #D1FAE5; font-weight: bold;">
                <td style="border: 1px solid #000000; text-align: center;">TOTAL</td>
                <td style="border: 1px solid #000000; font-weight: bold;">TOTAL {{ $group['label'] }}</td>
                @if($mode === 'semua' || $mode === 'masuk')
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $group['total_masuk'] }}</td>
                @endif
                @if($mode === 'semua' || $mode === 'keluar')
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $group['total_keluar'] }}</td>
                @endif
                @if($mode === 'semua')
                    <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $group['total_saldo'] }}</td>
                @endif
            </tr>
        @endforeach

        <!-- GRAND TOTAL -->
        <tr style="background-color: #0F172A; color: #FFFFFF; font-weight: bold;">
            <td colspan="2" style="border: 1px solid #000000; text-align: right; font-weight: bold; color: #FFFFFF;">
                GRAND TOTAL KESELURUHAN
            </td>
            @if($mode === 'semua' || $mode === 'masuk')
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold; color: #10B981;">
                    {{ $grandTotalMasuk }}
                </td>
            @endif
            @if($mode === 'semua' || $mode === 'keluar')
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold; color: #F87171;">
                    {{ $grandTotalKeluar }}
                </td>
            @endif
            @if($mode === 'semua')
                <td style="border: 1px solid #000000; text-align: right; font-weight: bold; color: #FFFFFF;">
                    {{ $grandTotalSaldo }}
                </td>
            @endif
        </tr>
    </tbody>
</table>
