<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\ImageFile;

class StoreProfileRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', $this->imageRule()],
            'address' => ['nullable', 'string', 'max:1000'],
            'post_code' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', Rule::date()->format('Y-m-d'), 'max:255'],
            'birthplace' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'website' => ['nullable', 'url:http,https'],
            'salary_desire' => ['nullable', 'integer'],
        ];
    }

    protected function imageRule(): ImageFile
    {
        $min = 256;
        $max = $min * 4;

        $dimensions = Rule::dimensions()
            ->minWidth($min)
            ->minHeight($min)
            ->maxWidth($max)
            ->maxHeight($max)
        ;

        return File::image()->max(5 * 1024)->dimensions($dimensions);
    }
}
