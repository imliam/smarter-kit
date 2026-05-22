<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;

test('sitemap returns 200 with correct content type', function (): void {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');
});

test('sitemap includes the home page url', function (): void {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertSeeText(url('/'));
});

test('sitemap response is cached', function (): void {
    Cache::shouldReceive('remember')
        ->once()
        ->andReturnUsing(fn (string $key, mixed $ttl, Closure $callback) => $callback());

    $this->get('/sitemap.xml')->assertOk();
});

test('robots.txt returns 200 with correct content type', function (): void {
    $response = $this->get('/robots.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
});

test('robots.txt contains user-agent and disallow directives', function (): void {
    $response = $this->get('/robots.txt');

    $response->assertSeeText('User-agent: *');
    $response->assertSeeText('Disallow:');
});

test('robots.txt contains sitemap directive pointing to app url', function (): void {
    config(['app.url' => 'https://example.com']);

    $response = $this->get('/robots.txt');

    $response->assertSeeText('Sitemap: https://example.com/sitemap.xml');
});
