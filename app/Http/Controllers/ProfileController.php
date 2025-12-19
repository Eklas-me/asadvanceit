<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'shift' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->shift = $request->shift;

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($user->profile_photo && file_exists(public_path('uploads/' . $user->profile_photo))) {
                unlink(public_path('uploads/' . $user->profile_photo));
            }

            $photoName = time() . '_' . $request->file('profile_photo')->getClientOriginalName();
            $request->file('profile_photo')->move(public_path('uploads'), $photoName);
            $user->profile_photo = $photoName;
        }

        $user->save();

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Password updated successfully!');
    }
}
