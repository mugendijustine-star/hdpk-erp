<?php

namespace App\Models;

class ProductionOutput extends BaseModel
{
    public ProductionBatch $batch;

    public function __construct(array $attributes = [], ?ProductionBatch $batch = null)
    {
        parent::__construct($attributes);
        if ($batch) {
            $this->batch = $batch;
        }
    }
}
