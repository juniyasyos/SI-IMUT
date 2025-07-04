<?php

namespace App\Models;

use App\Support\CacheKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\{User, UnitKerja, ImutPenilaian, LaporanUnitKerja};

/**
 * Class LaporanImut
 *
 * @property int $id
 * @property string $name
 * @property string $status
 * @property Carbon $assessment_period_start
 * @property Carbon $assessment_period_end
 * @property int $created_by
 * @property-read User $createdBy
 * @property-read \Illuminate\Support\Collection|UnitKerja[] $unitKerjas
 * @property-read \Illuminate\Support\Collection|LaporanUnitKerja[] $laporanUnitKerjas
 * @property-read \Illuminate\Support\Collection|ImutPenilaian[] $imutPenilaians
 */
class LaporanImut extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /** @var string Status sedang berlangsung */
    public const STATUS_PROCESS = 'process';

    /** @var string Status selesai */
    public const STATUS_COMPLETE = 'complete';

    /** @var string Status dibatalkan */
    public const STATUS_COMINGSOON = 'coming_soon';

    /**
     * Mass assignable attributes
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
        'assessment_period_start',
        'assessment_period_end',
        'created_by',
    ];

    /**
     * Guarded attributes
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * Hidden attributes in serialization
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Attribute casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deleted_at' => 'datetime',
        'assessment_period_start' => 'date',
        'assessment_period_end' => 'date',
    ];

    /**
     * Boot model events
     */
    protected static function booted(): void
    {
        static::creating(function (self $laporan): void {
            if (empty($laporan->slug)) {
                $laporan->slug = Str::slug($laporan->name ?? $laporan->id . '-' . now()->timestamp);
            }
        });

        static::saved(fn(self $laporan) => $laporan->clearCache());
        static::deleted(fn(self $laporan) => $laporan->clearCache());
    }

    /**
     * Clear related cache keys
     */
    public function clearCache(): void
    {
        Cache::forget(CacheKey::imutLaporans());
        Cache::forget(CacheKey::latestLaporan());
        Cache::forget(CacheKey::dashboardSiimutChartData());
        Cache::forget(CacheKey::dashboardSiimutAllData($this->id));
        Cache::forget(CacheKey::recentLaporanList());

        Cache::forget(CacheKey::getPenilaianStats($this->id, false));
        Cache::forget(CacheKey::getPenilaianStats($this->id, true));
    }

    /**
     * Setup for activity log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Relasi ke unit kerja
     */
    public function unitKerjas(): BelongsToMany
    {
        return $this->belongsToMany(UnitKerja::class, 'laporan_unit_kerjas', 'laporan_imut_id', 'unit_kerja_id')
            ->withTimestamps();
    }

    /**
     * Relasi ke penilaian melalui laporan unit kerja
     */
    public function imutPenilaians(): HasManyThrough
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

    /**
     * Relasi ke user pembuat laporan
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke laporan unit kerja
     */
    public function laporanUnitKerjas(): HasMany
    {
        return $this->hasMany(LaporanUnitKerja::class);
    }

    /**
     * Accessor otomatis update status menjadi sesuai dengan kasus real
     */
    public function getStatusAttribute(string $value): string
    {
        $today = now()->startOfDay();
        $start = $this->assessment_period_start->startOfDay();
        $end = $this->assessment_period_end->startOfDay();

        $newStatus = match (true) {
            $today->lt($start) => self::STATUS_COMINGSOON,
            $today->lte($end) => self::STATUS_PROCESS,
            default => self::STATUS_COMPLETE,
        };

        if ($value !== $newStatus) {
            $this->updateQuietly(['status' => $newStatus]);
        }

        return $newStatus;
    }
}
