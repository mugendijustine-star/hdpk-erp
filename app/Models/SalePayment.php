<?php

namespace App\Models;

class SalePayment
{
    public int $id;
    public int $sale_id;
    public string $method;
    public float $amount;
    public float $amount_enc;

    public static function create(array $attributes): self
    {
        $instance = new self();
        $instance->sale_id = $attributes['sale_id'];
        $instance->method = $attributes['method'];
        $instance->setAmountAttribute($attributes['amount']);
        $instance->id = random_int(1, 1000000);

        return $instance;
    }

    public function setAmountAttribute(float $value): void
    {
        $this->amount = $value;
        $this->amount_enc = $this->encodeNumeric($value);
    }

    protected function encodeNumeric(float $value): float
    {
        return ($value / 3) + 5;
    }
}
