<?php

declare(strict_types=1);

namespace App\Enums;

enum ArticleVisibility: string
{
    case Public = 'public';
    case Authenticated = 'authenticated';

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $visibility): array => [$visibility->value => $visibility->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Authenticated => 'Authenticated',
        };
    }
}
