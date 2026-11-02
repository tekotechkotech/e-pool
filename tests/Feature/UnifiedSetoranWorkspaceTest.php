<?php

namespace Tests\Feature;

use App\Models\Dana;
use App\Models\DanaTransaksi;
use App\Models\Muzakki;
use App\Models\Person;
use App\Models\SetoranUpz;
use App\Models\SetoranUpzItem;
use App\Models\Upz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class UnifiedSetoranWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Upz $upz;

    protected function setUp(): void
    {
        parent::setUp();

        $person = Person::create([
            'nama' => 'Staff Pengumpulan QA',
            'nik' => '3301998877660002',
        ]);

        $this->user = User::create([
            'person_id' => $person->id,
            'email' => 'qa_workspace@baznas.go.id',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->user);

        $this->upz = Upz::create([
            'nama' => 'UPZ Masjid Raya Al-Hikmah',
            'kode_simba' => '9991',
            'is_upz' => true,
            'is_active' => true,
        ]);
    }

    /**
     * 1. Search UPZ Asynchronous:
     * - Verifikasi bahwa input pencarian dengan < 4 huruf tanpa spasi tidak mengembalikan UPZ / mengembalikan array kosong.
     * - Verifikasi bahwa input pencarian dengan >= 4 huruf (misal: "Al-Hikmah" atau "Masjid" atau "Dinas") mengembalikan daftar UPZ yang sesuai.
     */
    public function test_search_upz_asynchronous_length_rules(): void
    {
        $upz2 = Upz::create([
            'nama' => 'UPZ Dinas Pendidikan Cilacap',
            'kode_simba' => '9992',
            'is_upz' => true,
            'is_active' => true,
        ]);

        $upzInactive = Upz::create([
            'nama' => 'UPZ Dinas Perhubungan Nonaktif',
            'kode_simba' => '9993',
            'is_upz' => true,
            'is_active' => false,
        ]);

        // Uji query < 4 huruf tanpa spasi: harus mengembalikan array kosong []
        $res1 = $this->getJson(route('pengumpulan.upz.search-upz', ['q' => 'Al']));
        $res1->assertStatus(200);
        $res1->assertJson([]);

        $res2 = $this->getJson(route('pengumpulan.upz.search-upz', ['q' => '   UP   ']));
        $res2->assertStatus(200);
        $res2->assertJson([]);

        $res3 = $this->getJson(route('pengumpulan.upz.search-upz', ['q' => 'Mas']));
        $res3->assertStatus(200);
        $res3->assertJson([]);

        // Uji query >= 4 huruf tanpa spasi: mengembalikan daftar UPZ yang sesuai
        $resHikmah = $this->getJson(route('pengumpulan.upz.search-upz', ['q' => 'Hikmah']));
        $resHikmah->assertStatus(200);
        $dataHikmah = $resHikmah->json();
        $this->assertCount(1, $dataHikmah);
        $this->assertEquals($this->upz->id, $dataHikmah[0]['id']);
        $this->assertEquals('UPZ Masjid Raya Al-Hikmah', $dataHikmah[0]['nama']);

        $resMasjid = $this->getJson(route('pengumpulan.upz.search-upz', ['q' => 'Masjid']));
        $resMasjid->assertStatus(200);
        $dataMasjid = $resMasjid->json();
        $this->assertCount(1, $dataMasjid);
        $this->assertEquals($this->upz->id, $dataMasjid[0]['id']);

        $resDinas = $this->getJson(route('pengumpulan.upz.search-upz', ['q' => 'Dinas']));
        $resDinas->assertStatus(200);
        $dataDinas = $resDinas->json();
        // Hanya UPZ aktif yang dikembalikan
        $this->assertCount(1, $dataDinas);
        $this->assertEquals($upz2->id, $dataDinas[0]['id']);
        $this->assertEquals('UPZ Dinas Pendidikan Cilacap', $dataDinas[0]['nama']);

        // Uji pencarian berdasarkan kode SIMBA (>= 4 huruf)
        $resSimba = $this->getJson(route('pengumpulan.upz.search-upz', ['q' => '9991']));
        $resSimba->assertStatus(200);
        $dataSimba = $resSimba->json();
        $this->assertCount(1, $dataSimba);
        $this->assertEquals($this->upz->id, $dataSimba[0]['id']);
    }

    /**
     * 2. Instant Inline Excel Parser:
     * - Verifikasi endpoint POST /pengumpulan/upz/parse-inline-excel membaca file spreadsheet XLSX dan CSV.
     * - Mengembalikan JSON items yang terklasifikasi (terdaftar, nihil, belum_terdaftar).
     */
    public function test_parse_inline_excel_xlsx_and_classifies_items(): void
    {
        $person1 = Person::create(['nama' => 'Muzakki Terdaftar Berbayar', 'nik' => '3301090909090001']);
        $m1 = Muzakki::create([
            'person_id' => $person1->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-PARSER-001',
        ]);

        $person2 = Person::create(['nama' => 'Muzakki Terdaftar Nihil', 'nik' => '3301090909090002']);
        $m2 = Muzakki::create([
            'person_id' => $person2->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-PARSER-002',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['ID Muzakki', 'NPWZ', 'Nama Lengkap', 'NIK', 'Zakat (Rp)', 'Infak (Rp)', 'Fitrah (Rp)', 'Titipan (Rp)', 'Keterangan'],
            // Row 1: Terdaftar berbayar
            [$m1->id, $m1->npwz, 'Muzakki Terdaftar Berbayar', '3301090909090001', 500000, 50000, 0, 0, 'Setoran Reguler'],
            // Row 2: Terdaftar nihil (nominal 0)
            [$m2->id, $m2->npwz, 'Muzakki Terdaftar Nihil', '3301090909090002', 0, 0, 0, 0, 'Laporan Nihil'],
            // Row 3: Belum terdaftar (orang baru)
            ['', '', 'Muzakki Baru Belum Terdaftar', '3301090909090003', 250000, 25000, 0, 0, 'Muzakki Lepas'],
            // Spare empty rows (must be skipped)
            ['', '', '', '', 0, 0, 0, 0, ''],
            ['', '', '', '', 0, 0, 0, 0, ''],
        ]);

        $tempXlsx = tempnam(sys_get_temp_dir(), 'test_parse_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempXlsx);

        $uploaded = new UploadedFile($tempXlsx, 'inline_setoran.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->post(route('pengumpulan.upz.parse-inline-excel'), [
            'file' => $uploaded,
            'lembaga_id' => $this->upz->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total_rows' => 3,
        ]);

        $items = $response->json('items');
        $this->assertCount(3, $items);

        // Row 1: terdaftar
        $this->assertEquals($m1->id, $items[0]['muzakki_id']);
        $this->assertEquals('terdaftar', $items[0]['status']);
        $this->assertEquals(500000, $items[0]['nominal_zakat']);
        $this->assertEquals(50000, $items[0]['nominal_infak']);
        $this->assertEquals(550000, $items[0]['total']);

        // Row 2: nihil
        $this->assertEquals($m2->id, $items[1]['muzakki_id']);
        $this->assertEquals('nihil', $items[1]['status']);
        $this->assertEquals(0, $items[1]['total']);

        // Row 3: belum_terdaftar
        $this->assertNull($items[2]['muzakki_id']);
        $this->assertEquals('belum_terdaftar', $items[2]['status']);
        $this->assertEquals(275000, $items[2]['total']);

        if (file_exists($tempXlsx)) {
            unlink($tempXlsx);
        }
    }

    public function test_parse_inline_excel_csv_format(): void
    {
        $person = Person::create(['nama' => 'Muzakki CSV Parser', 'nik' => '3301090909090004']);
        $m = Muzakki::create([
            'person_id' => $person->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-PARSER-CSV',
        ]);

        $csvContent = implode("\n", [
            'ID Muzakki,NPWZ,Nama Lengkap,NIK,Zakat (Rp),Infak (Rp),Fitrah (Rp),Titipan (Rp),Keterangan',
            "{$m->id},{$m->npwz},Muzakki CSV Parser,3301090909090004,1000000,100000,0,0,Via CSV",
            ',,Muzakki CSV Belum Terdaftar,3301090909090005,50000,0,0,0,Orang Baru',
        ]);

        $tempCsv = tempnam(sys_get_temp_dir(), 'test_inline_csv_') . '.csv';
        file_put_contents($tempCsv, $csvContent);

        $uploaded = new UploadedFile($tempCsv, 'inline_setoran.csv', 'text/csv', null, true);

        $response = $this->post(route('pengumpulan.upz.parse-inline-excel'), [
            'file' => $uploaded,
            'lembaga_id' => $this->upz->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total_rows' => 2,
        ]);

        $items = $response->json('items');
        $this->assertCount(2, $items);
        $this->assertEquals('terdaftar', $items[0]['status']);
        $this->assertEquals(1100000, $items[0]['total']);
        $this->assertEquals('belum_terdaftar', $items[1]['status']);
        $this->assertEquals(50000, $items[1]['total']);

        if (file_exists($tempCsv)) {
            unlink($tempCsv);
        }
    }

    public function test_parse_inline_excel_validation_error(): void
    {
        $response = $this->postJson(route('pengumpulan.upz.parse-inline-excel'), []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    /**
     * 3. Penyimpanan 1x Eksekusi di POST /pengumpulan/upz:
     * - Verifikasi penyimpanan file bukti_setor (JPG/PNG/PDF) ke storage public/bukti_setor.
     * - Verifikasi penyimpanan item muzakki nominal > 0 dan nominal 0 di setoran_upz_items.
     * - Verifikasi pengisolasian kas: DanaTransaksi hanya mencatat nominal > 0, tidak ada jurnal Rp 0.
     */
    public function test_store_saves_bukti_setor_items_and_isolates_zero_kas(): void
    {
        Storage::fake('public');

        $danaZakat = Dana::firstOrCreate(['kode' => '2.2'], ['nama' => 'Zakat Maal via UPZ', 'kategori' => 'Zakat']);
        $danaInfak = Dana::firstOrCreate(['kode' => '2.7'], ['nama' => 'Infak via UPZ', 'kategori' => 'Infak/Sedekah']);

        $person1 = Person::create(['nama' => 'Muzakki Bayar', 'nik' => '3301050505050010']);
        $m1 = Muzakki::create([
            'person_id' => $person1->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-STORE-001',
        ]);

        $person2 = Person::create(['nama' => 'Muzakki Nihil', 'nik' => '3301050505050011']);
        $m2 = Muzakki::create([
            'person_id' => $person2->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-STORE-002',
        ]);

        $fakeBukti = UploadedFile::fake()->image('bukti_transfer_bank.jpg', 600, 600);

        $payload = [
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'bulan' => 'November',
            'tahun' => '2026',
            'keterangan' => 'Setoran terpadu 1-click dengan bukti fisik transfer',
            'bukti_setor' => $fakeBukti,
            'items' => [
                [
                    'muzakki_id' => $m1->id,
                    'nama' => 'Muzakki Bayar',
                    'npwz' => 'NPWZ-STORE-001',
                    'nominal_zakat' => 750000,
                    'nominal_infak' => 150000,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                    'keterangan' => 'Zakat & Infak Profesi',
                ],
                [
                    'muzakki_id' => $m2->id,
                    'nama' => 'Muzakki Nihil',
                    'npwz' => 'NPWZ-STORE-002',
                    'nominal_zakat' => 0,
                    'nominal_infak' => 0,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                    'keterangan' => 'Nihil bulan ini',
                ],
            ],
        ];

        $response = $this->post(route('pengumpulan.upz.store'), $payload);

        $response->assertRedirect(route('pengumpulan.upz.index'));
        $response->assertSessionHas('success');

        // Verifikasi tabel setoran_upzs
        $setoran = SetoranUpz::where('lembaga_id', $this->upz->id)->first();
        $this->assertNotNull($setoran);
        $this->assertEquals(750000, $setoran->total_zakat);
        $this->assertEquals(150000, $setoran->total_infak);
        $this->assertEquals(900000, $setoran->total_jumlah);
        $this->assertNotNull($setoran->bukti_setor);
        $this->assertTrue(str_starts_with($setoran->bukti_setor, 'bukti_setor/'));

        // Verifikasi fisik file bukti setor tersimpan di storage public
        Storage::disk('public')->assertExists($setoran->bukti_setor);

        // Verifikasi setoran_upz_items (keduanya tersimpan, termasuk yang nominal 0)
        $items = SetoranUpzItem::where('setoran_upz_id', $setoran->id)->get();
        $this->assertCount(2, $items);

        $itemBayar = $items->firstWhere('muzakki_id', $m1->id);
        $this->assertEquals(750000, $itemBayar->nominal_zakat);
        $this->assertEquals(150000, $itemBayar->nominal_infak);
        $this->assertEquals(900000, $itemBayar->jumlah);

        $itemNihil = $items->firstWhere('muzakki_id', $m2->id);
        $this->assertEquals(0, $itemNihil->nominal_zakat);
        $this->assertEquals(0, $itemNihil->nominal_infak);
        $this->assertEquals(0, $itemNihil->jumlah);

        // Verifikasi pengisolasian kas: DanaTransaksi HANYA mencatat nominal > 0
        $kasTransaksis = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->get();

        $this->assertCount(2, $kasTransaksis);
        $this->assertTrue($kasTransaksis->contains(fn($t) => $t->nominal == 750000 && $t->dana_id == $danaZakat->id));
        $this->assertTrue($kasTransaksis->contains(fn($t) => $t->nominal == 150000 && $t->dana_id == $danaInfak->id));
        $this->assertFalse($kasTransaksis->contains(fn($t) => $t->nominal == 0));
    }

    public function test_store_ajax_wants_json_returns_json_response(): void
    {
        Storage::fake('public');

        Dana::firstOrCreate(['kode' => '2.2'], ['nama' => 'Zakat Maal via UPZ', 'kategori' => 'Zakat']);
        Dana::firstOrCreate(['kode' => '2.7'], ['nama' => 'Infak via UPZ', 'kategori' => 'Infak/Sedekah']);

        $payload = [
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'periode' => 'November 2026',
            'keterangan' => 'JSON Submission',
            'items' => [
                [
                    'nama' => 'Muzakki JSON Test',
                    'nominal_zakat' => 300000,
                    'nominal_infak' => 50000,
                ],
            ],
        ];

        $response = $this->postJson(route('pengumpulan.upz.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'redirect_url' => route('pengumpulan.upz.index'),
        ]);
    }

    /**
     * 4. Upload / Ganti Bukti Transfer Susulan:
     * - Verifikasi endpoint POST /pengumpulan/upz/{id}/upload-bukti berhasil mengganti berkas bukti setor dan menghapus file lama di storage.
     */
    public function test_upload_bukti_susulan_replaces_old_file_and_updates_database(): void
    {
        Storage::fake('public');

        // Buat file bukti awal
        $oldFile = UploadedFile::fake()->image('bukti_lama.jpg');
        $oldPath = $oldFile->store('bukti_setor', 'public');
        Storage::disk('public')->assertExists($oldPath);

        $setoran = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0088',
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'periode' => 'November 2026',
            'total_zakat' => 500000,
            'total_infak' => 100000,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 600000,
            'bukti_setor' => $oldPath,
            'status' => 'Terverifikasi',
            'user_id' => $this->user->id,
        ]);

        // Upload berkas bukti baru (misal PDF)
        $newPdf = UploadedFile::fake()->create('bukti_baru.pdf', 200, 'application/pdf');

        $response = $this->post(route('pengumpulan.upz.upload-bukti', $setoran->id), [
            'bukti_setor' => $newPdf,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $setoran->refresh();
        $this->assertNotEquals($oldPath, $setoran->bukti_setor);
        $this->assertTrue(str_starts_with($setoran->bukti_setor, 'bukti_setor/'));

        // File lama wajib terhapus dari storage
        Storage::disk('public')->assertMissing($oldPath);

        // File baru wajib tersimpan di storage
        Storage::disk('public')->assertExists($setoran->bukti_setor);
    }

    public function test_destroy_setoran_deletes_physical_bukti_setor_file(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('bukti_to_delete.jpg');
        $path = $oldFile->store('bukti_setor', 'public');
        Storage::disk('public')->assertExists($path);

        $setoran = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0099',
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'periode' => 'November 2026',
            'total_zakat' => 100000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 100000,
            'bukti_setor' => $path,
            'status' => 'Terverifikasi',
            'user_id' => $this->user->id,
        ]);

        $response = $this->delete(route('pengumpulan.upz.destroy', $setoran->id));
        $response->assertRedirect(route('pengumpulan.upz.index'));

        // Berkas bukti setor harus ikut terhapus dari storage
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('setoran_upzs', ['id' => $setoran->id]);
    }

    /**
     * 5. Verifikasi Render Tampilan (Smoke Test):
     * - Halaman create.blade.php merender elemen-elemen workspace terpadu.
     * - Halaman index.blade.php merender badge status bukti transfer dan tidak ada confirm() JS native.
     * - Halaman show.blade.php merender komponen bukti transfer dan modal upload.
     */
    public function test_create_blade_smoke_test_renders_unified_workspace_elements(): void
    {
        $response = $this->get(route('pengumpulan.upz.create'));

        $response->assertStatus(200);
        // Alpine data handler
        $response->assertSee('x-data="setoranWorkspace()"', false);
        // Asynchronous UPZ search
        $response->assertSee('searchUpz()', false);
        $response->assertSee('search-upz', false);
        // Drag-and-drop Bukti Transfer
        $response->assertSee('name="bukti_setor"', false);
        $response->assertSee('handleDropBukti', false);
        $response->assertSee('handleFileBukti', false);
        // Inline Excel Dropzone
        $response->assertSee('handleDropExcel', false);
        $response->assertSee('handleFileExcel', false);
        $response->assertSee('parse-inline-excel', false);
        // Bulk Actions & Ramadhan Mode Toggle
        $response->assertSee('applyBulkNominal()', false);
        $response->assertSee('markAllNihil()', false);
        $response->assertSee('showFitrahTitipan', false);
        // Form multipart
        $response->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_show_blade_smoke_test_renders_bukti_transfer_section_and_modal(): void
    {
        $setoran = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0077',
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'periode' => 'November 2026',
            'total_zakat' => 500000,
            'total_infak' => 100000,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 600000,
            'bukti_setor' => null,
            'status' => 'Terverifikasi',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get(route('pengumpulan.upz.show', $setoran->id));

        $response->assertStatus(200);
        $response->assertSee('Bukti Transfer / Setoran');
        $response->assertSee('Belum Diunggah');
        $response->assertSee('Unggah Bukti Transfer');
        $response->assertSee(route('pengumpulan.upz.upload-bukti', $setoran->id));
    }

    public function test_index_blade_smoke_test_renders_badges_and_no_native_confirm(): void
    {
        SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0001',
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'periode' => 'November 2026',
            'total_zakat' => 500000,
            'total_infak' => 100000,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 600000,
            'bukti_setor' => 'bukti_setor/test.jpg',
            'status' => 'Terverifikasi',
            'user_id' => $this->user->id,
        ]);

        SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0002',
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'periode' => 'November 2026',
            'total_zakat' => 200000,
            'total_infak' => 50000,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 250000,
            'bukti_setor' => null,
            'status' => 'Terverifikasi',
            'user_id' => $this->user->id,
        ]);

        $response = $this->get(route('pengumpulan.upz.index'));

        $response->assertStatus(200);
        $response->assertSee('Ada Bukti');
        $response->assertSee('Tanpa Bukti');
        // Pastikan tidak ada dialog confirm() native JavaScript
        $response->assertDontSee('return confirm(');
        $response->assertDontSee('window.confirm(');
        $response->assertDontSee('onsubmit="return confirm');
    }
}
