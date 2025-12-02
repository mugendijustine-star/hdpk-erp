<?php

use App\Http\Controllers\Manufacturing\ProductionController;
use Illuminate\Support\Facades\Route;

Route::post('/production-batches', [ProductionController::class, 'store']);
