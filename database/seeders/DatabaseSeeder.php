<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Seed the application's database. */
    public function run(): void
    {
        User::factory()->create([
            'name' => config('default_user.name'),
            'email' => config('default_user.email'),
            'password' => bcrypt(config()->string('default_user.password')),
            'is_admin' => true,
        ]);
    }
}
