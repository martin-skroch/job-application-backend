<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use App\Models\Scopes\OwnerScope;
use App\Observers\ProfileObserver;
use App\Policies\ProfilePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([OwnerScope::class])]
#[UsePolicy(ProfilePolicy::class)]
#[ObservedBy([ProfileObserver::class])]
class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\ResumeFactory> */
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
        'image',
        'name',
        'address',
        'post_code',
        'location',
        'birthdate',
        'birthplace',
        'phone',
        'email',
        'website',
        'api_active'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'api_active' => 'boolean',
        ];
    }

    /**
     * Make the birthdate nullable
     *
     * @return Carbon|null
     */
    public function getBirthdateAttribute($value): Carbon|null
    {
        return empty($value) ? null : Carbon::parse($value);
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    public function impressions(): HasMany
    {
        return $this->hasMany(Impression::class);
    }
}
