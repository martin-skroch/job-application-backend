<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resume>
 */
class ResumeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->firstName() . ' ' . fake()->lastName();
        $domain = fake()->domainName();

        return [
            'name' => $name,
            'image' => fake()->image(),
            'address' => fake()->streetAddress(),
            'post_code' => fake()->postcode(),
            'location' => fake()->city(),
            'birthdate' => fake()->date(),
            'birthplace' => fake()->city(),
            'phone' => fake()->phoneNumber(),
            'email' => Str::slug($name) . '@' . $domain,
            'website' => 'https://' . $domain,
            'token' => Str::random(64),
        ];
    }
}
