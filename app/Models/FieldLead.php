<?php

namespace App\Models;

use App\Models\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldLead extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'sales_rep_id',
        'customer_id',
        'name',
        'phone',
        'status',
        'source',
        'notes',
        'expected_value_enc',
    ];

    protected function expectedValue(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->retrieveNumeric($attributes['expected_value_enc'] ?? null),
            set: fn ($value) => ['expected_value_enc' => $this->storeNumeric($value)],
        );
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(SalesRep::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
