<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PimpinanLayout extends Component
{
    public function render(): View|Closure|string
    {
        return view('layouts.pimpinan.app');
    }
}
