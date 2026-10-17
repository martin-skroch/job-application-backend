<?php

namespace Database\Factories;

use App\Models\Impression;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Impression>
 */
class ImpressionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'image' => null,
            'title' => fake()->sentence(5, true),
            'description' => fake()->sentence(20),
        ];
    }
}
