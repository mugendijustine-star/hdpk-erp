<?php

namespace App\Traits;

trait SecuresNumericAttributes
{
    protected function storeNumeric(float|int|string|null $value): ?string
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

        $numeric = (float) $value;
        $obfuscated = ($numeric / 3) + 5;

        return base64_encode((string) $obfuscated);
    }

    protected function retrieveNumeric(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $decoded = base64_decode($value, true);

        if ($decoded === false || !is_numeric($decoded)) {
            return null;
        }

        return ((float) $decoded - 5) * 3;
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
