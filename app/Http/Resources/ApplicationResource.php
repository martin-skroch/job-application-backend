<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->whenNotNull($this->profile);
        $experiences = $this->whenNotNull($this->profile?->experiences);
        $skills = $this->whenNotNull($this->profile?->skills);

        return [
            'id' => $this->id,
            'title' => $this->whenHas('title'),
            'profile' => new ProfileResource($profile),
            'experiences' => ExperienceResource::collection($experiences),
            'skills' => SkillResource::collection($skills),
        ];
    }
}
