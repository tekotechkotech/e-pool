<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EndpointVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $person = Person::create([
            'nik' => '3301010000000001',
            'nama' => 'Admin QA',
        ]);

        $this->user = User::create([
            'email' => 'qa@baznas.go.id',
            'person_id' => $person->id,
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->user);
    }

    #[DataProvider('endpointProvider')]
    public function test_endpoint_returns_200_ok(string $endpoint): void
    {
        $response = $this->get($endpoint);

        $response->assertStatus(200);
    }

    public static function endpointProvider(): array
    {
        return [
            'dashboard' => ['/dashboard'],
            'muzakki' => ['/muzakki'],
            'pengumpulan/upz' => ['/pengumpulan/upz'],
            'pengumpulan/kas-masuk' => ['/pengumpulan/kas-masuk'],
            'simba/muzakki' => ['/simba/muzakki'],
            'simba/kas-masuk' => ['/simba/kas-masuk'],
            'laporan/buku-kas' => ['/laporan/buku-kas'],
            'laporan/realisasi-dana' => ['/laporan/realisasi-dana'],
            'manajemen/upz' => ['/manajemen/upz'],
            'master/danas' => ['/master/danas'],
        ];
    }
}
