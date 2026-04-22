<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Panel;
use Illuminate\Support\Facades\Gate;

test('users have no role by default', function (): void {
    $user = User::factory()->create();

    expect($user->role)->toBeNull();
    expect($user->isAdmin())->toBeFalse();
});

test('admins can access the admin panel', function (): void {
    $admin = User::factory()->admin()->create();
    $panel = app(Panel::class);

    expect($admin->canAccessPanel($panel))->toBeTrue();
});

test('regular users cannot access the admin panel', function (): void {
    $user = User::factory()->create();
    $panel = app(Panel::class);

    expect($user->canAccessPanel($panel))->toBeFalse();
});

test('admins pass all gates', function (): void {
    $admin = User::factory()->admin()->create();

    expect(Gate::forUser($admin)->allows('any-ability'))->toBeTrue();
});

test('regular users do not pass gates via the admin bypass', function (): void {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows('any-ability'))->toBeFalse();
});
