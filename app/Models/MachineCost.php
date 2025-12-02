<?php

namespace App\Models;

use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MachineCost extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'machine_id',
        'date',
        'cost_type',
        'paid_via',
        'reference',
        'user_id',
    ];

    protected function amount(): Attribute
    {
        return $this->secureNumeric('amount_enc');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
