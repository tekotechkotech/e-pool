<?php

namespace Tests\Feature;

use App\Models\Lembaga;
use App\Models\PengajuanBantuan;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function accessUser(string $role, string $kind = 'Baznas', ?Lembaga $upz = null): User
{
    $person = Person::create(['nama' => 'User ' . $role . uniqid(), 'nik' => substr(md5(uniqid()), 0, 16)]);
    $user = User::create(['person_id' => $person->id, 'email' => uniqid() . '@test.local', 'password' => bcrypt('secret')]);
    $jabatan = PengurusJabatan::create(['kode' => uniqid(), 'nama' => $role, 'tingkat' => $kind === 'Baznas' ? 'baznas' : 'lembaga', 'is_active' => true]);
    Pengurus::create(['jenis_pengurus' => $kind, 'person_id' => $person->id, 'lembaga_id' => $upz?->id, 'pengurus_jabatan_id' => $jabatan->id, 'is_active' => true]);
    return $user;
}

function scopedApplication(Lembaga $upz, User $creator, string $type = 'Lembaga'): PengajuanBantuan
{
    return PengajuanBantuan::create(['nomor_pengajuan' => uniqid('A-'), 'judul_surat' => 'Scope test', 'jenis_permohonan' => $type, 'lembaga_id' => $upz->id, 'tanggal_diterima' => now()->toDateString(), 'uraian_permohonan' => 'Test', 'created_by' => $creator->id]);
}

function accessUpz(string $name): Lembaga
{
    return Lembaga::create(['nama' => $name, 'is_upz' => true, 'is_active' => true]);
}

test('UPZ list and details stay inside assigned UPZ while BAZNAS sees all', function () {
    $upz1 = accessUpz('UPZ One');
    $upz2 = accessUpz('UPZ Two');
    $secretary = accessUser('Sekretaris', 'Lembaga', $upz1);
    $baznas = accessUser('Admin BAZNAS');
    $record1 = scopedApplication($upz1, $baznas);
    $record2 = scopedApplication($upz2, $baznas);

    $this->actingAs($secretary)->get(route('pengajuan.upz.index'))->assertOk()->assertSee($record1->nomor_pengajuan)->assertDontSee($record2->nomor_pengajuan);
    $this->actingAs($secretary)->get(route('pengajuan.upz.show', ['pengajuan' => $record2->id]))->assertNotFound();
    $this->actingAs($secretary)->get(route('pengajuan.upz.show', ['pengajuan' => $record1->id]))->assertOk();
    $this->actingAs($baznas)->get(route('pengajuan.upz.show', ['pengajuan' => $record2->id]))->assertOk();
});

test('people search restricted to BAZNAS appointments', function () {
    $upz = accessUpz('UPZ Search');
    $secretary = accessUser('Sekretaris', 'Lembaga', $upz);
    $admin = accessUser('Admin BAZNAS');

    $this->actingAs($secretary)->getJson(route('api.search.people', ['q' => 'ab']))->assertForbidden();
    $this->actingAs($admin)->getJson(route('api.search.people', ['q' => 'ab']))->assertOk();
});

test('edit and approval boundaries follow assigned role', function () {
    $upz = accessUpz('UPZ Boundary');
    $admin = accessUser('Admin BAZNAS');
    $record = scopedApplication($upz, $admin);
    $pimpinan = accessUser('Pimpinan BAZNAS');
    $staff = accessUser('Staff Penyaluran');
    $chair = accessUser('Ketua', 'Lembaga', $upz);

    $this->actingAs($pimpinan)->get(route('pengajuan.edit', $record))->assertForbidden();
    $this->actingAs($pimpinan)->post(route('pengajuan.persetujuan.store', ['id' => $record->id]))->assertStatus(302);
    $this->actingAs($staff)->post(route('pengajuan.tolak', ['id' => $record->id]))->assertForbidden();
    $this->actingAs($chair)->post(route('pengajuan.persetujuan.store', ['id' => $record->id]))->assertStatus(302);
    $this->actingAs($admin)->get(route('pengajuan.edit', $record))->assertOk();
});
