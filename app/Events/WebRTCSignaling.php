<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class WebRTCSignaling implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $payload;
    public $targetChannel;

    /**
     * Create a new event instance.
     * 
     * @param array $payload The signaling data (type, sdp, candidate, etc.)
     * @param string $targetChannel The channel to broadcast to
     */
    public function __construct(array $payload, string $targetChannel)
    {
        $this->payload = $payload;
        $this->targetChannel = $targetChannel;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel($this->targetChannel),
        ];
    }

    public function broadcastAs(): string
    {
        return 'webrtc.signal';
    }
}
