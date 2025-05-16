<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentCommented extends Mailable
{
    use Queueable, SerializesModels;

    public $senderName;
    public $commenterName;
    public $subject;
    public $fileName;
    public $comment;

    public function __construct($senderName, $commenterName, $subject, $fileName, $comment)
    {
        $this->senderName = $senderName;
        $this->commenterName = $commenterName;
        $this->subject = $subject;
        $this->fileName = $fileName;
        $this->comment = $comment;
    }

    public function build()
    {
        return $this->subject('New Comment on Your Document: ' . $this->subject)
                    ->view('emails.document-commented')
                    ->with([
                        'senderName' => $this->senderName,
                        'commenterName' => $this->commenterName,
                        'subject' => $this->subject,
                        'fileName' => $this->fileName,
                        'comment' => $this->comment,
                    ]);
    }
} 