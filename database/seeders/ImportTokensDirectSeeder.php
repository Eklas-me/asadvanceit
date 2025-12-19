<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportTokensDirectSeeder extends Seeder
{
    /**
     * Import tokens using MySQL native import for better parsing
     */
    public function run(): void
    {
        $sqlFile = base_path('../database/live_tokens.sql');

        if (!file_exists($sqlFile)) {
            $this->command->error("SQL file not found: {$sqlFile}");
            return;
        }

        $this->command->info("Step 1: Creating temporary table...");

        // Create temp table with same structure
        DB::statement('DROP TABLE IF EXISTS live_tokens_temp');
        DB::statement('
            CREATE TABLE live_tokens_temp (
                id INT PRIMARY KEY,
                user_id INT,
                admin_id INT NULL,
                user_name VARCHAR(255),
                live_token LONGTEXT,
                user_type ENUM("admin", "user"),
                insert_time TIMESTAMP
            )
        ');

        $this->command->info("Step 2: Importing to temp table using native MySQL...");

        // Use MySQL native import (much more robust)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Read and execute the SQL file directly
        $sqlContent = file_get_contents($sqlFile);

        // Replace table name in INSERT statements
        $sqlContent = str_replace('INSERT INTO `live_tokens`', 'INSERT INTO `live_tokens_temp`', $sqlContent);

        // Split by INSERT statements and execute in chunks
        $statements = preg_split('/INSERT INTO `live_tokens_temp`/i', $sqlContent);
        $inserted = 0;

        foreach ($statements as $index => $statement) {
            if ($index === 0 || empty(trim($statement)))
                continue;

            $fullStatement = 'INSERT INTO `live_tokens_temp`' . $statement;

            try {
                DB::unprepared($fullStatement);
                $inserted++;
                if ($inserted % 10 == 0) {
                    $this->command->info("Processed {$inserted} INSERT statements...");
                }
            } catch (\Exception $e) {
                $this->command->warn("Failed to execute statement {$inserted}: " . substr($e->getMessage(), 0, 100));
            }
        }

        $this->command->info("Step 3: Mapping user IDs...");

        // Build user ID mapping
        $userMapping = DB::table('users')
            ->whereNotNull('old_id')
            ->pluck('id', 'old_id')
            ->toArray();

        $this->command->info("Found " . count($userMapping) . " users to map");

        $this->command->info("Step 4: Migrating from temp table to live_tokens...");

        $totalInserted = 0;
        $totalSkipped = 0;

        // Process in chunks
        DB::table('live_tokens_temp')->orderBy('id')->chunk(1000, function ($tokens) use ($userMapping, &$totalInserted, &$totalSkipped) {
            $batch = [];

            foreach ($tokens as $token) {
                // Map old user IDs to new user IDs
                $newUserId = isset($userMapping[$token->user_id]) ? $userMapping[$token->user_id] : null;
                $newAdminId = $token->admin_id && isset($userMapping[$token->admin_id])
                    ? $userMapping[$token->admin_id]
                    : null;

                // Skip if user doesn't exist (those 19 users)
                if (!$newUserId) {
                    $totalSkipped++;
                    continue;
                }

                $batch[] = [
                    'user_id' => $newUserId,
                    'admin_id' => $newAdminId,
                    'user_name' => $token->user_name,
                    'live_token' => $token->live_token,
                    'user_type' => $token->user_type,
                    'insert_time' => $token->insert_time,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($batch)) {
                DB::table('live_tokens')->insert($batch);
                $totalInserted += count($batch);
                $this->command->info("Migrated {$totalInserted} tokens (Skipped: {$totalSkipped})");
            }
        });

        $this->command->info("Step 5: Cleanup...");
        DB::statement('DROP TABLE live_tokens_temp');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info("✓ Direct import complete!");
        $this->command->info("Total migrated: {$totalInserted}");
        $this->command->info("Total skipped (users don't exist): {$totalSkipped}");
    }
}
