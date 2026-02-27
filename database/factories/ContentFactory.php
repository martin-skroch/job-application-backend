<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Content>
 */
class ContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'heading' => fake()->sentence(4, true),
            'name' => fake()->slug(3),
            'text' => fake()->paragraph(),
            'image' => null,
            'order' => fake()->numberBetween(1, 50),
            'active' => fake()->boolean(75),
        ];
    }

    public function active(): static
    {
        return $this->state(['active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
