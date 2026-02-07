<?php

namespace App\Models;

use App\Models\Scopes\OrderScope;
use App\Models\Scopes\OwnerScope;
use App\Models\Scopes\ActiveScope;
use App\Policies\ApplicationPolicy;
use App\Observers\ImpressionObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ImpressionObserver::class])]
#[UsePolicy(ApplicationPolicy::class)]
#[ScopedBy([OwnerScope::class, OrderScope::class, ActiveScope::class])]
class Impression extends Model
{
    /** @use HasFactory<\Database\Factories\ImpressionFactory> */
    use HasFactory;
    use HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'image',
        'title',
        'description',
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
            'image' => 'string',
            'title' => 'string',
            'description' => 'string',
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

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
