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
}

