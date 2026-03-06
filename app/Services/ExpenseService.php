<?php

namespace App\Services;

use App\Enums\ExpenseStatus;
use App\Models\Expense;

/**
 * Service gérant le cycle de vie d'une dépense (Carte Entreprise).
 * Suit le principe de Responsabilité Unique (SRP).
 */
class ExpenseService
{
    /**
     * Soumettre une dépense pour validation.
     */
    public function submit(Expense $expense): bool
    {
        if ($expense->status !== ExpenseStatus::DRAFT) {
            return false;
        }

        // Vérification de la présence du justificatif avant soumission
        if ($expense->getMedia('receipts')->isEmpty()) {
            throw new \Exception('Un justificatif est obligatoire pour soumettre cette dépense.');
        }

        return $expense->update([
            'status' => ExpenseStatus::PENDING,
        ]);
    }

    /**
     * Approuver une dépense (Validation comptable).
     */
    public function approve(Expense $expense): bool
    {
        return $expense->update([
            'status' => ExpenseStatus::APPROVED,
        ]);
    }

    /**
     * Rejeter une dépense avec un motif.
     */
    public function reject(Expense $expense, string $reason): bool
    {
        return $expense->update([
            'status' => ExpenseStatus::REJECTED,
            'description' => $expense->description."\n\n[REFUS] : ".$reason,
        ]);
    }

    /**
     * Calculer la TVA suggérée selon le taux.
     */
    public function calculateTax(float $total, float $rate): float
    {
        return round($total - ($total / (1 + ($rate / 100))), 2);
    }
}
