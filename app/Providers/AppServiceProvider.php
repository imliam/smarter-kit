<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\LaravelMorphMapGenerator\MorphMapGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::useAggressivePrefetching();
        URL::forceHttps(app()->isProduction());
        DB::prohibitDestructiveCommands(app()->isProduction());
        Model::unguard();
        Model::shouldBeStrict(! app()->isProduction());
        Model::automaticallyEagerLoadRelationships();
        MorphMapGenerator::resolveUsing(fn (Model $model) => $model->getTable());
        Date::use(CarbonImmutable::class);

        Password::defaults(function (): ?Password {
            if (! app()->isProduction()) {
                return null;
            }

            return Password::min(12)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });
    }
}
