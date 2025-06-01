<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ImutCategory extends Model
{
    use SoftDeletes, LogsActivity, HasFactory;

    /**
     * Table terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'imut_kategori';

    /**
     * Atribut yang bisa diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_name',
        'scope',
        'short_name',
        'description',
        'is_use_global',
        'is_benchmark_category',
    ];

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
     * Relasi ke model ImutData.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function imutData(): HasMany
    {
        return $this->hasMany(ImutData::class, 'imut_kategori_id');
    }

    /**
     * Mendapatkan pengaturan log aktivitas untuk model ini.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }
}
