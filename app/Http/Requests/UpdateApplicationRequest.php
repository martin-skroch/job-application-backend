<?php

namespace App\Http\Requests;

use App\Enum\SalaryBehaviors;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateApplicationRequest extends StoreApplicationRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(?SalaryBehaviors $salaryBehavior = null): array
    {
        return [
            ...parent::rules($salaryBehavior),
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'url:http,https', 'max:255'],
        ];
    }
}
