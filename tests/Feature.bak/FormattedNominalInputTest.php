<?php

use App\Models\User;
use App\Models\Person;
use App\Models\Dana;
use App\Models\Barang;
use App\Models\PengadaanBarang;
use App\Models\DanaTransaksi;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use App\Models\Program;
use App\Models\Subprogram;
use App\Models\Asnaf;
use App\Models\Mustahik;
use App\Models\PengajuanBantuan;
use App\Models\PengajuanMustahik;
use App\Models\PengajuanSurvey;
use App\Models\PengajuanSurveyHadKifayah;
use App\Models\PengajuanSurveyAsesmen;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $adminPerson = Person::create(['nama' => 'Admin BAZNAS', 'nik' => '9999999999999999']);
    $this->user = User::create([
        'person_id' => $adminPerson->id,
        'email' => 'admin@baznas.go.id',
        'password' => bcrypt('password'),
    ]);

    $jabatanAdmin = PengurusJabatan::firstOrCreate(
        ['nama' => 'Admin BAZNAS'],
        ['kode' => 'JB-002', 'tingkat' => 'baznas', 'is_active' => true]
    );

    Pengurus::create([
        'jenis_pengurus' => 'Baznas',
        'person_id' => $adminPerson->id,
        'pengurus_jabatan_id' => $jabatanAdmin->id,
        'is_active' => true,
    ]);

    $this->dana = Dana::create([
        'kode' => '1.01',
        'nama' => 'Kas Zakat',
        'kategori' => 'Zakat',
        'saldo' => 10000000,
        'is_active' => true,
    ]);
});

test('store penerimaan dana accepts formatted thousand separator string', function () {
    $response = $this->actingAs($this->user)->post(route('master.danas.penerimaan'), [
        'penerimaan_dana_id' => $this->dana->id,
        'penerimaan_tanggal' => '2026-10-26',
        'penerimaan_nominal' => '5.000.000',
        'penerimaan_sumber'  => 'Muzakki H. Ahmad',
        'penerimaan_uraian'  => 'Infaq tunai',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('dana_transaksis', [
        'dana_id' => $this->dana->id,
        'nominal' => 5000000,
        'jenis'   => 'Masuk',
    ]);
});

test('store pengadaan barang accepts formatted thousand separator string', function () {
    $barang = Barang::create([
        'kode' => 'BRG-1234',
        'nama' => 'Beras Premium',
        'kategori' => 'Sembako',
        'satuan' => 'Kg',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)->post(route('master.barangs.pengadaan.store'), [
        'nomor_ba'     => 'BA-TEST/2026/001',
        'tanggal_ba'   => '2026-10-26',
        'barang_id'    => $barang->id,
        'jumlah'       => 10,
        'harga_satuan' => '15.000',
        'harga_total'  => '150.000',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengadaan_barangs', [
        'barang_id'    => $barang->id,
        'harga_satuan' => 15000,
        'harga_total'  => 150000,
    ]);
});

test('pengajuan disposisi and penyaluran accept formatted thousand separator string', function () {
    $program = Program::create(['nama' => 'BAZNAS Sehat']);
    $subprogram = Subprogram::create(['program_id' => $program->id, 'nama' => 'Bantuan Berobat', 'jenis' => 'Umum']);
    $asnaf = Asnaf::create(['nama' => 'Fakir', 'is_active' => true]);

    $person = Person::create(['nama' => 'Mustahik Test', 'nik' => '3301010101019999']);
    $mustahik = Mustahik::create(['person_id' => $person->id]);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan'  => 'PB-TEST-0001',
        'judul_surat'      => 'Bantuan Pengobatan',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => '2026-10-26',
        'uraian_permohonan'=> 'Bantuan pasien sakit',
        'mustahik_id'      => $mustahik->id,
        'created_by'       => $this->user->id,
    ]);

    $pm = PengajuanMustahik::create([
        'pengajuan_id'  => $pengajuan->id,
        'mustahik_id'   => $mustahik->id,
        'program_id'    => $program->id,
        'subprogram_id' => $subprogram->id,
        'asnaf_id'      => $asnaf->id,
        'nominal'       => 0,
    ]);

    $pimpinanPerson = Person::create(['nama' => 'Pimpinan BAZNAS', 'nik' => '8888888888888888']);
    $pimpinanUser = User::create([
        'person_id' => $pimpinanPerson->id,
        'email'     => 'pimpinan@baznas.go.id',
        'password'  => bcrypt('password'),
    ]);
    $jabatanPimpinan = PengurusJabatan::firstOrCreate(
        ['nama' => 'Pimpinan BAZNAS'],
        ['kode' => 'JB-001', 'tingkat' => 'baznas', 'is_active' => true]
    );
    Pengurus::create([
        'jenis_pengurus'      => 'Baznas',
        'person_id'           => $pimpinanPerson->id,
        'pengurus_jabatan_id' => $jabatanPimpinan->id,
        'is_active'           => true,
    ]);

    // 1. Disposisi dengan format titik
    $resDisposisi = $this->actingAs($pimpinanUser)->post(
        route('pengajuan.mustahik.disposisi.store', ['pengajuanId' => $pengajuan->id, 'mustahikId' => $pm->id]),
        [
            'bentuk_bantuan'    => 'Uang',
            'nominal_disetujui' => '750.000',
            'sumber_dana_id'    => $this->dana->id,
            'instruksi'         => 'Proses Langsung',
        ]
    );
    $resDisposisi->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'id'      => $pm->id,
        'nominal' => 750000,
    ]);

    // 2. Penyaluran dengan format titik
    $resSalur = $this->actingAs($this->user)->post(
        url("/pengajuan/{$pengajuan->id}/mustahik/{$pm->id}/penyaluran"),
        [
            'tanggal_penyaluran' => '2026-10-26',
            'metode_penyaluran'  => 'Tunai',
            'nominal'            => '750.000',
            'sumber_dana_id'     => $this->dana->id,
        ]
    );
    $resSalur->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_mustahiks', [
        'id'      => $pm->id,
        'nominal' => 750000,
    ]);

    // 3. LPJ dengan format titik
    $resLpj = $this->actingAs($this->user)->post(
        route('pengajuan.lpj.store', $pengajuan->id),
        [
            'tanggal_lpj'       => '2026-10-26',
            'nominal_realisasi' => '750.000',
            'nominal_sisa'      => '0',
            'catatan'           => 'Laporan selesai',
        ]
    );
    $resLpj->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_lpj_bas', [
        'pengajuan_id'      => $pengajuan->id,
        'nominal_realisasi' => 750000,
        'nominal_sisa'      => 0,
    ]);
});

test('public-survey had kifayah submission converts formatted thousand separator to clean integer', function () {
    $program = Program::create(['nama' => 'BAZNAS Peduli']);
    $asnaf = Asnaf::create(['nama' => 'Miskin', 'is_active' => true]);
    $person = Person::create(['nama' => 'Mustahik HK', 'nik' => '3301010101018888']);
    $mustahik = Mustahik::create(['person_id' => $person->id]);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan'  => 'PB-HK-0001',
        'judul_surat'      => 'Permohonan Survey HK',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => '2026-10-26',
        'uraian_permohonan'=> 'Survey kelayakan Had Kifayah',
        'mustahik_id'      => $mustahik->id,
        'created_by'       => $this->user->id,
    ]);

    $pm = PengajuanMustahik::create([
        'pengajuan_id' => $pengajuan->id,
        'mustahik_id'  => $mustahik->id,
        'program_id'   => $program->id,
        'asnaf_id'     => $asnaf->id,
        'nominal'      => 0,
    ]);

    $survey = $pm->fresh()->survey;
    $survey->update([
        'surveyor'       => 'Surveyor BAZNAS',
        'nohp_surveyor'  => '081234567890',
        'tanggal_survey' => '2026-10-26',
    ]);

    Http::fake([
        'https://www.puskasbaznas.com/application/hadkifayah/controller/calculate/*' => Http::response([
            'status'      => 'Layak',
            'prioritas'   => 'Prioritas 1',
            'pendapatan'  => '2.500.000',
            'pengeluaran' => '1.000.000',
            'selisih'     => '1.500.000',
            'files'       => 'HK_TEST_123',
        ], 200),
    ]);

    $response = $this->post(route('public.survey.hadkifayah', ['token' => $survey->token]), [
        'nama_mustahik'         => 'Mustahik HK',
        'alamat_mustahik'       => 'Jl. Cilacap Raya No. 10',
        'pendapatan_keluarga'   => '2.500.000',
        'pendapatan_anggotakel' => '750.000',
        'pengeluaran_kk'        => '1.000.000',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_survey_had_kifayahs', [
        'pengajuan_survey_id'   => $survey->id,
        'pendapatan_keluarga'   => 2500000,
        'pendapatan_anggotakel' => 750000,
        'pengeluaran_kk'        => 1000000,
    ]);
});

test('public-survey asesmen submission converts formatted thousand separator to clean integer string', function () {
    $program = Program::create(['nama' => 'BAZNAS Sejahtera']);
    $asnaf = Asnaf::create(['nama' => 'Fisabilillah', 'is_active' => true]);
    $person = Person::create(['nama' => 'Mustahik Asesmen', 'nik' => '3301010101017777']);
    $mustahik = Mustahik::create(['person_id' => $person->id]);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan'  => 'PB-AS-0001',
        'judul_surat'      => 'Permohonan Survey Asesmen',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => '2026-10-26',
        'uraian_permohonan'=> 'Survey asesmen lapangan',
        'mustahik_id'      => $mustahik->id,
        'created_by'       => $this->user->id,
    ]);

    $pm = PengajuanMustahik::create([
        'pengajuan_id' => $pengajuan->id,
        'mustahik_id'  => $mustahik->id,
        'program_id'   => $program->id,
        'asnaf_id'     => $asnaf->id,
        'nominal'      => 0,
    ]);

    $survey = $pm->fresh()->survey;
    $survey->update([
        'surveyor'       => 'Surveyor BAZNAS',
        'nohp_surveyor'  => '081234567890',
        'tanggal_survey' => '2026-10-26',
    ]);

    $response = $this->post(route('public.survey.asesmen', ['token' => $survey->token]), [
        'as_nama_mustahik'             => 'Mustahik Asesmen',
        'as_bersedia_disurvei'         => 'YA',
        'as_total_penghasilan_perbulan'=> '2.500.000',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_survey_asesmens', [
        'pengajuan_survey_id'        => $survey->id,
        'total_penghasilan_perbulan' => '2500000',
    ]);
});

test('nominal input edge cases handle zero, empty, and large numbers cleanly', function () {
    $program = Program::create(['nama' => 'BAZNAS Peduli Edge']);
    $asnaf = Asnaf::create(['nama' => 'Gharimin', 'is_active' => true]);
    $person = Person::create(['nama' => 'Mustahik Edge', 'nik' => '3301010101016666']);
    $mustahik = Mustahik::create(['person_id' => $person->id]);

    $pengajuan = PengajuanBantuan::create([
        'nomor_pengajuan'  => 'PB-EDGE-0001',
        'judul_surat'      => 'Permohonan Edge Cases',
        'jenis_permohonan' => 'Individu',
        'tanggal_diterima' => '2026-10-26',
        'uraian_permohonan'=> 'Test edge cases nominal',
        'mustahik_id'      => $mustahik->id,
        'created_by'       => $this->user->id,
    ]);

    $pm = PengajuanMustahik::create([
        'pengajuan_id' => $pengajuan->id,
        'mustahik_id'  => $mustahik->id,
        'program_id'   => $program->id,
        'asnaf_id'     => $asnaf->id,
        'nominal'      => 0,
    ]);

    $survey = $pm->fresh()->survey;
    $survey->update([
        'surveyor'       => 'Surveyor BAZNAS',
        'nohp_surveyor'  => '081234567890',
        'tanggal_survey' => '2026-10-26',
    ]);

    Http::fake([
        'https://www.puskasbaznas.com/application/hadkifayah/controller/calculate/*' => Http::response([
            'status'      => 'Layak',
            'prioritas'   => 'Prioritas 1',
            'pendapatan'  => '0',
            'pengeluaran' => '0',
            'selisih'     => '0',
            'files'       => 'HK_EDGE_0',
        ], 200),
    ]);

    // 1. Edge Case: 0 and empty in Had Kifayah
    $resHkZero = $this->post(route('public.survey.hadkifayah', ['token' => $survey->token]), [
        'nama_mustahik'         => 'Mustahik Edge',
        'alamat_mustahik'       => 'Jl. Edge No. 0',
        'pendapatan_keluarga'   => '0',
        'pendapatan_anggotakel' => '',
    ]);
    $resHkZero->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_survey_had_kifayahs', [
        'pengajuan_survey_id'   => $survey->id,
        'pendapatan_keluarga'   => 0,
        'pendapatan_anggotakel' => 0,
    ]);

    // 2. Edge Case: Large number (> 1.000.000.000) in Had Kifayah
    $resHkLarge = $this->post(route('public.survey.hadkifayah', ['token' => $survey->token]), [
        'nama_mustahik'         => 'Mustahik Edge',
        'alamat_mustahik'       => 'Jl. Edge No. 0',
        'pendapatan_keluarga'   => '2.500.000.000',
        'pendapatan_anggotakel' => '1.250.000.000',
    ]);
    $resHkLarge->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_survey_had_kifayahs', [
        'pengajuan_survey_id'   => $survey->id,
        'pendapatan_keluarga'   => 2500000000,
        'pendapatan_anggotakel' => 1250000000,
    ]);

    // 3. Edge Case: 0 and large number in Asesmen
    $resAsZero = $this->post(route('public.survey.asesmen', ['token' => $survey->token]), [
        'as_nama_mustahik'             => 'Mustahik Edge',
        'as_bersedia_disurvei'         => 'YA',
        'as_total_penghasilan_perbulan'=> '0',
    ]);
    $resAsZero->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_survey_asesmens', [
        'pengajuan_survey_id'        => $survey->id,
        'total_penghasilan_perbulan' => '0',
    ]);

    $resAsLarge = $this->post(route('public.survey.asesmen', ['token' => $survey->token]), [
        'as_nama_mustahik'             => 'Mustahik Edge',
        'as_bersedia_disurvei'         => 'YA',
        'as_total_penghasilan_perbulan'=> '1.500.000.000',
    ]);
    $resAsLarge->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengajuan_survey_asesmens', [
        'pengajuan_survey_id'        => $survey->id,
        'total_penghasilan_perbulan' => '1500000000',
    ]);

    // 4. Edge Case: Large number in Penerimaan Dana (> 1.000.000.000)
    $resDanaLarge = $this->actingAs($this->user)->post(route('master.danas.penerimaan'), [
        'penerimaan_dana_id' => $this->dana->id,
        'penerimaan_tanggal' => '2026-10-26',
        'penerimaan_nominal' => '1.500.000.000',
        'penerimaan_sumber'  => 'Muzakki Korporat',
    ]);
    $resDanaLarge->assertSessionHasNoErrors();
    $this->assertDatabaseHas('dana_transaksis', [
        'dana_id' => $this->dana->id,
        'nominal' => 1500000000,
    ]);

    // 5. Edge Case: Zero and Large number in Pengadaan Barang
    $barang = Barang::create([
        'kode'      => 'BRG-EDGE',
        'nama'      => 'Barang Edge',
        'kategori'  => 'Pendidikan',
        'satuan'    => 'Unit',
        'is_active' => true,
    ]);

    $resBrgZero = $this->actingAs($this->user)->post(route('master.barangs.pengadaan.store'), [
        'nomor_ba'     => 'BA-EDGE/001',
        'tanggal_ba'   => '2026-10-26',
        'barang_id'    => $barang->id,
        'jumlah'       => 5,
        'harga_satuan' => '0',
        'harga_total'  => '0',
    ]);
    $resBrgZero->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengadaan_barangs', [
        'barang_id'    => $barang->id,
        'harga_satuan' => 0,
        'harga_total'  => 0,
    ]);

    $resBrgLarge = $this->actingAs($this->user)->post(route('master.barangs.pengadaan.store'), [
        'nomor_ba'     => 'BA-EDGE/002',
        'tanggal_ba'   => '2026-10-26',
        'barang_id'    => $barang->id,
        'jumlah'       => 1,
        'harga_satuan' => '1.200.000.000',
        'harga_total'  => '1.200.000.000',
    ]);
    $resBrgLarge->assertSessionHasNoErrors();
    $this->assertDatabaseHas('pengadaan_barangs', [
        'barang_id'    => $barang->id,
        'harga_satuan' => 1200000000,
        'harga_total'  => 1200000000,
    ]);
});
