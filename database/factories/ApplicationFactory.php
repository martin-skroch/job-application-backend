<?php

namespace Database\Factories;

use App\Actions\CreateApplicationFromVacancy;
use App\Enum\FormOfAddress;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vacancy_id' => fn (array $attributes) => Vacancy::factory()->create([
                'user_id' => $attributes['user_id'] ?? null,
            ])->id,
            'form_of_address' => FormOfAddress::Formal,
        ];
    }

    /**
     * Copy company and contact details from the vacancy.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Application $application): void {
            $vacancy = Vacancy::withTrashed()->find($application->vacancy_id);

            if (! $vacancy instanceof Vacancy) {
                return;
            }

            foreach (CreateApplicationFromVacancy::COPIED_ATTRIBUTES as $attribute) {
                if (! $application->isDirty($attribute)) {
                    $application->{$attribute} = $vacancy->{$attribute};
                }
            }
        });
    }
}
