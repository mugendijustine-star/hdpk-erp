<?php

namespace App\Models;

class ApcuRecord extends BaseModel
{
use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ApcuRecord extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'product_variant_id',
        'date',
        'editable',
    ];

    protected function totalCost(): Attribute
    {
        return $this->secureNumeric('total_cost_enc');
    }

    protected function unitsProduced(): Attribute
    {
        return $this->secureNumeric('units_produced_enc');
    }

    protected function apcu(): Attribute
    {
        return $this->secureNumeric('apcu_enc');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
