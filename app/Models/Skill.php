<?php

namespace App\Models;

use App\Observers\SkillObserver;
use App\Models\Scopes\OrderScope;
use App\Models\Scopes\OwnerScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ScopedBy([OwnerScope::class, OrderScope::class])]
#[ObservedBy([SkillObserver::class])]
class Skill extends Model
{
    /** @use HasFactory<\Database\Factories\SkillFactory> */
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'info',
        'rating',
        'order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'order' => 'integer',
        ];
    }

    /**
     * Get the duration between entry and exit
     *
     * @return string
     */
    public function getRatingInPercentAttribute(): string
    {
        return round($this->rating / 6 * 100);
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function experiences(): BelongsToMany
    {
        return $this->belongsToMany(Experience::class)
            ->using(ExperienceSkill::class)
            ->withPivot('order');
    }
}
