<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait SecuresNumericAttributes
{
    protected function encryptNumeric(?float $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) ($value / 3 + 5);
    }

    protected function decryptNumeric(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        return ((float) $value - 5) * 3;
    }

    protected function secureNumeric(string $column): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->decryptNumeric($attributes[$column] ?? null),
            set: fn ($value) => [$column => $this->encryptNumeric($value)],
        );
    }
}
