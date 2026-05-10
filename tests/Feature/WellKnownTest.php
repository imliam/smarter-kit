<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;

test('security.txt returns correct content type and contact', function (): void {
    $response = $this->get('/.well-known/security.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    $response->assertSeeText('Contact: mailto:security@example.com');
    $response->assertSeeText('Preferred-Languages: en');
    $response->assertSeeText('Canonical:');
    $response->assertSeeText('Expires:');
});

test('security.txt expires field is approximately one year from now', function (): void {
    $response = $this->get('/.well-known/security.txt');

    $content = (string) $response->getContent();
    $matched = preg_match('/^Expires: (.+)$/m', $content, $matches);

    expect($matched)->toBe(1);
    assert(isset($matches[1]));

    $expires = CarbonImmutable::parse($matches[1]);

    expect($expires->diffInDays(CarbonImmutable::now()->addYear()))->toBeLessThanOrEqual(1);
});

test('change-password redirects to the security settings page', function (): void {
    $response = $this->get('/.well-known/change-password');

    $response->assertRedirect(route('security.edit'));
});

test('gpc.json returns correct json with gpc true', function (): void {
    $response = $this->get('/.well-known/gpc.json');

    $response->assertOk();
    $response->assertJsonStructure(['gpc', 'version', 'lastUpdate']);
    $response->assertJson(['gpc' => true, 'version' => 1]);
});

test('oauth-protected-resource returns resource metadata', function (): void {
    $response = $this->get('/.well-known/oauth-protected-resource');

    $response->assertOk();
    $response->assertJsonStructure([
        'resource',
        'authorization_servers',
        'bearer_methods_supported',
        'scopes_supported',
        'resource_documentation',
    ]);
    $response->assertJson([
        'bearer_methods_supported' => ['header'],
    ]);
});

test('api-catalog returns linkset with api documentation links', function (): void {
    $response = $this->get('/.well-known/api-catalog');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/linkset+json');
    $response->assertJsonStructure([
        'linkset' => [
            ['anchor', 'service-doc', 'service-desc'],
        ],
    ]);
});

test('traffic-advice returns allow all user agents', function (): void {
    $response = $this->get('/.well-known/traffic-advice');

    $response->assertOk();
    $response->assertJsonStructure([['user-agent', 'disallow']]);
    $response->assertJson([['user-agent' => '*', 'disallow' => false]]);
});
