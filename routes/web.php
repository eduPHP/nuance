<?php

use App\Livewire\Pages\FreeAnalysis;
use App\Livewire\Pages\Home;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Home::class)->name('home');

Route::livewire('free-analysis', FreeAnalysis::class)->name('free-analysis');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
