<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ContentResource extends JsonResource
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
            'name' => $this->name,
            'heading' => $this->heading,
            'text' => $this->text,
            'image' => $image,
        ];
    }
}
