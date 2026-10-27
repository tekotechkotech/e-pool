<?php

use App\Models\User;
use App\Models\Person;
use App\Models\Pengurus;
use App\Models\PengurusJabatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->jabatanSuperuser = PengurusJabatan::create([
        'kode' => 'JB-000',
        'nama' => 'Superuser',
        'tingkat' => 'baznas',
        'is_active' => true,
    ]);

    $this->personSuperuser = Person::create([
        'nik' => '3301010000000001',
        'nomor_kk' => '3301010000000000',
        'nama' => 'Super Administrator BAZNAS',
        'jenis_kelamin' => 'L',
        'tempat_lahir' => 'Cilacap',
        'tanggal_lahir' => '1980-01-01',
        'handphone' => '081111111111',
        'email' => 'superuser@baznas.id',
        'alamat' => 'Kantor BAZNAS',
        'id_provinsi' => '33',
        'id_kabupaten' => '3301',
    ]);

    $this->superuser = User::create([
        'email' => 'superuser@baznas.id',
        'person_id' => $this->personSuperuser->id,
        'password' => Hash::make('password'),
    ]);

    $this->pengurus = Pengurus::create([
        'jenis_pengurus' => 'Baznas',
        'person_id' => $this->personSuperuser->id,
        'pengurus_jabatan_id' => $this->jabatanSuperuser->id,
        'is_active' => true,
        'tanggal_mulai' => '2026-01-01',
    ]);
});

test('1. hash check password superuser@baznas.id bernilai true', function () {
    $user = User::where('email', 'superuser@baznas.id')->first();

    expect($user)->not->toBeNull();
    expect(Hash::check('password', $user->password))->toBeTrue();
});

test('2 & 3. simulasi http request login superuser berhasil, session terisi, tidak ditolak filter pengurus aktif', function () {
    $response = $this->post('/login', [
        'email' => 'superuser@baznas.id',
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($this->superuser);

    expect(session('active_jabatan'))->toBe('Superuser');
    expect(session('active_pengurus_id'))->toBe($this->pengurus->id);
});

test('filter pengurus aktif: menolak user jika tidak memiliki pengurus aktif', function () {
    $this->pengurus->update(['is_active' => false]);

    $response = $this->post('/login', [
        'email' => 'superuser@baznas.id',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'Akun Anda tidak terdaftar sebagai pengurus aktif.',
    ]);
    $this->assertGuest();
});

test('login gagal jika password salah', function () {
    $response = $this->post('/login', [
        'email' => 'superuser@baznas.id',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'Email atau password salah.',
    ]);
    $this->assertGuest();
});
