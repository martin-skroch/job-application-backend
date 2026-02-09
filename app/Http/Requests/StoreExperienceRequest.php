<?php

namespace App\Http\Requests;

use App\Models\Profile;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;

class StoreExperienceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Profile $profile): array
    {
        $date = Rule::date()->format('Y-m-d');

        $skillsExistsRule = Rule::exists('skills', 'id')
            ->where(function(Builder $query) use ($profile): void {
                $query->where('profile_id', $profile->id);
            })
        ;

        return [
            'entry' => ['required', $date],
            'exit' => ['nullable', $date],
            'institution' => ['nullable', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'office' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'skills' => ['array'],
            'skills.*' => ['required', 'ulid', $skillsExistsRule],
            'description' => ['nullable', 'string'],
            'active' => ['required', 'boolean'],
        ];
    }
}
