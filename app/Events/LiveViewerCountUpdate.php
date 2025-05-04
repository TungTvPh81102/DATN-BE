<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveViewerCountUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveSessionId;
    public $viewerCount;

    public function __construct($liveSessionId, $viewerCount)
    {
        $this->liveSessionId = $liveSessionId;
        $this->viewerCount = $viewerCount;
    }

    public function broadcastOn()
    {
        return new Channel('live-session.' . $this->liveSessionId);
    }

    public function broadcastAs()
    {
        return 'viewer-count-updated';
    }

    public function broadcastWith()
    {
        return [
            'viewer_count' => $this->viewerCount,
        ];
    }
}
