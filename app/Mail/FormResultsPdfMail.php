<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Form;

class FormResultsPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public $form;
    public $pdfPath;

    public function __construct(Form $form, $pdfPath)
    {
        $this->form = $form;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->markdown('emails.form-results-pdf')
            ->subject("Résultats du formulaire : {$this->form->title}")
            ->attach($this->pdfPath, [
                'as' => 'resultats-formulaire.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
