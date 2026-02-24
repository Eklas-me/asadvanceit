<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {
        // Only show devices active in the last 1.5 minutes (90 seconds)
        $devices = \App\Models\Device::with('user')
            ->where('last_seen', '>=', now()->subSeconds(90))
            ->orderBy('last_seen', 'desc')
            ->get();

        return view('admin.monitoring.index', compact('devices'));
    }

    public function show($id)
    {
        $device = \App\Models\Device::with('user')->findOrFail($id);
        return view('admin.monitoring.show', compact('device'));
    }
}
