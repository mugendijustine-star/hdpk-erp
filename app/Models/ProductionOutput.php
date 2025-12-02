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
use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProductionOutput extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'production_batch_id',
        'product_variant_id',
        'machine_id',
        'waste_reason',
    ];

    protected function qtyGood(): Attribute
    {
        return $this->secureNumeric('qty_good_enc');
    }

    protected function qtyWaste(): Attribute
    {
        return $this->secureNumeric('qty_waste_enc');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
