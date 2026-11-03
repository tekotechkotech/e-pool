<?php

namespace Tests\Feature;

use App\Exports\SetoranUpzTemplateExport;
use App\Models\Dana;
use App\Models\DanaTransaksi;
use App\Models\Lembaga;
use App\Models\Muzakki;
use App\Models\Person;
use App\Models\SetoranUpz;
use App\Models\SetoranUpzItem;
use App\Models\Upz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SetoranUpzImportDanAutoLoadTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Upz $upz;

    protected function setUp(): void
    {
        parent::setUp();

        $person = Person::create([
            'nama' => 'Staff Pengumpulan Test',
            'nik' => '3301998877660001',
        ]);

        $this->user = User::create([
            'person_id' => $person->id,
            'email' => 'pengumpulan_tester@baznas.go.id',
            'password' => bcrypt('password'),
        ]);

        $pj = \App\Models\PengurusJabatan::firstOrCreate(['nama' => 'Admin BAZNAS'], ['kode' => 'ADM-BAZNAS', 'tingkat' => 'baznas', 'is_active' => true]);
        \App\Models\Pengurus::create([
            'jenis_pengurus' => 'Baznas',
            'person_id' => $person->id,
            'pengurus_jabatan_id' => $pj->id,
            'lembaga_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($this->user);

        $this->upz = Upz::create([
            'nama' => 'UPZ Masjid Raya Al-Hikmah',
            'kode_simba' => '999',
            'is_upz' => true,
            'is_active' => true,
        ]);
    }

    public function test_autoload_muzakki_by_upz(): void
    {
        $person1 = Person::create(['nama' => 'Ahmad Muzakki', 'nik' => '3301010101010001']);
        $person2 = Person::create(['nama' => 'Budi Munfiq', 'nik' => '3301010101010002']);

        $m1 = Muzakki::create([
            'person_id' => $person1->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-999-001',
        ]);

        $m2 = Muzakki::create([
            'person_id' => $person2->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-999-002',
        ]);

        $response = $this->getJson(route('pengumpulan.upz.muzakki-by-upz', $this->upz->id));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(2, $data);
        $this->assertEquals('Ahmad Muzakki', $data[0]['nama']);
        $this->assertEquals('3301010101010001', $data[0]['nik']);
        $this->assertEquals('NPWZ-999-001', $data[0]['npwz']);
        $this->assertEquals($m1->id, $data[0]['id']);
    }

    public function test_download_template_export(): void
    {
        $person = Person::create(['nama' => 'Siti Muzakki', 'nik' => '3301010101010003']);
        Muzakki::create([
            'person_id' => $person->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-999-003',
        ]);

        $export = new SetoranUpzTemplateExport($this->upz->id);
        $headings = $export->headings();
        $this->assertEquals([
            'ID Muzakki', 'NPWZ', 'Nama Lengkap', 'NIK', 'Zakat (Rp)', 'Infak (Rp)', 'Fitrah (Rp)', 'Titipan (Rp)', 'Keterangan'
        ], $headings);

        $rows = $export->array();
        // 1 existing muzakki + 5 empty rows = 6 rows
        $this->assertCount(6, $rows);
        $this->assertEquals('Siti Muzakki', $rows[0][2]);
        $this->assertEquals('3301010101010003', $rows[0][3]);

        $response = $this->get(route('pengumpulan.upz.template', $this->upz->id));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'template_setoran_upz_'));
    }

    public function test_quick_store_muzakki_via_ajax(): void
    {
        $payload = [
            'nama' => 'Haji Salim',
            'nik' => '3301020202020004',
            'lembaga_id' => $this->upz->id,
            'jenis_kelamin' => 'L',
            'npwz' => 'NPWZ-NEW-004',
            'handphone' => '081234567899',
            'alamat' => 'Cilacap Tengah',
        ];

        $response = $this->postJson(route('pengumpulan.upz.quick-store-muzakki'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'muzakki' => [
                'nama' => 'Haji Salim',
                'nik' => '3301020202020004',
                'npwz' => 'NPWZ-NEW-004',
            ],
        ]);

        $this->assertDatabaseHas('persons', [
            'nama' => 'Haji Salim',
            'nik' => '3301020202020004',
            'jenis_kelamin' => 'L',
        ]);

        $this->assertDatabaseHas('muzakkis', [
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-NEW-004',
        ]);
    }

    public function test_import_preview_classifies_terdaftar_nihil_and_belum_terdaftar(): void
    {
        $person1 = Person::create(['nama' => 'Muzakki Bayar', 'nik' => '3301030303030005']);
        $m1 = Muzakki::create([
            'person_id' => $person1->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-BAYAR-001',
        ]);

        $person2 = Person::create(['nama' => 'Muzakki Nihil', 'nik' => '3301030303030006']);
        $m2 = Muzakki::create([
            'person_id' => $person2->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-NIHIL-002',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['ID Muzakki', 'NPWZ', 'Nama Lengkap', 'NIK', 'Zakat (Rp)', 'Infak (Rp)', 'Fitrah (Rp)', 'Titipan (Rp)', 'Keterangan'],
            [$m1->id, $m1->npwz, $person1->nama, $person1->nik, 250000, 50000, 0, 0, 'Setoran Tunai'],
            [$m2->id, $m2->npwz, $person2->nama, $person2->nik, 0, 0, 0, 0, 'Laporan Nihil'],
            ['', '', 'Calon Muzakki Lepas', '3301030303030007', 100000, 0, 0, 0, 'Belum Masuk Master'],
            ['', '', '', '', 0, 0, 0, 0, ''], // Blank row to skip
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_upz_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempFile);

        $uploaded = new UploadedFile($tempFile, 'setoran.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->post(route('pengumpulan.upz.import-preview'), [
            'file' => $uploaded,
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'bulan' => 'November',
            'tahun' => '2026',
            'keterangan' => 'Preview Test Batch',
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('pengumpulan.upz.import-preview');

        $items = $response->viewData('items');
        $this->assertCount(3, $items);

        $this->assertEquals('terdaftar', $items[0]['status']);
        $this->assertEquals(300000, $items[0]['total']);

        $this->assertEquals('nihil', $items[1]['status']);
        $this->assertEquals(0, $items[1]['total']);

        $this->assertEquals('belum_terdaftar', $items[2]['status']);
        $this->assertEquals(100000, $items[2]['total']);

        $stats = $response->viewData('stats');
        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(1, $stats['terdaftar']);
        $this->assertEquals(1, $stats['nihil']);
        $this->assertEquals(1, $stats['belum_terdaftar']);
        $this->assertEquals(400000, $stats['total_nominal']);

        $this->assertTrue(session()->has('import_setoran_staging'));

        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    public function test_import_confirm_saves_nihil_items_and_syncs_dana_transaksi_only_for_positive_totals(): void
    {
        $danaZakat = Dana::firstOrCreate(['kode' => '2.2'], ['nama' => 'Zakat Maal via UPZ', 'kategori' => 'Zakat']);
        $danaInfak = Dana::firstOrCreate(['kode' => '2.7'], ['nama' => 'Infak via UPZ', 'kategori' => 'Infak/Sedekah']);

        $person1 = Person::create(['nama' => 'Muzakki A', 'nik' => '3301040404040008']);
        $m1 = Muzakki::create([
            'person_id' => $person1->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-CONFIRM-001',
        ]);

        $person2 = Person::create(['nama' => 'Muzakki B (Nihil)', 'nik' => '3301040404040009']);
        $m2 = Muzakki::create([
            'person_id' => $person2->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-CONFIRM-002',
        ]);

        $stagingPayload = [
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'bulan' => 'November',
            'tahun' => '2026',
            'periode' => 'November 2026',
            'keterangan' => 'Konfirmasi Test Setoran',
            'is_verified' => true,
            'items' => [
                [
                    'muzakki_id' => $m1->id,
                    'nama' => 'Muzakki A',
                    'npwz' => $m1->npwz,
                    'nominal_zakat' => 500000,
                    'nominal_infak' => 100000,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                    'total' => 600000,
                    'status' => 'terdaftar',
                ],
                [
                    'muzakki_id' => $m2->id,
                    'nama' => 'Muzakki B (Nihil)',
                    'npwz' => $m2->npwz,
                    'nominal_zakat' => 0,
                    'nominal_infak' => 0,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                    'total' => 0,
                    'status' => 'nihil',
                ],
            ],
        ];

        session()->put('import_setoran_staging', $stagingPayload);

        $response = $this->post(route('pengumpulan.upz.import-confirm'));

        $response->assertRedirect(route('pengumpulan.upz.index'));
        $this->assertFalse(session()->has('import_setoran_staging'));

        $setoran = SetoranUpz::where('keterangan', 'Konfirmasi Test Setoran')->first();
        $this->assertNotNull($setoran);
        $this->assertEquals(600000, $setoran->total_jumlah);
        $this->assertEquals(500000, $setoran->total_zakat);
        $this->assertEquals(100000, $setoran->total_infak);

        // Keduanya tersimpan di SetoranUpzItem
        $this->assertEquals(2, $setoran->items()->count());
        $this->assertDatabaseHas('setoran_upz_items', [
            'setoran_upz_id' => $setoran->id,
            'nama' => 'Muzakki A',
            'jumlah' => 600000,
        ]);
        $this->assertDatabaseHas('setoran_upz_items', [
            'setoran_upz_id' => $setoran->id,
            'nama' => 'Muzakki B (Nihil)',
            'jumlah' => 0,
            'nominal_zakat' => 0,
            'nominal_infak' => 0,
        ]);

        // DanaTransaksi hanya mencatat zakat (500rb) dan infak (100rb), tanpa transaksi 0
        $kasTransaksis = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->get();
        $this->assertCount(2, $kasTransaksis);
        $this->assertEquals(500000, $kasTransaksis->where('dana_id', $danaZakat->id)->first()->nominal);
        $this->assertEquals(100000, $kasTransaksis->where('dana_id', $danaInfak->id)->first()->nominal);
    }

    public function test_form_store_saves_all_items_including_nihil(): void
    {
        $danaZakat = Dana::firstOrCreate(['kode' => '2.2'], ['nama' => 'Zakat Maal via UPZ', 'kategori' => 'Zakat']);
        $danaInfak = Dana::firstOrCreate(['kode' => '2.7'], ['nama' => 'Infak via UPZ', 'kategori' => 'Infak/Sedekah']);

        $payload = [
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'bulan' => 'November',
            'tahun' => '2026',
            'periode' => 'November 2026',
            'keterangan' => 'Form Store Test Nihil',
            'is_verified' => true,
            'items' => [
                [
                    'nama' => 'Muzakki Positif',
                    'npwz' => 'NPWZ-POS-001',
                    'nominal_zakat' => 200000,
                    'nominal_infak' => 0,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                ],
                [
                    'nama' => 'Muzakki Nihil Form',
                    'npwz' => 'NPWZ-NIH-002',
                    'nominal_zakat' => 0,
                    'nominal_infak' => 0,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                ],
            ],
        ];

        $response = $this->post(route('pengumpulan.upz.store'), $payload);

        $response->assertRedirect(route('pengumpulan.upz.index'));

        $setoran = SetoranUpz::where('keterangan', 'Form Store Test Nihil')->first();
        $this->assertNotNull($setoran);
        $this->assertEquals(200000, $setoran->total_jumlah);

        // Kedua baris disimpan di SetoranUpzItem
        $this->assertEquals(2, $setoran->items()->count());
        $this->assertDatabaseHas('setoran_upz_items', [
            'setoran_upz_id' => $setoran->id,
            'nama' => 'Muzakki Nihil Form',
            'jumlah' => 0,
        ]);

        // Kas hanya zakat 200.000, infak 0 tidak dicatat
        $kasTransaksis = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->get();
        $this->assertCount(1, $kasTransaksis);
        $this->assertEquals(200000, $kasTransaksis->first()->nominal);
    }

    public function test_show_and_cetak_view_displays_participation_stats_and_nihil_badge(): void
    {
        $setoran = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/9999',
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'periode' => 'November 2026',
            'total_zakat' => 500000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 500000,
            'status' => 'Terverifikasi',
            'user_id' => $this->user->id,
        ]);

        SetoranUpzItem::create([
            'setoran_upz_id' => $setoran->id,
            'nama' => 'Muzakki Aktif',
            'npwz' => 'NPWZ-001',
            'nominal_zakat' => 500000,
            'nominal_infak' => 0,
            'nominal_fitrah' => 0,
            'nominal_titipan' => 0,
            'jumlah' => 500000,
            'periode' => 'November 2026',
        ]);

        SetoranUpzItem::create([
            'setoran_upz_id' => $setoran->id,
            'nama' => 'Muzakki Pasif',
            'npwz' => 'NPWZ-002',
            'nominal_zakat' => 0,
            'nominal_infak' => 0,
            'nominal_fitrah' => 0,
            'nominal_titipan' => 0,
            'jumlah' => 0,
            'periode' => 'November 2026',
        ]);

        // Test Show page
        $resShow = $this->get(route('pengumpulan.upz.show', $setoran->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('Tingkat Partisipasi UPZ');
        $resShow->assertSee('50%'); // 1 of 2
        $resShow->assertSee('Nihil (Rp 0)');
        $resShow->assertSee('Muzakki Aktif');
        $resShow->assertSee('Muzakki Pasif');

        // Test Cetak page
        $resCetak = $this->get(route('pengumpulan.upz.cetak', $setoran->id));
        $resCetak->assertStatus(200);
        $resCetak->assertSee('LAPORAN SETORAN ZIS');
        $resCetak->assertSee('MUZAKKI AKTIF');
        $resCetak->assertSee('Rp 0 (Nihil)');
    }

    public function test_import_confirm_all_nihil_creates_no_dana_transaksi(): void
    {
        Dana::firstOrCreate(['kode' => '2.2'], ['nama' => 'Zakat Maal via UPZ', 'kategori' => 'Zakat']);
        Dana::firstOrCreate(['kode' => '2.7'], ['nama' => 'Infak via UPZ', 'kategori' => 'Infak/Sedekah']);

        $person = Person::create(['nama' => 'Muzakki Semua Nihil', 'nik' => '3301050505050010']);
        $muzakki = Muzakki::create([
            'person_id' => $person->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-NIHIL-ALL',
        ]);

        $stagingPayload = [
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'bulan' => 'November',
            'tahun' => '2026',
            'periode' => 'November 2026',
            'keterangan' => 'Laporan Seluruhnya Nihil',
            'items' => [
                [
                    'muzakki_id' => $muzakki->id,
                    'nama' => 'Muzakki Semua Nihil',
                    'npwz' => $muzakki->npwz,
                    'nominal_zakat' => 0,
                    'nominal_infak' => 0,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                    'total' => 0,
                    'status' => 'nihil',
                ],
            ],
        ];

        session()->put('import_setoran_staging', $stagingPayload);

        $response = $this->post(route('pengumpulan.upz.import-confirm'));
        $response->assertRedirect(route('pengumpulan.upz.index'));

        $setoran = SetoranUpz::where('keterangan', 'Laporan Seluruhnya Nihil')->first();
        $this->assertNotNull($setoran);
        $this->assertEquals(0, $setoran->total_jumlah);
        $this->assertEquals(1, $setoran->items()->count());
        $this->assertEquals(0, $setoran->items()->first()->jumlah);

        // KAS BAZNAS TIDAK BOLEH MENCATAT JURNAL RP 0
        $kasTransaksis = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->get();
        $this->assertCount(0, $kasTransaksis);
    }

    public function test_quick_store_muzakki_validation_error(): void
    {
        // 1. Missing nama
        $res1 = $this->postJson(route('pengumpulan.upz.quick-store-muzakki'), [
            'nik' => '3301060606060011',
            'lembaga_id' => $this->upz->id,
        ]);
        $res1->assertStatus(422);
        $res1->assertJsonValidationErrors(['nama']);

        // 2. Duplicate NIK
        Person::create(['nama' => 'Orang Sudah Ada', 'nik' => '3301060606060011']);
        $res2 = $this->postJson(route('pengumpulan.upz.quick-store-muzakki'), [
            'nama' => 'Duplikat NIK',
            'nik' => '3301060606060011',
            'lembaga_id' => $this->upz->id,
        ]);
        $res2->assertStatus(422);
        $res2->assertJsonValidationErrors(['nik']);
    }

    public function test_import_preview_handles_dirty_formatting_and_fuzzy_npwz_nik(): void
    {
        $person = Person::create(['nama' => 'Muzakki Format Aneh', 'nik' => '3301070707070012']);
        $muzakki = Muzakki::create([
            'person_id' => $person->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-FUZZY-123',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['ID Muzakki', 'NPWZ', 'Nama Lengkap', 'NIK', 'Zakat (Rp)', 'Infak (Rp)', 'Fitrah (Rp)', 'Titipan (Rp)', 'Keterangan'],
            // NPWZ with spaces and lowercase, NIK with spaces, nominal with Rupiah dots and commas
            ['', ' npwz-fuzzy-123 ', 'Muzakki Format Aneh', ' 3301070707070012 ', 'Rp 1.500.000,00', 'Rp 250.000', 0, 0, 'Format Kotor'],
            // 5 Spare empty rows
            ['', '', '', '', 0, 0, 0, 0, ''],
            ['', '', '', '', 0, 0, 0, 0, ''],
            ['', '', '', '', 0, 0, 0, 0, ''],
            ['', '', '', '', 0, 0, 0, 0, ''],
            ['', '', '', '', 0, 0, 0, 0, ''],
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_dirty_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempFile);

        $uploaded = new UploadedFile($tempFile, 'dirty.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->post(route('pengumpulan.upz.import-preview'), [
            'file' => $uploaded,
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'bulan' => 'November',
            'tahun' => '2026',
            'keterangan' => 'Test Dirty Formats',
        ]);

        $response->assertStatus(200);
        $items = $response->viewData('items');

        // All 5 empty rows must be skipped! Only 1 item returned
        $this->assertCount(1, $items);
        $this->assertEquals($muzakki->id, $items[0]['muzakki_id']);
        $this->assertEquals('terdaftar', $items[0]['status']);
        $this->assertEquals(1500000, $items[0]['nominal_zakat']);
        $this->assertEquals(250000, $items[0]['nominal_infak']);
        $this->assertEquals(1750000, $items[0]['total']);

        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    public function test_import_preview_with_csv_file(): void
    {
        $person = Person::create(['nama' => 'Muzakki CSV User', 'nik' => '3301080808080013']);
        $muzakki = Muzakki::create([
            'person_id' => $person->id,
            'lembaga_id' => $this->upz->id,
            'npwz' => 'NPWZ-CSV-001',
        ]);

        $csvContent = implode("\n", [
            'ID Muzakki,NPWZ,Nama Lengkap,NIK,Zakat (Rp),Infak (Rp),Fitrah (Rp),Titipan (Rp),Keterangan',
            "{$muzakki->id},{$muzakki->npwz},Muzakki CSV User,3301080808080013,750000,50000,0,0,Setoran via CSV",
            ',,Muzakki Lepas CSV,3301080808080014,100000,0,0,0,Belum Terdaftar',
        ]);

        $tempCsv = tempnam(sys_get_temp_dir(), 'test_csv_') . '.csv';
        file_put_contents($tempCsv, $csvContent);

        $uploaded = new UploadedFile($tempCsv, 'setoran.csv', 'text/csv', null, true);

        $response = $this->post(route('pengumpulan.upz.import-preview'), [
            'file' => $uploaded,
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-02',
            'bulan' => 'November',
            'tahun' => '2026',
            'keterangan' => 'Import CSV Batch',
        ]);

        $response->assertStatus(200);
        $items = $response->viewData('items');

        $this->assertCount(2, $items);
        $this->assertEquals('terdaftar', $items[0]['status']);
        $this->assertEquals(800000, $items[0]['total']);
        $this->assertEquals('belum_terdaftar', $items[1]['status']);
        $this->assertEquals(100000, $items[1]['total']);

        if (file_exists($tempCsv)) {
            unlink($tempCsv);
        }
    }

    public function test_create_view_renders_workspace_elements(): void
    {
        $response = $this->get(route('pengumpulan.upz.create'));

        $response->assertStatus(200);
        $response->assertSee('enctype="multipart/form-data"', false);
        $response->assertSee('setoranWorkspace()', false);
        $response->assertSee('searchUpz()', false);
        $response->assertSee('parse-inline-excel', false);
        $response->assertSee('quick-store-muzakki', false);
        $response->assertSee('showFitrahTitipan', false);
        $response->assertSee('applyBulkNominal()', false);
        $response->assertSee('markAllNihil()', false);
    }

    public function test_index_view_renders_bukti_transfer_badge(): void
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
        $response->assertDontSee('onsubmit="return confirm');
    }
}
