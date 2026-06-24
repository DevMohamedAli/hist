<?php

namespace Modules\Website\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Website\Enums\WebsiteContentStatus;
use Modules\Website\Models\Page;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'summary' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'status' => WebsiteContentStatus::Draft,
        ];
    }

    public function published(): self
    {
        return $this->state(fn (): array => [
            'status' => WebsiteContentStatus::Published,
            'published_at' => now()->subMinute(),
        ]);
    }
}
