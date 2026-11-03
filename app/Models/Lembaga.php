<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lembaga extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_upz',
        'kode_simba',
        'nama',
        'ketua_nama',
        'ketua_nik',
        'jenis',
        'jumlah_anggota',
        'keterangan',
        'alamat',
        'telepon',
        'handphone',
        'email',
        'id_provinsi',
        'id_kabupaten',
        'id_kecamatan',
        'id_kelurahan',
        'kode_pos',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_upz' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getAlamatLengkapAttribute(): string
    {
        $parts = [];
        if ($this->alamat) $parts[] = $this->alamat;
        if ($this->kelurahan) $parts[] = 'Desa/Kel. ' . $this->kelurahan->nama;
        if ($this->kecamatan) $parts[] = 'Kec. ' . $this->kecamatan->nama;
        if ($this->kabupaten) $parts[] = $this->kabupaten->nama;
        if ($this->provinsi) $parts[] = 'Prov. ' . $this->provinsi->nama;
        if ($this->kode_pos) $parts[] = $this->kode_pos;
        
        return count($parts) > 0 ? implode(', ', $parts) : ($this->alamat ?? '-');
    }

    public function penguruses()
    {
        return $this->hasMany(Pengurus::class);
    }

    public function ketua()
    {
        return $this->hasMany(Pengurus::class)->whereHas('pengurusJabatan', function($q) {
            $q->where('nama', 'Ketua UPZ');
        });
    }

    public function mustahiks()
    {
        return $this->belongsToMany(Mustahik::class, 'lembaga_mustahiks')->withTimestamps();
    }

    public function provinsi()
    {
        return $this->belongsTo(Wilayah::class, 'id_provinsi', 'id_wilayah');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Wilayah::class, 'id_kabupaten', 'id_wilayah');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Wilayah::class, 'id_kecamatan', 'id_wilayah');
    }

    public function kelurahan()
    {
        return $this->belongsTo(Wilayah::class, 'id_kelurahan', 'id_wilayah');
    }

    public function pengajuanBantuans()
    {
        if (!class_exists(PengajuanBantuan::class)) {
            return $this->hasMany(self::class, 'id', 'id')->whereRaw('1 = 0');
        }
        return $this->hasMany(PengajuanBantuan::class, 'lembaga_id');
    }

    public function setorans()
    {
        return $this->hasMany(SetoranUpz::class, 'lembaga_id');
    }

    public function muzakkis(): HasMany
    {
        return $this->hasMany(Muzakki::class, 'lembaga_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
