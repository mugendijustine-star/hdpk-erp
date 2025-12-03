<?php

use Illuminate\Support\Facades\Route;

Route::get('/favicon.ico', function () {
    $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#2563eb" />
            <stop offset="100%" stop-color="#22c55e" />
        </linearGradient>
    </defs>
    <rect width="64" height="64" rx="12" fill="url(#grad)" />
    <path d="M18 42c0-9 6-17 14-17s14 8 14 17" fill="none" stroke="#e5e7eb" stroke-width="5" stroke-linecap="round" />
    <circle cx="24" cy="27" r="4" fill="#e5e7eb" />
    <circle cx="40" cy="27" r="4" fill="#e5e7eb" />
    <path d="M26 46h12" stroke="#e5e7eb" stroke-width="5" stroke-linecap="round" />
</svg>
SVG;

    return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
});

Route::view('/', 'welcome');
