<?php

namespace App\Models;

class Sale
{
    public int $id;
    public ?int $branch_id;
    public ?int $customer_id;
    public string $date_time;
    public int $user_id;
    public string $status;
    public float $total;
    public float $total_enc;

    /** @var SaleLine[] */
    public array $lines = [];
    /** @var SalePayment[] */
    public array $payments = [];

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public static function create(array $attributes): self
    {
        $instance = new self();
        $instance->branch_id = $attributes['branch_id'] ?? null;
        $instance->customer_id = $attributes['customer_id'] ?? null;
        $instance->date_time = $attributes['date_time'];
        $instance->user_id = $attributes['user_id'];
        $instance->status = $attributes['status'];
        $instance->setTotalAttribute($attributes['total']);
        $instance->id = random_int(1, 1000000);

        return $instance;
    }

    public function load(array $relations): self
    {
        return $this;
    }

    public function setTotalAttribute(float $value): void
    {
        $this->total = $value;
        $this->total_enc = $this->encodeNumeric($value);
    }

    public function getTotalAttribute(): float
    {
        return $this->decodeNumeric($this->total_enc ?? 0);
    }

    protected function encodeNumeric(float $value): float
    {
        return ($value / 3) + 5;
    }

    protected function decodeNumeric(float $value): float
    {
        return ($value - 5) * 3;
use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'date_time',
        'status',
        'user_id',
        'approved_by',
        'notes',
    ];

    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->retrieveNumeric($attributes['total_enc'] ?? null),
            set: fn ($value) => ['total_enc' => $this->storeNumeric($value)],
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SaleLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }
}
