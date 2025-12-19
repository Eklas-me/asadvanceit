<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CoreAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if core admin already exists
        $coreAdmin = User::where('email', 'kh.eklas502@gmail.com')->first();

        if (!$coreAdmin) {
            User::create([
                'name' => 'Core Admin',
                'email' => 'kh.eklas502@gmail.com',
                'password' => Hash::make('eklas676'),
                'role' => 'admin',
                'status' => 'approved',
                'is_core_admin' => true, // Flag to identify core admin
            ]);

            $this->command->info('Core admin created successfully!');
        } else {
            // Update existing user to be core admin
            $coreAdmin->update([
                'is_core_admin' => true,
                'role' => 'admin',
                'status' => 'approved',
            ]);

            $this->command->info('Existing user updated to core admin!');
        }
    }
}
