<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentAuthController;
use App\Http\Controllers\Api\MonitoringController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are stateless and do not have CSRF protection.
|
*/

Route::post('/agent/login', [AgentAuthController::class, 'login']);
Route::post('/agent/heartbeat', [\App\Http\Controllers\Api\MonitoringController::class, 'heartbeat']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/agent/stream', [MonitoringController::class, 'uploadStream']);
});
