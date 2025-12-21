<?php

namespace App\Http\Requests;

use App\Models\Resume;

class UpdateExperienceRequest extends StoreExperienceRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Resume $resume): array
    {
        return parent::rules($resume);
    }
}
