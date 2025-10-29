<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'demo@pestphp.com',
        'password' => 'password',
    ]);
});

test('an unauthenticated user can login', function () {
    visit('/admin/login')
        ->fill('form.email', $this->user->email)
        ->fill('form.password', 'password')
        ->submit()
        ->assertSee('Dashboard');

    $this->assertAuthenticated();
});
