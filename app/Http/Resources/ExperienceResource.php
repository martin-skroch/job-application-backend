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
        $skills = $this->whenNotNull($this->skills);

        return [
            'id' => $this->id,
            'entry' => $this->entry,
            'exit' => $this->exit,
            'institution' => $this->institution,
            'position' => $this->position,
            'location' => $this->location,
            'office' => $this->office,
            'duration' => $this->duration,
            'description' => $this->description,
            'skills' => SkillResource::collection($skills),
        ];
    }
}
