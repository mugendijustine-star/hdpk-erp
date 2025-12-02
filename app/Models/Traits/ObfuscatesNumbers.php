<?php

namespace App\Models\Traits;

use App\Support\NumberObfuscator;

trait ObfuscatesNumbers
{
    protected function applyObfuscation(?float $value): ?float
    {
        return NumberObfuscator::encode($value);
    }

    protected function reverseObfuscation(?float $value): ?float
    {
        return NumberObfuscator::decode($value);
    }
}
