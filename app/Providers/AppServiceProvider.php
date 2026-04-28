<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
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
use Livewire\Blaze\Blaze;
use Override;
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
        $this->configureGates();
        $this->configureDefaults();
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
