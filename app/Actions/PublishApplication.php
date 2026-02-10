<?php

namespace App\Actions;

use App\Models\Application;
use Illuminate\Support\Str;

class PublishApplication
{
    public function handle(Application $application): void
    {
        $data = [
            'published_at' => now()
        ];

        if (Str::of($application->public_id)->isEmpty()) {
            $data['public_id'] = $this->generatePublicId();
        }

        $application->forceFill($data)->save();
    }

    private function generatePublicId(): string
    {
        $publicId = Str::random(10);

        if (Application::where('id', $publicId)->exists()) {
            $publicId = $this->generatePublicId();
        }

        return $publicId;
    }
}
