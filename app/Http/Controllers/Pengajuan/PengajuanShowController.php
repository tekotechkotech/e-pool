<?php

namespace App\Http\Controllers\Pengajuan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanMustahik;
use App\Models\PengajuanDisposisi;
use App\Models\PengajuanSurvey;
use App\Models\PengajuanPersetujuan;
use App\Models\PengajuanPenyaluran;
use App\Models\PengajuanLpjBa;
use App\Models\Person;
use App\Models\Lembaga;
use App\Models\Mustahik;
use App\Models\Program;
use App\Models\Subprogram;
use App\Models\Dana;
use App\Models\Asnaf;
use App\Models\DanaTransaksi;
use App\Models\Barang;
use App\Models\BarangTransaksi;
use App\Models\PengadaanBarang;
use App\Models\Lampiran;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PengajuanShowController extends Controller
{
    public function show(Request $request, $id, $tab = null)
    {
        $pengajuan = PengajuanBantuan::with([
            'upz',
            'lembaga',
            'picUpz.person',
            'picLembaga.person',
            'mustahik.person',
            'disposisi.pimpinan.person',
            'persetujuan.approvedBy.person',
            'penyaluran.createdBy.person',
            'lpjBa.createdBy.person',
            'lpjBa.lampirans',
            'pengajuanMustahiks.mustahik.person',
            'pengajuanMustahiks.mustahik.lembaga',
            'pengajuanMustahiks.program',
            'pengajuanMustahiks.subprogram',
            'pengajuanMustahiks.asnaf',
            'pengajuanMustahiks.sumberDana',
            'pengajuanMustahiks.survey.hadKifayah',
            'pengajuanMustahiks.survey.asesmen',
            'pengajuanMustahiks.survey.lampiranSurvey',
            'pengajuanMustahiks.survey.lampirans',
            'pengajuanMustahiks.disposisi.pimpinan.person',
            'pengajuanMustahiks.pengadaanBarang.barang',
            'pengajuanMustahiks.buktiPenyaluran',
            'pengajuanMustahiks.lampirans',
            'lampirans',
        ])->findOrFail($id);
        abort_unless(Auth::user()->canAccessPengajuan($pengajuan), 404);

        $allowedTabs = ['detail', 'survey', 'persetujuan', 'penyaluran', 'lpj'];
        $activeTab = in_array($tab, $allowedTabs) ? $tab : ($request->query('tab') && in_array($request->query('tab'), $allowedTabs) ? $request->query('tab') : 'detail');

        if ($activeTab === 'survey') {
            $needsReload = false;
            foreach ($pengajuan->pengajuanMustahiks as $pm) {
                if (!$pm->survey) {
                    $pm->survey()->firstOrCreate([], ['token' => Str::random(32)]);
                    $needsReload = true;
                } elseif (empty($pm->survey->token)) {
                    $pm->survey->update(['token' => Str::random(32)]);
                    $needsReload = true;
                }
            }
            if ($needsReload) {
                $pengajuan->load([
                    'pengajuanMustahiks.survey.hadKifayah',
                    'pengajuanMustahiks.survey.asesmen',
                    'pengajuanMustahiks.survey.lampiranSurvey',
                    'pengajuanMustahiks.survey.lampirans',
                ]);
            }
        }

        $programs = Program::orderBy('nama')->get();
        $subprograms = Subprogram::orderBy('nama')->get();
        $danas = Dana::active()->ordered()->get();
        $asnafs = Asnaf::where('is_active', true)->orderBy('id')->get();
        $pengadaanBarangs = PengadaanBarang::with('barang')->orderBy('tanggal_ba', 'desc')->get();

        $user = Auth::user();
        $roles = $user->person ? $user->person->penguruses()->where('is_active', true)->with('pengurusJabatan')->get()->pluck('pengurusJabatan.nama')->filter()->values()->toArray() : [];
        $isAdmin = in_array('Superuser', $roles) || in_array('Admin BAZNAS', $roles);
        $isPimpinan = in_array('Pimpinan BAZNAS', $roles);
        $isStaff = in_array('Staff Penyaluran', $roles);
        $isUpzSecretary = in_array('Sekretaris', $roles);
        $isUpzChair = in_array('Ketua', $roles);
        $isUpzTreasurer = in_array('Bendahara', $roles);

        return view('pengajuan.show', compact('pengajuan', 'activeTab', 'programs', 'subprograms', 'danas', 'asnafs', 'isAdmin', 'isPimpinan', 'isStaff', 'pengadaanBarangs'));
    }

    protected function redirectToTab($pengajuan, $tab = 'detail', $message = null)
    {
        $id = is_object($pengajuan) ? $pengajuan->id : $pengajuan;
        $isUpz = false;
        if (is_object($pengajuan)) {
            $isUpz = $pengajuan->is_upz || in_array($pengajuan->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']) || $pengajuan->lembaga_id !== null;
        } else {
            $p = PengajuanBantuan::find($id);
            $isUpz = $p ? ($p->is_upz || in_array($p->jenis_permohonan, ['UPZ/Lembaga', 'Lembaga']) || $p->lembaga_id !== null) : false;
        }

        $routeName = $isUpz ? 'pengajuan.upz.show' : 'pengajuan.umum.show';
        $redirect = redirect()->route($routeName, ['pengajuan' => $id, 'tab' => $tab]);
        return $message ? $redirect->with('success', $message) : $redirect;
    }

    // --- Tab 1: Detail & Mustahik Actions ---
    public function storeMustahik(Request $request, $id)
    {
        $pengajuan = PengajuanBantuan::findOrFail($id);

        $hasDisposisi = $pengajuan->pengajuanMustahiks()->where(function($q) {
            $q->whereHas('disposisi')->orWhere(function($sq) {
                $sq->where('nominal', '>', 0)->whereNotNull('sumber_dana_id');
            });
        })->exists() || $pengajuan->persetujuan !== null;

        if ($hasDisposisi) {
            return back()->withErrors(['error' => 'Tidak dapat menambah mustahik karena pengajuan sudah memiliki disposisi.']);
        }

        $request->validate([
            'nama' => 'required_without_all:person_id,lembaga_id|nullable|string|max:255',
            'person_id' => 'nullable|exists:persons,id',
            'lembaga_id' => 'nullable|exists:lembagas,id',
            'nik' => 'nullable|string|max:20',
            'nominal' => 'nullable',
            'keterangan' => 'nullable|string',
            'program_id' => 'required|exists:programs,id',
            'subprogram_id' => 'nullable|exists:subprograms,id',
            'asnaf_id' => 'nullable|exists:asnafs,id',
            'jumlah_jiwa' => 'nullable|integer|min:1',
            'jumlah_kk' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $nama = null;
            if ($request->filled('lembaga_id')) {
                $lembaga = Lembaga::findOrFail($request->lembaga_id);
                $nama = $request->filled('nama') ? $request->nama : $lembaga->nama;
                $mustahik = Mustahik::firstOrCreate(
                    ['lembaga_id' => $request->lembaga_id],
                    ['jenis_mustahik' => 'lembaga']
                );
                if ($mustahik->jenis_mustahik !== 'lembaga') {
                    $mustahik->update(['jenis_mustahik' => 'lembaga']);
                }
            } else {
                $personId = $request->person_id;
                if ($personId) {
                    $person = Person::findOrFail($personId);
                    $nama = $request->filled('nama') ? $request->nama : $person->nama;
                } elseif ($request->filled('nik')) {
                    $person = Person::firstOrCreate(
                        ['nik' => $request->nik],
                        [
                            'nama' => $request->nama,
                            'telepon' => $request->telepon ?? null,
                            'alamat' => $request->alamat ?? null,
                            'jenis_kelamin' => $request->jenis_kelamin ?? 'L'
                        ]
                    );
                    $personId = $person->id;
                    $nama = $request->nama;
                } else {
                    $person = Person::create([
                        'nama' => $request->nama,
                        'nik' => 'TMP' . time() . rand(100, 999),
                        'telepon' => $request->telepon ?? null,
                        'alamat' => $request->alamat ?? null,
                        'jenis_kelamin' => $request->jenis_kelamin ?? 'L'
                    ]);
                    $personId = $person->id;
                    $nama = $request->nama;
                }

                $mustahik = Mustahik::firstOrCreate(
                    ['person_id' => $personId],
                    ['jenis_mustahik' => 'perorangan']
                );
                if ($mustahik->jenis_mustahik !== 'perorangan') {
                    $mustahik->update(['jenis_mustahik' => 'perorangan']);
                }
            }

            $nominal = $request->filled('nominal') ? (int) preg_replace('/\D/', '', $request->nominal) : 0;

            $pmData = [
                'mustahik_id' => $mustahik->id,
                'program_id' => $request->program_id,
                'subprogram_id' => $request->subprogram_id ?: null,
                'asnaf_id' => $request->asnaf_id ?: null,
                'keterangan' => $request->keterangan ?: $pengajuan->judul_surat,
                'nominal' => $nominal,
                'sumber_dana_id' => $request->sumber_dana_id ?? null,
                'bentuk_bantuan' => $request->bentuk_bantuan ?? 'Uang',
                'nama_barang' => $request->nama_barang ?? null,
                'jumlah_barang' => $request->jumlah_barang ?? null,
                'satuan_barang' => $request->satuan_barang ?? null,
                'pengadaan_barang_id' => $request->pengadaan_barang_id ?? null,
                'jumlah_jiwa' => $request->filled('jumlah_jiwa') ? (int)$request->jumlah_jiwa : 1,
                'jumlah_kk' => $request->filled('jumlah_kk') ? (int)$request->jumlah_kk : 1,
            ];

            if (in_array('nama', (new PengajuanMustahik)->getFillable())) {
                $pmData['nama'] = $nama;
            }

            $pengajuan->pengajuanMustahiks()->create($pmData);

            $pengajuan->update([
                'jumlah_orang' => $pengajuan->pengajuanMustahiks()->count(),
            ]);

            DB::commit();
            return $this->redirectToTab($pengajuan, 'detail', 'Data penerima manfaat (mustahik) berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menambah mustahik: ' . $e->getMessage()]);
        }
    }

    public function updateMustahik(Request $request, $id, $pmId)
    {
        $pengajuan = PengajuanBantuan::findOrFail($id);
        $pm = PengajuanMustahik::with(['mustahik.person', 'mustahik.lembaga'])->where('id', $pmId)->where('pengajuan_id', $id)->firstOrFail();

        $hasDisposisi = $pm->disposisi !== null || ($pm->nominal > 0 && $pm->sumber_dana_id !== null) || $pengajuan->persetujuan !== null;
        if ($hasDisposisi) {
            return back()->withErrors(['error' => 'Data mustahik tidak dapat diubah karena sudah memiliki disposisi.']);
        }

        if ($request->filled('sub_program_id') && !$request->filled('subprogram_id')) {
            $request->merge(['subprogram_id' => $request->sub_program_id]);
        }
        if ($request->filled('asnaf') && !$request->filled('asnaf_id')) {
            $request->merge(['asnaf_id' => $request->asnaf]);
        }

        $request->validate([
            'nama'          => 'required|string|max:255',
            'nik'           => 'nullable|string|max:20',
            'asnaf_id'      => 'nullable|exists:asnafs,id',
            'program_id'    => 'required|exists:programs,id',
            'subprogram_id' => 'nullable|exists:subprograms,id',
            'keterangan'    => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            if ($pm->mustahik && $pm->mustahik->person) {
                $person = $pm->mustahik->person;
                $person->nama = $request->nama;
                if ($request->filled('nik')) {
                    if ($request->nik !== $person->nik) {
                        $existing = Person::where('nik', $request->nik)->where('id', '!=', $person->id)->first();
                        if ($existing) {
                            $existing->nama = $request->nama;
                            $existing->save();
                            $targetMustahik = Mustahik::firstOrCreate(['person_id' => $existing->id]);
                            $pm->mustahik_id = $targetMustahik->id;
                        } else {
                            $person->nik = $request->nik;
                            $person->save();
                        }
                    } else {
                        $person->save();
                    }
                } else {
                    $person->save();
                }
            }

            if ($pm->mustahik && $pm->mustahik->lembaga) {
                $pm->mustahik->lembaga->nama = $request->nama;
                $pm->mustahik->lembaga->save();
            }

            if (Schema::hasColumn('pengajuan_mustahiks', 'nama') || $pm->isFillable('nama')) {
                $pm->nama = $request->nama;
            }

            $pm->update([
                'asnaf_id'      => $request->asnaf_id,
                'program_id'    => $request->program_id,
                'subprogram_id' => $request->subprogram_id,
                'keterangan'    => $request->keterangan ?: $pm->keterangan,
                'jumlah_jiwa'   => $request->filled('jumlah_jiwa') ? (int)$request->jumlah_jiwa : 1,
                'jumlah_kk'    => $request->filled('jumlah_kk') ? (int)$request->jumlah_kk : 1,
            ]);

            DB::commit();
            return $this->redirectToTab($pengajuan, 'detail', 'Data mustahik berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui data mustahik: ' . $e->getMessage()]);
        }
    }

    public function destroyMustahik($id, $pmId)
    {
        $pengajuan = PengajuanBantuan::findOrFail($id);
        $pm = PengajuanMustahik::where('id', $pmId)->where('pengajuan_id', $id)->firstOrFail();

        $hasDisposisi = $pm->disposisi !== null || ($pm->nominal > 0 && $pm->sumber_dana_id !== null) || $pengajuan->persetujuan !== null;
        if ($hasDisposisi) {
            return back()->withErrors(['error' => 'Data mustahik tidak dapat dihapus karena sudah memiliki disposisi.']);
        }

        $pm->delete();

        $pengajuan->update([
            'jumlah_orang' => $pengajuan->pengajuanMustahiks()->count(),
        ]);

        return $this->redirectToTab($id, 'detail', 'Data mustahik berhasil dihapus dari pengajuan.');
    }

    public function storeLampiran(Request $request, $id)
    {
        $pengajuan = PengajuanBantuan::findOrFail($id);

        $request->validate([
            'lampiran_nama' => 'nullable|string|max:255',
            'nama'          => 'nullable|string|max:255',
            'lampiran_file' => 'nullable|file|max:10240',
            'file'          => 'nullable|file|max:10240',
            'category'      => 'nullable|string',
            'kategori'      => 'nullable|string',
        ]);

        $file = $request->file('lampiran_file') ?: $request->file('file');
        if (!$file) {
            return back()->withErrors(['error' => 'File lampiran dokumen wajib diunggah.']);
        }

        $name = $request->lampiran_nama ?: ($request->nama ?: 'Lampiran Berkas');
        $category = $request->category ?: ($request->kategori ?: 'pendukung');

        $path = $file->store('lampiran_pengajuan', 'public');
        $pengajuan->lampirans()->create([
            'name'      => $name,
            'category'  => $category,
            'file_path' => $path,
        ]);

        return $this->redirectToTab($pengajuan, 'detail', 'Lampiran dokumen berhasil diunggah.');
    }

    public function destroyLampiran($id, $lampiranId)
    {
        $pengajuan = PengajuanBantuan::findOrFail($id);
        $lampiran = Lampiran::findOrFail($lampiranId);

        // Scope to parent pengajuan: reject attachments belonging to other records.
        if ($lampiran->lampiranable_type !== $pengajuan->getMorphClass()
            || (int) $lampiran->lampiranable_id !== (int) $pengajuan->id) {
            abort(404);
        }

        if ($lampiran->file_path) {
            Storage::disk('public')->delete($lampiran->file_path);
        }
        $lampiran->delete();

        return $this->redirectToTab($pengajuan, 'detail', 'Lampiran berhasil dihapus.');
    }

    public function destroySurat($id)
    {
        $pengajuan = PengajuanBantuan::findOrFail($id);
        if ($pengajuan->file_surat) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($pengajuan->file_surat);
            $pengajuan->update(['file_surat' => null]);
        }

        return $this->redirectToTab($pengajuan, 'detail', 'File surat permohonan utama berhasil dihapus.');
    }

    // --- Action: Tolak Pengajuan ---
    public function tolak($id)
    {
        $pengajuan = PengajuanBantuan::findOrFail($id);
        $pengajuan->update(['ditolak_at' => now()]);

        return $this->redirectToTab($pengajuan, 'detail', 'Pengajuan bantuan telah ditolak.');
    }

    // --- Tab 2: Survey Actions (Legacy delegate to PengajuanSurveyController) ---
    public function storeSurvey(Request $request, $pengajuanId, $mustahikId)
    {
        return app(PengajuanSurveyController::class)->store($request, $pengajuanId, $mustahikId);
    }

    // --- Tab 3: Disposisi & Persetujuan Actions ---
    public function storeDisposisi(Request $request, $pengajuanId, $mustahikId)
    {
        $pm = PengajuanMustahik::where('id', $mustahikId)->where('pengajuan_id', $pengajuanId)->firstOrFail();

        $request->validate([
            'bentuk_bantuan'    => 'required|in:Uang,Barang',
            'nominal_disetujui' => 'required_if:bentuk_bantuan,Uang',
            'nama_barang'       => 'nullable|string|max:255',
            'jumlah_barang'     => 'required_if:bentuk_bantuan,Barang|nullable|integer|min:1',
            'satuan_barang'     => 'required_if:bentuk_bantuan,Barang|nullable|string|max:50',
            'sumber_dana_id'    => 'required|exists:danas,id',
            'instruksi'         => 'required|string',
            'catatan'           => 'nullable|string',
            'diteruskan_kepada' => 'nullable|string',
            'isi_disposisi'     => 'nullable|array',
        ], [
            'nominal_disetujui.required_if' => 'Nominal bantuan wajib diisi jika bentuk bantuan berupa uang.',
            'jumlah_barang.required_if'     => 'Banyak barang / qty wajib diisi jika bentuk bantuan berupa barang.',
            'satuan_barang.required_if'     => 'Satuan barang (unit/buah/paket/dll) wajib diisi.',
        ]);

        $bentukBantuan = $request->bentuk_bantuan ?: 'Uang';
        $nominalDisetujui = ($bentukBantuan === 'Uang')
            ? (int) preg_replace('/\D/', '', $request->nominal_disetujui ?? 0)
            : 0;

        $pm->update([
            'bentuk_bantuan' => $bentukBantuan,
            'nominal'        => $nominalDisetujui,
            'nama_barang'    => ($bentukBantuan === 'Barang') ? $request->nama_barang : null,
            'jumlah_barang'  => ($bentukBantuan === 'Barang') ? (int) $request->jumlah_barang : null,
            'satuan_barang'  => ($bentukBantuan === 'Barang') ? $request->satuan_barang : null,
            'sumber_dana_id' => $request->sumber_dana_id,
        ]);

        PengajuanDisposisi::updateOrCreate(
            ['pengajuan_mustahik_id' => $pm->id],
            [
                'pimpinan_id'       => Auth::id(),
                'instruksi'         => $request->instruksi,
                'diteruskan_kepada' => $request->diteruskan_kepada ?: 'Wakil Ketua II (Pendistribusian & Pendayagunaan)',
                'isi_disposisi'     => $request->isi_disposisi ?? ['Untuk diselesaikan'],
                'catatan'           => $request->catatan,
            ]
        );

        return $this->redirectToTab($pengajuanId, 'persetujuan', 'Disposisi mustahik berhasil disimpan.');
    }

    public function destroyDisposisi($pengajuanId, $mustahikId)
    {
        $pengajuan = PengajuanBantuan::findOrFail($pengajuanId);
        $pm = PengajuanMustahik::where('id', $mustahikId)->where('pengajuan_id', $pengajuanId)->firstOrFail();

        // Cek jika dana sudah disalurkan
        if ($pm->tanggal_penyaluran || ($pengajuan->penyaluran && $pengajuan->penyaluran->is_salur)) {
            return back()->withErrors(['error' => 'Disposisi tidak dapat dibatalkan karena dana sudah disalurkan.']);
        }

        DB::beginTransaction();
        try {
            if ($pm->disposisi) {
                $pm->disposisi->delete();
            }

            $pm->update([
                'bentuk_bantuan' => 'Uang',
                'nominal'        => 0,
                'nama_barang'    => null,
                'jumlah_barang'  => null,
                'satuan_barang'  => null,
                'sumber_dana_id' => null,
            ]);

            // Jika ada persetujuan pimpinan, perbarui total nominalnya
            if ($pengajuan->persetujuan) {
                $totalDisetujui = $pengajuan->pengajuanMustahiks()->sum('nominal');
                $pengajuan->persetujuan->update([
                    'nominal_disetujui' => $totalDisetujui,
                ]);
            }

            DB::commit();
            return $this->redirectToTab($pengajuanId, 'persetujuan', 'Disposisi mustahik berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan disposisi: ' . $e->getMessage()]);
        }
    }

    public function storePersetujuan(Request $request, $id)
    {
        $pengajuan = PengajuanBantuan::with('pengajuanMustahiks')->findOrFail($id);

        // Determine authorization scope based on request route
        $user = Auth::user();
        $isUpzApplication = $pengajuan->jenis_permohonan === 'Lembaga';
        
        // Explicit authorization check based on route (defense in depth)
        if (str_contains($request->path(), 'upz/')) {
            abort_unless($user->canPerformPengajuanAction('approve-upz', $pengajuan), 403);
        } elseif (str_contains($request->path(), 'umum/')) {
            abort_unless($user->canPerformPengajuanAction('approve-umum', $pengajuan), 403);
        } else {
            // Legacy route - allow both UPZ and Umum approval
            abort_unless($user->canPerformPengajuanAction('approve', $pengajuan), 403);
        }

        $request->validate([
            'status'              => 'nullable|in:disetujui,ditolak,tidak_dicairkan',
            'nominal_disetujui'   => 'nullable|numeric|min:0|max:999999999999',
            'referensi_dana'      => 'nullable|array',
            'catatan'             => 'nullable|string|max:1000',
            'selected_items'      => 'nullable|array',
            'selected_items.*'    => 'exists:barangs,id',
            'bentuk_bantuan'      => 'nullable|in:Uang,Barang',
            'nama_barang'         => 'nullable|string|max:255|required_if:bentuk_bantuan,Barang',
            'jumlah_barang'       => 'nullable|integer|min:1|required_if:bentuk_bantuan,Barang',
            'satuan_barang'       => 'nullable|string|max:50|required_if:bentuk_bantuan,Barang',
        ], [
            'selected_items.*.exists' => 'Item yang dipilih tidak valid.',
            'bentuk_bantuan.required_if' => 'Tentukan bentuk bantuan (Uang atau Barang).',
            'nama_barang.required_if' => 'Nama barang wajib diisi jika bentuk bantuan adalah Barang.',
            'jumlah_barang.required_if' => 'Jumlah barang wajib diisi jika bentuk bantuan adalah Barang.',
            'satuan_barang.required_if' => 'Satuan barang wajib diisi jika bentuk bantuan adalah Barang.',
        ]);

        $totalDisetujui = $pengajuan->pengajuanMustahiks->sum('nominal');
        $approvalStatus = $request->status ?: 'disetujui';

        // Validate approval amount if provided
        $finalNominal = $request->nominal_disetujui ?? $totalDisetujui;
        
        // Business logic validation: approval amount cannot exceed total
        if ($approvalStatus === 'disetujui' && $finalNominal > $totalDisetujui) {
            return back()->withErrors([
                'nominal_disetujui' => 'Jumlah persetujuan tidak dapat melebihi total dana yang diajukan.',
                'error' => 'Validasi jumlah persetujuan tidak valid.'
            ]);
        }

        // Handle selected items from modal if provided
        $selectedItems = $request->selected_items ?? [];
        $bentukBantuan = $request->bentuk_bantuan ?: ($selectedItems ? 'Barang' : 'Uang');
        $namaBarang = $request->nama_barang;
        $jumlahBarang = $request->jumlah_barang;
        $satuanBarang = $request->satuan_barang;

        // If items are selected, create or update pengadaan_barang (goods procurement)
        if ($selectedItems) {
            $pengadaanData = [
                'pengajuan_id' => $pengajuan->id,
                'bentuk_bantuan' => $bentukBantuan,
                'nama_barang' => $namaBarang,
                'jumlah_barang' => $jumlahBarang,
                'satuan_barang' => $satuanBarang,
                'sumber_dana_id' => $request->sumber_dana_id ?? null,
                'harga_satuan' => $request->harga_satuan ?? null,
                'catatan' => $request->catatan,
                'created_by' => $user->id,
            ];

            PengadaanBarang::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                $pengadaanData
            );
        }

        PengajuanPersetujuan::updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'tanggal_persetujuan' => now(),
                'status'              => $approvalStatus,
                'nominal_disetujui'   => $finalNominal,
                'referensi_dana'      => $request->referensi_dana,
                'catatan'             => $request->catatan,
                'approved_by'         => $user->id,
            ]
        );

        return $this->redirectToTab($pengajuan, 'persetujuan', 'Persetujuan pimpinan berhasil difinalisasi.');
    }

    // API Endpoint to fetch available products/items for modal selection
    public function getAvailableItems(Request $request)
    {
        $search = $request->input('search', '');
        $kategori = $request->input('kategori');
        $isActive = $request->input('is_active', true);

        $query = Barang::query()
            ->when($isActive !== null, fn($q) => $q->where('is_active', $isActive))
            ->when($kategori, fn($q) => $q->where('kategori', $kategori))
            ->when($search, fn($q) => $q
                ->where(function($q2) use ($search) {
                    $q2->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%")
                        ->orWhere('satuan', 'like', "%{$search}%");
                }));

        $items = $query->orderBy('nama', 'asc')
            ->limit(50)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'kode' => $item->kode,
                    'nama' => $item->nama,
                    'kategori' => $item->kategori,
                    'satuan' => $item->satuan,
                    'stok' => $item->stok,
                    'is_active' => $item->is_active,
                    'display_text' => "{$item->kode} - {$item->nama} ({$item->satuan}) - Stok: {$item->stok}",
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $items->values(),
            'count' => $items->count(),
        ]);
    }

    // --- Tab 4: Penyaluran Actions ---
    public function storeMustahikPenyaluran(Request $request, $pengajuanId, $mustahikId)
    {
        $pengajuan = PengajuanBantuan::findOrFail($pengajuanId);
        $pm = PengajuanMustahik::with('mustahik.person', 'mustahik.lembaga', 'program', 'sumberDana')
            ->where('id', $mustahikId)
            ->where('pengajuan_id', $pengajuanId)
            ->firstOrFail();

        $request->validate([
            'tanggal_penyaluran'  => 'required|date',
            'metode_penyaluran'   => 'required|string',
            'nominal'             => 'nullable',
            'sumber_dana_id'      => 'nullable|exists:danas,id',
            'pengadaan_barang_id' => 'nullable|exists:pengadaan_barangs,id',
            'jumlah_barang'       => 'nullable|integer|min:1',
            'satuan_barang'       => 'nullable|string|max:50',
            'catatan'             => 'nullable|string',
            'bukti_penyaluran'    => 'nullable|file|max:10240',
        ]);

        $metodeEnum = str_contains(strtolower($request->metode_penyaluran), 'transfer') ? 'Transfer'
            : (str_contains(strtolower($request->metode_penyaluran), 'barang') ? 'Barang' : 'Tunai');

        DB::beginTransaction();
        try {
            $catatanPenyaluran = $request->catatan;
            if ($metodeEnum === 'Transfer' && ($request->filled('nama_bank') || $request->filled('no_rekening') || $request->filled('atas_nama'))) {
                $bankParts = [];
                if ($request->filled('nama_bank')) $bankParts[] = 'Bank: ' . $request->nama_bank;
                if ($request->filled('no_rekening')) $bankParts[] = 'No. Rek: ' . $request->no_rekening;
                if ($request->filled('atas_nama')) $bankParts[] = 'a.n: ' . $request->atas_nama;
                $bankStr = implode(' | ', $bankParts);
                $catatanPenyaluran = $catatanPenyaluran ? ($bankStr . "\n" . $catatanPenyaluran) : $bankStr;
            }

            $updateData = [
                'tanggal_penyaluran' => $request->tanggal_penyaluran,
                'metode_penyaluran'  => $metodeEnum,
                'catatan_penyaluran' => $catatanPenyaluran,
            ];

            if ($request->filled('nominal')) {
                $updateData['nominal'] = (int) preg_replace('/\D/', '', $request->nominal);
            }

            if ($request->filled('sumber_dana_id')) {
                $updateData['sumber_dana_id'] = $request->sumber_dana_id;
            }

            if ($pm->bentuk_bantuan === 'Barang') {
                if ($request->filled('pengadaan_barang_id')) {
                    $updateData['pengadaan_barang_id'] = $request->pengadaan_barang_id;
                    $pengadaanSelected = PengadaanBarang::with('barang')->find($request->pengadaan_barang_id);
                    if ($pengadaanSelected && $pengadaanSelected->barang) {
                        $updateData['nama_barang'] = $pengadaanSelected->barang->nama;
                        $updateData['satuan_barang'] = $pengadaanSelected->barang->satuan;
                    }
                }
                if ($request->filled('jumlah_barang')) {
                    $updateData['jumlah_barang'] = (int) $request->jumlah_barang;
                }
                if ($request->filled('satuan_barang') && empty($updateData['satuan_barang'])) {
                    $updateData['satuan_barang'] = $request->satuan_barang;
                }
            }

            $pm->update($updateData);

            // Catat transaksi kas keluar per sumber dana jika ada
            if ($pm->sumber_dana_id && ($pm->nominal ?? 0) > 0) {
                $namaPenerima = $pm->mustahik?->person?->nama ?? $pm->mustahik?->lembaga?->nama ?? $pengajuan->mustahik_nama_snapshot ?? 'Mustahik';
                DanaTransaksi::updateOrCreate(
                    [
                        'referensi_type' => 'App\\Models\\PengajuanMustahik',
                        'referensi_id'   => $pm->id,
                    ],
                    [
                        'dana_id' => $pm->sumber_dana_id,
                        'tanggal' => $request->tanggal_penyaluran,
                        'jenis'   => 'Keluar',
                        'nominal' => $pm->nominal,
                        'uraian'  => 'Penyaluran ' . $namaPenerima . ' (' . ($pm->program->nama ?? '') . ') - #' . $pengajuan->nomor_pengajuan,
                    ]
                );
            }

            // Catat mutasi barang keluar jika bantuan berupa barang
            if ($pm->bentuk_bantuan === 'Barang' && ($pm->jumlah_barang ?? 0) > 0) {
                $namaPenerima = $pm->mustahik?->person?->nama ?? $pm->mustahik?->lembaga?->nama ?? $pengajuan->mustahik_nama_snapshot ?? 'Mustahik';

                $pengadaan = null;
                $barang = null;
                if ($pm->pengadaan_barang_id) {
                    $pengadaan = PengadaanBarang::with('barang')->find($pm->pengadaan_barang_id);
                    $barang = $pengadaan?->barang;
                }

                if (!$barang && $pm->nama_barang) {
                    $namaBarang = trim($pm->nama_barang);
                    $barang = Barang::where('nama', 'LIKE', '%' . $namaBarang . '%')->first();
                }

                if (!$barang) {
                    $barang = Barang::firstOrCreate(
                        ['nama' => $pm->nama_barang ?: 'Barang Bantuan'],
                        [
                            'kode'     => 'BRG-' . strtoupper(Str::random(5)),
                            'satuan'   => $pm->satuan_barang ?: 'Paket',
                            'kategori' => 'Sembako',
                            'stok'     => 0,
                        ]
                    );
                }

                // Kembalikan stok lama jika transaksi sebelumnya sudah pernah tercatat
                $existingBt = BarangTransaksi::where('referensi_type', 'App\\Models\\PengajuanMustahik')
                    ->where('referensi_id', $pm->id)
                    ->first();
                if ($existingBt) {
                    if ($existingBt->barang) {
                        $existingBt->barang->increment('stok', $existingBt->jumlah);
                    }
                }

                $barang->decrement('stok', $pm->jumlah_barang);

                $unitPrice = $pengadaan?->harga_satuan ?? $barang->harga_satuan_terakhir;
                $totalPrice = $unitPrice ? ($unitPrice * $pm->jumlah_barang) : null;
                $uraian = 'Penyaluran ' . $pm->jumlah_barang . ' ' . ($barang->satuan ?? 'unit') . ' ' . $barang->nama;
                if ($pengadaan) {
                    $uraian .= ' (Sesuai BA #' . $pengadaan->nomor_ba . ')';
                }
                $uraian .= ' untuk ' . $namaPenerima . ' - Pengajuan #' . $pengajuan->nomor_pengajuan;

                BarangTransaksi::updateOrCreate(
                    [
                        'referensi_type' => 'App\\Models\\PengajuanMustahik',
                        'referensi_id'   => $pm->id,
                    ],
                    [
                        'barang_id'      => $barang->id,
                        'tanggal'        => $request->tanggal_penyaluran,
                        'jenis'          => 'Keluar',
                        'jumlah'         => $pm->jumlah_barang,
                        'harga_satuan'   => $unitPrice,
                        'harga_total'    => $totalPrice,
                        'nomor_dokumen'  => $pengadaan?->nomor_ba ?? $pengajuan->nomor_pengajuan,
                        'uraian'         => $uraian,
                        'created_by'     => Auth::id(),
                    ]
                );
            }

            if ($request->hasFile('bukti_penyaluran')) {
                $path = $request->file('bukti_penyaluran')->store('lampiran_penyaluran', 'public');
                // Hapus berkas fisik lama jika ada
                if ($pm->buktiPenyaluran && Storage::disk('public')->exists($pm->buktiPenyaluran->file_path)) {
                    Storage::disk('public')->delete($pm->buktiPenyaluran->file_path);
                }
                $pm->buktiPenyaluran()->delete();
                $pm->lampirans()->create([
                    'name'      => 'Bukti Penyaluran - ' . ($pm->mustahik?->person?->nama ?? $pm->mustahik?->lembaga?->nama ?? 'Mustahik'),
                    'category'  => 'bukti_penyaluran',
                    'file_path' => $path,
                ]);
            }

            // Sinkronkan ke rekap induk PengajuanPenyaluran
            $totalDisalurkan = $pengajuan->pengajuanMustahiks()->whereNotNull('tanggal_penyaluran')->sum('nominal');
            PengajuanPenyaluran::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                [
                    'tanggal_penyaluran' => $request->tanggal_penyaluran,
                    'nominal_penyaluran' => $totalDisalurkan,
                    'metode_penyaluran'  => $metodeEnum,
                    'created_by'         => Auth::id(),
                ]
            );

            // Perbarui nominal disetujui di persetujuan jika persetujuan sudah dibuat
            if ($pengajuan->persetujuan) {
                $totalDisetujui = $pengajuan->pengajuanMustahiks()->sum('nominal');
                $pengajuan->persetujuan->update([
                    'nominal_disetujui' => $totalDisetujui,
                ]);
            }

            DB::commit();
            $namaMustahik = $pm->mustahik?->person?->nama ?? $pm->mustahik?->lembaga?->nama ?? 'Mustahik';
            return $this->redirectToTab($pengajuan, 'penyaluran', 'Penyaluran bantuan untuk ' . $namaMustahik . ' berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses penyaluran mustahik: ' . $e->getMessage()]);
        }
    }

    public function destroyMustahikPenyaluran($pengajuanId, $mustahikId)
    {
        $pengajuan = PengajuanBantuan::findOrFail($pengajuanId);
        $pm = PengajuanMustahik::where('id', $mustahikId)->where('pengajuan_id', $pengajuanId)->firstOrFail();

        // Cek jika sudah ada LPJ
        if ($pengajuan->lpjBa) {
            return back()->withErrors(['error' => 'Penyaluran tidak dapat dibatalkan karena dokumen LPJ sudah dibuat. Batalkan LPJ terlebih dahulu jika perlu koreksi.']);
        }

        DB::beginTransaction();
        try {
            // Hapus transaksi kas keluar
            DanaTransaksi::where('referensi_type', 'App\\Models\\PengajuanMustahik')
                ->where('referensi_id', $pm->id)
                ->delete();

            // Hapus mutasi barang keluar jika ada & kembalikan stok
            $bt = BarangTransaksi::where('referensi_type', 'App\\Models\\PengajuanMustahik')
                ->where('referensi_id', $pm->id)
                ->first();
            if ($bt) {
                if ($bt->barang) {
                    $bt->barang->increment('stok', $bt->jumlah);
                }
                $bt->delete();
            }

            $pm->update([
                'tanggal_penyaluran'  => null,
                'metode_penyaluran'   => null,
                'catatan_penyaluran'  => null,
                'pengadaan_barang_id' => null,
            ]);

            // Hapus bukti lampiran
            if ($pm->buktiPenyaluran) {
                if (Storage::disk('public')->exists($pm->buktiPenyaluran->file_path)) {
                    Storage::disk('public')->delete($pm->buktiPenyaluran->file_path);
                }
                $pm->buktiPenyaluran->delete();
            }

            $pm->update([
                'tanggal_penyaluran' => null,
                'metode_penyaluran'  => null,
                'catatan_penyaluran' => null,
            ]);

            // Sinkronkan kembali rekap induk PengajuanPenyaluran
            $totalDisalurkan = $pengajuan->pengajuanMustahiks()->whereNotNull('tanggal_penyaluran')->sum('nominal');
            if ($totalDisalurkan > 0) {
                PengajuanPenyaluran::updateOrCreate(
                    ['pengajuan_id' => $pengajuan->id],
                    [
                        'nominal_penyaluran' => $totalDisalurkan,
                    ]
                );
            } else {
                PengajuanPenyaluran::where('pengajuan_id', $pengajuan->id)->delete();
            }

            DB::commit();
            return $this->redirectToTab($pengajuan, 'penyaluran', 'Penyaluran mustahik berhasil dibatalkan dan transaksi kas keluar telah dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membatalkan penyaluran: ' . $e->getMessage()]);
        }
    }

    public function storePenyaluran(Request $request, $id)
    {
        $pengajuan = PengajuanBantuan::with('pengajuanMustahiks.mustahik.person', 'pengajuanMustahiks.mustahik.lembaga', 'pengajuanMustahiks.program', 'pengajuanMustahiks.sumberDana')->findOrFail($id);

        $request->validate([
            'tanggal_penyaluran' => 'required|date',
            'metode_penyaluran'  => 'required|string',
            'sumber_dana_id'     => 'nullable|exists:danas,id',
            'catatan'            => 'nullable|string',
            'bukti_penyaluran'   => 'nullable|file|max:10240',
        ]);

        $metodeEnum = str_contains(strtolower($request->metode_penyaluran), 'transfer') ? 'Transfer'
            : (str_contains(strtolower($request->metode_penyaluran), 'barang') ? 'Barang' : 'Tunai');

        DB::beginTransaction();
        try {
            $catatanBatch = $request->catatan;
            $pathBukti = null;
            if ($request->hasFile('bukti_penyaluran')) {
                $pathBukti = $request->file('bukti_penyaluran')->store('lampiran_penyaluran', 'public');
            }

            // Ambil mustahik yang belum disalurkan dengan nominal > 0 atau bentuk_bantuan == 'Barang'
            $pendingMustahiks = $pengajuan->pengajuanMustahiks->filter(fn($m) => is_null($m->tanggal_penyaluran) && (($m->nominal ?? 0) > 0 || $m->bentuk_bantuan === 'Barang'));
            $targets = $pendingMustahiks->isNotEmpty() ? $pendingMustahiks : $pengajuan->pengajuanMustahiks;

            foreach ($targets as $pm) {
                $sumberId = $request->filled('sumber_dana_id') ? $request->sumber_dana_id : $pm->sumber_dana_id;
                $pm->update([
                    'sumber_dana_id'     => $sumberId,
                    'tanggal_penyaluran' => $request->tanggal_penyaluran,
                    'metode_penyaluran'  => $metodeEnum,
                    'catatan_penyaluran' => $catatanBatch ?: $pm->catatan_penyaluran,
                ]);

                // Catat transaksi kas keluar per sumber dana jika ada
                if ($pm->sumber_dana_id && ($pm->nominal ?? 0) > 0) {
                    $namaPenerima = $pm->mustahik?->person?->nama ?? $pm->mustahik?->lembaga?->nama ?? $pengajuan->mustahik_nama_snapshot ?? 'Mustahik';
                    DanaTransaksi::updateOrCreate(
                        [
                            'referensi_type' => 'App\\Models\\PengajuanMustahik',
                            'referensi_id'   => $pm->id,
                        ],
                        [
                            'dana_id' => $pm->sumber_dana_id,
                            'tanggal' => $request->tanggal_penyaluran,
                            'jenis'   => 'Keluar',
                            'nominal' => $pm->nominal,
                            'uraian'  => 'Penyaluran ' . $namaPenerima . ' (' . ($pm->program->nama ?? '') . ') - #' . $pengajuan->nomor_pengajuan,
                        ]
                    );
                }

                // Catat mutasi barang keluar jika bantuan berupa barang
                if ($pm->bentuk_bantuan === 'Barang' && ($pm->jumlah_barang ?? 0) > 0) {
                    $namaPenerima = $pm->mustahik?->person?->nama ?? $pm->mustahik?->lembaga?->nama ?? $pengajuan->mustahik_nama_snapshot ?? 'Mustahik';
                    $namaBarang = trim($pm->nama_barang);
                    $barang = Barang::where('nama', 'LIKE', '%' . $namaBarang . '%')->first();
                    if (!$barang) {
                        $barang = Barang::firstOrCreate(
                            ['nama' => $namaBarang ?: 'Barang Bantuan'],
                            [
                                'kode'     => 'BRG-' . strtoupper(Str::random(5)),
                                'satuan'   => $pm->satuan_barang ?: 'Paket',
                                'kategori' => 'Sembako',
                                'stok'     => 0,
                            ]
                        );
                    }

                    $barang->decrement('stok', $pm->jumlah_barang);

                    BarangTransaksi::updateOrCreate(
                        [
                            'referensi_type' => 'App\\Models\\PengajuanMustahik',
                            'referensi_id'   => $pm->id,
                        ],
                        [
                            'barang_id'      => $barang->id,
                            'tanggal'        => $request->tanggal_penyaluran,
                            'jenis'          => 'Keluar',
                            'jumlah'         => $pm->jumlah_barang,
                            'nomor_dokumen'  => $pengajuan->nomor_pengajuan,
                            'uraian'         => 'Penyaluran barang bantuan untuk ' . $namaPenerima . ' - Pengajuan #' . $pengajuan->nomor_pengajuan,
                            'created_by'     => Auth::id(),
                        ]
                    );
                }

                if ($pathBukti) {
                    $pm->lampirans()->create([
                        'name'      => 'Bukti Penyaluran Serentak - ' . ($pm->mustahik?->person?->nama ?? 'Mustahik'),
                        'category'  => 'bukti_penyaluran',
                        'file_path' => $pathBukti,
                    ]);
                }
            }

            $totalDisalurkan = $pengajuan->pengajuanMustahiks()->whereNotNull('tanggal_penyaluran')->sum('nominal');

            $penyaluran = PengajuanPenyaluran::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                [
                    'tanggal_penyaluran' => $request->tanggal_penyaluran,
                    'nominal_penyaluran' => $totalDisalurkan,
                    'metode_penyaluran'  => $metodeEnum,
                    'catatan'            => $catatanBatch,
                    'created_by'         => Auth::id(),
                ]
            );

            if ($pathBukti) {
                $penyaluran->lampirans()->create([
                    'name'      => 'Bukti Penyaluran Serentak',
                    'category'  => 'bukti_penyaluran',
                    'file_path' => $pathBukti,
                ]);
            }

            DB::commit();
            return $this->redirectToTab($pengajuan, 'penyaluran', 'Seluruh bantuan mustahik berhasil disalurkan dan kas telah dipotong.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses penyaluran serentak: ' . $e->getMessage()]);
        }
    }

    // --- Tab 5: LPJ & Berita Acara Actions ---
    public function storeLpj(Request $request, $id)
    {
        $pengajuan = PengajuanBantuan::findOrFail($id);

        $request->validate([
            'tanggal_lpj'       => 'required|date',
            'nominal_realisasi' => 'nullable',
            'nominal_sisa'      => 'nullable',
            'sisa_dikembalikan' => 'nullable|boolean',
            'catatan'           => 'nullable|string',
            'file_lpj'          => 'nullable|file|max:10240',
        ]);

        $nominalRealisasi = $request->filled('nominal_realisasi')
            ? (int) preg_replace('/\D/', '', $request->nominal_realisasi)
            : $pengajuan->pengajuanMustahiks->sum('nominal');
        $nominalSisa = (int) preg_replace('/\D/', '', $request->nominal_sisa ?? '0');

        DB::beginTransaction();
        try {
            $lpj = PengajuanLpjBa::updateOrCreate(
                ['pengajuan_id' => $pengajuan->id],
                [
                    'tanggal_lpj'       => $request->tanggal_lpj,
                    'nominal_realisasi' => $nominalRealisasi,
                    'nominal_sisa'      => $nominalSisa,
                    'sisa_dikembalikan' => $request->has('sisa_dikembalikan') ? 1 : 0,
                    'catatan'           => $request->catatan,
                    'created_by'        => Auth::id(),
                ]
            );

            if ($request->hasFile('file_lpj')) {
                $path = $request->file('file_lpj')->store('lampiran_lpj', 'public');
                $lpj->lampirans()->create([
                    'name'      => 'Dokumen Berita Acara & LPJ',
                    'category'  => 'lpj_ba',
                    'file_path' => $path,
                ]);
            }

            if ($request->hasFile('file_dokumentasi')) {
                $path = $request->file('file_dokumentasi')->store('lampiran_lpj', 'public');
                $lpj->lampirans()->create([
                    'name'      => 'Foto Dokumentasi Serah Terima',
                    'category'  => 'lpj_dokumentasi',
                    'file_path' => $path,
                ]);
            }

            DB::commit();
            return $this->redirectToTab($pengajuan, 'lpj', 'Laporan Pertanggungjawaban (LPJ) & Berita Acara berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan LPJ: ' . $e->getMessage()]);
        }
    }
}
