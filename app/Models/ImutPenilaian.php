<?php

namespace App\Models;

use App\Support\CacheKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ImutPenilaian extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ImutPenilaianFactory> */
    use HasFactory, InteractsWithMedia, LogsActivity;

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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->useDisk('public');
    }

    public function clearCache()
    {
        $laporanUnitKerja = $this->laporanUnitKerja;

        if ($laporanUnitKerja) {
            $laporanId = $laporanUnitKerja->laporan_imut_id;
            $unitKerjaId = $laporanUnitKerja->unit_kerja_id;

            Cache::forget(CacheKey::laporanUnitDetail($laporanId, $unitKerjaId));

            Cache::forget(CacheKey::dashboardSiimutAllData($laporanId));
            Cache::forget(CacheKey::dashboardSiimutAllChartData());
        }

        Cache::forget(CacheKey::imutLaporans());
        Cache::forget(CacheKey::latestLaporan());
    }

    protected static function booted()
    {
        static::saved(fn ($penilaian) => $penilaian->clearCache());
        static::deleted(fn ($penilaian) => $penilaian->clearCache());
    }

    /**
     * Get the options for logging activity.
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