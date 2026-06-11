<?php

declare(strict_types=1);

namespace App\Filament\Resources\Articles;

use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Filament\Resources\Articles\Schemas\ArticleForm;
use App\Filament\Resources\Articles\Tables\ArticlesTable;
use App\Models\Article;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

class ArticleResource extends Resource
{
    #[Override]
    protected static ?string $model = Article::class;

    #[Override]
    protected static ?string $recordTitleAttribute = 'title';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    /** @return array<int, string> */
    #[Override]
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'title',
            'path',
        ];
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return ArticleForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return ArticlesTable::configure($table);
    }

    /** @return array<int, string> */
    #[Override]
    public static function getRelations(): array
    {
        return [
        ];
    }

    /** @return array<string, PageRegistration> */
    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }
}
