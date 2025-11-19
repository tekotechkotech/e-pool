<?php

use App\Models\User;
use App\Models\Person;
use App\Models\Lembaga;
use App\Models\Program;
use App\Models\Subprogram;
use App\Models\Asnaf;
use App\Models\Dana;
use App\Models\Mustahik;
use App\Models\PengajuanBantuan;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Wilayah;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $adminPerson = Person::create(['nama' => 'Staff BAZNAS', 'nik' => '9999888877776666']);
    $this->user = User::create([
        'person_id' => $adminPerson->id,
        'email' => 'admin@baznas.go.id',
        'password' => bcrypt('password'),
    ]);

    $jabatan = PengurusJabatan::firstOrCreate(
        ['nama' => 'Admin BAZNAS'],
        ['kode' => 'JB-003', 'tingkat' => 'baznas', 'is_active' => true]
    );

    Pengurus::create([
        'jenis_pengurus' => 'Baznas',
        'person_id' => $adminPerson->id,
        'pengurus_jabatan_id' => $jabatan->id,
        'is_active' => true,
    ]);

    $this->program = Program::create(['nama' => 'Kemanusiaan']);
    $this->subprogram = Subprogram::create([
        'program_id' => $this->program->id,
        'nama' => 'Bencana & Darurat',
        'jenis' => 'Umum',
    ]);
});

test('create page renders successfully', function () {
    $response = $this->actingAs($this->user)->get(route('pengajuan.umum.create'));
    $response->assertOk();
    $response->assertSee('penerimaManfaat');
});

test('can create person via inline api endpoint', function () {
    $response = $this->actingAs($this->user)->postJson('/pengajuan/umum/person', [
        'nik' => '3201012345670001',
        'nama' => 'Budi Santoso',
        'telepon' => '081234567890',
        'pekerjaan' => 'Buruh Harian',
        'alamat' => 'Jl. Merdeka No. 10',
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'nik' => '3201012345670001',
        'nama' => 'Budi Santoso',
    ]);

    $this->assertDatabaseHas('persons', [
        'nik' => '3201012345670001',
        'nama' => 'Budi Santoso',
    ]);
});

test('can create lembaga via inline api endpoint', function () {
    PengurusJabatan::firstOrCreate(['nama' => 'Ketua'], ['kode' => 'JB-KT', 'tingkat' => 'lembaga', 'is_active' => true]);

    $response = $this->actingAs($this->user)->postJson('/pengajuan/umum/lembaga', [
        'nama' => 'Musholla Al-Ikhlas',
        'jenis' => 'Musholla',
        'telepon' => '089876543210',
        'alamat' => 'Kampung Baru RT 01 RW 02',
        'pengurus' => [
            [
                'nik' => '3201019999990001',
                'nama' => 'Haji Mansur',
                'jabatan' => 'Ketua',
            ],
        ],
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'nama' => 'Musholla Al-Ikhlas',
        'ketua_nama' => 'Haji Mansur',
    ]);

    $this->assertDatabaseHas('lembagas', [
        'nama' => 'Musholla Al-Ikhlas',
        'ketua_nama' => 'Haji Mansur',
    ]);
});

test('can search person and lembaga via api', function () {
    Person::create([
        'nik' => '3201017777770001',
        'nama' => 'Siti Aminah',
    ]);

    $resPerson = $this->actingAs($this->user)->getJson('/api/search/person-by-nik?nik=320101777777');
    $resPerson->assertOk();
    $resPerson->assertJsonFragment(['nama' => 'Siti Aminah']);

    Lembaga::create([
        'nama' => 'Yayasan Nurul Fikri',
        'jenis' => 'Yayasan',
        'is_upz' => false,
        'is_active' => true,
    ]);

    $resLembaga = $this->actingAs($this->user)->getJson('/api/search/lembaga-by-nama?q=Nurul');
    $resLembaga->assertOk();
    $resLembaga->assertJsonFragment(['nama' => 'Yayasan Nurul Fikri']);
});

test('can submit pengajuan bantuan umum with penerima manfaat array', function () {
    $beneficiaryPerson = Person::create([
        'nik' => '3201015555550001',
        'nama' => 'Penerima Pertama',
        'alamat' => 'Desa Suka Maju',
    ]);

    $beneficiaryLembaga = Lembaga::create([
        'nama' => 'Masjid Raya Darussalam',
        'jenis' => 'Masjid',
        'is_upz' => false,
        'is_active' => true,
    ]);

    $payload = [
        'jenis_permohonan' => 'Individu',
        'tipe_pemohon' => 'perorangan',
        'p_nik' => '3201011111110001',
        'p_nama' => 'Pemohon Asli',
        'p_telepon' => '0811111111',
        'p_alamat' => 'Alamat Pemohon',
        'judul_surat' => 'Proposal Bantuan Kebencanaan',
        'nomor_surat' => '001/PROP/2026',
        'tanggal_surat' => '2026-10-24',
        'tanggal_diterima' => '2026-10-24',
        'penerima_manfaat' => [
            [
                'id' => 1,
                'jenis' => 'perorangan',
                'person_id' => $beneficiaryPerson->id,
                'nik' => $beneficiaryPerson->nik,
                'nama' => $beneficiaryPerson->nama,
                'alamat' => $beneficiaryPerson->alamat,
                'program_id' => $this->program->id,
                'subprogram_id' => $this->subprogram->id,
                'keterangan' => 'Bantuan sembako bencana',
            ],
            [
                'id' => 2,
                'jenis' => 'lembaga',
                'lembaga_id' => $beneficiaryLembaga->id,
                'nama' => $beneficiaryLembaga->nama,
                'program_id' => $this->program->id,
                'subprogram_id' => $this->subprogram->id,
                'keterangan' => 'Bantuan perbaikan kubah',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)->post(route('pengajuan.umum.store'), $payload);
    $response->assertSessionHasNoErrors();

    $pengajuan = PengajuanBantuan::where('judul_surat', 'Proposal Bantuan Kebencanaan')->first();
    expect($pengajuan)->not->toBeNull();
    $response->assertRedirect(route('pengajuan.umum.show', ['pengajuan' => $pengajuan->id]));

    // Check beneficiaries attached
    expect($pengajuan->pengajuanMustahiks()->count())->toBe(2);
});

test('can search people and lembaga with generic search query', function () {
    Person::create([
        'nik' => '3201013333330001',
        'nama' => 'Fauzi Rohman',
        'telepon' => '085712345678',
    ]);

    $res = $this->actingAs($this->user)->getJson('/api/search/people?q=Fauzi');
    $res->assertOk();
    $res->assertJsonFragment(['nama' => 'Fauzi Rohman']);

    Lembaga::create([
        'nama' => 'Pesantren Modern Al-Hikmah',
        'ketua_nama' => 'Kyai Abdullah',
        'is_upz' => false,
        'is_active' => true,
    ]);

    $resLembaga = $this->actingAs($this->user)->getJson('/api/search/lembaga?q=Hikmah');
    $resLembaga->assertOk();
    $resLembaga->assertJsonFragment(['nama' => 'Pesantren Modern Al-Hikmah']);
});

test('persists mixed multiple beneficiaries correctly to pengajuan_mustahiks and renders in detail view', function () {
    $person1 = Person::create(['nik' => '3201011111110010', 'nama' => 'Mustahik Individu Satu', 'alamat' => 'Jl. Satu']);
    $lembaga1 = Lembaga::create(['nama' => 'Yayasan Amal Satu', 'is_upz' => false, 'is_active' => true]);

    $payload = [
        'jenis_permohonan' => 'Individu',
        'tipe_pemohon' => 'perorangan',
        'p_nik' => '3201011111110099',
        'p_nama' => 'Pemohon Kolektif',
        'p_telepon' => '0812999999',
        'p_alamat' => 'Kantor Desa',
        'judul_surat' => 'Bantuan Gabungan Multi Penerima',
        'nomor_surat' => '009/MULTI/2026',
        'tanggal_surat' => '2026-10-24',
        'tanggal_diterima' => '2026-10-24',
        'penerima_manfaat' => [
            // 1. Existing person
            [
                'id' => 1,
                'jenis' => 'perorangan',
                'person_id' => $person1->id,
                'nik' => $person1->nik,
                'nama' => $person1->nama,
                'program_id' => $this->program->id,
                'subprogram_id' => $this->subprogram->id,
                'keterangan' => 'Santunan 1',
            ],
            // 2. Inline new person
            [
                'id' => 2,
                'jenis' => 'perorangan',
                'nik' => '3201012222220020',
                'nama' => 'Mustahik Individu Dua',
                'alamat' => 'Jl. Dua',
                'program_id' => $this->program->id,
                'subprogram_id' => $this->subprogram->id,
                'keterangan' => 'Santunan 2',
            ],
            // 3. Existing lembaga
            [
                'id' => 3,
                'jenis' => 'lembaga',
                'lembaga_id' => $lembaga1->id,
                'nama' => $lembaga1->nama,
                'program_id' => $this->program->id,
                'subprogram_id' => $this->subprogram->id,
                'keterangan' => 'Renovasi 1',
            ],
            // 4. Inline new lembaga
            [
                'id' => 4,
                'jenis' => 'lembaga',
                'nama' => 'Musholla Al-Barokah Baru',
                'alamat' => 'Jl. Tiga',
                'program_id' => $this->program->id,
                'subprogram_id' => $this->subprogram->id,
                'keterangan' => 'Renovasi 2',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)->post(route('pengajuan.umum.store'), $payload);
    $response->assertSessionHasNoErrors();

    $pengajuan = PengajuanBantuan::where('judul_surat', 'Bantuan Gabungan Multi Penerima')->first();
    expect($pengajuan)->not->toBeNull();

    $pmList = $pengajuan->pengajuanMustahiks()->with('mustahik.person', 'mustahik.lembaga')->get();
    expect($pmList->count())->toBe(4);

    // Verify Person 1
    $pm1 = $pmList->first(fn($pm) => $pm->keterangan === 'Santunan 1');
    expect($pm1)->not->toBeNull()
        ->and($pm1->mustahik->jenis_mustahik)->toBe('perorangan')
        ->and($pm1->mustahik->person->nama)->toBe('Mustahik Individu Satu');

    // Verify Inline Person 2
    $pm2 = $pmList->first(fn($pm) => $pm->keterangan === 'Santunan 2');
    expect($pm2)->not->toBeNull()
        ->and($pm2->mustahik->jenis_mustahik)->toBe('perorangan')
        ->and($pm2->mustahik->person->nama)->toBe('Mustahik Individu Dua')
        ->and($pm2->mustahik->person->nik)->toBe('3201012222220020');

    // Verify Lembaga 1
    $pm3 = $pmList->first(fn($pm) => $pm->keterangan === 'Renovasi 1');
    expect($pm3)->not->toBeNull()
        ->and($pm3->mustahik->jenis_mustahik)->toBe('lembaga')
        ->and($pm3->mustahik->lembaga->nama)->toBe('Yayasan Amal Satu');

    // Verify Inline Lembaga 2
    $pm4 = $pmList->first(fn($pm) => $pm->keterangan === 'Renovasi 2');
    expect($pm4)->not->toBeNull()
        ->and($pm4->mustahik->jenis_mustahik)->toBe('lembaga')
        ->and($pm4->mustahik->lembaga->nama)->toBe('Musholla Al-Barokah Baru');

    // Check detail view renders without Blade error
    $viewResp = $this->actingAs($this->user)->get(route('pengajuan.umum.show', [
        'pengajuan' => $pengajuan->id,
        'tab' => 'detail',
    ]));
    $viewResp->assertOk();
    $viewResp->assertSee('Mustahik Individu Satu');
    $viewResp->assertSee('Mustahik Individu Dua');
    $viewResp->assertSee('Yayasan Amal Satu');
    $viewResp->assertSee('Musholla Al-Barokah Baru');
});

test('validation rejects duplicate nik on person create and invalid ketua on lembaga create', function () {
    PengurusJabatan::firstOrCreate(['nama' => 'Ketua'], ['kode' => 'JB-KT', 'tingkat' => 'lembaga', 'is_active' => true]);
    PengurusJabatan::firstOrCreate(['nama' => 'Sekretaris'], ['kode' => 'JB-SK', 'tingkat' => 'lembaga', 'is_active' => true]);

    Person::create([
        'nik' => '3201019999990001',
        'nama' => 'Warga Lama',
    ]);

    // Duplicate NIK
    $resDup = $this->actingAs($this->user)->postJson('/pengajuan/umum/person', [
        'nik' => '3201019999990001',
        'nama' => 'Warga Baru',
        'telepon' => '081234567899',
    ]);
    $resDup->assertStatus(422);
    $resDup->assertJsonValidationErrors(['nik']);

    // Lembaga without Ketua
    $resNoKetua = $this->actingAs($this->user)->postJson('/pengajuan/umum/lembaga', [
        'nama' => 'Masjid Tanpa Ketua',
        'alamat' => 'Jl. Mawar No. 1',
        'pengurus' => [
            [
                'nik' => '3201018888880001',
                'nama' => 'Bambang',
                'jabatan' => 'Sekretaris',
            ],
        ],
    ]);
    $resNoKetua->assertStatus(422);
    $resNoKetua->assertJsonValidationErrors(['pengurus']);
});

test('can update person and lembaga inline', function () {
    PengurusJabatan::firstOrCreate(['nama' => 'Ketua'], ['kode' => 'JB-KT', 'tingkat' => 'lembaga', 'is_active' => true]);
    PengurusJabatan::firstOrCreate(['nama' => 'Sekretaris'], ['kode' => 'JB-SK', 'tingkat' => 'lembaga', 'is_active' => true]);

    $person = Person::create([
        'nik' => '3201015555550002',
        'nama' => 'Nama Lama',
        'telepon' => '0811111111',
        'alamat' => 'Alamat Lama',
    ]);

    $resPerson = $this->actingAs($this->user)->putJson("/pengajuan/umum/person/{$person->id}", [
        'nik' => '3201015555550002',
        'nama' => 'Nama Baru',
        'telepon' => '0822222222',
        'alamat' => 'Alamat Baru',
    ]);
    $resPerson->assertOk();
    $this->assertDatabaseHas('persons', [
        'id' => $person->id,
        'nama' => 'Nama Baru',
        'telepon' => '0822222222',
    ]);

    $lembaga = Lembaga::create([
        'nama' => 'Musholla Lama',
        'alamat' => 'Alamat Lembaga Lama',
        'ketua_nama' => 'Ketua Lama',
        'is_upz' => false,
        'is_active' => true,
    ]);

    $resLembaga = $this->actingAs($this->user)->putJson("/pengajuan/umum/lembaga/{$lembaga->id}", [
        'nama' => 'Musholla Terbarukan',
        'alamat' => 'Alamat Lembaga Baru',
        'pengurus' => [
            [
                'nik' => '3201019999990002',
                'nama' => 'Ustadz Ahmad',
                'jabatan' => 'Ketua',
            ],
        ],
    ]);
    $resLembaga->assertOk();
    $this->assertDatabaseHas('lembagas', [
        'id' => $lembaga->id,
        'nama' => 'Musholla Terbarukan',
        'ketua_nama' => 'Ustadz Ahmad',
    ]);
});

test('can update person via inline api endpoint', function () {
    $person = Person::create([
        'nik' => '3201019999990001',
        'nama' => 'Nama Lama',
        'alamat' => 'Alamat Lama',
    ]);

    $response = $this->actingAs($this->user)->putJson("/pengajuan/umum/person/{$person->id}", [
        'nik' => '3201019999990001',
        'nama' => 'Nama Baru Terupdate',
        'alamat' => 'Alamat Baru Terupdate',
    ]);

    $response->assertOk();
    $response->assertJsonFragment([
        'nama' => 'Nama Baru Terupdate',
        'alamat' => 'Alamat Baru Terupdate',
    ]);

    expect($person->fresh()->nama)->toBe('Nama Baru Terupdate');
});

test('can update lembaga via inline api endpoint', function () {
    $lembaga = Lembaga::create([
        'nama' => 'Yayasan Lama',
        'jenis' => 'Yayasan',
        'is_upz' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->putJson("/pengajuan/umum/lembaga/{$lembaga->id}", [
        'nama' => 'Yayasan Baru Terupdate',
        'jenis' => 'Yayasan',
        'alamat' => 'Jl. Baru No. 10',
        'pengurus' => [
            [
                'nik' => '3201018888880001',
                'nama' => 'Ketua Terupdate',
                'jabatan' => 'Ketua',
            ],
        ],
    ]);

    $response->assertOk();
    $response->assertJsonFragment([
        'nama' => 'Yayasan Baru Terupdate',
    ]);

    expect($lembaga->fresh()->nama)->toBe('Yayasan Baru Terupdate');
});

test('validates lembaga requires ketua', function () {
    $response = $this->actingAs($this->user)->postJson('/pengajuan/umum/lembaga', [
        'nama' => 'Lembaga Tanpa Ketua',
        'alamat' => 'Jl. Tanpa Ketua',
        'pengurus' => [
            [
                'nik' => '3201018888880002',
                'nama' => 'Hanya Sekretaris',
                'jabatan' => 'Sekretaris',
            ],
        ],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['pengurus']);
});

test('can update pengajuan berkas via edit controller', function () {
    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PJ-2026-TEST-01',
        'jenis_permohonan' => 'Individu',
        'judul_surat' => 'Judul Lama',
        'nomor_surat' => '001/LAMA',
        'tanggal_surat' => '2026-10-01',
        'tanggal_diterima' => '2026-10-02',
        'uraian_permohonan' => 'Uraian Lama',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->put(route('pengajuan.update', $pengajuan->id), [
        'judul_surat' => 'Judul Baru Diperbarui',
        'nomor_surat' => '002/BARU',
        'tanggal_surat' => '2026-10-10',
        'tanggal_diterima' => '2026-10-11',
        'uraian_permohonan' => 'Uraian Baru Diperbarui',
    ]);

    $response->assertRedirect(route('pengajuan.umum.show', $pengajuan->id));
    $this->assertDatabaseHas('pengajuan_bantuans', [
        'id' => $pengajuan->id,
        'judul_surat' => 'Judul Baru Diperbarui',
        'nomor_surat' => '002/BARU',
        'uraian_permohonan' => 'Uraian Baru Diperbarui',
    ]);
});

test('can update pemohon individu and sync snapshots and mustahik pekerjaan', function () {
    $person = Person::create([
        'nik' => '3201017777770001',
        'nama' => 'Pemohon Awal',
        'telepon' => '0812000000',
        'alamat' => 'Alamat Awal',
    ]);
    $mustahik = Mustahik::create([
        'person_id' => $person->id,
        'pekerjaan' => 'Pekerjaan Awal',
        'jenis_mustahik' => 'perorangan',
    ]);
    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PJ-2026-TEST-02',
        'jenis_permohonan' => 'Individu',
        'mustahik_id' => $mustahik->id,
        'mustahik_nama_snapshot' => $person->nama,
        'mustahik_nik_snapshot' => $person->nik,
        'mustahik_kontak_snapshot' => $person->telepon,
        'mustahik_alamat_snapshot' => $person->alamat,
        'judul_surat' => 'Permohonan Bantuan',
        'tanggal_surat' => '2026-10-01',
        'tanggal_diterima' => '2026-10-02',
        'uraian_permohonan' => 'Permohonan Bantuan',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->put(route('pengajuan.update-pemohon', $pengajuan->id), [
        'p_nama' => 'Pemohon Terupdate',
        'p_nik' => '3201017777770001',
        'p_telepon' => '0899999999',
        'p_alamat' => 'Alamat Terupdate',
        'p_pekerjaan' => 'Wiraswasta',
    ]);

    $response->assertRedirect(route('pengajuan.umum.show', $pengajuan->id));
    $this->assertDatabaseHas('persons', [
        'id' => $person->id,
        'nama' => 'Pemohon Terupdate',
        'telepon' => '0899999999',
        'alamat' => 'Alamat Terupdate',
    ]);
    $this->assertDatabaseHas('mustahiks', [
        'id' => $mustahik->id,
        'pekerjaan' => 'Wiraswasta',
    ]);
    $this->assertDatabaseHas('pengajuan_bantuans', [
        'id' => $pengajuan->id,
        'mustahik_nama_snapshot' => 'Pemohon Terupdate',
        'mustahik_kontak_snapshot' => '0899999999',
        'mustahik_alamat_snapshot' => 'Alamat Terupdate',
    ]);
});

test('jumlah_orang and file_surat accessor stay synchronized with database', function () {
    $person = Person::create(['nik' => '3201018888880005', 'nama' => 'Mustahik Sync']);
    $mustahik = Mustahik::create(['person_id' => $person->id, 'jenis_mustahik' => 'perorangan']);
    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PJ-2026-TEST-03',
        'jenis_permohonan' => 'Individu',
        'mustahik_id' => $mustahik->id,
        'judul_surat' => 'Uji Sync DB',
        'tanggal_surat' => '2026-10-01',
        'tanggal_diterima' => '2026-10-02',
        'uraian_permohonan' => 'Permohonan Sync DB',
        'created_by' => $this->user->id,
    ]);

    expect($pengajuan->file_surat)->toBeNull();

    $pengajuan->lampirans()->create([
        'category' => 'surat',
        'name' => 'Surat Permohonan',
        'file_path' => 'lampiran_pengajuan/test-surat.pdf',
    ]);

    expect($pengajuan->fresh()->file_surat)->toBe('lampiran_pengajuan/test-surat.pdf');

    $this->actingAs($this->user)->post(route('pengajuan.mustahik.store', $pengajuan->id), [
        'nama' => 'Mustahik Tambahan 1',
        'nik' => '3201019999990009',
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
    ]);

    expect($pengajuan->fresh()->jumlah_orang)->toBe(1);

    $pm = $pengajuan->pengajuanMustahiks()->first();
    $this->actingAs($this->user)->delete(route('pengajuan.mustahik.destroy', [$pengajuan->id, $pm->id]));

    expect($pengajuan->fresh()->jumlah_orang)->toBe(0);
});

test('can upload lampiran_surat and lampiran_tambahan during create and save to database lampirans', function () {
    Storage::fake('public');

    $fileSurat = UploadedFile::fake()->create('surat-permohonan.pdf', 150, 'application/pdf');
    $fileTambahan = UploadedFile::fake()->image('ktp-lampiran.jpg');

    $payload = [
        'jenis_permohonan' => 'Individu',
        'p_nik' => '3201016666660001',
        'p_nama' => 'Pemohon Berkas Lampiran',
        'judul_surat' => 'Permohonan Dengan Lampiran File',
        'tanggal_surat' => '2026-10-24',
        'tanggal_diterima' => '2026-10-24',
        'uraian_permohonan' => 'Uraian pengajuan berkas',
        'program_id' => $this->program->id,
        'subprogram_id' => $this->subprogram->id,
        'lampiran_surat' => $fileSurat,
        'lampiran_tambahan' => [$fileTambahan],
    ];

    $response = $this->actingAs($this->user)->post(route('pengajuan.umum.store'), $payload);
    $response->assertSessionHasNoErrors();

    $pengajuan = PengajuanBantuan::where('judul_surat', 'Permohonan Dengan Lampiran File')->first();
    expect($pengajuan)->not->toBeNull();

    $this->assertDatabaseHas('lampirans', [
        'lampiranable_type' => PengajuanBantuan::class,
        'lampiranable_id' => $pengajuan->id,
        'category' => 'surat',
    ]);

    $this->assertDatabaseHas('lampirans', [
        'lampiranable_type' => PengajuanBantuan::class,
        'lampiranable_id' => $pengajuan->id,
        'category' => 'pendukung',
        'name' => 'ktp-lampiran.jpg',
    ]);

    expect($pengajuan->fresh()->file_surat)->not->toBeNull();
});

test('can upload file_surat during edit pengajuan berkas and update lampiran surat', function () {
    Storage::fake('public');

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PJ-2026-TEST-UPLOAD',
        'jenis_permohonan' => 'Individu',
        'judul_surat' => 'Surat Belum Diupload',
        'nomor_surat' => '010/AWAL',
        'tanggal_surat' => '2026-10-01',
        'tanggal_diterima' => '2026-10-02',
        'uraian_permohonan' => 'Uraian Awal',
        'created_by' => $this->user->id,
    ]);

    $fileBaru = UploadedFile::fake()->create('surat-revisi.pdf', 200, 'application/pdf');

    $response = $this->actingAs($this->user)->put(route('pengajuan.update', $pengajuan->id), [
        'judul_surat' => 'Surat Setelah Diupload',
        'nomor_surat' => '010/REVISI',
        'tanggal_surat' => '2026-10-05',
        'tanggal_diterima' => '2026-10-06',
        'uraian_permohonan' => 'Uraian Revisi',
        'file_surat' => $fileBaru,
    ]);

    $response->assertRedirect(route('pengajuan.umum.show', $pengajuan->id));

    $this->assertDatabaseHas('pengajuan_bantuans', [
        'id' => $pengajuan->id,
        'judul_surat' => 'Surat Setelah Diupload',
        'nomor_surat' => '010/REVISI',
    ]);

    $this->assertDatabaseHas('lampirans', [
        'lampiranable_type' => PengajuanBantuan::class,
        'lampiranable_id' => $pengajuan->id,
        'category' => 'surat',
        'name' => 'Surat Permohonan / Proposal',
    ]);

    expect($pengajuan->fresh()->file_surat)->not->toBeNull();
});

test('can update pemohon lembaga and sync snapshots on pengajuan_bantuans', function () {
    $lembaga = Lembaga::create([
        'nama' => 'Lembaga Awal',
        'telepon' => '081234560000',
        'alamat' => 'Alamat Lembaga Awal',
        'is_upz' => false,
        'is_active' => true,
    ]);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan' => 'PJ-2026-TEST-LEMBAGA',
        'jenis_permohonan' => 'Lembaga',
        'lembaga_id' => $lembaga->id,
        'lembaga_nama_snapshot' => $lembaga->nama,
        'lembaga_kontak_snapshot' => $lembaga->telepon,
        'lembaga_alamat_snapshot' => $lembaga->alamat,
        'judul_surat' => 'Permohonan Lembaga',
        'tanggal_surat' => '2026-10-01',
        'tanggal_diterima' => '2026-10-02',
        'uraian_permohonan' => 'Uraian Lembaga',
        'created_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->put(route('pengajuan.update-pemohon', $pengajuan->id), [
        'u_nama' => 'Lembaga Terupdate',
        'u_telepon' => '089988776655',
        'u_alamat' => 'Alamat Lembaga Terupdate',
    ]);

    $response->assertRedirect(route('pengajuan.upz.show', $pengajuan->id));

    $this->assertDatabaseHas('lembagas', [
        'id' => $lembaga->id,
        'nama' => 'Lembaga Terupdate',
        'telepon' => '089988776655',
        'alamat' => 'Alamat Lembaga Terupdate',
    ]);

    $this->assertDatabaseHas('pengajuan_bantuans', [
        'id' => $pengajuan->id,
        'lembaga_nama_snapshot' => 'Lembaga Terupdate',
        'lembaga_kontak_snapshot' => '089988776655',
        'lembaga_alamat_snapshot' => 'Alamat Lembaga Terupdate',
    ]);
});

