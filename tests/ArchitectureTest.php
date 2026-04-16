<?php

declare(strict_types=1);

use App\Models\Model;

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel()
    ->ignoring([
        Model::class,
    ]);
