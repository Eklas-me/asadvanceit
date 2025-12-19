<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportLegacyAdminsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Eklas Mahmud',
                'email' => 'Kh.Eklas502@gmail.com',
                'password' => '21232f297a57a5a743894a0e4a801fc3',
                'profile_photo' => '1755360895_1740768785674.jpg',
                'created_at' => '2024-10-09 06:01:50',
                'role' => 'admin',
                'phone' => '03256465246'
            ],
            [
                'name' => 'Azad',
                'email' => 'luna.125dd@gmail.com',
                'password' => 'd2a256af3d338b66912ce21958ed09b4',
                'profile_photo' => '1760091032_Screenshot_1.jpg',
                'created_at' => '2025-08-12 12:32:40',
                'role' => 'admin',
                'phone' => ''
            ],
            [
                'name' => 'Boss',
                'email' => 'abubokkrs@gmail.com',
                'password' => '55cb248e2b7453f9aaa9027c2df50598',
                'profile_photo' => '',
                'created_at' => '2025-08-12 13:38:45',
                'role' => 'admin',
                'phone' => ''
            ],
            [
                'name' => 'Rocky Badsha',
                'email' => 'rockys621997@gmail.com',
                'password' => '9a47795837d5d0cfe6bf73976791d64c',
                'profile_photo' => '1755360927_Rocky.jpg',
                'created_at' => '2025-08-12 15:19:49',
                'role' => 'admin',
                'phone' => '01619108657'
            ],
            [
                'name' => 'MD Atikur Rahaman  (Moni)',
                'email' => 'atikur.rahaman.moni@gmail.com',
                'password' => '0f3a57b6c5312da73ba3d95732a888aa',
                'profile_photo' => 'user.png',
                'created_at' => '2025-09-20 12:23:50',
                'role' => 'admin',
                'phone' => '01812007705'
            ]
        ];

        foreach ($admins as $admin) {
            $exists = User::where('email', $admin['email'])->exists();

            if (!$exists) {
                User::create([
                    'name' => $admin['name'],
                    'email' => $admin['email'],
                    'password' => $admin['password'], // Storing raw MD5 as per MD5UserProvider logic
                    'phone' => $admin['phone'],
                    'profile_photo' => $admin['profile_photo'],
                    'role' => 'admin',
                    'status' => 'approved',
                    'is_core_admin' => false,
                    'needs_password_upgrade' => true, // Flag to upgrade password on next login
                    'created_at' => $admin['created_at'],
                    'updated_at' => now(),
                ]);

                $this->command->info("Imported admin: {$admin['name']}");
            } else {
                $this->command->warn("Skipped existing user: {$admin['email']}");
            }
        }
    }
}
