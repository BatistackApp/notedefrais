<?php

namespace App\jobs;

use App\Enums\ReconciliationStatus;
use App\Models\BankTransaction;
use App\Notifications\OrphanTransactionDetectedNotification;
use App\Services\ErrorHandlerToJiraService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DetectOrphanTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Cherche les transactions en attente vieilles de plus de 7 jours
        $orphanTransactions = BankTransaction::where('reconciliation_status', ReconciliationStatus::Pending)
            ->where('transaction_date', '<', Carbon::now()->subDays(7))
            ->get();

        foreach ($orphanTransactions as $transaction) {
            // Note: En conditions réelles, on notifierait l'utilisateur lié au compte bancaire.
            // Ici, nous notifions l'administrateur ou le responsable du compte.
            $user = $transaction->bankAccount->user ?? null;

            if ($user) {
                $user->notify(new OrphanTransactionDetectedNotification($transaction));
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function failed(\Throwable $exception): void
    {
        app(ErrorHandlerToJiraService::class)->handle(
            exception: $exception,
            summary: '[Job Failed]: Erreur lors du processus automatique',
            description: 'Le job DetectOrphanTransactionJob à échoué'
        );
    }
}
