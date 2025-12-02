<?php

namespace App\Models\Traits;

use App\Models\Traits\ObfuscatesNumbers;
use App\Models\Traits\EncryptsAttributes;

trait SecuresNumericAttributes
{
    use ObfuscatesNumbers;
    use EncryptsAttributes;

    /**
     * All money, cost, price, balance, quantity fields must use this trait.
     */
    protected function storeNumeric($value)
    {
        $obfuscated = $this->applyObfuscation($value);

        return $this->encryptValue($obfuscated);
    }

    protected function retrieveNumeric($value)
    {
        $decrypted = $this->decryptValue($value);

        return $this->reverseObfuscation($decrypted);
    }
}
