<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->strict();
arch()->preset()->security();
arch()->preset()->laravel()
    ->ignoring([
        'App\Models\Model',
    ]);
