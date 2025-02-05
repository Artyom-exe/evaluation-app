<?php

namespace App\Mail;

use App\Models\Form;
use App\Models\FormToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FormSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $form;
    public $token;

    /**
     * Create a new message instance.
     */
    public function __construct(Form $form, FormToken $token)
    {
        $this->form = $form;
        $this->token = $token;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Nouvelle évaluation : {$this->form->title}")
                    ->markdown('emails.form_submission')
                    ->with([
                        'form' => $this->form,
                        'token' => $this->token,
                    ]);
    }
}
