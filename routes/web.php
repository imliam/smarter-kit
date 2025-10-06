<?php

declare(strict_types=1);

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', fn (): View|\Illuminate\Contracts\View\Factory => view('welcome'))->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function (): void {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
