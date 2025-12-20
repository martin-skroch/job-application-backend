<?php

namespace App\Observers;

use App\Models\Skill;
use Illuminate\Support\Facades\Auth;

class SkillObserver
{
    /**
     * Handle the Experience "creating" event.
     */
    public function creating(Skill $skill): void
    {
        if ($skill->user_id === null) {
            $skill->user_id = Auth::user()?->id;
        }
    }
}
