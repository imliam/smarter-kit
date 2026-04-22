<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public const string DEFAULT_EMAIL = 'test@example.com';

    public const string DEFAULT_PASSWORD = 'password';

    /** Seed the application's database. */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => self::DEFAULT_EMAIL,
            'password' => Hash::make(self::DEFAULT_PASSWORD),
        ]);
    }
}
