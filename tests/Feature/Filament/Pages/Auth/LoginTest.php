<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

test('an unauthenticated user can access the login page', function () {
    $this->get(Filament::getLoginUrl())
        ->assertOk();
});

test('an unauthenticated user can not access the admin panel', function () {
    $this->get('admin')
        ->assertRedirect(Filament::getLoginUrl());
});

test('an unauthenticated user can login', function () {
    User::factory()->admin()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    livewire(Login::class)
        ->fillForm([
            'email' => 'admin@example.com',
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();
});

test('an authenticated user can access the admin panel', function () {
    actingAs(User::factory()->admin()->create());
    $this->get('admin')
        ->assertOk();
});

test('an authenticated user can logout', function () {
    actingAs(User::factory()->admin()->create());
    $this->assertAuthenticated();

    $this->post(Filament::getLogoutUrl())
        ->assertRedirect(Filament::getLoginUrl());
});
