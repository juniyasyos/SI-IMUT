<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ImutDataUnitKerja extends Pivot
{
    protected $table = 'imut_data_unit_kerja';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unit_kerja_id',
        'imut_data_id',
        'assigned_by',
        'assigned_at',
    ];

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
     * user relation table
     *
     * @return void
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * unitKerja relation Table
     *
     * @return void
     */
    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    /**
     * imutData relation Table
     *
     * @return void
     */
    public function imutData(): BelongsTo
    {
        return $this->belongsTo(ImutData::class);
    }
}