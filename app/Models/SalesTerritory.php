<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SalesTerritory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function salesReps(): BelongsToMany
    {
        return $this->belongsToMany(SalesRep::class, 'sales_rep_territory');
    }
}
