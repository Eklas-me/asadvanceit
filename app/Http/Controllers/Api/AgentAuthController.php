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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

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

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'magic_url' => $magicUrl,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
