<?php

namespace App\Models;

use App\Models\User;
use App\Models\LaporanImut;
use App\Models\ImutDataUnitKerja;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UnitKerja extends Model
{
    use SoftDeletes, LogsActivity;

    /** @use HasFactory<\Database\Factories\UnitKerjaFactory> */
    use HasFactory;

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
     * table
     *
     * @var string
     */
    protected $table = 'unit_kerja';

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
     * Relation to users with pivot table
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_unit_kerja', 'unit_kerja_id', 'user_id')->withTimestamps();
    }

    /**
     * Relation to imut data with pivot table
     *
     * @return void
     */
    public function imutData(): BelongsToMany
    {
        return $this->belongsToMany(ImutData::class, 'imut_data_unit_kerja')
            ->using(ImutDataUnitKerja::class)
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
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

    public function laporanImut()
    {
        return $this->belongsTo(LaporanImut::class, 'laporan_imut_id');
    }

}
