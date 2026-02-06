<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {
        // Filter users who have been active in the last 5 minutes
        $users = User::where('role', '!=', 'admin')
            ->where('last_seen', '>=', now()->subMinutes(5))
            ->orderBy('last_seen', 'desc')
            ->get();

        return view('admin.monitoring.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin.monitoring.show', compact('user'));
    }
}
