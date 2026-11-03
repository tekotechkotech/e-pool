<?php

namespace App\Http\Controllers\Pengumpulan;

use App\Exports\SetoranUpzTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\SetoranUpzImport;
use App\Models\Dana;
use App\Models\DanaTransaksi;
use App\Models\Muzakki;
use App\Models\Person;
use App\Models\SetoranUpz;
use App\Models\SetoranUpzItem;
use App\Models\Upz;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SetoranUpzController extends Controller
{
    private function generateNomorSetoran(Carbon $tanggal): string
    {
        $tahun = $tanggal->format('Y');
        $bulan = $tanggal->format('m');

        $lastSetoran = SetoranUpz::whereYear('tanggal_setor', $tahun)
            ->whereMonth('tanggal_setor', $bulan)
            ->orderBy('id', 'desc')
            ->first();

        $increment = 1;
        if ($lastSetoran && $lastSetoran->nomor_setoran) {
            $parts = explode('/', $lastSetoran->nomor_setoran);
            $lastNum = end($parts);
            if (is_numeric($lastNum)) {
                $increment = (int)$lastNum + 1;
            }
        }

        return sprintf("SET-UPZ/%s/%s/%04d", $tahun, $bulan, $increment);
    }

    private function parseNominal(mixed $value): int
    {
        if (is_int($value)) {
            return max(0, $value);
        }

        if (is_float($value)) {
            return max(0, (int) round($value));
        }

        if (is_string($value)) {
            $cleaned = trim($value);
            // Hapus prefix Rp atau non-angka kecuali koma dan titik
            $cleaned = preg_replace('/[^\d,\.]/', '', $cleaned);
            if ($cleaned === '') {
                return 0;
            }

            if (str_contains($cleaned, '.') && str_contains($cleaned, ',')) {
                if (strrpos($cleaned, ',') > strrpos($cleaned, '.')) {
                    // Indonesian: 1.500.000,00
                    $parts = explode(',', $cleaned);
                    return max(0, (int) str_replace('.', '', $parts[0]));
                } else {
                    // US: 1,500,000.00
                    $parts = explode('.', $cleaned);
                    return max(0, (int) str_replace(',', '', $parts[0]));
                }
            } elseif (str_contains($cleaned, '.')) {
                if (preg_match('/\.\d{3}(\.|$)/', $cleaned)) {
                    // Indonesian thousands: e.g. 50.000 atau 1.500.000
                    return max(0, (int) str_replace('.', '', $cleaned));
                } else {
                    return max(0, (int) round((float) $cleaned));
                }
            } elseif (str_contains($cleaned, ',')) {
                if (preg_match('/,\d{3}(,|$)/', $cleaned)) {
                    return max(0, (int) str_replace(',', '', $cleaned));
                } else {
                    $parts = explode(',', $cleaned);
                    return max(0, (int) $parts[0]);
                }
            }

            return max(0, (int) $cleaned);
        }

        return 0;
    }

    private function isVerifierEligible(): bool
    {
        $user = Auth::user();
        return (bool) ($user && ($user->isBaznasRole() || $user->hasBaznasAppointment() || $user->isSuperuser()));
    }

    private function authorizeBaznas(): void
    {
        if (!$this->isVerifierEligible()) {
            abort(403, 'Akses ditolak. Tindakan ini hanya dapat dilakukan oleh pihak BAZNAS.');
        }
    }

    private function syncDanaTransaksi(SetoranUpz $setoran): void
    {
        // Pastikan tidak ada duplikasi: bersihkan pencatatan lama untuk referensi setoran ini
        DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->delete();

        // Kas masuk hanya dicatat apabila status Terverifikasi
        if ($setoran->status !== 'Terverifikasi') {
            return;
        }

        $danaZakat = Dana::where('kode', '2.2')->first();
        $danaInfak = Dana::where('kode', '2.7')->first();
        $danaFitrah = Dana::where('kode', '2.5')->first();
        $danaTitipan = Dana::where('kode', '2.18')->first();
        $upzNama = $setoran->upz?->nama ?? 'UPZ';

        if ($setoran->total_zakat > 0 && $danaZakat) {
            DanaTransaksi::create([
                'tanggal' => $setoran->tanggal_setor,
                'dana_id' => $danaZakat->id,
                'jenis' => 'Masuk',
                'nominal' => $setoran->total_zakat,
                'uraian' => "Penerimaan Zakat via {$upzNama} ({$setoran->nomor_setoran}) - Periode {$setoran->periode}",
                'referensi_type' => SetoranUpz::class,
                'referensi_id' => $setoran->id,
            ]);
        }

        if ($setoran->total_infak > 0 && $danaInfak) {
            DanaTransaksi::create([
                'tanggal' => $setoran->tanggal_setor,
                'dana_id' => $danaInfak->id,
                'jenis' => 'Masuk',
                'nominal' => $setoran->total_infak,
                'uraian' => "Penerimaan Infak via {$upzNama} ({$setoran->nomor_setoran}) - Periode {$setoran->periode}",
                'referensi_type' => SetoranUpz::class,
                'referensi_id' => $setoran->id,
            ]);
        }

        if ($setoran->total_fitrah > 0 && $danaFitrah) {
            DanaTransaksi::create([
                'tanggal' => $setoran->tanggal_setor,
                'dana_id' => $danaFitrah->id,
                'jenis' => 'Masuk',
                'nominal' => $setoran->total_fitrah,
                'uraian' => "Penerimaan Zakat Fitrah via {$upzNama} ({$setoran->nomor_setoran}) - Periode {$setoran->periode}",
                'referensi_type' => SetoranUpz::class,
                'referensi_id' => $setoran->id,
            ]);
        }

        if ($setoran->total_titipan > 0 && $danaTitipan) {
            DanaTransaksi::create([
                'tanggal' => $setoran->tanggal_setor,
                'dana_id' => $danaTitipan->id,
                'jenis' => 'Masuk',
                'nominal' => $setoran->total_titipan,
                'uraian' => "Penerimaan Dana Titipan via {$upzNama} ({$setoran->nomor_setoran}) - Periode {$setoran->periode}",
                'referensi_type' => SetoranUpz::class,
                'referensi_id' => $setoran->id,
            ]);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $isUpz = (bool) ($user && $user->isUpzRole());
        $lembagaId = $isUpz ? $user->upzLembagaId() : $request->query('lembaga_id');
        $periode = $request->query('periode');
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');
        $status = $request->query('status');
        $search = $request->query('search');

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

        $query = SetoranUpz::with(['upz', 'user', 'verifiedBy.person'])->latest('tanggal_setor');

        if ($isUpz) {
            $query->where('lembaga_id', $user->upzLembagaId());
        } elseif ($lembagaId) {
            $query->where('lembaga_id', $lembagaId);
        }

        if ($periode) {
            $query->where('periode', 'like', "%{$periode}%");
        }

        if ($bulan) {
            $bulanNama = is_numeric($bulan) ? ($namaBulan[(int)$bulan] ?? null) : $bulan;
            $bulanNum = is_numeric($bulan) ? (int)$bulan : ($bulanKeAngka[$bulan] ?? null);

            $query->where(function ($q) use ($bulanNama, $bulanNum) {
                if ($bulanNama) {
                    $q->where('periode', 'like', "%{$bulanNama}%");
                }
                if ($bulanNum) {
                    $q->orWhereMonth('tanggal_setor', $bulanNum);
                }
            });
        }

        if ($tahun && $tahun !== 'semua') {
            $query->where(function ($q) use ($tahun) {
                $q->where('periode', 'like', "%{$tahun}%")
                    ->orWhereYear('tanggal_setor', $tahun);
            });
        }

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_setoran', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('upz', function ($uq) use ($search) {
                        $uq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $setorans = $query->paginate(15)->withQueryString();
        $upzs = $isUpz ? Upz::where('id', $user->upzLembagaId())->get() : Upz::active()->orderBy('nama')->get();

        return view('pengumpulan.upz.index', compact(
            'setorans',
            'upzs',
            'lembagaId',
            'periode',
            'bulan',
            'tahun',
            'status',
            'search',
            'daftarBulan',
            'daftarTahun'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        $isUpzUser = $user?->isUpzRole() ?? false;
        $lockedUpz = null;

        if ($isUpzUser) {
            $upzId = $user->upzLembagaId();
            $lockedUpz = $upzId ? Upz::find($upzId) : null;
            $upzs = $lockedUpz ? collect([$lockedUpz]) : collect();
            $muzakkis = $lockedUpz ? Muzakki::with('person')->where('lembaga_id', $lockedUpz->id)->get() : collect();
        } else {
            $upzs = Upz::active()->orderBy('nama')->get();
            $muzakkis = Muzakki::with('person')->get();
        }

        $daftarBulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $daftarTahun = range(min((int)date('Y') - 2, 2024), max((int)date('Y') + 4, 2030));

        return view('pengumpulan.upz.create', compact('upzs', 'muzakkis', 'daftarBulan', 'daftarTahun', 'isUpzUser', 'lockedUpz'));
    }

    public function getMuzakkiByUpz($lembagaId)
    {
        $query = Muzakki::with('person');

        if (!empty($lembagaId) && $lembagaId !== 'all') {
            $query->where('lembaga_id', $lembagaId);
        }

        $muzakkis = $query->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'npwz' => $m->npwz ?? '',
                'nama' => $m->person?->nama ?? '',
                'nik' => $m->person?->nik ?? '',
            ];
        });

        return response()->json($muzakkis);
    }

    public function searchUpz(Request $request)
    {
        $q = $request->query('q', '');
        $charCount = strlen(preg_replace('/\s+/', '', (string)$q));

        if ($charCount < 4) {
            return response()->json([]);
        }

        $results = Upz::active()
            ->where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('kode_simba', 'like', "%{$q}%");
            })
            ->take(20)
            ->get(['id', 'nama', 'kode_simba']);

        return response()->json($results);
    }

    public function parseInlineExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'lembaga_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $lembagaId = $request->input('lembaga_id');
        $uploadedFile = $request->file('file');

        try {
            $sheets = Excel::toArray(new SetoranUpzImport, $uploadedFile);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file spreadsheet: ' . $e->getMessage(),
            ], 422);
        }

        $rows = $sheets[0] ?? [];

        // Preload muzakki aktif untuk matching cepat (berdasarkan id, npwz, nik)
        $muzakkiQuery = Muzakki::with('person');
        if (!empty($lembagaId) && $lembagaId !== 'all') {
            $muzakkiQuery->where('lembaga_id', $lembagaId);
        }
        $allMuzakkis = $muzakkiQuery->get();

        $byId = [];
        $byNpwz = [];
        $byNik = [];

        foreach ($allMuzakkis as $m) {
            $byId[$m->id] = $m;
            if (!empty($m->npwz)) {
                $normNpwz = strtolower(preg_replace('/\s+/', '', (string)$m->npwz));
                $byNpwz[$normNpwz] = $m;
            }
            if ($m->person && !empty($m->person->nik)) {
                $normNik = preg_replace('/\s+/', '', (string)$m->person->nik);
                $byNik[$normNik] = $m;
            }
        }

        $items = [];

        foreach ($rows as $row) {
            // Dukung pembacaan berbasis slug heading row maupun key alternatif
            $rawId = $row['id_muzakki'] ?? $row['id'] ?? $row['id_muzaki'] ?? null;
            $rawNpwz = $row['npwz'] ?? null;
            $rawNama = $row['nama_lengkap'] ?? $row['nama'] ?? null;
            $rawNik = $row['nik'] ?? null;
            $rawZakat = $row['zakat_rp'] ?? $row['zakat'] ?? $row['nominal_zakat'] ?? 0;
            $rawInfak = $row['infak_rp'] ?? $row['infak'] ?? $row['nominal_infak'] ?? 0;
            $rawFitrah = $row['fitrah_rp'] ?? $row['fitrah'] ?? $row['nominal_fitrah'] ?? 0;
            $rawTitipan = $row['titipan_rp'] ?? $row['titipan'] ?? $row['nominal_titipan'] ?? 0;
            $rawKet = $row['keterangan'] ?? null;

            $cleanId = is_numeric($rawId) ? (int)$rawId : null;
            $cleanNpwz = is_string($rawNpwz) ? trim($rawNpwz) : ($rawNpwz ? (string)$rawNpwz : '');
            $cleanNama = is_string($rawNama) ? trim($rawNama) : ($rawNama ? (string)$rawNama : '');
            $cleanNik = is_string($rawNik) ? trim($rawNik) : ($rawNik ? (string)$rawNik : '');
            $cleanKet = is_string($rawKet) ? trim($rawKet) : ($rawKet ? (string)$rawKet : '');

            $nominalZakat = $this->parseNominal($rawZakat);
            $nominalInfak = $this->parseNominal($rawInfak);
            $nominalFitrah = $this->parseNominal($rawFitrah);
            $nominalTitipan = $this->parseNominal($rawTitipan);
            $totalBaris = $nominalZakat + $nominalInfak + $nominalFitrah + $nominalTitipan;

            // Lewati baris kosong cadangan jika seluruh kolom identitas dan nominal 0/kosong
            if (empty($cleanId) && empty($cleanNpwz) && empty($cleanNama) && empty($cleanNik) && $totalBaris === 0) {
                continue;
            }

            // Pencocokan Muzakki: ID -> NPWZ -> NIK
            $matchedMuzakki = null;
            if ($cleanId && isset($byId[$cleanId])) {
                $matchedMuzakki = $byId[$cleanId];
            } elseif (!empty($cleanNpwz)) {
                $searchNpwz = strtolower(preg_replace('/\s+/', '', $cleanNpwz));
                if (isset($byNpwz[$searchNpwz])) {
                    $matchedMuzakki = $byNpwz[$searchNpwz];
                }
            }

            if (!$matchedMuzakki && !empty($cleanNik)) {
                $searchNik = preg_replace('/\s+/', '', $cleanNik);
                if (isset($byNik[$searchNik])) {
                    $matchedMuzakki = $byNik[$searchNik];
                }
            }

            // Klasifikasi status: terdaftar, nihil, atau belum_terdaftar
            if ($matchedMuzakki) {
                if (empty($cleanNama)) {
                    $cleanNama = $matchedMuzakki->person?->nama ?? '';
                }
                if (empty($cleanNik)) {
                    $cleanNik = $matchedMuzakki->person?->nik ?? '';
                }
                if (empty($cleanNpwz)) {
                    $cleanNpwz = $matchedMuzakki->npwz ?? '';
                }

                $status = ($totalBaris > 0) ? 'terdaftar' : 'nihil';
            } else {
                $status = 'belum_terdaftar';
            }

            $items[] = [
                'row_number' => count($items) + 1,
                'muzakki_id' => $matchedMuzakki?->id ?? null,
                'nama' => $cleanNama ?: '-',
                'nik' => $cleanNik,
                'npwz' => $cleanNpwz,
                'nominal_zakat' => $nominalZakat,
                'nominal_infak' => $nominalInfak,
                'nominal_fitrah' => $nominalFitrah,
                'nominal_titipan' => $nominalTitipan,
                'total' => $totalBaris,
                'keterangan' => $cleanKet,
                'status' => $status,
            ];
        }

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'File spreadsheet tidak berisi baris data setoran yang dapat diproses.',
                'items' => [],
                'total_rows' => 0,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'items' => $items,
            'total_rows' => count($items),
        ]);
    }

    public function downloadTemplate($lembagaId = null)
    {
        $targetLembagaId = $lembagaId ?: request('lembaga_id');
        $targetLembagaId = (!empty($targetLembagaId) && $targetLembagaId !== 'all') ? (int)$targetLembagaId : null;

        $upzName = 'all';
        if ($targetLembagaId) {
            $upz = Upz::find($targetLembagaId);
            if ($upz) {
                $upzName = Str::slug($upz->nama, '_');
            }
        }

        $periode = request('periode') ? Str::slug(request('periode'), '_') : date('Y_m');
        $fileName = "template_setoran_upz_{$upzName}_{$periode}.xlsx";

        return Excel::download(new SetoranUpzTemplateExport($targetLembagaId), $fileName);
    }

    public function importPreview(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'lembaga_id' => 'required|exists:lembagas,id',
            'tanggal_setor' => 'required|date',
            'bulan' => 'required|string|max:50',
            'tahun' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        $periode = trim($validated['bulan'] . ' ' . $validated['tahun']);
        $upz = Upz::findOrFail($validated['lembaga_id']);
        $uploadedFile = $request->file('file');

        try {
            $sheets = Excel::toArray(new SetoranUpzImport, $uploadedFile);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Gagal membaca file spreadsheet: ' . $e->getMessage()]);
        }

        $rows = $sheets[0] ?? [];

        // Preload seluruh muzakki untuk pencocokan cepat tanpa N+1 query
        $allMuzakkis = Muzakki::with('person')->get();
        $byId = [];
        $byNpwz = [];
        $byNik = [];

        foreach ($allMuzakkis as $m) {
            $byId[$m->id] = $m;
            if (!empty($m->npwz)) {
                $normNpwz = strtolower(preg_replace('/\s+/', '', (string)$m->npwz));
                $byNpwz[$normNpwz] = $m;
            }
            if ($m->person && !empty($m->person->nik)) {
                $normNik = preg_replace('/\s+/', '', (string)$m->person->nik);
                $byNik[$normNik] = $m;
            }
        }

        $items = [];

        foreach ($rows as $row) {
            // Dukung pembacaan berbasis slug heading row maupun key alternatif
            $rawId = $row['id_muzakki'] ?? $row['id'] ?? $row['id_muzaki'] ?? null;
            $rawNpwz = $row['npwz'] ?? null;
            $rawNama = $row['nama_lengkap'] ?? $row['nama'] ?? null;
            $rawNik = $row['nik'] ?? null;
            $rawZakat = $row['zakat_rp'] ?? $row['zakat'] ?? $row['nominal_zakat'] ?? 0;
            $rawInfak = $row['infak_rp'] ?? $row['infak'] ?? $row['nominal_infak'] ?? 0;
            $rawFitrah = $row['fitrah_rp'] ?? $row['fitrah'] ?? $row['nominal_fitrah'] ?? 0;
            $rawTitipan = $row['titipan_rp'] ?? $row['titipan'] ?? $row['nominal_titipan'] ?? 0;
            $rawKet = $row['keterangan'] ?? null;

            $cleanId = is_numeric($rawId) ? (int)$rawId : null;
            $cleanNpwz = is_string($rawNpwz) ? trim($rawNpwz) : ($rawNpwz ? (string)$rawNpwz : '');
            $cleanNama = is_string($rawNama) ? trim($rawNama) : ($rawNama ? (string)$rawNama : '');
            $cleanNik = is_string($rawNik) ? trim($rawNik) : ($rawNik ? (string)$rawNik : '');
            $cleanKet = is_string($rawKet) ? trim($rawKet) : ($rawKet ? (string)$rawKet : '');

            $nominalZakat = $this->parseNominal($rawZakat);
            $nominalInfak = $this->parseNominal($rawInfak);
            $nominalFitrah = $this->parseNominal($rawFitrah);
            $nominalTitipan = $this->parseNominal($rawTitipan);
            $totalBaris = $nominalZakat + $nominalInfak + $nominalFitrah + $nominalTitipan;

            // Lewati baris kosong cadangan jika seluruh kolom identitas dan nominal 0/kosong
            if (empty($cleanId) && empty($cleanNpwz) && empty($cleanNama) && empty($cleanNik) && $totalBaris === 0) {
                continue;
            }

            // Pencocokan Muzakki: ID -> NPWZ -> NIK
            $matchedMuzakki = null;
            if ($cleanId && isset($byId[$cleanId])) {
                $matchedMuzakki = $byId[$cleanId];
            } elseif (!empty($cleanNpwz)) {
                $searchNpwz = strtolower(preg_replace('/\s+/', '', $cleanNpwz));
                if (isset($byNpwz[$searchNpwz])) {
                    $matchedMuzakki = $byNpwz[$searchNpwz];
                }
            }

            if (!$matchedMuzakki && !empty($cleanNik)) {
                $searchNik = preg_replace('/\s+/', '', $cleanNik);
                if (isset($byNik[$searchNik])) {
                    $matchedMuzakki = $byNik[$searchNik];
                }
            }

            // Klasifikasi status: terdaftar, nihil, atau belum_terdaftar
            if ($matchedMuzakki) {
                if (empty($cleanNama)) {
                    $cleanNama = $matchedMuzakki->person?->nama ?? '';
                }
                if (empty($cleanNik)) {
                    $cleanNik = $matchedMuzakki->person?->nik ?? '';
                }
                if (empty($cleanNpwz)) {
                    $cleanNpwz = $matchedMuzakki->npwz ?? '';
                }

                $status = ($totalBaris > 0) ? 'terdaftar' : 'nihil';
            } else {
                $status = 'belum_terdaftar';
            }

            $items[] = [
                'row_number' => count($items) + 1,
                'muzakki_id' => $matchedMuzakki?->id ?? null,
                'nama' => $cleanNama,
                'nik' => $cleanNik,
                'npwz' => $cleanNpwz,
                'nominal_zakat' => $nominalZakat,
                'nominal_infak' => $nominalInfak,
                'nominal_fitrah' => $nominalFitrah,
                'nominal_titipan' => $nominalTitipan,
                'total' => $totalBaris,
                'keterangan' => $cleanKet,
                'status' => $status,
            ];
        }

        if (empty($items)) {
            return back()->withErrors(['file' => 'File spreadsheet tidak berisi baris data setoran yang dapat diproses.']);
        }

        $countTerdaftar = 0;
        $countNihil = 0;
        $countBelumTerdaftar = 0;
        $totalNominalAll = 0;

        foreach ($items as $it) {
            if ($it['status'] === 'terdaftar') $countTerdaftar++;
            elseif ($it['status'] === 'nihil') $countNihil++;
            elseif ($it['status'] === 'belum_terdaftar') $countBelumTerdaftar++;

            $totalNominalAll += $it['total'];
        }

        $stats = [
            'total' => count($items),
            'terdaftar' => $countTerdaftar,
            'nihil' => $countNihil,
            'belum_terdaftar' => $countBelumTerdaftar,
            'total_nominal' => $totalNominalAll,
        ];

        $tanggalSetor = $validated['tanggal_setor'];
        $bulan = $validated['bulan'];
        $tahun = $validated['tahun'];
        $keterangan = $validated['keterangan'] ?? null;

        // Simpan ke session untuk tahap konfirmasi
        session()->put('import_setoran_staging', [
            'lembaga_id' => (int)$validated['lembaga_id'],
            'tanggal_setor' => $tanggalSetor,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'periode' => $periode,
            'keterangan' => $keterangan,
            'items' => $items,
            'stats' => $stats,
        ]);

        return view('pengumpulan.upz.import-preview', compact(
            'items',
            'upz',
            'tanggalSetor',
            'bulan',
            'tahun',
            'periode',
            'keterangan',
            'stats'
        ));
    }

    public function quickStoreMuzakki(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => ['required', 'string', 'digits:16', 'regex:/^[0-9]{16}$/', 'unique:persons,nik'],
            'lembaga_id' => 'required|exists:lembagas,id',
            'jenis_kelamin' => 'nullable|in:L,P,Pria,Wanita',
            'npwz' => 'nullable|string|max:50',
            'handphone' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
        ]);

        $user = Auth::user();
        if ($user && $user->isUpzRole()) {
            $validated['lembaga_id'] = $user->upzLembagaId();
        }

        $jk = $validated['jenis_kelamin'] ?? null;
        if ($jk === 'Pria') $jk = 'L';
        if ($jk === 'Wanita') $jk = 'P';

        $muzakki = DB::transaction(function () use ($validated, $jk) {
            $person = Person::create([
                'nama' => $validated['nama'],
                'nik' => $validated['nik'],
                'jenis_kelamin' => $jk,
                'handphone' => $validated['handphone'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
            ]);

            return Muzakki::create([
                'person_id' => $person->id,
                'lembaga_id' => $validated['lembaga_id'],
                'npwz' => $validated['npwz'] ?? null,
                'tanggal_registrasi' => now()->toDateString(),
            ]);
        });

        return response()->json([
            'success' => true,
            'muzakki' => [
                'id' => $muzakki->id,
                'nama' => $validated['nama'],
                'nik' => $validated['nik'],
                'npwz' => $muzakki->npwz ?? '',
            ],
        ]);
    }

    public function importConfirm(Request $request)
    {
        $user = Auth::user();
        $staging = session('import_setoran_staging');

        // Terima payload jika disubmit via hidden JSON/input
        if ($request->filled('staging_data')) {
            $decoded = json_decode($request->input('staging_data'), true);
            if (is_array($decoded) && !empty($decoded['items'])) {
                $staging = $decoded;
            }
        } elseif ($request->filled('items') && is_array($request->input('items'))) {
            $staging['items'] = $request->input('items');
            if ($request->filled('lembaga_id')) $staging['lembaga_id'] = $request->input('lembaga_id');
            if ($request->filled('tanggal_setor')) $staging['tanggal_setor'] = $request->input('tanggal_setor');
            if ($request->filled('periode')) $staging['periode'] = $request->input('periode');
            if ($request->has('keterangan')) $staging['keterangan'] = $request->input('keterangan');
        }

        if ($user && $user->isUpzRole()) {
            if (is_array($staging)) {
                $staging['lembaga_id'] = $user->upzLembagaId();
            }
        }

        if (!$staging || empty($staging['items']) || !is_array($staging['items']) || count($staging['items']) === 0) {
            return redirect()->route('pengumpulan.upz.index')->with('error', 'Data staging import tidak ditemukan atau kosong. Silakan unggah kembali.');
        }

        $tanggal = Carbon::parse($staging['tanggal_setor'] ?? date('Y-m-d'));
        $nomorSetoran = $this->generateNomorSetoran($tanggal);

        $isEligible = $this->isVerifierEligible();
        $isVerifiedInput = $request->boolean('is_verified', false) || !empty($staging['is_verified']);
        $isVerified = $isEligible && $isVerifiedInput;

        $status = $isVerified ? 'Terverifikasi' : 'Menunggu Verifikasi';
        $verifiedBy = $isVerified ? Auth::id() : null;
        $verifiedAt = $isVerified ? now() : null;
        $catatanVerifikasi = $request->input('catatan_verifikasi', $staging['catatan_verifikasi'] ?? null);

        DB::transaction(function () use ($staging, $nomorSetoran, $tanggal, $status, $verifiedBy, $verifiedAt, $catatanVerifikasi) {
            $totalZakat = 0;
            $totalInfak = 0;
            $totalFitrah = 0;
            $totalTitipan = 0;

            foreach ($staging['items'] as $item) {
                $totalZakat += max(0, (int)($item['nominal_zakat'] ?? 0));
                $totalInfak += max(0, (int)($item['nominal_infak'] ?? 0));
                $totalFitrah += max(0, (int)($item['nominal_fitrah'] ?? 0));
                $totalTitipan += max(0, (int)($item['nominal_titipan'] ?? 0));
            }

            $totalJumlah = $totalZakat + $totalInfak + $totalFitrah + $totalTitipan;

            $setoran = SetoranUpz::create([
                'nomor_setoran' => $nomorSetoran,
                'lembaga_id' => $staging['lembaga_id'],
                'tanggal_setor' => $staging['tanggal_setor'],
                'periode' => $staging['periode'],
                'total_zakat' => $totalZakat,
                'total_infak' => $totalInfak,
                'total_fitrah' => $totalFitrah,
                'total_titipan' => $totalTitipan,
                'total_jumlah' => $totalJumlah,
                'keterangan' => $staging['keterangan'] ?? null,
                'status' => $status,
                'user_id' => Auth::id(),
                'verified_by' => $verifiedBy,
                'verified_at' => $verifiedAt,
                'catatan_verifikasi' => $catatanVerifikasi,
            ]);

            foreach ($staging['items'] as $item) {
                $z = max(0, (int)($item['nominal_zakat'] ?? 0));
                $i = max(0, (int)($item['nominal_infak'] ?? 0));
                $f = max(0, (int)($item['nominal_fitrah'] ?? 0));
                $t = max(0, (int)($item['nominal_titipan'] ?? 0));
                $subtotal = $z + $i + $f + $t;

                $muzakkiId = !empty($item['muzakki_id']) ? $item['muzakki_id'] : null;
                $npwz = $item['npwz'] ?? null;
                if ($muzakkiId && empty($npwz)) {
                    $m = Muzakki::find($muzakkiId);
                    $npwz = $m?->npwz;
                }

                SetoranUpzItem::create([
                    'setoran_upz_id' => $setoran->id,
                    'muzakki_id' => $muzakkiId,
                    'npwz' => $npwz,
                    'nama' => !empty($item['nama']) ? $item['nama'] : '-',
                    'nominal_zakat' => $z,
                    'nominal_infak' => $i,
                    'nominal_fitrah' => $f,
                    'nominal_titipan' => $t,
                    'jumlah' => $subtotal,
                    'keterangan' => $item['keterangan'] ?? null,
                    'periode' => $item['periode'] ?? $staging['periode'],
                ]);
            }

            $this->syncDanaTransaksi($setoran);
        });

        session()->forget('import_setoran_staging');

        return redirect()->route('pengumpulan.upz.index')->with('success', "Setoran UPZ berhasil disimpan dengan nomor {$nomorSetoran}.");
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->isUpzRole()) {
            $request->merge(['lembaga_id' => $user->upzLembagaId()]);
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $request->merge([
                'periode' => trim($request->input('bulan') . ' ' . $request->input('tahun')),
            ]);
        }

        $validated = $request->validate([
            'lembaga_id' => 'required|exists:lembagas,id',
            'tanggal_setor' => 'required|date',
            'periode' => 'required|string|max:100',
            'bulan' => 'nullable|string|max:50',
            'tahun' => 'nullable|string|max:10',
            'keterangan' => 'nullable|string',
            'catatan_verifikasi' => 'nullable|string|max:500',
            'is_verified' => 'nullable',
            'bukti_setor' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'items' => 'required|array|min:1',
            'items.*.muzakki_id' => 'nullable',
            'items.*.nama' => 'required|string|max:255',
            'items.*.npwz' => 'nullable|string|max:50',
            'items.*.nominal_zakat' => 'nullable|numeric|min:0',
            'items.*.nominal_infak' => 'nullable|numeric|min:0',
            'items.*.nominal_fitrah' => 'nullable|numeric|min:0',
            'items.*.nominal_titipan' => 'nullable|numeric|min:0',
            'items.*.keterangan' => 'nullable|string|max:255',
            'items.*.periode' => 'nullable|string|max:100',
        ]);

        if ($user && $user->isUpzRole()) {
            $validated['lembaga_id'] = $user->upzLembagaId();
        }

        $pathBukti = null;
        if ($request->hasFile('bukti_setor')) {
            $pathBukti = $request->file('bukti_setor')->store('bukti_setor', 'public');
        }

        $isEligible = $this->isVerifierEligible();
        $isVerified = $isEligible && $request->boolean('is_verified', false);

        $status = $isVerified ? 'Terverifikasi' : 'Menunggu Verifikasi';
        $verifiedBy = $isVerified ? Auth::id() : null;
        $verifiedAt = $isVerified ? now() : null;
        $catatanVerifikasi = $request->input('catatan_verifikasi');

        $tanggal = Carbon::parse($validated['tanggal_setor']);
        $nomorSetoran = $this->generateNomorSetoran($tanggal);

        DB::transaction(function () use ($validated, $nomorSetoran, $pathBukti, $status, $verifiedBy, $verifiedAt, $catatanVerifikasi) {
            $totalZakat = 0;
            $totalInfak = 0;
            $totalFitrah = 0;
            $totalTitipan = 0;

            foreach ($validated['items'] as $item) {
                $totalZakat += max(0, (int)($item['nominal_zakat'] ?? 0));
                $totalInfak += max(0, (int)($item['nominal_infak'] ?? 0));
                $totalFitrah += max(0, (int)($item['nominal_fitrah'] ?? 0));
                $totalTitipan += max(0, (int)($item['nominal_titipan'] ?? 0));
            }

            $totalJumlah = $totalZakat + $totalInfak + $totalFitrah + $totalTitipan;

            $setoran = SetoranUpz::create([
                'nomor_setoran' => $nomorSetoran,
                'lembaga_id' => $validated['lembaga_id'],
                'tanggal_setor' => $validated['tanggal_setor'],
                'periode' => $validated['periode'],
                'total_zakat' => $totalZakat,
                'total_infak' => $totalInfak,
                'total_fitrah' => $totalFitrah,
                'total_titipan' => $totalTitipan,
                'total_jumlah' => $totalJumlah,
                'keterangan' => $validated['keterangan'] ?? null,
                'bukti_setor' => $pathBukti,
                'status' => $status,
                'user_id' => Auth::id(),
                'verified_by' => $verifiedBy,
                'verified_at' => $verifiedAt,
                'catatan_verifikasi' => $catatanVerifikasi,
            ]);

            // Seluruh baris (termasuk nominal 0 / nihil) tetap disimpan demi konsistensi data audit
            foreach ($validated['items'] as $item) {
                $z = max(0, (int)($item['nominal_zakat'] ?? 0));
                $i = max(0, (int)($item['nominal_infak'] ?? 0));
                $f = max(0, (int)($item['nominal_fitrah'] ?? 0));
                $t = max(0, (int)($item['nominal_titipan'] ?? 0));
                $subtotal = $z + $i + $f + $t;

                $muzakkiId = !empty($item['muzakki_id']) ? $item['muzakki_id'] : null;
                $npwz = $item['npwz'] ?? null;
                if ($muzakkiId && empty($npwz)) {
                    $m = Muzakki::find($muzakkiId);
                    $npwz = $m?->npwz;
                }

                SetoranUpzItem::create([
                    'setoran_upz_id' => $setoran->id,
                    'muzakki_id' => $muzakkiId,
                    'npwz' => $npwz,
                    'nama' => $item['nama'],
                    'nominal_zakat' => $z,
                    'nominal_infak' => $i,
                    'nominal_fitrah' => $f,
                    'nominal_titipan' => $t,
                    'jumlah' => $subtotal,
                    'keterangan' => $item['keterangan'] ?? null,
                    'periode' => $item['periode'] ?? $validated['periode'],
                ]);
            }

            $this->syncDanaTransaksi($setoran);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Setoran UPZ berhasil disimpan dengan nomor {$nomorSetoran}.",
                'redirect_url' => route('pengumpulan.upz.index'),
            ]);
        }

        return redirect()->route('pengumpulan.upz.index')->with('success', "Setoran UPZ berhasil disimpan dengan nomor {$nomorSetoran}.");
    }

    public function uploadBukti(Request $request, $id)
    {
        $user = Auth::user();
        $setoran = SetoranUpz::findOrFail($id);

        if ($user && $user->isUpzRole() && (int)$setoran->lembaga_id !== (int)$user->upzLembagaId()) {
            abort(403);
        }

        $request->validate([
            'bukti_setor' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        if ($setoran->bukti_setor && Storage::disk('public')->exists($setoran->bukti_setor)) {
            Storage::disk('public')->delete($setoran->bukti_setor);
        }

        $path = $request->file('bukti_setor')->store('bukti_setor', 'public');
        $setoran->update([
            'bukti_setor' => $path,
        ]);

        return back()->with('success', 'Bukti setor berhasil diperbarui.');
    }

    public function verifikasi(Request $request, $id)
    {
        $this->authorizeBaznas();

        $setoran = SetoranUpz::findOrFail($id);

        $request->validate([
            'catatan_verifikasi' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($setoran, $request) {
            $setoran->update([
                'status' => 'Terverifikasi',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'catatan_verifikasi' => $request->input('catatan_verifikasi', $setoran->catatan_verifikasi),
            ]);

            $this->syncDanaTransaksi($setoran);
        });

        return redirect()->route('pengumpulan.upz.show', $setoran->id)
            ->with('success', "Setoran UPZ {$setoran->nomor_setoran} berhasil diverifikasi dan disinkronkan ke buku kas.");
    }

    public function tolak(Request $request, $id)
    {
        $this->authorizeBaznas();

        $setoran = SetoranUpz::findOrFail($id);

        $validated = $request->validate([
            'catatan_verifikasi' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($setoran, $validated) {
            $setoran->update([
                'status' => 'Ditolak',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'catatan_verifikasi' => $validated['catatan_verifikasi'],
            ]);

            $this->syncDanaTransaksi($setoran);
        });

        return redirect()->route('pengumpulan.upz.show', $setoran->id)
            ->with('success', "Setoran UPZ {$setoran->nomor_setoran} telah ditolak.");
    }

    public function edit($id)
    {
        $this->authorizeBaznas();

        $setoran = SetoranUpz::with(['upz', 'items.muzakki.person'])->findOrFail($id);
        $upzs = Upz::active()->orderBy('nama')->get();
        $muzakkis = Muzakki::with('person')->where('lembaga_id', $setoran->lembaga_id)->get();

        $daftarBulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $daftarTahun = range(min((int)date('Y') - 2, 2024), max((int)date('Y') + 4, 2030));

        return view('pengumpulan.upz.edit', compact('setoran', 'upzs', 'muzakkis', 'daftarBulan', 'daftarTahun'));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeBaznas();

        $setoran = SetoranUpz::findOrFail($id);

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $request->merge([
                'periode' => trim($request->input('bulan') . ' ' . $request->input('tahun')),
            ]);
        }

        $validated = $request->validate([
            'lembaga_id' => 'required|exists:lembagas,id',
            'tanggal_setor' => 'required|date',
            'periode' => 'required|string|max:100',
            'bulan' => 'nullable|string|max:50',
            'tahun' => 'nullable|string|max:10',
            'keterangan' => 'nullable|string',
            'catatan_verifikasi' => 'nullable|string|max:500',
            'bukti_setor' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'items' => 'required|array|min:1',
            'items.*.muzakki_id' => 'nullable',
            'items.*.nama' => 'required|string|max:255',
            'items.*.npwz' => 'nullable|string|max:50',
            'items.*.nominal_zakat' => 'nullable|numeric|min:0',
            'items.*.nominal_infak' => 'nullable|numeric|min:0',
            'items.*.nominal_fitrah' => 'nullable|numeric|min:0',
            'items.*.nominal_titipan' => 'nullable|numeric|min:0',
            'items.*.keterangan' => 'nullable|string|max:255',
            'items.*.periode' => 'nullable|string|max:100',
        ]);

        $pathBukti = $setoran->bukti_setor;
        if ($request->hasFile('bukti_setor')) {
            if ($pathBukti && Storage::disk('public')->exists($pathBukti)) {
                Storage::disk('public')->delete($pathBukti);
            }
            $pathBukti = $request->file('bukti_setor')->store('bukti_setor', 'public');
        }

        DB::transaction(function () use ($setoran, $validated, $pathBukti, $request) {
            $totalZakat = 0;
            $totalInfak = 0;
            $totalFitrah = 0;
            $totalTitipan = 0;

            foreach ($validated['items'] as $item) {
                $totalZakat += max(0, (int)($item['nominal_zakat'] ?? 0));
                $totalInfak += max(0, (int)($item['nominal_infak'] ?? 0));
                $totalFitrah += max(0, (int)($item['nominal_fitrah'] ?? 0));
                $totalTitipan += max(0, (int)($item['nominal_titipan'] ?? 0));
            }

            $totalJumlah = $totalZakat + $totalInfak + $totalFitrah + $totalTitipan;

            $setoran->update([
                'lembaga_id' => $validated['lembaga_id'],
                'tanggal_setor' => $validated['tanggal_setor'],
                'periode' => $validated['periode'],
                'total_zakat' => $totalZakat,
                'total_infak' => $totalInfak,
                'total_fitrah' => $totalFitrah,
                'total_titipan' => $totalTitipan,
                'total_jumlah' => $totalJumlah,
                'keterangan' => $validated['keterangan'] ?? null,
                'bukti_setor' => $pathBukti,
                'catatan_verifikasi' => $request->input('catatan_verifikasi', $setoran->catatan_verifikasi),
            ]);

            // Recreate items
            $setoran->items()->delete();

            foreach ($validated['items'] as $item) {
                $z = max(0, (int)($item['nominal_zakat'] ?? 0));
                $i = max(0, (int)($item['nominal_infak'] ?? 0));
                $f = max(0, (int)($item['nominal_fitrah'] ?? 0));
                $t = max(0, (int)($item['nominal_titipan'] ?? 0));
                $subtotal = $z + $i + $f + $t;

                $muzakkiId = !empty($item['muzakki_id']) ? $item['muzakki_id'] : null;
                $npwz = $item['npwz'] ?? null;
                if ($muzakkiId && empty($npwz)) {
                    $m = Muzakki::find($muzakkiId);
                    $npwz = $m?->npwz;
                }

                SetoranUpzItem::create([
                    'setoran_upz_id' => $setoran->id,
                    'muzakki_id' => $muzakkiId,
                    'npwz' => $npwz,
                    'nama' => $item['nama'],
                    'nominal_zakat' => $z,
                    'nominal_infak' => $i,
                    'nominal_fitrah' => $f,
                    'nominal_titipan' => $t,
                    'jumlah' => $subtotal,
                    'keterangan' => $item['keterangan'] ?? null,
                    'periode' => $item['periode'] ?? $validated['periode'],
                ]);
            }

            // Jika setoran Terverifikasi, update DanaTransaksi agar sinkron dengan total baru
            $this->syncDanaTransaksi($setoran);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Setoran UPZ {$setoran->nomor_setoran} berhasil diperbarui.",
                'redirect_url' => route('pengumpulan.upz.show', $setoran->id),
            ]);
        }

        return redirect()->route('pengumpulan.upz.show', $setoran->id)
            ->with('success', "Setoran UPZ {$setoran->nomor_setoran} berhasil diperbarui.");
    }

    public function show($id)
    {
        $user = Auth::user();
        $setoran = SetoranUpz::with(['upz', 'items.muzakki.person', 'user', 'verifiedBy.person'])->findOrFail($id);

        if ($user && $user->isUpzRole() && (int)$setoran->lembaga_id !== (int)$user->upzLembagaId()) {
            abort(403);
        }

        return view('pengumpulan.upz.show', compact('setoran'));
    }

    public function cetakLampiran($id)
    {
        $user = Auth::user();
        $setoran = SetoranUpz::with(['upz.ketua.person', 'items'])->findOrFail($id);

        if ($user && $user->isUpzRole() && (int)$setoran->lembaga_id !== (int)$user->upzLembagaId()) {
            abort(403);
        }

        return view('pengumpulan.upz.cetak-lampiran', compact('setoran'));
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $setoran = SetoranUpz::findOrFail($id);

        $isVerifier = $this->isVerifierEligible();
        $isUpzOwner = $user && $user->isUpzRole()
            && (int)$setoran->lembaga_id === (int)$user->upzLembagaId()
            && $setoran->status === 'Menunggu Verifikasi';

        if (!$isVerifier && !$isUpzOwner) {
            abort(403, 'Akses ditolak. Anda tidak berhak menghapus setoran ini.');
        }

        DB::transaction(function () use ($setoran) {
            // Hapus bukti setor fisik jika tersimpan
            if ($setoran->bukti_setor && Storage::disk('public')->exists($setoran->bukti_setor)) {
                Storage::disk('public')->delete($setoran->bukti_setor);
            }

            // Hapus referensi transaksi dana
            DanaTransaksi::where('referensi_type', SetoranUpz::class)
                ->where('referensi_id', $setoran->id)
                ->delete();

            // SetoranUpzItem terhapus cascade di DB atau manual
            $setoran->items()->delete();
            $setoran->delete();
        });

        return redirect()->route('pengumpulan.upz.index')->with('success', 'Setoran UPZ dan transaksi dana terkait berhasil dihapus.');
    }

    public function kasMasuk(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $search = $request->query('search');

        $query = DanaTransaksi::with(['dana', 'referensi'])
            ->where('jenis', 'Masuk')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc');

        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('uraian', 'like', "%{$search}%")
                    ->orWhereHas('dana', function ($dq) use ($search) {
                        $dq->where('nama', 'like', "%{$search}%")
                            ->orWhere('kode', 'like', "%{$search}%");
                    });
            });
        }

        $transaksis = $query->paginate(20)->withQueryString();
        $totalMasuk = (clone $query)->sum('nominal');

        return view('pengumpulan.kas.index', compact('transaksis', 'totalMasuk', 'startDate', 'endDate', 'search'));
    }
}
