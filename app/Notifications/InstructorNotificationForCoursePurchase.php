<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class InstructorNotificationForCoursePurchase extends Notification implements ShouldQueue
{
    use Queueable;

    private $buyer;
    private $course;
    private $transaction;

    public function __construct($buyer, $course, $transaction)
    {
        $this->buyer = $buyer;
        $this->course = $course;
        $this->transaction = $transaction;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Khóa học của bạn đã được mua!')
            ->view(
                'emails.instructor_course_purchase',
                [
                    'notifiable' => $notifiable,
                    'buyer' => $this->buyer,
                    'course' => $this->course,
                    'transaction' => $this->transaction
                ]
            );
    }


    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Học viên ' . $this->buyer->name . ' đã mua khoá học ',
            'student_id' => $this->buyer->id,
            'course_name' => $this->course->name,
            'transaction_amount' => $this->transaction->amount,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => 'Học viên ' . $this->buyer->name . 'đã mua khoá học ',
            'student_id' => $this->buyer->id,
            'course_name' => $this->course->name,
            'transaction_amount' => $this->transaction->amount,
        ]);
    }

    public function broadcastOn()
    {
        $channel = new PrivateChannel('notification.' . $this->course->user_id);
        return $channel;
    }
}
