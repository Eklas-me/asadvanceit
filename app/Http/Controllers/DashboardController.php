<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\ShiftService;

class DashboardController extends Controller
{
    protected $shiftService;

    public function __construct(\App\Services\ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function adminDashboard(Request $request)
    {
        // Cache Top Cards Stats for 5 minutes
        $stats = Cache::remember('dashboard_stats_today', 300, function () {
            return [
                'totalTokensToday' => $this->shiftService->countTokensToday(),
                'totalWorkersToday' => $this->shiftService->countWorkersToday(),
                'totalAccountToday' => $this->shiftService->countAccountsToday(),
                'activeLast30Min' => \App\Models\LiveToken::where('insert_time', '>=', now()->subMinutes(30))
                    ->distinct('user_id')
                    ->count('user_id')
            ];
        });

        $totalTokensToday = $stats['totalTokensToday'];
        $totalWorkersToday = $stats['totalWorkersToday'];
        $totalAccountToday = $stats['totalAccountToday'];
        $activeLast30Min = $stats['activeLast30Min'];

        // Default date range: today 7:00 AM to tomorrow 7:00 AM
        $defaultStart = \Carbon\Carbon::today()->setTime(7, 0, 0)->format('Y-m-d\TH:i');
        $defaultEnd = \Carbon\Carbon::tomorrow()->setTime(7, 0, 0)->format('Y-m-d\TH:i');

        // Get filter inputs
        $startDate = $request->input('start_date', $defaultStart);
        $endDate = $request->input('end_date', $defaultEnd);
        $searchName = $request->input('search_name', '');
        $page = $request->input('page', 1);

        // Convert to datetime for query
        $startDateTime = \Carbon\Carbon::parse($startDate)->format('Y-m-d H:i:s');
        $endDateTime = \Carbon\Carbon::parse($endDate)->format('Y-m-d H:i:s');

        // Fetch workers report with token counts within date range
        $workersQuery = \App\Models\User::select('users.id', 'users.name', 'users.shift', 'users.profile_photo')
            ->leftJoin('live_tokens', function ($join) use ($startDateTime, $endDateTime) {
                $join->on('users.id', '=', 'live_tokens.user_id')
                    ->whereBetween('live_tokens.insert_time', [$startDateTime, $endDateTime]);
            })
            ->selectRaw('COUNT(live_tokens.id) as token_count')
            ->selectRaw('MAX(live_tokens.insert_time) as last_update')
            ->where('users.role', '!=', 'admin');

        // Apply search filter if provided
        if ($searchName) {
            $workersQuery->where('users.name', 'LIKE', '%' . $searchName . '%');
        }

        $workersQuery->groupBy('users.id', 'users.name', 'users.shift', 'users.profile_photo')
            ->having('token_count', '>', 0)  // Only show workers with tokens
            ->orderBy('token_count', 'desc')  // Order by token count (highest first)
            ->orderBy('users.name', 'asc');   // Then by name for ties

        // Paginate results (15 per page)
        if (empty($searchName)) {
            // Cache default view (no search) for 2 minutes
            // Include date range in cache key to ensure filter works
            $cacheKey = 'dashboard_workers_report_' . md5($startDateTime . $endDateTime) . '_page_' . $page;
            $workersReport = Cache::remember($cacheKey, 120, function () use ($workersQuery) {
                return $workersQuery->paginate(15);
            });
        } else {
            // Do not cache search results
            $workersReport = $workersQuery->paginate(15);
        }

        // If AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'workers' => $workersReport->items(),
                'has_more' => $workersReport->hasMorePages(),
                'next_page' => $workersReport->currentPage() + 1
            ]);
        }

        return view('admin.dashboard', compact(
            'totalTokensToday',
            'totalWorkersToday',
            'totalAccountToday',
            'activeLast30Min',
            'workersReport',
            'startDate',
            'endDate',
            'searchName'
        ));
    }

    public function userDashboard()
    {
        $userId = auth()->id();
        $myTokenToday = $this->shiftService->countMyTokensToday($userId);
        $myTokenYesterday = $this->shiftService->countMyTokensYesterday($userId);
        $myTokenMonth = $this->shiftService->countMyTokensMonth($userId);
        $myTokenTotal = $this->shiftService->countMyTokensLifetime($userId);

        // Fetch performance chart data (last 7 days)
        $chartData = $this->shiftService->getDailyTokenCounts($userId, 7);

        return view('user.dashboard', compact(
            'myTokenToday',
            'myTokenYesterday',
            'myTokenMonth',
            'myTokenTotal',
            'chartData'
        ));
    }
}
