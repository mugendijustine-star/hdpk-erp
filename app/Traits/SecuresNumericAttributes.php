<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait SecuresNumericAttributes
{
    protected function encryptNumeric(?float $value): ?string
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

        return (string) ($value / 3 + 5);
    }

    protected function decryptNumeric(?string $value): ?float
        $numeric = (float) $value;
        $obfuscated = ($numeric / 3) + 5;

        return base64_encode((string) $obfuscated);
    }

    protected function retrieveNumeric(?string $value): ?float
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
