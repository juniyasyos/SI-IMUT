<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\ImutStandard;
use App\Models\ImutBenchmarking;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImutProfile extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'imut_profil';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'imut_data_id',
        'version',
        'rationale',
        'quality_dimension',
        'objective',
        'operational_definition',
        'indicator_type',
        'numerator_formula',
        'denominator_formula',
        'inclusion_criteria',
        'exclusion_criteria',
        'data_source',
        'data_collection_frequency',
        'analysis_plan',
        'target_operator',
        'target_value',
        'analysis_period_type',
        'analysis_period_value',
        'data_collection_method',
        'sampling_method',
        'data_collection_tool',
        'responsible_person',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateSlug($model->version);
            }
        });
    }

    /**
     * Generate a unique slug based on the given string.
     * 
     * @param string $source
     * @return string
     */
    public function generateSlug(string $source): string
    {
        $slugBase = Str::slug($source);
        $uuid = Str::uuid()->toString(); 

        $slug = "{$slugBase}-{$uuid}";

        return $slug;
    }


    public function getRouteKeyName()
    {
        return 'slug';
    }

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
            'target_value' => 'integer',
            'analysis_period_value' => 'integer',
        ];
    }

    /**
     * Get the options for activity log.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('imut_profile')
            ->setDescriptionForEvent(fn(string $eventName) => "Profil IMUT telah {$eventName}");
    }

    /**
     * Scope a query to only include profil with a certain indicator type.
     */
    public function scopeOfIndicatorType($query, string $type)
    {
        return $query->where('indicator_type', $type);
    }

    /**
     * Accessor for full indicator type label.
     */
    public function getIndicatorTypeLabelAttribute(): string
    {
        return match ($this->indicator_type) {
            'process' => 'Proses',
            'output' => 'Hasil (Output)',
            'outcome' => 'Dampak (Outcome)',
            default => 'Tidak diketahui',
        };
    }

    /**
     * Get the related ImutData.
     */
    public function imutData()
    {
        return $this->belongsTo(ImutData::class);
    }

    /**
     * Get the related Imut Bencmarking.
     *
     * @return HasMany
     */
    public function benchmarkings(): HasMany
    {
        return $this->hasMany(related: ImutBenchmarking::class);
    }

    /**
     * Get the related ImutStandard.
     *
     * @return HasMany
     */
    public function imutStandards()
    {
        return $this->hasMany(ImutStandard::class);
    }

    public function getAvgStandardValue()
    {
        return $this->imutStandards()->avg('value');
    }
}
