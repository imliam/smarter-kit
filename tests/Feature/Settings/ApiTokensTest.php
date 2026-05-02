<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Livewire;

test('api tokens settings page can be rendered', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tokens.index'))
        ->assertOk()
        ->assertSee('API Tokens');
});

test('api tokens settings page requires authentication', function (): void {
    $this->get(route('tokens.index'))
        ->assertRedirect(route('login'));
});

test('api tokens settings page requires email verification', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('tokens.index'))
        ->assertRedirect(route('verification.notice'));
});

test('user can create a token', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::settings.tokens')
        ->set('tokenName', 'my-app')
        ->set('tokenAbilities', ['auth:me', 'auth:logout'])
        ->call('createToken');

    $component->assertHasNoErrors();

    expect($user->tokens()->where('name', 'my-app')->exists())->toBeTrue();
    expect($component->get('newTokenValue'))->toBeString()->not->toBeEmpty();
    expect($component->get('showTokenModal'))->toBeTrue();
    expect($component->get('tokenName'))->toBeEmpty();
});

test('token name is required to create a token', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.tokens')
        ->set('tokenName', '')
        ->set('tokenAbilities', ['auth:me'])
        ->call('createToken')
        ->assertHasErrors(['tokenName' => 'required']);
});

test('at least one ability is required to create a token', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.tokens')
        ->set('tokenName', 'my-app')
        ->set('tokenAbilities', [])
        ->call('createToken')
        ->assertHasErrors(['tokenAbilities']);
});

test('user can revoke a token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('my-app');
    $tokenId = $token->accessToken->id;

    $this->actingAs($user);

    Livewire::test('pages::settings.tokens')
        ->call('revokeToken', $tokenId);

    expect($user->tokens()->whereKey($tokenId)->exists())->toBeFalse();
});

test("user cannot revoke another user's token", function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherToken = $otherUser->createToken('their-app');
    $tokenId = $otherToken->accessToken->id;

    $this->actingAs($user);

    Livewire::test('pages::settings.tokens')
        ->call('revokeToken', $tokenId);

    expect($otherUser->tokens()->whereKey($tokenId)->exists())->toBeTrue();
});

test('user can revoke all tokens', function (): void {
    $user = User::factory()->create();
    $user->createToken('app-1');
    $user->createToken('app-2');

    $this->actingAs($user);

    Livewire::test('pages::settings.tokens')
        ->call('revokeAllTokens');

    expect($user->tokens()->count())->toBe(0);
});

test('dismissing new token clears the value', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test('pages::settings.tokens')
        ->set('tokenName', 'my-app')
        ->set('tokenAbilities', ['auth:me'])
        ->call('createToken')
        ->assertSet('newTokenValue', fn ($value): bool => ! empty($value))
        ->assertSet('showTokenModal', true);

    $component->call('dismissNewToken')
        ->assertSet('newTokenValue', null)
        ->assertSet('showTokenModal', false);
});
