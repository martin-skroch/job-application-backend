<?php

namespace Database\Factories;

use App\Enum\ApplicationStatus;
use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApplicationHistory>
 */
class ApplicationHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'status' => fake()->randomElement(ApplicationStatus::cases()),
            'comment' => fake()->optional()->sentence(),
        ];
    }

    /**
     * State for a standalone note without a status change.
     */
    public function note(): static
    {
        return $this->state(['status' => null, 'comment' => fake()->sentence()]);
    }
}
