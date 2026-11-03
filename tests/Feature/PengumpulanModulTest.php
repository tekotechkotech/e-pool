<?php

namespace Tests\Feature;

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
use Tests\TestCase;

class PengumpulanModulTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Buat pos dana standar jika belum ada
        Dana::firstOrCreate(['kode' => '2.2'], [
            'nama' => 'Zakat Maal-Perorangan via UPZ',
            'kategori' => 'Zakat',
            'via_upz' => true,
        ]);
        Dana::firstOrCreate(['kode' => '2.7'], [
            'nama' => 'Infak/Sedekah Tidak Terikat via UPZ',
            'kategori' => 'Infak/Sedekah',
            'via_upz' => true,
        ]);
    }

    public function test_muzakki_crud_and_export(): void
    {
        $person = Person::create(['nama' => 'Staff', 'nik' => '9999888877776666']);
        $user = User::create([
            'person_id' => $person->id,
            'email' => 'admin@baznas.go.id',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        $upz = Upz::create([
            'nama' => 'UPZ Test',
            'kode_simba' => '999',
            'is_upz' => true,
            'is_active' => true,
        ]);

        // 1. Create Muzakki
        $res = $this->post(route('muzakki.store'), [
            'nama' => 'Budi Muzakki',
            'nik' => '3301010101010001',
            'lembaga_id' => $upz->id,
            'npwz' => '3301001',
            'jenis_kelamin' => 'L',
            'pekerjaan' => 'PNS',
            'telepon' => '0282123',
            'handphone' => '08123456789',
            'email' => 'budi@test.com',
            'alamat' => 'Cilacap',
        ]);
        $res->assertRedirect(route('muzakki.index'));

        $this->assertDatabaseHas('persons', ['nik' => '3301010101010001', 'nama' => 'Budi Muzakki']);
        $this->assertDatabaseHas('muzakkis', ['npwz' => '3301001', 'lembaga_id' => $upz->id]);

        $muzakki = Muzakki::where('npwz', '3301001')->first();

        // 2. Edit Muzakki
        $resEdit = $this->put(route('muzakki.update', $muzakki->id), [
            'nama' => 'Budi Muzakki Updated',
            'nik' => '3301010101010001',
            'lembaga_id' => $upz->id,
            'npwz' => '3301001',
            'pekerjaan' => 'Wiraswasta',
        ]);
        $resEdit->assertRedirect(route('muzakki.index'));
        $this->assertDatabaseHas('persons', ['nama' => 'Budi Muzakki Updated']);

        // 3. Download Template
        $resTemplate = $this->get(route('muzakki.template'));
        $resTemplate->assertStatus(200);
        $this->assertTrue(str_contains($resTemplate->headers->get('content-disposition'), 'Format Input Data Muzaki'));

        // 4. Simba Export Muzakki
        $resSimbaPreview = $this->get(route('simba.muzakki'));
        $resSimbaPreview->assertStatus(200);

        $resSimbaCsv = $this->get(route('simba.muzakki', ['download' => 'csv']));
        $resSimbaCsv->assertStatus(200);
        $this->assertTrue(str_contains($resSimbaCsv->headers->get('content-type'), 'text/csv'));
    }

    public function test_setoran_upz_flow_and_dana_synchronization(): void
    {
        $person = Person::create(['nama' => 'Staff 2', 'nik' => '9999888877776665']);
        $user = User::create([
            'person_id' => $person->id,
            'email' => 'admin2@baznas.go.id',
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

        $this->actingAs($user);

        $upz = Upz::create([
            'nama' => 'UPZ Masjid Raya',
            'kode_simba' => '888',
            'is_upz' => true,
            'is_active' => true,
        ]);

        $postData = [
            'lembaga_id' => $upz->id,
            'tanggal_setor' => '2026-10-26',
            'periode' => 'Oktober 2026',
            'keterangan' => 'Setoran rutin Oktober',
            'is_verified' => true,
            'items' => [
                [
                    'nama' => 'Muzakki A',
                    'npwz' => 'NPWZ-01',
                    'nominal_zakat' => 500000,
                    'nominal_infak' => 100000,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                    'keterangan' => 'Zakat & Infak',
                ],
                [
                    'nama' => 'Munfiq B',
                    'npwz' => '',
                    'nominal_zakat' => 0,
                    'nominal_infak' => 200000,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                    'keterangan' => 'Infak',
                ]
            ]
        ];

        $res = $this->post(route('pengumpulan.upz.store'), $postData);
        $res->assertRedirect(route('pengumpulan.upz.index'));

        $this->assertDatabaseHas('setoran_upzs', [
            'lembaga_id' => $upz->id,
            'periode' => 'Oktober 2026',
            'total_zakat' => 500000,
            'total_infak' => 300000,
            'total_jumlah' => 800000,
        ]);

        $setoran = SetoranUpz::where('lembaga_id', $upz->id)->first();
        $this->assertNotNull($setoran);
        $this->assertStringStartsWith('SET-UPZ/2026/10/', $setoran->nomor_setoran);

        // Verifikasi sinkronisasi otomatis ke DanaTransaksi Masuk
        $this->assertDatabaseHas('dana_transaksis', [
            'referensi_type' => SetoranUpz::class,
            'referensi_id' => $setoran->id,
            'jenis' => 'Masuk',
            'nominal' => 500000,
        ]);
        $this->assertDatabaseHas('dana_transaksis', [
            'referensi_type' => SetoranUpz::class,
            'referensi_id' => $setoran->id,
            'jenis' => 'Masuk',
            'nominal' => 300000,
        ]);

        // Cetak lampiran
        $resCetak = $this->get(route('pengumpulan.upz.cetak', $setoran->id));
        $resCetak->assertStatus(200);

        // Kas Masuk page
        $resKas = $this->get(route('pengumpulan.kas.index'));
        $resKas->assertStatus(200);

        // Simba Export Kas Masuk
        $resSimbaKas = $this->get(route('simba.kas-masuk', ['download' => 'csv']));
        $resSimbaKas->assertStatus(200);

        // Hapus setoran beserta transaksi
        $resDel = $this->delete(route('pengumpulan.upz.destroy', $setoran->id));
        $resDel->assertRedirect(route('pengumpulan.upz.index'));
        $this->assertDatabaseMissing('setoran_upzs', ['id' => $setoran->id]);
        $this->assertDatabaseMissing('dana_transaksis', [
            'referensi_type' => SetoranUpz::class,
            'referensi_id' => $setoran->id,
        ]);
    }

    public function test_muzakki_seeder_generates_minimum_records(): void
    {
        $upz1 = Upz::create([
            'nama' => 'UPZ Baiturrahman',
            'kode_simba' => 'UPZ-001',
            'is_upz' => true,
            'is_active' => true,
        ]);

        $upz2 = Upz::create([
            'nama' => 'UPZ Al-Ikhlas',
            'kode_simba' => 'UPZ-002',
            'is_upz' => true,
            'is_active' => true,
        ]);

        $this->seed(\Database\Seeders\MuzakkiSeeder::class);

        $this->assertGreaterThanOrEqual(10, Muzakki::where('lembaga_id', $upz1->id)->count());
        $this->assertGreaterThanOrEqual(10, Muzakki::where('lembaga_id', $upz2->id)->count());

        $sampleMuzakki = Muzakki::where('lembaga_id', $upz1->id)->first();
        $this->assertNotNull($sampleMuzakki);
        $this->assertNotNull($sampleMuzakki->person);
        $this->assertNotEmpty($sampleMuzakki->person->nik);
        $this->assertNotEmpty($sampleMuzakki->person->nama);

        // Run again to verify idempotency (should not duplicate if minimum is already met)
        $countBefore = Muzakki::count();
        $this->seed(\Database\Seeders\MuzakkiSeeder::class);
        $this->assertEquals($countBefore, Muzakki::count());
    }

    public function test_setoran_upz_store_combines_bulan_and_tahun(): void
    {
        $person = Person::create(['nama' => 'Staff 3', 'nik' => '9999888877776664']);
        $user = User::create([
            'person_id' => $person->id,
            'email' => 'admin3@baznas.go.id',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        $upz = Upz::create([
            'nama' => 'UPZ An-Nur',
            'kode_simba' => '777',
            'is_upz' => true,
            'is_active' => true,
        ]);

        $postData = [
            'lembaga_id' => $upz->id,
            'tanggal_setor' => '2026-11-15',
            'bulan' => 'November',
            'tahun' => '2026',
            'keterangan' => 'Setoran bulan November 2026',
            'items' => [
                [
                    'nama' => 'Muzakki Baru',
                    'nominal_zakat' => 1000000,
                    'nominal_infak' => 250000,
                ]
            ]
        ];

        $res = $this->post(route('pengumpulan.upz.store'), $postData);
        $res->assertRedirect(route('pengumpulan.upz.index'));

        $this->assertDatabaseHas('setoran_upzs', [
            'lembaga_id' => $upz->id,
            'periode' => 'November 2026',
            'total_zakat' => 1000000,
            'total_infak' => 250000,
            'total_jumlah' => 1250000,
        ]);

        $this->assertDatabaseHas('setoran_upz_items', [
            'nama' => 'Muzakki Baru',
            'periode' => 'November 2026',
            'jumlah' => 1250000,
        ]);
    }

    public function test_filter_bulan_dan_tahun_pada_setoran_upz_dan_simba(): void
    {
        $person = Person::create(['nama' => 'Staff 4', 'nik' => '9999888877776663']);
        $user = User::create([
            'person_id' => $person->id,
            'email' => 'admin4@baznas.go.id',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        $upz = Upz::create([
            'nama' => 'UPZ Filter Test',
            'kode_simba' => '666',
            'is_upz' => true,
            'is_active' => true,
        ]);

        $setoran1 = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/01/0001',
            'lembaga_id' => $upz->id,
            'tanggal_setor' => '2026-01-10',
            'periode' => 'Januari 2026',
            'total_zakat' => 500000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 500000,
            'status' => 'Terverifikasi',
            'user_id' => $user->id,
        ]);

        SetoranUpzItem::create([
            'setoran_upz_id' => $setoran1->id,
            'nama' => 'Muzakki Jan',
            'nominal_zakat' => 500000,
            'nominal_infak' => 0,
            'nominal_fitrah' => 0,
            'nominal_titipan' => 0,
            'jumlah' => 500000,
            'periode' => 'Januari 2026',
        ]);

        $setoran2 = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2025/12/0001',
            'lembaga_id' => $upz->id,
            'tanggal_setor' => '2025-12-20',
            'periode' => 'Desember 2025',
            'total_zakat' => 700000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 700000,
            'status' => 'Terverifikasi',
            'user_id' => $user->id,
        ]);

        SetoranUpzItem::create([
            'setoran_upz_id' => $setoran2->id,
            'nama' => 'Muzakki Des',
            'nominal_zakat' => 700000,
            'nominal_infak' => 0,
            'nominal_fitrah' => 0,
            'nominal_titipan' => 0,
            'jumlah' => 700000,
            'periode' => 'Desember 2025',
        ]);

        // Filter pengumpulan.upz.index: Bulan Januari & Tahun 2026
        $resFilter1 = $this->get(route('pengumpulan.upz.index', ['bulan' => 'Januari', 'tahun' => '2026']));
        $resFilter1->assertStatus(200);
        $resFilter1->assertSee('SET-UPZ/2026/01/0001');
        $resFilter1->assertDontSee('SET-UPZ/2025/12/0001');

        // Filter pengumpulan.upz.index: Tahun 2025
        $resFilter2 = $this->get(route('pengumpulan.upz.index', ['tahun' => '2025']));
        $resFilter2->assertStatus(200);
        $resFilter2->assertSee('SET-UPZ/2025/12/0001');
        $resFilter2->assertDontSee('SET-UPZ/2026/01/0001');

        // Filter simba.kas-masuk preview: Bulan Desember
        $resSimba1 = $this->get(route('simba.kas-masuk', ['bulan' => 'Desember']));
        $resSimba1->assertStatus(200);
        $resSimba1->assertSee('Muzakki Des');
        $resSimba1->assertDontSee('Muzakki Jan');

        // Filter simba.kas-masuk CSV download
        $resSimbaCsv = $this->get(route('simba.kas-masuk', ['bulan' => 'Januari', 'tahun' => '2026', 'download' => 'csv']));
        $resSimbaCsv->assertStatus(200);
        $csvContent = $resSimbaCsv->streamedContent();
        $this->assertStringContainsString('Muzakki Jan', $csvContent);
        $this->assertStringNotContainsString('Muzakki Des', $csvContent);
    }

    public function test_views_contain_bulan_dan_tahun_select_dropdowns(): void
    {
        $person = Person::create(['nama' => 'Staff 5', 'nik' => '9999888877776662']);
        $user = User::create([
            'person_id' => $person->id,
            'email' => 'admin5@baznas.go.id',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        // 1. View create
        $resCreate = $this->get(route('pengumpulan.upz.create'));
        $resCreate->assertStatus(200);
        $resCreate->assertSee('name="bulan"', false);
        $resCreate->assertSee('name="tahun"', false);
        $resCreate->assertSee('Januari');
        $resCreate->assertSee('Desember');

        // 2. View index
        $resIndex = $this->get(route('pengumpulan.upz.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('name="bulan"', false);
        $resIndex->assertSee('name="tahun"', false);
        $resIndex->assertSee('Semua Bulan');
        $resIndex->assertSee('Semua Tahun');

        // 3. View simba kas-masuk
        $resSimba = $this->get(route('simba.kas-masuk'));
        $resSimba->assertStatus(200);
        $resSimba->assertSee('name="bulan"', false);
        $resSimba->assertSee('name="tahun"', false);
        $resSimba->assertSee('Semua Bulan');
        $resSimba->assertSee('Semua Tahun');
    }
}

