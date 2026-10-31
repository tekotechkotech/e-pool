<?php

namespace App\Http\Controllers\Simba;

use App\Http\Controllers\Controller;
use App\Models\Muzakki;
use App\Models\SetoranUpz;
use App\Models\SetoranUpzItem;
use App\Models\Upz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SimbaExportController extends Controller
{
    public function exportMuzakki(Request $request)
    {
        $lembagaId = $request->query('lembaga_id');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');
        $isDownload = $request->query('download') === 'csv';

        $query = Muzakki::with(['person', 'upz'])->latest('tanggal_registrasi');

        if ($lembagaId) {
            $query->where('lembaga_id', $lembagaId);
        }

        if ($tanggalMulai) {
            $query->whereDate('tanggal_registrasi', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->whereDate('tanggal_registrasi', '<=', $tanggalSelesai);
        }

        if ($isDownload) {
            $muzakkis = $query->get();
            $filename = 'Export_Simba_Muzakki_' . date('Ymd_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($muzakkis) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

                // Header persis: Template Migrasi Muzaki Perorangan (Sample) - Sheet1.csv
                fputcsv($handle, [
                    'Tanggal Registrasi',
                    'Nama',
                    'NPWP',
                    'NIK',
                    'NIP',
                    'Tempat Lahir',
                    'tanggal lahir',
                    'jenis kelamin',
                    'pekerjaan',
                    'alamat korespondensi',
                    'alamat rumah',
                    'alamat kantor',
                    'telepon',
                    'handphone',
                    'email ',
                    'UPZ',
                    'zakat',
                    'catatan',
                ]);

                foreach ($muzakkis as $m) {
                    $p = $m->person;
                    $tglReg = $m->tanggal_registrasi ? Carbon::parse($m->tanggal_registrasi)->format('d/m/Y') : '';
                    $tglLahir = ($p && $p->tanggal_lahir) ? Carbon::parse($p->tanggal_lahir)->format('d/m/Y') : '';
                    $jk = '';
                    if ($p) {
                        if ($p->jenis_kelamin === 'L') $jk = 'Pria';
                        elseif ($p->jenis_kelamin === 'P') $jk = 'Wanita';
                    }

                    $kodeUpz = $m->upz?->kode_simba ?? '';

                    fputcsv($handle, [
                        $tglReg,
                        $p?->nama ?? '',
                        $m->npwp ?? '',
                        $p?->nik ?? '',
                        $m->nip ?? '',
                        $p?->tempat_lahir ?? '',
                        $tglLahir,
                        $jk,
                        $m->pekerjaan ?? '',
                        $m->alamat_korespondensi ?? '',
                        $p?->alamat ?? '',
                        $m->alamat_kantor ?? '',
                        $p?->telepon ?? '',
                        $p?->handphone ?? '',
                        $p?->email ?? '',
                        $kodeUpz,
                        $m->jenis_zakat ?? '',
                        $m->keterangan ?? '',
                    ]);
                }

                fclose($handle);
            }, 200, $headers);
        }

        $muzakkis = $query->paginate(20)->withQueryString();
        $upzs = Upz::active()->orderBy('nama')->get();

        return view('simba.muzakki', compact(
            'muzakkis',
            'upzs',
            'lembagaId',
            'tanggalMulai',
            'tanggalSelesai'
        ));
    }

    public function exportKasMasuk(Request $request)
    {
        $lembagaId = $request->query('lembaga_id');
        $periode = $request->query('periode');
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');
        $tanggalMulai = $request->query('tanggal_mulai');
        $tanggalSelesai = $request->query('tanggal_selesai');
        $isDownload = $request->query('download') === 'csv';

        $daftarBulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $daftarTahun = range(min((int)date('Y') - 2, 2024), max((int)date('Y') + 4, 2030));

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $bulanKeAngka = array_flip($namaBulan);
        $bulanNama = $bulan ? (is_numeric($bulan) ? ($namaBulan[(int)$bulan] ?? null) : $bulan) : null;
        $bulanNum = $bulan ? (is_numeric($bulan) ? (int)$bulan : ($bulanKeAngka[$bulan] ?? null)) : null;

        $query = SetoranUpzItem::with(['setoranUpz.upz', 'muzakki.person'])
            ->whereHas('setoranUpz', function ($sq) use ($lembagaId, $periode, $bulan, $bulanNama, $bulanNum, $tahun, $tanggalMulai, $tanggalSelesai) {
                if ($lembagaId) {
                    $sq->where('lembaga_id', $lembagaId);
                }
                if ($periode) {
                    $sq->where('periode', 'like', "%{$periode}%");
                }
                if ($bulan) {
                    $sq->where(function ($bq) use ($bulanNama, $bulanNum) {
                        if ($bulanNama) {
                            $bq->where('periode', 'like', "%{$bulanNama}%");
                        }
                        if ($bulanNum) {
                            $bq->orWhereMonth('tanggal_setor', $bulanNum);
                        }
                    });
                }
                if ($tahun && $tahun !== 'semua') {
                    $sq->where(function ($tq) use ($tahun) {
                        $tq->where('periode', 'like', "%{$tahun}%")
                            ->orWhereYear('tanggal_setor', $tahun);
                    });
                }
                if ($tanggalMulai) {
                    $sq->whereDate('tanggal_setor', '>=', $tanggalMulai);
                }
                if ($tanggalSelesai) {
                    $sq->whereDate('tanggal_setor', '<=', $tanggalSelesai);
                }
            })
            ->latest('id');

        if ($isDownload) {
            $items = $query->get();
            $filename = 'Export_Simba_Kas_Masuk_' . date('Ymd_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($items) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

                // Header persis: Template Migrasi Kas Masuk (Sample) - Sheet1.csv
                fputcsv($handle, [
                    'No',
                    'tgl_transaksi',
                    'NPWZ',
                    'nama',
                    'zakat',
                    'zakat fitrah',
                    'infak',
                    'titipan',
                    'Keterangan',
                    'Periode',
                ]);

                $no = 1;
                foreach ($items as $item) {
                    $tgl = $item->setoranUpz?->tanggal_setor
                        ? Carbon::parse($item->setoranUpz->tanggal_setor)->format('d/m/Y')
                        : '';

                    $npwz = $item->npwz ?: ($item->muzakki?->npwz ?? '');
                    $nama = $item->nama ?: ($item->muzakki?->person?->nama ?? '');

                    fputcsv($handle, [
                        $no++,
                        $tgl,
                        $npwz,
                        $nama,
                        $item->nominal_zakat > 0 ? $item->nominal_zakat : '',
                        $item->nominal_fitrah > 0 ? $item->nominal_fitrah : '',
                        $item->nominal_infak > 0 ? $item->nominal_infak : '',
                        $item->nominal_titipan > 0 ? $item->nominal_titipan : '',
                        $item->keterangan ?? '',
                        $item->periode ?? ($item->setoranUpz?->periode ?? ''),
                    ]);
                }

                fclose($handle);
            }, 200, $headers);
        }

        $items = $query->paginate(20)->withQueryString();
        $upzs = Upz::active()->orderBy('nama')->get();

        return view('simba.kas-masuk', compact(
            'items',
            'upzs',
            'lembagaId',
            'periode',
            'bulan',
            'tahun',
            'tanggalMulai',
            'tanggalSelesai',
            'daftarBulan',
            'daftarTahun'
        ));
    }
}
