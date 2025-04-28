<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlreadyLiveEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $instructorId;

    public function __construct($instructorId)
    {
        $this->instructorId = $instructorId;
    }

    public function broadcastOn()
    {
        return new Channel('instructor.' . $this->instructorId);
    }

    public function broadcastAs()
    {
        return 'already-live';
    }
}
