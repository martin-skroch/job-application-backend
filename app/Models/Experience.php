<?php

namespace App\Models;

use App\Models\Scopes\OwnerScope;
use App\Models\Scopes\ActiveScope;
use App\Observers\ExperienceObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ScopedBy([OwnerScope::class])]
#[ScopedBy([ActiveScope::class])]
#[ObservedBy([ExperienceObserver::class])]
class Experience extends Model
{
    /** @use HasFactory<\Database\Factories\ExperienceFactory> */
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'position',
        'institution',
        'location',
        'type',
        'entry',
        'exit',
        'description',
        'active',
    ];

    // Global Scope
    protected static function booted()
    {
        static::addGlobalScope('sortByEntry', function (Builder $query) {
            $query->orderBy('entry', 'desc');
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entry' => 'date',
            'exit' => 'date',
            'active' => 'boolean',
        ];
    }

    /**
     * Get the duration between entry and exit
     *
     * @return string
     */
    public function getDurationAttribute(): string
    {
        return $this->entry->longAbsoluteDiffForHumans($this->exit?->addDay(), 2);
    }

    /**
     * Get the user that owns the experience.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)
            ->using(ExperienceSkill::class)
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
