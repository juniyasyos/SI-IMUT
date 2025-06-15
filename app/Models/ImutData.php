<?php

namespace App\Models;

use App\Support\CacheKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ImutData extends Model
{
    /** @use HasFactory<\Database\Factories\ImutDataFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['title', 'imut_kategori_id', 'slug', 'status', 'created_by'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

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
            'status' => 'boolean',
        ];
    }

    public function clearCache()
    {
        $laporanUnitKerja = $this->laporanUnitKerja;

        if ($laporanUnitKerja) {
            $laporanId = $laporanUnitKerja->laporan_imut_id;
            $unitKerjaId = $laporanUnitKerja->unit_kerja_id;

            Cache::forget(CacheKey::laporanUnitDetail($laporanId, $unitKerjaId));

            Cache::forget(CacheKey::dashboardSiimutAllData($laporanId));
            Cache::forget(CacheKey::dashboardSiimutChartData($laporanId));
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
     * func tion to get the category of the indicator
     *
     * @return void
     */
    public function categories(): BelongsTo
    {
        return $this->belongsTo(ImutCategory::class, 'imut_kategori_id');
    }

    /**
     * function to get the benchmarking of the indicator
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(ImutProfile::class);
    }

    /**
     * function to get the benchmarking of the indicator
     */
    public function unitKerja(): BelongsToMany
    {
        return $this->belongsToMany(UnitKerja::class, 'imut_data_unit_kerja')
            ->using(ImutDataUnitKerja::class)
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Get the related Imut Bencmarking.
     */
    public function benchmarkings(): HasMany
    {
        return $this->hasMany(related: ImutBenchmarking::class);
    }

    // Di model ImutData
    public function latestProfile()
    {
        return $this->hasOne(ImutProfile::class)->latestOfMany('version');
    }

    public function profileById($profileId): HasOne
    {
        return $this->hasOne(ImutProfile::class)->where('id', $profileId);
    }
}
