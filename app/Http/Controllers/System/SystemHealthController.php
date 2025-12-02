<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SystemHealthController extends Controller
{
    public function index()
    {
        $dbStatus = 'ok';

        try {
            DB::select('select 1');
        } catch (\Throwable $th) {
            $dbStatus = $th->getMessage();
            Log::error('Health check database connection failed.', ['exception' => $th]);
        }

        $storageStatus = 'ok';
        $tempFilePath = storage_path('app/health_check.tmp');

        try {
            File::put($tempFilePath, 'ok');
            File::delete($tempFilePath);
        } catch (\Throwable $th) {
            $storageStatus = $th->getMessage();
            Log::error('Health check storage test failed.', ['exception' => $th]);
        }

        return response()->json([
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'php_version' => PHP_VERSION,
            'laravel_version' => Application::VERSION,
            'db_status' => $dbStatus,
            'storage_status' => $storageStatus,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
