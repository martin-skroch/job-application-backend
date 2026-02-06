<?php

namespace App\Observers;

use App\Models\Impression;
use Illuminate\Support\Facades\Auth;

class ImpressionObserver
{
    /**
     * Handle the Impression "creating" event.
     */
    public function creating(Impression $impression): void
    {
        if ($impression->user_id === null) {
            $impression->user_id = Auth::user()?->id;
        }
    }
}
