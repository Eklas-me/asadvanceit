<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
            'phone' => '1234567890',
            'shift' => 'Day 12 Hours',
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
            'status' => 'active',
            'phone' => '0987654321',
            'shift' => 'Morning 8 Hours',
        ]);

        echo "✅ Admin and Test User created successfully!\n";
        echo "Admin: admin@test.com / admin123\n";
        echo "User: user@test.com / user123\n";
    }
}
