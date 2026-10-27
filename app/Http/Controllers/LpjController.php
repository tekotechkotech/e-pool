<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBantuan;
use App\Models\PengajuanMustahik;
use App\Models\Pengurus;
use App\Models\Lampiran;
use App\Helpers\TerbilangHelper;
use App\Exports\LaporanLpjExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LpjController extends Controller
{
    private function getBaznasInfo(): array
    {
        $default = [
            'nama_baznas' => 'BADAN AMIL ZAKAT NASIONAL',
            'kabupaten'   => 'CILACAP',
            'alamat'      => 'Jl. Masjid No. 19 Sidanegara Cilacap',
            'telepon'     => '(0282) 539 0181',
            'email'       => 'baznaskab.cilacap@baznas.go.id',
        ];

        if (Storage::disk('local')->exists('baznas_info.json')) {
            $data = json_decode(Storage::disk('local')->get('baznas_info.json'), true);
            return array_merge($default, $data ?: []);
        }

        return $default;
    }

    private function getPejabatBaznas(): array
    {
        $penguruses = Pengurus::with(['person', 'pengurusJabatan'])
            ->where('jenis_pengurus', 'Baznas')
            ->where('is_active', true)
            ->get();

        // Cari Wakil Ketua II (Bidang Distribusi & Pendayagunaan)
        $waka2 = $penguruses->first(function ($p) {
            $j = strtolower($p->pengurusJabatan?->nama ?? '');
            return str_contains($j, 'wakil ketua ii') || str_contains($j, 'waka ii') || str_contains($j, 'pendistribusian');
        });

        // Cari Staf WK II / Staf Pendistribusian
        $stafWk2 = $penguruses->first(function ($p) {
            $j = strtolower($p->pengurusJabatan?->nama ?? '');
            $personNama = strtolower($p->person?->nama ?? '');
            return str_contains($personNama, 'dzulfikri') || str_contains($personNama, 'fikri') || (str_contains($j, 'staf') && (str_contains($j, 'ii') || str_contains($j, 'distribusi') || str_contains($j, 'pendayagunaan')));
        });

        $waka2Nama = $waka2?->person?->nama ?? 'AKHMAD KHOLIL, SH';
        $waka2Jabatan = $waka2?->pengurusJabatan?->nama ?? 'WAKIL KETUA II';
        $waka2Bidang = 'Bidang Distribusi & Pendayagunaan';

        $stafNama = $stafWk2?->person?->nama ?? 'DZULFIKRI HIDAYAT';
        $stafJabatan = 'STAF WK II';
        $stafBidang = 'Bidang Distribusi & Pendayagunaan';

        return [
            'waka2_nama'    => $waka2Nama,
            'waka2_jabatan' => $waka2Jabatan,
            'waka2_bidang'  => $waka2Bidang,
            'staf_nama'     => $stafNama,
            'staf_jabatan'  => $stafJabatan,
            'staf_bidang'   => $stafBidang,
        ];
    }

    private function prepareLpjData(PengajuanBantuan $pengajuan): array
    {
        $pengajuan->load([
            'lembaga',
            'upz',
            'mustahik.person',
            'mustahik.lembaga',
            'pengajuanMustahiks.mustahik.person',
            'pengajuanMustahiks.mustahik.lembaga',
            'pengajuanMustahiks.sumberDana',
            'pengajuanMustahiks.program',
            'pengajuanMustahiks.subprogram',
            'lpjBa.createdBy',
            'persetujuan',
            'penyaluran',
        ]);

        $baznasInfo = $this->getBaznasInfo();
        $pejabat = $this->getPejabatBaznas();

        $mustahikList = $pengajuan->pengajuanMustahiks;

        // Kelompokkan mustahik berdasarkan kategori sumber dana (Zakat vs Infak/Sedekah/Lainnya)
        $zakatItems = [];
        $infakItems = [];

        $totalSumberZakat = 0;
        $totalSumberInfak = 0;
        $totalSalurZakat = 0;
        $totalSalurInfak = 0;

        foreach ($mustahikList as $pm) {
            $sumberDana = $pm->sumberDana;
            $namaDana = $sumberDana?->nama ?? '';
            $kategoriDana = $sumberDana?->kategori ?? '';
            $isZakat = str_contains(strtolower($namaDana), 'zakat') || str_contains(strtolower($kategoriDana), 'zakat');

            $m = $pm->mustahik;
            $isPerorangan = ($m?->person_id || !$m?->lembaga_id);
            $namaMustahik = $isPerorangan 
                ? ($m?->person?->nama ?? $pengajuan->mustahik_nama_snapshot ?? 'Mustahik') 
                : ($m?->lembaga?->nama ?? $pengajuan->lembaga_nama_snapshot ?? 'Lembaga');

            $keterangan = $pm->keterangan ?: ($pm->program?->nama ? $pm->program->nama . ($pm->subprogram ? ' - ' . $pm->subprogram->nama : '') : ($pengajuan->judul_surat ?: '-'));
            $nominal = (float) ($pm->nominal ?? 0);

            $itemData = [
                'nama'       => $namaMustahik,
                'keterangan' => $keterangan,
                'nominal'    => $nominal,
                'dana'       => $namaDana,
            ];

            if ($isZakat) {
                $zakatItems[] = $itemData;
                $totalSumberZakat += $nominal;
                if ($pm->is_disalurkan) {
                    $totalSalurZakat += $nominal;
                }
            } else {
                $infakItems[] = $itemData;
                $totalSumberInfak += $nominal;
                if ($pm->is_disalurkan) {
                    $totalSalurInfak += $nominal;
                }
            }
        }

        // Jika data LPJ BA ada penyesuaian nominal_realisasi & sisa
        $nominalRealisasiLpj = $pengajuan->lpjBa ? (float) $pengajuan->lpjBa->nominal_realisasi : ($totalSalurZakat + $totalSalurInfak);
        $nominalSisaLpj = $pengajuan->lpjBa ? (float) $pengajuan->lpjBa->nominal_sisa : max(0, ($totalSumberZakat + $totalSumberInfak) - $nominalRealisasiLpj);

        // Jika semua disalurkan penuh tapi ada sisa di LPJ BA
        $totalPenyaluranKeseluruhan = $totalSalurZakat + $totalSalurInfak;
        $totalSumberKeseluruhan = $totalSumberZakat + $totalSumberInfak;

        // Hitung sisa per zakat / infak proporsional jika belum spesifik
        $sisaZakat = 0;
        $sisaInfak = 0;
        if ($totalSumberKeseluruhan > 0 && $nominalSisaLpj > 0) {
            if ($totalSumberZakat > 0 && $totalSumberInfak == 0) {
                $sisaZakat = $nominalSisaLpj;
            } elseif ($totalSumberInfak > 0 && $totalSumberZakat == 0) {
                $sisaInfak = $nominalSisaLpj;
            } else {
                $propZakat = $totalSumberZakat / $totalSumberKeseluruhan;
                $sisaZakat = round($nominalSisaLpj * $propZakat);
                $sisaInfak = $nominalSisaLpj - $sisaZakat;
            }
        }

        // Tanggal LPJ
        $tanggalLpj = $pengajuan->lpjBa?->tanggal_lpj 
            ? Carbon::parse($pengajuan->lpjBa->tanggal_lpj) 
            : Carbon::now();

        // Bulan tahun untuk judul (contoh: "MARET 2026")
        $bulanTahunUangMuka = strtoupper($tanggalLpj->translatedFormat('F Y'));

        $isUpz = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']);
        $backRoute = $isUpz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';
        $backUrl = route($backRoute, ['pengajuan' => $pengajuan->id, 'tab' => 'lpj']);

        return [
            'pengajuan'                  => $pengajuan,
            'baznasInfo'                 => $baznasInfo,
            'pejabat'                    => $pejabat,
            'bulanTahunUangMuka'         => $bulanTahunUangMuka,
            'tanggalLpj'                 => $tanggalLpj,
            'zakatItems'                 => $zakatItems,
            'infakItems'                 => $infakItems,
            'totalSumberZakat'           => $totalSumberZakat,
            'totalSumberInfak'           => $totalSumberInfak,
            'totalSumberKeseluruhan'     => $totalSumberKeseluruhan,
            'totalSalurZakat'            => $totalSalurZakat,
            'totalSalurInfak'            => $totalSalurInfak,
            'totalPenyaluranKeseluruhan' => $totalPenyaluranKeseluruhan,
            'sisaZakat'                  => $sisaZakat,
            'sisaInfak'                  => $sisaInfak,
            'nominalSisaLpj'             => $nominalSisaLpj,
            'nominalRealisasiLpj'        => $nominalRealisasiLpj,
            'backUrl'                    => $backUrl,
        ];
    }

    /**
     * Cetak Laporan LPJ (Tampilan Web / Print PDF)
     */
    public function cetak($id)
    {
        $pengajuan = PengajuanBantuan::findOrFail($id);
        $data = $this->prepareLpjData($pengajuan);

        return view('pengajuan.cetak-laporan-lpj', $data);
    }

    /**
     * Export Laporan LPJ ke Excel (.xlsx)
     */
    public function exportExcel($id)
    {
        $pengajuan = PengajuanBantuan::findOrFail($id);
        $data = $this->prepareLpjData($pengajuan);

        $cleanNomor = str_replace(['/', '\\', ' '], '_', $pengajuan->nomor_pengajuan ?: 'LPJ_' . $pengajuan->id);
        $fileName = 'Laporan_LPJ_' . $cleanNomor . '.xlsx';

        return Excel::download(new LaporanLpjExport($data), $fileName);
    }

    private function getRomawi(int $bulan): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $map[$bulan] ?? 'I';
    }

    private function formatMustahikLpjItem(PengajuanMustahik $pm, PengajuanBantuan $pengajuan, array $pejabat): array
    {
        $m = $pm->mustahik;
        $isPerorangan = ($m?->person_id || !$m?->lembaga_id);
        $mPerson = $m?->person;
        $mLembaga = $m?->lembaga;

        $namaPenerima = $isPerorangan 
            ? ($mPerson?->nama ?? $pengajuan->mustahik_nama_snapshot ?? 'Mustahik') 
            : ($mLembaga?->nama ?? $pengajuan->lembaga_nama_snapshot ?? 'Lembaga');

        $alamatPenerima = $isPerorangan
            ? ($mPerson?->alamat ?? $pengajuan->mustahik_alamat_snapshot ?? '-')
            : ($mLembaga?->alamat ?? $pengajuan->lembaga_alamat_snapshot ?? '-');

        $nik = $isPerorangan ? ($mPerson?->nik ?? '-') : ($mLembaga?->nomor_izin ?? '-');
        if (str_starts_with($nik, 'TMP')) {
            $nik = '-';
        }
        $telepon = $isPerorangan ? ($mPerson?->telepon ?? '-') : ($mLembaga?->telepon ?? '-');

        $tanggalPenyaluran = $pm->tanggal_penyaluran ?? $pengajuan->lpjBa?->tanggal_lpj ?? Carbon::now();
        $bulanRomawi = $this->getRomawi((int) $tanggalPenyaluran->format('n'));
        $tahun = $tanggalPenyaluran->format('Y');

        $nomorBast = ($pm->id) . '/BAST-LPJ/' . $bulanRomawi . '/' . $tahun;
        $nominal = (int) ($pm->nominal ?? 0);
        $terbilang = TerbilangHelper::terbilang($nominal);

        $uraian = $pm->keterangan ?: ($pengajuan->judul_surat ?: 'Bantuan ' . ($pm->program?->nama ?? ''));

        $lampiranLpj = $pm->lampirans->where('category', 'lpj_mustahik');
        $buktiSalur = $pm->buktiPenyaluran;

        return [
            'pm_id'               => $pm->id,
            'nomor_bast'          => $nomorBast,
            'tanggal'             => $tanggalPenyaluran,
            'tanggal_formatted'   => $tanggalPenyaluran->translatedFormat('d F Y'),
            'nama'                => $namaPenerima,
            'nik'                 => $nik,
            'telepon'             => $telepon,
            'alamat'              => $alamatPenerima,
            'is_perorangan'       => $isPerorangan,
            'program_nama'        => $pm->program?->nama ?? '-',
            'subprogram_nama'     => $pm->subprogram?->nama ?? '',
            'asnaf_nama'          => $pm->asnaf?->nama ?? '-',
            'sumber_dana_nama'    => $pm->sumberDana?->nama ?? 'Kas BAZNAS',
            'metode_penyaluran'   => $pm->metode_penyaluran ?? 'Tunai',
            'nominal'             => $nominal,
            'terbilang'           => $terbilang,
            'uraian'              => $uraian,
            'catatan'             => $pm->catatan_penyaluran ?: '',
            'lampiran_lpj'        => $lampiranLpj,
            'bukti_salur'         => $buktiSalur,
            'pejabat_waka2_nama'  => $pejabat['waka2_nama'],
            'pejabat_waka2_jabatan'=> $pejabat['waka2_jabatan'],
            'pejabat_waka2_bidang'=> $pejabat['waka2_bidang'],
            'pejabat_staf_nama'   => $pejabat['staf_nama'],
            'pejabat_staf_jabatan'=> $pejabat['staf_jabatan'],
            'pejabat_staf_bidang' => $pejabat['staf_bidang'],
        ];
    }

    /**
     * Cetak Berita Acara & Lembar LPJ untuk Satu Mustahik
     */
    public function cetakMustahik($pengajuanId, $pmId)
    {
        $pengajuan = PengajuanBantuan::findOrFail($pengajuanId);
        $pm = PengajuanMustahik::with([
            'pengajuan.lembaga',
            'pengajuan.upz',
            'mustahik.person',
            'mustahik.lembaga',
            'program',
            'subprogram',
            'asnaf',
            'sumberDana',
            'lampirans',
            'buktiPenyaluran'
        ])->where('pengajuan_id', $pengajuan->id)->findOrFail($pmId);

        $baznasInfo = $this->getBaznasInfo();
        $pejabat = $this->getPejabatBaznas();

        $itemData = $this->formatMustahikLpjItem($pm, $pengajuan, $pejabat);

        $isUpz = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']);
        $backRoute = $isUpz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';
        $backUrl = route($backRoute, ['pengajuan' => $pengajuan->id, 'tab' => 'lpj']);

        return view('pengajuan.cetak-laporan-lpj-mustahik', [
            'pengajuan'  => $pengajuan,
            'baznasInfo' => $baznasInfo,
            'items'      => [$itemData],
            'backUrl'    => $backUrl,
            'title'      => 'BAST LPJ - ' . $itemData['nama'],
        ]);
    }

    /**
     * Cetak Berita Acara & Lembar LPJ untuk Semua Mustahik (Batch)
     */
    public function cetakMustahikBatch($pengajuanId)
    {
        $pengajuan = PengajuanBantuan::with([
            'pengajuanMustahiks' => function ($q) {
                $q->with([
                    'mustahik.person',
                    'mustahik.lembaga',
                    'program',
                    'subprogram',
                    'asnaf',
                    'sumberDana',
                    'lampirans',
                    'buktiPenyaluran'
                ]);
            }
        ])->findOrFail($pengajuanId);

        $baznasInfo = $this->getBaznasInfo();
        $pejabat = $this->getPejabatBaznas();

        $items = [];
        foreach ($pengajuan->pengajuanMustahiks as $pm) {
            $items[] = $this->formatMustahikLpjItem($pm, $pengajuan, $pejabat);
        }

        $isUpz = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']);
        $backRoute = $isUpz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';
        $backUrl = route($backRoute, ['pengajuan' => $pengajuan->id, 'tab' => 'lpj']);

        return view('pengajuan.cetak-laporan-lpj-mustahik', [
            'pengajuan'  => $pengajuan,
            'baznasInfo' => $baznasInfo,
            'items'      => $items,
            'backUrl'    => $backUrl,
            'title'      => 'BAST LPJ Batch - ' . ($pengajuan->nomor_pengajuan ?: $pengajuan->id),
        ]);
    }

    /**
     * Upload Berkas LPJ / BAST Perorangan Mustahik
     */
    public function uploadMustahikLpj(Request $request, $pengajuanId, $pmId)
    {
        $pengajuan = PengajuanBantuan::findOrFail($pengajuanId);
        $pm = PengajuanMustahik::where('id', $pmId)->where('pengajuan_id', $pengajuan->id)->firstOrFail();

        $request->validate([
            'file_lpj'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $path = $request->file('file_lpj')->store('lampiran_lpj_mustahik', 'public');
        $namaFile = $request->keterangan ?: ('Berkas BAST / LPJ - ' . ($pm->mustahik?->person?->nama ?? 'Mustahik'));

        $pm->lampirans()->create([
            'name'      => $namaFile,
            'category'  => 'lpj_mustahik',
            'file_path' => $path,
        ]);

        $isUpz = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']);
        $backRoute = $isUpz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';

        return redirect()->route($backRoute, ['pengajuan' => $pengajuan->id, 'tab' => 'lpj'])
            ->with('success', 'Berkas LPJ / BAST untuk ' . ($pm->mustahik?->person?->nama ?? 'mustahik') . ' berhasil diunggah.');
    }

    /**
     * Hapus Berkas LPJ Mustahik
     */
    public function destroyMustahikLpj($pengajuanId, $pmId, $lampiranId)
    {
        $pengajuan = PengajuanBantuan::findOrFail($pengajuanId);
        $pm = PengajuanMustahik::where('id', $pmId)->where('pengajuan_id', $pengajuan->id)->firstOrFail();
        
        $lampiran = $pm->lampirans()->where('id', $lampiranId)->firstOrFail();
        $lampiran->delete();

        $isUpz = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']);
        $backRoute = $isUpz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';

        return redirect()->route($backRoute, ['pengajuan' => $pengajuan->id, 'tab' => 'lpj'])
            ->with('success', 'Berkas LPJ / BAST mustahik berhasil dihapus.');
    }
}
