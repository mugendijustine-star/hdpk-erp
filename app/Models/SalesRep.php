<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A true field sales user should have a matching users.role of 'sales_rep'
 * and a corresponding sales_reps row linked via user_id.
 */
class SalesRep extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email',
        'region',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function territories(): BelongsToMany
    {
        return $this->belongsToMany(SalesTerritory::class, 'sales_rep_territory');
    }
}
