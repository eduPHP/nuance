<?php

namespace App\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.marketing')]
class Home extends Component
{
    public function render(): View
    {
        return view('livewire.pages.home');
    }
}
