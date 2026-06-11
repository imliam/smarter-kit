<?php

declare(strict_types=1);

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateArticle extends CreateRecord
{
    #[Override]
    protected static string $resource = ArticleResource::class;
}
