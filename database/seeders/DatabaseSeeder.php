<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ArticleVisibility;
use App\Models\Article;
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
        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => self::DEFAULT_EMAIL,
            'password' => Hash::make(self::DEFAULT_PASSWORD),
        ]);

        Article::factory()->create([
            'author_id' => $admin->id,
            'title' => 'About Us',
            'path' => 'about',
            'excerpt' => 'A short introduction to your application.',
            'visibility' => ArticleVisibility::Public,
            'published_at' => now(),
            'meta_title' => 'About Us',
            'meta_description' => 'Learn more about this application.',
        ]);

        Article::factory()->draft()->create([
            'author_id' => $admin->id,
            'title' => 'Draft Article',
            'path' => 'draft-article',
            'excerpt' => 'An unpublished article example.',
        ]);
    }
}
