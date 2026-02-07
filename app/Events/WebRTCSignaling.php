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
    public $eventName;

    /**
     * Create a new event instance.
     * 
     * @param array $payload The signaling data (type, sdp, candidate, etc.)
     * @param string $targetChannel The channel to broadcast to
     * @param string|null $eventName Custom event name (default: webrtc.signal)
     */
    public function __construct(array $payload, string $targetChannel, ?string $eventName = null)
    {
        $this->payload = $payload;
        $this->targetChannel = $targetChannel;
        $this->eventName = $eventName ?? 'webrtc.signal';
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        if (str_starts_with($this->targetChannel, 'device-control')) {
            return [
                new Channel($this->targetChannel),
            ];
        }

        return [
            new PrivateChannel($this->targetChannel),
        ];
    }

    public function broadcastAs(): string
    {
        return $this->eventName;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
