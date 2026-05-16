<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Teams\CreateTeam;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSocial;
use App\Notifications\Auth\Welcome;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialLoginCallbackController extends Controller
{
    public function __construct(private readonly CreateTeam $createTeam) {}

    public function __invoke(string $service): RedirectResponse
    {
        abort_unless(in_array($service, config('auth.social_providers'), true), 404);

        try {
            $serviceUser = Socialite::driver($service)->user();
        } catch (Throwable) {
            return to_route('login')
                ->withErrors(['email' => 'Social login failed or was cancelled. Please try again.']);
        }

        $socialToken = $serviceUser instanceof \Laravel\Socialite\Two\User || $serviceUser instanceof \Laravel\Socialite\One\User
            ? $serviceUser->token
            : null;
        $socialTokenSecret = $serviceUser instanceof \Laravel\Socialite\One\User ? $serviceUser->tokenSecret : null;
        $socialRefreshToken = $serviceUser instanceof \Laravel\Socialite\Two\User ? $serviceUser->refreshToken : null;

        $email = $serviceUser->getEmail();

        if (empty($email)) {
            return to_route('login')
                ->withErrors(['email' => "We couldn't retrieve your email from {$service}. Please ensure your {$service} account has a public email set."]);
        }

        if (session()->pull('social_connect_intent') && Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            if (! $user->hasSocialLinked($service)) {
                $user->social()->create([
                    'social_id' => $serviceUser->getId(),
                    'service' => $service,
                    'token' => $socialToken,
                    'token_secret' => $socialTokenSecret,
                    'refresh_token' => $socialRefreshToken,
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

            $this->createTeam->handle($user, $user->name."'s Team", isPersonal: true);

            event(new Registered($user));
        }

        if (! $user->hasSocialLinked($service)) {
            $user->social()->create([
                'social_id' => $serviceUser->getId(),
                'service' => $service,
                'token' => $socialToken,
                'token_secret' => $socialTokenSecret,
                'refresh_token' => $socialRefreshToken,
            ]);
        } else {
            $user->social()
                ->where('service', $service)
                ->update([
                    'token' => $socialToken,
                    'token_secret' => $socialTokenSecret,
                    'refresh_token' => $socialRefreshToken,
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
            ->orWhereIn('id', UserSocial::query()
                ->where('social_id', $serviceUser->getId())
                ->where('service', $service)
                ->select('user_id'))
            ->first();
    }
}
