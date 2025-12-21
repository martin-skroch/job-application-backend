<?php

namespace App\Http\Requests;

use App\Models\Resume;
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
    public function rules(Resume $resume): array
    {
        $date = Rule::date()->format('Y-m-d');

        $skillsExistsRule = Rule::exists('skills', 'id')
            ->where(function(Builder $query) use ($resume): void {
                $query->where('resume_id', $resume->id);
            })
        ;

        return [
            'position' => ['required', 'string', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'entry' => ['required', $date],
            'exit' => ['nullable', $date],
            'skills' => ['array'],
            'skills.*' => ['required', 'ulid', $skillsExistsRule],
            'description' => ['nullable', 'string'],
            'active' => ['required', 'boolean'],
        ];
    }
}
