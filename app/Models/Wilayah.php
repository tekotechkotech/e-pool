<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    protected $primaryKey = 'id_wilayah';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_wilayah',
        'nama',
        'kode_pos',
    ];

    public function getTipeAttribute(): string
    {
        return match (strlen($this->id_wilayah)) {
            2 => 'provinsi',
            5 => 'kabupaten',
            8 => 'kecamatan',
            13 => 'kelurahan',
            default => 'unknown',
        };
    }

    public function provinsi()
    {
        return self::where('id_wilayah', substr($this->id_wilayah, 0, 2))->first();
    }

    public function kabupaten()
    {
        return self::where('id_wilayah', substr($this->id_wilayah, 0, 5))->first();
    }

    public function kecamatan()
    {
        return self::where('id_wilayah', substr($this->id_wilayah, 0, 8))->first();
    }

    public function kelurahan()
    {
        return self::where('id_wilayah', $this->id_wilayah)->first();
    }

    public static function provinsis()
    {
        return self::whereRaw('LENGTH(id_wilayah) = 2');
    }

    public static function kabupatens($idProvinsi)
    {
        return self::whereRaw('LENGTH(id_wilayah) = 5')
            ->where('id_wilayah', 'like', $idProvinsi . '%');
    }

    public static function kecamatans($idKabupaten)
    {
        return self::whereRaw('LENGTH(id_wilayah) = 8')
            ->where('id_wilayah', 'like', $idKabupaten . '%');
    }

    public static function kelurahans($idKecamatan)
    {
        return self::whereRaw('LENGTH(id_wilayah) = 13')
            ->where('id_wilayah', 'like', $idKecamatan . '%');
    }
}