<?php

namespace App\Models;

class SaleLine
{
    public int $id;
    public int $sale_id;
    public int $product_variant_id;
    public float $qty;
    public float $qty_enc;
    public float $unit_price;
    public float $unit_price_enc;
    public float $line_total;
    public float $line_total_enc;

    public static function create(array $attributes): self
    {
        $instance = new self();
        $instance->sale_id = $attributes['sale_id'];
        $instance->product_variant_id = $attributes['product_variant_id'];
        $instance->setQtyAttribute($attributes['qty']);
        $instance->setUnitPriceAttribute($attributes['unit_price']);
        $instance->setLineTotalAttribute($attributes['line_total']);
        $instance->id = random_int(1, 1000000);

        return $instance;
    }

    public function setQtyAttribute(float $value): void
    {
        $this->qty = $value;
        $this->qty_enc = $this->encodeNumeric($value);
    }

    public function setUnitPriceAttribute(float $value): void
    {
        $this->unit_price = $value;
        $this->unit_price_enc = $this->encodeNumeric($value);
    }

    public function setLineTotalAttribute(float $value): void
    {
        $this->line_total = $value;
        $this->line_total_enc = $this->encodeNumeric($value);
    }

    protected function encodeNumeric(float $value): float
    {
        return ($value / 3) + 5;
    }
}
