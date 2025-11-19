<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBantuan;
use App\Models\PengajuanMustahik;
use App\Models\Pengurus;
use App\Helpers\TerbilangHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BuktiPengeluaranController extends Controller
{
    private function getBaznasInfo(): array
    {
        $default = [
            'nama_baznas' => 'BADAN AMIL ZAKAT NASIONAL',
            'kabupaten' => 'CILACAP',
            'alamat' => 'Jl. Masjid No. 19 Sidanegara Cilacap',
            'telepon' => '(0282) 539 0181',
            'email' => 'baznaskab.cilacap@baznas.go.id',
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

        // Cari Ketua / Wakil Ketua II (Setuju Bayar)
        $waka2 = $penguruses->first(function($p) {
            $j = strtolower($p->pengurusJabatan?->nama ?? '');
            return str_contains($j, 'wakil ketua ii') || str_contains($j, 'waka ii') || str_contains($j, 'pendistribusian');
        });
        
        $ketua = $penguruses->first(function($p) {
            $j = strtolower($p->pengurusJabatan?->nama ?? '');
            return $j === 'ketua' || str_contains($j, 'ketua baznas');
        });

        // Cari Bendahara / Wakil Ketua III (Keuangan)
        $waka3 = $penguruses->first(function($p) {
            $j = strtolower($p->pengurusJabatan?->nama ?? '');
            return str_contains($j, 'wakil ketua iii') || str_contains($j, 'waka iii') || str_contains($j, 'keuangan') || str_contains($j, 'bendahara');
        });

        $setujuBayarNama = $waka2?->person?->nama ?? $ketua?->person?->nama ?? 'AKHMAD KHOLIL, SH';
        $setujuBayarJabatan = $waka2 ? 'a.n. Ketua' : ($ketua ? 'Ketua BAZNAS' : 'a.n. Ketua');
        $setujuBayarSubJabatan = $waka2?->pengurusJabatan?->nama ?? 'Wakil Ketua II';

        $bendaharaNama = $waka3?->person?->nama ?? 'KHAKIMATUSODIQOH, S.Pd';
        $bendaharaJabatan = $waka3?->pengurusJabatan?->nama ?? 'Wakil Ketua III';

        return [
            'setuju_bayar_nama' => $setujuBayarNama,
            'setuju_bayar_jabatan' => $setujuBayarJabatan,
            'setuju_bayar_subjabatan' => $setujuBayarSubJabatan,
            'bendahara_nama' => $bendaharaNama,
            'bendahara_jabatan' => $bendaharaJabatan,
        ];
    }

    /**
     * Cetak bukti pengeluaran untuk satu mustahik
     */
    public function cetakSingle($id)
    {
        $pm = PengajuanMustahik::with([
            'pengajuan.lembaga',
            'pengajuan.picLembaga.person',
            'mustahik.person',
            'mustahik.lembaga',
            'asnaf',
            'program',
            'subprogram',
            'sumberDana',
            'disposisi'
        ])->findOrFail($id);

        $pengajuan = $pm->pengajuan;
        $baznasInfo = $this->getBaznasInfo();
        $pejabat = $this->getPejabatBaznas();

        $m = $pm->mustahik;
        $isPerorangan = ($m?->person_id || !$m?->lembaga_id);
        $mPerson = $m?->person;
        $mLembaga = $m?->lembaga;

        $namaPenerima = $isPerorangan 
            ? ($mPerson?->nama ?? $pengajuan->mustahik_nama_snapshot ?? 'Mustahik') 
            : ($mLembaga?->nama ?? $pengajuan->lembaga_nama_snapshot ?? 'Lembaga');

        $alamatPenerima = $isPerorangan
            ? ($mPerson?->alamat ?? $pengajuan->mustahik_alamat_snapshot ?? '')
            : ($mLembaga?->alamat ?? $pengajuan->lembaga_alamat_snapshot ?? '');

        $tanggalPenyaluran = $pm->tanggal_penyaluran ?? now();
        $bulanRomawi = $this->getRomawi((int) $tanggalPenyaluran->format('n'));
        $tahun = $tanggalPenyaluran->format('Y');
        
        $nomorBkk = ($pm->id) . '/BKK-DP/' . $bulanRomawi . '/In/' . $tahun;
        $tanggalFormatted = $tanggalPenyaluran->translatedFormat('d F Y');

        $nominal = (int) ($pm->nominal ?? 0);
        $terbilang = TerbilangHelper::terbilang($nominal);

        $uraian = $pm->keterangan ?: ($pengajuan->judul_surat ?: 'BANTUAN DANA ' . ($pm->program?->nama ?? ''));

        $itemData = [
            'nomor_bkk' => $nomorBkk,
            'tanggal_penyaluran' => $tanggalPenyaluran,
            'tanggal_penyaluran_formatted' => $tanggalFormatted,
            'mustahik_nama' => $namaPenerima,
            'mustahik_alamat' => $alamatPenerima,
            'is_uang' => $pm->metode_penyaluran !== 'Barang',
            'nominal' => $nominal,
            'terbilang' => $terbilang,
            'program_nama' => $pm->program?->nama ?? '',
            'subprogram_nama' => $pm->subprogram?->nama ?? '',
            'uraian' => $uraian,
            'asnaf_nama' => $pm->asnaf?->nama ?? '',
            'sumber_dana_nama' => $pm->sumberDana?->nama ?? '',
            'catatan' => $pm->catatan_penyaluran ?? '',
            'pejabat_setuju_nama' => $pejabat['setuju_bayar_nama'],
            'pejabat_setuju_jabatan' => $pejabat['setuju_bayar_jabatan'],
            'pejabat_setuju_subjabatan' => $pejabat['setuju_bayar_subjabatan'],
            'bendahara_nama' => $pejabat['bendahara_nama'],
            'bendahara_jabatan' => $pejabat['bendahara_jabatan'],
            'penerima_nama' => $namaPenerima,
        ];

        $isUpz = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']);
        $backRoute = $isUpz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';
        $backUrl = route($backRoute, ['pengajuan' => $pengajuan->id, 'tab' => 'penyaluran']);

        return view('pengajuan.cetak-bukti-pengeluaran', [
            'baznasInfo' => $baznasInfo,
            'mustahikNama' => $namaPenerima,
            'backUrl' => $backUrl,
            'items' => [$itemData],
        ]);
    }

    /**
     * Cetak bukti pengeluaran untuk semua mustahik dalam satu pengajuan
     */
    public function cetakBatch($pengajuanId)
    {
        $pengajuan = PengajuanBantuan::with([
            'pengajuanMustahiks' => function($q) {
                $q->with([
                    'mustahik.person',
                    'mustahik.lembaga',
                    'asnaf',
                    'program',
                    'subprogram',
                    'sumberDana',
                    'disposisi'
                ]);
            }
        ])->findOrFail($pengajuanId);

        $baznasInfo = $this->getBaznasInfo();
        $pejabat = $this->getPejabatBaznas();

        $items = [];
        $eligibleList = $pengajuan->pengajuanMustahiks->filter(function($pm) {
            return !is_null($pm->tanggal_penyaluran) || (($pm->nominal ?? 0) > 0 && (!$pm->disposisi || !in_array($pm->disposisi->instruksi, ['Tolak', 'Ditolak'])));
        });

        foreach ($eligibleList as $pm) {
            $m = $pm->mustahik;
            $isPerorangan = ($m?->person_id || !$m?->lembaga_id);
            $mPerson = $m?->person;
            $mLembaga = $m?->lembaga;

            $namaPenerima = $isPerorangan 
                ? ($mPerson?->nama ?? $pengajuan->mustahik_nama_snapshot ?? 'Mustahik') 
                : ($mLembaga?->nama ?? $pengajuan->lembaga_nama_snapshot ?? 'Lembaga');

            $alamatPenerima = $isPerorangan
                ? ($mPerson?->alamat ?? $pengajuan->mustahik_alamat_snapshot ?? '')
                : ($mLembaga?->alamat ?? $pengajuan->lembaga_alamat_snapshot ?? '');

            $tanggalPenyaluran = $pm->tanggal_penyaluran ?? now();
            $bulanRomawi = $this->getRomawi((int) $tanggalPenyaluran->format('n'));
            $tahun = $tanggalPenyaluran->format('Y');
            
            $nomorBkk = ' ' . ' ' . ' /BKK-DP/' . $bulanRomawi . '/In/' . $tahun;
            $tanggalFormatted = $tanggalPenyaluran->translatedFormat('F Y');

            $nominal = (int) ($pm->nominal ?? 0);
            $terbilang = TerbilangHelper::terbilang($nominal);

            $uraian = $pm->keterangan ?: ($pengajuan->judul_surat ?: 'BANTUAN DANA ' . ($pm->program?->nama ?? ''));

            $items[] = [
                'nomor_bkk' => $nomorBkk,
                'tanggal_penyaluran' => $tanggalPenyaluran,
                'tanggal_penyaluran_formatted' => $tanggalFormatted,
                'mustahik_nama' => $namaPenerima,
                'mustahik_alamat' => $alamatPenerima,
                'is_uang' => $pm->metode_penyaluran !== 'Barang',
                'nominal' => $nominal,
                'terbilang' => $terbilang,
                'program_nama' => $pm->program?->nama ?? '',
                'subprogram_nama' => $pm->subprogram?->nama ?? '',
                'uraian' => $uraian,
                'asnaf_nama' => $pm->asnaf?->nama ?? '',
                'sumber_dana_nama' => $pm->sumberDana?->nama ?? '',
                'catatan' => $pm->catatan_penyaluran ?? '',
                'pejabat_setuju_nama' => $pejabat['setuju_bayar_nama'],
                'pejabat_setuju_jabatan' => $pejabat['setuju_bayar_jabatan'],
                'pejabat_setuju_subjabatan' => $pejabat['setuju_bayar_subjabatan'],
                'bendahara_nama' => $pejabat['bendahara_nama'],
                'bendahara_jabatan' => $pejabat['bendahara_jabatan'],
                'penerima_nama' => $namaPenerima,
            ];
        }

        $isUpz = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']);
        $backRoute = $isUpz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';
        $backUrl = route($backRoute, ['pengajuan' => $pengajuan->id, 'tab' => 'penyaluran']);

        return view('pengajuan.cetak-bukti-pengeluaran', [
            'baznasInfo' => $baznasInfo,
            'mustahikNama' => 'Batch_' . $pengajuan->nomor_pengajuan,
            'backUrl' => $backUrl,
            'items' => $items,
        ]);
    }

    private function getRomawi(int $bulan): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $map[$bulan] ?? 'I';
    }
}
