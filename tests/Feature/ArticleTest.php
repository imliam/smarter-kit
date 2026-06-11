<?php

declare(strict_types=1);

use App\Models\Article;
use Illuminate\Support\Facades\Cache;

test('published public article can be viewed by path', function (): void {
    $article = Article::factory()->create([
        'title' => 'About Us',
        'path' => 'about-us',
    ]);

    $this->get('/about-us')
        ->assertOk()
        ->assertSeeText($article->title);
});

test('published article can use nested path', function (): void {
    $article = Article::factory()->create([
        'title' => 'Nested Article',
        'path' => 'blog/something',
    ]);

    $this->get('/blog/something')
        ->assertOk()
        ->assertSeeText($article->title);
});

test('draft articles return not found', function (): void {
    Article::factory()->draft()->create([
        'path' => 'draft-article',
    ]);

    $this->get('/draft-article')->assertNotFound();
});

test('scheduled articles return not found', function (): void {
    Article::factory()->create([
        'path' => 'scheduled-article',
        'published_at' => now()->addDay(),
    ]);

    $this->get('/scheduled-article')->assertNotFound();
});

test('authenticated articles redirect guests to login', function (): void {
    Article::factory()->authenticated()->create([
        'path' => 'private-article',
    ]);

    $this->get('/private-article')
        ->assertRedirect(route('login'));
});

test('authenticated articles can be viewed by signed in users', function (): void {
    $article = Article::factory()->authenticated()->create([
        'title' => 'Private Article',
        'path' => 'private-article',
    ]);

    $this->login()
        ->get('/private-article')
        ->assertOk()
        ->assertSeeText($article->title);
});

test('published public articles are included in the sitemap', function (): void {
    Cache::forget('sitemap');

    Article::factory()->create([
        'path' => 'public-article',
    ]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSeeText(url('/public-article'));
});

test('private and draft articles are excluded from the sitemap', function (): void {
    Cache::forget('sitemap');

    Article::factory()->authenticated()->create([
        'path' => 'private-article',
    ]);

    Article::factory()->draft()->create([
        'path' => 'draft-article',
    ]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertDontSeeText(url('/private-article'))
        ->assertDontSeeText(url('/draft-article'));
});

test('article paths are normalized', function (): void {
    $article = Article::factory()->create([
        'path' => '/Blog//Something/',
    ]);

    expect($article->refresh()->path)->toBe('blog/something');
});

test('article can determine reserved paths', function (): void {
    expect(Article::pathIsReserved('admin/articles'))->toBeTrue()
        ->and(Article::pathIsReserved('blog/articles'))->toBeFalse();
});
