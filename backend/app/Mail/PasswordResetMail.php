<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private $token, private $email)
    {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Password Reset Request');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'token' => $this->token,
                'email' => $this->email,
                'url' => url("password-reset/{$this->token}?email={$this->email}"),
            ],
        );
    }
}
