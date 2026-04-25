<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('profile page is displayed', function (): void {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

test('user can upload an avatar', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = Livewire::test('pages::settings.profile')
        ->set('avatar', $file)
        ->call('updateAvatar');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->avatar_url)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_url);
});

test('avatar upload validates file type', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = Livewire::test('pages::settings.profile')
        ->set('avatar', $file)
        ->call('updateAvatar');

    $response->assertHasErrors(['avatar']);
});

test('old avatar is deleted when a new one is uploaded', function (): void {
    Storage::fake('public');

    $oldPath = UploadedFile::fake()->image('old.jpg')->store('avatars', 'public');

    $user = User::factory()->create(['avatar_url' => $oldPath]);
    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->set('avatar', UploadedFile::fake()->image('new.jpg'))
        ->call('updateAvatar')
        ->assertHasNoErrors();

    Storage::disk('public')->assertMissing($oldPath);
});

test('user can remove their avatar', function (): void {
    Storage::fake('public');

    $path = UploadedFile::fake()->image('avatar.jpg')->store('avatars', 'public');

    $user = User::factory()->create(['avatar_url' => $path]);
    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->call('removeAvatar')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->avatar_url)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});
