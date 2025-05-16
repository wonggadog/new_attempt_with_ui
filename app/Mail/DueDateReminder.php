<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DueDateReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $document;

    public function __construct($document)
    {
        $this->document = $document;
    }

    public function build()
    {
        return $this->subject('Document Due Date Reminder')
            ->view('emails.due_date_reminder');
    }
} 