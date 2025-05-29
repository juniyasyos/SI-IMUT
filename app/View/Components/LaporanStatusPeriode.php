<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Carbon;

class LaporanStatusPeriode extends Component
{
    public $periode;
    public $current;

    public function __construct($periode, $current)
    {
        $this->periode = $periode;
        $this->current = $current;
    }

    public function render()
    {
        return view('components.laporan-status-periode');
    }
}
