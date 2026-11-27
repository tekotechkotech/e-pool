<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['person_id', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    // Shortcut untuk langsung mengambil data pengurus dari User
    public function pengurus(): ?Pengurus
    {
        return $this->person?->pengurus;
    }

    public function activePenguruses()
    {
        return $this->person?->penguruses()
            ->where('is_active', true)
            ->whereHas('pengurusJabatan', fn ($query) => $query->where('is_active', true))
            ->with(['pengurusJabatan', 'lembaga']);
    }

    private function activeAppointment(): ?Pengurus
    {
        $appointments = $this->activePenguruses()?->get() ?? collect();

        return $appointments->count() === 1 ? $appointments->first() : null;
    }

    public function activeJabatanNames(): array
    {
        $appointment = $this->activeAppointment();

        return $appointment?->pengurusJabatan?->nama ? [$appointment->pengurusJabatan->nama] : [];
    }

    public function activePengurus(): ?Pengurus
    {
        return $this->activeAppointment();
    }

    public function hasJabatan(string|array $names): bool
    {
        return count(array_intersect((array) $names, $this->activeJabatanNames())) > 0;
    }

    public function isSuperuser(): bool
    {
        return $this->hasJabatan('Superuser');
    }

    public function isBaznasRole(): bool
    {
        $appointment = $this->activeAppointment();

        return $appointment?->jenis_pengurus === 'Baznas'
            && $appointment->lembaga_id === null
            && $appointment->pengurusJabatan?->tingkat === 'baznas'
            && in_array($appointment->pengurusJabatan->nama, ['Superuser', 'Pimpinan BAZNAS', 'Admin BAZNAS', 'Staff Penyaluran'], true);
    }

    public function hasBaznasAppointment(): bool
    {
        $appointment = $this->activeAppointment();

        return $appointment?->jenis_pengurus === 'Baznas'
            && $appointment->lembaga_id === null
            && $appointment->pengurusJabatan?->tingkat === 'baznas';
    }

    public function isUpzRole(): bool
    {
        $appointment = $this->activeAppointment();

        return $appointment?->jenis_pengurus === 'Lembaga'
            && $appointment->lembaga_id !== null
            && $appointment->pengurusJabatan?->tingkat === 'lembaga'
            && $appointment->lembaga?->is_upz === true
            && $appointment->lembaga?->is_active === true
            && in_array($appointment->pengurusJabatan->nama, ['Ketua', 'Sekretaris', 'Bendahara', 'Anggota'], true);
    }

    public function upzLembagaId(): ?int
    {
        return $this->isUpzRole() ? (int) $this->activeAppointment()->lembaga_id : null;
    }

    public function getNameAttribute(): string
    {
        return $this->person?->nama ?? explode('@', $this->email)[0] ?? 'User';
    }
}

