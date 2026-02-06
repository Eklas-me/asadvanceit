<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {
        // Filter by online status or sort by last seen
        $users = User::where('role', '!=', 'admin')
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
