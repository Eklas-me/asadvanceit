<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Auth\MD5UserProvider;
use Illuminate\Support\Facades\Hash;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Extend the default Eloquent user provider to support MD5
        Auth::provider('eloquent-md5', function ($app, array $config) {
            return new MD5UserProvider($app['hash'], $config['model']);
        });
    }
}
