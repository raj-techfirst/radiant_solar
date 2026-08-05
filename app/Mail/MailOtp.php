<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $details;
    public $param;

    public function __construct($details, $param)
    {
        $this->details = $details;
        $this->param = $param;
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Forget Password on Techfirst CRM',
        );
    }

    public function content()
    {
        return new Content(
            view: 'mail_otp',
        );
    }

    public function attachments()
    {
        return [];
    }
}
