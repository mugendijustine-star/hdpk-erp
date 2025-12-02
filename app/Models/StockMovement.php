<?php

namespace App\Models;

class StockMovement
{
    public int $id;
    public int $product_variant_id;
    public ?int $branch_id;
    public string $type;
    public float $qty_change;
    public float $qty_change_enc;
    public ?float $unit_cost = null;
    public ?float $unit_cost_enc = null;
    public string $reference;
    public int $user_id;

    public static function create(array $attributes): self
    {
        $instance = new self();
        $instance->product_variant_id = $attributes['product_variant_id'];
        $instance->branch_id = $attributes['branch_id'] ?? null;
        $instance->type = $attributes['type'];
        $instance->setQtyChangeAttribute($attributes['qty_change']);
        $instance->setUnitCostAttribute($attributes['unit_cost'] ?? null);
        $instance->reference = $attributes['reference'];
        $instance->user_id = $attributes['user_id'];
        $instance->id = random_int(1, 1000000);

        return $instance;
    }

    public function setQtyChangeAttribute(float $value): void
    {
        $this->qty_change = $value;
        $this->qty_change_enc = $this->encodeNumeric($value);
    }

    public function setUnitCostAttribute(?float $value): void
    {
        $this->unit_cost = $value;
        $this->unit_cost_enc = $value === null ? null : $this->encodeNumeric($value);
    }

    protected function encodeNumeric(float $value): float
    {
        return ($value / 3) + 5;
    }
}
