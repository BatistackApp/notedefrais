<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use ToneGabes\Filament\Icons\Enums\Phosphor;

enum ReconciliationStatus: string implements HasLabel, HasColor, HasIcon, HasDescription
{
    case Pending = 'pending';
    case AutoReconciled = 'auto_reconciled';
    case ManualReconciled = 'manual_reconciled';
    case Ignored = 'ignored';
    case Disputed = 'disputed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'primary',
            self::AutoReconciled, self::Disputed, self::Ignored => 'danger',
            self::ManualReconciled => 'warning',
            default => 'null',
        };
    }

    public function getDescription(): string|Htmlable|null
    {
        return match ($this) {
            self::Pending => 'En attente de justificatif (orpheline)',
            self::AutoReconciled => 'Rapprochée automatiquement par l\'algorithme',
            self::ManualReconciled => 'Rapprochée manuellement par un comptable',
            self::Ignored => 'Ligne ignorée (ex: frais bancaires internes)',
            self::Disputed => 'Litige (montant différent, suspicion de fraude)',
            default => 'null',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::AutoReconciled, self::ManualReconciled => Phosphor::CheckCircle,
            self::Disputed => Phosphor::Prohibit,
            self::Ignored => Phosphor::ProhibitInset,
            default => Phosphor::Clock,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::AutoReconciled => 'Rapprochement Auto',
            self::ManualReconciled => 'Rapprochement Manuel',
            self::Ignored => 'Ignoré',
            self::Disputed => 'Litige',
            default => 'null',
        };
    }
}
