<?php

namespace Tests\Feature;

use App\Models\Dana;
use App\Models\DanaTransaksi;
use App\Models\Lembaga;
use App\Models\Muzakki;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Person;
use App\Models\SetoranUpz;
use App\Models\SetoranUpzItem;
use App\Models\Upz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetoranUpzRoleAwareAndVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected Upz $upz1;
    protected Upz $upz2;
    protected User $baznasUser;
    protected User $upzUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Pos Dana Standar
        Dana::firstOrCreate(['kode' => '2.2'], ['nama' => 'Zakat Maal via UPZ', 'kategori' => 'Zakat']);
        Dana::firstOrCreate(['kode' => '2.5'], ['nama' => 'Zakat Fitrah via UPZ', 'kategori' => 'Zakat']);
        Dana::firstOrCreate(['kode' => '2.7'], ['nama' => 'Infak via UPZ', 'kategori' => 'Infak/Sedekah']);
        Dana::firstOrCreate(['kode' => '2.18'], ['nama' => 'Dana Titipan', 'kategori' => 'Titipan']);

        // Jabatan
        $pjBaznas = PengurusJabatan::firstOrCreate(
            ['nama' => 'Admin BAZNAS'],
            ['kode' => 'ADM-BAZNAS', 'tingkat' => 'baznas', 'is_active' => true]
        );

        $pjKetuaUpz = PengurusJabatan::firstOrCreate(
            ['nama' => 'Ketua'],
            ['kode' => 'KT-UPZ', 'tingkat' => 'lembaga', 'is_active' => true]
        );

        // UPZ 1 & UPZ 2
        $this->upz1 = Upz::create([
            'nama' => 'UPZ Masjid Al-Ikhlas',
            'kode_simba' => 'UPZ-001',
            'is_upz' => true,
            'is_active' => true,
        ]);

        $this->upz2 = Upz::create([
            'nama' => 'UPZ Kantor Kemenag',
            'kode_simba' => 'UPZ-002',
            'is_upz' => true,
            'is_active' => true,
        ]);

        // User BAZNAS
        $personBaznas = Person::create(['nama' => 'Staff Baznas Verifikator', 'nik' => '3301010101010099']);
        $this->baznasUser = User::create([
            'person_id' => $personBaznas->id,
            'email' => 'verifikator@baznas.go.id',
            'password' => bcrypt('password'),
        ]);
        Pengurus::create([
            'jenis_pengurus' => 'Baznas',
            'person_id' => $personBaznas->id,
            'pengurus_jabatan_id' => $pjBaznas->id,
            'lembaga_id' => null,
            'is_active' => true,
        ]);

        // User UPZ 1 (Ketua UPZ Masjid Al-Ikhlas)
        $personUpz = Person::create(['nama' => 'Ketua UPZ Al-Ikhlas', 'nik' => '3301010101010088']);
        $this->upzUser = User::create([
            'person_id' => $personUpz->id,
            'email' => 'ketua_upz@alikhlas.org',
            'password' => bcrypt('password'),
        ]);
        Pengurus::create([
            'jenis_pengurus' => 'Lembaga',
            'person_id' => $personUpz->id,
            'pengurus_jabatan_id' => $pjKetuaUpz->id,
            'lembaga_id' => $this->upz1->id,
            'is_active' => true,
        ]);
    }

    public function test_create_endpoint_passes_locked_upz_for_upz_user(): void
    {
        $response = $this->actingAs($this->upzUser)->get(route('pengumpulan.upz.create'));
        $response->assertStatus(200);
        $response->assertViewHas('isUpzUser', true);
        $response->assertViewHas('lockedUpz', function ($upz) {
            return $upz && $upz->id === $this->upz1->id;
        });

        // BAZNAS user should have isUpzUser = false and lockedUpz = null
        $responseBaznas = $this->actingAs($this->baznasUser)->get(route('pengumpulan.upz.create'));
        $responseBaznas->assertStatus(200);
        $responseBaznas->assertViewHas('isUpzUser', false);
        $responseBaznas->assertViewHas('lockedUpz', null);
    }

    public function test_upz_user_store_forces_own_lembaga_id_and_sets_status_menunggu_verifikasi(): void
    {
        $payload = [
            'lembaga_id' => $this->upz2->id, // Mencoba memanipulasi ke UPZ 2
            'tanggal_setor' => '2026-11-03',
            'bulan' => 'November',
            'tahun' => '2026',
            'keterangan' => 'Setoran dari pengurus UPZ 1',
            'is_verified' => true, // Mencoba memaksa terverifikasi
            'items' => [
                [
                    'nama' => 'Muzakki A',
                    'nominal_zakat' => 500000,
                    'nominal_infak' => 100000,
                ],
            ],
        ];

        $response = $this->actingAs($this->upzUser)->post(route('pengumpulan.upz.store'), $payload);
        $response->assertRedirect(route('pengumpulan.upz.index'));

        $setoran = SetoranUpz::latest('id')->first();
        $this->assertNotNull($setoran);

        // Lembaga ID dipaksa ke UPZ 1 milik user
        $this->assertEquals($this->upz1->id, $setoran->lembaga_id);

        // Status WAJIB 'Menunggu Verifikasi' dan verifier null
        $this->assertEquals('Menunggu Verifikasi', $setoran->status);
        $this->assertNull($setoran->verified_by);
        $this->assertNull($setoran->verified_at);

        // DanaTransaksi TIDAK boleh dibuat (buku kas tetap bersih)
        $kasCount = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->count();
        $this->assertEquals(0, $kasCount);
    }

    public function test_baznas_user_can_store_and_auto_verify(): void
    {
        $payload = [
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'bulan' => 'November',
            'tahun' => '2026',
            'keterangan' => 'Setoran langsung verifikasi',
            'is_verified' => true,
            'catatan_verifikasi' => 'Validasi teller bank',
            'items' => [
                [
                    'nama' => 'Muzakki Baznas',
                    'nominal_zakat' => 1000000,
                    'nominal_infak' => 200000,
                ],
            ],
        ];

        $response = $this->actingAs($this->baznasUser)->post(route('pengumpulan.upz.store'), $payload);
        $response->assertRedirect(route('pengumpulan.upz.index'));

        $setoran = SetoranUpz::latest('id')->first();
        $this->assertEquals('Terverifikasi', $setoran->status);
        $this->assertEquals($this->baznasUser->id, $setoran->verified_by);
        $this->assertNotNull($setoran->verified_at);
        $this->assertEquals('Validasi teller bank', $setoran->catatan_verifikasi);

        // DanaTransaksi terbentuk untuk Zakat dan Infak
        $kas = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->get();
        $this->assertCount(2, $kas);
    }

    public function test_baznas_user_store_with_is_verified_false_leaves_status_menunggu_verifikasi_and_no_cash(): void
    {
        $payload = [
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'bulan' => 'November',
            'tahun' => '2026',
            'keterangan' => 'Setoran draft oleh Admin BAZNAS',
            'is_verified' => false,
            'items' => [
                [
                    'nama' => 'Muzakki Draft BAZNAS',
                    'nominal_zakat' => 750000,
                    'nominal_infak' => 150000,
                ],
            ],
        ];

        $response = $this->actingAs($this->baznasUser)->post(route('pengumpulan.upz.store'), $payload);
        $response->assertRedirect(route('pengumpulan.upz.index'));

        $setoran = SetoranUpz::latest('id')->first();
        $this->assertNotNull($setoran);
        $this->assertEquals('Menunggu Verifikasi', $setoran->status);
        $this->assertNull($setoran->verified_by);
        $this->assertNull($setoran->verified_at);

        // DanaTransaksi TIDAK boleh dibuat (buku kas tetap bersih)
        $kasCount = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->count();
        $this->assertEquals(0, $kasCount);
    }

    public function test_baznas_user_verifikasi_action_syncs_cash(): void
    {
        // Setoran dibuat dengan status Menunggu Verifikasi
        $setoran = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0010',
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 400000,
            'total_infak' => 100000,
            'total_jumlah' => 500000,
            'status' => 'Menunggu Verifikasi',
            'user_id' => $this->upzUser->id,
        ]);

        SetoranUpzItem::create([
            'setoran_upz_id' => $setoran->id,
            'nama' => 'Muzakki Al-Ikhlas',
            'nominal_zakat' => 400000,
            'nominal_infak' => 100000,
            'jumlah' => 500000,
        ]);

        // UPZ user tidak berhak verifikasi (403)
        $resForbidden = $this->actingAs($this->upzUser)->post(route('pengumpulan.upz.verifikasi', $setoran->id));
        $resForbidden->assertStatus(403);

        // BAZNAS user memverifikasi
        $resSuccess = $this->actingAs($this->baznasUser)->post(route('pengumpulan.upz.verifikasi', $setoran->id), [
            'catatan_verifikasi' => 'Sudah dicocokkan dengan mutasi BSI',
        ]);
        $resSuccess->assertRedirect(route('pengumpulan.upz.show', $setoran->id));
        $resSuccess->assertSessionHas('success');

        $setoran->refresh();
        $this->assertEquals('Terverifikasi', $setoran->status);
        $this->assertEquals($this->baznasUser->id, $setoran->verified_by);
        $this->assertNotNull($setoran->verified_at);
        $this->assertEquals('Sudah dicocokkan dengan mutasi BSI', $setoran->catatan_verifikasi);

        // Kas terbentuk
        $kas = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->get();
        $this->assertCount(2, $kas);

        // Double verifikasi tidak membuat duplikat transaksi kas
        $this->actingAs($this->baznasUser)->post(route('pengumpulan.upz.verifikasi', $setoran->id));
        $kasAfter = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->get();
        $this->assertCount(2, $kasAfter);
    }

    public function test_baznas_user_tolak_action_requires_catatan_and_cleans_cash(): void
    {
        $setoran = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0011',
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 300000,
            'total_infak' => 0,
            'total_jumlah' => 300000,
            'status' => 'Terverifikasi', // Misal sebelumnya terverifikasi lalu ditolak
            'user_id' => $this->baznasUser->id,
        ]);

        $danaZakat = Dana::where('kode', '2.2')->first();
        DanaTransaksi::create([
            'tanggal' => '2026-11-03',
            'dana_id' => $danaZakat->id,
            'jenis' => 'Masuk',
            'nominal' => 300000,
            'uraian' => 'Test',
            'referensi_type' => SetoranUpz::class,
            'referensi_id' => $setoran->id,
        ]);

        // UPZ tidak boleh menolak
        $resForbidden = $this->actingAs($this->upzUser)->post(route('pengumpulan.upz.tolak', $setoran->id), [
            'catatan_verifikasi' => 'Alasan penolakan',
        ]);
        $resForbidden->assertStatus(403);

        // Tanpa catatan -> validation error
        $resValidationError = $this->actingAs($this->baznasUser)->post(route('pengumpulan.upz.tolak', $setoran->id), []);
        $resValidationError->assertSessionHasErrors('catatan_verifikasi');

        // Dengan catatan valid
        $resSuccess = $this->actingAs($this->baznasUser)->post(route('pengumpulan.upz.tolak', $setoran->id), [
            'catatan_verifikasi' => 'Bukti transfer tidak terbaca / buram.',
        ]);
        $resSuccess->assertRedirect(route('pengumpulan.upz.show', $setoran->id));

        $setoran->refresh();
        $this->assertEquals('Ditolak', $setoran->status);
        $this->assertEquals($this->baznasUser->id, $setoran->verified_by);
        $this->assertEquals('Bukti transfer tidak terbaca / buram.', $setoran->catatan_verifikasi);

        // DanaTransaksi harus terhapus bersih
        $kasCount = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->count();
        $this->assertEquals(0, $kasCount);
    }

    public function test_edit_and_update_setoran_updates_items_totals_and_cash(): void
    {
        $setoran = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0012',
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 500000,
            'total_infak' => 100000,
            'total_jumlah' => 600000,
            'status' => 'Terverifikasi',
            'user_id' => $this->baznasUser->id,
        ]);

        SetoranUpzItem::create([
            'setoran_upz_id' => $setoran->id,
            'nama' => 'Muzakki Lama',
            'nominal_zakat' => 500000,
            'nominal_infak' => 100000,
            'jumlah' => 600000,
        ]);

        $danaZakat = Dana::where('kode', '2.2')->first();
        $danaInfak = Dana::where('kode', '2.7')->first();

        DanaTransaksi::create([
            'tanggal' => '2026-11-03',
            'dana_id' => $danaZakat->id,
            'jenis' => 'Masuk',
            'nominal' => 500000,
            'uraian' => 'Zakat Lama',
            'referensi_type' => SetoranUpz::class,
            'referensi_id' => $setoran->id,
        ]);

        // UPZ dilarang edit/update
        $this->actingAs($this->upzUser)->get(route('pengumpulan.upz.edit', $setoran->id))->assertStatus(403);
        $this->actingAs($this->upzUser)->put(route('pengumpulan.upz.update', $setoran->id), [])->assertStatus(403);

        // BAZNAS buka halaman edit
        $resEdit = $this->actingAs($this->baznasUser)->get(route('pengumpulan.upz.edit', $setoran->id));
        $resEdit->assertStatus(200);
        $resEdit->assertViewHas('setoran');

        // BAZNAS update data setoran (zakat naik jadi 800rb, infak jadi 200rb)
        $updatePayload = [
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'keterangan' => 'Revisi nilai setoran',
            'catatan_verifikasi' => 'Revisi disetujui',
            'items' => [
                [
                    'nama' => 'Muzakki Baru 1',
                    'nominal_zakat' => 800000,
                    'nominal_infak' => 200000,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                ],
            ],
        ];

        $resUpdate = $this->actingAs($this->baznasUser)->put(route('pengumpulan.upz.update', $setoran->id), $updatePayload);
        $resUpdate->assertRedirect(route('pengumpulan.upz.show', $setoran->id));

        $setoran->refresh();
        $this->assertEquals(800000, $setoran->total_zakat);
        $this->assertEquals(200000, $setoran->total_infak);
        $this->assertEquals(1000000, $setoran->total_jumlah);
        $this->assertEquals('Revisi nilai setoran', $setoran->keterangan);

        $items = $setoran->items;
        $this->assertCount(1, $items);
        $this->assertEquals('Muzakki Baru 1', $items->first()->nama);

        // DanaTransaksi tersinkron ke nominal baru
        $kas = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->get();
        $this->assertCount(2, $kas);
        $this->assertEquals(800000, $kas->firstWhere('dana_id', $danaZakat->id)->nominal);
        $this->assertEquals(200000, $kas->firstWhere('dana_id', $danaInfak->id)->nominal);
    }

    public function test_upz_detail_tab_muzakki_with_search(): void
    {
        $person1 = Person::create(['nama' => 'Farhan Muzakki', 'nik' => '3301111111110001']);
        $person2 = Person::create(['nama' => 'Siti Aisyah', 'nik' => '3301111111110002']);

        $m1 = Muzakki::create([
            'person_id' => $person1->id,
            'lembaga_id' => $this->upz1->id,
            'npwz' => 'NPWZ-FARHAN-01',
            'nrm' => 'NRM-001',
        ]);

        $m2 = Muzakki::create([
            'person_id' => $person2->id,
            'lembaga_id' => $this->upz1->id,
            'npwz' => 'NPWZ-AISYAH-02',
            'nrm' => 'NRM-002',
        ]);

        // Default tab muzakki list
        $response = $this->actingAs($this->baznasUser)->get(route('manajemen.upz.detail', [
            'id' => $this->upz1->id,
            'tab' => 'muzakki',
        ]));
        $response->assertStatus(200);
        $response->assertViewHas('muzakkis');
        $this->assertEquals(2, $response->viewData('muzakkis')->total());

        // Search by nama Farhan
        $resSearch = $this->actingAs($this->baznasUser)->get(route('manajemen.upz.detail', [
            'id' => $this->upz1->id,
            'tab' => 'muzakki',
            'muzakki_search' => 'Farhan',
        ]));
        $resSearch->assertStatus(200);
        $muzakkisSearch = $resSearch->viewData('muzakkis');
        $this->assertEquals(1, $muzakkisSearch->total());
        $this->assertEquals('Farhan Muzakki', $muzakkisSearch->first()->person->nama);

        // Search by NIK Siti
        $resNik = $this->actingAs($this->baznasUser)->get(route('manajemen.upz.detail', [
            'id' => $this->upz1->id,
            'tab' => 'muzakki',
            'muzakki_search' => '3301111111110002',
        ]));
        $this->assertEquals(1, $resNik->viewData('muzakkis')->total());
        $this->assertEquals('Siti Aisyah', $resNik->viewData('muzakkis')->first()->person->nama);
    }

    public function test_upz_detail_tab_muzakki_pagination(): void
    {
        // Buat total 20 muzakki untuk UPZ 1 (per page = 15)
        for ($i = 1; $i <= 20; $i++) {
            $nikSuffix = str_pad((string)$i, 4, '0', STR_PAD_LEFT);
            $person = Person::create([
                'nama' => "Muzakki Pagination {$i}",
                'nik' => "330100000000{$nikSuffix}",
            ]);
            Muzakki::create([
                'person_id' => $person->id,
                'lembaga_id' => $this->upz1->id,
                'npwz' => "NPWZ-PAGINATE-{$i}",
                'nrm' => "NRM-PAGINATE-{$i}",
            ]);
        }

        // Halaman 1
        $resPage1 = $this->actingAs($this->baznasUser)->get(route('manajemen.upz.detail', [
            'id' => $this->upz1->id,
            'tab' => 'muzakki',
            'muzakki_page' => 1,
        ]));
        $resPage1->assertStatus(200);
        $paginator1 = $resPage1->viewData('muzakkis');
        $this->assertEquals(20, $paginator1->total());
        $this->assertEquals(15, $paginator1->count());
        $this->assertEquals(1, $paginator1->currentPage());
        $this->assertTrue($paginator1->hasPages());

        // Halaman 2
        $resPage2 = $this->actingAs($this->baznasUser)->get(route('manajemen.upz.detail', [
            'id' => $this->upz1->id,
            'tab' => 'muzakki',
            'muzakki_page' => 2,
        ]));
        $resPage2->assertStatus(200);
        $paginator2 = $resPage2->viewData('muzakkis');
        $this->assertEquals(20, $paginator2->total());
        $this->assertEquals(5, $paginator2->count());
        $this->assertEquals(2, $paginator2->currentPage());
    }

    public function test_destroy_authorization_rules(): void
    {
        // 1. UPZ can delete their own 'Menunggu Verifikasi' setoran
        $setoranPendingUpz1 = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0010',
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 100000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 100000,
            'status' => 'Menunggu Verifikasi',
            'user_id' => $this->upzUser->id,
        ]);

        $resDeleteOwnPending = $this->actingAs($this->upzUser)->delete(route('pengumpulan.upz.destroy', $setoranPendingUpz1->id));
        $resDeleteOwnPending->assertRedirect(route('pengumpulan.upz.index'));
        $this->assertDatabaseMissing('setoran_upzs', ['id' => $setoranPendingUpz1->id]);

        // 2. UPZ CANNOT delete their own 'Terverifikasi' setoran
        $setoranVerifiedUpz1 = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0011',
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 100000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 100000,
            'status' => 'Terverifikasi',
            'user_id' => $this->upzUser->id,
        ]);

        $resDeleteOwnVerified = $this->actingAs($this->upzUser)->delete(route('pengumpulan.upz.destroy', $setoranVerifiedUpz1->id));
        $resDeleteOwnVerified->assertStatus(403);
        $this->assertDatabaseHas('setoran_upzs', ['id' => $setoranVerifiedUpz1->id]);

        // 3. UPZ CANNOT delete another UPZ's setoran
        $setoranUpz2 = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0012',
            'lembaga_id' => $this->upz2->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 100000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 100000,
            'status' => 'Menunggu Verifikasi',
            'user_id' => $this->baznasUser->id,
        ]);

        $resDeleteOther = $this->actingAs($this->upzUser)->delete(route('pengumpulan.upz.destroy', $setoranUpz2->id));
        $resDeleteOther->assertStatus(403);
        $this->assertDatabaseHas('setoran_upzs', ['id' => $setoranUpz2->id]);

        // 4. BAZNAS user CAN delete any setoran
        $resDeleteBaznas = $this->actingAs($this->baznasUser)->delete(route('pengumpulan.upz.destroy', $setoranUpz2->id));
        $resDeleteBaznas->assertRedirect(route('pengumpulan.upz.index'));
        $this->assertDatabaseMissing('setoran_upzs', ['id' => $setoranUpz2->id]);
    }

    public function test_tenant_scoping_for_upz_user_in_index_show_cetak_upload(): void
    {
        $setoranUpz1 = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0021',
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 50000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 50000,
            'status' => 'Menunggu Verifikasi',
        ]);

        $setoranUpz2 = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0022',
            'lembaga_id' => $this->upz2->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 75000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 75000,
            'status' => 'Menunggu Verifikasi',
        ]);

        // Index scoping: even if asking for upz2, UPZ user only gets upz1
        $resIndex = $this->actingAs($this->upzUser)->get(route('pengumpulan.upz.index', ['lembaga_id' => $this->upz2->id]));
        $resIndex->assertStatus(200);
        $paginator = $resIndex->viewData('setorans');
        $this->assertTrue($paginator->contains('id', $setoranUpz1->id));
        $this->assertFalse($paginator->contains('id', $setoranUpz2->id));

        // Show scoping
        $this->actingAs($this->upzUser)->get(route('pengumpulan.upz.show', $setoranUpz1->id))->assertStatus(200);
        $this->actingAs($this->upzUser)->get(route('pengumpulan.upz.show', $setoranUpz2->id))->assertStatus(403);

        // Cetak scoping
        $this->actingAs($this->upzUser)->get(route('pengumpulan.upz.cetak', $setoranUpz1->id))->assertStatus(200);
        $this->actingAs($this->upzUser)->get(route('pengumpulan.upz.cetak', $setoranUpz2->id))->assertStatus(403);

        // Upload bukti scoping
        $file = \Illuminate\Http\UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');
        $resUploadOwn = $this->actingAs($this->upzUser)->post(route('pengumpulan.upz.upload-bukti', $setoranUpz1->id), [
            'bukti_setor' => $file,
        ]);
        $resUploadOwn->assertSessionHas('success');

        $resUploadOther = $this->actingAs($this->upzUser)->post(route('pengumpulan.upz.upload-bukti', $setoranUpz2->id), [
            'bukti_setor' => $file,
        ]);
        $resUploadOther->assertStatus(403);
    }

    public function test_tamper_proofing_for_quick_store_and_import_confirm(): void
    {
        // 1. quickStoreMuzakki tamper attempt: pass upz2 lembaga_id
        $resQuickStore = $this->actingAs($this->upzUser)->postJson(route('pengumpulan.upz.quick-store-muzakki'), [
            'nama' => 'Muzakki Tamper Test',
            'nik' => '3301011111110099',
            'lembaga_id' => $this->upz2->id,
            'jenis_kelamin' => 'L',
        ]);
        $resQuickStore->assertStatus(200);
        $muzakkiId = $resQuickStore->json('muzakki.id');

        $muzakki = Muzakki::find($muzakkiId);
        $this->assertNotNull($muzakki);
        // Lembaga ID must be forced to upz1
        $this->assertEquals($this->upz1->id, $muzakki->lembaga_id);

        // 2. importConfirm tamper attempt: pass upz2 lembaga_id in staging
        session()->put('import_setoran_staging', [
            'lembaga_id' => $this->upz2->id,
            'tanggal_setor' => '2026-11-03',
            'bulan' => 'November',
            'tahun' => 2026,
            'periode' => 'November 2026',
            'keterangan' => 'Import tamper test',
            'items' => [
                [
                    'row_number' => 1,
                    'nama' => 'Muzakki Import Tamper',
                    'nominal_zakat' => 50000,
                    'nominal_infak' => 0,
                    'nominal_fitrah' => 0,
                    'nominal_titipan' => 0,
                ],
            ],
        ]);

        $resConfirm = $this->actingAs($this->upzUser)->post(route('pengumpulan.upz.import-confirm'), [
            'lembaga_id' => $this->upz2->id,
        ]);
        $resConfirm->assertRedirect(route('pengumpulan.upz.index'));

        $lastSetoran = SetoranUpz::latest('id')->first();
        $this->assertNotNull($lastSetoran);
        // Lembaga ID must be forced to upz1
        $this->assertEquals($this->upz1->id, $lastSetoran->lembaga_id);
    }

    public function test_ledger_synchronization_for_fitrah_and_titipan(): void
    {
        $danaFitrah = Dana::where('kode', '2.5')->first();
        $danaTitipan = Dana::where('kode', '2.18')->first();
        $this->assertNotNull($danaFitrah);
        $this->assertNotNull($danaTitipan);

        $setoran = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0033',
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 0,
            'total_infak' => 0,
            'total_fitrah' => 350000,
            'total_titipan' => 150000,
            'total_jumlah' => 500000,
            'status' => 'Menunggu Verifikasi',
        ]);

        // Verifikasi oleh BAZNAS
        $resVerif = $this->actingAs($this->baznasUser)->post(route('pengumpulan.upz.verifikasi', $setoran->id), [
            'catatan_verifikasi' => 'Valid',
        ]);
        $resVerif->assertRedirect(route('pengumpulan.upz.show', $setoran->id));

        $setoran->refresh();
        $this->assertEquals('Terverifikasi', $setoran->status);

        // Cek DanaTransaksi untuk fitrah dan titipan
        $kasFitrah = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->where('dana_id', $danaFitrah->id)
            ->first();
        $this->assertNotNull($kasFitrah);
        $this->assertEquals(350000, $kasFitrah->nominal);
        $this->assertStringContainsString('Penerimaan Zakat Fitrah via', $kasFitrah->uraian);

        $kasTitipan = DanaTransaksi::where('referensi_type', SetoranUpz::class)
            ->where('referensi_id', $setoran->id)
            ->where('dana_id', $danaTitipan->id)
            ->first();
        $this->assertNotNull($kasTitipan);
        $this->assertEquals(150000, $kasTitipan->nominal);
        $this->assertStringContainsString('Penerimaan Dana Titipan via', $kasTitipan->uraian);
    }

    public function test_simba_kas_masuk_only_exports_verified_transactions(): void
    {
        // Setoran Terverifikasi
        $setoranVerified = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0041',
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 100000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 100000,
            'status' => 'Terverifikasi',
        ]);
        $itemVerified = SetoranUpzItem::create([
            'setoran_upz_id' => $setoranVerified->id,
            'nama' => 'Muzakki Terverifikasi',
            'nominal_zakat' => 100000,
            'nominal_infak' => 0,
            'nominal_fitrah' => 0,
            'nominal_titipan' => 0,
            'jumlah' => 100000,
        ]);

        // Setoran Menunggu Verifikasi
        $setoranPending = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0042',
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 200000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 200000,
            'status' => 'Menunggu Verifikasi',
        ]);
        $itemPending = SetoranUpzItem::create([
            'setoran_upz_id' => $setoranPending->id,
            'nama' => 'Muzakki Pending',
            'nominal_zakat' => 200000,
            'nominal_infak' => 0,
            'nominal_fitrah' => 0,
            'nominal_titipan' => 0,
            'jumlah' => 200000,
        ]);

        // Setoran Ditolak
        $setoranRejected = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/0043',
            'lembaga_id' => $this->upz1->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 300000,
            'total_infak' => 0,
            'total_fitrah' => 0,
            'total_titipan' => 0,
            'total_jumlah' => 300000,
            'status' => 'Ditolak',
        ]);
        $itemRejected = SetoranUpzItem::create([
            'setoran_upz_id' => $setoranRejected->id,
            'nama' => 'Muzakki Ditolak',
            'nominal_zakat' => 300000,
            'nominal_infak' => 0,
            'nominal_fitrah' => 0,
            'nominal_titipan' => 0,
            'jumlah' => 300000,
        ]);

        // View test
        $resView = $this->actingAs($this->baznasUser)->get(route('simba.kas-masuk'));
        $resView->assertStatus(200);
        $items = $resView->viewData('items');
        $this->assertTrue($items->contains('id', $itemVerified->id));
        $this->assertFalse($items->contains('id', $itemPending->id));
        $this->assertFalse($items->contains('id', $itemRejected->id));

        // CSV export test
        $resExport = $this->actingAs($this->baznasUser)->get(route('simba.kas-masuk', ['download' => 'csv']));
        $resExport->assertStatus(200);
        $resExport->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csvContent = $resExport->streamedContent();
        $this->assertStringContainsString('Muzakki Terverifikasi', $csvContent);
        $this->assertStringNotContainsString('Muzakki Pending', $csvContent);
        $this->assertStringNotContainsString('Muzakki Ditolak', $csvContent);
    }
}

