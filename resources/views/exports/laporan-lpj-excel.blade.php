<table>
    <!-- Header Titles -->
    <tr>
        <th colspan="4" style="font-weight: bold; font-size: 13pt; text-align: center;">
            LAPORAN PENYALURAN DANA ZIS BAZNAS KABUPATEN CILACAP
        </th>
    </tr>
    <tr>
        <th colspan="4" style="font-weight: bold; font-size: 11pt; text-align: center;">
            BERDASARKAN UANG MUKA {{ $bulanTahunUangMuka }}
        </th>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>

    <!-- 1. SUMBER DANA -->
    <tr>
        <td colspan="4" style="font-weight: bold; font-size: 10.5pt;">1. SUMBER DANA</td>
    </tr>
    <tr>
        <td style="width: 5px;"></td>
        <td style="font-weight: 500;">a. Zakat :</td>
        <td colspan="2" style="text-align: right;">{{ $totalSumberZakat > 0 ? 'Rp ' . number_format($totalSumberZakat, 0, ',', '.') : 'Rp -' }}</td>
    </tr>
    <tr>
        <td></td>
        <td style="font-weight: 500;">b. Infak :</td>
        <td colspan="2" style="text-align: right;">{{ $totalSumberInfak > 0 ? 'Rp ' . number_format($totalSumberInfak, 0, ',', '.') : 'Rp -' }}</td>
    </tr>
    <tr>
        <td></td>
        <td style="font-weight: bold;">Total :</td>
        <td colspan="2" style="font-weight: bold; text-align: right; border-top: 1px solid #000000; border-bottom: 1px solid #000000;">
            {{ $totalSumberKeseluruhan > 0 ? 'Rp ' . number_format($totalSumberKeseluruhan, 0, ',', '.') : 'Rp -' }}
        </td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>

    <!-- 2. PENYALURAN DANA -->
    <tr>
        <td colspan="4" style="font-weight: bold; font-size: 10.5pt;">2. PENYALURAN DANA</td>
    </tr>
    
    <!-- 2.a. Zakat -->
    <tr>
        <td colspan="4" style="font-weight: bold;">a. Zakat :</td>
    </tr>
    <tr style="background-color: #E2E8F0; font-weight: bold;">
        <th style="border: 1px solid #000000; text-align: center; width: 6px;">NO</th>
        <th style="border: 1px solid #000000; text-align: center; width: 35px;">MUSTAHIQ/KEGIATAN</th>
        <th style="border: 1px solid #000000; text-align: center; width: 30px;">KETERANGAN</th>
        <th style="border: 1px solid #000000; text-align: center; width: 20px;">NOMINAL</th>
    </tr>
    @forelse($zakatItems as $idx => $item)
        <tr>
            <td style="border: 1px solid #000000; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #000000;">{{ $item['nama'] }}</td>
            <td style="border: 1px solid #000000;">{{ $item['keterangan'] }}</td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $item['nominal'] > 0 ? 'Rp ' . number_format($item['nominal'], 0, ',', '.') : '-' }}</td>
        </tr>
    @empty
        <tr>
            <td style="border: 1px solid #000000; text-align: center;">-</td>
            <td style="border: 1px solid #000000; text-align: center; font-style: italic; color: #64748b;" colspan="2">Tidak ada penyaluran pos Zakat</td>
            <td style="border: 1px solid #000000; text-align: right;">Rp -</td>
        </tr>
    @endforelse
    <tr style="background-color: #F1F5F9; font-weight: bold;">
        <td colspan="3" style="border: 1px solid #000000; text-align: center; font-weight: bold;">TOTAL</td>
        <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
            {{ $totalSalurZakat > 0 ? 'Rp ' . number_format($totalSalurZakat, 0, ',', '.') : 'Rp -' }}
        </td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>

    <!-- 2.b. Infak -->
    <tr>
        <td colspan="4" style="font-weight: bold;">b. Infak :</td>
    </tr>
    <tr style="background-color: #E2E8F0; font-weight: bold;">
        <th style="border: 1px solid #000000; text-align: center; width: 6px;">NO</th>
        <th style="border: 1px solid #000000; text-align: center; width: 35px;">MUSTAHIQ/KEGIATAN</th>
        <th style="border: 1px solid #000000; text-align: center; width: 30px;">KETERANGAN</th>
        <th style="border: 1px solid #000000; text-align: center; width: 20px;">NOMINAL</th>
    </tr>
    @forelse($infakItems as $idx => $item)
        <tr>
            <td style="border: 1px solid #000000; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #000000;">{{ $item['nama'] }}</td>
            <td style="border: 1px solid #000000;">{{ $item['keterangan'] }}</td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $item['nominal'] > 0 ? 'Rp ' . number_format($item['nominal'], 0, ',', '.') : '-' }}</td>
        </tr>
    @empty
        <tr>
            <td style="border: 1px solid #000000; text-align: center;">-</td>
            <td style="border: 1px solid #000000; text-align: center; font-style: italic; color: #64748b;" colspan="2">Tidak ada penyaluran pos Infak</td>
            <td style="border: 1px solid #000000; text-align: right;">Rp -</td>
        </tr>
    @endforelse
    <tr style="background-color: #F1F5F9; font-weight: bold;">
        <td colspan="3" style="border: 1px solid #000000; text-align: center; font-weight: bold;">TOTAL</td>
        <td style="border: 1px solid #000000; text-align: right; font-weight: bold;">
            {{ $totalSalurInfak > 0 ? 'Rp ' . number_format($totalSalurInfak, 0, ',', '.') : 'Rp -' }}
        </td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>

    <!-- TOTAL PENYALURAN ZAKAT + INFAK -->
    <tr style="background-color: #CBD5E1; font-weight: bold;">
        <td colspan="3" style="border: 1.5px solid #000000; text-align: center; font-weight: bold; font-size: 10.5pt;">TOTAL</td>
        <td style="border: 1.5px solid #000000; text-align: right; font-weight: bold; font-size: 10.5pt;">
            {{ $totalPenyaluranKeseluruhan > 0 ? 'Rp ' . number_format($totalPenyaluranKeseluruhan, 0, ',', '.') : 'Rp -' }}
        </td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>

    <!-- 3. SISA DANA -->
    <tr>
        <td colspan="4" style="font-weight: bold; font-size: 10.5pt;">3. SISA DANA</td>
    </tr>
    <tr>
        <td></td>
        <td style="font-weight: 500;">a. Zakat :</td>
        <td colspan="2" style="text-align: right;">{{ $sisaZakat > 0 ? 'Rp ' . number_format($sisaZakat, 0, ',', '.') : 'Rp -' }}</td>
    </tr>
    <tr>
        <td></td>
        <td style="font-weight: 500;">b. Infak :</td>
        <td colspan="2" style="text-align: right;">{{ $sisaInfak > 0 ? 'Rp ' . number_format($sisaInfak, 0, ',', '.') : 'Rp -' }}</td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>

    <!-- 4. RINCIAN DANA -->
    <tr>
        <td colspan="4" style="font-weight: bold; font-size: 10.5pt;">4. RINCIAN DANA :</td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: right; padding-right: 15px;">1 :</td>
        <td colspan="2" style="text-align: right;">{{ $sisaZakat > 0 ? 'Rp ' . number_format($sisaZakat, 0, ',', '.') : 'Rp -' }}</td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: right; padding-right: 15px;">2 :</td>
        <td colspan="2" style="text-align: right;">{{ $sisaInfak > 0 ? 'Rp ' . number_format($sisaInfak, 0, ',', '.') : 'Rp -' }}</td>
    </tr>
    <tr>
        <td></td>
        <td style="font-weight: bold;">PENGEMBALIAN :</td>
        <td colspan="2" style="font-weight: bold; text-align: right; border-top: 1px solid #000000;">
            {{ $nominalSisaLpj > 0 ? 'Rp ' . number_format($nominalSisaLpj, 0, ',', '.') : 'Rp -' }}
        </td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>

    <!-- TANGGAL DAN TANDA TANGAN -->
    <tr>
        <td colspan="2"></td>
        <td colspan="2" style="text-align: center; font-weight: 500;">
            Cilacap, {{ $tanggalLpj->translatedFormat('d F Y') }}
        </td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; font-weight: 500;">Disusun oleh</td>
        <td colspan="2" style="text-align: center; font-weight: 500;">Mengetahui</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; font-weight: bold;">{{ $pejabat['staf_jabatan'] ?? 'STAF WK II' }}</td>
        <td colspan="2" style="text-align: center; font-weight: bold;">{{ $pejabat['waka2_jabatan'] ?? 'WAKIL KETUA II' }}</td>
    </tr>
    <tr>
        <td colspan="2" style="height: 45px;"></td>
        <td colspan="2" style="height: 45px;"></td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; font-weight: bold; text-decoration: underline;">
            {{ $pejabat['staf_nama'] ?? 'DZULFIKRI HIDAYAT' }}
        </td>
        <td colspan="2" style="text-align: center; font-weight: bold; text-decoration: underline;">
            {{ $pejabat['waka2_nama'] ?? 'AKHMAD KHOLIL, SH' }}
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; font-size: 9pt;">( {{ $pejabat['staf_bidang'] ?? 'Bidang Distribusi & Pendayagunaan' }} )</td>
        <td colspan="2" style="text-align: center; font-size: 9pt;">( {{ $pejabat['waka2_bidang'] ?? 'Bidang Distribusi & Pendayagunaan' }} )</td>
    </tr>
</table>
