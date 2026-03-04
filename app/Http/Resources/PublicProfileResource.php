<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

use function is_string;

class PublicProfileResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = null;
        $phone = null;
        $email = null;

        if (filled($this->image) && Storage::exists($this->image)) {
            $image = Storage::url($this->image);
        }

        if (filled($this->phone)) {
            $phone = base64_encode('tel:'.$this->phone);
        }

        if (filled($this->email)) {
            $email = base64_encode('mailto:'.$this->email);
        }

        return [
            'image' => $image,
            'name' => $this->name,
            'phone' => $phone,
            'email' => $email,
            'skills' => SkillResource::collection($this->whenLoaded('skills')),
        ];
    }
}
