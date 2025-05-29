<?php

namespace App\Models;

use App\Models\ImutProfile;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ImutPenilaian extends Model
{
    use SoftDeletes, LogsActivity;

    /** @use HasFactory<\Database\Factories\ImutPenilaianFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'imut_profil_id',
        'laporan_unit_kerja_id',
        'analysis',
        'recommendations',
        'document_upload',
        'numerator_value',
        'denominator_value',
    ];

    /**
     * The attributes that are guarded.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the options for logging activity.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }


    /**
     * Get the profile that owns the ImutPenilaian
     *
     * @return void
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(ImutProfile::class, 'imut_profil_id');
    }

    /**
     * Get the unit kerja that owns the ImutPenilaian
     *
     * @return void
     */
    public function laporanUnitKerja(): BelongsTo
    {
        return $this->belongsTo(LaporanUnitKerja::class);
    }

    /**
     * Get the unit kerja that owns the ImutPenilaian
     *
     * @return void
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function profileById($profileId): HasOne
    {
        return $this->hasOne(ImutProfile::class)->where('id', $profileId);
    }

    public function latestProfile(): HasOne
    {
        return $this->hasOne(ImutProfile::class)->latestOfMany();
    }

}
