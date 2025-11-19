<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PengajuanSurvey extends Model
{
    protected $table = 'pengajuan_surveys';

    protected $fillable = [
        'pengajuan_mustahik_id',
        'token',
        'tanggal_survey',
        'hasil_akhir',
        'catatan',
        'lampiran',
        'surveyor',
        'nohp_surveyor',
    ];

    public function hadKifayah(): HasOne
    {
        return $this->hasOne(PengajuanSurveyHadKifayah::class, 'pengajuan_survey_id');
    }

    public function asesmen(): HasOne
    {
        return $this->hasOne(PengajuanSurveyAsesmen::class, 'pengajuan_survey_id');
    }

    protected function casts(): array
    {
        return [
            'tanggal_survey' => 'date',
        ];
    }

    public static function rules(): array
    {
        return [
            'surveyor'       => 'required|string|max:255',
            'nohp_surveyor'  => 'required|string|max:50',
            'tanggal_survey' => 'required|date',
            'catatan'        => 'nullable|string',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $survey) {
            // Jika survey sudah memiliki hasil akhir atau status kelayakan terisi (disubmit),
            // surveyor, nohp_surveyor, dan tanggal_survey wajib ada.
            if (!empty($survey->hasil_akhir)) {
                if (empty(trim($survey->surveyor ?? ''))) {
                    throw new \InvalidArgumentException('Nama petugas surveyor wajib diisi.');
                }
                if (empty(trim($survey->nohp_surveyor ?? ''))) {
                    throw new \InvalidArgumentException('Nomor WhatsApp/HP surveyor wajib diisi.');
                }
                if (empty($survey->tanggal_survey)) {
                    throw new \InvalidArgumentException('Tanggal survey wajib diisi.');
                }
            }
        });
    }

    public function pengajuanMustahik(): BelongsTo
    {
        return $this->belongsTo(PengajuanMustahik::class, 'pengajuan_mustahik_id');
    }

    public function pengajuan()
    {
        return $this->pengajuanMustahik?->pengajuan();
    }

    public function lampirans(): MorphMany
    {
        return $this->morphMany(Lampiran::class, 'lampiranable');
    }

    public function lampiranSurvey(): MorphMany
    {
        return $this->morphMany(Lampiran::class, 'lampiranable')->where('category', 'survey');
    }
}
