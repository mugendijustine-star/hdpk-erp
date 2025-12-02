<?php

namespace App\Traits;

trait SecuresNumericAttributes
{
    protected function storeNumeric(float|int|string|null $value): ?string
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
    }
}
