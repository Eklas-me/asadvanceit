<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {
        $devices = \App\Models\Device::with('user')->orderBy('last_seen', 'desc')->get();
        return view('admin.monitoring.index', compact('devices'));
    }

    public function show($id)
    {
        $device = \App\Models\Device::with('user')->findOrFail($id);
        return view('admin.monitoring.show', compact('device'));
    }
}
