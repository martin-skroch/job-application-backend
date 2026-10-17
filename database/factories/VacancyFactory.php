<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vacancy>
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
        $language = App::currentLocale();
        $title = fake()->jobTitle();
        $company = fake()->company();
        $domain = Str::slug($company, language: $language).'.'.fake()->tld();
        $website = "https://www.$domain";
        $source = "$website/jobs/".Str::slug($title, language: $language);
        $address = fake()->streetAddress()."\n".fake()->postcode().' '.fake()->city();

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'source' => $source,
            'description' => fake()->optional()->paragraphs(2, true),
            'contact_name' => fake()->name(),
            'contact_email' => "jobs@$domain",
            'contact_phone' => fake()->e164PhoneNumber(),
            'company_name' => $company,
            'company_address' => $address,
            'company_website' => $website,
        ];
    }
}
