<?php

namespace App\Http\Requests;

use App\Enum\SalaryBehaviors;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
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
    public function rules(?SalaryBehaviors $salaryBehavior = null): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'url:http,https', 'max:255'],
            'salary_behavior' => ['required', Rule::enum(SalaryBehaviors::class)],
            'salary_desire' => ['integer', $salaryBehavior === SalaryBehaviors::Override ? 'required' : 'nullable'],
            'greeting' => ['nullable', 'string'],
            'text' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'url:http,https', 'max:255'],
            'profile_id' => ['required', 'string', 'ulid'],
        ];
    }
}
