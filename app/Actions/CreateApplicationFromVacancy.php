<?php

namespace App\Actions;

use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Validation\ValidationException;

class CreateApplicationFromVacancy
{
    /**
     * Company and contact details that are copied from the vacancy.
     *
     * @var list<string>
     */
    public const array COPIED_ATTRIBUTES = [
        'contact_name',
        'contact_email',
        'contact_phone',
        'company_name',
        'company_address',
        'company_website',
    ];

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws ValidationException
     */
    public function handle(Vacancy $vacancy, array $attributes = []): Application
    {
        if ($vacancy->application()->exists()) {
            throw ValidationException::withMessages([
                'vacancy' => __('An application has already been written for this vacancy.'),
            ]);
        }

        $application = new Application;

        foreach (self::COPIED_ATTRIBUTES as $attribute) {
            $application->{$attribute} = $vacancy->{$attribute};
        }

        $application->forceFill([
            ...$attributes,
            'vacancy_id' => $vacancy->id,
            'user_id' => $vacancy->user_id,
        ])->save();

        return $application;
    }
}
