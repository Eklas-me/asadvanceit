<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MagicLoginToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AgentAuthController extends Controller
{
    /**
     * Authenticate agent and return magic login URL
     */
    public function login(Request $request)
    {
        \Log::info('Agent login attempt', ['email' => $request->email, 'version' => $request->agent_version]);
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'agent_version' => 'nullable|string',
        ]);

        $requiredVersion = '1.0.2';
        $agentVersion = $request->input('agent_version', '1.0.0'); // Default to 1.0.0 if not provided

        if (version_compare($agentVersion, $requiredVersion, '<')) {
            return response()->json([
                'success' => false,
                'message' => "Update Required! You are using v{$agentVersion}. Please download and install the latest version (v{$requiredVersion} or higher) to continue.",
            ], 426); // 426 Upgrade Required
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email.',
            ], 404);
        }

        // Check password (supports both bcrypt and MD5 via custom provider)
        $passwordValid = false;

        if ($user->needs_password_upgrade) {
            // MD5 password check
            $passwordValid = hash_equals(md5($request->password), $user->password);
        } else {
            // Bcrypt password check
            $passwordValid = Hash::check($request->password, $user->password);
        }

        if (!$passwordValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password.',
            ], 401);
        }

        // Check if user is active (or admin)
        if ($user->status !== 'active' && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is pending approval.',
            ], 403);
        }

        // Generate magic login token
        $token = Str::random(64);

        MagicLoginToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addMinutes(1), // 1 minute expiry
        ]);

        // Return magic link URL
        $magicUrl = url('/magic-login/' . $token);

        // Broadcast force logout event to connected clients on the old token/channel
        broadcast(new \App\Events\ForceLogoutEvent($user->id));

        // Delete all existing tokens to enforce single-device login
        $user->tokens()->delete();

        // Generate Sanctum API Token for the Agent
        $apiToken = $user->createToken('agent-app')->plainTextToken;

        // Send Telegram notification (async-like, won't block response significantly)
        try {
            if (!$user->is_core_admin) {
                $agent = new \Jenssegers\Agent\Agent();
                $loginData = [
                    'ip' => $request->ip(),
                    'device' => $request->input('device_name') ?: ($agent->device() ?: 'Unknown Device'),
                    'browser' => $request->input('browser') ?: 'Advance IT Client',
                    'platform' => $request->input('os_info') ?: ($agent->platform() ?: 'Windows'),
                    'location' => $this->getLocation($request->ip()),
                ];

                $telegramService = app(\App\Services\TelegramService::class);
                $telegramService->sendLoginNotification($user, $loginData);
            }
        } catch (\Exception $e) {
            \Log::error('Agent login notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'magic_url' => $magicUrl,
            'access_token' => $apiToken,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Get location from IP address
     */
    protected function getLocation($ip)
    {
        try {
            if ($ip === '127.0.0.1' || $ip === '::1') {
                return 'Local';
            }
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
}
