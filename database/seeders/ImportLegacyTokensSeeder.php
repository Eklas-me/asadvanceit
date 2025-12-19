<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportLegacyTokensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlFile = base_path('../database/live_tokens.sql');

        if (!file_exists($sqlFile)) {
            $this->command->error("SQL file not found: {$sqlFile}");
            return;
        }

        $this->command->info("Building user ID mapping...");
        $userMapping = DB::table('users')
            ->whereNotNull('old_id')
            ->pluck('id', 'old_id')
            ->toArray();

        $this->command->info("Mapped " . count($userMapping) . " users");

        // Increase memory limit temporarily
        ini_set('memory_limit', '512M');

        // Disable foreign key checks for faster import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $totalInserted = 0;
        $totalSkipped = 0;
        $batch = [];
        $batchSize = 500;
        $buffer = '';
        $inInsertStatement = false;

        $this->command->info("Processing SQL file (this may take several minutes)...");

        // Open file for streaming
        $handle = fopen($sqlFile, 'r');
        if (!$handle) {
            $this->command->error("Could not open SQL file");
            return;
        }

        while (($line = fgets($handle)) !== false) {
            // Skip comments and empty lines
            if (empty(trim($line)) || strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
                continue;
            }

            // Check if this is an INSERT statement
            if (stripos($line, 'INSERT INTO `live_tokens`') !== false) {
                $inInsertStatement = true;
                $buffer = $line;
                continue;
            }

            if ($inInsertStatement) {
                $buffer .= $line;

                // Check if statement is complete
                if (strpos($line, ');') !== false) {
                    // Process this INSERT statement
                    $this->processInsertStatement($buffer, $userMapping, $batch, $totalInserted, $totalSkipped, $batchSize);
                    $buffer = '';
                    $inInsertStatement = false;
                }
            }
        }

        fclose($handle);

        // Insert remaining batch
        if (!empty($batch)) {
            DB::table('live_tokens')->insert($batch);
            $totalInserted += count($batch);
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info("✓ Import complete!");
        $this->command->info("Total inserted: {$totalInserted}");
        $this->command->info("Total skipped: {$totalSkipped}");
    }

    /**
     * Process a single INSERT statement
     */
    private function processInsertStatement(string $statement, array $userMapping, array &$batch, int &$totalInserted, int &$totalSkipped, int $batchSize): void
    {
        // Extract VALUES portion
        if (!preg_match('/VALUES\s*(.+);$/s', $statement, $valuesMatch)) {
            return;
        }

        $valuesString = $valuesMatch[1];

        // Split into individual rows
        preg_match_all('/\(([^)]+(?:\([^)]*\)[^)]*)*)\)/s', $valuesString, $rows);

        foreach ($rows[1] as $row) {
            // Parse the row values
            $values = $this->parseRow($row);

            if (count($values) < 7) {
                $totalSkipped++;
                continue;
            }

            list($id, $oldUserId, $oldAdminId, $userName, $liveToken, $userType, $insertTime) = $values;

            // Map old user IDs to new user IDs
            $newUserId = isset($userMapping[$oldUserId]) ? $userMapping[$oldUserId] : null;
            $newAdminId = $oldAdminId && isset($userMapping[$oldAdminId]) ? $userMapping[$oldAdminId] : null;

            // Skip if user doesn't exist in new system
            if (!$newUserId) {
                $totalSkipped++;
                continue;
            }

            $batch[] = [
                'user_id' => $newUserId,
                'admin_id' => $newAdminId,
                'user_name' => $userName,
                'live_token' => $liveToken,
                'user_type' => $userType,
                'insert_time' => $insertTime,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert batch when it reaches the batch size
            if (count($batch) >= $batchSize) {
                DB::table('live_tokens')->insert($batch);
                $totalInserted += count($batch);
                $this->command->info("Inserted {$totalInserted} tokens (Skipped: {$totalSkipped})");
                $batch = [];
            }
        }
    }

    /**
     * Parse a row of values from SQL INSERT statement
     */
    private function parseRow(string $row): array
    {
        $values = [];
        $current = '';
        $inQuotes = false;
        $escapeNext = false;

        for ($i = 0; $i < strlen($row); $i++) {
            $char = $row[$i];

            if ($escapeNext) {
                $current .= $char;
                $escapeNext = false;
                continue;
            }

            if ($char === '\\') {
                $escapeNext = true;
                continue;
            }

            if ($char === "'" && !$escapeNext) {
                $inQuotes = !$inQuotes;
                continue;
            }

            if ($char === ',' && !$inQuotes) {
                $values[] = $this->cleanValue($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        // Add the last value
        if ($current !== '') {
            $values[] = $this->cleanValue($current);
        }

        return $values;
    }

    /**
     * Clean and prepare a value
     */
    private function cleanValue(string $value): mixed
    {
        $value = trim($value);

        if ($value === 'NULL') {
            return null;
        }

        // Remove surrounding quotes if present
        if (preg_match("/^'(.*)'$/s", $value, $match)) {
            return stripslashes($match[1]);
        }

        return $value;
    }
}
