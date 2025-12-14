<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function resumes(): BelongsToMany
    {
        return $this->belongsToMany(Resume::class);
    }
}
