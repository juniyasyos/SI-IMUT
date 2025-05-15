<?php

namespace App\Models;

use App\Models\User;
use App\Models\UnitKerja;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserUnitKerja extends Model
{
    protected $table = 'user_unit_kerja';

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'unit_kerja_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * user realation table
     *
     * @return void
     */
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * unitKerja relation Table
     *
     * @return void
     */
    public function unitKerja():BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }
}
