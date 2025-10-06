<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmergencyLogMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;
    public $subjectLine;

    public function __construct($body, $subjectLine)
    {
        $this->body = $body;
        $this->subjectLine = $subjectLine;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
                    ->html($this->body);
    }
}