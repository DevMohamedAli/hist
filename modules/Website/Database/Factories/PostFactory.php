<?php

namespace Modules\Website\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Website\Enums\WebsiteContentStatus;
use Modules\Website\Models\Post;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'summary' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'type' => 'news',
            'status' => WebsiteContentStatus::Draft,
        ];
    }
}
