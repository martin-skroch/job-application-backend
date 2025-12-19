<?php

namespace App\Http\Resources;

use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = $this->image;

        if (Storage::exists($image)) {
            $image = Storage::url($image);
        }

        return [
            'id' => $this->id,
            'image' => $image,
            'name' => $this->name,
            'address' => $this->address,
            'post_code' => $this->post_code,
            'location' => $this->location,
            'birthdate' => $this->birthdate,
            'birthplace' => $this->birthplace,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'experiences' => ExperienceResource::collection($this->experiences),
            'skills' => SkillResource::collection($this->skills),
        ];
    }
}
