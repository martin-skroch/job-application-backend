<?php

namespace App\Casts;

class SalaryValueObject
{
    public function __construct(
        public ?string $monthly,
        public ?string $yearly,
    ) {}

    public function __toString(): string
    {
        if ($this->monthly) {
            return "{$this->monthly} € (Monat)";
        } else {
            return $this->yearly ? "{$this->yearly} € (Jahr)" : '';
        }
    }
}
