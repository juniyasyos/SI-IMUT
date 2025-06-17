<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class LaporanImut extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laporanimut';
    }
}
