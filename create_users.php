<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create 60 dummy active users
for ($i = 0; $i < 60; $i++) {
    User::create([
        'name' => "Dummy User $i",
        'email' => "dummy$i@example.com",
        'password' => Hash::make('password'),
        'phone' => "12345678$i",
        'shift' => 'Morning',
        'role' => 'user',
        'status' => 'active',
        'created_at' => now()->subMinutes($i),
    ]);
}

echo "Created 60 dummy users.";
