<?php

declare(strict_types=1);

use App\Enums\ArticleVisibility;
use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Models\Article;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->login(User::factory()->admin()->create());
});

it('can render the index page', function (): void {
    livewire(ListArticles::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateArticle::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $article = Article::factory()->create();

    livewire(EditArticle::class, [
        'record' => $article->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'title' => $article->title,
            'path' => $article->path,
        ]);
});

it('has columns', function (string $column): void {
    livewire(ListArticles::class)
        ->assertTableColumnExists($column);
})->with(['title', 'path', 'visibility', 'author.name', 'published_at', 'created_at']);

it('can create an article', function (): void {
    $author = User::factory()->admin()->create();

    livewire(CreateArticle::class)
        ->fillForm([
            'title' => 'About Us',
            'path' => 'about-us',
            'body' => [
                'type' => 'doc',
                'content' => [],
            ],
            'excerpt' => 'About this app.',
            'visibility' => ArticleVisibility::Public->value,
            'published_at' => now(),
            'author_id' => $author->id,
            'meta_title' => 'About Us',
            'meta_description' => 'About this app.',
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Article::class, [
        'title' => 'About Us',
        'path' => 'about-us',
        'author_id' => $author->id,
    ]);
});

it('can update an article', function (): void {
    $article = Article::factory()->create();

    livewire(EditArticle::class, [
        'record' => $article->id,
    ])
        ->fillForm([
            'title' => 'Updated Article',
            'path' => 'updated-article',
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Article::class, [
        'id' => $article->id,
        'title' => 'Updated Article',
        'path' => 'updated-article',
    ]);
});

it('can soft delete an article', function (): void {
    $article = Article::factory()->create();

    livewire(EditArticle::class, [
        'record' => $article->id,
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertSoftDeleted($article);
});

it('can bulk delete articles', function (): void {
    $articles = Article::factory()->count(5)->create();

    livewire(ListArticles::class)
        ->loadTable()
        ->assertCanSeeTableRecords($articles)
        ->selectTableRecords($articles)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($articles);

    $articles->each(fn (Article $article) => assertSoftDeleted($article));
});

it('validates unique article paths', function (): void {
    $article = Article::factory()->create([
        'path' => 'about-us',
    ]);

    livewire(CreateArticle::class)
        ->fillForm([
            'title' => 'Another Article',
            'path' => $article->path,
            'body' => [
                'type' => 'doc',
                'content' => [],
            ],
            'visibility' => ArticleVisibility::Public->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['path' => ['unique']]);
});

it('validates reserved article paths', function (): void {
    livewire(CreateArticle::class)
        ->fillForm([
            'title' => 'Admin Article',
            'path' => 'admin/article',
            'body' => [
                'type' => 'doc',
                'content' => [],
            ],
            'visibility' => ArticleVisibility::Public->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['path']);
});
