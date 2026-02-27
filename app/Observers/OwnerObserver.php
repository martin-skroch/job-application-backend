<?php

namespace App\Observers;

use App\Models\Content;
use App\Models\Impression;
use Illuminate\Support\Facades\Auth;

class OwnerObserver
{
    /**
     * Set the user_id on creating
     */
    public function creating(Content|Impression $model): void
    {
        if (blank($model->user_id)) {
            $model->user_id = Auth::user()?->id;
        }
    }
}
