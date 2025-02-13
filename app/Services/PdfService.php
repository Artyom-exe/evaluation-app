<?php

namespace App\Services;

use App\Models\Form;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    public function generateFormResultsPdf(Form $form, $responses)
    {
        // Créer le dossier temp s'il n'existe pas
        $tempPath = storage_path('app/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0777, true);
        }

        try {
            $pdf = PDF::loadView('pdfs.form-results', [
                'form' => $form,
                'responses' => $responses
            ]);

            $filename = $tempPath . '/' . uniqid() . '_results.pdf';
            $pdf->save($filename);

            return $filename;
        } catch (\Exception $e) {
            \Log::error('Erreur génération PDF:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
