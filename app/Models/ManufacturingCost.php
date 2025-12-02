<?php

namespace App\Models;

class ManufacturingCost extends BaseModel
{
    public static function whereDateAndBranch(string $date, ?int $branchId = null): array
    {
        // Placeholder: in a real app this would query the database
        return [];
use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ManufacturingCost extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'date',
        'branch_id',
        'type',
        'description',
        'user_id',
    ];

    protected function amount(): Attribute
    {
        return $this->secureNumeric('amount_enc');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
