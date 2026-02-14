<?php

namespace App\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.marketing')]
class FreeAnalysis extends Component
{
    public function render(): View
    {
        return view('livewire.pages.free-analysis');
    }
}
