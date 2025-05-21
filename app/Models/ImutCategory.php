<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImutCategory extends Model
{
    use HasFactory;

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
        'is_imut_bencmarking',
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi.
     *
     * @var array<int, string>
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Relasi ke model ImutData.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function imutData(): HasMany
    {
        return $this->hasMany(ImutData::class, 'imut_kategori_id');
    }
}
