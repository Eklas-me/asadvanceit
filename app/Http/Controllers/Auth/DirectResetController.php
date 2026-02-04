<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DirectResetController extends Controller
{
    public function showEmailForm()
    {
        return view('auth.passwords.direct-reset-email');
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Insecurely store email in session to allow password reset
        session(['reset_email' => $request->email]);

        return redirect()->route('password.reset.form');
    }

    public function showPasswordForm()
    {
        if (!session('reset_email')) {
            return redirect()->route('password.request')->withErrors(['email' => 'Session expired. Please enter email again.']);
        }

        return view('auth.passwords.direct-reset-password', ['email' => session('reset_email')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        $email = session('reset_email');

        if (!$email) {
            return redirect()->route('password.request')->withErrors(['email' => 'Session expired.']);
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            // Clear session
            session()->forget('reset_email');

            return redirect()->route('login')->with('success', 'Password reset successfully.');
        }

        return back()->withErrors(['email' => 'User not found.']);
    }
}
