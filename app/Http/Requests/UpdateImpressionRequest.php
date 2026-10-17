<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class UpdateImpressionRequest extends StoreImpressionRequest
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
    public function rules(): array
    {
        $rules = parent::rules();

        if (isset($rules['image']) && is_array($rules['image'])) {
            $key = array_search('required', $rules['image']);
            unset($rules['image'][$key]);
            array_unshift($rules['image'], 'nullable');
        }

        return $rules;
    }
}
