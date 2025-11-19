<?php

use App\Models\User;
use App\Models\Person;
use App\Models\Program;
use App\Models\Subprogram;
use App\Models\Asnaf;
use App\Models\Dana;
use App\Models\Mustahik;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanMustahik;
use App\Models\PengajuanDisposisi;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Lembaga;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $adminPerson = Person::create(['nama' => 'Admin BAZNAS', 'nik' => '9999999999999999']);
    $this->user = User::create([
        'person_id' => $adminPerson->id,
        'email' => 'admin@baznas.go.id',
        'password' => bcrypt('password'),
    ]);

    $jabatan = PengurusJabatan::where('nama', 'Admin BAZNAS')->first();
    if (!$jabatan) {
        $jabatan = PengurusJabatan::create([
            'kode' => 'JB-002',
            'nama' => 'Admin BAZNAS',
            'tingkat' => 'baznas',
            'is_active' => true,
        ]);
    }

    Pengurus::create([
        'jenis_pengurus' => 'Baznas',
        'person_id' => $adminPerson->id,
        'pengurus_jabatan_id' => $jabatan->id,
        'is_active' => true,
    ]);

    $this->program = Program::create(['nama' => 'BAZNAS Sehat']);
    $this->subprogram = Subprogram::create(['program_id' => $this->program->id, 'nama' => 'Bantuan Berobat', 'jenis' => 'Umum']);
    $this->program2 = Program::create(['nama' => 'BAZNAS Cerdas']);
    $this->subprogram2 = Subprogram::create(['program_id' => $this->program2->id, 'nama' => 'Beasiswa', 'jenis' => 'Umum']);
    $this->asnaf = Asnaf::create(['nama' => 'Fakir', 'is_active' => true]);
    $this->asnaf2 = Asnaf::create(['nama' => 'Miskin', 'is_active' => true]);
    $this->dana = Dana::create(['kode' => '1.1.01', 'nama' => 'Kas BAZNAS', 'kategori' => 'Kas', 'is_active' => true]);

    $this->person = Person::create([
        'nama' => 'Ahmad',
        'nik' => '3301010101010001',
    ]);
    $this->mustahik = Mustahik::create(['person_id' => $this->person->id]);

    $this->pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-2026-0001',
        'judul_surat' => 'Permohonan Bantuan Kesehatan',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Permohonan bantuan berobat mustahik',
        'mustahik_id' => $this->mustahik->id,
        'created_by' => $this->user->id,
    ]);

    $this->pengajuanMustahik = PengajuanMustahik::create([
        'pengajuan_id' => $this->pengajuan->id,
        'mustahik_id' => $this->mustahik->id,
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
        'asnaf_id' => $this->asnaf->id,
        'nominal' => 0,
    ]);
});

test('authenticated user can update mustahik data', function () {
    $response = $this->actingAs($this->user)->put(route('pengajuan.mustahik.update', [
        'pengajuanId' => $this->pengajuan->id,
        'mustahikId' => $this->pengajuanMustahik->id,
    ]), [
        'nama' => 'Ahmad Fauzi',
        'nik' => '3301010101010002',
        'program_id' => $this->program2->id,
        'subprogram_id' => $this->subprogram2->id,
        'asnaf_id' => $this->asnaf2->id,
        'keterangan' => 'Keterangan diubah',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('pengajuan.umum.show', ['pengajuan' => $this->pengajuan->id, 'tab' => 'detail']));

    $this->assertDatabaseHas('persons', [
        'id' => $this->person->id,
        'nama' => 'Ahmad Fauzi',
        'nik' => '3301010101010002',
    ]);

    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'id' => $this->pengajuanMustahik->id,
        'program_id' => $this->program2->id,
        'subprogram_id' => $this->subprogram2->id,
        'asnaf_id' => $this->asnaf2->id,
        'keterangan' => 'Keterangan diubah',
    ]);
});

test('cannot update or delete mustahik if disposisi already exists', function () {
    // Berikan disposisi
    $this->pengajuanMustahik->update([
        'nominal' => 500000,
        'sumber_dana_id' => $this->dana->id,
    ]);
    PengajuanDisposisi::create([
        'pengajuan_mustahik_id' => $this->pengajuanMustahik->id,
        'pimpinan_id' => $this->user->id,
        'instruksi' => 'Proses Langsung',
    ]);

    // Percobaan update harus gagal
    $updateResponse = $this->actingAs($this->user)->put(route('pengajuan.mustahik.update', [
        'pengajuanId' => $this->pengajuan->id,
        'mustahikId' => $this->pengajuanMustahik->id,
    ]), [
        'nama' => 'Nama Lain',
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
    ]);

    $updateResponse->assertSessionHasErrors('error');

    // Percobaan delete harus gagal
    $deleteResponse = $this->actingAs($this->user)->delete(route('pengajuan.mustahik.destroy', [
        'pengajuanId' => $this->pengajuan->id,
        'mustahikId' => $this->pengajuanMustahik->id,
    ]));

    $deleteResponse->assertSessionHasErrors('error');
    $this->assertDatabaseHas('pengajuan_mustahiks', ['id' => $this->pengajuanMustahik->id]);

    // Percobaan tambah mustahik baru saat sudah ada disposisi harus gagal
    $storeResponse = $this->actingAs($this->user)->post(route('pengajuan.mustahik.store', [
        'id' => $this->pengajuan->id,
    ]), [
        'nama' => 'Mustahik Baru',
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
    ]);

    $storeResponse->assertSessionHasErrors('error');
});

test('view renders edit button and hides survey, disposisi, and hides all when disposisi exists', function () {
    // 1. Sebelum disposisi:
    // "Tambah Mustahik" dan tombol "Edit" & "Hapus" harus muncul.
    // Tombol "Survey" pintasan baris dan tombol "Disposisi" baris mustahik tidak boleh ada.
    $responseBefore = $this->actingAs($this->user)->get(route('pengajuan.umum.show', [
        'pengajuan' => $this->pengajuan->id,
        'tab' => 'detail',
    ]));

    $responseBefore->assertOk();
    $responseBefore->assertSee('Tambah Penerima Manfaat');
    $responseBefore->assertSee('openEditMustahik(' . $this->pengajuanMustahik->id . ')', false);
    $responseBefore->assertDontSee('openSurvey(' . $this->pengajuanMustahik->id . ')', false);
    $responseBefore->assertSee('Edit Data Penerima Manfaat'); // Modal edit mustahik harus ada

    // Pastikan di tabel mustahik tab detail tidak ada tombol pintasan Survey
    $responseBefore->assertDontSee('text-emerald-700 hover:text-emerald-900 font-medium">
                                        Survey', false);

    // 2. Setelah disposisi diberikan:
    $this->pengajuanMustahik->update([
        'nominal' => 1000000,
        'sumber_dana_id' => $this->dana->id,
    ]);
    PengajuanDisposisi::create([
        'pengajuan_mustahik_id' => $this->pengajuanMustahik->id,
        'pimpinan_id' => $this->user->id,
        'instruksi' => 'Proses Langsung',
    ]);

    $responseAfter = $this->actingAs($this->user)->get(route('pengajuan.umum.show', [
        'pengajuan' => $this->pengajuan->id,
        'tab' => 'detail',
    ]));

    $responseAfter->assertOk();
    // Tambah Mustahik, Edit, dan Hapus harus HIDDEN
    $responseAfter->assertDontSee('Tambah Penerima Manfaat');
    $responseAfter->assertDontSee('openEditMustahik(' . $this->pengajuanMustahik->id . ')', false);
    $responseAfter->assertSee('Terkunci');
});

test('storeMustahik supports nullable subprogram_id', function () {
    $pengajuanBaru = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-2026-0002',
        'judul_surat' => 'Permohonan Bantuan',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Permohonan bantuan',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('pengajuan.mustahik.store', $pengajuanBaru->id), [
        'nama' => 'Mustahik Tanpa Subprogram',
        'program_id' => $this->program->id,
        'subprogram_id' => null,
        'asnaf_id' => $this->asnaf->id,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('pengajuan.umum.show', ['pengajuan' => $pengajuanBaru->id, 'tab' => 'detail']));
    $response->assertSessionHas('success', 'Data penerima manfaat (mustahik) berhasil ditambahkan.');

    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'pengajuan_id' => $pengajuanBaru->id,
        'program_id' => $this->program->id,
        'subprogram_id' => null,
        'asnaf_id' => $this->asnaf->id,
    ]);
});

test('storeMustahik supports lembaga with null subprogram_id and null asnaf_id', function () {
    $lembaga = Lembaga::create([
        'nama' => 'Panti Asuhan Al-Hidayah',
        'is_upz' => false,
        'is_active' => true,
    ]);

    $pengajuanBaru = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-2026-0099',
        'judul_surat' => 'Permohonan Bantuan Operasional Lembaga',
        'jenis_permohonan' => 'Lembaga',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Bantuan lembaga',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('pengajuan.mustahik.store', $pengajuanBaru->id), [
        'lembaga_id' => $lembaga->id,
        'program_id' => $this->program->id,
        'subprogram_id' => null,
        'asnaf_id' => null,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('pengajuan.upz.show', ['pengajuan' => $pengajuanBaru->id, 'tab' => 'detail']));
    $response->assertSessionHas('success', 'Data penerima manfaat (mustahik) berhasil ditambahkan.');

    $mustahik = Mustahik::where('lembaga_id', $lembaga->id)->first();
    expect($mustahik)->not->toBeNull();
    expect($mustahik->jenis_mustahik)->toBe('lembaga');

    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'pengajuan_id' => $pengajuanBaru->id,
        'mustahik_id' => $mustahik->id,
        'program_id' => $this->program->id,
        'subprogram_id' => null,
        'asnaf_id' => null,
    ]);
});

test('storeMustahik supports lembaga_id without nama', function () {
    $lembaga = Lembaga::create([
        'nama' => 'Yayasan Berkah Bersama',
        'is_upz' => false,
        'is_active' => true,
    ]);

    $pengajuanBaru = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-2026-0003',
        'judul_surat' => 'Permohonan Bantuan Lembaga',
        'jenis_permohonan' => 'Lembaga',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Permohonan bantuan lembaga',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('pengajuan.mustahik.store', $pengajuanBaru->id), [
        'lembaga_id' => $lembaga->id,
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
    ]);

    $response->assertSessionHasNoErrors();

    $mustahik = Mustahik::where('lembaga_id', $lembaga->id)->first();
    expect($mustahik)->not->toBeNull();
    expect($mustahik->jenis_mustahik)->toBe('lembaga');

    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'pengajuan_id' => $pengajuanBaru->id,
        'mustahik_id' => $mustahik->id,
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
    ]);
});

test('storeMustahik supports person_id without nama', function () {
    $person = Person::create([
        'nama' => 'Budi Santoso',
        'nik' => '3301010101019999',
    ]);

    $pengajuanBaru = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-2026-0004',
        'judul_surat' => 'Permohonan Bantuan Person ID',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Permohonan bantuan',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('pengajuan.mustahik.store', $pengajuanBaru->id), [
        'person_id' => $person->id,
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
    ]);

    $response->assertSessionHasNoErrors();

    $mustahik = Mustahik::where('person_id', $person->id)->first();
    expect($mustahik)->not->toBeNull();
    expect($mustahik->jenis_mustahik)->toBe('perorangan');

    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'pengajuan_id' => $pengajuanBaru->id,
        'mustahik_id' => $mustahik->id,
    ]);
});

test('storeMustahik requires nama when neither person_id nor lembaga_id is provided', function () {
    $pengajuanBaru = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-2026-0005',
        'judul_surat' => 'Permohonan Bantuan Validasi',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Permohonan bantuan',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('pengajuan.mustahik.store', $pengajuanBaru->id), [
        'program_id' => $this->program->id,
    ]);

    $response->assertSessionHasErrors('nama');
});

test('storeMustahik creates or finds mustahik by lembaga_id', function () {
    $lembaga = Lembaga::create([
        'nama' => 'Yayasan Amal Mulia',
        'jenis' => 'Yayasan',
        'no_registrasi' => 'REG-12345',
        'created_by' => $this->user->id,
    ]);

    $pengajuanBaru = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-2026-0006',
        'judul_surat' => 'Permohonan Bantuan Lembaga',
        'jenis_permohonan' => 'Lembaga',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Permohonan bantuan lembaga',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('pengajuan.mustahik.store', $pengajuanBaru->id), [
        'lembaga_id' => $lembaga->id,
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
    ]);

    $response->assertSessionHasNoErrors();

    $mustahik = Mustahik::where('lembaga_id', $lembaga->id)->first();
    expect($mustahik)->not->toBeNull();
    expect($mustahik->jenis_mustahik)->toBe('lembaga');

    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'pengajuan_id' => $pengajuanBaru->id,
        'mustahik_id' => $mustahik->id,
    ]);
});

test('updateMustahik accepts null subprogram_id and updates lembaga mustahik', function () {
    $lembaga = Lembaga::create([
        'nama' => 'Panti Asuhan Sejahtera',
        'jenis' => 'Panti Asuhan',
        'no_registrasi' => 'PA-001',
        'created_by' => $this->user->id,
    ]);

    $mustahikLembaga = Mustahik::create([
        'jenis_mustahik' => 'lembaga',
        'lembaga_id' => $lembaga->id,
    ]);

    $pengajuanLembaga = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-2026-0007',
        'judul_surat' => 'Permohonan Bantuan Lembaga Update',
        'jenis_permohonan' => 'Lembaga',
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Permohonan bantuan lembaga',
        'mustahik_id' => $mustahikLembaga->id,
        'created_by' => $this->user->id,
    ]);

    $pm = PengajuanMustahik::create([
        'pengajuan_id' => $pengajuanLembaga->id,
        'mustahik_id' => $mustahikLembaga->id,
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
        'asnaf_id' => $this->asnaf->id,
        'nominal' => 0,
    ]);

    $response = $this->actingAs($this->user)->put(route('pengajuan.mustahik.update', [
        'pengajuanId' => $pengajuanLembaga->id,
        'mustahikId' => $pm->id,
    ]), [
        'nama' => 'Panti Asuhan Sejahtera Baharu',
        'program_id' => $this->program->id,
        'subprogram_id' => null,
        'asnaf_id' => $this->asnaf->id,
        'keterangan' => 'Lembaga diupdate',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('pengajuan.upz.show', ['pengajuan' => $pengajuanLembaga->id, 'tab' => 'detail']));

    $this->assertDatabaseHas('lembagas', [
        'id' => $lembaga->id,
        'nama' => 'Panti Asuhan Sejahtera Baharu',
    ]);

    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'id' => $pm->id,
        'subprogram_id' => null,
        'keterangan' => 'Lembaga diupdate',
    ]);
});

test('survey show route redirects to tab survey with mustahik id', function () {
    $response = $this->actingAs($this->user)->get(route('pengajuan.mustahik.survey.show', [
        'pengajuanId' => $this->pengajuan->id,
        'mustahikId' => $this->pengajuanMustahik->id,
    ]));

    $response->assertRedirect(route('pengajuan.umum.show', [
        'pengajuan' => $this->pengajuan->id,
        'tab' => 'survey',
        'mustahik' => $this->pengajuanMustahik->id,
    ]));
});

test('survey show route redirects to upz show when pengajuan is upz/lembaga', function () {
    $lembaga = Lembaga::create([
        'nama' => 'UPZ Masjid Al-Hidayah',
        'is_upz' => true,
    ]);
    $mustahikLembaga = Mustahik::create([
        'lembaga_id' => $lembaga->id,
        'asnaf_id' => $this->asnaf->id,
    ]);
    $pengajuanUpz = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-UPZ-0001',
        'judul_surat' => 'Permohonan UPZ',
        'jenis_permohonan' => 'UPZ/Lembaga',
        'is_upz' => true,
        'tanggal_diterima' => now()->toDateString(),
        'uraian_permohonan' => 'Bantuan UPZ',
        'mustahik_id' => $mustahikLembaga->id,
        'created_by' => $this->user->id,
    ]);
    $pmUpz = PengajuanMustahik::create([
        'pengajuan_id' => $pengajuanUpz->id,
        'mustahik_id' => $mustahikLembaga->id,
        'program_id' => $this->program->id,
        'nominal' => 0,
    ]);

    $response = $this->actingAs($this->user)->get(route('pengajuan.mustahik.survey.show', [
        'pengajuanId' => $pengajuanUpz->id,
        'mustahikId' => $pmUpz->id,
    ]));

    $response->assertRedirect(route('pengajuan.upz.show', [
        'pengajuan' => $pengajuanUpz->id,
        'tab' => 'survey',
        'mustahik' => $pmUpz->id,
    ]));
});

test('survey show route returns json when requested', function () {
    $response = $this->actingAs($this->user)->getJson(route('pengajuan.mustahik.survey.show', [
        'pengajuanId' => $this->pengajuan->id,
        'mustahikId' => $this->pengajuanMustahik->id,
    ]));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pengajuan.id', $this->pengajuan->id)
        ->assertJsonPath('pengajuan.nomor_pengajuan', $this->pengajuan->nomor_pengajuan)
        ->assertJsonPath('pengajuan.judul', $this->pengajuan->judul_surat)
        ->assertJsonPath('pengajuan.status', $this->pengajuan->status_tahapan)
        ->assertJsonPath('mustahik.id', $this->pengajuanMustahik->id);
});

test('survey store route saves survey conclusions and redirects to tab survey', function () {
    $response = $this->actingAs($this->user)->post(route('pengajuan.mustahik.survey.store', [
        'pengajuanId' => $this->pengajuan->id,
        'mustahikId' => $this->pengajuanMustahik->id,
    ]), [
        'petugas_survey' => 'Ahmad Surveyor',
        'nohp_surveyor' => '081234567890',
        'tanggal_survey' => '2026-10-25',
        'status_kelayakan' => 'layak',
        'catatan' => 'Kondisi riil sangat membutuhkan bantuan.',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('pengajuan.umum.show', [
        'pengajuan' => $this->pengajuan->id,
        'tab' => 'survey',
        'mustahik' => $this->pengajuanMustahik->id,
    ]));

    $this->assertDatabaseHas('pengajuan_surveys', [
        'pengajuan_mustahik_id' => $this->pengajuanMustahik->id,
        'surveyor' => 'Ahmad Surveyor',
        'nohp_surveyor' => '081234567890',
        'hasil_akhir' => 'layak',
        'catatan' => 'Kondisi riil sangat membutuhkan bantuan.',
    ]);
});

test('pengajuan mustahik creation automatically generates survey with token', function () {
    $person = Person::create(['nama' => 'Mustahik Baru', 'nik' => '3301010101019999']);
    $mustahik = Mustahik::create(['person_id' => $person->id]);

    $pm = PengajuanMustahik::create([
        'pengajuan_id' => $this->pengajuan->id,
        'mustahik_id' => $mustahik->id,
        'program_id' => $this->program->id,
        'asnaf_id' => $this->asnaf->id,
        'nominal' => 0,
    ]);

    expect($pm->survey)->not->toBeNull();
    expect($pm->survey->token)->not->toBeEmpty();
    expect(strlen($pm->survey->token))->toBe(32);
});

test('survey show route auto-creates survey token if missing', function () {
    $this->pengajuanMustahik->survey()->delete();
    expect($this->pengajuanMustahik->fresh()->survey)->toBeNull();

    $response = $this->actingAs($this->user)->getJson(route('pengajuan.mustahik.survey.show', [
        'pengajuanId' => $this->pengajuan->id,
        'mustahikId' => $this->pengajuanMustahik->id,
    ]));

    $response->assertOk();
    $token = $response->json('mustahik.survey.token');
    expect($token)->not->toBeNull();
    expect(strlen($token))->toBe(32);
});

test('pengajuan show view on survey tab lazily generates survey token for legacy data', function () {
    $this->pengajuanMustahik->survey()->delete();
    expect($this->pengajuanMustahik->fresh()->survey)->toBeNull();

    $response = $this->actingAs($this->user)->get(route('pengajuan.umum.show', [
        'pengajuan' => $this->pengajuan->id,
        'tab' => 'survey',
    ]));

    $response->assertOk();
    $pmSurvey = $this->pengajuanMustahik->fresh()->survey;
    expect($pmSurvey)->not->toBeNull();
    expect($pmSurvey->token)->not->toBeEmpty();
    expect(strlen($pmSurvey->token))->toBe(32);
});
