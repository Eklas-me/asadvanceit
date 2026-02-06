<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentDataStream implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $screenImage; // Base64 encoded image
    public $stats;       // CPU, RAM, etc.

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $screenImage, $stats)
    {
        $this->userId = $userId;
        $this->screenImage = $screenImage;
        $this->stats = $stats;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('agent-stream.' . $this->userId),
        ];
    }

    public function broadcastAs()
    {
        return 'agent.data';
    }
}
