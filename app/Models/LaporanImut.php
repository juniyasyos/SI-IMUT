<?php

namespace App\Models;

use App\Support\CacheKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LaporanImut extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public const string STATUS_PROCESS = 'process';

    public const string STATUS_COMPLETE = 'complete';

    public const string STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'name',
        'status',
        'assessment_period_start',
        'assessment_period_end',
        'created_by',
    ];

    protected $guarded = ['id'];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'assessment_period_start' => 'date',
        'assessment_period_end' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($laporan) {
            if (empty($laporan->slug)) {
                $laporan->slug = Str::slug($laporan->name ?? $laporan->id.'-'.now()->timestamp);
            }
        });

        static::saved(fn ($laporan) => $laporan->clearCache());
        static::deleted(fn ($laporan) => $laporan->clearCache());
    }

    public function clearCache()
    {
        Cache::forget(CacheKey::imutLaporans());
        Cache::forget(CacheKey::latestLaporan());
        Cache::forget(CacheKey::dashboardSiimutChartData($this->id));
        Cache::forget(CacheKey::dashboardSiimutAllData($this->id));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    public function unitKerjas()
    {
        return $this->belongsToMany(UnitKerja::class, 'laporan_unit_kerjas', 'laporan_imut_id', 'unit_kerja_id')
            ->withTimestamps();
    }

    public function imutPenilaians()
    {
        return $this->hasManyThrough(
            ImutPenilaian::class,
            LaporanUnitKerja::class,
            'laporan_imut_id',
            'laporan_unit_kerja_id',
            'id',
            'id'
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function laporanUnitKerjas()
    {
        return $this->hasMany(LaporanUnitKerja::class);
    }
}
