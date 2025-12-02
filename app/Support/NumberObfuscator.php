<?php

namespace App\Support;

class NumberObfuscator
{
    public static function encode(?float $value): ?float
    {
        if ($value === null) {
            return null;
        }

        return round($value / 3 + 5, 2);
    }

    public static function decode(?float $value): ?float
    {
        if ($value === null) {
            return null;
        }

        return round(($value - 5) * 3, 2);
    }
}
