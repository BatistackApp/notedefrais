<?php

namespace App\Models;

use App\Observers\MediaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

#[ObservedBy([MediaObserver::class])]
class Media extends BaseMedia
{
    /**
     * Calcule le hash SHA-256 du fichier physique actuel sur le disque.
     */
    public function calculateCurrentHash(): ?string
    {
        if (! file_exists($this->getPath())) {
            return null;
        }

        return hash_file('sha256', $this->getPath());
    }

    /**
     * Vérifie si le fichier actuel correspond toujours au hash enregistré lors du scellement.
     */
    public function isIntegrityValid(): bool
    {
        $originalHash = $this->getCustomProperty('original_file_hash');

        if (! $originalHash) {
            return false; // Pas de hash initial = pas d'intégrité garantie
        }

        return $this->calculateCurrentHash() === $originalHash;
    }
}
