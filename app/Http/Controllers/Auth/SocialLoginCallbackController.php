<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\Auth\Welcome;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialLoginCallbackController extends Controller
{
    public function __invoke(string $service): RedirectResponse
    {
        abort_unless(in_array($service, config('auth.social_providers'), true), 404);

        try {
            $serviceUser = Socialite::driver($service)->user();
        } catch (Throwable) {
            return to_route('login')
                ->withErrors(['email' => 'Social login failed or was cancelled. Please try again.']);
        }

        $email = $serviceUser->getEmail();

        if (empty($email)) {
            return to_route('login')
                ->withErrors(['email' => "We couldn't retrieve your email from {$service}. Please ensure your {$service} account has a public email set."]);
        }

        if (session()->pull('social_connect_intent') && Auth::check()) {
            $user = Auth::user();

            if (! $user->hasSocialLinked($service)) {
                $user->social()->create([
                    'social_id' => $serviceUser->getId(),
                    'service' => $service,
                    'token' => $serviceUser->token,
                    'token_secret' => $serviceUser->tokenSecret ?? null,
                    'refresh_token' => $serviceUser->refreshToken,
                ]);
            }

            return to_route('security.edit');
        }

        $user = $this->findExistingUser($serviceUser, $service);
        $isNewUser = ! $user instanceof User;

        if ($isNewUser) {
            $user = User::query()->create([
                'name' => $serviceUser->getName(),
                'email' => $email,
                'password' => Str::password(32),
                'email_verified_at' => now(),
            ]);

            event(new Registered($user));
        }

        if (! $user->hasSocialLinked($service)) {
            $user->social()->create([
                'social_id' => $serviceUser->getId(),
                'service' => $service,
                'token' => $serviceUser->token,
                'token_secret' => $serviceUser->tokenSecret ?? null,
                'refresh_token' => $serviceUser->refreshToken,
            ]);
        } else {
            $user->social()
                ->where('service', $service)
                ->update([
                    'token' => $serviceUser->token,
                    'token_secret' => $serviceUser->tokenSecret ?? null,
                    'refresh_token' => $serviceUser->refreshToken,
                ]);
        }

        if ($isNewUser) {
            $user->notify(new Welcome($service));
        }

        Auth::login($user);

        return redirect()->intended()
            ->withCookie(cookie()->forever('last_login_method', $service));
    }

    protected function findExistingUser(SocialiteUser $serviceUser, string $service): ?User
    {
        return User::query()
            ->where('email', $serviceUser->getEmail())
            ->orWhereHas(
                'social',
                fn (Builder $query) => $query->where('social_id', $serviceUser->getId())->where('service', $service),
            )
            ->first();
    }
}
