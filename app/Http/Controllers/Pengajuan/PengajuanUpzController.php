<?php

namespace App\Http\Controllers\Pengajuan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanMustahik;
use App\Models\Person;
use App\Models\Mustahik;
use App\Models\Upz;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Program;
use App\Models\Subprogram;
use App\Models\Dana;
use App\Models\Asnaf;
use App\Models\Wilayah;
use App\Models\User;
use App\Exports\PengajuanBantuanImportTemplate;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PengajuanUpzController extends Controller
{
    private function getRomawiBulan($bulan)
    {
        $romawi = ['1'=>'I', '2'=>'II', '3'=>'III', '4'=>'IV', '5'=>'V', '6'=>'VI', '7'=>'VII', '8'=>'VIII', '9'=>'IX', '10'=>'X', '11'=>'XI', '12'=>'XII'];
        return $romawi[(int)$bulan] ?? 'I';
    }

    private function generateNomorPengajuan($jenisStr)
    {
        $tahun = date('Y');
        $bulan = date('m');
        $bulanRomawi = $this->getRomawiBulan($bulan);
        $prefix = "E-POOL/UPZ/{$bulanRomawi}/{$tahun}";
        
        $lastPengajuan = PengajuanBantuan::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->orderBy('id', 'desc')
            ->first();
            
        $increment = 1;
        if ($lastPengajuan && $lastPengajuan->nomor_pengajuan) {
            $parts = explode('/', $lastPengajuan->nomor_pengajuan);
            if (is_numeric($parts[0])) {
                $increment = (int)$parts[0] + 1;
            }
        }
        
        return sprintf("%03d/%s", $increment, $prefix);
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status', 'semua');
        $user = Auth::user();
        $upzId = $user->isBaznasRole() ? $request->query('upz_id') : $user->upzLembagaId();
        $programId = $request->query('program_id') ?? $request->query('program_filter');

        $baseQuery = PengajuanBantuan::query()->whereIn('jenis_permohonan', ['UPZ/Lembaga', 'Lembaga']);
        if (!$user->isBaznasRole()) {
            $baseQuery->where('lembaga_id', $upzId ?? 0);
        }

        $stats = [
            'total_pengajuan' => (clone $baseQuery)->count(),
            'total_disetujui' => (clone $baseQuery)->whereHas('persetujuan', fn($q) => $q->where('status', 'disetujui'))->count(),
            'total_proses' => (clone $baseQuery)->whereNull('ditolak_at')->whereDoesntHave('persetujuan')->count(),
            'total_ditolak' => (clone $baseQuery)->where(function($q) {
                $q->whereNotNull('ditolak_at')->orWhereHas('persetujuan', fn($pq) => $pq->where('status', 'ditolak'));
            })->count(),
        ];

        $query = (clone $baseQuery)
            ->with([
                'upz',
                'mustahik.person',
                'pengajuanMustahiks.program',
                'pengajuanMustahiks.subprogram',
                'pengajuanMustahiks.asnaf',
                'pengajuanMustahiks.survey',
                'pengajuanMustahiks.disposisi',
                'persetujuan',
                'disposisi',
                'penyaluran',
                'lpjBa',
            ])
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_pengajuan', 'like', '%' . $search . '%')
                  ->orWhere('judul_surat', 'like', '%' . $search . '%')
                  ->orWhere('lembaga_nama_snapshot', 'like', '%' . $search . '%')
                  ->orWhereHas('upz', fn($uq) => $uq->where('nama', 'like', '%' . $search . '%'))
                  ->orWhereHas('mustahik.person', fn($pq) => $pq->where('nama', 'like', '%' . $search . '%'));
            });
        }

        if ($upzId) {
            $query->where('lembaga_id', $upzId);
        }

        if ($programId) {
            $query->whereHas('pengajuanMustahiks', fn($q) => $q->where('program_id', $programId));
        }

        if ($status !== 'semua') {
            if ($status === 'draft' || $status === 'aktif') {
                $query->whereNull('ditolak_at')
                      ->where(function($q) {
                          $q->whereDoesntHave('persetujuan')
                            ->orWhereHas('persetujuan', fn($pq) => $pq->where('status', 'disetujui'));
                      });
            } elseif ($status === 'proses') {
                $query->whereNull('ditolak_at')
                      ->whereDoesntHave('disposisi')
                      ->whereDoesntHave('persetujuan');
            } elseif ($status === 'disetujui') {
                $query->whereHas('persetujuan', fn($q) => $q->where('status', 'disetujui'));
            } elseif ($status === 'ditolak') {
                $query->where(function($q) {
                    $q->whereNotNull('ditolak_at')
                      ->orWhereHas('persetujuan', fn($pq) => $pq->where('status', 'ditolak'));
                });
            } elseif ($status === 'disalurkan') {
                $query->whereHas('penyaluran');
            } elseif ($status === 'selesai') {
                $query->whereHas('lpjBa');
            }
        }

        $pengajuans = $query->paginate(10)->withQueryString();
        $upzs = $user->isBaznasRole()
            ? Upz::active()->orderBy('nama')->get()
            : Upz::active()->whereKey($upzId ?? 0)->orderBy('nama')->get();
        $programs = Program::orderBy('nama')->get();
        $programFilter = $programId;

        return view('pengajuan.upz.index', compact('pengajuans', 'upzs', 'programs', 'search', 'status', 'upzId', 'programId', 'programFilter', 'stats'));
    }

    public function create()
    {
        $upzs = Auth::user()->isBaznasRole()
            ? Upz::active()->orderBy('nama')->get()
            : Upz::active()->whereKey(Auth::user()->upzLembagaId() ?? 0)->orderBy('nama')->get();
        $programs = Program::orderBy('nama')->get();
        $subprograms = Subprogram::orderBy('nama')->get();
        $danas = Dana::active()->ordered()->get();
        $asnafs = Asnaf::where('is_active', true)->orderBy('id')->get();
        $provinsis = Wilayah::provinsis()->orderBy('nama')->get();

        return view('pengajuan.upz.create', compact('upzs', 'programs', 'subprograms', 'danas', 'asnafs', 'provinsis'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'upz_id' => $user->isBaznasRole() ? 'required|integer|exists:lembagas,id' : 'nullable|integer|exists:lembagas,id',
            'judul_surat' => 'required|string|max:255',
            'nomor_surat' => 'nullable|string|max:100',
            'tanggal_surat' => 'required|date',
            'tanggal_pelaksanaan' => 'nullable|date',
            'tanggal_diterima' => 'required|date',
            'uraian_permohonan' => 'nullable|string',
            'lampiran_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'file_surat' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'mustahiks' => 'nullable|array',
            'mustahiks.*' => 'array',
            'mustahiks.*.nama' => 'nullable|string|max:255',
            'mustahiks.*.nik' => 'nullable|string|max:32',
            'mustahiks.*.pekerjaan' => 'nullable|string|max:255',
            'mustahiks.*.telepon' => 'nullable|string|max:30',
            'mustahiks.*.alamat' => 'nullable|string',
            'mustahiks.*.program_id' => 'nullable|integer|exists:programs,id',
            'mustahiks.*.subprogram_id' => 'nullable|integer|exists:subprograms,id',
            'mustahiks.*.keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            abort_unless($user->isBaznasRole() || $user->isUpzRole(), 403);
            $upzId = $user->isBaznasRole() ? (int) $validated['upz_id'] : $user->upzLembagaId();
            abort_unless($upzId, 403);
            $upz = Upz::active()->findOrFail($upzId);
            $jenisPermohonan = 'Lembaga';

            $nomorPengajuan = $this->generateNomorPengajuan($jenisPermohonan);

            $pengajuan = PengajuanBantuan::create([
                'nomor_pengajuan' => $nomorPengajuan,
                'lembaga_id' => $upz->id,
                'lembaga_nama_snapshot' => $upz->nama,
                'lembaga_kontak_snapshot' => $upz->telepon ?: $upz->handphone,
                'lembaga_alamat_snapshot' => $upz->alamat_lengkap,
                'jenis_permohonan' => $jenisPermohonan,
                'mustahik_id' => null,
                'is_rekomendasi' => true,
                'judul_surat' => $request->judul_surat,
                'nomor_surat' => $request->nomor_surat,
                'tanggal_surat' => $request->tanggal_surat,
                'tanggal_pelaksanaan' => $request->tanggal_pelaksanaan,
                'tanggal_diterima' => $request->tanggal_diterima,
                'uraian_permohonan' => $request->uraian_permohonan ?: $request->judul_surat,
                'created_by' => $user->id,
            ]);

            // Save Mustahiks / Program
            $mustahiksInput = $request->input('mustahiks', []);
            $createdAnyMustahik = false;

            if (is_array($mustahiksInput) && count($mustahiksInput) > 0) {
                foreach ($mustahiksInput as $mItem) {
                    if (empty($mItem['program_id'])) continue;

                    $mNik = !empty($mItem['nik']) ? trim((string)$mItem['nik']) : null;
                    $mNama = !empty($mItem['nama']) ? trim((string)$mItem['nama']) : null;

                    if ($mNik) {
                        $mPerson = Person::firstOrCreate(['nik' => $mNik], [
                            'nama' => $mNama ?: 'Mustahik',
                            'pekerjaan' => $mItem['pekerjaan'] ?? null,
                            'telepon' => $mItem['telepon'] ?? null,
                            'alamat' => $mItem['alamat'] ?? null,
                        ]);
                        $mMustahik = Mustahik::firstOrCreate(['person_id' => $mPerson->id]);
                        $mMustahikId = $mMustahik->id;
                    } elseif ($mNama) {
                        $mPerson = Person::create([
                            'nama' => $mNama,
                            'nik' => 'TMP' . time() . rand(100, 999),
                            'pekerjaan' => $mItem['pekerjaan'] ?? null,
                            'telepon' => $mItem['telepon'] ?? null,
                            'alamat' => $mItem['alamat'] ?? null,
                        ]);
                        $mMustahik = Mustahik::firstOrCreate(['person_id' => $mPerson->id]);
                        $mMustahikId = $mMustahik->id;
                    } else {
                        continue;
                    }

                    $pengajuan->pengajuanMustahiks()->create([
                        'mustahik_id' => $mMustahikId,
                        'program_id' => $mItem['program_id'],
                        'subprogram_id' => !empty($mItem['subprogram_id']) ? $mItem['subprogram_id'] : null,
                        'keterangan' => !empty($mItem['keterangan']) ? $mItem['keterangan'] : $request->judul_surat,
                        'nominal' => 0,
                        'sumber_dana_id' => $mItem['sumber_dana_id'] ?? null,
                        'bentuk_bantuan' => $mItem['bentuk_bantuan'] ?? 'Uang',
                        'nama_barang' => $mItem['nama_barang'] ?? null,
                        'jumlah_barang' => $mItem['jumlah_barang'] ?? null,
                        'satuan_barang' => $mItem['satuan_barang'] ?? null,
                        'pengadaan_barang_id' => $mItem['pengadaan_barang_id'] ?? null,
                    ]);
                    $createdAnyMustahik = true;
                }
            }

            if ($request->hasFile('file_surat') || $request->hasFile('lampiran_surat')) {
                $file = $request->file('file_surat') ?: $request->file('lampiran_surat');
                $path = $file->store('lampiran_pengajuan', 'public');
                $pengajuan->lampirans()->create([
                    'name' => 'Surat Rekomendasi UPZ / Proposal',
                    'category' => 'surat',
                    'file_path' => $path,
                ]);
            }

            DB::commit();

            return redirect()->route('pengajuan.upz.show', ['pengajuan' => $pengajuan->id])->with('success', 'Pengajuan rekomendasi UPZ berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat pengajuan UPZ: ' . $e->getMessage()])->withInput();
        }
    }

    public function showImport()
    {
        $programs = Program::all();
        $subprograms = Subprogram::all();
        return view('pengajuan.upz.import', compact('programs', 'subprograms'));
    }

    public function importView()
    {
        return $this->showImport();
    }

    public function downloadTemplate($jenis)
    {
        return Excel::download(new PengajuanBantuanImportTemplate($jenis), 'template_import_pengajuan_'.strtolower($jenis).'.xlsx');
    }

    public function importStore(Request $request)
    {
        return $this->processImport($request);
    }

    public function processImport(Request $request)
    {
        $hasUpload = $request->hasFile('importFile') || $request->hasFile('import_file');
        if (!$hasUpload) {
            return back()->withErrors(['importError' => 'File Excel rekomendasi wajib diunggah.']);
        }

        $uploadedFile = $request->file('importFile') ?: $request->file('import_file');

        try {
            $importObj = new class extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements \Maatwebsite\Excel\Concerns\WithCustomValueBinder {};
            $data = Excel::toArray($importObj, $uploadedFile->getRealPath());
            $rows = $data[0] ?? [];
            
            if (empty($rows)) throw new \Exception('File Excel kosong.');

            $headerRow = $rows[0];
            $templateType = null;
            foreach($headerRow as $cell) {
                if (is_string($cell) && str_starts_with($cell, 'TEMPLATE_TYPE=')) {
                    $templateType = str_replace('TEMPLATE_TYPE=', '', $cell);
                    break;
                }
            }

            if (!$templateType) {
                throw new \Exception('Format template tidak dikenali. Pastikan Anda mendownload template terbaru dan tidak mengubah baris header.');
            }

            array_shift($rows);

            $programs = Program::all();
            $subprograms = Subprogram::all();
            $allUsers = User::with('person')->get();

            $findUserId = function($searchTerm) use ($allUsers) {
                if (!$searchTerm) return null;
                $user = $allUsers->first(function($u) use ($searchTerm) {
                    return strtolower(trim($u->email)) === strtolower(trim($searchTerm)) || 
                           ($u->person && strtolower(trim($u->person->nama)) === strtolower(trim($searchTerm)));
                });
                return $user ? $user->id : null;
            };

            $parseDate = function($val) {
                if (!$val) return null;
                if (is_numeric($val)) return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
                return $val;
            };

            DB::beginTransaction();
            $authUserId = Auth::id();
            $importedCount = 0;

            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue;

                $prog = $programs->firstWhere('nama', $row[2] ?? '');
                $subprog = $subprograms->firstWhere('nama', $row[3] ?? '');
                $nik_lembaga = trim((string)($row[0] ?? ''));
                $nama_lengkap = trim((string)($row[1] ?? ''));

                if (!$prog || !$subprog || !$nik_lembaga) continue;

                $upz_id = null;
                $pic_upz_id = null;
                $mustahik_id = null;
                $upz_nama_snapshot = null;

                if ($templateType === 'Individu') {
                    $nik = preg_replace('/[^0-9]/', '', $nik_lembaga);
                    $person = Person::firstOrCreate(
                        ['nik' => $nik],
                        ['nama' => $nama_lengkap]
                    );
                    $mustahik = Mustahik::firstOrCreate(['person_id' => $person->id]);
                    $mustahik_id = $mustahik->id;
                } else {
                    $upz = Upz::firstOrCreate(
                        ['nama' => $nik_lembaga], 
                        ['is_active' => true, 'jenis' => 'Lembaga / Organisasi']
                    );
                    $upz_id = $upz->id;
                    $upz_nama_snapshot = $upz->nama;
                    
                    $ketuaPerson = Person::firstOrCreate(
                        ['nama' => $nama_lengkap],
                        ['nik' => '9999999999999999']
                    );
                    
                    $jabatanKetua = PengurusJabatan::firstOrCreate(['nama' => 'Ketua']);
                    $pengurus = Pengurus::firstOrCreate(
                        ['lembaga_id' => $upz->id, 'person_id' => $ketuaPerson->id],
                        ['jenis_pengurus' => 'Lembaga', 'pengurus_jabatan_id' => $jabatanKetua->id, 'is_active' => true, 'tanggal_mulai' => date('Y-m-d')]
                    );
                    $pic_upz_id = $pengurus->id;
                }

                $nomor_pengajuan = $this->generateNomorPengajuan($templateType);

                $pengajuan = PengajuanBantuan::create([
                    'nomor_pengajuan' => $nomor_pengajuan,
                    'jenis_permohonan' => in_array($templateType, ['UPZ/Lembaga', 'Lembaga']) ? $templateType : 'UPZ/Lembaga',
                    'lembaga_id' => $upz_id,
                    'pic_lembaga_id' => $pic_upz_id,
                    'mustahik_id' => $mustahik_id,
                    'created_by' => $authUserId,
                    'lembaga_nama_snapshot' => $upz_nama_snapshot,
                    'is_rekomendasi' => true,
                    'judul_surat' => $row[4] ?? 'Pengajuan Rekomendasi UPZ',
                    'uraian_permohonan' => $row[4] ?? 'Pengajuan Rekomendasi UPZ',
                    'tanggal_surat' => $parseDate($row[5] ?? null) ?: date('Y-m-d'),
                    'tanggal_diterima' => $parseDate($row[6] ?? null) ?: date('Y-m-d'),
                    'ditolak_at' => (isset($row[8]) && strtolower($row[8]) === 'ditolak') ? now() : null,
                ]);

                if ($mustahik_id) {
                    $nomDiajukan = (int) preg_replace('/\D/', '', $row[7] ?? '0');
                    $nomDisetujui = (int) preg_replace('/\D/', '', $row[16] ?? '0');
                    $pengajuan->pengajuanMustahiks()->create([
                        'mustahik_id' => $mustahik_id,
                        'program_id' => $prog->id,
                        'subprogram_id' => $subprog->id,
                        'keterangan' => $row[4] ?? 'Pengajuan Bantuan',
                        'nominal' => $nomDisetujui ?: $nomDiajukan,
                        'sumber_dana_id' => null,
                        'bentuk_bantuan' => 'Uang',
                        'nama_barang' => null,
                        'jumlah_barang' => null,
                        'satuan_barang' => null,
                        'pengadaan_barang_id' => null,
                        'jumlah_jiwa' => 1,
                        'jumlah_kk' => 1,
                    ]);
                }

                // Disposisi
                if (!empty($row[9]) || !empty($row[10])) {
                    $pengajuan->disposisi()->create([
                        'instruksi' => $row[9] ?: 'Proses Langsung',
                        'catatan' => $row[10] ?? '',
                        'pimpinan_id' => $authUserId,
                    ]);
                }

                // Survey
                if (!empty($row[12]) || !empty($row[13])) {
                    $firstPm = $pengajuan->pengajuanMustahiks()->first();
                    if ($firstPm) {
                        $firstPm->survey()->create([
                            'surveyor' => $row[11] ?? 'Surveyor',
                            'tanggal_survey' => $parseDate($row[12] ?? null) ?: date('Y-m-d'),
                            'hasil_akhir' => strtolower($row[13] ?? '') ?: 'layak',
                            'catatan' => $row[14] ?? '',
                            'token' => \Illuminate\Support\Str::random(32),
                        ]);
                    }
                }

                // Persetujuan
                $nominal_disetujui = (int) preg_replace('/\D/', '', $row[16] ?? '0');
                if (!empty($row[15]) || $nominal_disetujui > 0) {
                    $pengajuan->persetujuan()->create([
                        'status' => in_array(strtolower($row[15] ?? ''), ['disetujui','ditolak','tidak_dicairkan']) ? strtolower($row[15]) : 'disetujui',
                        'nominal_disetujui' => $nominal_disetujui,
                        'catatan' => $row[17] ?? '',
                        'approved_by' => $findUserId($row[18] ?? '') ?: $authUserId,
                        'tanggal_persetujuan' => $parseDate($row[19] ?? null) ?: date('Y-m-d'),
                    ]);
                }

                // Penyaluran
                $nominal_penyaluran = (int) preg_replace('/\D/', '', $row[21] ?? '0');
                if (!empty($row[20]) || $nominal_penyaluran > 0) {
                    $pengajuan->penyaluran()->create([
                        'tanggal_penyaluran' => $parseDate($row[20] ?? null) ?: date('Y-m-d'),
                        'nominal_penyaluran' => $nominal_penyaluran,
                        'metode_penyaluran' => in_array($row[22] ?? '', ['Tunai','Transfer','Barang']) ? $row[22] : 'Tunai',
                        'catatan' => $row[23] ?? '',
                        'created_by' => $findUserId($row[24] ?? '') ?: $authUserId,
                    ]);
                }

                // LPJ
                $nominal_realisasi = (int) preg_replace('/\D/', '', $row[26] ?? '0');
                $nominal_sisa = (int) preg_replace('/\D/', '', $row[27] ?? '0');
                if (!empty($row[25]) || $nominal_realisasi > 0) {
                    $pengajuan->lpjBa()->create([
                        'tanggal_lpj' => $parseDate($row[25] ?? null) ?: date('Y-m-d'),
                        'nominal_realisasi' => $nominal_realisasi,
                        'nominal_sisa' => $nominal_sisa,
                        'sisa_dikembalikan' => ($row[28] ?? '') === 'Ya' ? 1 : 0,
                        'catatan' => $row[29] ?? '',
                        'created_by' => $authUserId,
                    ]);
                }

                $importedCount++;
            }

            DB::commit();
            return redirect()->route('pengajuan.upz.index')->with('success', 'Berhasil mengimpor ' . $importedCount . ' data pengajuan UPZ beserta tahapannya.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['importError' => 'Gagal memproses import Excel: ' . $e->getMessage()]);
        }
    }
}
