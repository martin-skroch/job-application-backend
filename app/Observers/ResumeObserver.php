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
        $resume->user_id = Auth::user()->id;
        $resume->token = $this->generateToken();
    }

    /**
     * Handle the Resume "updated" event.
     */
    public function retrieved(Resume $resume): void
    {
        if ($resume->token === null) {
            $resume->token = $this->generateToken();
            $resume->save();
        }
    }

    private function generateToken(): string
    {
        return Str::random(64);
    }
}
