<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\FormAccessToken;

class FormAccessEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $formAccessToken;

    public function __construct(FormAccessToken $formAccessToken)
    {
        $this->formAccessToken = $formAccessToken;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Accès à votre formulaire d\'évaluation',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.form-access',
            with: [
                'token' => $this->formAccessToken->token,
                'studentName' => $this->formAccessToken->student->name,
                'formTitle' => $this->formAccessToken->form->title,
                'expiresAt' => $this->formAccessToken->expires_at->format('d/m/Y H:i')
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
