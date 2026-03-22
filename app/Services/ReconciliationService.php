<?php

namespace App\Services;

use App\Enums\ExpenseStatus;
use App\Models\BankTransaction;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;

class ReconciliationService
{
    /**
     * Tente de trouver des correspondances pour une transaction bancaire.
     */
    public function findPotentialMatches(BankTransaction $transaction): Collection
    {
        return Expense::query()
            ->where('amount_total', $transaction->amount_total)
            ->where('status', '!=', ExpenseStatus::REIMBURSED)
            ->whereBetween('expensed_at', [
                $transaction->transaction_at->subDays(4),
                $transaction->transaction_at->addDays(1),
            ])
            ->get();
    }

    /**
     * Effectue le rapprochement officiel.
     */
    public function reconcile(BankTransaction $transaction, Expense $expense): bool
    {
        return \DB::transaction(function () use ($transaction, $expense) {
            $expense->update([
                'status' => ExpenseStatus::REIMBURSED,
                'bank_transaction_id' => $transaction->id,
            ]);

            return $transaction->update([
                'is_reconciled' => true,
                'expense_id' => $expense->id,
            ]);
        });
    }
}
