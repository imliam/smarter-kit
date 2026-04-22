<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public const string DEFAULT_EMAIL = 'admin@example.com';

    public const string DEFAULT_PASSWORD = 'password';

    /** Seed the application's database. */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => self::DEFAULT_EMAIL,
            'password' => Hash::make(self::DEFAULT_PASSWORD),
        ]);
    }
}
