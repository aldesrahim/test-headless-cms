<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Lottery;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $published = Lottery::odds(2, 5)->choose();

        return [
            'title' => fake()->sentence(2),
            'body' => fake()->paragraph(5),
            'status' => (int) $published,
            'published_at' => $published ? now() : null,
        ];
    }
}
