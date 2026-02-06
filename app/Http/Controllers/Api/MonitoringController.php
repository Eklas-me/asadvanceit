<?php

namespace App\Http\Controllers\Api;

use App\Events\AgentDataStream;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            'image' => 'required|string',
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

        return response()->json(['status' => 'ok']);
    }
}
