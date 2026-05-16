<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\Sanctum;
use Livewire\Blaze\Blaze;
use Override;
use RuntimeException;
use Spatie\LaravelMorphMapGenerator\MorphMapGenerator;

class AppServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        RedirectIfAuthenticated::redirectUsing(function (Request $request): string {
            /** @var User|null $user */
            $user = $request->user();
            $team = $user !== null ? ($user->currentTeam ?? $user->personalTeam()) : null;

            if ($team) {
                return route('dashboard', ['current_team' => $team->slug]);
            }

            return route('home');
        });

        $this->checkEnvironment();
        $this->configureGates();
        $this->configureDefaults();
    }

    protected function checkEnvironment(): void
    {
        if (! app()->isProduction()) {
            return;
        }

        throw_if((bool) config('app.debug', false), RuntimeException::class, 'In production, APP_DEBUG must be false.');

        $appUrl = mb_strtolower((string) config('app.url', ''));
        throw_unless(str_starts_with($appUrl, 'https://'), RuntimeException::class, 'In production, APP_URL must use https://.');

        $allowedOrigins = config()->array('cors.allowed_origins', []);
        throw_if(in_array('*', $allowedOrigins, true), RuntimeException::class, 'In production, CORS allowed origins must not use wildcard "*".');

        $trustedHosts = config()->array('security.trusted_hosts', []);
        throw_if($trustedHosts === [], RuntimeException::class, 'In production, TRUSTED_HOSTS must be configured.');
    }

    protected function configureGates(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->isAdmin()) {
                return true;
            }

            return null;
        });
    }

    protected function configureDefaults(): void
    {
        URL::forceHttps(app()->isProduction());

        Vite::useAggressivePrefetching();

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Model::unguard();

        Model::shouldBeStrict(! app()->isProduction());

        Model::automaticallyEagerLoadRelationships();

        MorphMapGenerator::resolveUsing(fn (Model $model) => $model->getTable());

        FormRequest::failOnUnknownFields();

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        if (! app()->isProduction()) {
            RequestException::dontTruncate();
        }

        Blaze::optimize()->in(
            resource_path('views/components'),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
