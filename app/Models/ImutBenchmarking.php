<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class ImutBenchmarking
 *
 * @property int $id
 * @property int $imut_profile_id
 * @property int $region_type_id
 * @property string|null $region_name
 * @property int $year
 * @property int $month
 * @property float $benchmark_value
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ImutProfile $imutProfile
 * @property-read \App\Models\RegionType $regionType
 *
 * @mixin \Eloquent
 */
class ImutBenchmarking extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'imut_data_id',
        'region_type_id',
        'region_name',
        'year',
        'month',
        'benchmark_value',
    ];

    /**
     * The attributes that should be hidden for arrays and JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
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
     * Configure the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Get the related ImutProfile.
     */
    public function imutData(): BelongsTo
    {
        return $this->belongsTo(ImutData::class);
    }

    /**
     * Get the related RegionType.
     */
    public function regionType(): BelongsTo
    {
        return $this->belongsTo(RegionType::class);
    }
}
