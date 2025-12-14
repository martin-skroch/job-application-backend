<?php

namespace App\Http\Requests;

use App\Enum\SalaryPeriod;
use App\Enum\Workplace;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVacancyRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'salary_period' => ['nullable', Rule::enum(SalaryPeriod::class)],
            'salary_min' => ['nullable', 'integer'],
            'salary_max' => ['nullable', 'integer'],
            'workplace' => ['nullable', 'array'],
            'workplace.*' => [Rule::enum(Workplace::class)],
            'weekhours' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url:http,https'],
            'email' => ['nullable', 'email'],
        ];
    }
}
