<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFollowNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;
    protected $follower;

    public function __construct($follower)
    {
        $this->follower = $follower;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }


    public function notificationData()
    {
        return [
            'message' => $this->follower->name . ' đã theo dõi bạn!',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notification.' . $this->follower->id);
    }
}
