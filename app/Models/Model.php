<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    public static function existingOrFactory(): mixed
    {
        $instance = static::query()->inRandomOrder()->first();

        if ($instance) {
            return $instance->getKey();
        }

        if (method_exists(static::class, 'factory')) {
            return static::factory();
        }

        throw new Exception('No existing instance of ['.static::class.'] found and factory not available');
    }
}
