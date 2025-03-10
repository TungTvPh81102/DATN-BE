<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversation;

    /**
     * Create a new event instance.
     */
    public function __construct($message, $conversation)
    {
        $this->message = $message;
        $this->conversation = $conversation;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->conversation->id);
    }

    public function broadcastAs()
    {
        return 'MessageSent';
    }

    public function broadcastWith()
    {
        $sender = $this->message->sender;

        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->conversation->id,
            'content' => $this->message->content,
            'type' => $this->message->type,
            'meta_data' => $this->message->meta_data,
            'sender' => [
                'id' => $sender->id,
                'name' => $sender->name,
                'avatar' => $sender->avatar ?? null,
            ],
            'sent_at' => $this->message->created_at->toDateTimeString(),
        ];
    }

}
