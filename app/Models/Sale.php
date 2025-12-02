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
    }
}
