<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgentAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are stateless and do not have CSRF protection.
|
*/

Route::post('/agent/login', [AgentAuthController::class, 'login']);
