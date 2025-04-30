<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveSessionStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sessionId;
    public $status;
    public $data;

    public function __construct($sessionId, $status, $data = [])
    {
        $this->sessionId = $sessionId;
        $this->status = $status;
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new Channel('live-session.'  . $this->sessionId);
    }

    public function broadcastAs()
    {
        return 'status-changed';
    }
}
