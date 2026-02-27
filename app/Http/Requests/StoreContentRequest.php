<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\ImageFile;

class StoreContentRequest extends FormRequest
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
            'heading' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
            'image' => ['nullable', $this->imageRule()],
            'order' => ['nullable', 'integer', 'min:-32768', 'max:32767'],
            'active' => ['required', 'boolean'],
        ];
    }

    protected function imageRule(): ImageFile
    {
        $min = 512;
        $max = $min * 10;

        $dimensions = Rule::dimensions()
            ->minWidth($min)
            ->minHeight($min)
            ->maxWidth($max)
            ->maxHeight($max);

        return File::image()->max(10 * 1024)->dimensions($dimensions);
    }
}
