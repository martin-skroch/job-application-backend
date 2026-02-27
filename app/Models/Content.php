<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\OrderScope;
use App\Models\Scopes\OwnerScope;
use App\Observers\OwnerObserver;
use App\Policies\ContentPolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([OwnerObserver::class])]
#[UsePolicy(ContentPolicy::class)]
#[ScopedBy([OwnerScope::class, OrderScope::class, ActiveScope::class])]
class Content extends Model
{
    /** @use HasFactory<\Database\Factories\ContentFactory> */
    use HasFactory;

    use HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'heading',
        'text',
        'image',
        'order',
        'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'string',
            'heading' => 'string',
            'text' => 'string',
            'image' => 'string',
            'order' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the experience.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the profile for that content.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
