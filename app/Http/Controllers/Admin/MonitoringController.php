<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {
        // In a real app, maybe filter by online status or role
        $users = User::where('role', '!=', 'admin')->get();
        return view('admin.monitoring.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin.monitoring.show', compact('user'));
    }
}
