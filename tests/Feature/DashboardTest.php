<?php

use App\Models\User;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('dashboard page returns http 200 ok for authenticated user', function () {
    $person = Person::create([
        'nik' => '3301010000000001',
        'nama' => 'Admin BAZNAS',
    ]);

    $user = User::create([
        'email' => 'admin@baznas.go.id',
        'person_id' => $person->id,
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

test('dashboard page redirects unauthenticated user to login', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

