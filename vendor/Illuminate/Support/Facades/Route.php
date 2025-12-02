<?php

namespace Illuminate\Support\Facades;

class Route
{
    /** @var array<int, array<string, mixed>> */
    public static array $routes = [];

    public static function post(string $uri, $action): void
    {
        self::$routes[] = ['method' => 'POST', 'uri' => $uri, 'action' => $action];
    }
}
