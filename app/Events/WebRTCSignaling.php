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
    protected $targetChannel;
    protected $eventType;

    /**
     * Create a new event instance.
     * 
     * @param array $payload The signaling data (type, sdp, candidate, action, etc.)
     * @param string $targetChannel The channel to broadcast to
     * @param string $eventType The event name to broadcast as (default: webrtc.signal)
     */
    public function __construct(array $payload, string $targetChannel, string $eventType = 'webrtc.signal')
    {
        $this->payload = $payload;
        $this->targetChannel = $targetChannel;
        $this->eventType = $eventType;
    }

    /**
     * Data to broadcast - flatten payload so fields like type, sdp, action
     * are directly accessible (e.g. data.type instead of data.payload.type)
     */
    public function broadcastWith(): array
    {
        return is_array($this->payload) ? $this->payload : [];
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
        return $this->eventType;
    }
}
