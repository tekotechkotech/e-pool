<?php

use App\Models\User;
use App\Models\Person;
use App\Models\Lembaga;
use App\Models\Program;
use App\Models\Subprogram;
use App\Models\Asnaf;
use App\Models\Mustahik;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanMustahik;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $adminPerson = Person::create(['nama' => 'Admin Test', 'nik' => '1111222233334444']);
    $this->user = User::create([
        'person_id' => $adminPerson->id,
        'email' => 'admintest@baznas.go.id',
        'password' => bcrypt('password'),
    ]);

    $jabatan = PengurusJabatan::firstOrCreate(
        ['nama' => 'Admin BAZNAS'],
        ['kode' => 'JB-TEST', 'tingkat' => 'baznas', 'is_active' => true]
    );

    Pengurus::create([
        'jenis_pengurus' => 'Baznas',
        'person_id' => $adminPerson->id,
        'pengurus_jabatan_id' => $jabatan->id,
        'is_active' => true,
    ]);

    $this->program = Program::create(['nama' => 'BAZNAS Peduli']);
    $this->subprogram = Subprogram::create([
        'program_id' => $this->program->id,
        'nama' => 'Bantuan Bencana',
        'jenis' => 'Umum',
    ]);
    $this->asnaf = Asnaf::create(['nama' => 'Gharimin', 'is_active' => true]);
});

test('edge case: perorangan without subprogram (subprogram_id = null)', function () {
    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-EDGE-001',
        'judul_surat' => 'Bantuan Perorangan No Subprogram',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Uraian pengajuan',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('pengajuan.mustahik.store', $pengajuan->id), [
        'nama' => 'Siti Nurhaliza',
        'nik' => '3301019999990001',
        'program_id' => $this->program->id,
        'subprogram_id' => null,
        'asnaf_id' => $this->asnaf->id,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'pengajuan_id' => $pengajuan->id,
        'subprogram_id' => null,
    ]);

    $showResponse = $this->actingAs($this->user)->get(route('pengajuan.umum.show', ['pengajuan' => $pengajuan->id, 'tab' => 'detail']));
    $showResponse->assertOk();
    $showResponse->assertSee('Siti Nurhaliza');
});

test('edge case: lembaga without subprogram and without asnaf', function () {
    $lembaga = Lembaga::create([
        'nama' => 'Musholla Nurul Iman',
        'is_upz' => false,
        'is_active' => true,
    ]);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-EDGE-002',
        'judul_surat' => 'Bantuan Lembaga No Subprogram No Asnaf',
        'jenis_permohonan' => 'Lembaga',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Renovasi musholla',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('pengajuan.mustahik.store', $pengajuan->id), [
        'lembaga_id' => $lembaga->id,
        'program_id' => $this->program->id,
        'subprogram_id' => null,
        'asnaf_id' => null,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'pengajuan_id' => $pengajuan->id,
        'subprogram_id' => null,
        'asnaf_id' => null,
    ]);

    $showResponse = $this->actingAs($this->user)->get(route('pengajuan.upz.show', ['pengajuan' => $pengajuan->id, 'tab' => 'detail']));
    $showResponse->assertOk();
    $showResponse->assertSee('Musholla Nurul Iman');
});

test('edge case: updating perorangan mustahik with subprogram_id = null', function () {
    $person = Person::create(['nama' => 'Joko Tarub', 'nik' => '3301018888880001']);
    $mustahik = Mustahik::create(['person_id' => $person->id, 'jenis_mustahik' => 'perorangan']);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-EDGE-003',
        'judul_surat' => 'Update Mustahik Null Subprogram',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Bantuan perorangan',
        'mustahik_id' => $mustahik->id,
        'created_by' => $this->user->id,
    ]);

    $pm = PengajuanMustahik::create([
        'pengajuan_id' => $pengajuan->id,
        'mustahik_id' => $mustahik->id,
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
        'asnaf_id' => $this->asnaf->id,
        'nominal' => 0,
    ]);

    $response = $this->actingAs($this->user)->put(route('pengajuan.mustahik.update', [
        'pengajuanId' => $pengajuan->id,
        'mustahikId' => $pm->id,
    ]), [
        'nama' => 'Joko Tarub Perkasa',
        'nik' => '3301018888880001',
        'program_id' => $this->program->id,
        'subprogram_id' => null,
        'asnaf_id' => $this->asnaf->id,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'id' => $pm->id,
        'subprogram_id' => null,
    ]);
    $this->assertDatabaseHas('persons', [
        'id' => $person->id,
        'nama' => 'Joko Tarub Perkasa',
    ]);
});

test('edge case: updating lembaga mustahik name', function () {
    $lembaga = Lembaga::create([
        'nama' => 'Panti Asuhan Baitul Yatim',
        'is_upz' => false,
        'is_active' => true,
    ]);
    $mustahik = Mustahik::create(['lembaga_id' => $lembaga->id, 'jenis_mustahik' => 'lembaga']);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-EDGE-004',
        'judul_surat' => 'Update Lembaga Name Test',
        'jenis_permohonan' => 'Lembaga',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Bantuan panti',
        'mustahik_id' => $mustahik->id,
        'created_by' => $this->user->id,
    ]);

    $pm = PengajuanMustahik::create([
        'pengajuan_id' => $pengajuan->id,
        'mustahik_id' => $mustahik->id,
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
        'asnaf_id' => $this->asnaf->id,
        'nominal' => 0,
    ]);

    $response = $this->actingAs($this->user)->put(route('pengajuan.mustahik.update', [
        'pengajuanId' => $pengajuan->id,
        'mustahikId' => $pm->id,
    ]), [
        'nama' => 'Panti Asuhan Baitul Yatim Pusat',
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('lembagas', [
        'id' => $lembaga->id,
        'nama' => 'Panti Asuhan Baitul Yatim Pusat',
    ]);
});

test('edge case: rendering /pengajuan/umum/create with authenticated user', function () {
    $response = $this->actingAs($this->user)->get(route('pengajuan.umum.create'));
    $response->assertOk();
    $response->assertSee('penerimaManfaat');
    $response->assertSee('Program BAZNAS &amp; Penerima Manfaat', false);
});

test('edge case: rendering /pengajuan/umum/{id} (show/detail) with authenticated user', function () {
    $person = Person::create(['nama' => 'Mustahik Show Test', 'nik' => '3301017777770001']);
    $mustahik = Mustahik::create(['person_id' => $person->id, 'jenis_mustahik' => 'perorangan']);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-EDGE-005',
        'judul_surat' => 'Show View Test',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Detail view render test',
        'mustahik_id' => $mustahik->id,
        'created_by' => $this->user->id,
    ]);

    PengajuanMustahik::create([
        'pengajuan_id' => $pengajuan->id,
        'mustahik_id' => $mustahik->id,
        'program_id' => $this->program->id,
        'subprogram_id' => null,
        'asnaf_id' => null,
        'nominal' => 0,
    ]);

    $response = $this->actingAs($this->user)->get(route('pengajuan.umum.show', [
        'pengajuan' => $pengajuan->id,
        'tab' => 'detail',
    ]));

    $response->assertOk();
    $response->assertSee('Mustahik Show Test');
    $response->assertSee('Daftar Penerima Manfaat &amp; Program BAZNAS', false);
    $response->assertSee('Tambah Penerima Manfaat');
});
