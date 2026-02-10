<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Policies\ApplicationPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsUri;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'notes',
        'contact_name',
        'contact_email',
        'contact_phone',
        'company_name',
        'company_address',
        'company_website',
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
            'notes' => 'string',
            'contact_name' => 'string',
            'contact_email' => 'string',
            'contact_phone' => 'string',
            'company_name' => 'string',
            'company_address' => 'string',
            'company_website' => AsUri::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * Check if the application is public
     */
    public function isPublic(): bool
    {
        return !Str::of($this->public_id)->isEmpty() && $this->published_at instanceof Carbon;
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
}
