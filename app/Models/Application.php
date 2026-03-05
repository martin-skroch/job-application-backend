<?php

namespace App\Models;

use App\Enum\ApplicationStatus;
use App\Enum\FormOfAddress;
use App\Enum\SalaryBehaviors;
use App\Policies\ApplicationPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsUri;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[UsePolicy(ApplicationPolicy::class)]
class Application extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationFactory> */
    use HasFactory;

    use HasUlids;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'source',
        'description',
        'contact_name',
        'contact_email',
        'contact_phone',
        'company_name',
        'company_address',
        'company_website',
        'form_of_address',
        'earliest_entry_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'title' => 'string',
            'source' => AsUri::class,
            'description' => 'string',
            'form_of_address' => FormOfAddress::class,
            'salary_behavior' => SalaryBehaviors::class,
            'salary_desire' => 'integer',
            'contact_name' => 'string',
            'contact_email' => 'string',
            'contact_phone' => 'string',
            'company_name' => 'string',
            'company_address' => 'string',
            'company_website' => AsUri::class,
            'published_at' => 'datetime',
            'earliest_entry_date' => 'date',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('sortByEntry', function (Builder $query) {
            $query->latest();
        });
    }

    /**
     * Check if the application is public
     */
    public function isPublic(): bool
    {
        if (Str::of($this->public_id)->isEmpty()) {
            return false;
        }

        if (! $this->published_at instanceof Carbon) {
            return false;
        }

        return true;
    }

    public function isArchived(): bool
    {
        return $this->deleted_at !== null;
    }

    /**
     * Get the user that owns the experience.
     */
    public function getMapUrlAttribute(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * Get the analytics for this application.
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(Analytics::class);
    }

    /**
     * Get the history entries for this application.
     */
    public function history(): HasMany
    {
        return $this->hasMany(ApplicationHistory::class)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Get the most recent history entry that has a status set.
     */
    public function latestStatusEntry(): HasOne
    {
        return $this->hasOne(ApplicationHistory::class)
            ->whereNotNull('status')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Get the current (latest) status of this application.
     */
    public function status(): ?ApplicationStatus
    {
        return $this->latestStatusEntry?->status;
    }
}
