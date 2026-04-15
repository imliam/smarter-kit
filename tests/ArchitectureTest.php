<?php

use App\Models\Model;

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel()
    ->ignoring([
        Model::class,
    ]);
