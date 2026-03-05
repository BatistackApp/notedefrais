<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ExpenseStatus: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 'draft';         // Brouillon (non soumis)
    case PENDING = 'pending';     // En attente de validation
    case APPROVED = 'approved';   // Validée par l'admin/compta
    case REJECTED = 'rejected';   // Refusée (nécessite un motif)
    case REIMBURSED = 'paid';     // Remboursée au salarié

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PENDING => 'En attente',
            self::APPROVED => 'Validée',
            self::REJECTED => 'Refusée',
            self::REIMBURSED => 'Remboursée',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::REIMBURSED => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-m-pencil-square',
            self::PENDING => 'heroicon-m-clock',
            self::APPROVED => 'heroicon-m-check-circle',
            self::REJECTED => 'heroicon-m-x-circle',
            self::REIMBURSED => 'heroicon-m-banknotes',
        };
    }
}
