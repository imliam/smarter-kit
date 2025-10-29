<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Override;
use Spatie\LaravelMorphMapGenerator\MorphMapGenerator;

class AppServiceProvider extends ServiceProvider
{
    /** Register any application services. */
    #[Override]
    public function register(): void
    {
        //
    }

    /** Bootstrap any application services. */
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

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));

        if (! app()->isProduction()) {
            RequestException::dontTruncate();
        }

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

        $this->configureFilamentDefaults();
    }

    private function configureFilamentDefaults(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table->striped()->deferLoading();
            $table->paginated([10, 25, 50, 100]);
        });

        Section::configureUsing(function (Section $section): void {
            $section->columns(1);
        }, isImportant: true);
    }
}
