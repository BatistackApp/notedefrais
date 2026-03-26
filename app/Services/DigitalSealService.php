<?php

namespace App\Services;

use App\Enums\DigitalSealStatus;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DigitalSealService
{
    /**
     * Scelle numériquement les justificatifs d'une note de frais.
     */
    public function sealExpense(Expense $expense): bool
    {
        // On ne scelle que si ce n'est pas déjà fait
        if ($expense->digital_seal_status === DigitalSealStatus::SEALED) {
            return true;
        }

        $mediaItems = $expense->getMedia('receipts'); // 'receipts' est la collection Spatie par défaut

        if ($mediaItems->isEmpty()) {
            Log::warning("Tentative de scellement d'une note de frais sans justificatif (ID: {$expense->id})");
            return false;
        }

        DB::transaction(function () use ($expense, $mediaItems) {
            foreach ($mediaItems as $media) {
                // Calcul de l'empreinte numérique du fichier physique
                $hash = $media->calculateCurrentHash();

                // Enregistrement de l'empreinte et de la date dans le JSON custom_properties de Spatie
                $media->setCustomProperty('original_file_hash', $hash);
                $media->setCustomProperty('sealed_at', now()->toIso8601String());
                $media->save();
            }

            // Verrouillage de la note de frais parente
            $expense->update([
                'digital_seal_status' => DigitalSealStatus::Sealed,
                'sealed_at' => now(),
            ]);
        });

        return true;
    }

    /**
     * Vérifie l'intégrité de la note de frais (Audit).
     */
    public function verifyIntegrity(Expense $expense): bool
    {
        if ($expense->digital_seal_status !== DigitalSealStatus::Sealed) {
            return true; // Rien à vérifier si ce n'est pas scellé
        }

        $isValid = true;
        $mediaItems = $expense->getMedia('receipts');

        foreach ($mediaItems as $media) {
            if (! $media->isIntegrityValid()) {
                $isValid = false;
                break;
            }
        }

        if (! $isValid) {
            // Le fichier a été compromis ! On déclenche l'alerte.
            $expense->update(['digital_seal_status' => DigitalSealStatus::COMPROMISED]);
            return false;
        }

        return true;
    }
}
