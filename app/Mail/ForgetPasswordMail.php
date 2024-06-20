<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $domain;
    public $name;

    public function __construct($domain , $name)
    {
        $this->domain = $domain;
        $this->name = $name;
    }

    public function build()
    {
        return $this->markdown('emails.forget_password')
                    ->with(['domain' => $this->domain]);
    }
}
