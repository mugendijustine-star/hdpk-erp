<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_rep_id',
        'customer_id',
        'sales_territory_id',
        'date',
        'check_in_time',
        'check_out_time',
        'purpose',
        'notes',
        'latitude',
        'longitude',
        'created_by',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
