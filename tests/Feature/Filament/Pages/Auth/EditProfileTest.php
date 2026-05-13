<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Livewire\livewire;

test('changing password in filament profile signs out other devices', function (): void {
    $user = User::factory()->admin()->create([
        'password' => Hash::make('old-password'),
    ]);

    $this->login($user);

    livewire(EditProfile::class)
        ->fillForm([
            'currentPassword' => 'old-password',
            'password' => 'new-password',
            'passwordConfirmation' => 'new-password',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('updating profile without a password does not sign out other devices', function (): void {
    $user = User::factory()->admin()->create([
        'name' => 'Original Name',
        'password' => Hash::make('password'),
    ]);

    $this->login($user);

    livewire(EditProfile::class)
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->refresh()->name)->toBe('Updated Name');
    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});
