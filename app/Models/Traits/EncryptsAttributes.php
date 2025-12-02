<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Crypt;

trait EncryptsAttributes
{
    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @return string|null
     */
    protected function encryptValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Crypt::encryptString($value);
    }

    /**
     * Decrypt the given value.
     *
     * @param  mixed  $value
     * @return string|null
     */
    protected function decryptValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Crypt::decryptString($value);
    }
}
