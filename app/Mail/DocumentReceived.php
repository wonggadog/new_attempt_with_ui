<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $recipientName;
    public $senderName;
    public $subject;
    public $fileName;
    public $actionItems;
    public $additionalActions;
    public $notes;

    public function __construct($recipientName, $senderName, $subject, $fileName, $actionItems, $additionalActions, $notes)
    {
        $this->recipientName = $recipientName;
        $this->senderName = $senderName;
        $this->subject = $subject;
        $this->fileName = $fileName;
        $this->actionItems = $actionItems;
        $this->additionalActions = $additionalActions;
        $this->notes = $notes;
    }

    public function build()
    {
        return $this->subject('New Document Uploaded: ' . $this->subject)
                    ->view('emails.document-received')
                    ->with([
                        'recipientName' => $this->recipientName,
                        'senderName' => $this->senderName,
                        'subject' => $this->subject,
                        'fileName' => $this->fileName,
                        'actionItems' => $this->actionItems,
                        'additionalActions' => $this->additionalActions,
                        'notes' => $this->notes,
                    ]);
    }
} 