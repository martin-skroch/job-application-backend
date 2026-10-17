<?php

namespace App\Models;

use App\Policies\VacancyPolicy;
use Database\Factories\VacancyFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsUri;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UsePolicy(VacancyPolicy::class)]
class Vacancy extends Model
{
    /** @use HasFactory<VacancyFactory> */
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
            'contact_name' => 'string',
            'contact_email' => 'string',
            'contact_phone' => 'string',
            'company_name' => 'string',
            'company_address' => 'string',
            'company_website' => AsUri::class,
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

    public function isArchived(): bool
    {
        return $this->deleted_at !== null;
    }

    /**
     * Get the user that owns the vacancy.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the application written for this vacancy.
     */
    public function application(): HasOne
    {
        return $this->hasOne(Application::class)->withTrashed();
    }
}
