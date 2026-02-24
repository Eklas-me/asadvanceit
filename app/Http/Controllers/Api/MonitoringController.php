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
            'agent_version' => 'nullable|string',
        ]);

        $device = Device::updateOrCreate(
            ['hardware_id' => $request->hardware_id],
            [
                'computer_name' => $request->computer_name,
                'agent_version' => $request->agent_version,
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
            'agent_version' => 'nullable|string',
        ]);

        $user = $request->user();
        $user->last_seen = now();
        $user->save();

        // Update device record too if HWID is provided
        if ($request->hardware_id) {
            $deviceData = [
                'user_id' => $user->id,
                'last_seen' => now()
            ];
            if ($request->has('agent_version')) {
                $deviceData['agent_version'] = $request->agent_version;
            }
            Device::updateOrCreate(
                ['hardware_id' => $request->hardware_id],
                $deviceData
            );
        }

        $channelId = 'user.' . $user->id;
        broadcast(new AgentDataStream($channelId, $request->image, $request->stats));

        // Check if an admin is actively watching this device
        $streamRequested = false;
        $streamPaused = false;

        if ($request->hardware_id) {
            $streamRequested = Cache::get('stream_requested_' . $request->hardware_id, false);
            $streamPaused = Cache::get('stream_paused_' . $request->hardware_id, false);
        }

        return response()->json([
            'status' => 'ok',
            'stream_requested' => $streamRequested,
            'stream_paused' => $streamPaused,
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
            'paused' => 'nullable|boolean',
        ]);

        $cacheKey = 'stream_requested_' . $request->hardware_id;
        $pausedKey = 'stream_paused_' . $request->hardware_id;

        if ($request->watching) {
            Cache::put($cacheKey, true, 15); // 15 second TTL

            // Store paused state if provided
            if ($request->has('paused')) {
                Cache::put($pausedKey, $request->paused, 15);
            }
        } else {
            Cache::forget($cacheKey);
            Cache::forget($pausedKey);
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

