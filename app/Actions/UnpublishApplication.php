<?php

namespace App\Actions;

use App\Models\Application;

class UnpublishApplication
{
    public function handle(Application $application): void
    {
        if (!$application->isPublic()) {
            return;
        }

        $application->forceFill([
            'published_at' => null,
        ])->save();
    }
}
