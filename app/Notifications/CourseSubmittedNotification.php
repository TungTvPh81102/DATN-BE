<?php

namespace App\Notifications;

use App\Models\Course;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CourseSubmittedNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public $course;

    /**
     * Create a new notification instance.
     */
    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $this->course
            ->load(['instructor', 'category'])
            ->loadCount(['chapters', 'lessons']);

        return (new MailMessage)
            ->subject('Yêu cầu kiểm duyệt khóa học mới')
            ->view('emails.course-submitted', [
                'admin' => $notifiable,
                'course' => $this->course,
                'approvalUrl' => $this->getUrl()
            ]);
    }

    private function getUrl()
    {
        $approvableId = $this->course->approvables ? $this->course->approvables->id : null;
        return $approvableId ? route('admin.approvals.courses.show', $approvableId) : '#';
    }

    private function notificationData(): array
    {
        return [
            'type' => 'register_course',
            'course_id' => $this->course->id,
            'course_name' => $this->course->name,
            'course_slug' => $this->course->slug,
            'course_thumbnail' => $this->course->thumbnail,
            'message' => 'Khóa học "' . $this->course->name . '" đã được gửi yêu cầu kiểm duyệt.',
            'url' => $this->getUrl(),
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->notificationData();
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->notificationData());
    }
}
