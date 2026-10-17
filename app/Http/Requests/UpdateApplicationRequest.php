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
        return parent::rules($salaryBehavior);
    }
}
