<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpcomingLiveSessionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $liveSession;

    public function __construct($liveSession)
    {
        $this->liveSession = $liveSession;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'broadcast', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $viewData = [
            'liveSession' => $this->liveSession,
            'notifiable' => $notifiable,
            'url' => config('app.fe_url') . '/live-streaming/' . $this->liveSession->code,
        ];

        return (new MailMessage)
            ->subject('Buổi phát sóng sắp bắt đầu: ' . $this->liveSession->title)
            ->view('emails.upcoming-live-session', $viewData);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Buổi phát sóng "' . $this->liveSession->title . '" sắp đến giờ ',
            'live_session_id' => $this->liveSession->id,
            'title' => $this->liveSession->title,
            'starts_at' => $this->liveSession->starts_at,
            'type' => 'upcoming_live_session'
        ];
    }

    public function toBroadcast(object $notifiable)
    {
        return new BroadcastMessage([
            'message' => 'Buổi phát sóng "' . $this->liveSession->title . '" sắp đến giờ ',
            'live_session_id' => $this->liveSession->id,
            'title' => $this->liveSession->title,
            'starts_at' => $this->liveSession->starts_at,
            'type' => 'upcoming_live_session',
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('notification.' . $this->liveSession->instructor_id);
    }
}
