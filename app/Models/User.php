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

    public function pengajuanBantuans(): HasMany
    {
        return $this->hasMany(PengajuanBantuan::class, 'created_by');
    }

    public function persetujuans(): HasMany
    {
        return $this->hasMany(PengajuanPersetujuan::class, 'approved_by');
    }

    public function penyalurans(): HasMany
    {
        return $this->hasMany(PengajuanPenyaluran::class, 'created_by');
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

    public function canAccessPengajuan(PengajuanBantuan $pengajuan): bool
    {
        if ($this->isSuperuser() || $this->hasBaznasAppointment()) {
            return true;
        }

        return $this->isUpzRole()
            && $pengajuan->jenis_permohonan === 'Lembaga'
            && $pengajuan->lembaga_id !== null
            && $this->upzLembagaId() === (int) $pengajuan->lembaga_id;
    }

    public function canPerformPengajuanAction(string $action, ?PengajuanBantuan $pengajuan = null): bool
    {
        if ($this->isSuperuser()) {
            return true;
        }

        $roles = $this->activeJabatanNames();
        $baznas = $this->isBaznasRole();
        $upz = $this->isUpzRole();
        $isUpzApplication = $pengajuan?->jenis_permohonan === 'Lembaga';

        $allowed = match ($action) {
            'view-umum' => $baznas,
            'view-upz' => $baznas || $upz,
            'people-search' => $baznas,
            'create-umum' => in_array('Admin BAZNAS', $roles, true),
            'create-upz' => in_array('Admin BAZNAS', $roles, true) || in_array('Sekretaris', $roles, true),
            'edit' => in_array('Admin BAZNAS', $roles, true) || ($isUpzApplication && in_array('Sekretaris', $roles, true)),
            'manage' => in_array('Admin BAZNAS', $roles, true) || ($isUpzApplication && in_array('Sekretaris', $roles, true)),
            'approve-upz' => $isUpzApplication && in_array('Ketua', $roles, true),
            'approve-umum' => !$isUpzApplication && in_array('Pimpinan BAZNAS', $roles, true),
            'approve' => ($isUpzApplication && in_array('Ketua', $roles, true)) || in_array('Pimpinan BAZNAS', $roles, true),
            'approve-baznas' => in_array('Pimpinan BAZNAS', $roles, true),
            'transfer' => ($isUpzApplication && in_array('Pimpinan BAZNAS', $roles, true))
                || (!$isUpzApplication && in_array('Staff Penyaluran', $roles, true)),
            'beneficiary-payout' => $isUpzApplication && in_array('Bendahara', $roles, true),
            'lpj' => (!$isUpzApplication && (in_array('Staff Penyaluran', $roles, true) || in_array('Admin BAZNAS', $roles, true)))
                || ($isUpzApplication && (in_array('Sekretaris', $roles, true) || in_array('Bendahara', $roles, true))),
            'view' => $baznas || $upz,
            default => false,
        };

        return $allowed && (!$pengajuan || $this->canAccessPengajuan($pengajuan));
    }

    public function getNameAttribute(): string
    {
        return $this->person?->nama ?? explode('@', $this->email)[0] ?? 'User';
    }
}

