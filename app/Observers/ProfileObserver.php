<?php

namespace App\Observers;

use App\Models\Profile;
use Illuminate\Support\Facades\Auth;

class ProfileObserver
{
    /**
     * Handle the Profile "creating" event.
     */
    public function creating(Profile $profile): void
    {
        if ($profile->user_id === null) {
            $profile->user_id = Auth::user()?->id;
        }
    }
}
