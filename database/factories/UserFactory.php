<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TeamRole;
use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Override;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /** The current password being used by the factory. */
    protected static ?string $password;

    /** Define the model's default state. */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::User,
            'avatar_url' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /** Configure the model factory. */
    #[Override]
    public function configure(): static
    {
        /** @var $this $factory */
        $factory = $this->afterCreating(function ($user): void {
            $teamName = $user->name."'s Team";

            $team = Team::factory()->personal()->create([
                'name' => $teamName,
                'slug' => Str::slug($teamName),
            ]);

            $team->members()->attach($user, [
                'role' => TeamRole::Owner->value,
            ]);

            $user->switchTeam($team);
        });

        return $factory;
    }

    /** Indicate that the model's email address should be unverified. */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /** Indicate that the user is a regular user. */
    public function user(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::User,
        ]);
    }

    /** Indicate that the user is an admin. */
    public function admin(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Admin,
        ]);
    }

    /** Indicate that the model has two-factor authentication configured. */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes): array => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /** Indicate that the user should be created without a personal team. */
    public function withoutTeam(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->teams()->detach();
            $user->update(['current_team_id' => null]);
        });
    }
}
