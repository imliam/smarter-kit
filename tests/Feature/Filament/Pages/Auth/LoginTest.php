<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;

use function Pest\Livewire\livewire;

test('an unauthenticated user can access the login page', function (): void {
    $this->get(Filament::getLoginUrl())
        ->assertOk();
});

test('an unauthenticated user can not access the admin panel', function (): void {
    $this->get('admin')
        ->assertRedirect(Filament::getLoginUrl());
});

test('an unauthenticated user can login', function (): void {
    User::factory()->admin()->create([
        'email' => DatabaseSeeder::DEFAULT_EMAIL,
        'password' => Hash::make(DatabaseSeeder::DEFAULT_PASSWORD),
    ]);

    livewire(Login::class)
        ->fillForm([
            'email' => DatabaseSeeder::DEFAULT_EMAIL,
            'password' => DatabaseSeeder::DEFAULT_PASSWORD,
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();
});

test('an authenticated user can access the admin panel', function (): void {
    $this->login(User::factory()->admin()->create());

    $this->get('admin')
        ->assertOk();
});

test('an authenticated user can logout', function (): void {
    $this->login(User::factory()->admin()->create());

    $this->assertAuthenticated();

    $this->post(Filament::getLogoutUrl())
        ->assertRedirect(Filament::getLoginUrl());
});
