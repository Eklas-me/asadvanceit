<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecoverTokens extends Command
{
    protected $signature = 'tokens:recover';
    protected $description = 'Recovers missing tokens from missing_tokens.sql';

    public function handle()
    {
        // Increase limits for this process
        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $this->info("=== Token Recovery Process (Missing Data) ===");

        // Configuration
        $sqlFile = 'e:\Xampp\htdocs\asitrunning\dashboard001\database\missing_tokens.sql';

        if (!file_exists($sqlFile)) {
            $this->error("File not found: $sqlFile");
            return 1;
        }

        // Step 1: Initialize Temp Table
        $this->info("\nStep 1: Initializing temp table...");

        if (!Schema::hasTable('live_tokens_temp')) {
            $this->info("Creating temp table structure...");
            // Create like live_tokens
            DB::statement('CREATE TABLE live_tokens_temp LIKE live_tokens');

            // Drop foreign keys to avoid constraints during raw import
            $fks = ['live_tokens_temp_user_id_foreign', 'live_tokens_temp_admin_id_foreign'];
            foreach ($fks as $fk) {
                try {
                    DB::statement("ALTER TABLE live_tokens_temp DROP FOREIGN KEY $fk");
                } catch (\Exception $e) {
                    // Ignore
                }
            }
        }

        $this->info("Truncating temp table...");
        DB::table('live_tokens_temp')->truncate();

        // Step 2: Import SQL with Table Replacement
        $this->info("\nStep 2: Importing SQL file (Streaming with replacement)...");
        $this->streamingImport($sqlFile);

        // Step 3: Verify Import
        $count = DB::table('live_tokens_temp')->count();
        $this->info("Records loaded into temp table: " . number_format($count));

        if ($count == 0) {
            $this->error("No records imported. Aborting.");
            return 1;
        }

        // Step 4: Process into Live Table
        $this->info("\nStep 4: Migrating to live_tokens...");
        return $this->processFromTempTable();
    }

    private function streamingImport(string $sqlFile): void
    {
        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $handle = fopen($sqlFile, 'r');
        $buffer = '';
        $inInsert = false;
        $count = 0;

        $this->info("Starting stream...");

        while (($line = fgets($handle)) !== false) {

            // Detect INSERT start
            if (stripos($line, 'INSERT INTO `live_tokens`') !== false) {
                // Replace table name dynamically
                $line = str_replace('INSERT INTO `live_tokens`', 'INSERT INTO `live_tokens_temp`', $line);
                $inInsert = true;
                $buffer = $line;
                continue;
            }

            if ($inInsert) {
                $buffer .= $line;

                // End of statement
                if (strpos($line, ');') !== false) {
                    try {
                        DB::unprepared($buffer);
                        $count++;
                        if ($count % 500 == 0) {
                            $this->info("Processed {$count} INSERT statements...");
                        }
                    } catch (\Exception $e) {
                        $this->warn("Failed statement: " . substr($e->getMessage(), 0, 100));
                    }

                    $buffer = '';
                    $inInsert = false;
                }
            }
        }

        fclose($handle);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info("✓ Import complete. Processed {$count} statements.");
    }

    private function processFromTempTable()
    {
        $this->comment("Loading user map...");
        $userMapping = DB::table('users')->whereNotNull('old_id')->pluck('id', 'old_id')->toArray();

        $this->comment("Loading existing token signatures (all timestamps)...");
        // To be safe, let's load ALL signatures to avoid duplicates
        // Warning: 500k records might be heavy for array.
        // Optimization: valid insert_time range check?
        // Since we are recovering NEW data, we can just check if it exists?
        // Or trust the unique constraint?
        // Let's use the hash map approach but only for relevant subset if possible.
        // Actually, 500k strings in array is about 50MB. It fits in 2048M memory easily.

        $existingSignatures = [];
        $query = DB::table('live_tokens')->select('user_name', 'insert_time');
        foreach ($query->cursor() as $record) {
            $existingSignatures[$record->user_name . '|' . $record->insert_time] = true;
        }

        $this->info("Loaded " . count($existingSignatures) . " existing signatures.");

        $this->comment("Processing migration in chunks...");

        $totalMigrated = 0;
        $totalSkippedMap = 0;
        $totalDuplicate = 0;
        $totalSkippedMismatch = 0;

        DB::table('live_tokens_temp')->orderBy('id')->chunk(5000, function ($rows) use ($userMapping, &$existingSignatures, &$totalMigrated, &$totalSkippedMap, &$totalDuplicate) {
            $batch = [];

            foreach ($rows as $row) {
                // Check if already exists
                $sig = $row->user_name . '|' . $row->insert_time;
                if (isset($existingSignatures[$sig])) {
                    $totalDuplicate++;
                    continue;
                }

                // Mark as seen
                $existingSignatures[$sig] = true;

                // Map User ID
                if (!isset($userMapping[$row->user_id])) {
                    $totalSkippedMap++;
                    continue;
                }

                $batch[] = [
                    'user_id' => $userMapping[$row->user_id],
                    'admin_id' => $row->admin_id ? ($userMapping[$row->admin_id] ?? null) : null,
                    'user_name' => $row->user_name,
                    'live_token' => $row->live_token,
                    'user_type' => $row->user_type,
                    'insert_time' => $row->insert_time,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($batch)) {
                DB::table('live_tokens')->insert($batch);
                $totalMigrated += count($batch);
                $this->info("Migrated batch: " . count($batch));
            }
        });

        $this->info("\n=== RECOVERY RESULTS ===");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total in temp table', DB::table('live_tokens_temp')->count()],
                ['Final total in live_tokens', DB::table('live_tokens')->count()],
                ['Newly Migrated', $totalMigrated],
                ['Skipped (Duplicate)', $totalDuplicate],
                ['Skipped (User Not Found)', $totalSkippedMap],
            ]
        );

        $this->info("\nStep 6: Cleanup...");
        DB::table('live_tokens_temp')->truncate();
        $this->info("✓ Cleanup complete");

        $this->info("\n✓ Recovery process complete!");
        return 0;
    }
}
