<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ExperienceSkill extends Pivot
{
    public $timestamps = false;

    protected $fillable = [
        'experience_id',
        'skill_id',
        'order',
    ];
}
