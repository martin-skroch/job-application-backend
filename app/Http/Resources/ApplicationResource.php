<?php

namespace App\Http\Resources;

use App\Enum\ExperienceType;
use Illuminate\Http\Request;
use App\Http\Resources\ImpressionResource;
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
        $experiences = $this->whenNotNull($this->profile?->experiences(ExperienceType::Work)->get());
        $educations = $this->whenNotNull($this->profile?->experiences(ExperienceType::Education)->get());
        $skills = $this->whenNotNull($this->profile?->skills);
        $impressions = $this->whenNotNull($this->profile?->impressions);

        return [
            'id' => $this->id,
            'title' => $this->whenHas('title'),
            'profile' => new ProfileResource($profile),
            'experiences' => ExperienceResource::collection($experiences),
            'educations' => ExperienceResource::collection($educations),
            'skills' => SkillResource::collection($skills),
            'impressions' => ImpressionResource::collection($impressions),
        ];
    }
}
