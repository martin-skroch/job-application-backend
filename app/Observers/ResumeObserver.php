<?php

namespace App\Observers;

use App\Models\Resume;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ResumeObserver
{
    /**
     * Handle the Resume "creating" event.
     */
    public function creating(Resume $resume): void
    {
        $resume->api_token = $this->generateToken();

        if ($resume->user_id === null) {
            $resume->user_id = Auth::user()?->id;
        }
    }

    private function generateToken(): string
    {
        return Str::random(64);
    }
}
