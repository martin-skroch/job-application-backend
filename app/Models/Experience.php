<?php

namespace App\Models;

use App\Enum\ExperienceType;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\OwnerScope;
use App\Observers\ExperienceObserver;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[ObservedBy([ExperienceObserver::class])]
#[ScopedBy([OwnerScope::class])]
#[ScopedBy([ActiveScope::class])]
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
        'entry',
        'exit',
        'institution',
        'position',
        'location',
        'office',
        'type',
        'description',
        'active',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
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
            'type'=> ExperienceType::class,
            'active' => 'boolean',
        ];
    }

    /**
     * Get the duration between entry and exit
     *
     * @return string
     */
    public function getFromToAttribute(): string
    {
        $value = $this->entry->format('m/Y');

        if ($this->exit instanceof Carbon) {
            $value .= ' - ' . $this->exit->format('m/Y');
        } else {
            $value .= ' - ' . __('Today');
        }

        return $value;
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

    /**
     * Get the profile for this application.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the file for this application.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Get the skills for this application.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)
            ->using(ExperienceSkill::class)
            ->withPivot('order')
            ->orderByPivot('order');
    }
}
