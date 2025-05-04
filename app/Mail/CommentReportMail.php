<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $data;
   
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Báo cáo bình luận trong bài học')
                    ->view('emails.comment_report')
                    ->with(['data' => $this->data]); 
    }

    public function attachments(): array
    {
        return [];
    }
}
