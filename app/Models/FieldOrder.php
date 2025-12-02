<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_rep_id',
        'customer_id',
        'sales_territory_id',
        'status',
        'requested_date',
        'notes',
        'manager_id',
        'approved_at',
        'assigned_clerk_id',
        'dispatched_by',
        'dispatched_at',
        'sale_id',
    ];

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(SalesRep::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(SalesTerritory::class, 'sales_territory_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function assignedClerk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_clerk_id');
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(FieldOrderLine::class);
    }
}
