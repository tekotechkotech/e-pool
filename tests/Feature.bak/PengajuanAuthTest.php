<?php

use App\Models\User;
use App\Models\Person;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Lampiran;
use App\Models\PengajuanBantuan;
use App\Models\Mustahik;
use App\Models\Lembaga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function makeUserWithRole(string $jabatanNama, string $jenis = 'Baznas', ?Lembaga $lembaga = null): User
{
    $person = Person::create(['nama' => 'Tester ' . $jabatanNama . uniqid(), 'nik' => substr(md5(uniqid()), 0, 16)]);
    $user = User::create([
        'person_id' => $person->id,
        'email'     => strtolower(str_replace(' ', '.', $jabatanNama)) . uniqid() . '@test.local',
        'password'  => bcrypt('password'),
    ]);

    $pj = PengurusJabatan::where('nama', $jabatanNama)->first();
    if (!$pj) {
        $pj = PengurusJabatan::create([
            'kode'      => substr(md5($jabatanNama), 0, 6),
            'nama'      => $jabatanNama,
            'tingkat'   => $jenis === 'Baznas' ? 'baznas' : 'lembaga',
            'is_active' => true,
        ]);
    }

    Pengurus::create([
        'jenis_pengurus'      => $jenis,
        'person_id'           => $person->id,
        'pengurus_jabatan_id' => $pj->id,
        'lembaga_id'          => $lembaga?->id,
        'is_active'           => true,
    ]);

    return $user;
}

function makeUserWithoutRole(): User
{
    $person = Person::create(['nama' => 'Plain User ' . uniqid(), 'nik' => substr(md5(uniqid()), 0, 16)]);
    $user = User::create([
        'person_id' => $person->id,
        'email'     => 'plain' . uniqid() . '@test.local',
        'password'  => bcrypt('password'),
    ]);

    // Create a Pengurus record with BAZNAS type but no valid jabatan
    // This makes canAccessPengajuan return true (isBaznasRole checks specific jabatan names)
    // but canPerformPengajuanAction will fail because no valid role
    $jabatan = PengurusJabatan::create([
        'kode'      => 'JB-TEST',
        'nama'      => 'Test Role',
        'tingkat'   => 'baznas',
        'is_active' => true,
    ]);

    Pengurus::create([
        'jenis_pengurus'      => 'Baznas',
        'person_id'           => $person->id,
        'pengurus_jabatan_id' => $jabatan->id,
        'is_active'           => true,
    ]);

    return $user;
}

function makePengajuan(User $creator): PengajuanBantuan
{
    $person = Person::create(['nama' => 'Mustahik Uji', 'nik' => sprintf('%016d', random_int(1, 9999999999))]);
    $mustahik = Mustahik::create(['person_id' => $person->id]);

    return PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-2026-' . rand(1000, 9999),
        'judul_surat'     => 'Permohonan Uji',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Uji akses mutasi',
        'mustahik_id'     => $mustahik->id,
        'created_by'      => $creator->id,
    ]);
}

beforeEach(function () {
    $this->admin = makeUserWithRole('Admin BAZNAS');
});

test('attachment delete scoped to parent pengajuan', function () {
    Storage::fake('public');
    $a = makePengajuan($this->admin);
    $b = makePengajuan($this->admin);

    $pathA = 'lampiran_pengajuan/test-a.txt';
    $pathB = 'lampiran_pengajuan/test-b.txt';
    Storage::disk('public')->put($pathA, 'a');
    Storage::disk('public')->put($pathB, 'b');

    $lampA = Lampiran::create(['file_path' => $pathA, 'lampiranable_type' => $a::class, 'lampiranable_id' => $a->id, 'name' => 'A', 'category' => 'pendukung']);
    $lampB = Lampiran::create(['file_path' => $pathB, 'lampiranable_type' => $b::class, 'lampiranable_id' => $b->id, 'name' => 'B', 'category' => 'pendukung']);

    // Cross-parent delete must fail and keep both rows/files
    $resp = $this->actingAs($this->admin)->delete(route('pengajuan.lampiran.destroy', [
        'id' => $a->id, 'lampiranId' => $lampB->id,
    ]));
    $resp->assertNotFound();
    expect($lampB->fresh())->not->toBeNull()
        ->and(Storage::disk('public')->exists($pathB))->toBeTrue()
        ->and($lampA->fresh())->not->toBeNull();

    // Own-parent delete succeeds, file removed
    $resp2 = $this->actingAs($this->admin)->delete(route('pengajuan.lampiran.destroy', [
        'id' => $a->id, 'lampiranId' => $lampA->id,
    ]));
    $resp2->assertRedirect();
    expect($lampA->fresh())->toBeNull()
        ->and(Storage::disk('public')->exists($pathA))->toBeFalse();
});

test('unprivileged user cannot delete attachment', function () {
    Storage::fake('public');
    $plain = makeUserWithoutRole(); // BAZNAS user without any pengurus role
    $p = makePengajuan($this->admin);
    $path = 'lampiran_pengajuan/test.txt';
    Storage::disk('public')->put($path, 'x');
    $lamp = Lampiran::create(['file_path' => $path, 'lampiranable_type' => $p::class, 'lampiranable_id' => $p->id, 'name' => 'L', 'category' => 'pendukung']);

    $resp = $this->actingAs($plain)->delete(route('pengajuan.lampiran.destroy', [
        'id' => $p->id, 'lampiranId' => $lamp->id,
    ]));
    $resp->assertForbidden();
    expect($lamp->fresh())->not->toBeNull()
        ->and(Storage::disk('public')->exists($path))->toBeTrue();
});

test('staff penyaluran can access penyaluran mutation route', function () {
    $staff = makeUserWithRole('Staff Penyaluran');
    $p = makePengajuan($this->admin);

    $resp = $this->actingAs($staff)->post(route('pengajuan.penyaluran.store', ['id' => $p->id]));
    // Authorized: passes role middleware (controller may reject on validation/business rules).
    $resp->assertStatus(302);
});

test('staff penyaluran cannot access disposisi mutation route', function () {
    $staff = makeUserWithRole('Staff Penyaluran');
    $p = makePengajuan($this->admin);
    $pm = $p->pengajuanMustahiks()->create([
        'mustahik_id' => Mustahik::first()->id,
        'nominal'     => 0,
    ]);

    $resp = $this->actingAs($staff)->post(route('pengajuan.mustahik.disposisi.store', [
        'pengajuanId' => $p->id, 'mustahikId' => $pm->id,
    ]));
    $resp->assertForbidden();
});

test('pimpinan can access disposisi mutation route', function () {
    $pimpinan = makeUserWithRole('Pimpinan BAZNAS');
    $p = makePengajuan($this->admin);
    $pm = $p->pengajuanMustahiks()->create([
        'mustahik_id' => Mustahik::first()->id,
        'nominal'     => 0,
    ]);

    $resp = $this->actingAs($pimpinan)->post(route('pengajuan.mustahik.disposisi.store', [
        'pengajuanId' => $p->id, 'mustahikId' => $pm->id,
    ]));
    // Authorized: passes role middleware; controller runs validation/business checks.
    $resp->assertStatus(302);
});

test('user without role gets 403 on sensitive mutation routes', function () {
    $plain = makeUserWithoutRole(); // BAZNAS user without any pengurus role
    $p = makePengajuan($this->admin);
    $pm = $p->pengajuanMustahiks()->create([
        'mustahik_id' => Mustahik::first()->id,
        'nominal'     => 0,
    ]);

    $this->actingAs($plain)->post(route('pengajuan.tolak', ['id' => $p->id]))->assertForbidden();
    $this->actingAs($plain)->post(route('pengajuan.mustahik.store', ['id' => $p->id]))->assertForbidden();
    $this->actingAs($plain)->put(route('pengajuan.mustahik.update', ['pengajuanId' => $p->id, 'mustahikId' => $pm->id]))->assertForbidden();
    $this->actingAs($plain)->delete(route('pengajuan.mustahik.destroy', ['pengajuanId' => $p->id, 'mustahikId' => $pm->id]))->assertForbidden();
    $this->actingAs($plain)->delete(route('pengajuan.mustahik.disposisi.destroy', ['pengajuanId' => $p->id, 'mustahikId' => $pm->id]))->assertForbidden();
    $this->actingAs($plain)->post(route('pengajuan.mustahik.survey.store', ['pengajuanId' => $p->id, 'mustahikId' => $pm->id]))->assertForbidden();
    $this->actingAs($plain)->post(route('pengajuan.persetujuan.store', ['id' => $p->id]))->assertForbidden();
    $this->actingAs($plain)->post(route('pengajuan.penyaluran.store', ['id' => $p->id]))->assertForbidden();
    $this->actingAs($plain)->post(route('pengajuan.mustahik.penyaluran.store', ['pengajuanId' => $p->id, 'mustahikId' => $pm->id]))->assertForbidden();
    $this->actingAs($plain)->delete(route('pengajuan.mustahik.penyaluran.destroy', ['pengajuanId' => $p->id, 'mustahikId' => $pm->id]))->assertForbidden();
    $this->actingAs($plain)->post(route('pengajuan.lpj.store', ['id' => $p->id]))->assertForbidden();
    $this->actingAs($plain)->post(route('pengajuan.lampiran.store', ['id' => $p->id]))->assertForbidden();
    $this->actingAs($plain)->delete(route('pengajuan.surat.destroy', ['id' => $p->id]))->assertForbidden();
});
