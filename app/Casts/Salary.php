<?php

namespace App\Casts;

use App\Enum\SalaryPeriod;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Salary implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (!$model instanceof Vacancy) {
            return new SalaryValueObject(null, null);
        }

        $salary = collect([$model->salary_min, $model->salary_max])->filter();

        switch ($model->salary_period) {
            case SalaryPeriod::Monthly:
                $monthly = $salary->join(' - ');
                $yearly  = $salary->map(fn ($v) => round($v * 12))->join(' - ');
                break;

            case SalaryPeriod::Yearly:
                $monthly = $salary->map(fn (int $v) => round($v / 12))->join(' - ');
                $yearly = $salary->join(' - ');
                break;

            default:
                $monthly = null;
                $yearly = null;
        }

        return new SalaryValueObject($monthly, $yearly);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        return [];
    }
}
