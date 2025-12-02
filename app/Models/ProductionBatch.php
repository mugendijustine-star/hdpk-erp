<?php

namespace App\Models;

class ProductionBatch extends BaseModel
{
    /** @var array<int, ProductionOutput> */
    public array $outputs = [];
}
