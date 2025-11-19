<?php

namespace Tests\Feature;

use App\Models\Lembaga;
use App\Models\Mustahik;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanMustahik;
use App\Models\PengajuanPersetujuan;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Person;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createQaUser(string $role, string $kind = 'Baznas', ?Lembaga $upz = null): User
{
    $person = Person::create([
        'nama' => 'User ' . $role . ' ' . uniqid(),
        'nik'  => substr(md5(uniqid()), 0, 16),
    ]);

    $user = User::create([
        'person_id' => $person->id,
        'email'     => strtolower(str_replace(' ', '.', $role)) . uniqid() . '@test.local',
        'password'  => bcrypt('secret'),
    ]);

    $tingkat = $kind === 'Baznas' ? 'baznas' : 'lembaga';
    $jabatan = PengurusJabatan::firstOrCreate(
        ['nama' => $role, 'tingkat' => $tingkat],
        ['kode' => uniqid(), 'is_active' => true]
    );

    Pengurus::create([
        'jenis_pengurus'      => $kind,
        'person_id'           => $person->id,
        'lembaga_id'          => $upz?->id,
        'pengurus_jabatan_id' => $jabatan->id,
        'is_active'           => true,
    ]);

    return $user;
}

function createQaUpz(string $name): Lembaga
{
    return Lembaga::create([
        'nama'      => $name,
        'is_upz'    => true,
        'is_active' => true,
    ]);
}

function createQaPengajuan(User $creator, string $type = 'Individu', ?Lembaga $lembaga = null): PengajuanBantuan
{
    return PengajuanBantuan::create([
        'nomor_pengajuan'   => uniqid('PB-'),
        'judul_surat'       => 'Pengajuan QA Test ' . $type,
        'jenis_permohonan'  => $type,
        'lembaga_id'        => $lembaga?->id,
        'tanggal_diterima'  => now()->toDateString(),
        'uraian_permohonan' => 'Deskripsi Pengajuan',
        'created_by'        => $creator->id,
    ]);
}

function addQaMustahik(PengajuanBantuan $pengajuan, int $nominal = 1000000): PengajuanMustahik
{
    $program = Program::firstOrCreate(['nama' => 'Program QA']);
    $person = Person::create(['nama' => 'Mustahik QA ' . uniqid(), 'nik' => substr(md5(uniqid()), 0, 16)]);
    $mustahik = Mustahik::create(['person_id' => $person->id]);

    return PengajuanMustahik::create([
        'pengajuan_id' => $pengajuan->id,
        'mustahik_id'  => $mustahik->id,
        'program_id'   => $program->id,
        'nominal'      => $nominal,
    ]);
}

/* =========================================================================
 * 1. CRITICAL VALIDATION BUG FIX (PengajuanShowController storePersetujuan)
 * ========================================================================= */

describe('1. Critical Validation Bug Fix in storePersetujuan', function () {
    beforeEach(function () {
        $this->admin = createQaUser('Admin BAZNAS');
        $this->pimpinan = createQaUser('Pimpinan BAZNAS');
        $this->pengajuanUmum = createQaPengajuan($this->admin, 'Individu');
        addQaMustahik($this->pengajuanUmum, 1000000);
    });

    test('validates nominal_disetujui must be numeric', function () {
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'status'            => 'disetujui',
                'nominal_disetujui' => 'bukan-angka',
            ]);

        $resp->assertSessionHasErrors(['nominal_disetujui']);
    });

    test('validates nominal_disetujui cannot be negative', function () {
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'status'            => 'disetujui',
                'nominal_disetujui' => -50000,
            ]);

        $resp->assertSessionHasErrors(['nominal_disetujui']);
    });

    test('validates nominal_disetujui cannot exceed 999999999999 max limit', function () {
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'status'            => 'disetujui',
                'nominal_disetujui' => 1000000000000,
            ]);

        $resp->assertSessionHasErrors(['nominal_disetujui']);
    });

    test('business logic rejects oversized approval amount exceeding total requested nominal', function () {
        // total requested is 1,000,000; attempt to approve 1,500,000
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'status'            => 'disetujui',
                'nominal_disetujui' => 1500000,
            ]);

        $resp->assertSessionHasErrors([
            'nominal_disetujui' => 'Jumlah persetujuan tidak dapat melebihi total dana yang diajukan.',
            'error'             => 'Validasi jumlah persetujuan tidak valid.',
        ]);

        // Verify DB record was not created
        expect(PengajuanPersetujuan::where('pengajuan_id', $this->pengajuanUmum->id)->exists())->toBeFalse();
    });

    test('allows valid approval amount less than or equal to total requested nominal', function () {
        // total requested is 1,000,000; approve partial 750,000
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'status'            => 'disetujui',
                'nominal_disetujui' => 750000,
            ]);

        $resp->assertSessionHasNoErrors();
        $resp->assertRedirect();

        $persetujuan = PengajuanPersetujuan::where('pengajuan_id', $this->pengajuanUmum->id)->first();
        expect($persetujuan)->not->toBeNull()
            ->and($persetujuan->status)->toBe('disetujui')
            ->and((int) $persetujuan->nominal_disetujui)->toBe(750000);
    });

    test('empty payload defaults to status disetujui and nominal equals total', function () {
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), []);

        $resp->assertSessionHasNoErrors();
        $resp->assertRedirect();

        $persetujuan = PengajuanPersetujuan::where('pengajuan_id', $this->pengajuanUmum->id)->first();
        expect($persetujuan)->not->toBeNull()
            ->and($persetujuan->status)->toBe('disetujui')
            ->and((int) $persetujuan->nominal_disetujui)->toBe(1000000);
    });

    test('validates status must be one of disetujui, ditolak, tidak_dicairkan', function () {
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'status'            => 'pending_approval',
                'nominal_disetujui' => 500000,
            ]);

        $resp->assertSessionHasErrors(['status']);
    });

    test('validates referensi_dana must be array', function () {
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'status'            => 'disetujui',
                'nominal_disetujui' => 500000,
                'referensi_dana'    => 'bukan_array',
            ]);

        $resp->assertSessionHasErrors(['referensi_dana']);
    });

    test('validates catatan cannot exceed 1000 characters', function () {
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'status'            => 'disetujui',
                'nominal_disetujui' => 500000,
                'catatan'           => str_repeat('a', 1001),
            ]);

        $resp->assertSessionHasErrors(['catatan']);
    });

    test('handles pengajuan with zero mustahik and nominal zero', function () {
        $emptyPengajuan = createQaPengajuan($this->admin, 'Individu');
        // Total requested is 0

        // Attempting to approve > 0 should be rejected
        $respFail = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $emptyPengajuan->id), [
                'status'            => 'disetujui',
                'nominal_disetujui' => 50000,
            ]);
        $respFail->assertSessionHasErrors(['nominal_disetujui']);

        // Approving with 0 should succeed
        $respPass = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $emptyPengajuan->id), [
                'status'            => 'disetujui',
                'nominal_disetujui' => 0,
            ]);
        $respPass->assertSessionHasNoErrors();
        $respPass->assertRedirect();
    });

    test('sending status disetujui without nominal_disetujui succeeds with default fallback to total', function () {
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'status' => 'disetujui',
            ]);

        $resp->assertSessionHasNoErrors();
        $resp->assertRedirect();

        $persetujuan = PengajuanPersetujuan::where('pengajuan_id', $this->pengajuanUmum->id)->first();
        expect($persetujuan)->not->toBeNull()
            ->and($persetujuan->status)->toBe('disetujui')
            ->and((int) $persetujuan->nominal_disetujui)->toBe(1000000);
    });

    test('rejection does not require nominal_disetujui', function () {
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'status'  => 'ditolak',
                'catatan' => 'Permohonan ditolak karena syarat tidak terpenuhi',
            ]);

        $resp->assertSessionHasNoErrors();
        $resp->assertRedirect();

        $persetujuan = PengajuanPersetujuan::where('pengajuan_id', $this->pengajuanUmum->id)->first();
        expect($persetujuan)->not->toBeNull()
            ->and($persetujuan->status)->toBe('ditolak');
    });

    test('tidak_dicairkan status does not require nominal_disetujui', function () {
        $resp = $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'status'  => 'tidak_dicairkan',
                'catatan' => 'Dana tidak dapat dicairkan',
            ]);

        $resp->assertSessionHasNoErrors();
        $resp->assertRedirect();

        $persetujuan = PengajuanPersetujuan::where('pengajuan_id', $this->pengajuanUmum->id)->first();
        expect($persetujuan)->not->toBeNull()
            ->and($persetujuan->status)->toBe('tidak_dicairkan');
    });
});

/* =========================================================================
 * 2. AUTHORIZATION SCOPE VERIFICATION (User.php canPerformPengajuanAction)
 * ========================================================================= */

describe('2. Authorization Scope Verification in User model', function () {
    beforeEach(function () {
        $this->upz1 = createQaUpz('UPZ Baitul Mal 1');
        $this->upz2 = createQaUpz('UPZ Baitul Mal 2');

        $this->admin = createQaUser('Admin BAZNAS');
        $this->pimpinan = createQaUser('Pimpinan BAZNAS');
        $this->staff = createQaUser('Staff Penyaluran');

        $this->ketuaUpz1 = createQaUser('Ketua', 'Lembaga', $this->upz1);
        $this->sekretarisUpz1 = createQaUser('Sekretaris', 'Lembaga', $this->upz1);
        $this->bendaharaUpz1 = createQaUser('Bendahara', 'Lembaga', $this->upz1);
        $this->ketuaUpz2 = createQaUser('Ketua', 'Lembaga', $this->upz2);

        $this->pengajuanUpz1 = createQaPengajuan($this->admin, 'Lembaga', $this->upz1);
        $this->pengajuanUmum = createQaPengajuan($this->admin, 'Individu');
    });

    test('approve-upz permission is strictly granted to UPZ Ketua for own UPZ', function () {
        // UPZ Ketua on own UPZ application -> true
        expect($this->ketuaUpz1->canPerformPengajuanAction('approve-upz', $this->pengajuanUpz1))->toBeTrue();

        // UPZ Ketua on different UPZ application -> false
        expect($this->ketuaUpz2->canPerformPengajuanAction('approve-upz', $this->pengajuanUpz1))->toBeFalse();

        // UPZ Ketua on Umum application -> false
        expect($this->ketuaUpz1->canPerformPengajuanAction('approve-upz', $this->pengajuanUmum))->toBeFalse();

        // UPZ Sekretaris on own UPZ application -> false
        expect($this->sekretarisUpz1->canPerformPengajuanAction('approve-upz', $this->pengajuanUpz1))->toBeFalse();

        // UPZ Bendahara on own UPZ application -> false
        expect($this->bendaharaUpz1->canPerformPengajuanAction('approve-upz', $this->pengajuanUpz1))->toBeFalse();

        // Pimpinan BAZNAS on UPZ application -> false
        expect($this->pimpinan->canPerformPengajuanAction('approve-upz', $this->pengajuanUpz1))->toBeFalse();

        // Admin BAZNAS on UPZ application -> false
        expect($this->admin->canPerformPengajuanAction('approve-upz', $this->pengajuanUpz1))->toBeFalse();
    });

    test('approve-umum permission is strictly granted to Pimpinan BAZNAS for Umum only', function () {
        // Pimpinan BAZNAS on Umum application -> true
        expect($this->pimpinan->canPerformPengajuanAction('approve-umum', $this->pengajuanUmum))->toBeTrue();

        // Pimpinan BAZNAS on UPZ application -> false (scoped out!)
        expect($this->pimpinan->canPerformPengajuanAction('approve-umum', $this->pengajuanUpz1))->toBeFalse();

        // UPZ Ketua on Umum application -> false
        expect($this->ketuaUpz1->canPerformPengajuanAction('approve-umum', $this->pengajuanUmum))->toBeFalse();

        // Admin BAZNAS on Umum application -> false
        expect($this->admin->canPerformPengajuanAction('approve-umum', $this->pengajuanUmum))->toBeFalse();

        // Staff Penyaluran on Umum application -> false
        expect($this->staff->canPerformPengajuanAction('approve-umum', $this->pengajuanUmum))->toBeFalse();
    });

    test('legacy approve permission maintains backward compatibility', function () {
        // UPZ Ketua on own UPZ application -> true
        expect($this->ketuaUpz1->canPerformPengajuanAction('approve', $this->pengajuanUpz1))->toBeTrue();

        // Pimpinan BAZNAS on Umum application -> true
        expect($this->pimpinan->canPerformPengajuanAction('approve', $this->pengajuanUmum))->toBeTrue();

        // Pimpinan BAZNAS on UPZ application -> true (legacy route allows Pimpinan BAZNAS)
        expect($this->pimpinan->canPerformPengajuanAction('approve', $this->pengajuanUpz1))->toBeTrue();

        // UPZ Ketua on Umum application -> false
        expect($this->ketuaUpz1->canPerformPengajuanAction('approve', $this->pengajuanUmum))->toBeFalse();

        // Admin BAZNAS and Staff -> false
        expect($this->admin->canPerformPengajuanAction('approve', $this->pengajuanUmum))->toBeFalse();
        expect($this->staff->canPerformPengajuanAction('approve', $this->pengajuanUmum))->toBeFalse();
    });
});

/* =========================================================================
 * 3. ROUTE SEPARATION (routes/web.php)
 * ========================================================================= */

describe('3. Route Separation and Middleware Restrictions', function () {
    beforeEach(function () {
        $this->upz1 = createQaUpz('UPZ Route 1');
        $this->upz2 = createQaUpz('UPZ Route 2');

        $this->admin = createQaUser('Admin BAZNAS');
        $this->pimpinan = createQaUser('Pimpinan BAZNAS');
        $this->staff = createQaUser('Staff Penyaluran');
        $this->ketuaUpz1 = createQaUser('Ketua', 'Lembaga', $this->upz1);
        $this->ketuaUpz2 = createQaUser('Ketua', 'Lembaga', $this->upz2);

        $this->pengajuanUpz1 = createQaPengajuan($this->admin, 'Lembaga', $this->upz1);
        addQaMustahik($this->pengajuanUpz1, 500000);

        $this->pengajuanUmum = createQaPengajuan($this->admin, 'Individu');
        addQaMustahik($this->pengajuanUmum, 500000);
    });

    test('route /pengajuan/upz/{id}/persetujuan enforces approve-upz middleware', function () {
        // UPZ Ketua can access own UPZ approval
        $this->actingAs($this->ketuaUpz1)
            ->post(route('pengajuan.upz.persetujuan.store', $this->pengajuanUpz1->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertRedirect();

        // Pimpinan BAZNAS gets 403 Forbidden on UPZ-specific route
        $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.upz.persetujuan.store', $this->pengajuanUpz1->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertForbidden();

        // Ketua of different UPZ gets 404 (isolated by canAccessPengajuan)
        $this->actingAs($this->ketuaUpz2)
            ->post(route('pengajuan.upz.persetujuan.store', $this->pengajuanUpz1->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertNotFound();

        // Pimpinan BAZNAS targeting Umum pengajuan via UPZ route gets 403
        $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.upz.persetujuan.store', $this->pengajuanUmum->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertForbidden();
    });

    test('route /pengajuan/umum/{id}/persetujuan enforces approve-umum middleware', function () {
        // Pimpinan BAZNAS can access Umum approval
        $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertRedirect();

        // UPZ Ketua gets 404 (isolated by canAccessPengajuan) on Umum route
        $this->actingAs($this->ketuaUpz1)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertNotFound();

        // Pimpinan BAZNAS targeting UPZ pengajuan via Umum route gets 403 Forbidden
        $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUpz1->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertForbidden();

        // Staff Penyaluran gets 403 Forbidden
        $this->actingAs($this->staff)
            ->post(route('pengajuan.umum.persetujuan.store', $this->pengajuanUmum->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertForbidden();
    });

    test('legacy route /pengajuan/{id}/persetujuan continues to work with approve middleware', function () {
        // UPZ Ketua can access own UPZ pengajuan via legacy route
        $this->actingAs($this->ketuaUpz1)
            ->post(route('pengajuan.persetujuan.store', $this->pengajuanUpz1->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertRedirect();

        // Pimpinan BAZNAS can access Umum pengajuan via legacy route
        $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.persetujuan.store', $this->pengajuanUmum->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertRedirect();

        // Pimpinan BAZNAS can also access UPZ pengajuan via legacy route (backward compat)
        $this->actingAs($this->pimpinan)
            ->post(route('pengajuan.persetujuan.store', $this->pengajuanUpz1->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertRedirect();

        // Staff Penyaluran gets 403
        $this->actingAs($this->staff)
            ->post(route('pengajuan.persetujuan.store', $this->pengajuanUmum->id), [
                'nominal_disetujui' => 500000,
            ])
            ->assertForbidden();
    });
});

/* =========================================================================
 * 4. INTEGRATION TESTING & END-TO-END PERSISTENCE
 * ========================================================================= */

describe('4. Integration Testing & Workflow', function () {
    test('end-to-end UPZ approval persists record with user id and proper amount', function () {
        $upz = createQaUpz('UPZ Integrated');
        $admin = createQaUser('Admin BAZNAS');
        $ketua = createQaUser('Ketua', 'Lembaga', $upz);

        $pengajuan = createQaPengajuan($admin, 'Lembaga', $upz);
        addQaMustahik($pengajuan, 1200000);
        addQaMustahik($pengajuan, 800000); // total 2,000,000

        $resp = $this->actingAs($ketua)
            ->post(route('pengajuan.upz.persetujuan.store', $pengajuan->id), [
                'nominal_disetujui' => 1800000,
                'catatan'           => 'Disetujui sebagian oleh Ketua UPZ',
                'referensi_dana'    => ['kas_upz'],
            ]);

        $resp->assertSessionHasNoErrors();
        $resp->assertRedirect();

        $persetujuan = PengajuanPersetujuan::where('pengajuan_id', $pengajuan->id)->first();
        expect($persetujuan)->not->toBeNull()
            ->and($persetujuan->status)->toBe('disetujui')
            ->and((int) $persetujuan->nominal_disetujui)->toBe(1800000)
            ->and($persetujuan->approved_by)->toBe($ketua->id)
            ->and($persetujuan->catatan)->toBe('Disetujui sebagian oleh Ketua UPZ')
            ->and($persetujuan->referensi_dana)->toBe(['kas_upz']);
    });

    test('UI integration: persetujuan tab renders scoped route and form submission succeeds without explicit nominal_disetujui', function () {
        $admin = createQaUser('Admin BAZNAS');
        $pimpinan = createQaUser('Pimpinan BAZNAS');
        $pengajuan = createQaPengajuan($admin, 'Individu');
        addQaMustahik($pengajuan, 1000000);

        // 1. Verify UI view renders the persetujuan form action pointing to scoped route
        $viewResp = $this->actingAs($pimpinan)
            ->get(route('pengajuan.umum.show', ['pengajuan' => $pengajuan->id, 'tab' => 'persetujuan']));
        $viewResp->assertOk();
        $viewResp->assertSee(route('pengajuan.umum.persetujuan.store', $pengajuan->id));

        // 2. Simulate user clicking the "Finalisasi Persetujuan Pimpinan" button from blade template
        // Blade has: <input type="hidden" name="status" value="disetujui"> with no nominal_disetujui input
        $submitResp = $this->actingAs($pimpinan)
            ->post(route('pengajuan.umum.persetujuan.store', $pengajuan->id), [
                'status' => 'disetujui',
            ]);

        $submitResp->assertSessionHasNoErrors();
        $submitResp->assertRedirect();
        expect(PengajuanPersetujuan::where('pengajuan_id', $pengajuan->id)->exists())->toBeTrue();
    });
});
