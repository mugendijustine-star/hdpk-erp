<?php

namespace Illuminate\Http;

class Request
{
    /** @var array<string, mixed> */
    protected array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /** @return mixed */
    public function input(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->data;
    }
}
