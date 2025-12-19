<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UpgradeMD5Password
{
    /**
     * Handle an incoming request.
     *
     * After successful login, check if user has MD5 password and upgrade it
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only process after successful authentication
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user needs password upgrade
            if ($user->needs_password_upgrade && $request->filled('password')) {
                // Get the plain password from the request (only available during login)
                $plainPassword = $request->input('password');

                // Upgrade to bcrypt
                $user->password = Hash::make($plainPassword);
                $user->needs_password_upgrade = false;
                $user->save();

                \Log::info("Upgraded MD5 password to bcrypt for user: {$user->email}");
            }
        }

        return $response;
    }
}
