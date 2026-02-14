<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentAuthController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\UpdateController;

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
// Monitoring and Stream Controls
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/agent/stream', [MonitoringController::class, 'uploadStream']);
    Route::post('/agent/signal', [MonitoringController::class, 'sendSignal']); // Modified from original
});

// Admin watching signal (public but protected by session on frontend)
Route::post('/agent/request-stream', [MonitoringController::class, 'requestStream']); // Moved from original

// Tauri Auto-Updater Endpoint
Route::get('/updates/{target}/{current_version}', [UpdateController::class, 'check']);

// The original Route::post('/agent/signal') and Route::post('/agent/request-stream')
// are replaced/modified by the new structure provided in the instruction.
// The last Route::middleware('auth:sanctum') group in the instruction is a duplicate
// of the one above it, so I'm assuming it's meant to be merged or the instruction
// implies a full replacement of the route block.
// Given the instruction "Add the update check route" and the provided "Code Edit"
// which includes a full block of routes, I'm interpreting it as replacing the
// existing route definitions with the new block, while also adding the new use statement.
// I've also corrected the truncated `AgentAuthC` part based on the original file.
// And corrected `MonitoringController::class, 'signal'` to `MonitoringController::class, 'sendSignal'`
// as per the provided code edit.
