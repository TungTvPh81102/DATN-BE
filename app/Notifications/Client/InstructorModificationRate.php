<?php

namespace App\Notifications\Client;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class InstructorModificationRate extends Notification implements ShouldQueue
{
    use Queueable, Notifiable;

    public $newRate;
    public $user;

    public function __construct(float  $newRate, User $user)
    {
        $this->newRate = $newRate * 100;
        $this->user = $user;
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        Log::info($this->newRate);
        return (new MailMessage)
            ->view('emails.update_rate_instructor', [
                'newSharePercentage' => $this->newRate,
                'user' => $this->user
            ])
            ->subject('Thay đổi tỉ lệ doanh thu với giảng viên');
    }
}
