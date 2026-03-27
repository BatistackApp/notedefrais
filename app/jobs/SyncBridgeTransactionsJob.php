<?php

namespace App\jobs;

use App\Models\BankAccount;
use App\Notifications\BridgeSyncFailedNotification;
use App\Services\BankTransactionImportService;
use App\Services\BridgeApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncBridgeTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(BridgeApiService $bridgeApi, BankTransactionImportService $importService): void
    {
        // On récupère tous les comptes liés à Bridge et actifs
        $accounts = BankAccount::whereNotNull('bridge_account_id')->where('is_active', true)->get();

        foreach ($accounts as $account) {
            try {
                $since = $account->last_synced_at ? $account->last_synced_at->toIso8601String() : null;

                // Appel au service
                $transactions = $bridgeApi->getTransactions($account->bridge_account_id, $since);

                // Formatage minimaliste pour notre service d'import universel
                $formattedData = collect($transactions)->map(function ($tx) {
                    return [
                        'reference' => $tx['id'],
                        'date'      => $tx['date'],
                        'vendor'    => $tx['clean_description'] ?? $tx['description'] ?? 'Marchand inconnu',
                        'amount'    => $tx['amount'],
                    ];
                })->toArray();

                // Import en base via le service du Rapprochement
                $importCount = $importService->importFromCsv($account, $formattedData);

                // Validation de la synchro
                $account->update(['last_synced_at' => now()]);

                Log::info("Synchro Bridge réussie : {$importCount} nouvelles transactions pour le compte ID {$account->id}");

            } catch (\Exception $e) {
                Log::error("Erreur de synchro Bridge (Compte ID {$account->id}) : " . $e->getMessage());

                // Si l'erreur mentionne une expiration du consentement (DSP2 - 90 jours), on notifie l'admin
                if (str_contains(strtolower($e->getMessage()), 'consent') || str_contains(strtolower($e->getMessage()), 'unauthorized')) {
                    // Désactivation temporaire pour éviter de spammer l'API
                    $account->update(['is_active' => false]);
                    // Notifier l'utilisateur lié au compte (Voir 5.3)
                    if ($account->user) {
                        $account->user->notify(new BridgeSyncFailedNotification($account));
                    }
                }
            }
        }
    }
}
