<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'position' => $this->position,
            'institution' => $this->institution,
            'location' => $this->location,
            'type' => $this->type,
            'entry' => $this->entry,
            'exit' => $this->exit,
            'duration' => $this->duration,
            'skills' => SkillResource::collection($this->skills),
            'description' => $this->description,
        ];
    }
}
