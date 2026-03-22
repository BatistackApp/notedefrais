<?php

namespace App\Services;

use App\Enums\ReconciliationStatus;
use App\Models\BankTransaction;
use App\Models\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BankReconciliationService
{
    /**
     * Tente de rapprocher automatiquement toutes les transactions en attente.
     * C'est la méthode qui sera appelée par notre futur Job planifié.
     *
     * * @return int Le nombre de transactions rapprochées avec succès.
     */
    public function autoReconcilePendingTransactions(): int
    {
        $pendingTransactions = BankTransaction::where('reconciliation_status', ReconciliationStatus::Pending)->get();
        $reconciledCount = 0;

        foreach ($pendingTransactions as $transaction) {
            if ($this->attemptAutoReconciliation($transaction)) {
                $reconciledCount++;
            }
        }

        return $reconciledCount;
    }

    /**
     * Tente de rapprocher une transaction spécifique.
     */
    public function attemptAutoReconciliation(BankTransaction $transaction): bool
    {
        $expense = $this->findMatchingExpense($transaction);

        if ($expense) {
            // Rapprochement automatique réussi
            $this->reconcile($transaction, $expense, ReconciliationStatus::AutoReconciled);

            return true;
        }

        return false;
    }

    /**
     * Applique les heuristiques (Étape 3) pour trouver une correspondance.
     * - Montant exact (en valeur absolue)
     * - Date de dépense entre (Date Banque - 3 jours) et (Date Banque + 1 jour)
     */
    private function findMatchingExpense(BankTransaction $transaction): ?Expense
    {
        // La banque affiche un débit en négatif, mais la note de frais a un montant positif
        $targetAmount = abs($transaction->amount);
        $transactionDate = Carbon::parse($transaction->transaction_date);

        return Expense::where('reconciliation_status', ReconciliationStatus::PENDING)
            ->where('amount', $targetAmount) // Montant strict
            ->whereBetween('expense_date', [
                $transactionDate->copy()->subDays(3)->format('Y-m-d'), // Dépense max 3 jours avant le passage en banque
                $transactionDate->copy()->addDay()->format('Y-m-d'),    // Dépense saisie exceptionnellement 1 jour après
            ])
            ->first();
        // Note: S'il y a plusieurs résultats, on prend le premier.
        // TODO: Une évolution future (Fuzzy Matching) pourrait comparer les noms des marchands ici.
    }

    /**
     * Lie la transaction et la note de frais en base de données.
     * Utilisation d'une transaction DB pour garantir l'intégrité des données.
     */
    public function reconcile(BankTransaction $transaction, Expense $expense, ReconciliationStatus $status): void
    {
        DB::transaction(function () use ($transaction, $expense, $status) {
            $transaction->update(['reconciliation_status' => $status]);

            $expense->update([
                'bank_transaction_id' => $transaction->id,
                'reconciliation_status' => $status,
            ]);
        });
    }

    /**
     * Permet d'annuler un rapprochement (utile pour le comptable en cas d'erreur).
     */
    public function unreconcile(BankTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $expense = $transaction->expense;

            if ($expense) {
                $expense->update([
                    'bank_transaction_id' => null,
                    'reconciliation_status' => ReconciliationStatus::Pending,
                ]);
            }

            $transaction->update(['reconciliation_status' => ReconciliationStatus::Pending]);
        });
    }
}
