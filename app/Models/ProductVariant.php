<?php

namespace App\Models;

class ProductVariant extends BaseModel
{
    public function setCost(float $value): void
    {
        $this->attributes['cost'] = $value;
    }
}
