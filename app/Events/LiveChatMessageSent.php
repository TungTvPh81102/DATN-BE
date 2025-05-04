<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $user;
    public $liveSessionId;

    /**
     * Create a new event instance.
     */
    public function __construct($message, $user, $liveSessionId)
    {
        $this->message = $message;
        $this->user = $user;
        $this->liveSessionId = $liveSessionId;
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'message' => $this->message->content,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_avater' => $this->user->avatar ?? 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1743316311/users/34wNL3FsA1.png',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function broadcastOn()
    {
        return new Channel('live-session.' . $this->liveSessionId);
    }
}
