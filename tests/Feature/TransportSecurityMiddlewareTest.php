<?php

declare(strict_types=1);

it('rejects insecure requests when https enforcement is enabled', function (): void {
    config()->set('security.force_https', true);

    $this->getJson('/api/v1/auth/me')
        ->assertStatus(400)
        ->assertJsonPath('message', 'HTTPS is required for this endpoint.');
});

it('adds hsts header for secure requests when enabled', function (): void {
    config()->set('security.force_https', false);
    config()->set('security.hsts.enabled', true);
    config()->set('security.hsts.max_age', 31536000);
    config()->set('security.hsts.include_subdomains', true);
    config()->set('security.hsts.preload', false);

    $this->withHeaders([
        'X-Forwarded-Proto' => 'https',
    ])->getJson('/api/v1/auth/me')
        ->assertUnauthorized()
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});
