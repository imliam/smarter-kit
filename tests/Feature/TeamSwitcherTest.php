<?php

declare(strict_types=1);

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

test('team switcher is hidden when user has only one team', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('⚡team-switcher')
        ->assertDontSeeHtml('data-test="team-switcher-trigger"');
});

test('team switcher is visible when user has multiple teams', function (): void {
    $user = User::factory()->create();

    $secondTeam = Team::factory()->create();
    $secondTeam->members()->attach($user, ['role' => TeamRole::Member->value]);

    Livewire::actingAs($user)
        ->test('⚡team-switcher')
        ->assertSeeHtml('data-test="team-switcher-trigger"');
});

test('hasMultipleTeams returns false with one team', function (): void {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test('⚡team-switcher');

    expect($component->instance()->hasMultipleTeams())->toBeFalse();
});

test('hasMultipleTeams returns true with multiple teams', function (): void {
    $user = User::factory()->create();

    $secondTeam = Team::factory()->create();
    $secondTeam->members()->attach($user, ['role' => TeamRole::Member->value]);

    $component = Livewire::actingAs($user)->test('⚡team-switcher');

    expect($component->instance()->hasMultipleTeams())->toBeTrue();
});
