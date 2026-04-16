<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\RegisterResponse;
use App\Http\Responses\TwoFactorLoginResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;
use Override;

class FortifyServiceProvider extends ServiceProvider
{
    /** Register any application services. */
    #[Override]
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
        $this->app->singleton(TwoFactorLoginResponseContract::class, TwoFactorLoginResponse::class);
    }

    /** Bootstrap any application services. */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /** Configure Fortify actions. */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /** Configure Fortify views. */
    private function configureViews(): void
    {
        Fortify::loginView(fn (): Factory|\Illuminate\Contracts\View\View => view('pages::auth.login'));
        Fortify::verifyEmailView(fn (): Factory|\Illuminate\Contracts\View\View => view('pages::auth.verify-email'));
        Fortify::twoFactorChallengeView(fn (): Factory|\Illuminate\Contracts\View\View => view('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn (): Factory|\Illuminate\Contracts\View\View => view('pages::auth.confirm-password'));
        Fortify::registerView(fn (): Factory|\Illuminate\Contracts\View\View => view('pages::auth.register'));
        Fortify::resetPasswordView(fn (): Factory|\Illuminate\Contracts\View\View => view('pages::auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn (): Factory|\Illuminate\Contracts\View\View => view('pages::auth.forgot-password'));
    }

    /** Configure rate limiting. */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
