<?php

namespace App\Http\Controllers\Muzakki;

use App\Http\Controllers\Controller;
use App\Models\Muzakki;
use App\Models\Person;
use App\Models\Upz;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MuzakkiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $lembagaId = $request->query('lembaga_id');

        $query = Muzakki::with(['person', 'upz'])->latest();

        if ($lembagaId) {
            $query->where('lembaga_id', $lembagaId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('npwz', 'like', "%{$search}%")
                    ->orWhere('nrm', 'like', "%{$search}%")
                    ->orWhereHas('person', function ($pq) use ($search) {
                        $pq->where('nama', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
            });
        }

        $muzakkis = $query->paginate(15)->withQueryString();
        $upzs = Upz::active()->orderBy('nama')->get();

        return view('muzakki.index', compact('muzakkis', 'upzs', 'search', 'lembagaId'));
    }

    public function create()
    {
        $upzs = Upz::active()->orderBy('nama')->get();
        $provinsis = Wilayah::provinsis()->orderBy('nama')->get();

        return view('muzakki.create', compact('upzs', 'provinsis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => ['required', 'string', 'digits:16', 'regex:/^[0-9]{16}$/', Rule::unique('persons', 'nik')],
            'lembaga_id' => 'nullable|exists:lembagas,id',
            'npwz' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'nip' => 'nullable|string|max:50',
            'pekerjaan' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:L,P,Pria,Wanita',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'telepon' => 'nullable|string|max:50',
            'handphone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'alamat_kantor' => 'nullable|string',
            'alamat_korespondensi' => 'nullable|string',
            'jenis_zakat' => 'nullable|string|max:100',
            'tanggal_registrasi' => 'nullable|date',
            'keterangan' => 'nullable|string',
        ]);

        $jk = $validated['jenis_kelamin'] ?? null;
        if ($jk === 'Pria') $jk = 'L';
        if ($jk === 'Wanita') $jk = 'P';

        DB::transaction(function () use ($validated, $jk) {
            $person = Person::create([
                'nik' => $validated['nik'],
                'nama' => $validated['nama'],
                'jenis_kelamin' => $jk,
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'telepon' => $validated['telepon'] ?? null,
                'handphone' => $validated['handphone'] ?? null,
                'email' => $validated['email'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
            ]);

            Muzakki::create([
                'person_id' => $person->id,
                'lembaga_id' => $validated['lembaga_id'] ?? null,
                'npwz' => $validated['npwz'] ?? null,
                'npwp' => $validated['npwp'] ?? null,
                'nip' => $validated['nip'] ?? null,
                'pekerjaan' => $validated['pekerjaan'] ?? null,
                'alamat_kantor' => $validated['alamat_kantor'] ?? null,
                'alamat_korespondensi' => $validated['alamat_korespondensi'] ?? null,
                'jenis_zakat' => $validated['jenis_zakat'] ?? null,
                'tanggal_registrasi' => $validated['tanggal_registrasi'] ?? now()->toDateString(),
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        });

        return redirect()->route('muzakki.index')->with('success', 'Data Muzakki berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $muzakki = Muzakki::with('person')->findOrFail($id);
        $upzs = Upz::active()->orderBy('nama')->get();

        return view('muzakki.edit', compact('muzakki', 'upzs'));
    }

    public function update(Request $request, $id)
    {
        $muzakki = Muzakki::with('person')->findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik' => ['required', 'string', 'digits:16', 'regex:/^[0-9]{16}$/', Rule::unique('persons', 'nik')->ignore($muzakki->person_id)],
            'lembaga_id' => 'nullable|exists:lembagas,id',
            'npwz' => 'nullable|string|max:50',
            'npwp' => 'nullable|string|max:50',
            'nip' => 'nullable|string|max:50',
            'pekerjaan' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:L,P,Pria,Wanita',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'telepon' => 'nullable|string|max:50',
            'handphone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'alamat' => 'nullable|string',
            'alamat_kantor' => 'nullable|string',
            'alamat_korespondensi' => 'nullable|string',
            'jenis_zakat' => 'nullable|string|max:100',
            'tanggal_registrasi' => 'nullable|date',
            'keterangan' => 'nullable|string',
        ]);

        $jk = $validated['jenis_kelamin'] ?? null;
        if ($jk === 'Pria') $jk = 'L';
        if ($jk === 'Wanita') $jk = 'P';

        DB::transaction(function () use ($muzakki, $validated, $jk) {
            $muzakki->person->update([
                'nik' => $validated['nik'],
                'nama' => $validated['nama'],
                'jenis_kelamin' => $jk,
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'telepon' => $validated['telepon'] ?? null,
                'handphone' => $validated['handphone'] ?? null,
                'email' => $validated['email'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
            ]);

            $muzakki->update([
                'lembaga_id' => $validated['lembaga_id'] ?? null,
                'npwz' => $validated['npwz'] ?? null,
                'npwp' => $validated['npwp'] ?? null,
                'nip' => $validated['nip'] ?? null,
                'pekerjaan' => $validated['pekerjaan'] ?? null,
                'alamat_kantor' => $validated['alamat_kantor'] ?? null,
                'alamat_korespondensi' => $validated['alamat_korespondensi'] ?? null,
                'jenis_zakat' => $validated['jenis_zakat'] ?? null,
                'tanggal_registrasi' => $validated['tanggal_registrasi'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        });

        return redirect()->route('muzakki.index')->with('success', 'Data Muzakki berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $muzakki = Muzakki::findOrFail($id);
        $muzakki->delete();

        return redirect()->route('muzakki.index')->with('success', 'Data Muzakki berhasil dihapus.');
    }

    public function importForm()
    {
        $upzs = Upz::active()->orderBy('nama')->get();
        return view('muzakki.import', compact('upzs'));
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'lembaga_id' => 'nullable|exists:lembagas,id',
        ]);

        $defaultLembagaId = $request->input('lembaga_id');
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return back()->with('error', 'Gagal membuka file CSV.');
        }

        $headerFound = false;
        $headerMap = [];
        $importedCount = 0;
        $skippedCount = 0;

        $upzList = Upz::all();

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 4096, ',')) !== false) {
                $cleanedRow = array_map(fn($v) => trim((string)$v), $row);

                // Cari baris header
                if (!$headerFound) {
                    $rowStr = implode(' ', array_map('strtolower', $cleanedRow));
                    if (str_contains($rowStr, 'nik') && str_contains($rowStr, 'nama')) {
                        $headerFound = true;
                        foreach ($cleanedRow as $idx => $colName) {
                            $norm = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $colName));
                            $headerMap[$norm] = $idx;
                        }
                    }
                    continue;
                }

                $nikIdx = $headerMap['nik'] ?? null;
                $namaIdx = $headerMap['nama'] ?? null;
                $nik = $nikIdx !== null ? ($cleanedRow[$nikIdx] ?? '') : '';
                $nama = $namaIdx !== null ? ($cleanedRow[$namaIdx] ?? '') : '';

                if (empty($nik) || empty($nama) || str_starts_with($nik, '*') || str_contains($nik, 'wajib')) {
                    $skippedCount++;
                    continue;
                }

                $npwp = isset($headerMap['npwp']) ? ($cleanedRow[$headerMap['npwp']] ?? null) : null;
                $nip = isset($headerMap['nip']) ? ($cleanedRow[$headerMap['nip']] ?? null) : null;
                $tempatLahir = isset($headerMap['tempatlahir']) ? ($cleanedRow[$headerMap['tempatlahir']] ?? null) : null;
                $tanggalLahirRaw = isset($headerMap['tanggallahir']) ? ($cleanedRow[$headerMap['tanggallahir']] ?? null) : null;
                $jkRaw = isset($headerMap['jeniskelamin']) ? ($cleanedRow[$headerMap['jeniskelamin']] ?? null) : null;
                $pekerjaan = isset($headerMap['pekerjaan']) ? ($cleanedRow[$headerMap['pekerjaan']] ?? null) : null;
                $alamatKorespondensi = isset($headerMap['alamatkorespondensi']) ? ($cleanedRow[$headerMap['alamatkorespondensi']] ?? null) : null;
                $alamatRumah = isset($headerMap['alamatrumah']) ? ($cleanedRow[$headerMap['alamatrumah']] ?? null) : null;
                $alamatKantor = isset($headerMap['alamatkantor']) ? ($cleanedRow[$headerMap['alamatkantor']] ?? null) : null;
                $telepon = isset($headerMap['telepon']) ? ($cleanedRow[$headerMap['telepon']] ?? null) : null;
                $hp = isset($headerMap['handphone']) ? ($cleanedRow[$headerMap['handphone']] ?? null) : null;
                $email = isset($headerMap['email']) ? ($cleanedRow[$headerMap['email']] ?? null) : null;
                $upzRaw = isset($headerMap['upz']) ? ($cleanedRow[$headerMap['upz']] ?? null) : null;
                $zakat = isset($headerMap['zakat']) ? ($cleanedRow[$headerMap['zakat']] ?? null) : null;
                $catatan = isset($headerMap['catatan']) ? ($cleanedRow[$headerMap['catatan']] ?? null) : null;

                $tglLahir = null;
                if (!empty($tanggalLahirRaw)) {
                    $parsed = date_create($tanggalLahirRaw);
                    if ($parsed) {
                        $tglLahir = date_format($parsed, 'Y-m-d');
                    }
                }

                $jk = null;
                if ($jkRaw) {
                    $jkLower = strtolower($jkRaw);
                    if (str_starts_with($jkLower, 'pria') || $jkLower === 'l') {
                        $jk = 'L';
                    } elseif (str_starts_with($jkLower, 'wanita') || $jkLower === 'p') {
                        $jk = 'P';
                    }
                }

                $matchedLembagaId = $defaultLembagaId;
                if (!empty($upzRaw) && !in_array(strtolower($upzRaw), ['dikosongi', '-', 'null'])) {
                    $foundUpz = $upzList->first(function ($u) use ($upzRaw) {
                        return strtolower($u->nama) === strtolower($upzRaw)
                            || (!empty($u->kode_simba) && (string)$u->kode_simba === (string)$upzRaw);
                    });
                    if ($foundUpz) {
                        $matchedLembagaId = $foundUpz->id;
                    }
                }

                $person = Person::updateOrCreate(
                    ['nik' => $nik],
                    [
                        'nama' => $nama,
                        'jenis_kelamin' => $jk,
                        'tempat_lahir' => $tempatLahir ?: null,
                        'tanggal_lahir' => $tglLahir,
                        'telepon' => $telepon ?: null,
                        'handphone' => $hp ?: null,
                        'email' => $email ?: null,
                        'alamat' => $alamatRumah ?: ($alamatKorespondensi ?: null),
                    ]
                );

                Muzakki::updateOrCreate(
                    ['person_id' => $person->id],
                    [
                        'lembaga_id' => $matchedLembagaId,
                        'npwp' => $npwp ?: null,
                        'nip' => $nip ?: null,
                        'pekerjaan' => $pekerjaan ?: null,
                        'alamat_kantor' => $alamatKantor ?: null,
                        'alamat_korespondensi' => $alamatKorespondensi ?: null,
                        'jenis_zakat' => (!empty($zakat) && strtolower($zakat) !== 'dikosongi') ? $zakat : null,
                        'tanggal_registrasi' => now()->toDateString(),
                        'keterangan' => $catatan ?: null,
                    ]
                );

                $importedCount++;
            }

            fclose($handle);
            DB::commit();

            return redirect()->route('muzakki.index')->with('success', "Import selesai: {$importedCount} data berhasil disimpan.");
        } catch (\Throwable $e) {
            fclose($handle);
            DB::rollBack();
            return back()->with('error', 'Gagal memproses file CSV: ' . $e->getMessage());
        }
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Format Input Data Muzaki dan Munfiq UPZ.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($handle, [
                'Nama',
                'NPWP',
                'NIK *',
                'NIP',
                'Tempat Lahir',
                'tanggal lahir',
                'jenis kelamin',
                'pekerjaan',
                'alamat korespondensi',
                'alamat rumah*',
                'alamat kantor',
                'telepon',
                'handphone *',
                'email *',
                'UPZ',
                'zakat',
                'catatan',
            ]);

            fputcsv($handle, [
                'Ahmad Fulan',
                '12313212432132',
                '3301000000000001',
                '198501012010011001',
                'Cilacap',
                '12/12/1990',
                'Pria',
                'PNS',
                'rumah',
                'Jl. Gatot Subroto No. 12, Cilacap',
                'Kantor Dinas',
                '028212345',
                '081234567890',
                'fulan@example.com',
                'UPZ Dinas Kesehatan Kab. Cilacap',
                'zakat profesi',
                'Muzaki aktif',
            ]);

            fclose($handle);
        }, 200, $headers);
    }
}
