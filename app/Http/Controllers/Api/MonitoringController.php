<?php

namespace App\Http\Controllers\Api;

use App\Events\AgentDataStream;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User; // Added this line

class MonitoringController extends Controller
{
    public function uploadStream(Request $request)
    {
        \Log::info('Stream upload attempt', [
            'user_id' => $request->user()->id,
            'has_image' => $request->has('image'),
            'stats' => $request->stats
        ]);

        $request->validate([
            'image' => 'required|string',
            'stats' => 'required|array',
        ]);

        $user = $request->user();

        // Update last_seen to mark as online
        $user->last_seen = now();
        $user->save();

        // Broadcast the event immediately
        broadcast(new AgentDataStream($user->id, $request->image, $request->stats));

        return response()->json(['status' => 'ok']);
    }
}
