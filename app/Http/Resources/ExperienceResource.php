<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExperienceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $entry = $this->entry;
        $exit = $this->exit;

        $dateFormat = $request->string('date_format', 'm/Y');

        if ($dateFormat !== 'raw') {
            $entry = $entry?->format($dateFormat);
            $exit = $exit?->format($dateFormat);
        }

        return [
            'id' => $this->id,
            'position' => $this->position,
            'institution' => $this->institution,
            'location' => $this->location,
            'type' => $this->type,
            'entry' => $entry,
            'exit' => $exit,
            'duration' => $this->duration,
            'skills' => SkillResource::collection($this->skills),
            'description' => $this->description,
        ];
    }
}
