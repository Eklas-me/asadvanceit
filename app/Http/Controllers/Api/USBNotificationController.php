<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Auth;

class USBNotificationController extends Controller
{
    protected $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Handle USB event from Agent
     */
    public function handleUsbEvent(Request $request)
    {
        \Log::info('USB Event Received from Agent', $request->all());
        $request->validate([
            'name' => 'nullable|string',
            'mount' => 'nullable|string',
            'total_space' => 'nullable|numeric',
        ]);

        $user = Auth::user();

        if ($this->telegram->sendUsbNotification($user, $request->all())) {
            return response()->json([
                'success' => true,
                'message' => 'USB notification sent to Telegram.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send Telegram notification.'
        ], 500);
    }
}
