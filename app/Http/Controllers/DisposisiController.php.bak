<?php

namespace App\Http\Controllers;

use App\Models\PengajuanBantuan;
use App\Models\Pengurus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DisposisiController extends Controller
{
    private function getBaznasInfo(): array
    {
        $default = [
            'nama_baznas' => 'BADAN AMIL ZAKAT NASIONAL',
            'kabupaten' => 'CILACAP',
            'alamat' => 'Jl. Masjid No. 19',
            'telepon' => '(0282) 539 0181',
            'kode_pos' => '53223 Cilacap',
            'email' => 'baznaskab.cilacap@baznas.go.id',
        ];

        if (Storage::disk('local')->exists('baznas_info.json')) {
            $data = json_decode(Storage::disk('local')->get('baznas_info.json'), true);
            return array_merge($default, $data ?: []);
        }

        return $default;
    }

    /**
     * Cetak Lembar Disposisi untuk satu Pengajuan Bantuan
     */
    public function cetak($id)
    {
        $pengajuan = PengajuanBantuan::with([
            'lembaga',
            'upz',
            'mustahik.person',
            'picUpz.person',
            'pengajuanMustahiks.disposisi.pimpinan',
            'pengajuanMustahiks.mustahik.person',
            'pengajuanMustahiks.mustahik.lembaga',
            'pengajuanMustahiks.program',
            'pengajuanMustahiks.subprogram',
            'persetujuan.approvedBy',
        ])->findOrFail($id);

        $baznasInfo = $this->getBaznasInfo();

        // 1. Surat dari (Nama Pemohon / Lembaga / Instansi)
        $isLembaga = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']) || !empty($pengajuan->lembaga_id);
        if ($isLembaga) {
            $suratDari = $pengajuan->upz?->nama 
                ?? $pengajuan->lembaga?->nama 
                ?? $pengajuan->lembaga_nama_snapshot 
                ?? '-';
        } else {
            $suratDari = $pengajuan->mustahik?->person?->nama 
                ?? $pengajuan->mustahik_nama_snapshot 
                ?? '-';
        }

        // 2. Nomor Surat & No Agenda
        $nomorSurat = $pengajuan->nomor_surat ?: '-';
        $nomorAgenda = $pengajuan->nomor_pengajuan ?: '-';

        // 3. Tanggal Surat & Tanggal Diterima
        $tglSurat = $pengajuan->tanggal_surat 
            ? $pengajuan->tanggal_surat->format('d/m/Y') 
            : ($pengajuan->created_at ? $pengajuan->created_at->format('d/m/Y') : '-');

        $tglDiterima = $pengajuan->tanggal_diterima 
            ? $pengajuan->tanggal_diterima->format('d/m/Y') 
            : ($pengajuan->created_at ? $pengajuan->created_at->format('d/m/Y') : '-');

        // 4. Perihal
        $perihal = $pengajuan->judul_surat 
            ?: ($pengajuan->uraian_permohonan ?: 'Permohonan Bantuan Dana');

        // 5. Data Disposisi & Checklist
        $disposisis = $pengajuan->pengajuanMustahiks->pluck('disposisi')->filter();
        $firstDisposisi = $disposisis->first();

        // Instruksi & catatan pimpinan
        $instruksiList = $disposisis->pluck('instruksi')->unique()->values()->all();
        $catatanList = $disposisis->pluck('catatan')->filter()->unique()->values()->all();
        if (empty($catatanList) && $pengajuan->persetujuan?->catatan) {
            $catatanList[] = $pengajuan->persetujuan->catatan;
        }

        // Diteruskan Kepada (Default Yth. Wakil Ketua II)
        $diteruskanKepadaRaw = $firstDisposisi?->diteruskan_kepada ?: 'Wakil Ketua II';
        $diteruskanKepada = str_starts_with(strtolower($diteruskanKepadaRaw), 'yth') 
            ? $diteruskanKepadaRaw 
            : 'Yth. ' . $diteruskanKepadaRaw;

        // Checklist Isi Disposisi items
        $checklistItems = [
            'Untuk diselesaikan',
            'Untuk dikoordinasikan',
            'Untuk ditindaklanjuti',
            'Untuk diketahui',
            'Catat',
            'Wakili',
        ];

        // Ambil dari isi_disposisi jika ada di database, atau mapping otomatis
        $savedIsiDisposisi = $disposisis->pluck('isi_disposisi')->filter()->flatten()->unique()->values()->all();
        if (!empty($savedIsiDisposisi)) {
            $checkedItems = $savedIsiDisposisi;
        } else {
            $checkedItems = [];
            foreach ($instruksiList as $inst) {
                $instLower = strtolower($inst);
                if (str_contains($instLower, 'selesai') || str_contains($instLower, 'proses langsung')) {
                    $checkedItems[] = 'Untuk diselesaikan';
                }
                if (str_contains($instLower, 'koordinasi') || str_contains($instLower, 'revisi')) {
                    $checkedItems[] = 'Untuk dikoordinasikan';
                }
                if (str_contains($instLower, 'tindak') || str_contains($instLower, 'survey')) {
                    $checkedItems[] = 'Untuk ditindaklanjuti';
                }
                if (str_contains($instLower, 'ketahui') || str_contains($instLower, 'tolak')) {
                    $checkedItems[] = 'Untuk diketahui';
                }
                if (str_contains($instLower, 'catat')) {
                    $checkedItems[] = 'Catat';
                }
                if (str_contains($instLower, 'wakil')) {
                    $checkedItems[] = 'Wakili';
                }
            }
        }

        $isUpz = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']);
        $backRoute = $isUpz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';
        $backUrl = route($backRoute, ['pengajuan' => $pengajuan->id, 'tab' => 'persetujuan']);

        return view('pengajuan.cetak-lembar-disposisi', [
            'pengajuan' => $pengajuan,
            'baznasInfo' => $baznasInfo,
            'suratDari' => $suratDari,
            'nomorSurat' => $nomorSurat,
            'nomorAgenda' => $nomorAgenda,
            'tglSurat' => $tglSurat,
            'tglDiterima' => $tglDiterima,
            'perihal' => $perihal,
            'diteruskanKepada' => $diteruskanKepada,
            'checklistItems' => $checklistItems,
            'checkedItems' => $checkedItems,
            'catatan' => implode("\n", $catatanList),
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Cetak Form Pengajuan - Penyaluran ZIS berdasarkan Disposisi
     */
    public function cetakFormPenyaluran($id)
    {
        $pengajuan = PengajuanBantuan::with([
            'lembaga',
            'upz',
            'mustahik.person',
            'picUpz.person',
            'pengajuanMustahiks.disposisi.pimpinan',
            'pengajuanMustahiks.mustahik.person',
            'pengajuanMustahiks.mustahik.lembaga',
            'pengajuanMustahiks.sumberDana',
            'pengajuanMustahiks.program',
            'pengajuanMustahiks.subprogram',
            'persetujuan.approvedBy',
        ])->findOrFail($id);

        $baznasInfo = $this->getBaznasInfo();
        $pejabat = $this->getPejabatFormPenyaluran();

        // Ambil item mustahik yang valid didisposisi (tidak ditolak & nominal > 0 atau berupa barang)
        $eligibleList = $pengajuan->pengajuanMustahiks->filter(function($pm) {
            return (!$pm->disposisi || !in_array($pm->disposisi->instruksi, ['Tolak', 'Ditolak'])) && (($pm->nominal ?? 0) > 0 || $pm->bentuk_bantuan === 'Barang');
        });

        // Fallback jika belum ada disposisi nominal
        if ($eligibleList->isEmpty()) {
            $eligibleList = $pengajuan->pengajuanMustahiks;
        }

        $tanggalDokumen = $pengajuan->tanggal_surat 
            ?? $pengajuan->tanggal_diterima 
            ?? $pengajuan->created_at 
            ?? Carbon::now();

        $bulanRomawi = $this->getRomawi((int) $tanggalDokumen->format('n'));
        $tahun = $tanggalDokumen->format('Y');

        // Nomor Formulir Pengajuan Penyaluran ZIS
        $nomorUrut = str_pad($pengajuan->id, 2, '0', STR_PAD_LEFT);
        $nomorForm = $nomorUrut . '/PP-POOL/' . $bulanRomawi . '/' . $tahun;

        $items = [];
        $totalNominal = 0;

        foreach ($eligibleList as $index => $pm) {
            $sumberDana = $pm->sumberDana;
            $namaDana = $sumberDana?->nama ?? '';
            $kategoriDana = $sumberDana?->kategori ?? '';
            
            // Tentukan Alokasi Dana: Zakat / Infak / dll
            if (str_contains(strtolower($namaDana), 'zakat') || str_contains(strtolower($kategoriDana), 'zakat')) {
                $alokasiDana = 'Zakat';
            } elseif (str_contains(strtolower($namaDana), 'infak') || str_contains(strtolower($kategoriDana), 'infak')) {
                $alokasiDana = 'Infak';
            } elseif (str_contains(strtolower($namaDana), 'sedekah') || str_contains(strtolower($kategoriDana), 'sedekah')) {
                $alokasiDana = 'Sedekah';
            } else {
                $alokasiDana = $namaDana ?: 'Zakat';
            }

            // Uraian kegiatan / program / mustahik
            $m = $pm->mustahik;
            $isPerorangan = ($m?->person_id || !$m?->lembaga_id);
            $namaPenerima = $isPerorangan 
                ? ($m?->person?->nama ?? $pengajuan->mustahik_nama_snapshot ?? '') 
                : ($m?->lembaga?->nama ?? $pengajuan->lembaga_nama_snapshot ?? '');

            if ($pm->keterangan) {
                $uraian = $pm->keterangan;
            } elseif ($pm->subprogram?->nama) {
                $uraian = $pm->program?->nama ? ($pm->program->nama . ' - ' . $pm->subprogram->nama) : $pm->subprogram->nama;
            } elseif ($pm->program?->nama) {
                $uraian = $pm->program->nama;
            } elseif ($namaPenerima) {
                $uraian = 'Bantuan untuk ' . $namaPenerima;
            } else {
                $uraian = $pengajuan->judul_surat ?: 'Bantuan Penyaluran ZIS';
            }

            if ($pm->bentuk_bantuan === 'Barang') {
                $detailBarang = trim(($pm->jumlah_barang ? $pm->jumlah_barang . ' ' . $pm->satuan_barang : '') . ($pm->nama_barang ? ' ' . $pm->nama_barang : ''));
                if ($detailBarang) {
                    $uraian .= ' [Natura/Barang: ' . $detailBarang . ']';
                }
            }

            $nominal = (int) ($pm->nominal ?? 0);
            $totalNominal += $nominal;

            $items[] = [
                'no' => $index + 1,
                'alokasi_dana' => $alokasiDana,
                'uraian' => $uraian,
                'tanggal' => $tanggalDokumen->format('d/m/Y'),
                'nominal' => $nominal,
            ];
        }

        $terbilang = \App\Helpers\TerbilangHelper::terbilang($totalNominal);
        $terbilangFormatted = strtolower($terbilang);

        $isUpz = in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']);
        $backRoute = $isUpz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';
        $backUrl = route($backRoute, ['pengajuan' => $pengajuan->id, 'tab' => 'persetujuan']);

        return view('pengajuan.cetak-form-penyaluran', [
            'pengajuan' => $pengajuan,
            'baznasInfo' => $baznasInfo,
            'nomorForm' => $nomorForm,
            'tanggalDokumen' => $tanggalDokumen,
            'tanggalFormatted' => $tanggalDokumen->format('j/n/Y'),
            'totalNominal' => $totalNominal,
            'terbilang' => $terbilangFormatted,
            'items' => $items,
            'pejabat' => $pejabat,
            'backUrl' => $backUrl,
        ]);
    }

    private function getPejabatFormPenyaluran(): array
    {
        $penguruses = Pengurus::with(['person', 'pengurusJabatan'])
            ->where('jenis_pengurus', 'Baznas')
            ->where('is_active', true)
            ->get();

        // 1. Staf Waka II (Dzulfikri Hidayat)
        $stafWk2 = $penguruses->first(function ($p) {
            $j = strtolower($p->pengurusJabatan?->nama ?? '');
            $nama = strtolower($p->person?->nama ?? '');
            return str_contains($nama, 'dzulfikri') || str_contains($nama, 'fikri') || (str_contains($j, 'staf') && (str_contains($j, 'ii') || str_contains($j, 'distribusi')));
        });

        // 2. Pelaksana Waka II (Cili Wibowo)
        $pelaksanaWk2 = $penguruses->first(function ($p) {
            $j = strtolower($p->pengurusJabatan?->nama ?? '');
            $nama = strtolower($p->person?->nama ?? '');
            return str_contains($nama, 'cili') || (str_contains($j, 'pelaksana') && (str_contains($j, 'ii') || str_contains($j, 'distribusi')));
        });

        // 3. Wakil Ketua II (Akhmad Kholil, SH)
        $waka2 = $penguruses->first(function ($p) {
            $j = strtolower($p->pengurusJabatan?->nama ?? '');
            $nama = strtolower($p->person?->nama ?? '');
            return str_contains($nama, 'kholil') || str_contains($j, 'wakil ketua ii') || str_contains($j, 'waka ii') || str_contains($j, 'pendistribusian');
        });

        // 4. Wakil Ketua III (Khakimatusodiqoh, S.Pd)
        $waka3 = $penguruses->first(function ($p) {
            $j = strtolower($p->pengurusJabatan?->nama ?? '');
            $nama = strtolower($p->person?->nama ?? '');
            return str_contains($nama, 'khakimat') || str_contains($j, 'wakil ketua iii') || str_contains($j, 'waka iii') || str_contains($j, 'keuangan') || str_contains($j, 'bendahara');
        });

        return [
            'staf_nama' => $stafWk2?->person?->nama ?? 'Dzulfikri Hidayat',
            'staf_jabatan' => 'Staf Waka II',
            'pelaksana_nama' => $pelaksanaWk2?->person?->nama ?? 'Cili Wibowo',
            'pelaksana_jabatan' => 'Pelaksana Waka II',
            'waka2_nama' => $waka2?->person?->nama ?? 'Akhmad Kholil, SH',
            'waka2_jabatan' => 'Wakil Ketua II',
            'waka3_nama' => $waka3?->person?->nama ?? 'Khakimatusodiqoh, S.Pd',
            'waka3_jabatan' => 'Wakil Ketua III',
        ];
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
