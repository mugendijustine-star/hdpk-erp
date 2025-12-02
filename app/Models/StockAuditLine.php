<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAuditLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_audit_id',
        'item_id',
        'expected_qty',
        'counted_qty',
        'difference_qty',
        'loss_type',
        'responsible_user_id',
        'manager_comment',
        'admin_comment',
    ];

    public function stockAudit()
    {
        return $this->belongsTo(StockAudit::class);
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function stockMovement()
    {
        return $this->morphOne(StockMovement::class, 'movable');
    }
}
