<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Override;

class ApiServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('auth-register', fn (Request $request): array => [
            Limit::perMinute(10)->by($request->ip()),
        ]);

        RateLimiter::for('auth-login', fn (Request $request): array => [
            Limit::perMinute(10)->by(sprintf('%s|%s', $request->ip(), (string) $request->input('email'))),
        ]);

        RateLimiter::for('auth-password', fn (Request $request): array => [
            Limit::perMinute(5)->by(sprintf('%s|%s', $request->ip(), (string) $request->input('email'))),
        ]);

        RateLimiter::for('auth-protected', fn (Request $request): array => [
            Limit::perMinute(60)->by(
                (string) ($request->user()?->getAuthIdentifier() ?? $request->ip())
            ),
        ]);
    }
}
