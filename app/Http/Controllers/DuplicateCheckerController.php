<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LiveToken;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DuplicateCheckerController extends Controller
{
    public function index(Request $request)
    {
        // Set timezone
        date_default_timezone_set('Asia/Dhaka');

        // Get date range from request or use defaults
        $start = $request->input('start');
        $end = $request->input('end');

        // Handle presets
        if ($request->has('preset')) {
            if ($request->preset === 'today') {
                $start = Carbon::today()->setTime(7, 0)->format('Y-m-d\TH:i');
                $end = Carbon::tomorrow()->setTime(7, 0)->format('Y-m-d\TH:i');
            } elseif ($request->preset === 'yesterday') {
                $start = Carbon::yesterday()->setTime(7, 0)->format('Y-m-d\TH:i');
                $end = Carbon::today()->setTime(7, 0)->format('Y-m-d\TH:i');
            }
        }

        // Set defaults if not provided
        if (empty($start)) {
            $start = Carbon::today()->setTime(7, 0)->format('Y-m-d\TH:i');
        }
        if (empty($end)) {
            $end = Carbon::tomorrow()->setTime(7, 0)->format('Y-m-d\TH:i');
        }

        $exactDuplicates = [];
        $nearDuplicates = [];

        // Only run queries if both dates are provided
        if (!empty($start) && !empty($end)) {
            $startDateTime = Carbon::parse($start)->format('Y-m-d H:i:s');
            $endDateTime = Carbon::parse($end)->format('Y-m-d H:i:s');

            // Get exact duplicates
            $exactDuplicates = $this->getExactDuplicates($startDateTime, $endDateTime);

            // Get near duplicates
            $nearDuplicates = $this->getNearDuplicates($startDateTime, $endDateTime);
        }

        return view('duplicate-checker.index', compact('start', 'end', 'exactDuplicates', 'nearDuplicates'));
    }

    private function getExactDuplicates($start, $end)
    {
        // Use the indexed token_hash column for lightning-fast grouping
        return DB::select("
            SELECT 
                MAX(live_token) as normalized_token,
                COUNT(*) AS total_submissions,
                GROUP_CONCAT(DISTINCT user_name SEPARATOR ', ') AS users
            FROM live_tokens
            WHERE insert_time BETWEEN ? AND ?
            GROUP BY token_hash
            HAVING total_submissions > 1
            ORDER BY total_submissions DESC
            LIMIT 200
        ", [$start, $end]);
    }

    private function getNearDuplicates($start, $end)
    {
        // Use DB cursor to minimize memory usage (avoids loading all models)
        // Switch to insert_time for filtering as it is indexed
        $query = DB::table('live_tokens')
            ->select('id', 'user_id', 'user_name', 'live_token')
            ->whereBetween('insert_time', [$start, $end])
            ->orderBy('id'); // meaningful order for cursor

        $grouped = [];

        foreach ($query->cursor() as $token) {
            $url = strtolower(trim($token->live_token));
            $url = preg_replace("#^https?://#", "", $url);
            $url = rtrim($url, '/');
            $parts = explode('/', $url);

            // Extract the last part (slug/ID)
            $normalized = preg_replace('/[^a-z0-9]/', '', end($parts));

            if (!empty($normalized)) {
                // Optimization: Keep array small, store minimal data
                $grouped[$normalized][] = [
                    'u' => $token->user_name,
                    't' => $token->live_token
                ];
            }
        }

        // Filter only groups with more than 1 entry
        $nearDuplicates = [];
        foreach ($grouped as $key => $entries) {
            if (count($entries) > 1) {
                // De-duplicate users and links
                $users = array_unique(array_column($entries, 'u'));
                $links = array_unique(array_column($entries, 't'));

                $nearDuplicates[] = [
                    'matched_part' => $key,
                    'total' => count($entries),
                    'users' => implode(', ', $users),
                    'links' => $links
                ];
            }
        }

        // Sort by biggest clusters first
        usort($nearDuplicates, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // Limit results to top 50 to prevent UI lag
        return array_slice($nearDuplicates, 0, 50);
    }
}
