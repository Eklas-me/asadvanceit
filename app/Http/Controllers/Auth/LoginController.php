<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt to authenticate
        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            // Check if user status is active
            if (Auth::user()->status !== 'active' && Auth::user()->role !== 'admin') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Your account is pending approval by an administrator.',
                ]);
            }

            $request->session()->regenerate();

            // Trigger authenticated method for Telegram notification - DO NOT REMOVE
            $this->authenticated($request, Auth::user());

            return redirect()->intended('/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => 'Invalid email or password.',
        ]);
    }

    /**
     * The user has been authenticated.
     */
    protected function authenticated(Request $request, $user)
    {
        // Skip notification for Core Admins
        if ($user->is_core_admin) {
            return;
        }

        try {
            // Capture login data
            $agent = new \Jenssegers\Agent\Agent();

            $loginData = [
                'ip' => $request->ip(),
                'device' => $agent->device() ?: 'Unknown',
                'browser' => $agent->browser() . ' ' . $agent->version($agent->browser()),
                'platform' => $agent->platform() . ' ' . $agent->version($agent->platform()),
                'location' => $this->getLocation($request->ip()),
            ];

            // Send Telegram notification (async, won't block login)
            $telegramService = app(\App\Services\TelegramService::class);
            $telegramService->sendLoginNotification($user, $loginData);
        } catch (\Exception $e) {
            // Log error but don't block login
            \Log::error('Login notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Get location from IP address
     */
    protected function getLocation($ip)
    {
        try {
            // Skip for local IPs
            if ($ip === '127.0.0.1' || $ip === '::1') {
                return 'Local';
            }

            // Use ipapi.co for geolocation
            $response = \Http::timeout(3)->get("https://ipapi.co/{$ip}/json/");

            if ($response->successful()) {
                $data = $response->json();
                return ($data['city'] ?? 'Unknown') . ', ' . ($data['country_name'] ?? 'Unknown');
            }
        } catch (\Exception $e) {
            \Log::warning('Location detection failed: ' . $e->getMessage());
        }

        return 'Unknown';
    }

    public function logout(Request $request)
    {
        // Clear last_seen to immediately show user as offline
        if (auth()->check()) {
            auth()->user()->update(['last_seen' => null]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
