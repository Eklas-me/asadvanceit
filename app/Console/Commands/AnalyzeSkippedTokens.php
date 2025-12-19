<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AnalyzeSkippedTokens extends Command
{
    protected $signature = 'tokens:analyze-skipped';
    protected $description = 'Analyze which tokens were skipped during migration';

    public function handle()
    {
        $sqlFile = base_path('../database/live_tokens.sql');

        if (!file_exists($sqlFile)) {
            $this->error("SQL file not found: {$sqlFile}");
            return 1;
        }

        $this->info("Building user ID mapping...");
        $userMapping = DB::table('users')
            ->whereNotNull('old_id')
            ->pluck('id', 'old_id')
            ->toArray();

        $skippedUsers = [];
        $buffer = '';
        $inInsertStatement = false;

        $this->info("Analyzing SQL file...");
        $handle = fopen($sqlFile, 'r');

        while (($line = fgets($handle)) !== false) {
            if (empty(trim($line)) || strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
                continue;
            }

            if (stripos($line, 'INSERT INTO `live_tokens`') !== false) {
                $inInsertStatement = true;
                $buffer = $line;
                continue;
            }

            if ($inInsertStatement) {
                $buffer .= $line;

                if (strpos($line, ');') !== false) {
                    $this->processInsertStatement($buffer, $userMapping, $skippedUsers);
                    $buffer = '';
                    $inInsertStatement = false;
                }
            }
        }

        fclose($handle);

        // Display results
        $this->info("\n=== SKIPPED TOKENS ANALYSIS ===\n");

        arsort($skippedUsers);

        $this->table(
            ['User ID', 'User Name', 'Token Count'],
            collect($skippedUsers)->map(function ($count, $userId) {
                return [$userId, $count['name'], $count['count']];
            })->toArray()
        );

        $this->info("\nTotal unique users with skipped tokens: " . count($skippedUsers));
        $this->info("Total tokens skipped: " . array_sum(array_column($skippedUsers, 'count')));

        return 0;
    }

    private function processInsertStatement(string $statement, array $userMapping, array &$skippedUsers): void
    {
        if (!preg_match('/VALUES\s*(.+);$/s', $statement, $valuesMatch)) {
            return;
        }

        $valuesString = $valuesMatch[1];
        preg_match_all('/\(([^)]+(?:\([^)]*\)[^)]*)*)\)/s', $valuesString, $rows);

        foreach ($rows[1] as $row) {
            $values = $this->parseRow($row);

            if (count($values) < 7) {
                continue;
            }

            list($id, $oldUserId, $oldAdminId, $userName, $liveToken, $userType, $insertTime) = $values;

            if (!isset($userMapping[$oldUserId])) {
                if (!isset($skippedUsers[$oldUserId])) {
                    $skippedUsers[$oldUserId] = ['name' => $userName, 'count' => 0];
                }
                $skippedUsers[$oldUserId]['count']++;
            }
        }
    }

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

        if ($current !== '') {
            $values[] = $this->cleanValue($current);
        }

        return $values;
    }

    private function cleanValue(string $value): mixed
    {
        $value = trim($value);

        if ($value === 'NULL') {
            return null;
        }

        if (preg_match("/^'(.*)'$/s", $value, $match)) {
            return stripslashes($match[1]);
        }

        return $value;
    }
}
