<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Post;
use App\Services\Posts\Enums\AttachmentKey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Lottery;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
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
            'content' => $content = fake()->paragraph(5),
            'excerpt' => Str::limit($content),
            'status' => (int) $published,
            'published_at' => $published ? now() : null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Post $post) {
            $this->attachCategories($post);
            $this->attachImages($post);
        });
    }

    protected function attachCategories(Post $post): void
    {
        $categories = Category::limit(2)->inRandomOrder()->get();
        $post->categories()->attach($categories);
    }

    public function attachImages(Post $post): void
    {
        $image = Attachment::limit(1)->inRandomOrder()->first();
        $post->attachments()->attach($image, ['key' => AttachmentKey::Banner->value]);
    }
}
