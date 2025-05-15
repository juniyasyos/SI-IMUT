<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory;

    // SoftDeletes jika kamu menggunakan soft delete
    use SoftDeletes;

    protected $fillable = ['name', 'guard_name'];
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];

    // Cek apakah role ini adalah super_admin
    public function isSuperAdmin()
    {
        return $this->name === 'super_admin';
    }

    // Cegah perubahan pada role super_admin
    public static function boot()
    {
        parent::boot();

        // Cegah update jika role adalah super_admin
        static::updating(function ($role) {
            if ($role->isSuperAdmin()) {
                throw new \Exception('Role super_admin tidak dapat diubah.');
            }
        });

        // Cegah penghapusan jika role adalah super_admin
        static::deleting(function ($role) {
            if ($role->isSuperAdmin()) {
                throw new \Exception('Role super_admin tidak dapat dihapus.');
            }
        });
    }
}
