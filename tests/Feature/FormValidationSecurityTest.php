<?php

namespace Tests\Feature;

use App\Models\Lembaga;
use App\Models\Mustahik;
use App\Models\Muzakki;
use App\Models\Person;
use App\Models\SetoranUpz;
use App\Models\Upz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormValidationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Person $userPerson;
    protected Upz $upz;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userPerson = Person::create([
            'nama' => 'Staff Administrator BAZNAS',
            'nik' => '3301010101019999',
            'nomor_kk' => '3301010101010000',
            'jenis_kelamin' => 'L',
            'email' => 'admin_validation@baznas.id',
        ]);

        $this->user = User::create([
            'person_id' => $this->userPerson->id,
            'email' => 'admin_validation@baznas.id',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->user);

        $this->upz = Upz::create([
            'nama' => 'UPZ Masjid Al-Hidayah',
            'is_upz' => true,
            'is_active' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Validasi NIK pada Modul Muzakki
    |--------------------------------------------------------------------------
    */

    public function test_muzakki_store_accepts_valid_16_digit_numeric_nik(): void
    {
        $validNik = '3301011508850001';

        $response = $this->post(route('muzakki.store'), [
            'nama' => 'H. Ahmad Syarifuddin',
            'nik' => $validNik,
            'lembaga_id' => $this->upz->id,
            'npwz' => '3301001',
            'jenis_kelamin' => 'L',
            'pekerjaan' => 'Wiraswasta',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('muzakki.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('persons', [
            'nik' => $validNik,
            'nama' => 'H. Ahmad Syarifuddin',
        ]);

        $this->assertDatabaseHas('muzakkis', [
            'npwz' => '3301001',
            'pekerjaan' => 'Wiraswasta',
        ]);
    }

    public function test_muzakki_store_rejects_nik_with_less_than_16_digits(): void
    {
        $shortNik = '12345';

        $response = $this->from(route('muzakki.create'))->post(route('muzakki.store'), [
            'nama' => 'Muzakki Invalid NIK',
            'nik' => $shortNik,
            'lembaga_id' => $this->upz->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('muzakki.create'));
        $response->assertSessionHasErrors('nik');

        $errorMsg = session('errors')->first('nik');
        $this->assertMatchesRegularExpression(
            '/NIK harus (berupa|tepat berjumlah) 16 digit angka/i',
            $errorMsg,
            "Pesan error NIK diharapkan dalam Bahasa Indonesia, didapatkan: {$errorMsg}"
        );
        $this->assertStringContainsString('16 digit angka', $errorMsg);
    }

    public function test_muzakki_store_rejects_nik_with_more_than_16_digits(): void
    {
        $longNik = '33010115088500019'; // 17 digit

        $response = $this->from(route('muzakki.create'))->post(route('muzakki.store'), [
            'nama' => 'Muzakki Long NIK',
            'nik' => $longNik,
            'lembaga_id' => $this->upz->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('muzakki.create'));
        $response->assertSessionHasErrors('nik');

        $errorMsg = session('errors')->first('nik');
        $this->assertMatchesRegularExpression(
            '/NIK harus (berupa|tepat berjumlah) 16 digit angka/i',
            $errorMsg,
            "Pesan error NIK diharapkan dalam Bahasa Indonesia, didapatkan: {$errorMsg}"
        );
    }

    public function test_muzakki_store_rejects_non_numeric_nik(): void
    {
        $nonNumericNik = '330101150885000A'; // 16 char tapi berkarakter non-digit

        $response = $this->from(route('muzakki.create'))->post(route('muzakki.store'), [
            'nama' => 'Muzakki Alpha NIK',
            'nik' => $nonNumericNik,
            'lembaga_id' => $this->upz->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('muzakki.create'));
        $response->assertSessionHasErrors('nik');

        $errorMsg = session('errors')->first('nik');
        $this->assertMatchesRegularExpression(
            '/NIK harus (berupa|tepat berjumlah) 16 digit angka/i',
            $errorMsg,
            "Pesan error NIK diharapkan dalam Bahasa Indonesia, didapatkan: {$errorMsg}"
        );
    }

    public function test_muzakki_update_validates_nik_properly(): void
    {
        $person = Person::create([
            'nama' => 'Muzakki Awal',
            'nik' => '3301011508850011',
            'jenis_kelamin' => 'L',
        ]);

        $muzakki = Muzakki::create([
            'person_id' => $person->id,
            'lembaga_id' => $this->upz->id,
            'pekerjaan' => 'PNS',
        ]);

        // Coba update dengan NIK < 16 digit
        $responseInvalid = $this->put(route('muzakki.update', $muzakki->id), [
            'nama' => 'Muzakki Awal Edit',
            'nik' => '12345',
            'pekerjaan' => 'PNS Baru',
        ]);
        $responseInvalid->assertStatus(302);
        $responseInvalid->assertSessionHasErrors('nik');

        // Update dengan NIK valid 16 digit
        $validNewNik = '3301011508850022';
        $responseValid = $this->put(route('muzakki.update', $muzakki->id), [
            'nama' => 'Muzakki Awal Edit',
            'nik' => $validNewNik,
            'pekerjaan' => 'PNS Baru',
        ]);
        $responseValid->assertStatus(302);
        $responseValid->assertRedirect(route('muzakki.index'));
        $responseValid->assertSessionHas('success');

        $this->assertDatabaseHas('persons', [
            'id' => $person->id,
            'nik' => $validNewNik,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Validasi NIK dan Nomor KK pada Modul Mustahik (Store & Update)
    |--------------------------------------------------------------------------
    */

    public function test_mustahik_store_accepts_valid_16_digit_nik_and_nomor_kk(): void
    {
        $nik = '3301022005900001';
        $kk = '3301022005900000';

        $response = $this->post(route('manajemen.mustahik.store'), [
            'is_new_person' => '1',
            'nama' => 'Mustahik Sejahtera',
            'nik' => $nik,
            'nomor_kk' => $kk,
            'jenis_kelamin' => 'L',
            'pekerjaan' => 'Buruh Harian Lepas',
            'keterangan' => 'Fakir Miskin',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('manajemen.mustahik'));
        $response->assertSessionHas('success');
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('persons', [
            'nik' => $nik,
            'nomor_kk' => $kk,
            'nama' => 'Mustahik Sejahtera',
        ]);

        $this->assertDatabaseHas('mustahiks', [
            'pekerjaan' => 'Buruh Harian Lepas',
            'keterangan' => 'Fakir Miskin',
        ]);
    }

    public function test_mustahik_store_rejects_invalid_nomor_kk(): void
    {
        // 1. KK kurang dari 16 digit
        $responseShort = $this->post(route('manajemen.mustahik.store'), [
            'is_new_person' => '1',
            'nama' => 'Mustahik KK Pendek',
            'nik' => '3301022005900002',
            'nomor_kk' => '12345',
            'jenis_kelamin' => 'P',
        ]);
        $responseShort->assertStatus(302);
        $responseShort->assertSessionHasErrors('nomor_kk');

        $errorMsg = session('errors')->first('nomor_kk');
        $this->assertMatchesRegularExpression(
            '/Nomor KK harus (berupa|tepat berjumlah) 16 digit angka/i',
            $errorMsg,
            "Pesan error Nomor KK diharapkan dalam Bahasa Indonesia, didapatkan: {$errorMsg}"
        );

        // 2. KK lebih dari 16 digit
        $responseLong = $this->post(route('manajemen.mustahik.store'), [
            'is_new_person' => '1',
            'nama' => 'Mustahik KK Panjang',
            'nik' => '3301022005900003',
            'nomor_kk' => '33010220059000009',
            'jenis_kelamin' => 'P',
        ]);
        $responseLong->assertStatus(302);
        $responseLong->assertSessionHasErrors('nomor_kk');

        // 3. KK non-numerik
        $responseAlpha = $this->post(route('manajemen.mustahik.store'), [
            'is_new_person' => '1',
            'nama' => 'Mustahik KK Alpha',
            'nik' => '3301022005900004',
            'nomor_kk' => '330102200590000A',
            'jenis_kelamin' => 'P',
        ]);
        $responseAlpha->assertStatus(302);
        $responseAlpha->assertSessionHasErrors('nomor_kk');

        // 4. NIK tidak valid (< 16 digit)
        $responseShortNik = $this->post(route('manajemen.mustahik.store'), [
            'is_new_person' => '1',
            'nama' => 'Mustahik NIK Pendek',
            'nik' => '12345',
            'nomor_kk' => '3301022005900000',
            'jenis_kelamin' => 'P',
        ]);
        $responseShortNik->assertStatus(302);
        $responseShortNik->assertSessionHasErrors('nik');

        // 5. NIK non-numerik
        $responseAlphaNik = $this->post(route('manajemen.mustahik.store'), [
            'is_new_person' => '1',
            'nama' => 'Mustahik NIK Alpha',
            'nik' => '330102200590000X',
            'nomor_kk' => '3301022005900000',
            'jenis_kelamin' => 'P',
        ]);
        $responseAlphaNik->assertStatus(302);
        $responseAlphaNik->assertSessionHasErrors('nik');
    }

    public function test_mustahik_update_validates_nik_and_nomor_kk(): void
    {
        $person = Person::create([
            'nama' => 'Mustahik Existing',
            'nik' => '3301022005900010',
            'nomor_kk' => '3301022005900010',
            'jenis_kelamin' => 'L',
        ]);

        $mustahik = Mustahik::create([
            'person_id' => $person->id,
            'pekerjaan' => 'Petani',
        ]);

        // Update dengan KK tidak valid (< 16 digit)
        $responseInvalid = $this->post(route('manajemen.mustahik.store'), [
            'mustahik_id' => $mustahik->id,
            'person_id' => $person->id,
            'nama' => 'Mustahik Existing Edit',
            'nik' => $person->nik,
            'nomor_kk' => '99999', // Invalid
        ]);
        $responseInvalid->assertStatus(302);
        $responseInvalid->assertSessionHasErrors('nomor_kk');

        // Update dengan NIK tidak valid (< 16 digit)
        $responseInvalidNik = $this->post(route('manajemen.mustahik.store'), [
            'mustahik_id' => $mustahik->id,
            'person_id' => $person->id,
            'nama' => 'Mustahik Existing Edit',
            'nik' => '12345',
            'nomor_kk' => $person->nomor_kk,
        ]);
        $responseInvalidNik->assertStatus(302);
        $responseInvalidNik->assertSessionHasErrors('nik');

        // Update dengan KK valid 16 digit
        $validKK = '3301022005909999';
        $responseValid = $this->post(route('manajemen.mustahik.store'), [
            'mustahik_id' => $mustahik->id,
            'person_id' => $person->id,
            'nama' => 'Mustahik Existing Edit Berhasil',
            'nik' => $person->nik,
            'nomor_kk' => $validKK,
        ]);
        $responseValid->assertStatus(302);
        $responseValid->assertRedirect(route('manajemen.mustahik'));
        $responseValid->assertSessionHas('success');

        $this->assertDatabaseHas('persons', [
            'id' => $person->id,
            'nomor_kk' => $validKK,
            'nama' => 'Mustahik Existing Edit Berhasil',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Validasi NIK pada Pengguna dan Profile
    |--------------------------------------------------------------------------
    */

    public function test_pengguna_store_validates_nik_and_nomor_kk(): void
    {
        // 1. Sukses dengan NIK 16 digit valid
        $responseSuccess = $this->post(route('manajemen.pengguna.store'), [
            'nama' => 'Pengguna Baru',
            'nik' => '3301031010900001',
            'nomor_kk' => '3301031010900000',
            'jenis_kelamin' => 'L',
            'email' => 'pengguna.baru@baznas.id',
        ]);
        $responseSuccess->assertStatus(302);
        $responseSuccess->assertRedirect(route('manajemen.pengguna'));
        $responseSuccess->assertSessionHas('success');

        // 2. Gagal NIK < 16 digit
        $responseShortNik = $this->post(route('manajemen.pengguna.store'), [
            'nama' => 'Pengguna NIK Gagal',
            'nik' => '12345',
            'jenis_kelamin' => 'L',
        ]);
        $responseShortNik->assertStatus(302);
        $responseShortNik->assertSessionHasErrors('nik');

        // 3. Gagal KK non-numerik
        $responseInvalidKK = $this->post(route('manajemen.pengguna.store'), [
            'nama' => 'Pengguna KK Gagal',
            'nik' => '3301031010900002',
            'nomor_kk' => '123456789012345X',
            'jenis_kelamin' => 'L',
        ]);
        $responseInvalidKK->assertStatus(302);
        $responseInvalidKK->assertSessionHasErrors('nomor_kk');
    }

    public function test_profile_update_validates_nik_and_nomor_kk(): void
    {
        // Update profile dengan NIK tidak 16 digit
        $responseInvalidNik = $this->put(route('profile.update'), [
            'nama' => 'Staff Administrator BAZNAS',
            'email' => 'admin_validation@baznas.id',
            'nik' => '123',
            'nomor_kk' => '3301010101010000',
        ]);
        $responseInvalidNik->assertStatus(302);
        $responseInvalidNik->assertSessionHasErrors('nik');

        // Update profile dengan Nomor KK tidak 16 digit
        $responseInvalidKK = $this->put(route('profile.update'), [
            'nama' => 'Staff Administrator BAZNAS',
            'email' => 'admin_validation@baznas.id',
            'nik' => '3301010101019999',
            'nomor_kk' => '456',
        ]);
        $responseInvalidKK->assertStatus(302);
        $responseInvalidKK->assertSessionHasErrors('nomor_kk');

        // Update profile dengan NIK & KK 16 digit valid
        $responseValid = $this->put(route('profile.update'), [
            'nama' => 'Staff Administrator BAZNAS Updated',
            'email' => 'admin_validation@baznas.id',
            'nik' => '3301010101019999',
            'nomor_kk' => '3301010101018888',
        ]);
        $responseValid->assertStatus(302);
        $responseValid->assertRedirect(route('profile'));
        $responseValid->assertSessionHas('status', 'Profil berhasil diperbarui.');

        $this->assertDatabaseHas('persons', [
            'id' => $this->userPerson->id,
            'nama' => 'Staff Administrator BAZNAS Updated',
            'nomor_kk' => '3301010101018888',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Validasi NIK Ketua pada UPZ dan Lembaga
    |--------------------------------------------------------------------------
    */

    public function test_upz_store_validates_ketua_nik(): void
    {
        // 1. Sukses dengan ketua_nik 16 digit numerik
        $validKetuaNik = '3301041505800001';
        $responseSuccess = $this->post(route('manajemen.upz.store'), [
            'nama' => 'UPZ Masjid Jami Al-Falah',
            'ketua_nama' => 'K.H. Mustofa',
            'ketua_nik' => $validKetuaNik,
            'jenis' => 'UPZ Masjid',
        ]);
        $responseSuccess->assertStatus(302);
        $responseSuccess->assertRedirect(route('manajemen.upz'));
        $responseSuccess->assertSessionHas('success');

        $this->assertDatabaseHas('lembagas', [
            'nama' => 'UPZ Masjid Jami Al-Falah',
            'ketua_nama' => 'K.H. Mustofa',
            'ketua_nik' => $validKetuaNik,
            'is_upz' => true,
        ]);

        // 2. Gagal dengan ketua_nik < 16 digit
        $responseShort = $this->post(route('manajemen.upz.store'), [
            'nama' => 'UPZ Masjid Gagal NIK',
            'ketua_nama' => 'K.H. Abdullah',
            'ketua_nik' => '12345',
        ]);
        $responseShort->assertStatus(302);
        $responseShort->assertSessionHasErrors('ketua_nik');

        $errorMsg = session('errors')->first('ketua_nik');
        $this->assertMatchesRegularExpression(
            '/NIK Ketua harus (berupa|tepat berjumlah) 16 digit angka/i',
            $errorMsg,
            "Pesan error NIK Ketua diharapkan dalam Bahasa Indonesia, didapatkan: {$errorMsg}"
        );

        // 3. Gagal dengan ketua_nik non-numerik
        $responseAlpha = $this->post(route('manajemen.upz.store'), [
            'nama' => 'UPZ Masjid Gagal Alpha',
            'ketua_nama' => 'K.H. Abdullah',
            'ketua_nik' => '330104150580000X',
        ]);
        $responseAlpha->assertStatus(302);
        $responseAlpha->assertSessionHasErrors('ketua_nik');
    }

    public function test_lembaga_store_validates_ketua_nik(): void
    {
        // 1. Sukses dengan ketua_nik 16 digit numerik
        $validKetuaNik = '3301052512820001';
        $responseSuccess = $this->post(route('manajemen.lembaga.store'), [
            'nama' => 'Yayasan Pendidikan Islam Darussalam',
            'ketua_nama' => 'Drs. H. Maimun',
            'ketua_nik' => $validKetuaNik,
            'jenis' => 'Yayasan',
        ]);
        $responseSuccess->assertStatus(302);
        $responseSuccess->assertRedirect(route('manajemen.lembaga'));
        $responseSuccess->assertSessionHas('success');

        $this->assertDatabaseHas('lembagas', [
            'nama' => 'Yayasan Pendidikan Islam Darussalam',
            'ketua_nama' => 'Drs. H. Maimun',
            'ketua_nik' => $validKetuaNik,
            'is_upz' => false,
        ]);

        // 2. Gagal dengan ketua_nik > 16 digit
        $responseLong = $this->post(route('manajemen.lembaga.store'), [
            'nama' => 'Yayasan Gagal NIK',
            'ketua_nama' => 'H. Suwardi',
            'ketua_nik' => '330105251282000199',
        ]);
        $responseLong->assertStatus(302);
        $responseLong->assertSessionHasErrors('ketua_nik');

        // 3. Gagal dengan ketua_nik non-numerik
        $responseAlpha = $this->post(route('manajemen.lembaga.store'), [
            'nama' => 'Yayasan Gagal Alpha',
            'ketua_nama' => 'H. Suwardi',
            'ketua_nik' => 'ABC1234567890123',
        ]);
        $responseAlpha->assertStatus(302);
        $responseAlpha->assertSessionHasErrors('ketua_nik');
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Validasi File Upload (Mimetype, Max Size)
    |--------------------------------------------------------------------------
    */

    public function test_setoran_upz_bukti_transfer_upload_validation(): void
    {
        Storage::fake('public');

        $setoran = SetoranUpz::create([
            'nomor_setoran' => 'SET-UPZ/2026/11/TEST1',
            'lembaga_id' => $this->upz->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'total_zakat' => 1000000,
            'status' => 'Pending',
            'user_id' => $this->user->id,
        ]);

        // 1. Upload file dengan MIME type terlarang (script shell / php)
        $disallowedFile = UploadedFile::fake()->create('malicious.sh', 100, 'text/x-sh');
        $responseMimeError = $this->post(route('pengumpulan.upz.upload-bukti', $setoran->id), [
            'bukti_setor' => $disallowedFile,
        ]);
        $responseMimeError->assertStatus(302);
        $responseMimeError->assertSessionHasErrors('bukti_setor');

        // 2. Upload file melebihi batas 5120 KB (6MB)
        $oversizedFile = UploadedFile::fake()->create('huge_receipt.jpg', 6000, 'image/jpeg');
        $responseSizeError = $this->post(route('pengumpulan.upz.upload-bukti', $setoran->id), [
            'bukti_setor' => $oversizedFile,
        ]);
        $responseSizeError->assertStatus(302);
        $responseSizeError->assertSessionHasErrors('bukti_setor');

        // 3. Upload file valid (JPEG 500 KB)
        $validFile = UploadedFile::fake()->image('bukti_transfer.jpg', 800, 600)->size(500);
        $responseSuccess = $this->post(route('pengumpulan.upz.upload-bukti', $setoran->id), [
            'bukti_setor' => $validFile,
        ]);
        $responseSuccess->assertStatus(302);
        $responseSuccess->assertSessionHas('success');
        $responseSuccess->assertSessionHasNoErrors();

        $setoran->refresh();
        $this->assertNotNull($setoran->bukti_setor);
        Storage::disk('public')->assertExists($setoran->bukti_setor);
    }

    public function test_muzakki_import_file_validation(): void
    {
        // 1. Upload file format tidak diizinkan (.pdf)
        $invalidFile = UploadedFile::fake()->create('document.pdf', 200, 'application/pdf');
        $responseInvalid = $this->post(route('muzakki.import.process'), [
            'file' => $invalidFile,
            'lembaga_id' => $this->upz->id,
        ]);
        $responseInvalid->assertStatus(302);
        $responseInvalid->assertSessionHasErrors('file');

        // 2. Upload file melebihi batas 5120 KB
        $oversizedFile = UploadedFile::fake()->create('large_data.csv', 6000, 'text/csv');
        $responseOversized = $this->post(route('muzakki.import.process'), [
            'file' => $oversizedFile,
            'lembaga_id' => $this->upz->id,
        ]);
        $responseOversized->assertStatus(302);
        $responseOversized->assertSessionHasErrors('file');
    }

    /*
    |--------------------------------------------------------------------------
    | 6. Form Hydration (Old Value) saat Validasi Gagal
    |--------------------------------------------------------------------------
    */

    public function test_form_hydration_preserves_old_inputs_on_validation_failure(): void
    {
        // Submit Muzakki store dengan NIK invalid tetapi field lain terisi lengkap
        $formData = [
            'nama' => 'Drs. H. Bambang Pamungkas',
            'nik' => '999', // Invalid NIK (< 16 digit)
            'lembaga_id' => (string) $this->upz->id,
            'npwz' => 'NPWZ-998877',
            'npwp' => '09.123.456.7-123.000',
            'nip' => '198001012005011001',
            'pekerjaan' => 'Dosen Universitas',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Cilacap',
            'tanggal_lahir' => '1980-01-01',
            'telepon' => '0282123456',
            'handphone' => '081234567890',
            'email' => 'bambang@kampus.ac.id',
            'alamat' => 'Jl. Jend. Sudirman No. 45',
            'alamat_kantor' => 'Kampus A Lt. 2',
            'alamat_korespondensi' => 'Perumahan Indah Blok C',
            'jenis_zakat' => 'Zakat Profesi',
            'keterangan' => 'Donatur rutin bulanan',
        ];

        $response = $this->from(route('muzakki.create'))->post(route('muzakki.store'), $formData);

        $response->assertStatus(302);
        $response->assertRedirect(route('muzakki.create'));
        $response->assertSessionHasErrors('nik');

        // Verifikasi Session Flashed Old Input
        $response->assertSessionHasInput('nama', 'Drs. H. Bambang Pamungkas');
        $response->assertSessionHasInput('nik', '999');
        $response->assertSessionHasInput('npwz', 'NPWZ-998877');
        $response->assertSessionHasInput('npwp', '09.123.456.7-123.000');
        $response->assertSessionHasInput('nip', '198001012005011001');
        $response->assertSessionHasInput('pekerjaan', 'Dosen Universitas');
        $response->assertSessionHasInput('jenis_kelamin', 'L');
        $response->assertSessionHasInput('tempat_lahir', 'Cilacap');
        $response->assertSessionHasInput('handphone', '081234567890');
        $response->assertSessionHasInput('email', 'bambang@kampus.ac.id');
        $response->assertSessionHasInput('alamat', 'Jl. Jend. Sudirman No. 45');
        $response->assertSessionHasInput('alamat_kantor', 'Kampus A Lt. 2');
        $response->assertSessionHasInput('alamat_korespondensi', 'Perumahan Indah Blok C');
        $response->assertSessionHasInput('keterangan', 'Donatur rutin bulanan');

        // Verifikasi View Hydration pada create blade saat membuka halaman dengan session data
        $pageResponse = $this->get(route('muzakki.create'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('value="Drs. H. Bambang Pamungkas"', false);
        $pageResponse->assertSee('value="999"', false);
        $pageResponse->assertSee('value="Dosen Universitas"', false);
        $pageResponse->assertSee('value="081234567890"', false);
        $pageResponse->assertSee('bambang@kampus.ac.id');
    }

    /*
    |--------------------------------------------------------------------------
    | 7. Rate Limiting pada Login Route (Point 2 Security)
    |--------------------------------------------------------------------------
    */

    public function test_login_rate_limiting_triggers_too_many_requests_on_seventh_attempt(): void
    {
        \Illuminate\Support\Facades\Auth::logout();

        // 6 Percobaan berturut-turut dengan kredensial salah diizinkan (HTTP 302 redirect)
        for ($i = 1; $i <= 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'unauthorized_attacker@baznas.id',
                'password' => 'wrong_password_' . $i,
            ]);
            $response->assertStatus(302);
            $response->assertSessionHasErrors('email');
        }

        // Percobaan ke-7 dalam rentang 1 menit harus diblokir dengan HTTP 429 Too Many Requests
        $responseSeventh = $this->post('/login', [
            'email' => 'unauthorized_attacker@baznas.id',
            'password' => 'wrong_password_7',
        ]);
        $responseSeventh->assertStatus(429);
    }
}
