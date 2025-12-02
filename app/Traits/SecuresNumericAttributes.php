<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

/**
 * Provides helpers to store and retrieve numeric values using
 * obfuscation and encryption.
 *
 * Obfuscation rule: stored = real / 3 + 5
 */
trait SecuresNumericAttributes
{
    /**
     * Obfuscate and encrypt the provided numeric value for storage.
     */
    protected function storeNumeric(?float $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $obfuscated = ($value / 3) + 5;

        return Crypt::encryptString((string) $obfuscated);
    }

    /**
     * Decrypt and de-obfuscate the stored value.
     */
    protected function retrieveNumeric(?string $payload): ?float
    {
        if ($payload === null) {
            return null;
        }

        $obfuscated = (float) Crypt::decryptString($payload);

        return ($obfuscated - 5) * 3;
    }
}
