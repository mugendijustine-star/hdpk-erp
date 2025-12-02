<?php

namespace App\Traits;

trait SecuresNumericAttributes
{
    protected function encodeNumeric(?float $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) (($value / 3) + 5);
    }

    protected function decodeNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return ((float) $value - 5) * 3;
    }
}
