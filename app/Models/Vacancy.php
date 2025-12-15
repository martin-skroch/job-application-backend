<?php

namespace App\Models;

use App\Casts\Salary;
use App\Enum\Workplace;
use App\Enum\SalaryPeriod;
use Illuminate\Support\Str;
use App\Policies\VacancyPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;

#[UsePolicy(VacancyPolicy::class)]
class Vacancy extends Model
{
    /** @use HasFactory<\Database\Factories\VacancyFactory> */
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
        'content',
        'salary_period',
        'salary_min',
        'salary_max',
        'workplace',
        'weekhours',
        'location',
        'source',
        'company',
        'address',
        'contact',
        'website',
        'email',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'salary_period' => SalaryPeriod::class,
            'salary_mine' => 'integer',
            'salary_max' => 'integer',
            'salary' => Salary::class,
            'workplace' => AsEnumCollection::of(Workplace::class),
        ];
    }

    /**
     * Get the user's first name.
     */
    protected function website(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): string|null {
                if (empty($value)) {
                    return null;
                }

                return route('redirect', ['url' => $value]);
            },
        );
    }

    protected function workplaceFormatted(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->workplace) {
                return null;
            }

            return $this->workplace
                ->map(fn(Workplace $w): string|null => __(Str::title($w->name)))
                ->join(', ');
        });
    }

    /**
     * Get the user that owns the resume.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
