<?php

namespace App\Http\Requests;

use App\Enum\FormOfAddress;
use App\Enum\SalaryBehaviors;
use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(?SalaryBehaviors $salaryBehavior = null): array
    {
        return [
            'form_of_address' => ['required', Rule::enum(FormOfAddress::class)],
            'salary_behavior' => ['required', Rule::enum(SalaryBehaviors::class)],
            'salary_desire' => ['integer', $salaryBehavior === SalaryBehaviors::Override ? 'required' : 'nullable'],
            'earliest_entry_date' => ['nullable', 'date'],
            'text' => ['nullable', 'string'],
            'profile_id' => ['required', 'string', 'ulid'],
        ];
    }
}
