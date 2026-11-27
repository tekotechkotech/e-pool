<?php

namespace Tests\Feature;

use App\Models\Dana;
use App\Models\DanaTransaksi;
use App\Models\Muzakki;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Person;
use App\Models\SetoranUpz;
use App\Models\SetoranUpzItem;
use App\Models\Upz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ClientRequirementVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected Upz $upz;
    protected User $baznasUser;
    protected User $upzUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Master Dana
        Dana::firstOrCreate(['kode' => '2.2'], ['nama' => 'Zakat Maal via UPZ', 'kategori' => 'Zakat', 'via_upz' => true, 'is_active' => true]);
        Dana::firstOrCreate(['kode' => '2.5'], ['nama' => 'Zakat Fitrah via UPZ', 'kategori' => 'Zakat', 'via_upz' => true, 'is_active' => true]);
        Dana::firstOrCreate(['kode' => '2.7'], ['nama' => 'Infak via UPZ', 'kategori' => 'Infak/Sedekah', 'via_upz' => true, 'is_active' => true]);
        Dana::firstOrCreate(['kode' => '2.18'], ['nama' => 'Dana Titipan', 'kategori' => 'Dana Titipan', 'via_upz' => true, 'is_active' => true]);

        // 2. UPZ
        $this->upz = Upz::create([
            'nama' => 'UPZ Masjid Jami',
            'kode_simba' => 'UPZ-JAMI',
            'is_upz' => true,
            'is_active' => true,
        ]);

        // 3. User BAZNAS
        $personBaznas = Person::create(['nama' => 'Admin BAZNAS Test', 'nik' => '1111222233334444']);
        $this->baznasUser = User::create([
            'person_id' => $personBaznas->id,
            'email' => 'admin.test@baznas.id',
            'password' => bcrypt('password'),
        ]);
        $pjBaznas = PengurusJabatan::firstOrCreate(['nama' => 'Admin BAZNAS'], ['kode' => 'ADM', 'tingkat' => 'baznas', 'is_active' => true]);
        Pengurus::create([
            'jenis_pengurus' => 'Baznas',
            'person_id' => $personBaznas->id,
            'pengurus_jabatan_id' => $pjBaznas->id,
            'is_active' => true,
        ]);

        // 4. User UPZ
        $personUpz = Person::create(['nama' => 'Ketua UPZ Test', 'nik' => '5555666677778888']);
        $this->upzUser = User::create([
            'person_id' => $personUpz->id,
            'email' => 'ketua.test@upz.id',
            'password' => bcrypt('password'),
        ]);
        $pjUpz = PengurusJabatan::firstOrCreate(['nama' => 'Ketua'], ['kode' => 'KT', 'tingkat' => 'lembaga', 'is_active' => true]);
        Pengurus::create([
            'jenis_pengurus' => 'Lembaga',
            'lembaga_id' => $this->upz->id,
            'person_id' => $personUpz->id,
            'pengurus_jabatan_id' => $pjUpz->id,
            'is_active' => true,
        ]);
    }

    /**
     * 1. Master Jenis Dana: Sederhana & Sembunyikan Saldo/Penerimaan/Penyaluran
     */
    public function test_master_dana_simplification_hides_operational_totals(): void
    {
        $response = $this->actingAs($this->baznasUser)->get(route('master.danas'));
        $response->assertStatus(200);

        // Pastikan widget finansial operasional dan tombol catat kas tidak tampil
        $response->assertDontSee('Total Transaksi Masuk');
        $response->assertDontSee('Total Transaksi Keluar');
        $response->assertDontSee('Catat Penerimaan Kas');
        $response->assertDontSee('Catat Penerimaan Dana');

        // Pastikan metrik master yang bersih tampil
        $response->assertSee('Master Jenis Dana');
        $response->assertSee('Total Jenis Dana');
        $response->assertSee('Dana Aktif');
        $response->assertSee('Dihimpun via UPZ');
    }

    /**
     * 2. Format Excel (.xlsx) pada Muzakki: Template & Impor
     */
    public function test_muzakki_template_and_import_excel_support(): void
    {
        // Download Template Excel default
        $resXlsx = $this->actingAs($this->baznasUser)->get(route('muzakki.template'));
        $resXlsx->assertStatus(200);
        $this->assertTrue(str_contains($resXlsx->headers->get('content-disposition'), '.xlsx'));

        // Download Template CSV alternatif
        $resCsv = $this->actingAs($this->baznasUser)->get(route('muzakki.template', ['format' => 'csv']));
        $resCsv->assertStatus(200);
        $this->assertTrue(str_contains($resCsv->headers->get('content-type'), 'text/csv'));

        // Halaman index dan import memuat informasi Excel
        $resIndex = $this->actingAs($this->baznasUser)->get(route('muzakki.index'));
        $resIndex->assertSee('Template Excel (.xlsx)');
        $resIndex->assertSee('Import Excel / CSV');

        $resImport = $this->actingAs($this->baznasUser)->get(route('muzakki.import'));
        $resImport->assertSee('.xlsx');
    }

    /**
     * 3. Format Excel (.xlsx) pada SIMBA Export
     */
    public function test_simba_export_excel_download(): void
    {
        // Export Simba Muzakki format Excel
        $resMuzakkiExcel = $this->actingAs($this->baznasUser)->get(route('simba.muzakki', ['download' => 'excel']));
        $resMuzakkiExcel->assertStatus(200);
        $this->assertTrue(str_contains($resMuzakkiExcel->headers->get('content-disposition'), 'Export_Simba_Muzakki_'));
        $this->assertTrue(str_contains($resMuzakkiExcel->headers->get('content-disposition'), '.xlsx'));

        // Export Simba Kas Masuk format Excel
        $resKasExcel = $this->actingAs($this->baznasUser)->get(route('simba.kas-masuk', ['download' => 'excel']));
        $resKasExcel->assertStatus(200);
        $this->assertTrue(str_contains($resKasExcel->headers->get('content-disposition'), 'Export_Simba_Kas_Masuk_'));
        $this->assertTrue(str_contains($resKasExcel->headers->get('content-disposition'), '.xlsx'));
    }

    /**
     * 4. Dashboard Muzakki vs Munfiq & Aturan De-duplikasi 1 Orang:
     *    Jika dalam 1 periode ZIS ada zakat dan infak masuk dari 1 orang,
     *    orang yang bersangkutan masuk nya cuma muzakki tidak + munfiq.
     */
    public function test_dashboard_muzakki_vs_munfiq_deduplication(): void
    {
        $person1 = Person::create(['nama' => 'H. Zakat & Infak', 'nik' => '3301010101010091']);
        $muzakki1 = Muzakki::create(['person_id' => $person1->id, 'lembaga_id' => $this->upz->id, 'npwz' => 'ZIS-01']);

        $person2 = Person::create(['nama' => 'Ibu Murni Infak', 'nik' => '3301010101010092']);
        $muzakki2 = Muzakki::create(['person_id' => $person2->id, 'lembaga_id' => $this->upz->id, 'npwz' => 'ZIS-02']);

        // Setoran Terverifikasi untuk periode Maret 2026
        $setoran = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/03/0001',
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-03-10',
            'periode' => 'Maret 2026',
            'total_zakat' => 500000,
            'total_infak' => 300000,
            'total_jumlah' => 800000,
            'status' => 'Terverifikasi',
        ]);

        // Item 1: Person 1 menyetor Zakat 500.000 DAN Infak 100.000
        SetoranUpzItem::create([
            'setoran_upz_id' => $setoran->id,
            'muzakki_id' => $muzakki1->id,
            'nama' => 'H. Zakat & Infak',
            'nominal_zakat' => 500000,
            'nominal_infak' => 100000,
            'jumlah' => 600000,
            'periode' => 'Maret 2026',
        ]);

        // Item 2: Person 2 menyetor Infak murni 200.000 (Zakat 0)
        SetoranUpzItem::create([
            'setoran_upz_id' => $setoran->id,
            'muzakki_id' => $muzakki2->id,
            'nama' => 'Ibu Murni Infak',
            'nominal_zakat' => 0,
            'nominal_infak' => 200000,
            'jumlah' => 200000,
            'periode' => 'Maret 2026',
        ]);

        // Akses dashboard dengan filter periode Maret 2026
        $response = $this->actingAs($this->baznasUser)->get(route('dashboard', ['bulan' => 'Maret', 'tahun' => '2026']));
        $response->assertStatus(200);

        // Ambil data view stats
        $stats = $response->viewData('stats');
        $this->assertEquals(1, $stats['totalMuzakki'], 'Person 1 yang bayar Zakat & Infak harus terhitung Muzakki');
        $this->assertEquals(1, $stats['totalMunfiq'], 'Person 2 yang bayar Infak saja terhitung Munfiq, Person 1 TIDAK boleh masuk Munfiq');
    }

    /**
     * 5. Rekonsiliasi Otomatis (Auto-Approval jika data UPZ dan BAZNAS sama persis)
     */
    public function test_dual_entry_auto_approval_when_data_matches_identically(): void
    {
        $person = Person::create(['nama' => 'Ahmad Warga', 'nik' => '3301010101010093']);
        $muzakki = Muzakki::create(['person_id' => $person->id, 'lembaga_id' => $this->upz->id, 'npwz' => 'ZIS-03']);

        $payloadBase = [
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-05-15',
            'bulan' => 'Mei',
            'tahun' => '2026',
            'keterangan' => 'Setoran ZIS Mei 2026',
            'items' => [
                [
                    'muzakki_id' => $muzakki->id,
                    'nama' => 'Ahmad Warga',
                    'nominal_zakat' => 1000000,
                    'nominal_infak' => 200000,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                ],
            ],
        ];

        // 1. UPZ input setoran dari sisi UPZ
        $resUpz = $this->actingAs($this->upzUser)->post(route('pengumpulan.upz.store'), $payloadBase);
        $resUpz->assertRedirect(route('pengumpulan.upz.index'));

        $setoranUpz = SetoranUpz::where('lembaga_id', $this->upz->id)->where('periode', 'Mei 2026')->where('sumber_input', 'UPZ')->first();
        $this->assertNotNull($setoranUpz);
        $this->assertEquals('Menunggu Verifikasi', $setoranUpz->status);
        $this->assertEquals('menunggu_pasangan', $setoranUpz->status_rekonsiliasi);

        // 2. BAZNAS input data dari sisi BAZNAS (data sama persis)
        $resBaznas = $this->actingAs($this->baznasUser)->post(route('pengumpulan.upz.store'), $payloadBase);
        $resBaznas->assertRedirect(route('pengumpulan.upz.index'));

        $setoranBaznas = SetoranUpz::where('lembaga_id', $this->upz->id)->where('periode', 'Mei 2026')->where('sumber_input', 'BAZNAS')->first();
        $this->assertNotNull($setoranBaznas);

        // Refresh kedua setoran
        $setoranUpz->refresh();
        $setoranBaznas->refresh();

        // Keduanya OTOMATIS LOLOS TANPA VERIFIKASI
        $this->assertEquals('Terverifikasi', $setoranUpz->status);
        $this->assertEquals('cocok_otomatis', $setoranUpz->status_rekonsiliasi);
        $this->assertEquals('Terverifikasi', $setoranBaznas->status);
        $this->assertEquals('cocok_otomatis', $setoranBaznas->status_rekonsiliasi);

        // DanaTransaksi terbentuk tunggal (mencegah double booking)
        $kas = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->whereIn('referensi_id', [$setoranUpz->id, $setoranBaznas->id])
            ->get();
        $this->assertCount(2, $kas); // 1 Zakat + 1 Infak
    }

    /**
     * 6. Rekonsiliasi dengan Selisih: Wajib Verifikasi jika ada selisih
     */
    public function test_dual_entry_discrepancy_requires_verification(): void
    {
        $person = Person::create(['nama' => 'Santoso', 'nik' => '3301010101010094']);
        $muzakki = Muzakki::create(['person_id' => $person->id, 'lembaga_id' => $this->upz->id, 'npwz' => 'ZIS-04']);

        // 1. UPZ input Rp 1.000.000
        $payloadUpz = [
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-06-15',
            'bulan' => 'Juni',
            'tahun' => '2026',
            'items' => [
                [
                    'muzakki_id' => $muzakki->id,
                    'nama' => 'Santoso',
                    'nominal_zakat' => 1000000,
                    'nominal_infak' => 0,
                ],
            ],
        ];
        $this->actingAs($this->upzUser)->post(route('pengumpulan.upz.store'), $payloadUpz);

        // 2. BAZNAS input Rp 950.000 (Selisih Rp 50.000)
        $payloadBaznas = [
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-06-15',
            'bulan' => 'Juni',
            'tahun' => '2026',
            'items' => [
                [
                    'muzakki_id' => $muzakki->id,
                    'nama' => 'Santoso',
                    'nominal_zakat' => 950000,
                    'nominal_infak' => 0,
                ],
            ],
        ];
        $this->actingAs($this->baznasUser)->post(route('pengumpulan.upz.store'), $payloadBaznas);

        $setUpz = SetoranUpz::where('periode', 'Juni 2026')->where('sumber_input', 'UPZ')->first();
        $setBaznas = SetoranUpz::where('periode', 'Juni 2026')->where('sumber_input', 'BAZNAS')->first();

        // Status WAJIB MENUNGGU VERIFIKASI karena ada selisih
        $this->assertEquals('Menunggu Verifikasi', $setUpz->status);
        $this->assertEquals('ada_selisih', $setUpz->status_rekonsiliasi);
        $this->assertEquals(50000, $setUpz->selisih_nominal);

        $this->assertEquals('Menunggu Verifikasi', $setBaznas->status);
        $this->assertEquals('ada_selisih', $setBaznas->status_rekonsiliasi);
        $this->assertEquals(50000, $setBaznas->selisih_nominal);
    }

    /**
     * 7. Verifikasi Silang: Inputan BAZNAS bisa diverifikasi oleh UPZ
     */
    public function test_cross_verification_upz_can_verify_baznas_input(): void
    {
        // BAZNAS input setoran untuk UPZ
        $setoranBaznas = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/07/0001',
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-07-01',
            'periode' => 'Juli 2026',
            'total_zakat' => 2000000,
            'total_jumlah' => 2000000,
            'status' => 'Menunggu Verifikasi',
            'sumber_input' => 'BAZNAS',
            'user_id' => $this->baznasUser->id,
        ]);

        SetoranUpzItem::create([
            'setoran_upz_id' => $setoranBaznas->id,
            'nama' => 'Muzakki Donatur BAZNAS',
            'nominal_zakat' => 2000000,
            'jumlah' => 2000000,
        ]);

        // UPZ memverifikasi setoran BAZNAS
        $res = $this->actingAs($this->upzUser)->post(route('pengumpulan.upz.verifikasi', $setoranBaznas->id), [
            'catatan_verifikasi' => 'Data disetujui oleh pengurus UPZ',
        ]);
        $res->assertRedirect(route('pengumpulan.upz.show', $setoranBaznas->id));

        $setoranBaznas->refresh();
        $this->assertEquals('Terverifikasi', $setoranBaznas->status);
        $this->assertEquals($this->upzUser->id, $setoranBaznas->verified_by);
        $this->assertNotNull($setoranBaznas->verified_at);

        // Kas masuk buku kas BAZNAS terbit
        $kas = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoranBaznas->id)
            ->first();
        $this->assertNotNull($kas);
        $this->assertEquals(2000000, $kas->nominal);
    }
}
