<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ArticleVisibility;
use App\Models\Model as BaseModel;
use Carbon\CarbonImmutable;
use Database\Factories\ArticleFactory;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Override;

/**
 * @property string $id
 * @property string|null $author_id
 * @property string $title
 * @property string $path
 * @property array<string, mixed> $body
 * @property string|null $excerpt
 * @property ArticleVisibility $visibility
 * @property CarbonImmutable|null $published_at
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read User|null $author
 *
 * @method static ArticleFactory factory($count = null, $state = [])
 *
 * @mixin Model
 */
class Article extends BaseModel implements HasRichContent
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    use InteractsWithRichContent;

    use SoftDeletes;

    /** @var list<string> */
    public const array RESERVED_TOP_LEVEL_PATHS = [
        'admin',
        'api',
        'dashboard',
        'invitations',
        'login',
        'logout',
        'register',
        'robots.txt',
        'sanctum',
        'settings',
        'sitemap.xml',
        'up',
    ];

    public static function normalizePath(string $path): string
    {
        return Str::of($path)
            ->trim('/')
            ->lower()
            ->replaceMatches('/\/{2,}/', '/')
            ->toString();
    }

    public static function pathIsReserved(string $path): bool
    {
        $path = self::normalizePath($path);
        $topLevelPath = Str::before($path, '/');

        return in_array($topLevelPath, self::RESERVED_TOP_LEVEL_PATHS, true);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function isPublished(): bool
    {
        return $this->published_at instanceof CarbonImmutable && $this->published_at->lte(now());
    }

    public function isPublic(): bool
    {
        return $this->visibility === ArticleVisibility::Public;
    }

    public function url(): string
    {
        return route('articles.show', ['path' => $this->path]);
    }

    /** @param Builder<Article> $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** @param Builder<Article> $query */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->published()
            ->where('visibility', ArticleVisibility::Public);
    }

    public function setPathAttribute(string $path): void
    {
        $this->attributes['path'] = self::normalizePath($path);
    }

    protected function setUpRichContent(): void
    {
        $this->registerRichContent('body')
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsVisibility('public');
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'body' => 'array',
            'published_at' => 'datetime',
            'visibility' => ArticleVisibility::class,
        ];
    }
}
