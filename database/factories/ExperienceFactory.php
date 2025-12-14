<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Experience>
 */
class ExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position' => fake()->jobTitle(),
            'institution' => fake()->company(),
            'location' => fake()->city(),
            'type' => Str::title(fake()->words(1, true)),
            'entry' => fake()->date(),
            'exit' => fake()->date(),
            'description' => fake()->paragraph(3),
            'active' => fake()->boolean(75),
        ];
    }
}
