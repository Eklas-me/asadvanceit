<?php

namespace App\Http\Controllers\Api;

use App\Events\AgentDataStream;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonitoringController extends Controller
{
    public function uploadStream(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'stats' => 'required|array',
        ]);

        $user = $request->user();

        // Broadcast the event immediately
        broadcast(new AgentDataStream($user->id, $request->image, $request->stats));

        return response()->json(['status' => 'ok']);
    }
}
