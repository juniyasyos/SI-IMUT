<?php

namespace App\Models;

use App\Models\ImutProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImutStandard extends Model
{
    /** @use HasFactory<\Database\Factories\ImutStandardFactory> */
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $table = 'imut_standar';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'imut_profile_id',
        'value',
        'description',
        'start_period',
        'end_period',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    public function profil()
    {
        return $this->belongsTo(ImutProfile::class, 'imut_profile_id');
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
    protected $hidden = ['created_at', 'updated_at'];
}
