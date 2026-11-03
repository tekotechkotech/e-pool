<?php

namespace Tests\Unit;

use App\Models\Lembaga;
use App\Models\Muzakki;
use App\Models\Person;
use App\Models\SetoranUpz;
use App\Models\Upz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetoranUpzVerificationAndRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_setoran_upz_has_verification_fields_and_relationship(): void
    {
        $verifierPerson = Person::create([
            'nama' => 'Verifier Staff',
            'nik' => '3301010101010001',
        ]);

        $verifier = User::create([
            'person_id' => $verifierPerson->id,
            'name' => 'Verifier Staff',
            'email' => 'verifier@example.com',
            'password' => bcrypt('password'),
        ]);

        $lembaga = Lembaga::create([
            'nama' => 'UPZ Masjid Jami',
            'is_upz' => true,
            'is_active' => true,
        ]);

        $now = now();
        $setoran = SetoranUpz::create([
            'nomor_setoran' => 'STR-202611-0001',
            'lembaga_id' => $lembaga->id,
            'tanggal_setor' => '2026-11-03',
            'periode' => 'November 2026',
            'status' => 'Terverifikasi',
            'verified_by' => $verifier->id,
            'verified_at' => $now,
            'catatan_verifikasi' => 'Data setoran valid dan cocok dengan rekening koran.',
        ]);

        $this->assertDatabaseHas('setoran_upzs', [
            'id' => $setoran->id,
            'verified_by' => $verifier->id,
            'catatan_verifikasi' => 'Data setoran valid dan cocok dengan rekening koran.',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $setoran->verified_at);
        $this->assertEquals($verifier->id, $setoran->verifiedBy->id);
        $this->assertEquals('Verifier Staff', $setoran->verifiedBy->person->nama);
    }

    public function test_lembaga_and_upz_have_muzakkis_relationship(): void
    {
        $lembaga = Lembaga::create([
            'nama' => 'UPZ Kantor Kemenag',
            'is_upz' => true,
            'is_active' => true,
        ]);

        $person = Person::create([
            'nama' => 'Ahmad Muzakki',
            'nik' => '3301020202020002',
        ]);

        $muzakki = Muzakki::create([
            'person_id' => $person->id,
            'lembaga_id' => $lembaga->id,
            'nrm' => 'NRM-001',
        ]);

        $this->assertTrue($lembaga->muzakkis->contains($muzakki));
        $this->assertEquals(1, $lembaga->muzakkis()->count());

        $upz = Upz::find($lembaga->id);
        $this->assertNotNull($upz);
        $this->assertTrue($upz->muzakkis->contains($muzakki));
        $this->assertEquals(1, $upz->muzakkis()->count());
    }
}
