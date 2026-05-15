<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\Auth\Welcome;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function (): void {
    config(['auth.social_providers' => ['github']]);
});

function makeSocialiteUser(array $attributes = []): SocialiteUser
{
    return (new SocialiteUser)->map(array_merge([
        'id' => 'gh-000',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'token' => 'tok',
        'refreshToken' => null,
        'tokenSecret' => null,
    ], $attributes));
}

describe('redirect', function (): void {
    test('returns 404 for unlisted provider', function (): void {
        $this->get(route('social-login.redirect', 'evil'))->assertNotFound();
    });

    test('sends user to provider', function (): void {
        Socialite::fake('github');

        $this->get(route('social-login.redirect', 'github'))->assertRedirect();
    });

    test('stores connect intent in session', function (): void {
        Socialite::fake('github');

        $this->get(route('social-login.redirect', 'github').'?connect=true')
            ->assertSessionHas('social_connect_intent', true);
    });
});

describe('callback', function (): void {
    test('returns 404 for unlisted provider', function (): void {
        $this->get(route('social-login.callback', 'evil'))->assertNotFound();
    });

    test('redirects to login on exception', function (): void {
        Socialite::shouldReceive('driver->user')->andThrow(new Exception('denied'));

        $this->get(route('social-login.callback', 'github'))
            ->assertRedirect(route('login'));
    });

    test('new user is created and logged in on first social login', function (): void {
        Notification::fake();

        Socialite::fake('github', makeSocialiteUser([
            'id' => 'gh-001',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]));

        $this->get(route('social-login.callback', 'github'))->assertRedirect();

        $this->assertAuthenticated();

        $user = User::query()->where('email', 'jane@example.com')->first();
        expect($user)->not->toBeNull()
            ->and($user->email_verified_at)->not->toBeNull()
            ->and($user->social()->where('service', 'github')->exists())->toBeTrue();

        Notification::assertSentTo($user, Welcome::class, fn ($n): bool => $n->provider === 'github');
    });

    test('existing user is logged in without creating duplicate social record', function (): void {
        Notification::fake();

        $user = User::factory()->create(['email' => 'existing@example.com']);
        $user->social()->create(['social_id' => 'gh-002', 'service' => 'github', 'token' => 'tok']);

        Socialite::fake('github', makeSocialiteUser([
            'id' => 'gh-002',
            'name' => $user->name,
            'email' => $user->email,
        ]));

        $this->get(route('social-login.callback', 'github'));

        $this->assertAuthenticatedAs($user);
        expect($user->social()->where('service', 'github')->count())->toBe(1);
        Notification::assertNothingSentTo($user);
    });

    test('sets last_login_method cookie', function (): void {
        Socialite::fake('github', makeSocialiteUser([
            'id' => 'gh-003',
            'email' => 'cookie@example.com',
        ]));

        $this->get(route('social-login.callback', 'github'))
            ->assertCookie('last_login_method', 'github');
    });

    test('connect intent links social account to authenticated user', function (): void {
        $user = User::factory()->create();

        Socialite::fake('github', makeSocialiteUser([
            'id' => 'gh-004',
            'name' => $user->name,
            'email' => $user->email,
        ]));

        $this->actingAs($user)
            ->withSession(['social_connect_intent' => true])
            ->get(route('social-login.callback', 'github'))
            ->assertRedirect(route('security.edit'));

        expect($user->social()->where('service', 'github')->exists())->toBeTrue();
    });

    test('connect intent does not duplicate an existing link', function (): void {
        $user = User::factory()->create();
        $user->social()->create(['social_id' => 'gh-005', 'service' => 'github', 'token' => 'tok']);

        Socialite::fake('github', makeSocialiteUser([
            'id' => 'gh-005',
            'name' => $user->name,
            'email' => $user->email,
        ]));

        $this->actingAs($user)
            ->withSession(['social_connect_intent' => true])
            ->get(route('social-login.callback', 'github'));

        expect($user->social()->where('service', 'github')->count())->toBe(1);
    });
});
