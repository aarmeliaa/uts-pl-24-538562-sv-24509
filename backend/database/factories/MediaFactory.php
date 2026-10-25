<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'type' => fake()->randomElement(Media::TYPES),
            'status' => fake()->randomElement(Media::STATUSES),
            'rating' => fake()->optional()->numberBetween(1, 10),
            'notes' => fake()->optional()->sentence(),
            'consumed_at' => fake()->optional()->date(),
        ];
    }
}
