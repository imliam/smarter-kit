<?php

declare(strict_types=1);

use App\Models\Model;
use Pest\Arch\Support\Composer;

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel()
    ->ignoring([
        Model::class,
    ]);

arch()->expect(['sleep', 'usleep'])->not->toBeUsed();

arch('tests to not be used in application code')
    ->expect('Tests')
    ->not->toBeUsedIn(['App', 'Database']);

foreach (Composer::userNamespaces() as $namespace) {
    arch()->expect($namespace)->toUseStrictTypes();
    arch()->expect($namespace)->toUseStrictEquality();
}
