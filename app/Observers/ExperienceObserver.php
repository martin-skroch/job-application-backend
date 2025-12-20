<?php

namespace App\Observers;

use App\Models\Experience;
use Illuminate\Support\Facades\Auth;

class ExperienceObserver
{
    /**
     * Handle the Experience "creating" event.
     */
    public function creating(Experience $experience): void
    {
        if ($experience->user_id === null) {
            $experience->user_id = Auth::user()?->id;
        }
    }
}
