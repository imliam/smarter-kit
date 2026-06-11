<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ArticleVisibility;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'author_id' => User::existingOrFactory(),
            'title' => $title,
            'path' => Str::slug($title),
            'body' => [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => fake()->paragraph(),
                            ],
                        ],
                    ],
                ],
            ],
            'excerpt' => fake()->sentence(),
            'visibility' => ArticleVisibility::Public,
            'published_at' => now(),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'published_at' => null,
        ]);
    }

    public function authenticated(): static
    {
        return $this->state(fn (array $attributes): array => [
            'visibility' => ArticleVisibility::Authenticated,
        ]);
    }
}
