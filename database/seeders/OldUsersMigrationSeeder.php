<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OldUsersMigrationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting simplified user migration...');

        // Read and parse the SQL file line by line
        $sqlFile = 'E:\\Xampp\\htdocs\\asitrunning\\dashboard001\\database\\users.sql';

        if (!file_exists($sqlFile)) {
            $this->command->error("SQL file not found");
            return;
        }

        $content = file_get_contents($sqlFile);

        // Extract all INSERT VALUES
        preg_match_all('/\((\d+),\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*(?:\'([^\']*)\'|NULL),\s*(?:\'([^\']*)\'|NULL),\s*\'([^\']*)\'\)/s', $content, $matches, PREG_SET_ORDER);

        $this->command->info("Found " . count($matches) . " users to import");

        $idMapping = [];
        $bar = $this->command->getOutput()->createProgressBar(count($matches));
        $bar->start();

        foreach ($matches as $match) {
            $oldId = (int) $match[1];
            $name = $match[2];
            $email = $match[3];
            $phone = $match[4] ?: null;
            $password = $match[5];
            $profilePhoto = $match[6] ?: null;
            $createdAt = $match[7];
            $role = $match[8];
            $shift = $match[9] ?: null;
            $gender = $match[10] ?: null;
            $status = $match[11];

            // Check if MD5
            $isMD5 = strlen($password) === 32 && !str_starts_with($password, '$2y$');

            try {
                $newId = DB::table('users')->insertGetId([
                    'old_id' => $oldId,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $password,
                    'needs_password_upgrade' => $isMD5,
                    'profile_photo' => $profilePhoto,
                    'role' => 'user',
                    'shift' => $shift,
                    'gender' => $gender,
                    'status' => $this->mapStatus($status),
                    'email_verified_at' => $status === 'approved' ? now() : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $idMapping[$oldId] = $newId;
            } catch (\Exception $e) {
                $this->command->error("Error with user {$email}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();

        // Save mapping
        file_put_contents(
            storage_path('app/user_id_mapping.json'),
            json_encode($idMapping, JSON_PRETTY_PRINT)
        );

        $totalUsers = DB::table('users')->where('role', 'user')->count();
        $md5Count = DB::table('users')->where('needs_password_upgrade', true)->count();

        $this->command->info("✓ {$totalUsers} users migrated successfully");
        $this->command->warn("⚠️  {$md5Count} users have MD5 passwords");
    }

    private function mapStatus(string $oldStatus): string
    {
        return match ($oldStatus) {
            'approved' => 'active',
            'rejected' => 'rejected',
            'pending' => 'pending',
            default => 'pending',
        };
    }
}
