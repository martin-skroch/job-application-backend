<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $url = null;

        if ($this->path !== null && Storage::exists($this->path)) {
            $url = route('file', $this);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->whenNotNull($url),
            'mime' => $this->mime,
            'size' => Number::fileSize($this->size),
        ];
    }
}
