<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\FormAccessToken;

class FormAccessEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    public function __construct(FormAccessToken $token)
    {
        $this->token = $token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation à évaluer votre module',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.form-access',
        );
    }
}
