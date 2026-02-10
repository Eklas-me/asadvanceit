<?php

namespace App\Http\Controllers\Api;

use App\Events\AgentDataStream;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Device;

class MonitoringController extends Controller
{
    public function heartbeat(Request $request)
    {
        $request->validate([
            'hardware_id' => 'required|string',
            'computer_name' => 'required|string',
        ]);

        $device = Device::updateOrCreate(
            ['hardware_id' => $request->hardware_id],
            [
                'computer_name' => $request->computer_name,
                'last_seen' => now(),
                'user_id' => $request->user()?->id // Only set if logged in
            ]
        );

        return response()->json(['status' => 'ok']);
    }

    public function uploadStream(Request $request)
    {
        $request->validate([
            'image' => 'nullable|string',
            'stats' => 'required|array',
            'hardware_id' => 'nullable|string',
        ]);

        $user = $request->user();
        $user->last_seen = now();
        $user->save();

        // Update device record too if HWID is provided
        if ($request->hardware_id) {
            Device::updateOrCreate(
                ['hardware_id' => $request->hardware_id],
                [
                    'user_id' => $user->id,
                    'last_seen' => now()
                ]
            );
        }

        $channelId = 'user.' . $user->id;
        broadcast(new AgentDataStream($channelId, $request->image, $request->stats));

        // Check if an admin is actively watching this device
        $streamRequested = false;
        if ($request->hardware_id) {
            $streamRequested = Cache::get('stream_requested_' . $request->hardware_id, false);
        }

        return response()->json([
            'status' => 'ok',
            'stream_requested' => $streamRequested,
        ]);
    }

    /**
     * Admin calls this to mark a device as "being watched".
     * Uses cache with 15s TTL — expires automatically if admin stops pinging.
     */
    public function requestStream(Request $request)
    {
        $request->validate([
            'hardware_id' => 'required|string',
            'watching' => 'required|boolean',
        ]);

        $cacheKey = 'stream_requested_' . $request->hardware_id;

        if ($request->watching) {
            Cache::put($cacheKey, true, 15); // 15 second TTL
        } else {
            Cache::forget($cacheKey);
        }

        return response()->json(['status' => 'ok']);
    }

    public function signal(Request $request)
    {
        $payload = $request->payload;
        $targetChannel = $request->target_channel;
        $eventType = $request->input('event_type', 'webrtc.signal');

        broadcast(new \App\Events\WebRTCSignaling($payload, $targetChannel, $eventType))->toOthers();

        return response()->json(['status' => 'sent']);
    }
}

