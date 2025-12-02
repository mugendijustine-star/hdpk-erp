<?php

namespace App\Models;

use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use SecuresNumericAttributes;

    protected $fillable = [
        'product_variant_id',
        'branch_id',
        'type',
        'qty_change_enc',
        'unit_cost_enc',
        'reference',
        'user_id',
    ];

    protected function qtyChange(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->retrieveNumeric($attributes['qty_change_enc'] ?? null),
            set: fn ($value) => ['qty_change_enc' => $this->storeNumeric($value)],
        );
    }

    protected function unitCost(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->retrieveNumeric($attributes['unit_cost_enc'] ?? null),
            set: fn ($value) => ['unit_cost_enc' => $this->storeNumeric($value)],
        );
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
