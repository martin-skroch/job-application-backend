<?php

namespace Database\Factories;

use App\Enum\Workplace;
use App\Enum\SalaryPeriod;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vacancy>
 */
class VacancyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locale = App::currentLocale();
        $company = fake()->company();
        $domain = Str::slug($company, language: $locale) . '.' . fake()->tld();
        $address = fake()->streetAddress() . "\n" . fake()->postcode() . ' ' . fake()->city();

        $salaryPeriod = fake()->randomElement(SalaryPeriod::values());

        if ($salaryPeriod === 'yearly') {
            $salaryValue = fake()->randomElement(range(18000, 48000, 1000));
            $salaryMin = $salaryValue - 1000;
            $salaryMax = $salaryValue + 1000;
        } else {
            $salaryValue = fake()->randomElement(range(1500, 4000, 100));
            $salaryMin = $salaryValue - 100;
            $salaryMax = $salaryValue + 100;
        }

        return [
            'title' => fake()->jobTitle(),
            'content' => fake()->sentences(3, true),
            'salary_period' => $salaryPeriod,
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'workplace' => fake()->randomElements(Workplace::values(), 2),
            'weekhours' => fake()->randomElement([30, 35, 40]),
            'location' => fake()->city(),
            'source' => fake()->url(),
            'company' => $company,
            'address' => $address,
            'contact' => fake()->name(),
            'website' => 'https://' . $domain,
            'email' => 'jobs@' . $domain,
        ];
    }
}
