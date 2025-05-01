<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserJoinedLiveSession implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveSessionId;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct($liveSessionId, $user)
    {
        $this->liveSessionId = $liveSessionId;
        $this->user = $user;
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->user
                ? "{$this->user->name} đã tham gia phòng"
                : "Một khách ẩn danh đã tham gia phòng",
            'user' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name
            ] : null
        ];
    }

    public function broadcastOn()
    {
        return new Channel('live-session.' . $this->liveSessionId);
    }

    public function broadcastAs()
    {
        return 'user-joined';
    }
}
