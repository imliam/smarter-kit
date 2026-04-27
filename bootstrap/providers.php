<?php

declare(strict_types=1);
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\MacroServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelServiceProvider::class,
    FortifyServiceProvider::class,
    MacroServiceProvider::class,
];
