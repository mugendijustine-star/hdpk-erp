<?php

namespace App\Traits;

/**
 * Encode numeric values using the project's secure numeric obfuscation rule.
 */
trait SecureNumeric
{
    /**
     * Encode numeric values so they are stored in an obfuscated/encrypted form.
     */
    protected function encodeSecureNumeric(float|int $value): float
    {
        return ($value / 3) + 5;
    }
}
