<?php

namespace App\Observers;

use App\Enums\DigitalSealStatus;
use App\Models\Media;
use Illuminate\Auth\Access\AuthorizationException;

class MediaObserver
{
    public function updating(Media $media): void
    {
        $this->ensureParentIsNotSealed($media);
    }

    public function deleting(Media $media): void
    {
        $this->ensureParentIsNotSealed($media);
    }

    private function ensureParentIsNotSealed(Media $media): void
    {
        // Le polymorphisme de Spatie permet d'accéder au modèle parent (ici l'Expense)
        $parent = $media->model;

        // Si le parent a un statut de scellement et qu'il est déjà scellé, on bloque l'action
        if ($parent && method_exists($parent, 'getAttribute') && $parent->getAttribute('digital_seal_status') === DigitalSealStatus::Sealed) {
            throw new AuthorizationException('Ce justificatif a été scellé numériquement et ne peut plus être modifié ou supprimé.');
        }
    }
}
