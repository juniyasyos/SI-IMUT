<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImutCategory extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string|null
     */
    protected $table = 'imut_kategori';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['category_name', 'scope', 'short_name', 'description'];

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
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * imutData func relation to ImutData model.
     *
     * @return void
     */
    public function imutData():HasMany
    {
        return $this->hasMany(ImutData::class, 'imut_kategori_id');
    }
}
