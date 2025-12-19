<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LiveToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;

class TokenController extends Controller
{
    public function index(Request $request)
    {
        // Increase memory limit for large datasets
        ini_set('memory_limit', '512M');

        // Default Time Range
        $defaultFrom = Carbon::today()->addHours(7)->format('Y-m-d\TH:i');
        $defaultTo = Carbon::tomorrow()->addHours(7)->format('Y-m-d\TH:i');

        $from = $request->input('from_datetime', $defaultFrom);
        $to = $request->input('to_datetime', $defaultTo);
        $userId = $request->input('user_id');

        // Total Count Query (Fast) with Caching (5 mins)
        $cacheKey = 'count_tokens_' . md5($from . $to . ($userId ?? 'all'));
        $totalTokensCount = Cache::remember($cacheKey, 300, function () use ($from, $to, $userId) {
            $query = LiveToken::whereBetween('insert_time', [$from, $to]);
            if ($userId) {
                $query->where('user_id', $userId);
            }
            return $query->count();
        });

        // Paginate Users who have tokens in this range
        $usersQuery = User::whereHas('liveTokens', function ($q) use ($from, $to) {
            $q->whereBetween('insert_time', [$from, $to]);
        });

        if ($userId) {
            $usersQuery->where('id', $userId);
        }

        // Eager load only relevant tokens with selected columns to save memory
        $paginatedUsers = $usersQuery->with([
            'liveTokens' => function ($q) use ($from, $to) {
                $q->select('id', 'user_id', 'live_token', 'insert_time')
                    ->whereBetween('insert_time', [$from, $to])
                    ->orderBy('insert_time', 'desc');
            }
        ])
            ->orderBy('name') // Sort users alphabetically
            ->paginate(15)
            ->appends($request->all());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.tokens.list', compact('paginatedUsers'))->render(),
                'hasMore' => $paginatedUsers->hasMorePages()
            ]);
        }

        // Cache Workers List for 24 hours (Select ONLY needed fields to save memory)
        $workers = Cache::remember('workers_list_all', 86400, function () {
            return User::where('role', '!=', 'admin')
                ->orderBy('name')
                ->toBase() // Use query builder instance to skip model hydration
                ->get(['id', 'name']); // Critical: Select specific columns
        });

        // Export Functionality (Streaming)
        if ($request->has('export')) {
            return $this->exportTokensStreaming($from, $to, $userId);
        }

        return view('admin.tokens.index', compact('paginatedUsers', 'workers', 'from', 'to', 'request', 'totalTokensCount'));
    }

    private function exportTokensStreaming($from, $to, $userId = null)
    {
        $fileName = 'tokens_' . date('Y-m-d_H-i-s') . '.txt';

        // Calculate total count first for progress bar
        $countQuery = LiveToken::whereBetween('insert_time', [$from, $to]);
        if ($userId) {
            $countQuery->where('user_id', $userId);
        }
        $totalCount = $countQuery->count();

        return Response::streamDownload(function () use ($from, $to, $userId) {
            set_time_limit(0);
            ini_set('memory_limit', '1024M');

            $query = LiveToken::whereBetween('insert_time', [$from, $to])
                ->orderBy('insert_time', 'desc');

            if ($userId) {
                $query->where('user_id', $userId);
            }

            foreach ($query->cursor() as $token) {
                echo $token->live_token . "\n";
                if (ob_get_level() > 0)
                    ob_flush();
                flush();
            }
        }, $fileName, [
            'Content-Type' => 'text/plain',
            'X-Total-Count' => $totalCount,
            'Access-Control-Expose-Headers' => 'X-Total-Count',
        ]);
    }

    public function myTokens(Request $request)
    {
        $user = Auth::user();
        $date = $request->input('task_date', Carbon::today()->format('Y-m-d'));

        $start = Carbon::parse($date)->addHours(7);
        $end = Carbon::parse($date)->addDay()->addHours(7);

        $tokens = LiveToken::where('user_id', $user->id)
            ->whereBetween('insert_time', [$start, $end])
            ->orderBy('insert_time', 'desc')
            ->get();

        return view('user.tokens.my_tokens', compact('tokens', 'date'));
    }

    public function destroy(LiveToken $token)
    {
        // Allow user to delete their own token or admin to delete any
        if (Auth::user()->id !== $token->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $token->delete();
        return redirect()->back()->with('success', 'Token deleted successfully!');
    }

    public function create()
    {
        return view('user.tokens.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tinder_token' => 'required|string',
        ]);

        $rawTokens = explode("\n", $request->tinder_token);
        $tokens = array_filter(array_map('trim', $rawTokens));
        $uniqueTokens = array_unique($tokens);

        if (count($tokens) !== count($uniqueTokens)) {
            return redirect()->back()->with('error', 'Duplicate tokens found! Please enter unique tokens only.')->withInput();
        }

        if (empty($tokens)) {
            return redirect()->back()->with('error', 'Please enter at least one valid token.')->withInput();
        }

        $count = 0;
        foreach ($uniqueTokens as $tokenString) {
            // Check if token exists globally to prevent global duplicates if needed, 
            // or just rely on DB unique constraint if any. 
            // Legacy code checks `add_live_token` method which likely checks existence.
            // Let's assume unique constraint or check first.
            if (LiveToken::where('live_token', $tokenString)->exists()) {
                continue; // specific token duplicate handling?
            }

            LiveToken::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'live_token' => $tokenString,
                'user_type' => Auth::user()->role,
                'insert_time' => Carbon::now(),
            ]);
            $count++;
        }

        return redirect()->back()->with('success', "$count tokens submitted successfully!");
    }
}
