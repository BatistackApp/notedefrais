<?php

namespace App\Services;

use App\Enums\ReconciliationStatus;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class BankTransactionImportService
{
    /**
     * Importe un fichier CSV de transactions bancaires.
     *
     * * @param BankAccount $bankAccount Le compte cible
     * @param  array  $csvRecords  Les lignes du CSV parsées en tableau
     * @return int Le nombre de transactions importées
     */
    public function importFromCsv(BankAccount $bankAccount, array $csvRecords): int
    {
        $importedCount = 0;

        foreach ($csvRecords as $record) {
            // Empêcher les doublons grâce à un identifiant externe (ex: référence de l'opération)
            // ou en générant un hash unique basé sur la date, le marchand et le montant.
            $externalId = $record['reference'] ?? md5($record['date'].$record['vendor'].$record['amount']);

            $exists = BankTransaction::where('external_id', $externalId)
                ->orWhere(function ($query) use ($bankAccount, $record) {
                    $query->where('bank_account_id', $bankAccount->id)
                        ->where('transaction_date', Carbon::parse($record['date'])->format('Y-m-d'))
                        ->where('vendor_name', $record['vendor'])
                        ->where('amount', $record['amount']);
                })->exists();

            if (! $exists) {
                BankTransaction::create([
                    'bank_account_id' => $bankAccount->id,
                    'external_id' => $externalId,
                    'transaction_date' => Carbon::parse($record['date'])->format('Y-m-d'),
                    'vendor_name' => $record['vendor'],
                    'amount' => (float) $record['amount'],
                    'currency' => $bankAccount->currency,
                    'reconciliation_status' => ReconciliationStatus::Pending,
                ]);

                $importedCount++;
            } else {
                Log::info("Transaction {$externalId} ignorée (déjà existante).");
            }
        }

        return $importedCount;
    }
}
