<?php

declare(strict_types=1);

namespace App\Filament\Resources\Articles\Schemas;

use App\Enums\ArticleVisibility;
use App\Models\Article;
use App\Models\User;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('Content')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, mixed $state, callable $set): void {
                                        if ($operation !== 'create' || blank($state)) {
                                            return;
                                        }

                                        $set('path', Str::slug((string) $state));
                                    }),
                                TextInput::make('path')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->rules([
                                        'regex:/^[a-z0-9]+(?:[\/-][a-z0-9]+)*$/',
                                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                            if (Article::pathIsReserved((string) $value)) {
                                                $fail('The :attribute uses a reserved application path.');
                                            }
                                        },
                                    ])
                                    ->dehydrateStateUsing(fn (mixed $state): string => Article::normalizePath((string) $state)),
                                RichEditor::make('body')
                                    ->required()
                                    ->json()
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('article-attachments')
                                    ->fileAttachmentsVisibility('public')
                                    ->columnSpanFull(),
                                Textarea::make('excerpt')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(['lg' => 2]),
                        Section::make('Publishing')
                            ->schema([
                                Select::make('visibility')
                                    ->options(ArticleVisibility::options())
                                    ->default(ArticleVisibility::Public->value)
                                    ->required(),
                                DateTimePicker::make('published_at')
                                    ->seconds(false),
                                Select::make('author_id')
                                    ->label('Author')
                                    ->relationship('author', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(fn (): ?string => Auth::user() instanceof User ? Auth::id() : null)
                                    ->nullable(),
                            ])
                            ->columnSpan(['lg' => 1]),
                        Section::make('SEO')
                            ->schema([
                                TextInput::make('meta_title')
                                    ->maxLength(255),
                                Textarea::make('meta_description')
                                    ->maxLength(65535),
                            ])
                            ->columnSpan(['lg' => 1]),
                    ])
                    ->columns(['lg' => 3])
                    ->columnSpanFull(),
            ]);
    }
}
