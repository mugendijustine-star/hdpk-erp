<?php

namespace App\Models;

class BaseModel
{
    protected static int $increment = 1;

    /** @var array<string, mixed> */
    protected array $attributes = [];

    public int $id;

    public function __construct(array $attributes = [])
    {
        $this->id = static::$increment++;
        $this->attributes = $attributes;
    }

    public static function create(array $attributes): static
    {
        return new static($attributes);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['id' => $this->id] + $this->attributes;
    }
}
