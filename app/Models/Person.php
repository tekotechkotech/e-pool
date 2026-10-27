<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $table = 'persons';

    protected $fillable = [
        'nik',
        'nomor_kk',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'telepon',
        'handphone',
        'email',
        'alamat',
        'id_provinsi',
        'id_kabupaten',
        'id_kecamatan',
        'id_kelurahan',
        'kode_pos',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
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

    public function muzakki(): HasOne
    {
        return $this->hasOne(Muzakki::class);
    }

    public function mustahik(): HasOne
    {
        return $this->hasOne(Mustahik::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
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

    public function pengurus(): HasOne
    {
        return $this->hasOne(Pengurus::class)->where('is_active', true)->latestOfMany();
    }

    public function penguruses(): HasMany
    {
        return $this->hasMany(Pengurus::class);
    }
}
