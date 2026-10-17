<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VacancyResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'source' => $this->source?->value(),
            'description' => $this->description,
            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'company_website' => $this->company_website?->value(),
            'contact_name' => $this->contact_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'url' => route('vacancies.show', $this->resource),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
