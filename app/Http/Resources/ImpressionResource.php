<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class ImpressionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = $this->image;

        if ($image !== null && Storage::exists($image)) {
            $image = Storage::url($image);
        }

        return [
            'id' => $this->id,
            'image' => $image,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
