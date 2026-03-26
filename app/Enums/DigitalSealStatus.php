<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DigitalSealStatus: string implements HasLabel, HasColor, HasIcon
{
    case Unsealed = 'Unsealed';
    case Sealed = 'Sealed';
    case Compromised = 'Compromised';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Unsealed => 'primary',
            self::Sealed => 'gray',
            self::Compromised => 'danger',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Unsealed => 'Non Scellé',
            self::Sealed => 'Scellé',
            self::Compromised => 'Hash Compromis',
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::Unsealed => 'Le ticket est encore en brouillon, modifiable',
            self::Sealed => 'Le ticket est scellé, intègre et horodaté',
            self::Compromised => 'ALERTE : Le fichier physique a été modifié, le hash ne correspond plus !',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Unsealed => 'heroicon-o-lock-closed',
            self::Sealed => 'heroicon-o-document-text',
            self::Compromised => 'heroicon-o-exclamation-triangle',
        };
    }
}
