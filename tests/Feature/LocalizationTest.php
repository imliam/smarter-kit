<?php

declare(strict_types=1);

it('falls back to default locale for unsupported language values', function (): void {
    $this->withHeaders([
        'Accept-Language' => 'fr-FR,fr;q=0.9',
    ])->postJson('/api/v1/auth/password/forgot', [
        'email' => 'unknown@example.com',
    ])
        ->assertOk()
        ->assertHeader('Content-Language', 'en')
        ->assertJsonPath('message', 'If the account exists, a password reset link has been sent.');
});
