<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('can create a new user', function () {
    $user = User::factory()->make();

    visit('/admin')
        ->click('Users')
        ->click('New user')
        ->fill('form.name', $user->name)
        ->fill('form.email', $user->email)
        ->fill('form.password', 'password')
        ->click('[type="submit"][wire\\:target="create"]')
        ->assertSee('Created');

    assertDatabaseHas('users', [
        'name' => $user->name,
        'email' => $user->email,
    ]);
});

it('can edit an existing user', function () {
    $newRecord = User::factory()->make();

    visit('/admin')
        ->click('Users')
        ->click('Edit')
        ->fill('form.name', $newRecord->name)
        ->click('[type="submit"][wire\\:target="save"]')
        ->assertSee('Saved');

    assertDatabaseHas('users', [
        'name' => $newRecord->name,
    ]);
});

it('can delete an existing user', function () {
    visit('/admin')
        ->click('Users')
        ->click('Edit')
        ->click('Delete')
        ->click('[type="submit"][wire\\:target="callMountedAction"]')
        ->assertSee('Deleted');

    assertDatabaseMissing('users', [
        'id' => auth()->user()->id,
    ]);
});
