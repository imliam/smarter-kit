<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel()
    ->ignoring([
        'App\Models\Model',
    ]);
arch()->expect(['sleep', 'usleep'])->not->toBeUsed();

arch('tests to not be used in application code')
    ->expect('Tests')
    ->not->toBeUsedIn('App');
