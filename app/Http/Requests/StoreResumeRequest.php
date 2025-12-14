<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreResumeRequest extends FormRequest
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
        $dimensions = Rule::dimensions()
            ->minWidth(256)
            ->minHeight(256)
            ->maxWidth(1024)
            ->maxHeight(1024)
        ;

        $image = File::image()
            ->max(5 * 1024)
            ->dimensions($dimensions)
        ;

        $date = Rule::date()->format('Y-m-d');

        return [
            'image' => ['nullable', $image],
            'name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'post_code' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', $date, 'max:255'],
            'birthplace' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'website' => ['nullable', 'url:http,https'],
        ];
    }
}
