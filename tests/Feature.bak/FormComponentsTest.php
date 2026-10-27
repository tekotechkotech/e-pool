<?php

use App\Models\User;
use App\Models\Person;
use App\Models\Program;
use App\Models\Subprogram;
use App\Models\Asnaf;
use App\Models\Dana;
use App\Models\Mustahik;
use App\Models\PengajuanBantuan;
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

    $jabatan = PengurusJabatan::firstOrCreate(
        ['kode' => 'JB-002'],
        ['nama' => 'Admin BAZNAS', 'tingkat' => 'baznas', 'is_active' => true]
    );

    Pengurus::create([
        'jenis_pengurus' => 'Baznas',
        'person_id' => $adminPerson->id,
        'pengurus_jabatan_id' => $jabatan->id,
        'is_active' => true,
    ]);

    $this->program = Program::create(['nama' => 'BAZNAS Sehat']);
    $this->subprogram = Subprogram::create(['program_id' => $this->program->id, 'nama' => 'Bantuan Berobat', 'jenis' => 'Umum']);
    $this->asnaf = Asnaf::create(['nama' => 'Fakir', 'is_active' => true]);
    $this->dana = Dana::create(['kode' => '1.1.01', 'nama' => 'Kas BAZNAS', 'kategori' => 'Kas', 'is_active' => true]);
});

test('render edit pemohon view for individu with person-fields and wilayah-select', function () {
    $person = Person::create([
        'nama' => 'Budi Santoso',
        'nik' => '3301010101010002',
        'nomor_kk' => '3301010101010003',
        'telepon' => '081234567890',
        'alamat' => 'Jl. Kenanga No 12',
        'kode_pos' => '53211',
    ]);
    $mustahik = Mustahik::create(['person_id' => $person->id, 'pekerjaan' => 'Buruh']);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-IND-001',
        'subprogram_id' => $this->subprogram->id,
        'dana_id' => $this->dana->id,
        'mustahik_id' => $mustahik->id,
        'jenis_permohonan' => 'Individu',
        'judul_surat' => 'Permohonan Bantuan',
        'uraian_permohonan' => 'Uraian pengajuan',
        'nominal_permohonan' => 500000,
        'tanggal_diterima' => now()->toDateString(),
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->user)->get(route('pengajuan.edit-pemohon', $pengajuan->id));

    $response->assertOk();
    $response->assertSee('name="p_nik"', false);
    $response->assertSee('value="3301010101010002"', false);
    $response->assertSee('name="p_nama"', false);
    $response->assertSee('value="Budi Santoso"', false);
    $response->assertSee('name="p_id_provinsi"', false);
    $response->assertSee('name="p_id_kabupaten"', false);
    $response->assertSee('name="p_id_kecamatan"', false);
    $response->assertSee('name="p_id_kelurahan"', false);
    $response->assertSee('name="p_kode_pos"', false);
    $response->assertSee('name="p_alamat"', false);
    $response->assertSee('Alamat &amp; Wilayah Pemohon', false);
});

test('render edit pemohon view for upz with lembaga-fields and wilayah-select', function () {
    $lembaga = Lembaga::create([
        'nama' => 'UPZ Masjid Al-Ikhlas',
        'telepon' => '089876543210',
        'alamat' => 'Jl. Diponegoro No 5',
        'kode_pos' => '53212',
        'is_upz' => true,
    ]);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-UPZ-001',
        'subprogram_id' => $this->subprogram->id,
        'dana_id' => $this->dana->id,
        'lembaga_id' => $lembaga->id,
        'jenis_permohonan' => 'UPZ/Lembaga',
        'judul_surat' => 'Permohonan Operasional',
        'uraian_permohonan' => 'Uraian pengajuan upz',
        'nominal_permohonan' => 1000000,
        'tanggal_diterima' => now()->toDateString(),
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->user)->get(route('pengajuan.edit-pemohon', $pengajuan->id));

    $response->assertOk();
    $response->assertSee('name="u_nama"', false);
    $response->assertSee('value="UPZ Masjid Al-Ikhlas"', false);
    $response->assertSee('name="u_telepon"', false);
    $response->assertSee('value="089876543210"', false);
    $response->assertSee('name="u_id_provinsi"', false);
    $response->assertSee('name="u_id_kabupaten"', false);
    $response->assertSee('name="u_id_kecamatan"', false);
    $response->assertSee('name="u_id_kelurahan"', false);
    $response->assertSee('name="u_kode_pos"', false);
    $response->assertSee('name="u_alamat"', false);
    $response->assertSee('Alamat &amp; Wilayah UPZ / Lembaga', false);
});

test('update pemohon for individu works through updated view contract', function () {
    $person = Person::create([
        'nama' => 'Lama',
        'nik' => '3301010101010099',
    ]);
    $mustahik = Mustahik::create(['person_id' => $person->id]);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-IND-002',
        'subprogram_id' => $this->subprogram->id,
        'dana_id' => $this->dana->id,
        'mustahik_id' => $mustahik->id,
        'jenis_permohonan' => 'Individu',
        'judul_surat' => 'Permohonan Bantuan',
        'uraian_permohonan' => 'Uraian pengajuan 2',
        'tanggal_diterima' => now()->toDateString(),
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->user)->put(route('pengajuan.update-pemohon', $pengajuan->id), [
        'p_nama' => 'Baru',
        'p_nik' => '3301010101010098',
        'p_nomor_kk' => '3301010101010077',
        'p_pekerjaan' => 'Wiraswasta',
        'p_telepon' => '081234123412',
        'p_alamat' => 'Alamat Baru',
    ]);

    $response->assertRedirect(route('pengajuan.umum.show', ['pengajuan' => $pengajuan->id]));
    $this->assertDatabaseHas('persons', [
        'id' => $person->id,
        'nama' => 'Baru',
        'nik' => '3301010101010098',
        'nomor_kk' => '3301010101010077',
    ]);
});

test('render edit pemohon view prioritizes old input over database and snapshot', function () {
    $person = Person::create([
        'nama' => 'Nama DB',
        'nik' => '3301010101010001',
    ]);
    $mustahik = Mustahik::create(['person_id' => $person->id]);
    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-IND-OLD',
        'subprogram_id' => $this->subprogram->id,
        'dana_id' => $this->dana->id,
        'mustahik_id' => $mustahik->id,
        'jenis_permohonan' => 'Individu',
        'judul_surat' => 'Permohonan Bantuan',
        'uraian_permohonan' => 'Uraian pengajuan',
        'tanggal_diterima' => now()->toDateString(),
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->user)
        ->withSession([
            '_old_input' => [
                'p_nama' => 'Nama Flash Old',
                'p_nik' => '3301010101019999',
                'p_alamat' => 'Alamat Flash Old',
            ],
        ])
        ->get(route('pengajuan.edit-pemohon', $pengajuan->id));

    $response->assertOk();
    $response->assertSee('value="Nama Flash Old"', false);
    $response->assertSee('value="3301010101019999"', false);
    $response->assertSee('Alamat Flash Old', false);
    $response->assertDontSee('value="Nama DB"', false);
});

test('render edit pemohon falls back to snapshots when relation model is missing', function () {
    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-SNAP-001',
        'subprogram_id' => $this->subprogram->id,
        'dana_id' => $this->dana->id,
        'jenis_permohonan' => 'Umum',
        'judul_surat' => 'Permohonan Bantuan Umum',
        'uraian_permohonan' => 'Uraian pengajuan umum',
        'tanggal_diterima' => now()->toDateString(),
        'status' => 'draft',
        'mustahik_nama_snapshot' => 'Nama Snapshot Saja',
        'mustahik_nik_snapshot' => '3301010101017777',
    ]);

    $response = $this->actingAs($this->user)->get(route('pengajuan.edit-pemohon', $pengajuan->id));

    $response->assertOk();
    $response->assertSee('value="Nama Snapshot Saja"', false);
    $response->assertSee('value="3301010101017777"', false);
});

test('update pemohon for upz / lembaga updates database and snapshots', function () {
    $lembaga = Lembaga::create([
        'nama' => 'UPZ Lama',
        'telepon' => '08111111111',
        'alamat' => 'Alamat Lembaga Lama',
        'is_upz' => true,
    ]);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-UPZ-UPD',
        'subprogram_id' => $this->subprogram->id,
        'dana_id' => $this->dana->id,
        'lembaga_id' => $lembaga->id,
        'jenis_permohonan' => 'UPZ/Lembaga',
        'judul_surat' => 'Permohonan UPZ',
        'uraian_permohonan' => 'Uraian UPZ',
        'tanggal_diterima' => now()->toDateString(),
        'status' => 'draft',
    ]);

    $response = $this->actingAs($this->user)->put(route('pengajuan.update-pemohon', $pengajuan->id), [
        'u_nama' => 'UPZ Sukses Terupdate',
        'u_telepon' => '08222222222',
        'u_alamat' => 'Alamat Lembaga Baru',
        'u_kode_pos' => '53215',
    ]);

    $response->assertRedirect(route('pengajuan.upz.show', ['pengajuan' => $pengajuan->id]));

    $this->assertDatabaseHas('lembagas', [
        'id' => $lembaga->id,
        'nama' => 'UPZ Sukses Terupdate',
        'telepon' => '08222222222',
        'alamat' => 'Alamat Lembaga Baru',
        'kode_pos' => '53215',
    ]);

    $this->assertDatabaseHas('pengajuan_bantuans', [
        'id' => $pengajuan->id,
        'lembaga_nama_snapshot' => 'UPZ Sukses Terupdate',
        'lembaga_kontak_snapshot' => '08222222222',
        'lembaga_alamat_snapshot' => 'Alamat Lembaga Baru, 53215',
    ]);
});

test('components render with alpineModel and custom slots', function () {
    $personHtml = Illuminate\Support\Facades\Blade::render(
        '<x-form.person-fields alpine-model="true" :show-ttl="true"><div id="custom-person-slot">Extra</div></x-form.person-fields>'
    );
    expect($personHtml)->toContain('x-model="nik"');
    expect($personHtml)->toContain('x-model="nama"');
    expect($personHtml)->toContain('x-model="tempat_lahir"');
    expect($personHtml)->toContain('id="custom-person-slot"');

    $lembagaHtml = Illuminate\Support\Facades\Blade::render(
        '<x-form.lembaga-fields alpine-model="true" :show-jenis="true" :jenis-options="[\'A\' => \'Type A\']"><div id="custom-lembaga-slot">Extra</div></x-form.lembaga-fields>'
    );
    expect($lembagaHtml)->toContain('x-model="nama"');
    expect($lembagaHtml)->toContain('x-model="jenis"');
    expect($lembagaHtml)->toContain('Type A');
    expect($lembagaHtml)->toContain('id="custom-lembaga-slot"');

    $wilayahHtml = Illuminate\Support\Facades\Blade::render(
        '<x-form.wilayah-select :standalone="false" alpine-scope="wilayah" />'
    );
    expect($wilayahHtml)->not->toContain('x-data="wilayahSelector');
    expect($wilayahHtml)->toContain('x-model="wilayah.selectedProvinsi"');
    expect($wilayahHtml)->toContain('x-model="wilayah.selectedKabupaten"');
    expect($wilayahHtml)->toContain('x-model="wilayah.selectedKecamatan"');
    expect($wilayahHtml)->toContain('x-model="wilayah.selectedKelurahan"');
    expect($wilayahHtml)->toContain('x-model="wilayah.kodePos"');
    expect($wilayahHtml)->toContain('x-model="wilayah.alamat"');
});

test('management views render successfully with refactored components', function () {
    $this->actingAs($this->user)->get(route('profile'))->assertOk()->assertSee('Alamat & Wilayah', false);
    $this->actingAs($this->user)->get(route('manajemen.baznas'))->assertOk()->assertSee('Lokasi &amp; Alamat Kantor BAZNAS', false);
    $this->actingAs($this->user)->get(route('manajemen.lembaga'))->assertOk()->assertSee('Informasi Alamat &amp; Lokasi Wilayah', false);
    $this->actingAs($this->user)->get(route('manajemen.upz'))->assertOk()->assertSee('Informasi Alamat &amp; Lokasi Wilayah', false);
    $this->actingAs($this->user)->get(route('manajemen.mustahik'))->assertOk()->assertSee('Informasi Alamat &amp; Wilayah', false);
    $this->actingAs($this->user)->get(route('manajemen.pengguna'))->assertOk()->assertSee('Informasi Alamat &amp; Wilayah', false);
});

test('unauthenticated users are redirected to login for all refactored views', function () {
    $person = Person::create(['nama' => 'Test', 'nik' => '3301010101018888']);
    $mustahik = Mustahik::create(['person_id' => $person->id]);
    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PB-GUEST-001',
        'subprogram_id' => $this->subprogram->id,
        'dana_id' => $this->dana->id,
        'mustahik_id' => $mustahik->id,
        'jenis_permohonan' => 'Individu',
        'judul_surat' => 'Permohonan Bantuan',
        'uraian_permohonan' => 'Uraian pengajuan',
        'tanggal_diterima' => now()->toDateString(),
        'status' => 'draft',
    ]);

    $routes = [
        route('profile'),
        route('manajemen.baznas'),
        route('manajemen.lembaga'),
        route('manajemen.upz'),
        route('manajemen.mustahik'),
        route('manajemen.pengguna'),
        route('pengajuan.edit-pemohon', $pengajuan->id),
    ];

    foreach ($routes as $url) {
        $response = $this->get($url);
        $response->assertRedirect(route('login'));
    }
});


