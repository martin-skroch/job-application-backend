<?php

namespace App\Models;

use App\Enum\ExperienceType;
use App\Models\Scopes\OwnerScope;
use App\Observers\ProfileObserver;
use App\Policies\ProfilePolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;

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
        'salary_desire',
        'cover_letter',
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
            'salary_desire' => 'integer',
            'cover_letter' => 'string',
        ];
    }

    /**
     * Make the birthdate nullable
     */
    public function getImageUrlAttribute($value): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        try {
            return Storage::url($this->image);
        } catch (Throwable $e) {
            report($e);
            abort(404);
        }
    }

    /**
     * Make the birthdate nullable
     */
    public function getBirthdateAttribute($value): ?Carbon
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

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function experiences(?ExperienceType $type = null): HasMany
    {
        $related = $this->hasMany(Experience::class);

        if ($type instanceof ExperienceType) {
            $related->where('type', $type->value);
        }

        return $related;
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(Experience::class)->where('type', ExperienceType::Work->value);
    }

    public function educationExperiences(): HasMany
    {
        return $this->hasMany(Experience::class)->where('type', ExperienceType::Education->value);
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
