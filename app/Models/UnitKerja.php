<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class UnitKerja
 *
 * @property int $id
 * @property string $unit_name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ImutData[] $imutData
 * @property-read Folder|null $folder
 * @property-read \App\Models\LaporanImut|null $laporanImut
 */
class UnitKerja extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'unit_kerja';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unit_name',
        'description',
    ];

    /**
     * The attributes that are hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }

    /**
     * Get related users with pivot.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_unit_kerja', 'unit_kerja_id', 'user_id')->withTimestamps();
    }

    /**
     * Get related imut data with pivot.
     */
    public function imutData(): BelongsToMany
    {
        return $this->belongsToMany(ImutData::class, 'imut_data_unit_kerja')
            ->using(ImutDataUnitKerja::class)
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    /**
     * Get related laporan imut.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function laporanImut()
    {
        return $this->belongsTo(LaporanImut::class, 'laporan_imut_id');
    }
}