<?php

use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Wilayah::create([
        'id_wilayah' => '33',
        'nama' => 'JAWA TENGAH',
    ]);

    Wilayah::create([
        'id_wilayah' => '33.01',
        'nama' => 'KABUPATEN CILACAP',
    ]);

    Wilayah::create([
        'id_wilayah' => '33.01.01',
        'nama' => 'KEDUNGREJA',
    ]);

    Wilayah::create([
        'id_wilayah' => '33.01.01.2001',
        'nama' => 'TAMBAKREJA',
        'kode_pos' => '53263',
    ]);
});

test('model wilayah hierarchical accessor and navigation work correctly', function () {
    $desa = Wilayah::find('33.01.01.2001');

    expect($desa)->not->toBeNull();
    expect($desa->tipe)->toBe('kelurahan');
    expect($desa->kecamatan()->nama)->toBe('KEDUNGREJA');
    expect($desa->kabupaten()->nama)->toBe('KABUPATEN CILACAP');
    expect($desa->provinsi()->nama)->toBe('JAWA TENGAH');

    expect(Wilayah::find('33.01.01')->tipe)->toBe('kecamatan');
    expect(Wilayah::find('33.01')->tipe)->toBe('kabupaten');
    expect(Wilayah::find('33')->tipe)->toBe('provinsi');
});

test('model wilayah static queries return filtered records', function () {
    expect(Wilayah::provinsis()->count())->toBe(1);
    expect(Wilayah::kabupatens('33')->count())->toBe(1);
    expect(Wilayah::kecamatans('33.01')->count())->toBe(1);
    expect(Wilayah::kelurahans('33.01.01')->count())->toBe(1);
});

test('api wilayah endpoints return 200 and expected json structure', function () {
    $this->getJson('/api/wilayah/provinsi')
        ->assertOk()
        ->assertJsonFragment([
            'id_wilayah' => '33',
            'nama' => 'JAWA TENGAH',
        ]);

    $this->getJson('/api/wilayah/kabupaten/33')
        ->assertOk()
        ->assertJsonFragment([
            'id_wilayah' => '33.01',
            'nama' => 'KABUPATEN CILACAP',
        ]);

    $this->getJson('/api/wilayah/kecamatan/33.01')
        ->assertOk()
        ->assertJsonFragment([
            'id_wilayah' => '33.01.01',
            'nama' => 'KEDUNGREJA',
        ]);

    $this->getJson('/api/wilayah/kelurahan/33.01.01')
        ->assertOk()
        ->assertJsonFragment([
            'id_wilayah' => '33.01.01.2001',
            'nama' => 'TAMBAKREJA',
            'kode_pos' => '53263',
        ]);

    $this->getJson('/api/wilayah/kode-pos?id=33.01.01.2001')
        ->assertOk()
        ->assertExactJson([
            'kelurahan_id' => '33.01.01.2001',
            'kode_pos' => '53263',
        ]);
});

test('api wilayah query parameters and edge cases', function () {
    $this->getJson('/api/wilayah/kabupaten?provinsi_id=33')
        ->assertOk()
        ->assertJsonCount(1);

    $this->getJson('/api/wilayah/kecamatan?kabupaten_id=33.01')
        ->assertOk()
        ->assertJsonCount(1);

    $this->getJson('/api/wilayah/kelurahan?kecamatan_id=33.01.01')
        ->assertOk()
        ->assertJsonCount(1);

    $this->getJson('/api/wilayah/kabupaten/99')
        ->assertOk()
        ->assertExactJson([]);

    $this->getJson('/api/wilayah/kode-pos?id=99.99.99.9999')
        ->assertOk()
        ->assertExactJson([
            'kelurahan_id' => '99.99.99.9999',
            'kode_pos' => '',
        ]);
});
