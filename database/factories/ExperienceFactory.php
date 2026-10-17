<?php

namespace Database\Factories;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Experience>
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
